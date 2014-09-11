<?php

/*
	[Xiuno] (C)2008-2010
	This is NOT a freeware, use is subject to license terms

	$RCSfile: base_model.class.php,v $
	$Revision: 1.0 $
	$Date: 2013/11/19 17:30:00 $
	$Author: littlebear $
*/

class WeixinModel extends Model{
	
	function __construct() {		
		$this->tableName = 'weixin';
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
	
}
?>