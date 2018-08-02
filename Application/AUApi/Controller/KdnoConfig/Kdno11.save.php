<?php
// 暂时没用
class save{

	public function getData($mkno){
		return M('TranList')->field('STNO,STEXT')->where(array('MKNO'=>$mkno))->find();
	}
}