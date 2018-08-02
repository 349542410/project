<?php
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Author: Man 2016-04-26
// +----------------------------------------------------------------------

namespace Org\MK;

class Hashtb {
	private $M;
	public function select($tb=0)
	{
		return $this->M = M('hash_tb'.(string)$tb);
	}
	private function _M()
	{
		return $this->M?:(self::select());
	}
	private function val($valnum,$valstr)
	{
		return ($valstr==null)?$valnum:$valstr;
	}
	public function exists($hd,$hkey,$rt=false) //$rt=true返回结果 false返回id
	{
		$rs = self::_M()->where(array('hd'=> $hd,'hkey'=> $hkey))->find();
		if(!$rs) return 0;
		if($rt){
			return array('hd'=>$rs['hd'],'key'=>$rs['hkey'],'value'=>self::val($rs['hvalnum'],$rs['hvalstr']));
		}
		return $rs['id']; //返回id
	}

	//set function
	public function set($hd,$hkey,$value=null,$data=null)
	{
		$gt 	= array('integer');
		$dt 	= array('hd'=> $hd,'hkey'=> $hkey);
		$dtw 	= $dt;
		if($value!==null){
			if(in_array(gettype($value),$gt)){
				$dt['hvalnum'] = $value;
				$dt['hvalstr'] = null;
			}else{
				$dt['hvalstr'] = $value;
				$dt['hvalnum'] = 0;
			}
		}
		if(is_array($data)){
			$dt = array_merge($dt,$data);
		}
		//这样不行，如果没有改变数据就返回0
		//$id 	= self::_M()->where($dtw)->save($dt);

		$id 	= self::exists($hd,$hkey);
		//echo $id;die();
		if($id<1){
			self::_M()->add($dt);
		}else{
			self::_M()->where(array('id'=>$id))->save($dt);
		}
		//var_dump($dt);
		//return self::_M()->add($dt,array(),true); //Man这样会有问题，更新时是默认删除新建，所以对val值有影响
		return true;
	}
	public function sset($hd,$hkey,$status=0,$value)
	{
		return self::set($hd,$hkey,$value,array('status'=>$status));
	}



	//get function
	public function get($hd,$hkey)
	{
		$dt 		= array('hd'=> $hd,'hkey'=> $hkey);
		$rs 		= self::_M()->where($dt)->find();
		//var_dump($rs);
		if(!$rs) return false;
		return self::val($rs['hvalnum'],$rs['hvalstr']);
	}
	public function sget($hd,$hkey)
	{
		$dt 		= array('hd'=> $hd,'hkey'=> $hkey);
		$rs 		= self::_M()->where($dt)->find();
		//var_dump($rs);
		if(!$rs) return false;
		$rt = array(
			'status'	=>$rs['status'],
			'value'		=> self::val($rs['hvalnum'],$rs['hvalstr']),
		);
		return $rt;
	}
	public function mget($hd,$page=1,$limit=20,$order='desc',$where=null) //无需hkey
	{
		if($limit>200) 	$limit = 200;
		if($page<1) 	$page = 1;
		$dt 		= array('hd'=> $hd);
		if(is_array($where)){
			$dt = array_merge($dt,$where);
		}
		$rs 		= self::_M()->where($dt)->order('id '.$order)->page($page)->limit($limit)->select();
		if(!$rs) return false;
		$rt = array();
		foreach ($rs as $k => $v) {
			$rt[] = array(
				'key'	=> $v['hkey'],
				'status'=> $v['status'],
				'value' => self::val($v['hvalnum'],$v['hvalstr']),
			);
		}
		return $rt;
	}
	//包含status的记录
	public function msget($hd,$status=0,$page=1,$limit=20,$order='desc') //无需hkey
	{
		return self::mget($hd,$page,$limit,$order,array('status'=>$status));
	}




	//delete function
	public function delete($hd,$hkey)
	{
		$dt 			= array('hd'=> $hd,'hkey'=> $hkey);
		return self::_M()->where($dt)->delete();
	}

	//other function
	public function increase($hd,$hkey,$value=1) //$value<0时为 减,仅接收整数，如金额的以分为单位
	{
		$gt = array('integer');
		if(!in_array(gettype($value),$gt) || $value===0){
			return false;
		}
		if(self::exists($hd,$hkey)<1){
			return self::set($hd,$hkey,$value);
		}
		$dt 			= array('hd'=> $hd,'hkey'=> $hkey);
		if($value>0){
			return self::_M()->where($dt)->lock(true)->setInc('hvalnum',$value);
		}else{
			return self::_M()->where($dt)->lock(true)->setDec('hvalnum',abs($value));
		}
	}
}