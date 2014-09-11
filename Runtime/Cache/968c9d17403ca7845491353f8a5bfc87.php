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
			<span> 来源列表：(一共<?php echo isset($count) ? $count : '';?>条记录) </span>
			<?php if(!empty($collect) ) { ?>
			   <table id="collect_table" cellspacing="15">
			   		<tr> 
			   			<th>ID</th> 
			   			<th>域名</th> 
			   			<th>别名</th> 
			   			<th>标题匹配规则</th> 
			   			<th>内容匹配规则</th> 
			   			<th>操作</th>
			   		<tr>
				<?php if(!empty($collect)) { foreach($collect as &$c) {?>
					<tr id="tr_<?php echo isset($c['id']) ? $c['id'] : '';?>"> 
			   			<td><?php echo isset($c['id']) ? $c['id'] : '';?></td> 
			   			<td><a href="http://<?php echo isset($c['domain']) ? $c['domain'] : '';?>" target="_blank" style="color:blue;"><?php echo isset($c['alias']) ? $c['alias'] : '';?></a></td> 
			   			<td><?php echo isset($c['alias']) ? $c['alias'] : '';?></td> 
			   			<td>
						<?php if(!empty($c['title']) ) { ?>
							<?php if(!empty($c['title'])) { foreach($c['title'] as &$m) {?>
								<p>  <?php echo isset($m['match']) ? $m['match'] : '';?>=><?php echo isset($m['pos']) ? $m['pos'] : '';?> </p>
							<?php }} ?>
						<?php } ?>
			   			</td> 
			   			<td>
			   			<?php if(!empty($c['content']) ) { ?>
							<?php if(!empty($c['content'])) { foreach($c['content'] as &$m) {?>
								<p>  <?php echo isset($m['match']) ? $m['match'] : '';?>=><?php echo isset($m['pos']) ? $m['pos'] : '';?> </p>
							<?php }} ?>
						<?php } ?>
					    </td> 
					    <td><a href="./?m=collect&a=edit&id=<?php echo isset($c['id']) ? $c['id'] : '';?>">修改</a>&nbsp;/&nbsp;<a class="delete_collect_a" href="javascript:;" cid=<?php echo isset($c['id']) ? $c['id'] : '';?>>删除</a></td>
			   		<tr>
				<?php }} ?>
			   </table>
			<?php } ?>
			<button id="add_collect" style="padding:5px 10px;">添加记录</button>
			<a style="padding:5px 10px;" href="./" target="_blank">抓取页面</a>
		</div>
	</div>

</div>
<!--index_li_结束-->
<script type="text/javascript">
	$(document).ready(function(){
		$('.delete_collect_a').click(function(){
			if (confirm('你确定删除该条记录吗?')) {
				var obj = $(this);
				var cid = obj.attr('cid');
				var url = "./?m=collect&a=delete_collect";
				$.post( url,{ id:cid }, function(res){
					res = $.parseJSON( res );
					alert( res.msg );
					if (res.error==0) {
						//刷新页面
						$('#tr_'+cid).fadeOut();
					};
				});
			};
		});

		$('#add_collect').click(function(){
			var table = $('#collect_table');
			var html = '<tr>';
			html += '<td>添加</td>';
			html += '<td><input type="text" id="add_domain" /></td>';
			html += '<td><input type="text" id="add_alias" /></td>';
			html += '<td><button id="save_add_collect">保存</button><td>';
			html += '</tr>';
			table.append( html );
			$(this).attr('disabled','disabled');
		});
		$('#save_add_collect').live('click',function(){
			var domain = $('#add_domain');
			var alias = $('#add_alias');
			var url = "./?m=collect&a=add_collect";
			$.post( url,{ domain:domain.val(), alias:alias.val() }, function(res){
				res = $.parseJSON( res );
				alert( res.msg );
				if ( res.error==0 ) {
					window.location.reload();
				};
			});
		});
	});
</script>