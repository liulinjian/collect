<?php
class IndexAction extends CommonAction {
	/*
		kaoder    采集器
		version   1.0
		author    littlebear
		date      2013/11/19
		func      根据文章来源url获取文章标题和内容 
	*/
	public $snoopy;
	const TYPE_TITLE = 0;
	const TYPE_CONTENT = 1;

	function __construct() {
		parent::__construct();
		//伪装
		$this->snoopy=new Snoopy();
		$this->snoopy->agent = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 Safari/537.11"; //伪装浏览器  
		$this->snoopy->rawheaders["Pragma"] = "no-cache";	 //cache 的http头信息  
		//$this->snoopy->rawheaders["X_FORWARDED_FOR"] = "222.28.40.101"; //伪装ip  
		$this->snoopy->rawheaders["X-Requested-With"] = "XMLHttpRequest"; //伪装AJAX
		$this->snoopy->rawheaders["Accept"] = "*/*; q=0.01"; //伪装AJAX
		//$this->snoopy->rawheaders["Cookie"] = "JSESSIONID=C30C576FF8B75E962644997ECFC25D12;"; //伪装AJAX
		//$this->snoopy->rawheaders["Referer"] = "http://www.simsimi.com/talk.htm?lc=ch";
	}

	//域名管理主页：http://localhost/kd/?m=collect&a=rules
	public function rules(){
		$collect = $this->collect->get_collect_by_page(0,null);
		foreach ($collect as &$c) {
			$c['title'] = $this->collect_match->get_matchlist_by_collect_type( $c['id'], self::TYPE_TITLE );
			$c['content'] = $this->collect_match->get_matchlist_by_collect_type( $c['id'], self::TYPE_CONTENT );
		}
		$this->assign( 'collect', $collect );
		$this->display('Tpl/rules.htm');
	}

	//删除某一采集域名
	public function delete_collect(){
		$id = intval( $this->_post('id') );
		$res = $this->collect->_delete( $id );
		$json = array();
		if ( $res ) {
			$json['error'] = 0;
			$json['msg'] = '删除成功';
		}
		else{
			$json['error'] = 1;
			$json['msg'] = '删除失败，请稍后再试';
		}
		echo json_encode( $json );
	}

 	//采集url的方法，采集核心代码
 	//url页面的url，最好以http://开头，否则自动增加http://
	public function collect(){
		$url = trim( $this->_post('url') );
		//返回结果
		$res = array( 'title'=>'', 'content'=>'' );

		//分析网页是否包含视频
		$video = $this->uVideoUpload( $url );
		if ($video!='10' && $video != '11') {
			//获取标题
			$htm = file_get_html( $url );
			$title = $htm->find('title',0)->plaintext;
			$htm->clear();

			$res['title'] =  $title;
			$res['content'] = $video;
			echo json_encode( $res );
			exit();
		}
		//不含视频，则按文章和音乐处理
		$collect = D('collect');
		$domin = '';
		$match = "/http:\/\/([^\/]*).*/i";
		if ( !substr_count($url, "http") ) {
			$url = "http://".$url;
		}
		preg_match($match, $url, $out);
		$domin = $out[1];
		//pattern用于记录内容匹配规则
		$pattern = '';
		if ( !empty($domin) ) {
			//如果是音乐网站，暂时仅支持虾米音乐
			$music_websites = C('MUSIC_WEBSITES');
			if ( in_array($domin, $music_websites) ) {
				$res = $this->get_music( $url, $domin );
				echo json_encode( $res );
				exit();
			}

			//查看数据库中是否已经有该域名的记录
			$c = $collect->where('domain="'.$domin.'"')->find();
			if ( !$c ) {
				//没有数据库记录，则title为页面title，content为body正文
				$collect->data(array(
					'alias' => $domin,
					'domain'   => $domin,
				))->add();
				//查找body
				$htm = file_get_html( $url );
				//title
				$title = $htm->find('title',0)->plaintext;
				//content & pattern
				$results = $this->get_content_body( $domin, $htm ); 
				$pattern = $results['pattern'];
				//content
				$res['content'] = $results['content'];
				//title取title标签的内容
				$res['title'] = $title;
			}
			else{
				//新浪博客URL处理，去掉结尾的 ?tj=...
				if ( $domin == 'blog.sina.com.cn' ) {
					$url = preg_replace('/\?tj=.*/i', '', $url);
				}

				$htm = file_get_html( $url );
				//获取title
				$matchlist = $this->collect_match->get_matchlist_by_collect_type( $c['id'], self::TYPE_TITLE );
				if ( !empty( $matchlist ) ) {
					$exec = '$htm';
					foreach ($matchlist as $match) {
					  $exec .= "->find( '$match[match]', $match[pos] )"; 
					}
					$exec = $exec.'->plaintext;';
					eval("\$str = $exec;");
					$res['title'] = $str;
				}
				else{
					$title = $htm->find('title',0)->plaintext;
					$res['title'] = $title;
				}
				
				//获取content
				$matchlist = $this->collect_match->get_matchlist_by_collect_type( $c['id'], self::TYPE_CONTENT );
				if ( !empty( $matchlist ) ) {
					$pattern = '$htm';
					foreach ($matchlist as $match) {
					  $pattern .= "->find( '$match[match]', $match[pos] )"; 
					}
					$pattern = $pattern.'->innertext;';

					eval("\$str = $pattern;");
					$res['content'] = $str;
				}
				else{
					$results = $this->get_content_body( $domin, $htm );
					$pattern = $results['pattern'];
					$res['content'] =  $results['content'];
				}
				//内容特殊处理
				$res['content'] = $this->process_special_content( $url, $domin, $res['content'] );
			}
			//释放内存消耗
			$htm->clear();
		}else{
			$res['title'] = '抓取失败';
			$res['content'] = '抓取失败';
			echo json_encode( $res );
			exit();
		}

		//获取内容分页特殊处理
		$res['content'] = $this->process_pagination( $url, $domin, $res['content'] );
		

		$res['content'] = $this->clearhtml( $res['content'] );
		$res['title'] = trim( $this->clearhtml( $res['title'] ) );

		//转码处理
		$need_iconv = C('NEED_ICONV');
		if ( in_array( $domin , $need_iconv ) ) {
			if ( $domin == 'history.sina.com.cn'  ) {
				//GBK  编码特殊处理
				$res['title'] = iconv("GBK","UTF-8//IGNORE",$res['title']);
				$res['content'] = iconv("GBK","UTF-8//IGNORE",$res['content']);
			}
			else{
				//GB2312 编码处理
				$res['title'] = iconv("GB2312","UTF-8//IGNORE", $res['title']);
				$res['content'] = iconv("GB2312","UTF-8//IGNORE",$res['content']);
			}
		}
		echo json_encode( $res );
	}

	//解析URL主页 http://localhost/kd/?m=collect
	public function index(){
		$this->display('Tpl/index.html');
	}

	//文章主体内容特殊处理
	private function process_special_content($url, $domain, $content){
		//某些网站图片路径补全处理
		if (  $domain == 'history.people.com.cn' ) {
			$content = preg_replace("/src=\"(.*?)\"/i", 'src="http://'.$domain."$1".'"', $content);
		}
		else if ( $domain == 'blog.sina.com.cn' ) {
			//新浪图片，需要把real_src和src属性互相特换
			$new_html = new simple_html_dom();
			$new_html->load( $content );
			$imgs = $new_html->find('img');
			foreach ($imgs as &$img) {
				$img->src = $img->real_src;
				$img->real_src = null;
			}
			$content = $new_html->innertext;
			$new_html->clear();
		}else if ( $domain == 'www.nowamagic.net' ) {
			$base = 'http://www.nowamagic.net/librarys/';
			$content = preg_replace("/src=\"\.\.\/\.\.\/(.*?)\"/i", 'src="'.$base."$1".'"', $content);
		}else if ( $domain == 'www.jfdaily.com' ) {
			$content = preg_replace("/src=\"(.*?)\"/i", 'src="http://'.$domain."$1".'"', $content);
		}
		return $content;
	}

	//文章若有分页，在此特殊处理
	private function process_pagination($url, $domain, $content){
		//获取内容处理html标签
		if (preg_match("/id\s*?=\s*?['\"]page['\"]/", $content) || preg_match("/class\s*?=\s*?['\"]page['\"]/", $content)) {
			$all = substr($url, strripos($url, '/'));
			
			$arr = explode('.', $all);
			$arr[0] .= '_all';
			$str = $arr[0].'.'.$arr[1];
			//查看全文的url
			$url = str_replace($all, $str, $url);
			$htm = file_get_html( $url );
			//查看全文
			eval("\$content = $pattern;");
			$content = $this->process_special_content( $url, $domain, $content );
		}
		return $content;
	}

	//处理抓取文章里面的HTML
	function clearhtml( &$content ) {
		$content = preg_replace("/<a[^>]*>/i", "", $content);  
		$content = preg_replace("/<\/a>/i", "", $content);
		$content = preg_replace("/<div[^>]*>(.*?)<\/div>/i","<p>$1</p>", $content);
		$content = preg_replace("/<br>{2,}/i",'<br>', $content);
        $content = preg_replace("/(<br\s\/>){2,}/i",'<br />', $content);
		$content = preg_replace("/<font[^>]*>/i",'', $content);   
		$content = preg_replace("/<\/font>/i",'', $content);
		$content = preg_replace("/<span[^>]*>/i",'', $content);   
		$content = preg_replace("/<\/span>/i",'', $content);
		$content = preg_replace("/<u[^>]*>/i",'', $content);
		$content = preg_replace("/<\/u>/i",'', $content);
		//$content = preg_replace("/<b[^>]*>/i",'', $content);
		//$content = preg_replace("/<\/b>/i",'', $content);
		$content = preg_replace("/style=.+?['|\"]/i",'',$content);
		$content = preg_replace("/<script[^>]*>(.*?)<\/script>/i", '', $content);
		$content = preg_replace("/<style[^>]*>(.*?)<\/style>/i", '', $content);
		$content = preg_replace("/<link[^>]*\/>/i", '', $content);
		$content = preg_replace("/class=.+?['|\"]/i",'',$content);

		$content = preg_replace("/<input[^>]*\/>/i", '', $content);
		$content = preg_replace("/<input[^>]*>(.*?)<\/input>/i", '', $content);

		//label属性
		$content = preg_replace("/<label[^>]*>(.*?)<\/label>/i", '<label>$1</label>', $content);
		$content = preg_replace("/<form[^>]*>(.*?)<\/form>/", '', $content);

		$content = preg_replace("/\sid=.+?['|\"]/i",'',$content);  
		$content = preg_replace("/\sclass=.+?['|\"]/i",'',$content);  
		$content = preg_replace("/lang=.+?['|\"]/i",'',$content); 
		
		$content = preg_replace("/border=.+?['|\"]/i",'',$content);
		$content = preg_replace("/<iframe[^>]*>(.*?)<\/iframe>/i",'', $content); 
		$content = preg_replace("/<div[^>]*>/i", "", $content);  
		$content = preg_replace("/<\/div>/i", "", $content);
		$content = preg_replace("/<p>(<br>)?<\/p>/i", "", $content);
		$content = preg_replace("/<p>(&nbsp;){1,}<\/p>/i", "", $content);

		$content = preg_replace("/\"/i", "'", $content);

		$content = preg_replace("/<!--(.*?)-->/i", "", $content);
		$content = preg_replace("/\t/", "", $content);
		return $content;
	}

	//数据库中没有匹配规则时根据一下算法自动获取文章主题内容
	// $domain 域名   $htm 文章主题内容
	private function get_content_body( $domain, $htm ){
		//content
		$divs = $htm->find('body', 0)->find('div');
		$content_match = 'body';
		$flag = false;
		$match_array = C('CONTENT_CODE');
		$contentlist = $this->collect_match->get_matchlist_by_type( self::TYPE_CONTENT );
		foreach ($contentlist as &$cl) {
			$cl['match'] = strtolower( preg_replace('/.*?[\.|\#]/', '', $cl['match']) );
			array_push( $match_array , $cl['match']);
		}
		//push body
		array_unique( $match_array );

		$page_code = C('PAGE_CODE');
		$p = 0;
		foreach ($divs as $div) {
			foreach ($match_array as $ma) {
				$tp1 = 0;
				$tp2 = 0;
				if ( $div->class ) {
					similar_text($ma, trim( strtolower( $div->class ) ), $tp1);
				}
				if ( $div->id ) {
					similar_text($ma, trim( strtolower( $div->id ) ), $tp2);
				}
				$temp = ($tp1 || $tp2) && ($tp1>$tp2) ? $div->class : $div->id;
				if ( $temp ) {
					$which = $tp1>$tp2 ? '.' : '#';
					$tp1 = max( $tp1, $tp2 );
					if ( $tp1 > $p ) {
						$p = $tp1;
						$content_match = 'div'.$which.trim( $temp );
					}
					if ( $p >= 95.0 ) {
						$flag = true;
						break;
					}
				}
			}
			if ( $flag ) {
				break;
			}
		}
		//将算法获取的匹配规则存入数据库
		//content匹配规则
		$collect = $this->collect->get_collect_by_domain( $domain );
		$data = array(
					'cid'   =>  $collect['id'],
					'match' =>  $content_match,
					'pos'   =>  0,
					'type'  =>  self::TYPE_CONTENT,
			);
		$this->collect_match->_create( $data );
		//title匹配规则
		$data = array(
					'cid'   =>  $collect['id'],
					'match' =>  'title',
					'pos'   =>  0,
					'type'  =>  self::TYPE_TITLE,
			);
		$this->collect_match->_create( $data );

		$exec = '$htm->find("'.$content_match.'", 0)->innertext;';
		//echo $exec;
		//content
		eval("\$content = $exec;");
		//echo $content;
		return array(
			'content' => $content,
			'pattern' => $exec,
 		);
	}

	//如果是音乐网站，暂时仅支持虾米音乐
	private function get_music( $url, $domin ){
		$res = array();
		//虾米音乐
		if ( $domin == 'www.xiami.com' ) {
			$htm = file_get_html( $url );
			$p = preg_match('/var\s*?_xiamitoken\s*?=\s*?[\'\"](.*?)[\'\"]/i', $htm, $out);
			$token = $out[1];
			//onclick="playalbum(682938274, '', '时间的歌', '');   获取xid特征码
			$xid = ''; 
			if ( preg_match('/playalbum\((\\d+),\s*?\'*?\',\s*?\'(.*?)\',\s*?\'*?\'\)/i', $htm, $out) ) {
				//xid
				$xid = $out[1];
				//title
				$title = $out[2];
			}else if( preg_match('/\/album\/(\d{1,})/', $htm, $out) ){
				$xid = $out[1];
				$title = $htm->find('div#title',0)->plaintext;
			}else if ( preg_match('/var\s*?cid\s*?=\s*?[\'\"](.*?)[\'\"]/i', $htm, $out) ) {
				#var cid = '22454617';
				$xid = $out[1];
				$title = $htm->find('title',0)->plaintext;
			}
			//清除内存消耗
			$htm->clear();
			if ( $xid ) {
				//为了获取flash播放地址，得到音乐转帖页面源代码  example：
				//http://www.xiami.com/ajax/getquote/type/2/id/682938274?_xiamitoken=0802020a13ba3df687e7ca4ef45cf1a8
				$zurl = "http://www.xiami.com/ajax/getquote/type/2/id/$xid?_xiamitoken=$token";
				$htm = file_get_html( $zurl );
				$content = $htm->find('textarea.tarea',1)->innertext;
				$res['title'] = trim( $title );
				$res['content'] = $content;
				//清除内存消耗
				$htm->clear();
			}else{
				$res['title'] = '';
				$res['content'] = '没有找到音乐';
			}
		}
		return $res;
	}

	public function test(){
		// $url = $this->_get( 'url' );
		// $htm = file_get_html( $url );
		// echo "URL===>".$url.'<br>';
		// $title = $htm->find('title',0)->plaintext;
		// echo $title;
		// echo "<br>";
		// //content
		// $divs = $htm->find('div');
		// $content_match = 'body';
		// $flag = false;
		// $contentlist = $this->collect_match->get_matchlist_by_type( self::TYPE_CONTENT );
		// $p = 0.00;
		// foreach ($divs as $div) {
		// 	foreach ($contentlist as &$cl) {
		// 		$cl['match'] = strtolower( preg_replace('/.*?[\.|\#]/', '', $cl['match']) );
				
		// 		similar_text($cl['match'], trim( strtolower( $div->class ) ), $tp);
		// 		//echo "p class========$div->class========$p<br>";
		// 		if ( $tp>$p ) {
		// 			$p = $tp;
		// 		}
		// 		if ( $p >= 100.0 ) {
		// 			$flag = true;
		// 			$content_match = 'div.'.trim( $div->class );
		// 			break;
		// 		}

		// 		similar_text($cl['match'], trim( strtolower( $div->id ) ), $tp);
		// 		//echo "p id========$div->id========$p<br>";
		// 		if ( $tp>$p ) {
		// 			$p = $tp;
		// 		}
		// 		if ( $p >= 100.0 ) {
		// 			$flag = true;
		// 			$content_match = 'div.'.trim( $div->id );
		// 			break;
		// 		}
		// 	}
		// 	if ( $flag ) {
		// 		echo "$p ======> ".$content_match.'<br>';
		// 		break;
		// 	}
		// }
		// echo $content_match;
		// echo "<br>";
		// print_r( $contentlist );
		// // $exec = '$htm->find("'.$content_match.'", 0)->plaintext;';
		// // echo $exec;
		// // echo "<br>";
		// // eval("\$str = $exec;");
		// // echo $str;
		// $htm->clear();
		// echo "<br>";
		$url = "http://news.qq.com/a/20131216/005480.htm";
		$htm = file_get_contents( $url  );
		// $a = $htm->find('img');
		print_r( $htm );
	}

	/*======================================================================*\
	Function:	_expandlinks
	Purpose:	expand each link into a fully qualified URL
	Input:		$links			the links to qualify
				$URI			the full URI to get the base from
	Output:		$expandedLinks	the expanded links
	\*======================================================================*/

	function _expandlinks($links,$URI)
	{
		
		preg_match("/^[^\?]+/",$URI,$match);

		$match = preg_replace("|/[^\/\.]+\.[^\/\.]+$|","",$match[0]);
		$match = preg_replace("|/$|","",$match);
		$match_part = parse_url($match);
		$match_root =
		$match_part["scheme"]."://".$match_part["host"];
				
		$search = array( 	"|^http://".preg_quote($this->host)."|i",
							"|^(\/)|i",
							"|^(?!http://)(?!mailto:)|i",
							"|/\./|",
							"|/[^\/]+/\.\./|"
						);
						
		$replace = array(	"",
							$match_root."/",
							$match."/",
							"/",
							"/"
						);			
				
		$expandedLinks = preg_replace($search,$replace,$links);

		return $expandedLinks;
	}

	//分析视频内容，在抓取URL第一步进行
	private function uVideoUpload($url) {
		// 分析URL
		$check = preg_match('#http://#i', $url, $f);  //url已http://开头  i 忽略大小写
		if(empty($check)) {
			return "10"; 
			exit;
		}
		// 解析 swf url 是否在 指定的网站内
		$vidonet = array('youku.com', '56.com', 'ku6.com', 'tudou.com', 'joy.cn', 'sina.com.cn', 'ifeng.com', 'qq.com', 'sohu.com', 'iqiyi.com', 'qiyi.com', 'baidu.com', 'tv.tv.cn', 'iqiyi.com', 'wasu.cn', '163.com');
				$url = trim($url);
		$exp = explode('.', $url);
		$ext = end($exp);
				$turl = parse_url($url);
		$host = $turl['host'];
		$h = explode('.', $host);
	
		if(false === strpos($host, '.') || preg_match("/^(\d+\.){3}(\d+)$/", $host)){
			$root = $host;
		}
		elseif(preg_match("/(?:com|tel|mobi|net|org|asia|me|tv|biz|cc|name|info|cn)(?:\.\w{2})$/", $host, $match)){
			array_pop($h);		// 新浪视频在这里执行
			array_pop($h);
			$root = array_pop($h) . '.' . $match[0];
		}
		elseif(preg_match("/(?:\w{2,4})$/", $host, $match)){
			array_pop($h);
			$root = array_pop($h) . '.' . $match[0];
		}
		/* for video.baidu.com */
		if($root == 'baidu.com' && preg_match("/url=http/i", $url)) {
			$path_info = explode('&', $url);
			$count = count($path_info);
			if($count > 0) {
				$end = $path_info[$count-1];
				$end_info = explode('=', $end);
				if($end_info[1]) $url = $end_info[1];
			}
		}
		elseif(substr($host, 0, 5) == 'baidu' && in_array($root, $vidonet)) {
			/* for baidu.v....com 例如 baidu.v.ifeng.com */
			/* http://baidu.v.ifeng.com/kan/Z1Ie */
			$text= @file_get_contents($url);
			preg_match_all("/encodeURIComponent\(\'(.*?)\'\)/i", $text, $info);
			$url = $info[1][0];
		}
		$html = '';
		if(in_array($root, $vidonet) && $ext != 'swf'){		// 解析所有在数组中的不是以swf url 的视频
			$video = new Video($url);
			$videoInfo = $video->VideGet();
			//print_r( $videoInfo );
			//exit();
			if($videoInfo && $videoInfo['flashvar'] == 'ifeng.swf') {	// 凤凰网swf格式视频解析
				$swfurl = $url;   		//视频地址
				$vidoimg = 'Tpl/image/article_ifeng.jpg';//swfurl 视频背景图片 凤凰网默认视频背景
				$html = "<embed wmode=\"transparent\" src=\"$swfurl\" style=\"z-index:0;\" width=\"580\" height=\"400\" type=\"application/x-shockwave-flash\" flashid=\"[!-- includeswfimg=$vidoimg --][!-- includeswfurl=$swfurl --]\" />";
				return $html;
			} 
			else if($root == 'tv.tv.cn' || preg_match("/cntv/i", $url)) {		/* 5.22 */
				$swfurl = isset($videoInfo['swfurl']) ? $videoInfo['swfurl'] : '';   		//视频地址
				$vidoimg = isset($videoInfo['imageurl']) ? $videoInfo['imageurl'] : 'Tpl/image/article_simg.jpg';   	//swfurl 视频背景图片    includeflash 是否包含视频
				if($swfurl && $vidoimg) {
					$html = "<iframe width=\"580\" style=\"z-index:0;\" height=\"400\" frameborder=\"1\" src=\"$swfurl\"></iframe>";
					return $html; exit;
				} 
				else {
					return "11"; exit;
				}
			} 
			else if($root == 'iqiyi.com') {
				$swfurl = isset($videoInfo['swfurl']) ? $videoInfo['swfurl'] : '';
				$vidoimg = isset($videoInfo['imageurl']) ? $videoInfo['imageurl'] : 'Tpl/image/article_simg.jpg';
				if($swfurl && $vidoimg) {
					$html = "<div style=\"width:580px; height:400px;border:#000 solid 1px;\"><embed wmode=\"opaque\" src=\"$swfurl\" style=\"z-index:0;\" width=\"580\" height=\"400\" type=\"application/x-shockwave-flash\" flashid=\"[!-- includeswfimg=$vidoimg --][!-- includeswfurl=$swfurl --]\" /></div>";
					return $html; exit;
				} else {
					return "11"; exit;
				}
			}else if($root == '163.com'){
				$sid = isset($videoInfo['sid']) ? $videoInfo['sid'] : '';
				$vid = isset($videoInfo['vid']) ? $videoInfo['vid'] : '';
				$topicid = isset($videoInfo['topicid']) ? $videoInfo['topicid'] : '';
				
				$vidoimg = isset($videoInfo['pic']) ? $videoInfo['pic'] : 'Tpl/image/article_simg.jpg';
				if($sid && $vid && $vidoimg && $topicid) {
					$html = "
						<object width=\"100%\" height=\"100%\" id=\"FPlayer\" data=\"http://v.163.com/swf/video/NetEaseFlvPlayerV3.swf\" type=\"application/x-shockwave-flash\">

							<param name=\"bgcolor\" value=\"#000000\">
							<param name=\"allowFullScreen\" value=\"true\">
							<param name=\"allowscriptaccess\" value=\"always\">
							<param name=\"allownetworking\" value=\"all\">
							<param name=\"wmode\" value=\"opaque\">
							<param name=\"flashvars\" value=\"topicid=".$topicid."&amp;sid=".$sid."&amp;vid=".$vid."&amp;includeswfimg=".$vidoimg."\">
							
						</object>
					";
					return $html; exit;
				} 
				else {
					return "11"; exit;
				}
			}else {
				$swfurl = isset($videoInfo['swfurl']) ? $videoInfo['swfurl'] : '';   		//视频地址
				$vidoimg = isset($videoInfo['imageurl']) ? $videoInfo['imageurl'] : 'Tpl/image/article_simg.jpg';   	//swfurl 视频背景图片    includeflash 是否包含视频
				$iid = isset($videoInfo['iid']) ? $videoInfo['iid'] : 0;
				$mp4 = isset($videoInfo['mp4']) ? $videoInfo['mp4'] : '';
				if($swfurl && $vidoimg) {
					$html = "<embed src=\"$swfurl\"  wmode=\"transparent\" style=\"z-index:0;\" width=\"580\" height=\"400\" type=\"application/x-shockwave-flash\" iids=\"$iid\" mp4url=\"$mp4\" flashid=\"[!-- includeswfimg=$vidoimg --][!-- includeswfurl=$swfurl --]\" />";
					return $html; exit;
				}
				else {
					return "11"; exit;
				}
			}
		} elseif(in_array($root,$vidonet) && $ext == 'swf' && $root == 'sina.com.cn') {	// 解析新浪 swf url
			$vidoimg = 'Tpl/image/article_simg.jpg'; 	//默认封面
			$html = "<embed wmode=\"transparent\" src=\"$url\" style=\"z-index:0;\" width=\"580\" height=\"400\" type=\"application/x-shockwave-flash\" flashid=\"[!-- includeswfimg=$vidoimg --][!-- includeswfurl=$url --]\" />";
			return $html; exit;
		} elseif(in_array($root,$vidonet) && $ext == 'swf' && $root != 'sina.com.cn') {		// 解析其他视频网站的 swf url
			$video = new Video($url);
			$videoInfo = $video->VideGet();
			$swfurl = isset($videoInfo['swfurl']) ? $videoInfo['swfurl'] : '';   		//视频地址
			$vidoimg = isset($videoInfo['imageurl']) ? $videoInfo['imageurl'] : 'Tpl/image/article_simg.jpg';   	//swfurl 视频背景图片    includeflash 是否包含视频
			if($swfurl && $vidoimg) {
				$html = "<embed wmode=\"transparent\" src=\"$swfurl\" style=\"z-index:0;\" width=\"580\" height=\"400\" type=\"application/x-shockwave-flash\" flashid=\"[!-- includeswfimg=$vidoimg --][!-- includeswfurl=$swfurl --]\" />";
				return $html; 
				exit;
			} 
			else {
				return "11"; exit;
			}
		} 
		elseif(!in_array($root,$vidonet) && $ext == 'swf') {	
		    // 解析 url不是自定义的视频网站中， 直接写入 swf url
			$vidoimg = 'Tpl/image/article_simg.jpg'; 	//默认封面
			$html = "<embed wmode=\"transparent\" src=\"$url\" style=\"z-index:0;\" width=\"580\" height=\"400\" type=\"application/x-shockwave-flash\" flashid=\"[!-- includeswfimg=$vidoimg --][!-- includeswfurl=$url --]\" />";
			return $html; 
			exit;
		}
		else {
			return "11"; exit;
		}
	}
}
?>