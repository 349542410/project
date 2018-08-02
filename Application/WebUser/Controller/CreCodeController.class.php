<?php
/**
 * 在线下单
 */
namespace WebUser\Controller;
use Think\Controller;
class CreCodeController extends Controller {

    /**
     * 条形码生成 方法
     * @return [type] [description]
     */
    public function barcode(){
        $barcode = new \Libm\barcode\barcodeApi();
        $barcode->text = trim(I('text'));
        echo $barcode->png();
    }
    
    /**
     * 二维码生成 方法  前端页面请求数据后返回二维码图片 Jie 20151126
     * @param  string  $url     [生成链接地址]
     * @param  string  $outfile [输出图片类型]
     * @param  integer $level   [容错级别]
     * @param  integer $size    [图片大小]
     * @return [type]           [description]
     */
    public function qrcode(){
        $qrcode = new \Libm\phpqrcode\qrcodeApi();
        $qrcode->text = trim(I('get.url'));
        echo $qrcode->png();
    }
}