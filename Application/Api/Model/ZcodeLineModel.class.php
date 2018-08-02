<?php
namespace Api\Model;
use Think\Model;
class ZcodeLineModel extends Model {
	public  function zcodeline($rowid=0,$resArray=array(),$line_id){
      	$res=$this->field('id')->where('pid = '.$rowid. ' AND line_id = ' . $line_id)->select();
      	foreach($res as $rows){
          //$rows['catname'] = $str;
          $resArray[] = $rows['id'];
          $resArray = $this->zcodeline($rows['id'],$resArray,$line_id);	
    	}
    	return $resArray;
	}


}