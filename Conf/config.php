<?php
return array(
	'URL_MODEL'=>2,
	'DB_TYPE'=>'mysql',
	'DB_HOST'=>'localhost',
	'DB_NAME'=>'collector',
	'DB_USER'=>'root',
	'DB_PWD'=>'mysql',
	'DB_PORT'=>'3306',
	'DB_PREFIX'=>'kaoder_',
    	
    //表单令牌设置
    'TOKEN_ON'=>true,  // 是否开启令牌验证
	'TOKEN_NAME'=>'__hash__',    // 令牌验证的表单隐藏字段名称
	'TOKEN_TYPE'=>'md5',  //令牌哈希验证规则 默认为MD5
	'TOKEN_RESET'=>true,  //令牌验证出错后是否重置令牌 默认为true


	'APP_NAME' => '采集器',	
	// 站点介绍
	'APP_BRIEF' => '采集器',
	
	'APP_COPYRIGHT' => '© littlebear',
	
    //是否开启邮箱注册
	'REG_EMAIL_ON' => false,
	
	// 是否开启 URL-Rewrite
	'URLREWRITE' => '?',

	//以下网站是在抓取文章的时候需要iconv转码的
	'NEED_ICONV' => array( 
		'history.sina.com.cn',
		'fo.sina.com.cn',
		'view.news.qq.com',
		'foxue.qq.com',
		'history.people.com.cn',
		'news.sina.com.cn',
		'sports.sina.com.cn',
		'ent.sina.com.cn',
		'politics.people.com.cn',
		'cloud.51cto.com',
		'tech.qq.com',
	),

	//抓取的主流音乐网站
	'MUSIC_WEBSITES'=>array(
		'www.xiami.com',
	),
	//特征码
	'CONTENT_CODE'=>array(
		'body',
		'artibody',
		'content',
		'main-content',
		'display',
		'article_details',
		'article',
	),

	//分页特征码
	'PAGE_CODE' => array(
		'page',
	),
);
?>