<?php
	init_get();
	function init_get() {
		$r = $_SERVER['REQUEST_URI'];
		$get = &$_GET;
		$r = substr($r, strrpos($r, '/') + 1);				//第[1]步
		substr($r, 0, 9) == 'index.php' && $r = substr($r, 9);
		substr($r, 0, 1) == '?' && $r = substr($r, 1);

		//$r = preg_replace('#^/?([^/]+/(index\.php)?\??)*#', '', $r);	//第[1]步
		
		// 第一个分号作为分隔
		$r = str_replace('.htm&', '?', $r);				//第[2]步
		$r = str_replace('.htm?', '?', $r);				//第[3]步
		$sep = strpos($r, '?');						//第[4]步
		
		$s1 = $s2 = '';	// $s1 为 url 前半部分(格式：user-login-page-123), $s2 为后半部分(格式：user=login&page=2)。
		if($sep !== FALSE) {
			$s1 = substr($r, 0, $sep);
			$s2 = substr($r, $sep + 1);
		} else {
			$s1 = $r;
			$s2 = '';
			if(substr($s1, -4) == '.htm') {
				$s1 = substr($s1, 0, -4);			//第[5]步
			} else {
				$s2 = $s1;					//第[6]步
				$s1 = '';
			}
		}
		
		$arr1 = $arr2 = array();
		
		$s1 && $arr1 = explode('-', $s1);
		parse_str($s2, $arr2);
		$get += $arr1;
		$get += $arr2;
		
		$num = count($arr1);
		if($num > 2) {
			for($i=2; $i<$num; $i+=2) {
				isset($arr1[$i+1]) && $get[$arr1[$i]] = urldecode($arr1[$i+1]);
			}		
		}
		
		if(isset($get[0]) && preg_match("/^\w+$/", $get[0])) {
			header('HTTP/1.1 301 Moved Permanently');
			if(isset($get[4]) && isset($get[5])) {
				header("Location:?m=$get[0]&a=$get[1]&$get[2]=$get[3]&$get[4]=$get[5]");
			} else if(isset($get[2]) && isset($get[3])) {
				header("Location:?m=$get[0]&a=$get[1]&$get[2]=$get[3]");
			} else if(isset($get[0]) && isset($get[1])) {
				header("Location:?m=$get[0]&a=$get[1]");
			} else {
				header('Location:?m=index&a=index');
			}		
		}
	}

	define('APP_DEBUG', true);
	define('DEBUG', true);
	define('WWW_PATH', str_replace('\\', '/', getcwd()).'/');
	require './ThinkPHP/ThinkPHP.php';