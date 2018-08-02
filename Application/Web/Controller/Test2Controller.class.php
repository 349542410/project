<?php
namespace Web\Controller;
use Think\Controller;
class Test2Controller extends Controller {
   
    public function index(){
        $barcode = new \Libm2\barcode\barcodeApi();
		$barcode->text = "125475510165646";
		echo $barcode->png();
    }
}