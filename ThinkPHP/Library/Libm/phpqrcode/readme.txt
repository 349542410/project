调用 qrcodeApi.class.php  之后，
$qrcode = new \Libm\phpqrcode\qrcodeApi();
$qrcode->text = trim(I('get.url'));
$qrcode->png();
即可