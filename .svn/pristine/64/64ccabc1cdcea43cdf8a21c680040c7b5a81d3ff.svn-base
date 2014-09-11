<?php

/*
	[Xiuno] (C)2008-2010
	This is NOT a freeware, use is subject to license terms

	$RCSfile: base_model.class.php,v $
	$Revision: 1.0 $
	$Date: 2013/11/19 17:33:00 $
	$Author: littlebear $
*/

class CollectmatchModel extends Model{
	const TYPE_TITLE = 0;
	const TYPE_CONTENT = 1;
	
	function __construct() {		
		$this->tableName = 'collect_match';
		$this->pk = 'id';
		parent::__construct();		
	}
	
	public function _create($arr) {		
		return $this->data($arr)->add();
	}
	
	public function update($id, $arr) {
		return $this->where("id = '".$id."'")->data($arr)->save();
	}
	
	public function read($id) {
		return $this->where("id = '".$id."'")->find();
	}

	public function _delete($id) {
		return $this->where("id = '".$id."'")->delete();
	}
	
	public function get_maxid() {
		return $this->maxid('collect_match');
	}
	
	//从collect id获取匹配记录,按匹配序号排序
	public function get_matchlist_by_collect_type( $cid, $type ) {
		$m = $this->where(array( 'cid' => $cid, 'type'=>$type))->order('id asc')->select();
		return !empty($m) ? $m : FALSE;
	}

	//从collect id获取匹配记录,按匹配序号排序
	public function get_matchlist_by_collect( $cid ) {
		$m = $this->where(array( 'cid' => $cid ))->order('id asc')->select();
		return !empty($m) ? $m : FALSE;
	}

	//根据类型获取所有的匹配规则
	public function get_matchlist_by_type( $type ) {
		$m = $this->where(array( 'type' => $type ))->order('id asc')->select();
		return !empty($m) ? $m : FALSE;
	}


	// $cl['match'] = strtolower( preg_replace('/.*?[\.|\#]/', '', $cl['match']) );
	// //求相似度最大值
	// similar_text( $cl['match'], strtolower( $div->class ), $tp);
	// $p = $tp>$p ? $tp:$p;
	// if ( $p >= 100.0 ) {
	// 	$content_match = 'div.'.$div->class;
	// 	$flag = true;
	// 	break;
	// }

	// similar_text($cl['match'], strtolower( $div->id ), $tp);
	// $p = $tp>$p ? $tp:$p;
	// if ( $p >= 100.0 ) {
	// 	$content_match = 'div.'.$div->id;
	// 	$flag = true;
	// 	break;
	// }

	// if ( !$flag ) {
	// 		echo 'percatage======>'.$p.'<br>';
	// 	}

}
?>