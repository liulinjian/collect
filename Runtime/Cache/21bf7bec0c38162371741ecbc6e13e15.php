<?php if (!defined('THINK_PATH')) exit();?><script src="Tpl/js/jquery1.7.js" type="text/javascript"></script>
<style type="text/css">
.mydiv{
	margin-top: 5px;
	padding: 5px 10px;
}
</style>
<div style="min-height:540px;margin-bottom:50px;">
<!--通栏背景-->
	<!-- content start -->
	<div style="width:1000px;margin:0 auto;padding:20px;">
		<div class="mydiv" id="collect_form">  
			<span> 文章地址： </span>
			<input id="url" type="text" style="width:500px;" name="url">
			<button id="submitBtn"> 采集 </button>
		</div>
		<div class="mydiv" id="collect_result"> 
			<div class="result_title mydiv">  
				<span> 标题： </span> <input type="text" name="title" id="article_title" style="width:500px;">  </input>
			</div>
			<div class="result_content mydiv"> 
				<span> 内容： </span>
				<!-- <textarea id="content" rows="20"> 
				</textarea> -->
			</div>
		</div>
	</div>

</div>
<!--index_li_结束-->

<script type="text/javascript">
	$(document).ready(function(){
		var path = './?a=collect';
		$('#submitBtn').click(function(){
			var url = $('#url').val().trim();
			$('#article_title').val( '' );
				$('.result_content').html( '' );
			if ( url == '' || url == null ) {
				return;
			};
			$.post(path, {'url':url}, function(res){
				res = $.parseJSON( res );
				$('#article_title').val( res.title );
				$('.result_content').html( res.content );
			});
		});
	});
</script>