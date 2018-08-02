调用 barcodeApi.class.php  之后，
$barcode = new \Libm\barcode\barcodeApi();
$barcode->text = trim(I('text'));
$barcode->png();
即可