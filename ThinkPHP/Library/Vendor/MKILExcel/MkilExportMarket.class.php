<?php
/**
 * 导出/导入功能  jie
 * 功能：导出csv、xls、xlsx三种类型的文档
 * 修改日期：2017-04-26
 * 新增配置选项：Sort、OutPut、Title_Style
 */
// namespace Admin\Controller;
class MkilExportMarket{

    //默认配置
    protected $config = array(
        'SaveName'    => '',          //文件路径+文件名  必需
        'Title'       => '',          //csv表头  必需
        'Data'        => '',          //需要导出的数据数组  必需
        'Format'      => 'csv',       //默认导出文件为csv，导出的文件类型,以此作为判断是导出excel2003,2007,csv
        'Clear_List'  => array(),     //默认执行清空值的字段。外部$config不传入此项参数，则表示没有需要清空的字段
        'Model_Type'  => '1',         //导出csv的模板类型，默认1为申通csv模式(全部单元格都填写)，2为顺丰csv模式(内容相同的单元格则忽略为空)
        'Sort'        => true,        //是否带序号。默认带有序号(Data需要传入f0字段)；不带序号的从f1开始记数
        'OutPut'      => false,       //是否直接输出到浏览器。如果是导出到指定目录，SaveName必须为文件路径+文件名；如果是浏览器，SaveName只需是文件名
        'Title_Style' => false,       //单元格表头是否需要样式设计。默认不需要样式
    );

    public function __construct($config = array()){
        /* 获取配置 */
        // $this->config = array_merge($this->config, $config);
    }

    /**
    * 使用 $this->name 获取配置
    * @access public     
    * @param  string $name 配置名称
    * @return multitype    配置值
    */
    public function __get($name) {
        return $this->config[$name];
    }

    /**
    * 设置邮件配置
    * @access public     
    * @param  string $name 配置名称
    * @param  string $value 配置值     
    * @return void
    */
    public function __set($name,$value){
        if(isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
    * 检查配置
    * @access public     
    * @param  string $name 配置名称
    * @return bool
    */
    public function __isset($name){
        return isset($this->config[$name]);
    }

    //判断行为
    public function export(){
        if($this->Format == 'csv'){
            return $this->exportCSV();
        }else{
            return $this->exportExcel();
        }
    }

//===================================== 导出CSV ==================================
    //导出csv
	public function exportCSV(){

        $this->check($this->config);
        $str   = array();
        $str[] = $this->Title;  //表头
        $arr   = $this->Data;   //数据数组

        //执行模板二类输出csv,否则默认执行输出模板一
        if($this->Model_Type == '2'){
            $arr = $this->typeTwo($arr);
        }else{  //默认执行输出模板一
            $arr = $this->typeOne($arr);
        }

        //数组转为字符串 以一维数组装载
        foreach($arr as $v){
            $str[] = implode(",",$v); 
        }
        
        foreach ($str as $kp => $vo) { 
        // CSV的Excel支持GBK编码，一定要转换，否则乱码 
            $str[$kp] = iconv('utf-8', "gbk//IGNORE", $vo); 
        }

        // $this->SaveName .= '.csv';  //拼接后缀
        $fileurl  = $this->SaveName.'.csv';    //文件路径，包括文件名  拼接后缀
        // $filename = basename($fileurl); //文件名
        // true表示允许创建多级目录。
        $fileDir  = dirname($fileurl); //文件路径，不包括文件名
        if (!is_dir($fileDir)) mkdir($fileDir, 0777, true);       //以该用户名为名字新建一个文件夹，如果文件夹不存在则创建，否则不创建

        // dump($fileDir);die;
        $fp = fopen($fileurl, 'w+');

		foreach ($str as $line) {
			fputcsv($fp, split(',', $line));	//写入数据到csv文件
		}
		fclose($fp);
        return true;
	}

    //模板一(申通适用 全部单元格都填写)
    public function typeOne($arr){
        $p   = 1;   //序号
        foreach($arr as $k=>$it){
            unset($arr[$k]['f1']);

            //判断是否需要带序号，如果不需要带序号，f0不需要填写
            if($this->Sort === true){
                $arr[$k]['f0'] = $p;
            }
            $p++;
        }
        return $arr;
    }

    //模板二(顺丰适用 相同内容的单元格为空)
    public function typeTwo($arr){
        $fid = 0;   //
        $dd  = 0;   //
        $p   = 1;   //序号
        foreach($arr as $k=>$it){
            $dd  = $it['f1'] == $fid ? $dd : 0;
            $fid = $it['f1'] == $fid ? $fid : $it['f1'];

            unset($arr[$k]['f1']);

            if($dd == 0){
                //判断是否需要带序号，如果不需要带序号，f0不需要填写
                if($this->Sort === true){
                    $arr[$k]['f0'] = $p;      //序号
                }
                $dd++;
                $p++;
            }else{
                foreach($this->Clear_List as $cont){
                    $arr[$k][$cont] = '';
                }
                $dd++;
            }
        }
        return $arr;
    }

//=========================================== 导出Excel2003,Excel2007 =======================================

    /**
     * Excel2003或Excel2007 导出
     * @return [type]
     */
    public function exportExcel(){
        $this->check($this->config);
        require_once 'Classes/PHPExcel.php';
        require_once 'Classes/PHPExcel/IOFactory.php';
        require_once 'Classes/PHPExcel/Writer/Excel5.php';     // 用于其他低版本xls
        require_once 'Classes/PHPExcel/Writer/Excel2007.php'; // 用于 excel-2007 格式
        $objExcel = new \PHPExcel();

        $title = explode(',',$this->Title);
        //设置文档基本属性
        $objProps = $objExcel->getProperties();

        // //设置属性 (这段代码无关紧要，其中的内容可以替换为你需要的)  
        // $objExcel->getProperties()->setCreator("andy");  
        // $objExcel->getProperties()->setLastModifiedBy("andy");  
        // $objExcel->getProperties()->setTitle("Office 2003 XLS Test Document");  
        // $objExcel->getProperties()->setSubject("Office 2003 XLS Test Document");  
        // $objExcel->getProperties()->setDescription("Test document for Office 2003 XLS, generated using PHP classes.");  
        // $objExcel->getProperties()->setKeywords("office 2003 openxml php");  
        // $objExcel->getProperties()->setCategory("Test result file");  
        // $objExcel->setActiveSheetIndex(0);

        //设置当前的sheet索引，用于后续的内容操作。
        //一般只有在使用多个sheet的时候才需要显示调用。
        //缺省情况下，PHPExcel会自动创建第一个sheet被设置SheetIndex=0
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

        //设置当前活动sheet的名称
        $objActSheet->setTitle('Sheet1');

/*        //保护cell  开启则用户不可以编辑该文档的内容
        $objExcel->getActiveSheet()->getProtection()->setSheet(true); // Needs to be set to true in order to enable any worksheet protection!
        $objExcel->getActiveSheet()->protectCells('A3:E13', 'PHPExcel');*/

        if($this->Model_Type == '2'){
            $arr = $this->typeTwo($this->Data);
        }else{  //默认执行输出模板一
            $arr = $this->typeOne($this->Data);
        }

        // 设置单元格表头
        $rany = range('A','Z');
        foreach($title as $k=>$item){
            // dump($rany[$k].('1').'=>'.$item);
            /*----------写入单元格表头-------------*/
            $objExcel->getActiveSheet()->setCellValue(($rany[$k].('1')), $item);    //A1-Z1之间赋予表头

            //单元格表头是否需要样式设计
            if($this->config['Title_Style'] === true){
                $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getFont()->setName('宋体');//字体种类
                $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getFont()->setSize(10);//字体大小
                // $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getFont()->setBold(true);//粗体字
                // $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);//下划线
                // $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);//字体颜色

                //设置align
                $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                //垂直居中
                $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                //设置column的border
                $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

                //设置border的color
                $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getBorders()->getLeft()->getColor()->setARGB('FF993300');
                $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getBorders()->getTop()->getColor()->setARGB('FF993300');
                $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getBorders()->getBottom()->getColor()->setARGB('FF993300');
                $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getBorders()->getRight()->getColor()->setARGB('FF993300');

                //设置填充颜色
                $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objExcel->getActiveSheet()->getStyle(($rany[$k].('1')))->getFill()->getStartColor()->setARGB('FF808080');
            }

            //设置宽度
            // $objActSheet->getColumnDimension($rany[$k])->setAutoSize(true);
            $objActSheet->getColumnDimension($rany[$k])->setWidth(13);
        }
        // echo '<br>';
        // 写入单元格内容
        $j = 0;
        foreach($arr as $k=>$v) {
            // dump($v);
            $v = array_values($v);
            for ($i=0; $i <= (count($v)-1); $i++) {
                $ul = $j+2;
                // dump(($rany[$i].$ul).'=>'.$v[$i]);
                /*----------写入内容-------------*/
                $objExcel->getActiveSheet()->setCellValue(($rany[$i].$ul), ' '.$v[$i]); //A2(...)-Z2(...)之间赋值
            }
            // echo '<br>';
            $j++;
        }

        // die;

        //合并单元格
        // $objActSheet->mergeCells('E2:E16');

        //分离单元格
        // $objActSheet->unmergeCells('B1:C22');

        //导出文件保存到指定目录
        if($this->config['OutPut'] === false){

            $fileurl  = $this->SaveName;    //文件路径+包括文件名

            // return pathinfo($fileurl);
            // 如果在 path 中没有斜线，则返回一个点（'.'），表示当前目录；但我们需要的是详细的相对路径，不能用当前目录作为储存位置
            if(pathinfo($fileurl)['dirname'] == '.'){
                return 'Error：[SaveName]中缺少必要的文件储存路径';
            }

            // 根据路径创建必需的文件夹目录
            $fileDir  = dirname($fileurl); //文件路径，不包括文件名
            if (!is_dir($fileDir)) mkdir($fileDir, 0777, true);       //true表示允许创建多级目录。

            if($this->Format == '2007'){
                $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
                $objWriter->setOffice2003Compatibility(true);
                //输出到文件
                $objWriter->save($this->SaveName.'.xlsx');
            }else{
                $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
                //输出到文件
                $objWriter->save($this->SaveName.'.xls');
            }

            return true;
        
        }else{//输出到浏览器

            //检查是否存在文件路径，删除文件路径，保留文件名
/*            $fileDir  = dirname($this->SaveName); //文件路径，不包括文件名
            $outputFileName = str_replace($fileDir,'',$this->SaveName); //得到文件名*/
            $outputFileName = basename($this->SaveName); //获取文件名

            if($this->Format == '2007'){
                $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
                $objWriter->setOffice2003Compatibility(true);
                //文件名+后缀
                $filename = $outputFileName.'.xlsx';
            }else{
                $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
                //文件名+后缀
                $filename = $outputFileName.'.xls';
            }

            //到浏览器
/*            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Disposition:inline;filename="'.$filename.'"');
            header("Content-Transfer-Encoding: binary");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            $objWriter->save('php://output');*/
            //or
            //到浏览器
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');
            $objWriter->save('php://output');
            exit;
        }
        
    }

    /**
    * 检查
    * @param  [type] $name [description]
    * @return [type]       [description]
    */
    public function check(){
        //校验是否存在或为空
        if(trim($this->config['SaveName']) == ''){
            die('[SaveName]未配置');
        }
        // if(trim($this->config['SavePath']) == ''){
        //     die('[SavePath]未配置');
        // }
        if(trim($this->config['Title']) == ''){
            die('[Title]未配置');
        }
        if(count($this->config['Data']) < 1){
            die('[Data]数组数据缺失');
        }
        if(trim($this->config['Format']) == ''){
            die('[Format]未配置');
        }
    }

}