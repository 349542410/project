<?php
/**
 * 2017-07-19 jie
 * 类名: qrcodeApi
 * 用途：调用phpqrcode 生成二维码图片
 */
namespace Libm\phpqrcode;
class qrcodeApi{
	public $text;	//表示生成二位的的信息文本
	public $outfile=false;	//默认为否，不生成文件，只将二维码图片返回，否则需要给出存放生成二维码图片的路径
	public $level='L'; //表示容错率，也就是有被覆盖的区域还能识别，分别是 L（QR_ECLEVEL_L，7%），M（QR_ECLEVEL_M，15%），Q（QR_ECLEVEL_Q，25%），H（QR_ECLEVEL_H，30%）；
	public $size=3; //表示生成图片大小，默认是3；参数$margin表示二维码周围边框空白区域间距值；
	public $margin=2; //控制生成二维码的空白区域大小
	public $saveandprint=false; //保存二维码图片并显示出来，$outfile必须传递图片路径

	public function png(){
	    require_once 'phpqrcode.php';

	    //生成二维码图片 
	    $object = new \QRcode();
	    $object->png($this->text, $this->outfile, $this->level, $this->size, $this->margin, $this->saveandprint);
	}
}