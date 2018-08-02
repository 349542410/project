<?php
namespace Admin\Controller;
use Think\Controller;
class AdminTaxhscodeController extends AdminbaseController{
	public $client;
	//public $writes;
	
	function _initialize(){
		parent::_initialize();
		//vendor('Hprose.HproseHttpClient');
        $this->client = new \HproseHttpClient(C('RAPIURL').'/AdminTaxhscode');		//读取、查询操作
        //$this->writes = new \HproseHttpClient(C('WAPIURL').'/AdminCollectPoint');		//添加，修改 
       
	}
	
	/**
	 * 海关税费列表
	 * Enter description here ...
	 */
	public function taxlist(){
		$data['epage'] = C('EPAGE');
		$data['p'] = I('get.p');
		$code = I('get.code');
		$name = I('get.name');
		$number   = I('get.number');
		$status = I('get.status');
		if(!empty($status)){
			$status = ($status == 1) ? 0 : $status;
			$data['tax_sale_status'] = $status;
		}
		
		if(!empty($code)){
			$code = trim($code, ' ');
			$data['hs_code'] = $code;
		}
		if(!empty($name)){
			$name = trim($name, ' ');
			$data['hs_name'] = array('like', '%'.$name.'%');
		}
		if(!empty($number)){
			$data['epage'] = $number;
		}		
		
		$res = $this->client->taxlist($data);
		
		$list = $res['list'];
		$count = $res['count'];
		$page=new \Think\Page($count,$data['epage']);
		//%FIRST% 表示第一页的链接显示 
		//%UP_PAGE% 表示上一页的链接显示 
		//%LINK_PAGE% 表示分页的链接显示 
		//%DOWN_PAGE% 表示下一页的链接显示 
		//%END% 表示最后一页的链接显示 
		
		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
		$page -> setConfig('prev', '上一页');
		$page -> setConfig('next','下一页');
		$page -> setConfig('last','末页');
		$page -> setConfig('first','首页');
		
		$show = $page->show();
		
		$this->assign('page',$show);// 赋值分页输出
		$this->assign('list',$list);
		$this->display();
		
	}
	
	/**
	 * 海关税费添加
	 * Enter description here ...
	 */
	public function taxadd(){
		
		$this->display();
	}
	
	
	/**
	 *  海关税费修改
	 * Enter description here ...
	 */
	public function taxedit(){
		$id = I('get.id');
		if(empty($id)){
			$this->assign('content', '参数');
			$cont = $this->fetch('Public:hints');
	    	//$cont = '您没有操作权限';
	    	echo $cont;
	    	exit;
		}
		$w['id'] = $id;
		$res = $this->client->taxedit($w);
		
		$this->assign('res', $res);
		$this->display('taxadd');
	}
	
	
	/**
	 * 海关税费添加处理
	 * Enter description here ...
	 */
	public function taxhandle(){
//		$data = I('post.');
//		print_r($data);
//		exit;
		$code = I('post.code');
		$name = I('post.name');
		$hs_unit1 = I('post.hs_unit1');
		$hs_unit2 = I('post.hs_unit2');
		$tax_sale = sprintf("%.4f",I('post.tax_sale'));
		$tax_added = sprintf("%.4f",I('post.tax_added'));
		$tax_tariff = sprintf("%.4f",I('post.tax_tariff'));
		$status = I('post.status');
		$id = I('post.id');
		//检验HS编号是否唯一
		$w['hs_code'] = $code;
		if(!empty($id)){
			$w['id'] = array('neq', $id);
		}
		
		$res = $this->client->hs_code($w);
		
		if($res){
     		$rew['status'] = 0;
    		$rew['data']['strstr'] = 'HS编号已存在';
    		//$rew['data']['url'] = U('AdminAuth/index_authNav');
    		$this->ajaxReturn($rew);
	    	exit;   
		}
		$data['hs_code'] 			= $code;
		$data['hs_name'] 			= $name;
		$data['hs_unit1'] 			= $hs_unit1;
		$data['hs_unit2'] 			= $hs_unit2;
		$data['tax_sale'] 			= $tax_sale;
		$data['tax_added'] 			= $tax_added;
		$data['tax_tariff'] 		= $tax_tariff;
		$data['tax_sale_status'] 	= $status;
		
		if(empty($id)){
			
			$res = $this->client->taxhandle($data);
			
			if($res){
				$rew['status'] = 1;
	    		$rew['data']['strstr'] = '添加成功！';
	    		$rew['data']['url'] = U('AdminTaxhscode/taxlist');
	    		$this->ajaxReturn($rew);
		    	exit;			
			
			}else{
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '添加失败！';
	    		//$rew['data']['url'] = U('AdminTaxhscode/taxlist');
	    		$this->ajaxReturn($rew);
		    	exit;	
			}
		}else{
			$data['id'] = $id;
			
			$res = $this->client->taxhandle($data);
			if($res){
				$rew['status'] = 1;
	    		$rew['data']['strstr'] = '修改成功！';
	    		$rew['data']['url'] = U('AdminTaxhscode/taxlist');
	    		$this->ajaxReturn($rew);
		    	exit;			
			
			}else{
	     		$rew['status'] = 0;
	    		$rew['data']['strstr'] = '修改失败！';
	    		$rew['data']['url'] = U('AdminTaxhscode/taxlist');
	    		$this->ajaxReturn($rew);
		    	exit;	
			}		
		}
		
		
	}
	
	
	/**
	 * 海关税费删除
	 * Enter description here ...
	 */
	public function taxdelete(){
		$id = I('get.id');
		if(empty($id)){
			$this->assign('content', '参数');
			$cont = $this->fetch('Public:hints');
	    	//$cont = '您没有操作权限';
	    	echo $cont;
	    	exit;			
		}
		$w['id'] = $id;
		$res = $this->client->taxdelete($w);
		if($res){
			$rew['status'] = 1;
    		$rew['data']['strstr'] = '删除成功！';
    		$rew['data']['url'] = U('AdminTaxhscode/taxlist');
    		$this->ajaxReturn($rew);
	    	exit;			
		
		}else{
				$rew['status'] = 0;
	    		$rew['data']['strstr'] = '删除失败！';
	    		$rew['data']['url'] = U('AdminTaxhscode/taxlist');
	    		$this->ajaxReturn($rew);
		    	exit;			
					
		
		}
		
		
	}
	
	
	/**
	 * 导入XLS海关税费
	 * Enter description here ...
	 */
	public function taximport(){
		
		$this->display();
	}

	/**
	 * 导入XLS海关税费
	 */
	public function taximporthandle(){
	    /******************导入文件处理*******************/
	    
		$tmp_file = $_FILES['file_stu']['tmp_name'];
	    $file_types = explode(".", $_FILES['file_stu']['name']);
	    $file_type = $file_types [count($file_types) - 1];
	
	    /*判别是不是.xls文件，判别是不是excel文件*/
	    if (strtolower($file_type) != "xlsx" && strtolower($file_type) != "xls") {
	        $this->error('不是Excel文件，重新上传');
	        exit;
	    }
	
	    /*设置上传路径*/
	    $savePath = C('UPLOAD_DIRS');
	    /*以时间来命名上传的文件*/
	    $str = date('Ymdhis');
	    $file_name = $str . "." . $file_type;
		$file_n =  $savePath . $file_name;
		
		$A  = $this->copyfiles($tmp_file, $file_n);
		if (!$A) {
	        $this->error('上传失败');
	    }

	    /*是否上传成功*/
	    $res = $this->_readExcel($file_n);
	    //print_r($res);
	    //exit;
		
		$Q = array();
		$i = 0;
	    foreach ($res as $key => $val){
	    	if ($key > 0){
	    		trim($val['0'], ' ');
	    		
	    		if(!empty($val) && !in_array($val['0'], $Q)){
	    			$Q[] = $val['0'];
	    			
	    			//trim($val['0'], ' ');
	    			trim($val['1'], ' ');
	    			trim($val['2'], ' ');
	    			trim($val['3'], ' ');
	    			trim($val['4'], ' ');
	    			trim($val['5'], ' ');
	    			trim($val['6'], ' ');
	    			$data[$i]['hs_code'] 	= $val['0'];
	    			$data[$i]['hs_name'] 	= $val['1'];
	    			$data[$i]['hs_unit1'] 	= $val['2'];
	    			$data[$i]['hs_unit2'] 	= !empty($val['3']) ? $val['3']:'';
    				$data[$i]['tax_sale_status'] = '0';
	    			$data[$i]['tax_sale'] 	= sprintf("%.4f",$val['4']);
	    			$data[$i]['tax_added'] 	= sprintf("%.4f",$val['5']);
	    			$data[$i]['tax_tariff'] = sprintf("%.4f",$val['6']);
	    			

	    			
	    			$i++;
	    		}
	    		//else{
	    			//$prompt[$key]['errorstr'] = '第'.$key.'行存在空格行,请删除'; 
	    		//}
//	    		if($i > 3500){
//	    			break;
//	    		}
	    	}
	    }

	    set_time_limit(30);
//	    if(!empty($prompt)){
//	    	$rek['status'] = 1;
//	    	$rek['data'] = $prompt;
//	    	//print_r($rek);
//	    	//exit;
//	    	//$req = json_encode($rek);
//	    	$this->ajaxReturn($rek);
//	    	exit;
//	    }else{
	    	//清空数据库
	    	$w['id'] = '';
	    	$res = $this->client->taxdel($w);
	    	$k = 0;
	    	
	    	foreach ($data as $key => $val){
	    		
	    		if($k <= 500){
	    			$da[$k] = $val;
	    			if($i-1 == $key){
	    				$row[] = $this->client->taxAlladd($da);
	    			}
	    			$k++;
	    		}else{
	    			$da[$k] = $val;
	    			$row[] = $this->client->taxAlladd($da);
	    			$k = 0;
	    			$da = array();
	    			//$this->client->setTimeout(12000000000);
	    			sleep(0.00001);
	    			
	    		}
	    		
	    	}
//	    	print_r($data);
//	    	exit;
	    	//if($res){
	    		//$row = $this->client->taxAlladd($data);
	    		//print_r($row);
	    		//exit;
	    		if(!in_array(0, $row)){
					$rew['status'] = 1;
		    		$rew['data']['strstr'] = '添加成功!';
		    		$rew['data']['url'] = U('AdminTaxhscode/taxlist');
		    		$this->ajaxReturn($rew);
			    	exit;		
	    		}else{
					$rew['status'] = 0;
		    		$rew['data']['strstr'] = '添加失败!';
		    		$rew['data']['url'] = U('AdminTaxhscode/taxlist');
		    		$this->ajaxReturn($rew);
			    	exit;	    		
	    		}
//	    	}else{
//					$rew['status'] = 0;
//		    		$rew['data']['strstr'] = '添加失败!';
//		    		//$rew['data']['url'] = U('AdminAuth/index_authNav');
//		    		$this->ajaxReturn($rew);
//			    	exit;		    	
//	    	}
	    	
	    	//$this->ajaxReturn($rek);
	    	//exit;
//	    }
	    	
	}
	

    //创建一个读取excel数据，可用于入库  
    public function _readExcel($path)  
    {      
        //引用PHPexcel 类  
        include_once(str_replace('\\', '/', dirname(dirname(__FILE__)).'/PHPExcel/PHPExcel.php'));  
        include_once(str_replace('\\', '/', dirname(dirname(__FILE__)).'/PHPExcel/PHPExcel/IOFactory.php'));//静态类  
        $inputFileType = \PHPExcel_IOFactory::identify($path);         //检测类型
        //$type = 'Excel2007';//设置为Excel5代表支持2003或以下版本，Excel2007代表2007版  
        //$xlsReader = \PHPExcel_IOFactory::createReader($type);
        $xlsReader = \PHPExcel_IOFactory::createReader($inputFileType);    
        $xlsReader->setReadDataOnly(true);  
        $xlsReader->setLoadSheetsOnly(true);  
        $Sheets = $xlsReader->load($path);  
            //开始读取上传到服务器中的Excel文件，返回一个二维数组  
        $dataArray = $Sheets->getSheet(0)->toArray();  
        return $dataArray;  
    }    
    
	/**
	 * 复制文件
	 * Enter description here ...
	 * @param $file1
	 * @param $file2
	 */
	public function copyfiles($file1,$file2){
 		$contentx =@file_get_contents($file1); 
 		//return $contentx;
 		
  		$openedfile = fopen($file2, "w"); 
  		fwrite($openedfile, $contentx); 
  		fclose($openedfile); 
   		if ($contentx === FALSE) { 
   		$status=false; 
   		}else{$status=true;} 
  	 	return $status; 
  	}
  		
	
	
	
	

}
	
	