<?php
namespace Api\Model;
use Think\Model;
class AuthModulesModel extends Model {
	public  function Authsmodules($rowid=0,$resArray=array(),$str=''){
      	$res=$this->where('pid='.$rowid)->select();
      	foreach($res as $rows){
          $rows['catname'] = $str;
          $resArray[] = $rows;
          $resArray = $this->Authsmodules($rows['id'],$resArray,"<span style='color:red'>|-</span>".$str);	
    	}
    	return $resArray;
	}

	public  function rules($rowid=0,$resArray=array(),$str=''){
      	$res=$this->where('pid='.$rowid)->select();
      	foreach($res as $rows){
          $rows['catname'] = $str;
          $resArray[] = $rows;
          
          $resArray = $this->rules($rows['id'],$resArray,'1'.$str);	
    	}
    	return $resArray;
	}	
	
	

}