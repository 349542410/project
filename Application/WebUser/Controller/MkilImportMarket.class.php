<?php
/**
 * 导出/导入功能	
 * 添加默认不保留空白数据的单元格(即Model_Type=1)  20160329 Jie
 */
// namespace Admin\Controller;
class MkilImportMarket{

	//默认导入文件信息为空白数组
	private $config = array(
		'Model_Type' => '1',	//20160329 Jie 用于判断是否从文档的第一行开始读取，1表示默认是;另一个作用是，如果为1的时候，读取文档的所有单元格，即使该单元格为空
	);

	public function __construct($config = array()){
		/* 获取文件信息 */
        $this->config = array_merge($this->config, $config);
        // dump($config);
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

	public function import(){
		$filename = $this->config['file']['name'];
		$Model_Type = $this->config['Model_Type'];
		// dump($Model_Type);
		//获取上传文件的扩展名
		$ect = strrchr($filename,'.');	// $ect = explode('.',$filename);

		if (empty ($filename)) {
			return $result = array('status'=>'0','msg'=>'请选择要导入的csv或Excel文件！');
		}
		if(!in_array($ect,array('.csv','.xls','.xlsx'))){
			return $result = array('status'=>'0','msg'=>'请导入合法的csv或Excel文件！');
		}

		return $this->excel($this->config,$ect,$Model_Type);
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
	 * @param  [type] $file [导入文档]
	 * @param  [type] $ect  [导入文件的后缀类型]
	 * @return [type]       [description]
	 */
	protected function excel($file,$ect,$Model_Type){
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

		// $Model_Type 用于执行从第几行开始读取数据，默认是从1开始 20160329 Jie
	    for($j=$Model_Type;$j<=$highestRow;$j++)                        //从第一行开始读取数据
        {	
        	for($k='A';$k<=$highestColumn;$k++)            //从A列读取数据
            {
            	$cellval = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
            	if($Model_Type == '1'){
            		if(trim($cellval) != '') $arr[$j-1][] = trim($cellval);		//读取单元格并拼入到数组中，单元格为空的则不读取 20160329 Jie
            	}else{
            		$arr[$j][] = trim($cellval);		//读取文档的所有单元格并拼入到数组中，即使该单元格为空 20160329 Jie
            	}

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