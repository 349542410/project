<?php
namespace Admin\Model;
use Think\Model\ViewModel;
class TranViewModel extends ViewModel {
	public $viewFields = array(     
		'TransitNo'=>array('id','no'),
		'TranList'=>array('count(IL_State=20)', '_on'=>'TranList.noid=TransitNo.id'),
		// 'User'=>array('name'=>'username', '_on'=>'Blog.user_id=User.id'),
	); 
}