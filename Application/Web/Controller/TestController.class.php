<?php
namespace Web\Controller;
use Think\Controller;
class TestController extends Controller {
   
    public function index(){
        $barcode = new \Libm\barcode\barcodeApi();
		$barcode->text = "125475510165646";
		echo $barcode->png();
    }
}