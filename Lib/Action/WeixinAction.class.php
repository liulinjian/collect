<?php
class WeixinAction extends CommonAction {
	/*
		kaoder    采集器
		version   1.0
		author    littlebear
		date      2013/11/19
		func      根据文章来源url获取文章标题和内容 
	*/

	function __construct() {
		parent::__construct();
	}

	//解析URL主页 http://localhost/kd/?m=weixin
	public function index(){
		$this->display('Tpl/wx/index.html');
	}

	//获取openId
	public function openId(){
		$wxname = trim( $_REQUEST['wxname'] );
		$wxname = iconv('UTF-8', 'GBK//IGNORE', $wxname);
		$wxid = trim( $_REQUEST['wxid'] );
		$page = 1;

		$url = "http://weixin.sogou.com/weixin?type=1&query=".$wxname."&page=".$page;
		
		$html = file_get_html( $url );

		$results = $html->find('div.results a');
		$wxhs = $html->find('div.txt-box h4');

		if (!preg_match('/resnum/i', $html)) {
			echo '';
			exit();
		}
		$totalNum = intval($html->find('resnum#scd_num', 0)->innertext);
		$pageCount = ceil( $totalNum / 10 );


		$openid = '';
		for ($i=1; $i <= $pageCount; $i++) {

			for ($j=0; $j < count($wxhs); $j++) { 
				$wxhs[$j] = strip_tags($wxhs[$j]);
				$wxhs[$j] = substr($wxhs[$j], 13);
				$wxhs[$j] = trim($wxhs[$j]);
				
				if ($wxid == $wxhs[$j]) {
					$openid = substr($results[$j]->getAttribute('href'), 12);
					break;
				}
			}
			if (!$openid && $i!=$pageCount) {
				$html = file_get_html("http://weixin.sogou.com/weixin?type=1&query=".$wxname."&page=".($i+1));

				$results = $html->find('div.results a');
				$wxhs = $html->find('div.txt-box h4');
			}else{
				break;
			}
		}
		$html->clear();
		echo $openid.'';
	}

	//获取articles
	//这里需要保存：微信号，容文章标题，内容简介，内容
	public function getXml(){
		$openid = trim( $_REQUEST['openid'] );
		$wxid = trim( $_REQUEST['wxid'] );
		if (!$openid) {
			echo "no openid";
			exit();
		}

		$url = "http://weixin.sogou.com/gzhjs?cb=sogou.weixin.gzhcb&openid=".$openid;

		$json = file_get_html( $url );
		$json = stripslashes($json);

		preg_match('/\"totalItems\"\:(\d+)/', $json, $matches);
		$itemCount = $matches[1];
		preg_match('/\"totalPages\"\:(\d+)/', $json, $matches);
		$pageCount = $matches[1];

		if ( intval($itemCount) == 0 ) {
			echo "该公众号没有发布文章";
			exit();
		}

		$tmp = array();
		$k = 0;
		for ($j=0; $j < 2; $j++) { 
			if ($j==0) {
				preg_match_all("/<url>(.*?)<\/url>/i", $json, $links, PREG_PATTERN_ORDER);
				preg_match_all("/<content>(.*?)<\/content>/i", $json, $contents, PREG_PATTERN_ORDER);
				
				for ($i=0; $i < count($links[1]); $i++) { 
					$url = ltrim( $links[1][$i], '<![CDATA[' );
					$url = rtrim( $url, ']]>' );

					$content = ltrim( $contents[1][$i], '<![CDATA[' );
					$content = rtrim( $content, ']]>' );

					$tmp[$k]['url'] = $url;
					$tmp[$k]['content'] = $content;
					
					$k++;
				}
			}else{
				$url = "http://weixin.sogou.com/gzhjs?cb=sogou.weixin.gzhcb&openid=".$openid.'&page='.($j+1);
				$json = file_get_html( $url );
				$json = stripslashes($json);

				preg_match_all("/<url>(.*?)<\/url>/i", $json, $links, PREG_PATTERN_ORDER);
				preg_match_all("/<content>(.*?)<\/content>/i", $json, $contents, PREG_PATTERN_ORDER);
				
				for ($i=0; $i < count($links[1]); $i++) { 
					$url = ltrim( $links[1][$i], '<![CDATA[' );
					$url = rtrim( $url, ']]>' );

					$content = ltrim( $contents[1][$i], '<![CDATA[' );
					$content = rtrim( $content, ']]>' );

					$tmp[$k]['url'] = $url;
					$tmp[$k]['content'] = $content;
					
					$k++;
				}
			}
		}
		$mh = curl_multi_init();   
		for ($m=0; $m<count($tmp); $m++) {   
		  $conn[$i] = curl_init($tmp[$m]['url']);   
		  curl_setopt($conn[$i], CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)");   
		  curl_setopt($conn[$i], CURLOPT_HEADER ,0);   
		  curl_setopt($conn[$i], CURLOPT_CONNECTTIMEOUT,60);   
		  curl_setopt($conn[$i],CURLOPT_RETURNTRANSFER,true);  // 设置不将爬取代码写到浏览器，而是转化为字符串   
		  curl_multi_add_handle ($mh,$conn[$i]);   
		}
		do {   
		  curl_multi_exec($mh,$active);   
		} while ($active);   
		     
		for ($m=0; $m<count($tmp); $m++) {   
		  
		  $data = curl_multi_getcontent($conn[$m]); // 获得爬取的代码字符串  
		  
		  $a = new simple_html_dom();
		  $a->load( $data );
		  $title = $a->find('h1#activity-name',0)->outertext;
		  $content = $a->find('div#page-content',0)->outertext; 
		  $article = array(
				'title' => $title,
				'content' => $content,
			);
		  $a->clear(); 
		  $data = array(
					'wxh'   =>  $wxid,
					'openId' =>  $openid,
					'link'	=>	$tmp[$m]['url'],
					'title'   =>  $article['title'],
					'summary'  =>  $tmp[$m]['content'],
					'content'	=>	$article['content']
				);
		  $this->weixin->_create( $data );
		  echo("save success".$m.'<br>');
		}
		for ($m=0; $m<count($tmp); $m++) {
		  curl_multi_remove_handle($mh,$conn[$m]);   
		  curl_close($conn[$i]);   
		}
		curl_multi_close($mh); 
		unset($tmp);
	}

	private function getArticle($tmp_url){
		$html = file_get_html($tmp_url);

		$title = $html->find('h1#activity-name',0)->outertext;
		$content = $html->find('div#page-content',0)->outertext;
		$html->clear();     
		$article = array(
			'title' => $title,
			'content' => $content,
		);
		return $article;
	}
}