<?php
namespace Org\MK;
class SYS {
	//通过appID,appKey获取ID
	public function getid($appID,$appKey){
		$Sys = M("union_key");
		$where	= array(
			"appID"=>':appID',
			"appKey"=>':appKey',
			);
		$bind	= array(
			":appID"=>array($appID,\PDO::PARAM_STR),
			":appKey"=>array($appKey,\PDO::PARAM_STR),
			);
		$rs = $Sys->where($where)->bind($bind)->limit(1)->getField('id');
		return $rs;
	}
	//返回数组
	public function getidname($appID,$appKey){
		$Sys = M("union_key");
		$where	= array(
			"appID"=>':appID',
			"appKey"=>':appKey',
			);
		$bind	= array(
			":appID"=>array($appID,\PDO::PARAM_STR),
			":appKey"=>array($appKey,\PDO::PARAM_STR),
			);
		$rs = $Sys->where($where)->bind($bind)->limit(1)->getField('id,unname');
		return $rs;
	}
	public function getappidkey($id){
		if($id==0)return 0;
		$Sys = M("union_key");
		$where	= array("uid"=>":uid");
		$bind	= array(':uid' => array($id,\PDO::PARAM_INT));
		$rs = $Sys->where($where)->bind($bind)->limit(1)->find();
		//$fs	= $rs->fetch();
		//print_r($fs);
		if(!is_array($rs)) return 0;
		return $rs;
	}
	public function getappid($id){
		if($id==0)return 0;
		$Sys = M("union_key");
		$where	= array("uid"=>":uid");
		$bind	= array(':uid' => array($id,\PDO::PARAM_INT));
		$rs 	= trim($Sys->where($where)->bind($bind)->limit(1)->select());
		return ($rs=='')?0:$rs;
	}
}