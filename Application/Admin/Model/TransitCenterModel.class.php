<?php
namespace Admin\Model;
use Think\Model;
class TransitCenterModel extends Model{

	protected $_validate = array(
		array('name','require','线路名称不能为空'),
		array('transit','require','转发快递不能为空'),
	);
}