<?php
namespace Org\GZP;

require_once('D:/wwwroot/tp323/app82/Customs/Controller/SendCustoms.class.php');
class SendCustoms{
	public function send($data, $type)
	{
		$sp 	= new \SendCustoms();
		return $sp->send($data, $type);
	}
}
