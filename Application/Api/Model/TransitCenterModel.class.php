<?php
namespace Api\Model;
use Think\Model;
class TransitCenterModel extends Model{

	protected $_validate = array(
		array('name','require','线路名称不能为空'),
		array('transit','require','转发快递不能为空'),
		array('email','require','邮箱不能为空'),
	);
}