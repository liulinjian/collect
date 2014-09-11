<?php

/*
	[Xiuno] (C)2008-2010
	This is NOT a freeware, use is subject to license terms

	$RCSfile: base_model.class.php,v $
	$Revision: 1.0 $
	$Date: 2013/11/19 17:30:00 $
	$Author: littlebear $
*/

class CollectModel extends Model{
	
	function __construct() {		
		$this->tableName = 'collect';
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
		return $this->maxid('collect');
	}
	
	//从域名获取记录
	public function get_collect_by_domain( $domain ) {
		$m = $this->where("domain = '".$domain."'")->order('id ASC')->find();
		return !empty($m) ? $m : FALSE;
	}
	
	public function get_collect_by_page($start, $limit) {
		//$version = $this->index_fetch(array(), array('vid'=>1), $start, $limit);
		if(!empty($limit)) {
			$collect = $this->order('id ASC')->limit($start.' ,'.$limit)->select();
		} else {
			$collect = $this->order('id ASC')->select();
		}
		return (!empty($collect)) ? $collect : FALSE;
	}
}
?>