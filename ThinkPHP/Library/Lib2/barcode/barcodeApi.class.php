<?php
/**
 * 2017-07-19 jie
 * 类名: barcodeApi
 * 用途：调用phpqrcode 生成条形码图片
 * 只需要传入参数$text即可
 */
namespace Lib2\barcode;
class barcodeApi{
	
	public $text;	//表示生成条形码的的信息文本

    public function png(){
        $codebar ='BCGcode128';
        $text    = $this->text; //条形码将要数据的内容
        // Including all required classes
        require_once 'class/BCGFont.php';
        require_once 'class/BCGColor.php';
        require_once 'class/BCGDrawing.php'; 


        /*'BCGcodabar','BCGcode11','BCGcode39','BCGcode39extended','BCGcode93',
        'BCGcode128','BCGean8','BCGean13','BCGisbn','BCGi25','BCGs25','BCGmsi',
        'BCGupca','BCGupce','BCGupcext2','BCGupcext5','BCGpostnet','BCGothercode'*/
        // $codebar = $_REQUEST['codebar']; //该软件支持的所有编码，只需调整$codebar参数即可。

        // Including the barcode technology
        include_once('class/'.$codebar.'.barcode.php');

        // 加载字体
        $path = str_replace('/', '\\', str_replace('///', '\\', dirname(__FILE__)));
        $font = new \BCGFont($path.'/class/font/Arial.ttf',10);  //因为这里的引入是相对于入口文件index.php，所以给个完整路径
        // The arguments are R, G, B for color.
        $color_black = new \BCGColor(0, 0, 0);
        $color_white = new \BCGColor(255, 255, 255);

        $code = new $codebar();
        $code->setScale(2); // Resolution
        $code->setThickness(30); // Thickness
        $code->setForegroundColor($color_black); // Color of bars
        $code->setBackgroundColor($color_white); // Color of spaces
        $code->setFont($font); // Font (or 0)
        $code->parse($text);

        /* Here is the list of the arguments
        1 - Filename (empty : display on screen)
        2 - Background color */
        $drawing = new \BCGDrawing('', $color_white);
        $drawing->setBarcode($code);
        $drawing->draw();

        // Header that says it is an image (remove it if you save the barcode to a file)
        header('Content-Type: image/png');
        // Draw (or save) the image into PNG format.
        $drawing->finish();
    }
}