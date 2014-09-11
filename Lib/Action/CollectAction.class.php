<?php
class CollectAction extends CommonAction {
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
	}
	
	//修改域名信息页面
	public function edit(){
		$id = intval( $this->_get('id') );
		$collect = $this->collect->read( $id );
		if ( !empty( $collect ) ) {
			$collect['title'] = $this->collect_match->get_matchlist_by_collect_type( $collect['id'], self::TYPE_TITLE );
			$collect['content'] = $this->collect_match->get_matchlist_by_collect_type( $collect['id'], self::TYPE_CONTENT );
		}
		$this->assign( 'c', $collect );
		$this->display('Tpl/edit.htm');
	}

	//增加采集域名
	//domain域名 alias该域名的别名
	public function add_collect(){
		$domain = trim( $this->_post('domain') );
		$alias = trim( $this->_post('alias') );
		$json = array();

		$match = "/http:\/\/([^\/]*).*/i";
		if ( !substr_count($domain, "http") ) {
			$domain = "http://".$domain;
		}
		preg_match($match, $domain, $out);
		if ( !empty($out) ) {
			$domin = $out[1];
			$c = $this->collect->where('domain="'.$domin.'"')->find();
			if ( $c ) {
				$json['error'] = 1;
				$json['msg'] = '添加失败，该域名已经添加过';
				echo json_encode( $json );
				exit();
			}
			$data = array(
				'domain'   =>  $domain,
				'alias' =>  $alias,
			);
			$res = $this->collect->_create( $data );
			if ( $res ) {
				$json['error'] = 0;
				$json['msg'] = '添加成功';
			}
			else{
				$json['error'] = 1;
				$json['msg'] = '添加失败，请稍后再试';
			}
			echo json_encode( $json );
			exit();
		}
		else{
			$json['error'] = 1;
			$json['msg'] = '添加失败，不是正确的URL';
			echo json_encode( $json );
			exit();
		}
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

	//修改采集域名信息
	public function edit_collect(){
		$id = intval( $this->_post('id') );
		$domain = trim( $this->_post('domain') );
		$alias = trim( $this->_post('alias') );

		$res = $this->collect->update( $id, array('domain'=>$domain, 'alias'=>$alias) );
		$json = array();
		if ( $res ) {
			$json['error'] = 0;
			$json['msg'] = '修改成功';
		}
		else{
			$json['error'] = 1;
			$json['msg'] = '修改失败，请稍后再试';
		}
		echo json_encode( $json );
	}

	//为某一域名增加采集规则
	//type 0：标题采集规则  1:内容采集规则
	public function add_match(){
		$cid = intval( $this->_post('cid') );
		$match = trim( $this->_post('match') );
		$pos = trim( $this->_post('pos') );
		$type = intval( $this->_post('type') );
		$data = array(
					'cid'   =>  $cid,
					'match' =>  $match,
					'pos'   =>  $pos,
					'type'  =>  $type
			);
		$res = $this->collect_match->_create( $data );
		$json = array();
		if ( $res ) {
			$json['error'] = 0;
			$json['msg'] = '添加成功';
		}
		else{
			$json['error'] = 1;
			$json['msg'] = '添加失败，请稍后再试';
		}
		echo json_encode( $json );
	}
	
	//删除某一匹配规则
	public function delete_match(){
		$mid = intval( $this->_post('mid') );
		$res = $this->collect_match->_delete( $mid );
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

	//修改某一匹配规则
	public function edit_match(){
		$id = intval( $this->_post('id') );
		$match = trim( $this->_post('match') );
		$pos = trim( $this->_post('pos') );
		
		$res = $this->collect_match->update( $id, array('match'=>$match, 'pos'=>$pos) );
		$json = array();
		if ( $res ) {
			$json['error'] = 0;
			$json['msg'] = '修改成功';
		}
		else{
			$json['error'] = 1;
			$json['msg'] = '修改失败，请稍后再试';
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
		//不含视频，则按文章处理
		$collect = D('collect');
		$domin = '';
		$match = "/http:\/\/([^\/]*).*/i";
		if ( !substr_count($url, "http") ) {
			$url = "http://".$url;
		}
		preg_match($match, $url, $out);
		$domin = $out[1];
		if ( !empty($domin) ) {
			//分析是不是音乐网站
			$music_websites = C('MUSIC_WEBSITES');
			if ( in_array($domin, $music_websites) ) {
				$htm = file_get_html( $url );
				$p = preg_match('/var\s*?_xiamitoken\s*?=\s*?[\'\"](.*?)[\'\"]/i', $htm, $out);
				$token = $out[1];
				//onclick="playalbum(682938274, '', '时间的歌', '');
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
				if ( $xid ) {
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
					'match' => '123',
				))->add();
				//查找body
				$htm = file_get_html( $url );
				$title = $htm->find('title',0)->plaintext;

				$content = $htm->find('body',0)->innertext;
				//title取正文的10个左右字符
				$res['title'] = $title;
				$res['content'] = $content;
			}
			else{
				//找到了匹配规则
				//新浪博客URL特殊处理，去掉结尾的 ?tj=...
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
					$exec = '$htm';
					foreach ($matchlist as $match) {
					  $exec .= "->find( '$match[match]', $match[pos] )"; 
					}
					$exec = $exec.'->innertext;';
					eval("\$str = $exec;");
					$res['content'] = $str;
				}
				else{
					$content = $htm->find('body',0)->innertext;
					$res['content'] = $content;
				}
				//某些网站图片特殊处理
				if (  in_array($domin, array('history.people.com.cn') ) ) {
					$res['content'] = preg_replace("/src=\"(.*?)\"/i", 'src="http://'.$domin."$1".'"', $res['content']);
				}
				else if ( $domin == 'blog.sina.com.cn' ) {
					//新浪图片，需要把real_src和src属性互相特换 
					// 新建一个Dom实例
					$new_html = new simple_html_dom();
					$new_html->load( $res['content'] );
					$imgs = $new_html->find('img');
					foreach ($imgs as &$img) {
						$img->src = $img->real_src;
						$img->real_src = null;
					}
					$res['content'] = $new_html->innertext;
					$new_html->clear();
				}
			}
			//释放内存消耗
			$htm->clear();
		}else{
			$res['title'] = '';
			$res['content'] = '';
			echo json_encode( $res );
			exit();
		}
		//获取内容处理html标签
		$res['content'] = $this->clearhtml( $res['content'] );
		$res['title'] = trim( $this->clearhtml( $res['title'] ) );

		//转码处理
		$no_need_iconv = C('NO_NEED_ICONV');
		if ( !in_array( $domin , $no_need_iconv ) ) {
			if ( $domin == 'history.sina.com.cn'  ) {
				//GBK    编码特殊处理
				$res['title'] = iconv("GBK","UTF-8//IGNORE",$res['title']);
				$res['content'] = iconv("GBK","UTF-8//IGNORE",$res['content']);
			}
			else{
				//GB2312 编码处理
				$res['title'] = iconv("GB2312","UTF-8//IGNORE",$res['title']);
				$res['content'] = iconv("GB2312","UTF-8//IGNORE",$res['content']);
			}
		}
		echo json_encode( $res );
	}

	//解析URL主页 http://localhost/kd/?m=collect
	public function index(){
		$this->display('Tpl/index.html');
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


	public function test(){
		$url = "http://www.xiami.com/res/player/xiamiMusicPlayer_1.6.0.swf?v=20131126181023";
		$html = "<embed width=\"290\" height=\"24\" src=\"Tpl/js/editor/audioplayer.swf?soundFile=".urlencode($url)."&bg=0xCDDFF3&leftbg=0x357DCE&lefticon=0xF2F2F2&rightbg=0x357DCE&rightbghover=0x4499EE&righticon=0xF2F2F2&righticonhover=0xFFFFFF&text=0x357DCE&slider=0x357DCE&track=0xFFFFFF&border=0xFFFFFF&loader=0x8EC2F4&autostart=yes&loop=yes\"></embed>";
		echo $html;
	}

	public function music(){
		$url = trim( $this->_get('url') );
		//返回结果
		$res = array( 'title'=>'', 'content'=>'' );

		//不含视频，则按文章处理
		$collect = D('collect');
		$domain = '';
		$match = "/http:\/\/([^\/]*).*/i";
		if ( !substr_count($url, "http") ) {
			$url = "http://".$url;
		}
		preg_match($match, $url, $out);
		//yuming
		$music_websites = C('MUSIC_WEBSITES');
		$domain = $out[1];
	
		// echo $domain;
		// $str = "var _xiamitoken = '0802020a13ba3df687e7ca4ef45cf1a8';";
		// preg_match("/^var\s*?_xiamitoken\s*?=\s*?[\'\"](.*?)[\'\"]/", $str, $out);
		// print_r( $out );
		if ( in_array($domain, $music_websites) ) {
			$htm = file_get_html( $url );
			$p = preg_match('/var\s*?_xiamitoken\s*?=\s*?[\'\"](.*?)[\'\"]/i', $htm, $out);
			print_r( $out );
			$token = $out[1];
			echo "<br>";
			//onclick="playalbum(682938274, '', '时间的歌', '');
			if ( preg_match('/playalbum\((\\d+),\s*?\'*?\',\s*?\'(.*?)\',\s*?\'*?\'\)/i', $htm, $out) ) {
				//xid
				$xid = $out[1];
				//title
				$title = $out[2];
			}else if( preg_match('/\/album\/(\d{1,})/', $htm, $out) ){
				$xid = $out[1];
				$title = $htm->find('div#title',0)->innertext;
			}
			//http://www.xiami.com/ajax/getquote/type/2/id/682938274?_xiamitoken=0802020a13ba3df687e7ca4ef45cf1a8
			$zurl = "http://www.xiami.com/ajax/getquote/type/2/id/$xid?_xiamitoken=$token";
			echo $zurl;
			echo "<br>";
			$htm = file_get_html( $zurl );
			$res = $htm->find('textarea.tarea',1)->innertext;
			echo "<br>";
			echo $res;
		}
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
		if(in_array($root,$vidonet) && $ext != 'swf'){		// 解析所有在数组中的不是以swf url 的视频
			$video = new Video($url);
			$videoInfo = $video->VideGet();
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


	// 音频上传,暂时不做
	private function uMusicUpload( $url ) {
		$url = stripslashes( htmlspecialchars_decode( $url ) );
		$mp3 = substr($url, -4, 4);
		if($mp3 == '.mp3' || $mp3 == '.MP3') {
			$html = "<embed width=\"290\" height=\"24\" src=\"Tpl/js/editor/audioplayer.swf?soundFile=".urlencode($url)."&bg=0xCDDFF3&leftbg=0x357DCE&lefticon=0xF2F2F2&rightbg=0x357DCE&rightbghover=0x4499EE&righticon=0xF2F2F2&righticonhover=0xFFFFFF&text=0x357DCE&slider=0x357DCE&track=0xFFFFFF&border=0xFFFFFF&loader=0x8EC2F4&autostart=yes&loop=yes\"></embed>";
			return $html; exit;
		} else {
			$substr = substr($url, 0, 6);
			if($substr == 'http:/') {
				$html = "<embed src=\"$url\" style=\"z-index:0;\" type=\"application/x-shockwave-flash\" width=\"257\" height=\"33\" wmode=\"transparent\" musicid=\"[!-- includemusicurl=$url --]\"></embed>";
				return $html; exit;
			} elseif ($substr == '<embed') {
				$reg = "#\<embed[\s\S]*src=['\"](?<swf>.*?)['\"][\s\S\/]*\>#i";	//正则匹配url
				preg_match($reg, $url, $matches);
				$newurl = $matches['swf'];
				$html = "<embed src=\"$newurl\" style=\"z-index:0;\" type=\"application/x-shockwave-flash\" width=\"257\" height=\"33\" wmode=\"transparent\" musicid=\"[!-- includemusicurl=$newurl --]\"></embed>";
				return $html; exit;
			} elseif ($substr == '<scrip') {
				$reg = "#\<script[\s\S]*src=['\"](?<swf>.*?)['\"][\s\S\/]*\>#i";	//正则匹配url
				preg_match($reg, $url, $matches);
				preg_match('#sid=(?<sid>.*?)&#', $matches['swf'], $sid);
				$newurl = 'http://www.xiami.com/widget/0_'.$sid['sid'].'/singlePlayer.swf';
				$html = "<embed src=\"$newurl\" style=\"z-index:0;\" type=\"application/x-shockwave-flash\" width=\"257\" height=\"33\" wmode=\"transparent\" musicid=\"[!-- includemusicurl=$newurl --]\"></embed>";
				return $html; exit;
			} else {
				return 1; exit;
			}
		}
		return $url;
	}

	//测试用的视频接口
	public function video() {
		// 分析URL
		$url = $this->_get('url');
		$check = preg_match('#http://#i', $url, $f);  //url已http://开头  i 忽略大小写
		if(empty($check)) {
		echo "10"; exit;
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
		}elseif(preg_match("/(?:com|tel|mobi|net|org|asia|me|tv|biz|cc|name|info|cn)(?:\.\w{2})$/", $host, $match)){
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
			if(!empty($check)){
			if(in_array($root,$vidonet) && $ext != 'swf'){		// 解析所有在数组中的不是以swf url 的视频
				$video = new Video($url);
				$videoInfo = $video->VideGet();
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
						echo $html; exit;
						} else {
							echo "11"; exit;
						}
			} 
			else if($root == 'iqiyi.com') {
				$swfurl = isset($videoInfo['swfurl']) ? $videoInfo['swfurl'] : '';
				$vidoimg = isset($videoInfo['imageurl']) ? $videoInfo['imageurl'] : 'Tpl/image/article_simg.jpg';
				if($swfurl && $vidoimg) {
					$html = "<div style=\"width:580px; height:400px;border:#000 solid 1px;\"><embed wmode=\"opaque\" src=\"$swfurl\" style=\"z-index:0;\" width=\"580\" height=\"400\" type=\"application/x-shockwave-flash\" flashid=\"[!-- includeswfimg=$vidoimg --][!-- includeswfurl=$swfurl --]\" /></div>";
					echo $html; exit;
				} else {
					echo "11"; exit;
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
					echo $html; exit;
				} else {
					echo "11"; exit;
				}
			
			} else {
				$swfurl = isset($videoInfo['swfurl']) ? $videoInfo['swfurl'] : '';   		//视频地址
				$vidoimg = isset($videoInfo['imageurl']) ? $videoInfo['imageurl'] : 'Tpl/image/article_simg.jpg';   	//swfurl 视频背景图片    includeflash 是否包含视频
				$iid = isset($videoInfo['iid']) ? $videoInfo['iid'] : 0;
				$mp4 = isset($videoInfo['mp4']) ? $videoInfo['mp4'] : '';
				if($swfurl && $vidoimg) {
					$html = "<embed src=\"$swfurl\"  wmode=\"transparent\" style=\"z-index:0;\" width=\"580\" height=\"400\" type=\"application/x-shockwave-flash\" iids=\"$iid\" mp4url=\"$mp4\" flashid=\"[!-- includeswfimg=$vidoimg --][!-- includeswfurl=$swfurl --]\" />";
					echo $html; exit;
				} else {
					echo "11"; exit;
				}
			}
			} elseif(in_array($root,$vidonet) && $ext == 'swf' && $root == 'sina.com.cn') {	// 解析新浪 swf url
			$vidoimg = 'Tpl/image/article_simg.jpg'; 	//默认封面
			$html = "<embed wmode=\"transparent\" src=\"$url\" style=\"z-index:0;\" width=\"580\" height=\"400\" type=\"application/x-shockwave-flash\" flashid=\"[!-- includeswfimg=$vidoimg --][!-- includeswfurl=$url --]\" />";
			echo $html; exit;
			} elseif(in_array($root,$vidonet) && $ext == 'swf' && $root != 'sina.com.cn') {		// 解析其他视频网站的 swf url
			$video = new Video($url);
			$videoInfo = $video->VideGet();
			$swfurl = isset($videoInfo['swfurl']) ? $videoInfo['swfurl'] : '';   		//视频地址
			$vidoimg = isset($videoInfo['imageurl']) ? $videoInfo['imageurl'] : 'Tpl/image/article_simg.jpg';   	//swfurl 视频背景图片    includeflash 是否包含视频
			if($swfurl && $vidoimg) {
				$html = "<embed wmode=\"transparent\" src=\"$swfurl\" style=\"z-index:0;\" width=\"580\" height=\"400\" type=\"application/x-shockwave-flash\" flashid=\"[!-- includeswfimg=$vidoimg --][!-- includeswfurl=$swfurl --]\" />";
				echo $html; exit;
			} else {
				echo "11"; exit;
			}
		} elseif(!in_array($root,$vidonet) && $ext == 'swf') {	// 解析 url不是自定义的视频网站中， 直接写入 swf url
			$vidoimg = 'Tpl/image/article_simg.jpg'; 	//默认封面
			$html = "<embed wmode=\"transparent\" src=\"$url\" style=\"z-index:0;\" width=\"580\" height=\"400\" type=\"application/x-shockwave-flash\" flashid=\"[!-- includeswfimg=$vidoimg --][!-- includeswfurl=$url --]\" />";
			echo $html; exit;
		} else {
			echo "11"; exit;
		}
		} else {
			echo "11"; exit;
		}
	}
}	
?>