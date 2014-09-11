<?php
/*******************************
* 依据传入的视频地址获取视频缩略图 视频名称
* 传入SWF地址支持 优酷，土豆网，Ku6，56，激动网的视频
* 传入HTML地址支持 优酷，土豆网，Ku6，激动网，56的视频
* 使用方法
* $video = new UCLibVideoInfoAcquisition($videourl);
* $videoInfo = $video->VideGet();
* @string $videourl: 视频的URL地址
* 返回信息
* array('flashvar'=>视频编码,'title'=>视频标题,'imageurl'=>视频图片地址,'swfurl'=>视频地址);
* -----------------------------
* Author <dingjunhua@snda.com>
*********************************/
class Video{
	var $link,$host,$videInfo,$swf;
	var $linkarray = array('youku.com', '56.com', 'ku6.com', 'tudou.com', 'joy.cn', 'sina.com.cn', 'ifeng.com', 'qq.com', 'sohu.com', 'iqiyi.com', 'qiyi.com', 'tv.tv.cn', 'baidu.com', 'iqiyi', 'wasu.cn', '163.com');
	
	//public $test_link;

	function __construct($link) {
		if(empty($link)){
			return false;
		}
		$link = trim($link);
		$turl = parse_url($link);
		$host = $turl['host'];
		$h = explode('.', $host);
		if(false === strpos($host, '.') || preg_match("/^(\d+\.){3}(\d+)$/", $host)){
			$root = $host;
		}elseif(preg_match("/(?:com|tel|mobi|net|org|asia|me|tv|biz|cc|name|info|cn)(?:\.\w{2})$/", $host, $match)){
			array_pop($h);		// 新浪视频在这里执行
			array_pop($h);
			$root = array_pop($h) . '.' . $match[0];
		}elseif(preg_match("/(?:\w{2,4})$/", $host, $match)){
			array_pop($h);
			$root = array_pop($h) . '.' . $match[0];
		}
		if(!in_array($root,$this->linkarray)){
			return false;
		}
		$this->host = $root;
		$this->link = $link;
	}
	

	//判断传入的数据是HTML还是SWF
	function VideGet(){
		$str = pathinfo($this->link);

		if(!empty($str['extension'])) {
			$str = explode("?",$str['extension']);
			$str = $str[0];
			$this->str = $str;	// 凤凰网视频 shtml#01qis-...用到
		}
		if($str == 'swf' || ($str != 'htm' && $str != 'html' && $str != 'do' && $str != 'shtml')){
			$this->SwfToHtml();
		}
		$info = $this->getVideoInfo();
		return $info;
	}

	//依据SWF的地址获取HTML地址信息
	function SwfToHtml(){
		if('youku.com' == $this->host) {
			//$pieces = explode("/", $this->link);
			preg_match("/sid\/(.*?)\/v.swf/i", $this->link, $pieces);			/* @mar 2013-06-24 */
			$this->link = 'http://v.youku.com/v_show/id_'.$pieces[1].'.html';
		}elseif('ku6.com' == $this->host){
			$pieces = explode("/", $this->link);
			$this->link = 'http://v.ku6.com/show/'.$pieces[4].'.html';
		}elseif('tudou.com' == $this->host){
			$pieces = explode("/", $this->link);
			if($pieces[3] == 'l'){		// listplay
				// $Location = $this->curlLocation($this->link);
				//preg_match("/lid=(\d{1,20})/i",  $Location, $url);
				preg_match("/lid=(\d{1,20})/i",  $this->link, $url);
				$this->link = 'http://www.tudou.com/playlist/playindex.do?lid='.$url[1];
			} elseif ($pieces[3] == 'a') {	// albumplay    todo
				
			} else{
				if(strlen($pieces[4]) < 8){
					$flashvar = $pieces[5];
				}else{
					$flashvar = $pieces[4];
				}
				$this->link = 'http://www.tudou.com/programs/view/'.$flashvar.'/';
			}
		}elseif('56.com' == $this->host){
			preg_match_all("/\/([\w\-]+)\.swf/", $this->link, $matches);
			$pieces = explode("_", $matches[1][0]);
			$this->link = 'http://www.56.com/u85/v_'.$pieces[1].'.html';
		}elseif('joy.cn' == $this->host){
			preg_match_all("/\/([\w\-]+)\.swf/", $this->link, $matches);
			$pieces = explode("_", $matches[1][0]);
			if($pieces[0] == '51com'){
				$this->link = 'http://news.joy.cn/video/'.$pieces[1].'.htm';
			}elseif($pieces[0] == '51comvodsp'){
				$this->link = 'http://news.joy.cn/video/'.$pieces[4].'.htm';
			}else{
				$this->link = 'http://news.joy.cn/video/'.$pieces[0].'.htm';
			}
		} elseif('ifeng.com' == $this->host) {
			// 分割shtml#url过来的地址
			$shtml = explode('#', $this->link);
			if(is_array($shtml)) {	// #格式 url = 'http://v.ifeng.com/v/20111006/index.shtml#e7550d32-10e2-469e-869a-e08796308c9e'
				$text= @file_get_contents($this->link);
				$preg_match = "/value=['\"](.*?)".$shtml[1]."\.shtml/";
				preg_match($preg_match, $text, $flashinfo);
				if(!empty($flashinfo[1])) {
					$this->link = $flashinfo[1].$shtml[1].'.shtml';
				}		
			} else {
				preg_match_all("/swf\?guid=(.*?)\&/", $this->link, $matches); // swf 格式转 html 暂时反解析不成功
				$this->swf = 'ifeng.swf';
			}
			
		} else if('qq.com' == $this->host) {
			$shtml = isset( $this->str ) ? $this->str : '';
			$this->swf = 'TPout.swf';
		}
	}

	//依据HTML地址获取视频信息
	function getVideoInfo(){
		$return = array();
		if('youku.com' == $this->host) {
			//分析视频网址，获取视频编码号
			preg_match_all("/id\_(\w+)[\=|.html]/", $this->link, $matches);
			if(!empty($matches[1][0])) {
				$return['flashvar'] = $matches[1][0];
			}
			//获取视频页面内容，存与$text中
			$text= @file_get_contents($this->link);
			if(!$text){
				$return['flashvar'] = '';
				return $return;
			}
			$text = str_replace("\n", "", $text);
			//获取视频标题
			//preg_match("/<title>(.*?) - (.*)<\/title>/i",  $text, $title);
			//获取优酷网上某一视频对应的视频截图，经分析，视频的截图的图片地址在该视频页面html代码里以<li class="download"></li>标记里的最后一个http://vimg....例如http://vimg20.yoqoo.com/0100641F4649B9D27344B00131FBB6AFDF5175-7D35-930B-E43C-99C59F918E00
			//preg_match_all("/<li class=\"download\">(.*?)<\/li>/i",$text,$match2);
			//preg_match_all("/<li class=\"v_thumb\">(.*?)<\/li>/i",$text,$match2);
			//$pieces = explode("|", $match2[1][0]);
			//preg_match("/http:\/\/(.*)\"\>/", $match2[1][0], $imageurl);
			//if (!empty($imageurl[1])) {
			//	$return['imageurl'] = 'http://'.$imageurl[1];
			//}
			//if(!empty($pieces[8])){
			//	$return['imageurl'] = $pieces[8];
			//}
			//if (!empty($title)) {
			//	$return['title'] = $title[1];
			//}
			$return['swfurl'] = 'http://player.youku.com/player.php/sid/'.$return['flashvar'].'/v.swf';
		} elseif('ku6.com' == $this->host) {
			// http://v.ku6.com/show/bjbJKPEex097wVtC.html
			// http://v.ku6.com/special/index_3628020.html
			//对于酷6网，末尾以index_开头的地址需要另外分析其视频编码
			$text= @file_get_contents($this->link);
			if(!$text){
				$return['flashvar'] = '';
				return $return;
			}
			$text = str_replace("\n", "", $text);
			preg_match_all("/\/([\w\-]+)\.html/", $this->link, $matches);
			
			if(1 > preg_match("/\/index_([\w\-]+)\.html/", $this->link) && !empty($matches[1][0])) {
				$return['flashvar'] = $matches[1][0];
			}else{
				preg_match_all("/\/refer\/(.*?)\/v.swf/",$text,$videourl);
				$return['flashvar'] = $videourl[1][0];
			}
			preg_match("/<title>(.*?) - (.*)<\/title>/i",  $text, $title);
			//经分析，酷六的视频截图地址在视频页面的<span class="s_pic“></span>标签之间
			//preg_match_all("/<span class=\"s_pic\">(.*?)<\/span>/",$text,$imageurl);
			preg_match_all("/cover: \"(.*?)\",/",$text,$imageurl);
			if (!empty($imageurl[1][0])) {
				$return['imageurl'] = $imageurl[1][0];
			}
			if (!empty($title)) {
				$return['title'] = $title[1];
			}
			$return['swfurl'] = 'http://player.ku6.com/refer/'.$return['flashvar'].'/v.swf';
		}elseif ('tudou.com' == $this->host){
			//分析视频网址，获取视频编码号
			preg_match_all("/view\/([\w\-]+)\//", $this->link, $matches);
			//获取视频页面内容，存与$text中
			$text= @file_get_contents($this->link);
			if(!empty($matches[1][0])) {
				$return['flashvar'] = $matches[1][0];
				//方法二			
				if(empty($return['flashvar'])){
					preg_match("#icode\s=\s\'([\w\-]+)\'#i",$text,$flashvar);
					$return['flashvar'] = $flashvar[1][0];
				}
				preg_match("/<title>(.*?)_(.*)<\/title>/i",  $text, $title);
				if (!empty($title)) {
					$return['title'] = $title[1];
				}
				//preg_match_all("/<span class=\"s_pic\">(.*?)<\/span>/i",$text,$imageurl);
				preg_match_all("#pic\s=\s\'(.*?)\'#i",$text,$imageurl);
				if (!empty($imageurl[1][0])) {
					$return['imageurl'] = $imageurl[1][0];
				}
				//$return['swfurl'] = "http://www.tudou.com/v/".$return['flashvar'].'/&resourceId=0_05_05_99&bid=05/v.swf';
			} else {
				preg_match_all("#listplay\/([\w\-\/]+)\.#", $this->link, $matches);
				if(!empty($matches[1][0])) {
					preg_match("#\/([\w\-]+)#", $matches[1][0], $listpaly);
					$return['flashvar'] = $listpaly[1];
					preg_match_all("#icode:\"$listpaly[1]\"(.*?)\};#ism", $text, $matches1);
					preg_match("#pic:\"(.*?)\"#i",$matches1[1][0], $imgurl);
					$return['imageurl'] = $imgurl[1];
					$return['title'] = '';	//默认为空
				}
			}
			if(!empty($imgurl[1])) {
				$iurl = explode('/', $imgurl[1]);
				if(!empty($iurl[4]) && !empty($iurl[5])) {
					$return['iid'] = $iurl[3].$iurl[4].$iurl[5];
				}
			} else {
				preg_match("/iid:(.*?)\s/i", $text, $iids);
				$return['iid'] = $iids[1];
			}
			if(!empty($return['flashvar'])) {
				$return['swfurl'] = "http://www.tudou.com/v/".$return['flashvar'].'/&resourceId=0_05_05_99&bid=05/v.swf';
			}else{
				//swfurl为空时,电视剧可能会造成这种情况
				$exp = explode('/', $this->link);
				$exp_last = !empty($exp[5]) ? $exp[5] : (!empty($exp[4]) ? $exp[4] : '');
				if(!empty($exp_last)){
					$vid = explode(".", $exp_last);
					if($vid[0]){
						$return['swfurl'] = "http://www.tudou.com/v/".$vid[0].'/&resourceId=0_05_05_99&bid=05/v.swf';
						$return['flashvar'] = $vid[0];
					}
				}else{
					echo '分析失败';exit;
				}
			}
		}elseif('56.com' == $this->host){
			//分析视频网址，获取视频编码号
			preg_match_all("/v\_(\w+)[\=|.html]/", $this->link, $matches);
			if(!empty($matches[1][0])) {
				$return['flashvar'] = $matches[1][0];
			}
			//获取视频页面内容，存与$text中
			$text= @file_get_contents($this->link);
			if(!$text){
				$return['flashvar'] = '';
				return $return;
			}
			$text = str_replace("\n", "", $text);
			//获取视频标题
			preg_match("/<title>(.*?) - (.*)<\/title>/i",  $text, $title);
			//preg_match_all("/\"img\":\"(.*?)\"\};/i",$text,$match2); //提取不到封面图
			//if(!empty($match2[1])){
			//	$return['imageurl'] = stripslashes($match2[1][0]);
			//}
			if (!empty($title)) {
				$return['title'] = $title[1];
			}
			$return['imageurl'] = 'Tpl/image/article_simg.jpg';	// 默认封面图
			$return['swfurl'] = 'http://player.56.com/v_'.$return['flashvar'].'.swf';
		}elseif('joy.cn' == $this->host){
			//分析视频网址，获取视频编码号
			preg_match_all("/\/([\w\-]+)\.htm/", $this->link, $matches);
			if(!empty($matches[1][0])) {
				$return['flashvar'] = $matches[1][0];
			}
			//获取视频页面内容，存与$text中
			$text= @file_get_contents($this->link);
			if(!$text){
				$return['flashvar'] = '';
				return $return;
			}
			$text = str_replace("\n", "", $text);
			//获取视频标题
			preg_match("/<title>(.*?)-(.*)<\/title>/i",  $text, $title);
			preg_match_all("/Cover:\"(.*?)\",/i",$text,$match2); //提取
			if(!empty($match2[1])){
				$return['imageurl'] = stripslashes($match2[1][0]);
			}
			if (!empty($title)) {
				$return['title'] = $title[1];
			}
			$return['swfurl'] = 'http://client.joy.cn/flvplayer/'.$return['flashvar'].'_1_0_1.swf';
		} elseif('sina.com.cn' == $this->host) {
			//获取视频页面内容，存与$text中
			$text= @file_get_contents($this->link);
			$return['flashvar'] = '';
			preg_match("/pic\: \'(.*?)\'/i",$text,$imageurl);	// 正则匹配图片
			$return['imageurl'] = empty( $imageurl[1] )?null : $imageurl[1] ;
			
			preg_match("/swfOutsideUrl:\'(.*?)\'/i",$text,$flashvar);
			$return['swfurl'] = empty($flashvar[1])?null : $flashvar[1];
			
			//preg_match("/title:\'(.*?)\'/i",$text,$title);
			//$return['title'] = $title[1];
		} elseif('ifeng.com' == $this->host) {
			if(!empty($this->swf) && $this->swf == 'ifeng.swf') {
				$return['flashvar'] = 'ifeng.swf';
				return $return;
			} else {
				$text= @file_get_contents($this->link);
				
				preg_match_all("/\"id\": \"(.*?)\"/i", $text, $id);
				if(empty($id[1][0])) {
					preg_match_all("/vid=\"(.*?)\"/i", $text, $id);
				}
				
				preg_match_all("/\"img\": \"(.*?)\"/i", $text, $img);
				if(empty($img[1][0])) {
					$img[1][0] = "Tpl/image/article_ifeng.jpg";
				}
				
				$return['flashvar'] = isset( $id[1][0] ) ? $id[1][0] : '';
				$return['imageurl'] = isset(  $img[1][0] ) ?  $img[1][0] : '';
				$return['swfid'] 	= isset( $id[1][0] ) ? $id[1][0] : '';
				if ( $return['swfid'] ) {
					$return['swfurl'] 	= 'http://v.ifeng.com/include/exterior.swf?guid='.$return['swfid'].'&AutoPlay=false';
				}else{
					$return['swfurl'] = '';
				}
			}
		} else if('qq.com' == $this->host) {
			// 如果是直接拷贝的swf播放器地址 http://static.video.qq.com/TPout.swf?auto=1&vid=v0011afoaza
			if($this->swf != 'TPout.swf') {
				// 短小视频(vid=)与电影视频(null) 通过url区分(正常情况)，电影视频内容页 vid:"v0011afoaza", title:"xxxxx",
				if(preg_match("/vid=(.*)/i", $this->link)) {
					preg_match("/vid=(.*)/i", $this->link, $vids);
					if(!empty($vids[1])) {
						$return['flashvar'] = 'http://static.video.qq.com/TPout.swf?auto=1&vid='.$vids[1];
						$return['imageurl'] = 'http://vpic.video.qq.com//'.$vids[1].'_160_90_1.jpg';
						$return['swfurl'] = 'http://static.video.qq.com/TPout.swf?auto=1&vid='.$vids[1];
					} else {
						echo '分析失败'; exit;
					}
				} else {
					$text = @file_get_contents($this->link);
					if(empty($text)) {	return null; exit; }
					preg_match_all("/vid=(.*)/i", $this->link, $urlinfo);
					if(!empty($urlinfo) && !empty($urlinfo[1])) {
						preg_match_all("/<li\sid=['\"]li_".$urlinfo[1][0]."['\"]\s[\S]*>(.*?)<\/li>/ism", $text, $qqinfo);
						/*if(!empty($qqinfo) && !empty($qqinfo[1][0])) {
							preg_match_all("/_src=['\"](.*)['\"]/i", $qqinfo[1][0], $imageurl);	// 封面图
							$return['flashvar'] = $urlinfo[1][0];
							$return['imageurl'] = $imageurl[1][0];
							$return['swfurl'] = 'http://static.video.qq.com/TPout.swf?auto=1&vid='.$urlinfo[1][0];
						}*/
						/* @mar 2013-07-18 */
						$return['flashvar'] = 'http://static.video.qq.com/TPout.swf?auto=1&vid='.$urlinfo[1][0];
						$return['imageurl'] = 'http://vpic.video.qq.com//'.$urlinfo[1][0].'_160_90_1.jpg';
						$return['swfurl'] = 'http://static.video.qq.com/TPout.swf?auto=1&vid='.$urlinfo[1][0];
						
					} else {	// 电影视频部分 (正常情况)
						preg_match_all("/pic\s:['\"](.*?)['\"]/i", $text, $imageurl);
						preg_match_all("/vid\s*:['\"](.*?)['\"]/i", $text, $vid);
						$return['flashvar'] = isset( $vid[1][0] ) ? $vid[1][0] : '';
						$return['imageurl'] = isset($imageurl[1][0])?$imageurl[1][0]:'';
						$temp_swfurl = isset( $vid[1][0] ) ? $vid[1][0] : '';
						$return['swfurl'] = 'http://static.video.qq.com/TPout.swf?auto=1&vid='.$temp_swfurl;
					}
				}
			} else {
				$return['flashvar'] = '';
				$return['imageurl'] = 'Tpl/image/article_simg.jpg';	// 默认封面图
				$return['swfurl'] = '';
			}
		} elseif ('sohu.com' == $this->host) {
			// 如果是flash地址 http://share.vrs.sohu.com/995090/v.swf&topBar=1&autoplay=false&plid=5168220&pub_catecode= 直接返回去
			$text = @file_get_contents($this->link);
			if(substr($this->link, 0, 25) == 'http://share.vrs.sohu.com') {
				$return['flashvar'] = '';
				$return['imageurl'] = 'Tpl/image/article_simg.jpg';	// 默认封面图
				$return['swfurl'] = $this->link;
			} elseif(substr($this->link, 0, 21) == 'http://my.tv.sohu.com') {
				// 获取源码 含有videoSrc是视频地址 原创
				preg_match_all("/videoSrc\s*:\s['\"](.*?)['\"]/i", $text, $vid);
				preg_match_all("/sCover\s*:\s['\"](.*?)['\"]/i", $text, $imageurl);
				$return['flashvar'] = $vid[1][0];
				$return['imageurl'] = $imageurl[1][0];
				$return['swfurl'] = $vid[1][0];
			} else { // 正常视频分享地址
				preg_match_all("/vid\s*=\s*['\"](.*?)['\"]/i", $text, $vid);
				preg_match_all("/playlistId\s*=\s*['\"](.*?)['\"]/i", $text, $playlistId);
				$return['flashvar'] = '';
				$return['imageurl'] = 'Tpl/image/article_simg.jpg';
				$return['swfurl'] = 'http://share.vrs.sohu.com/'.$vid[1][0].'/v.swf&topBar=1&autoplay=false&plid='.$playlistId[1][0].'&pub_catecode=';
			}
		} elseif ('iqiyi.com' == $this->host || 'qiyi.com' == $this->host) {
			// 如果是flash地址 http://player.video.qiyi.com/7bab2ee009a3406f86ceb68fb489c69e/0/79/life/20130306/d264f883f4587529.swf-pid=0-ptype=2-albumId=341370-tvId=413520 直接返回
			if(substr($this->link, 0, 28) == 'http://player.video.qiyi.com') {
				$return['swfurl'] = $this->link;
			} elseif (substr($this->link, 0, 6) == '<embed') {	// html代码 <embed src="..." ></embed>   todo:待分析
				preg_match_all("/<embed\s*src=['\"](.*?)['\"]/i", $text, $vid);
				$return['swfurl'] = $vid[1][0];
			} elseif (substr($this->link, 0, 20) == 'http://www.iqiyi.com') {
				/*$text = @file_get_contents($this->link);
				// 分析url  正常地址   http://www.iqiyi.com/dianshiju/20130106/cd7b26578b8753f1.html
				$urlarr = explode('/', $this->link);
				$vid = explode('.', $urlarr[5]);
				preg_match_all("/\"videoId\":['\"](.*?)['\"]/i", $text, $videoId);
				preg_match_all("/\"pid\":['\"](.*?)['\"]/i", $text, $pid);
				preg_match_all("/\"ptype\":['\"](.*?)['\"]/i", $text, $ptype);
				preg_match_all("/\"albumId\":['\"](.*?)['\"]/i", $text, $albumId);
				preg_match_all("/\"tvId\":['\"](.*?)['\"]/i", $text, $tvId);
				preg_match_all("/\"playTime\":['\"](.*?)['\"]/i", $text, $playTime);
				$timestring = explode(':', $playTime[1][0]);	// 时间转换成字符串(秒)  00:46:23  => 00*60*60 + 60*46 + 23
				$second = $timestring[0]*60*60 + $timestring[1]*60 + $timestring[2];
				$return['swfurl'] = 'http://player.video.qiyi.com/'.$videoId[1][0].'/0/'.$second.'/'.$urlarr[3].'/'.$urlarr[4].'/'.$vid[0].'.swf-pid='.$pid[1][0].'-ptype='.$ptype[1][0].'-albumId='.$albumId[1][0].'-tvId='.$tvId[1][0];
				*/
				$text = @file_get_contents($this->link);
				preg_match_all("/ata-player-videoid=\"(.*?)\"/i", $text, $vid);
				$return['swfurl'] = 'http://www.iqiyi.com/player/20130513112511/Player.swf?vid='.$vid[1][0].'&autoplay=true&isMember=false&cyclePlay=false&share_sTime=%30&share_eTime=%30';
			} else {
				return null;exit;
			}
			$return['flashvar'] = '';
			$return['imageurl'] = 'Tpl/image/article_simg.jpg';	// 默认封面图
		} elseif('tv.tv.cn' == $this->host) {
			$text = @file_get_contents($this->link);
			$url_arr = explode('/', $this->link);
			$count = count($url_arr);
			if($count > 0) {
				$video_id = $url_arr[$count-1];
			} else {
				preg_match_all("/\"videoCenterId\",\"(.*?)\"/i", $text, $video_id_info);
				$video_id = $video_id_info[1][0];
			}
			if(empty($video_id)) { return null; exit; }
			
			$return['swfurl'] = 'http://player.cntv.cn/standard/cntvplayer5.swf?id='.$video_id;
			$return['flashvar'] = '';
			$return['imageurl'] = 'Tpl/image/article_simg.jpg';					// 默认封面图
			
		} else if('wasu.cn' == $this->host) {
			$text = @file_get_contents($this->link);
			preg_match_all("/_playUrl\s=\s\'(.*?)\'/i", $text, $mp4);
			preg_match_all("/_playId\s=\s\'(.*?)\'/i", $text, $id);
			if(empty($id[1][0])) { return null; exit; }
			
			$return['swfurl'] = "http://play.wasu.cn/player/20130314/WasuPlayer.swf?vid=".$id[1][0]."&ap=0";
			$return['flashvar'] = $return['swfurl'];
			$return['imageurl'] = 'Tpl/image/article_simg.jpg';
			$return['mp4'] = $mp4[1][0];
		} else if('163.com' == $this->host) {
			$text = @file_get_contents($this->link);
			preg_match_all("/_playUrl\s=\s\'(.*?)\'/i", $text, $mp4);
			preg_match_all("/_playId\s=\s\'(.*?)\'/i", $text, $id);
			
			$url_arr = explode('/', $this->link);
			if(!empty($url_arr)) {
				$sid = $url_arr[4];
				$vid_arr = explode('.', $url_arr[5]);
				if(!empty($vid_arr)) {
					$vid = $vid_arr[0];
				} else {
					$vid = '';
				}
				
				$text = @file_get_contents($this->link);
				preg_match_all("/coverpic\s:\s\"(.*?)\",/i", $text, $coverpics);
				if(!empty($coverpics[1][0])) {
					$pic = $coverpics[1][0];
				} else {
					$pic = 'Tpl/image/article_simg.jpg';
				}
				preg_match_all("/topicid\s:\s\"(.*?)\",/i", $text, $topicids);
				if(!empty($topicids[1][0])) {
					$topicid = $topicids[1][0];
				} else {
					return null; exit; 
				}
				
				if(empty($sid) || empty($vid)) {
					return null; exit; 
				}
			} else {
				return null; exit; 
			}
			
			$return['sid'] = $sid;
			$return['vid'] = $vid;
			$return['pic'] = $pic;
			$return['topicid'] = $topicid;
			$return['flashvar'] = '';
		}
		return $return;
	}

	//判断字符串编码
	function is_gb2312($str){
		for($i=0; $i<strlen($str); $i++) {
			$v = ord( $str[$i] );
			if( $v > 127) {
				if(($v >= 228) && ($v <= 233)){
					if( ($i+2) >= (strlen($str) - 1)) return true;  // not enough characters
					$v1 = ord( $str[$i+1] );
					$v2 = ord( $str[$i+2] );
					if( ($v1 >= 128) && ($v1 <=191) && ($v2 >=128) && ($v2 <= 191) ) // utf编码
						return false;
					else
						return true;
				}
			}
		}
		return true;
	}
	
	//获取网页charset
	function is_charset($html){
		preg_match("/<meta.+?charset=([-\w]+)/i",$html,$charset);
		if(empty($charset)){
			preg_match("/<meta.+?charset=\"([-\w]+)\"/i",$html,$charset);
		}
		if(empty($charset)){
			preg_match("/<meta.+?charset=\'([-\w]+)\'/i",$html,$charset);
		}
		// 匹配土豆网网页编码
		if(empty($charset)) {
			preg_match("/<meta\s*charset=\"([\w])\"/ixs",$html,$charset);		
		}
		return $charset[1];    
	}

	//获取网页的header信息，并返回Location的参数
	function curlLocation($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		$get_contents = curl_exec($ch);
		if ($error = curl_error($ch) ) {
			//出错处理
			return false;
		}
		curl_close($ch);
		preg_match("/Location:(.*)/i",  trim($get_contents), $charset);
		return $charset[1];
	}
	
function gzdecode($data) {
  $len = strlen($data);
  if ($len < 18 || strcmp(substr($data,0,2),"\x1f\x8b")) {
    return null;  // Not GZIP format (See RFC 1952)
  }
  $method = ord(substr($data,2,1));  // Compression method
  $flags  = ord(substr($data,3,1));  // Flags
  if ($flags & 31 != $flags) {
    // Reserved bits are set -- NOT ALLOWED by RFC 1952
    return null;
  }
  // NOTE: $mtime may be negative (PHP integer limitations)
  $mtime = unpack("V", substr($data,4,4));
  $mtime = $mtime[1];
  $xfl   = substr($data,8,1);
  $os    = substr($data,8,1);
  $headerlen = 10;
  $extralen  = 0;
  $extra     = "";
  if ($flags & 4) {
    // 2-byte length prefixed EXTRA data in header
    if ($len - $headerlen - 2 < 8) {
      return false;    // Invalid format
    }
    $extralen = unpack("v",substr($data,8,2));
    $extralen = $extralen[1];
    if ($len - $headerlen - 2 - $extralen < 8) {
      return false;    // Invalid format
    }
    $extra = substr($data,10,$extralen);
    $headerlen += 2 + $extralen;
  }
  $filenamelen = 0;
  $filename = "";
  if ($flags & 8) {
    // C-style string file NAME data in header
    if ($len - $headerlen - 1 < 8) {
      return false;    // Invalid format
    }
    $filenamelen = strpos(substr($data,8+$extralen),chr(0));
    if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
      return false;    // Invalid format
    }
    $filename = substr($data,$headerlen,$filenamelen);
    $headerlen += $filenamelen + 1;
  }
  $commentlen = 0;
  $comment = "";
  if ($flags & 16) {
    // C-style string COMMENT data in header
    if ($len - $headerlen - 1 < 8) {
      return false;    // Invalid format
    }
    $commentlen = strpos(substr($data,8+$extralen+$filenamelen),chr(0));
    if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
      return false;    // Invalid header format
    }
    $comment = substr($data,$headerlen,$commentlen);
    $headerlen += $commentlen + 1;
  }
  $headercrc = "";
  if ($flags & 1) {
    // 2-bytes (lowest order) of CRC32 on header present
    if ($len - $headerlen - 2 < 8) {
      return false;    // Invalid format
    }
    $calccrc = crc32(substr($data,0,$headerlen)) & 0xffff;
    $headercrc = unpack("v", substr($data,$headerlen,2));
    $headercrc = $headercrc[1];
    if ($headercrc != $calccrc) {
      return false;    // Bad header CRC
    }
    $headerlen += 2;
  }
  // GZIP FOOTER - These be negative due to PHP's limitations
  $datacrc = unpack("V",substr($data,-8,4));
  $datacrc = $datacrc[1];
  $isize = unpack("V",substr($data,-4));
  $isize = $isize[1];
  // Perform the decompression:
  $bodylen = $len-$headerlen-8;
  if ($bodylen < 1) {
    // This should never happen - IMPLEMENTATION BUG!
    return null;
  }
  $body = substr($data,$headerlen,$bodylen);
  $data = "";
  if ($bodylen > 0) {
    switch ($method) {
      case 8:
        // Currently the only supported compression method:
        $data = gzinflate($body);
        break;
      default:
        // Unknown compression method
        return false;
    }
  } else {
    // I'm not sure if zero-byte body content is allowed.
    // Allow it for now...  Do nothing...
  }
  // Verifiy decompressed size and CRC32:
  // NOTE: This may fail with large data sizes depending on how
  //       PHP's integer limitations affect strlen() since $isize
  //       may be negative for large sizes.
  if ($isize != strlen($data) || crc32($data) != $datacrc) {
    // Bad format!  Length or CRC doesn't match!
    return false;
  }
  return $data;
}

}
?>