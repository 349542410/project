<?php 
namespace Api\Model;
use Think\Model\ViewModel;
class RuleViewModel extends ViewModel{
	public $viewFields=array(		
		'rule'=>array('_table'=>'mk_auth_rule','id','name','title','type','condition'=>'term','status','pid_one', 'pid_two', 'pid_three', 'sort'),
		//condition必须别名,否则出错
		'modules'=>array('_table'=>'mk_auth_modules','ModulesName','_on'=>'rule.pid_one = modules.id')
		//'modules'=>array('_table'=>'mk_auth_modules','ModulesName','_or'=>'rule.pid_one=modules.id','_or'=>'rule.pid_two=modules.id', '_or'=>'rule.pid_three=modules.id'),
		//'modules_b'=>array('_table'=>'mk_auth_modules','ModulesName' => 'modules_b','_on'=>'rule.pid_two=modules_b.id'),
		//'modules_c'=>array('_table'=>'mk_auth_modules','ModulesName' => 'modules_c','_or'=>'rule.pid_three=modules_c.id'),
		
	
		);
}
 ?>