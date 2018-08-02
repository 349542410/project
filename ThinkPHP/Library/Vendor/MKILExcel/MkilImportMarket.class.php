<?php
/**
 * 导出/导入功能
 */
// namespace Admin\Controller;
class MkilImportMarket{

	//默认导入文件信息为空白数组
	private $config = array();

	public function __construct($config = array()){
		/* 获取文件信息 */
        $this->config = array_merge($this->config, $config);
        // dump($config);
	}

	public function import(){
		$filename = $this->config['file']['name'];
		// return $filename;
		//获取上传文件的扩展名
		$ect = strrchr($filename,'.');	// $ect = explode('.',$filename);

		if (empty ($filename)) {
			return $result = array('status'=>'0','msg'=>'请选择要导入的csv或Excel文件！');
		}
		if(!in_array($ect,array('.csv','.xls','.xlsx'))){
			return $result = array('status'=>'0','msg'=>'请导入合法的csv或Excel文件！');
		}

		return $this->excel($this->config,$ect);
/*	以下是自定义写的导入csv方法 20160127 Jie 暂不使用
		if($ect == 'csv'){
			$handle = fopen($this->config['file']['tmp_name'], 'r');
			$arr    = $this->input_csv($handle); //解析csv
			fclose($handle); //关闭指针
			$len_result = count($arr);
			if($len_result == 0){
				return $result = array('status'=>'0','msg'=>'没有任何数据！');
			}
			return $arr;
		}else{
			$this->excel($this->config,$ect);
		}*/
	}

	/**
	 * 使用PHPExcel进行csv或者Excel的导入
	 * @param  [type] $file [description]
	 * @param  [type] $ect  [description]
	 * @return [type]       [description]
	 */
	protected function excel($file,$ect){
	    require_once 'Classes/PHPExcel.php';
	    require_once 'Classes/PHPExcel/IOFactory.php';

	    if($ect == '.xls'){
	    	require_once 'Classes/PHPExcel/Reader/Excel5.php';
	    	$objReader     = PHPExcel_IOFactory::createReader('Excel5');
	    }else if($ect == '.xlsx'){
	    	require_once 'Classes/PHPExcel/Reader/Excel2007.php';
	    	$objReader     = PHPExcel_IOFactory::createReader('Excel2007');
	    }else{
	    	require_once 'Classes/PHPExcel/Reader/CSV.php';
	    	$objReader     = PHPExcel_IOFactory::createReader('CSV');
	    }
		
		$objPHPExcel   = $objReader->load($file['file']['tmp_name']);
		$sheet         = $objPHPExcel->getSheet(0);
		$highestRow    = $sheet->getHighestRow();           //取得总行数
		$highestColumn = $sheet->getHighestColumn(); 	//取得总列数
		
		$arr =array();
	    for($j=1;$j<=$highestRow;$j++)                        //从第一行开始读取数据
        {	
        	for($k='A';$k<=$highestColumn;$k++)            //从A列读取数据
            {
            	$cellval = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
        		if(trim($cellval) != '') $arr[$j-1][] = trim($cellval);		//读取单元格并拼入到数组中
        	}

        }
	    // dump($strs);die;
	    return $arr;
	}

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