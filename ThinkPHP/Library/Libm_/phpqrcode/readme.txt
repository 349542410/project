���� qrcodeApi.class.php  ֮��
$qrcode = new \Libm\phpqrcode\qrcodeApi();
$qrcode->text = trim(I('get.url'));
$qrcode->png();
����