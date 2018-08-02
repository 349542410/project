<?php
/**
 * 导入功能 v2.0
 * 功能：导入csv、xls、xlsx三种类型的文档
 * 修改日期：2018-01-09
 * 新增配置选项：inputFileName
 */
namespace Libm\MKILExcel;
class MkilImportMarket{

	private $inputFileType; //文件类型
	//默认导入文件信息为空白数组
	private $config = array(
		'inputFileName' => '',//文件路径+文件名  必需
	);
	
	public $Error;
	public $excelData;

	public function __construct($config = array()){
        // 引用PHPexcel 类
        include_once(dirname(__FILE__) . '/Classes/PHPExcel.php');  
        include_once(dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php');
        
        foreach ($this->config as $name => $value) $this->$name = $value;
	}

	public function import(){
		$this->inputFileType = \PHPExcel_IOFactory::identify($this->inputFileName);         //检测类型
		// 设置 上传文件类型限制
		$allow = array(
			'CSV',//       =>'.csv',
			'Excel5',//    =>'.xls',
			'Excel2007',// =>'.xlsx',
		);

		//strrchr 查找字符串在另一个字符串中最后一次出现的位置，并返回从该位置到字符串结尾的所有字符
		// $ect = strrchr($filename,'.');	// $ect = explode('.',$filename);

		if (empty ($this->inputFileName)) {
			$this->Error = '文件不存在';
			return false;
		}
		if(!in_array($this->inputFileType, $allow)){
			$this->Error = '请选择要导入的CSV或Excel文件';
			return false;
		}
		
		return $this->excel();
	}

	/**
	 * 使用PHPExcel进行csv或者Excel的导入
	 * @param  [type] $file [description]
	 * @param  [type] $ect  [description]
	 * @return [type]       [description]
	 */
	protected function excel(){
/*	    require_once 'Classes/PHPExcel.php';
	    require_once 'Classes/PHPExcel/IOFactory.php';
	    if($ect == '.xls'){
	    	require_once 'Classes/PHPExcel/Reader/Excel5.php';
	    	$objReader     = \PHPExcel_IOFactory::createReader('Excel5');
	    }else if($ect == '.xlsx'){
	    	require_once 'Classes/PHPExcel/Reader/Excel2007.php';
	    	$objReader     = \PHPExcel_IOFactory::createReader('Excel2007');
	    }else{
	    	require_once 'Classes/PHPExcel/Reader/CSV.php';
	    	$objReader     = \PHPExcel_IOFactory::createReader('CSV');
	    }*/

	    // \set_error_handler(array('PHPExcel_Exception','errorHandlerCallback'));
		try{
			$objReader = \PHPExcel_IOFactory::createReader($this->inputFileType);         //创建excel对象
			$objReader->setReadDataOnly(true);	//设置只读（加快速度）
			$objPHPExcel   = $objReader->load($this->inputFileName);//载入文件
			$sheet         = $objPHPExcel->getSheet(0);
			$highestRow    = $sheet->getHighestRow();           //取得总行数
			$highestColumn = $sheet->getHighestColumn(); 	//取得总列数
			$highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
		}catch(\PHPExcel_Exception $e){
            $this->Error = $e->getMessage();
            return false;
		}

/*		$excelData =array();
		for($j=1;$j<=$highestRow;$j++)                        //从第一行开始读取数据
        {	
        	for($k='A';$k<=$highestColumn;$k++)            //从A列读取数据
            {
            	$cellval = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
        		if(trim($cellval) != '') $excelData[$j-1][] = trim($cellval);		//读取单元格并拼入到数组中
        	}

        }*/
		$excelData = array ();
		for($row = 1; $row <= $highestRow; $row++) {
		    for($col = 0; $col < $highestColumnIndex; $col++) {
		        $excelData[$row-1][] = (string)$sheet->getCellByColumnAndRow( $col, $row )->getValue();
		    }
		}

	    return $excelData;
	}

    public function getError(){
        return $this->Error;
    }

//================
    /**
     * 20160127 Jie 暂不使用
     * 读取为数组的形式
     * @param  [type] $handle [description]
     * @return [type]         [description]
     */
	protected function input_csv($handle){
		$out = array ();
		$n = 0;
		while ($data = fgetcsv($handle, 10000)) {
			$num = count($data);
			for ($i = 0; $i < $num; $i++) {
				$out[$n][$i] = $data[$i];
			}
			$n++;
		}
		return $out;
	}
}