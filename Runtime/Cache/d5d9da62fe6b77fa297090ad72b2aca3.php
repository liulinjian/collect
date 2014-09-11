<?php if (!defined('THINK_PATH')) exit();?><script src="Tpl/js/jquery1.7.js" type="text/javascript"></script>
<link href="Tpl/css/font-awesome.min.css" rel="stylesheet">
<style type="text/css">
.mydiv{
	margin-top: 5px;
	padding: 5px 10px;
}
#spinner{

}
</style>
<div style="min-height:540px;margin-bottom:50px;">
<!--通栏背景-->
	<!-- content start -->
	<div style="width:1000px;margin:0 auto;padding:20px;">
		<div class="mydiv" id="collect_form">  
			<span> 公众号名称： </span>
			<input id="wxname" type="text" style="width:500px;" name="wxname">
		</div>
		<div class="mydiv" id="collect_result"> 
			<div class="result_title mydiv">  
				<span> 微信号： </span> 
				<input type="text" name="title" id="wxid" style="width:500px;">  </input>
				<button id="submitBtn"> 获取openid </button>
			</div>
			<div class="result_content mydiv"> 
				<span> OpenID： 
					<span id="weixin_openid"></span>
					<button id='getArticles'>获取文章列表</button>
				</span>
			</div>
			<center id="spinner" style="display:none;">
				<i class="fa fa-spinner fa-spin"></i>
				<span class="msg">正在抓取文章</span>
			</center>
			<div id="content" rows="25" cols="140"> 
			</div>
		</div>
	</div>

</div>
<!--index_li_结束-->

<script type="text/javascript">
	function test(){
		var url = './?m=weixin&a=getXml';
		var wxid = $('#wxid').val().trim();
		var openid = $('#weixin_openid').text().trim();
		$.post(url, {'openid':openid, 'wxid':wxid}, function(res){
			$('#content').html( res );
		});
	}
	$(document).ready(function(){
		var path = './?m=weixin&a=openId';
		$('#submitBtn').click(function(){
			$("#spinner").css("display","block");
			$('.msg').text('正在获取openId');

			var wxname = $('#wxname').val().trim();
			var wxid = $('#wxid').val().trim();
			$('#article_title').val( '' );
			if ( wxname == '' || wxname == null ) {
				return;
			};
			if ( wxid == '' || wxid == null ) {
				return;
			};
			$.post(path, {'wxname':wxname, 'wxid':wxid}, function(res){
				$("#spinner").css("display","none");
				if ( res=="" ) {
					$('#weixin_openid').empty();
					$('#weixin_openid').html( "没有找到OPENID" );
					return;
				};
				$('#weixin_openid').empty();
				$('#weixin_openid').html( res );
			});
		});
		$('#getArticles').live('click',function(){
			var url = './?m=weixin&a=getXml';
			var wxid = $('#wxid').val().trim();
			$("#spinner").css("display","block");
			$('.msg').text('正在抓取文章');

			var openid = $('#weixin_openid').text().trim();
			$.post(url, {'openid':openid, 'wxid':wxid}, function(res){
				$('#content').empty();
				$('#content').html( res );
				$("#spinner").css("display","none");
			});
		});

		//test();
	});
</script>