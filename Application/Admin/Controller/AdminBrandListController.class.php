<?php
namespace Admin\Controller;
use Think\Controller;
class AdminBrandListController extends AdminbaseController{
	public $client;
	
	function __construct(){
		parent::__construct();
		vendor('Hprose.HproseHttpClient');
        $this->client = new \HproseHttpClient(C('RAPIURL').'/AdminBrandList');		//读取、查询操作
        //$this->writes = new \HproseHttpClient(C('WAPIURL').'/AdminCollectPoint');		//添加，修改 
        
	}
	
	/**
	 * 品牌列表
	 * Enter description here ...
	 */
	public  function index(){

		$data['epage'] = C('EPAGE');
		$data['p'] = I('get.p');
		//$map['a'] =array('like',array('%thinkphp%','%tp'),'OR');
		$name = I('get.name');
		$start_time = I('get.start_time');
		$end_time = I('get.end_time');
		$number   = I('get.number');
		if(!empty($name)){
			$name = trim($name, ' ');
			$name = trim($name, '，');
			$name = trim($name, ',');
			$data['bl.brand_name'] = array('like', array('%'.$name.'%'));
		}
		if(!empty($start_time)){
			//$start_time_number = strtotime($start_time);
			$stime[] = array('gt', $start_time);
			$data['bl.cre_time'] = $stime;
		}
		if(!empty($end_time)){
			//$end_time_number = strtotime($end_time);
			$stime[] = array('lt', $end_time);
			$data['bl.cre_time'] = $stime;
		}
		
		if(!empty($number)){
			$data['epage'] = $number;
		}
		
	
		$res = $this->client->index_list($data);
		//echo ADMIN_FILE;
		//exit;
		//print_r($res);
		//exit;
		$list = $res['list'];
		//var_dump($list);
		//exit;
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
	 * 品牌添加
	 * Enter description here ...
	 */
	public function add(){
		
		$this->display();	
	}
	
	/**
	 * 品牌添加处理
	 * Enter description here ...
	 */
	public function addhandle(){
		$name = I('post.name');
		$id = I('post.id');
		$cre_by = session('admin')['adid'];
		//$orders = I('post.orders');
		if(!empty($id)){
			$data['brand_name'] = $name;
			$data['id']  = $id;
			$data['cre_by'] = $cre_by;
			//$data['orders'] = $orders;
			$res = $this->client->addhandle($data);
			
			if(!$res['status'] && isset($res['status'])){
				$rew['status'] = 0;
	    		$rew['data']['strstr'] = '修改失败';
	    		//$rew['data']['url'] =U('AdminBrandList/index');
	    		$this->ajaxReturn($rew);
		    	exit;					
			}
			
			if($res){
				$rew['status'] = 1;
	    		$rew['data']['strstr'] = '修改成功！';
	    		//$rew['data']['url'] =U('AdminBrandList/index');
	    		$this->ajaxReturn($rew);
		    	exit;
			}else{
				
				$rew['status'] = 0;
	    		$rew['data']['strstr'] = '修改失败！';
	    		//$rew['data']['url'] =U('AdminBrandList/index');
	    		$this->ajaxReturn($rew);
		    	exit;
			
			}
		}else{
			
			//$name = str_replace('', ',', $name);
			$arr = explode("\r\n", $name);
			//删除空值
			$arr = array_filter($arr); //array_filter
			//删除重复值
			$arr = array_unique($arr);
			//处理两端空建
			foreach ($arr as $key => $val){
				if($val){
					$arr[$key] = trim($val, ' ');
				}
			}
			
			$wh['brand_name'] = array('in', $arr);
			//删除重复
			//print_r($wh);
			//exit;
			$wres = $this->client->edit_all($wh);
			//print_r($wres);
			//exit;
			if($wres){
				foreach ($wres as $k => $v){
					
					$r[] = $v['brand_name'];
				}
				//print_r($r);
				//exit;
				$arr = array_diff($arr, $r);
			}
			//print_r($arr);
			//exit;
			$i = 0;
			foreach ($arr as $key => $val){
				$data[$i]['brand_name'] = $val;
				$data[$i]['cre_by'] 	= $cre_by;
				//$data[$i]['orders']		= $orders;
				$i++;
			}
			
			$res = $this->client->addhandle($data);
			if(!$res['status'] && isset($res['status'])){
				$rew['status'] = 0;
	    		$rew['data']['strstr'] = $res['data']['strstr'];
	    		//$rew['data']['url'] =U('AdminBrandList/index');
	    		$this->ajaxReturn($rew);
		    	exit;					
			}
			
			if($res){
				$rew['status'] = 1;
	    		$rew['data']['strstr'] = '添加成功！';
	    		//$rew['data']['url'] =U('AdminBrandList/index');
	    		$this->ajaxReturn($rew);
		    	exit;
			}else{
				$rew['status'] = 0;
	    		$rew['data']['strstr'] = '添加失败！';
	    		//$rew['data']['url'] =U('AdminBrandList/index');
	    		$this->ajaxReturn($rew);
		    	exit;
			
			}
		}
		
	}
	
	/**
	 *	品牌名称修改 
	 */
	public function edit(){
		$where['id'] = I('get.id');
		if(empty($where['id'])){
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '品牌名称不存在！';
    		//$rew['data']['url'] =U('AdminBrandList/index');
    		$this->ajaxReturn($rew);
	    	exit;
		}
		
		$data = $this->client->edit($where);
		$this->assign('data', $data);
		$this->display();
	}
	
	/**
	 * 品牌名称删除
	 * Enter description here ...
	 */
	public function delete(){
		$where['id'] = I('get.id');
		if(empty($where['id'])){
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '品牌名称不存在！';
    		//$rew['data']['url'] =U('AdminBrandList/index');
    		$this->ajaxReturn($rew);
	    	exit;
		}
		
		$res = $this->client->delete($where);	
		if($res){
			$rew['status'] = 1;
    		$rew['data']['strstr'] = '品牌名称删除成功！';
    		//$rew['data']['url'] =U('AdminBrandList/index');
    		$this->ajaxReturn($rew);
	    	exit;
		}else{
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '品牌名称删除失败！';
    		//$rew['data']['url'] =U('AdminBrandList/index');
    		$this->ajaxReturn($rew);
	    	exit;
		
		}
	
	}
	
	
	
	
	
	/**
	 * 品牌csv导入
	 * Enter description here ...
	 */
	public function import(){
	
		$this->display();
	}
	
	/**
	 * 导入处理
	 * Enter description here ...
	 */
	public function importhandle(){
	    $filename = $_FILES['file_stu']['tmp_name'];
	    //$orders = I('post.orders');
		$cre_by = session('admin')['adid'];
	    if (empty ($filename)) {
	        $rew['status'] = 0;
    		$rew['data']['strstr'] = '请选择要导入的CSV文件！';
    		//$rew['data']['url'] =U('AdminBrandList/index');
    		$this->ajaxReturn($rew);
	    	exit;
	    }
		$file_types = explode(".", $_FILES['file_stu']['name']);
	    $file_type = $file_types [count($file_types) - 1];
	
	    /*判别是不是.xls文件，判别是不是excel文件*/
	    if (strtolower($file_type) != "csv") {
	        $rew['status'] = 0;
    		$rew['data']['strstr'] = '不是CSV文件，重新上传';
    		//$rew['data']['url'] =U('AdminBrandList/index');
    		$this->ajaxReturn($rew);
	    	exit;
	    }
	    // echo $filename;
	    // echo '<br/>';
	    $handle = fopen($filename, 'r');
	    // $handle = fopen('aaaa.csv', 'r');
	    $result = $this->input_csv($handle); //解析csv

	    $len_result = count($result);
	    if($len_result==0){
	        $rew['status'] = 0;
    		$rew['data']['strstr'] = '没有任何数据！';
    		//$rew['data']['url'] =U('AdminBrandList/index');
    		$this->ajaxReturn($rew);
	    	exit;
	    }
	    
	   	//删除第0行数据
	   	unset($result[0]);

        $k = 1;
	   	$rek = array();
	   	foreach ($result as $key => $val){
	   		if(!empty($val[0])){
	   		    if (empty($rek)){
	   		        $rek[] = trim($val['0'], ' ');

                }else{
                    if(!in_array($val['0'], $rek)){
                        $rek_str = implode(',', $rek);
                        $str = $val['0'];
                        if(strpos($rek_str, $str) <= 0){
                            $rek[] = trim($val['0'], ' ');
                        }
                    }else{
                        $aa[] = $val['0'];
                    }

                }


	   		}
	   		//$rew[$i]['brand_name'] = $val[0];
	   		//$rew[$i]['cre_by'] = session('admin')['adid'];
	   		//$rew[$i]['orders'] = $orders;
	   		
	   	}
	   	//echo $k;
	   	//exit;
//	   	$a = 'Peter Thomas Roth';
//	   	$b = array('Peter Thomas Roth','sds');
//	   	if (!in_array($a, $b)){
//	   	    echo 'dfg';
//        }else{
//	   	    echo '234543';
//
//        }
//       print_r($rek);
//	   	exit;
        array_unique($rek);
//        print_r($rek);
//        exit;
//        echo 'asasdf';
//        print_r($rek);
//        if($rek['431'] == $rek['432']){
//            echo 'sdgfsdfg';
//            exit;
//        }
//	   	exit;
		//删除空值
		//$arr = array_filter($rek); //array_filter
		//删除重复值
		//$arr = array_unique($arr);
		//处理两端空建
		//foreach ($arr as $key => $val){
		//	if($val){
		//		$arr[$key] = trim($val, ' ');
		//	}
		//}
		$wh['brand_name'] = array('in', $rek);

	   	$wres = $this->client->edit_all($wh);

	   	if($wres){
			foreach ($wres as $k => $v){
				
				$r[] = $v['brand_name'];
			}
			//print_r($r);
			//exit;
            if (!empty($r)){
                $arr = array_diff($rek, $r);
            }else{
			    $arr = $rek;
            }

		}else{
            $arr = $rek;
        }

	   	//$data['wh'] = $rek;
	   	//$data['da'] = $arr;
	   	$i = 0;
	   	foreach($arr as $key => $val){
	   		$data[$i]['brand_name'] = $val;
	   		$data[$i]['cre_by'] = $cre_by;
	   		//$data[$i]['orders'] = $orders;
	   		$i++;
	   	}
        $res = $this->client->importhandle($data);

//	   	if(!$res['status']){
//			$rew['status'] = 0;
//    		$rew['data']['strstr'] = $res['data']['strstr'];
//    		//$rew['data']['url'] =U('AdminBrandList/index');
//    		$this->ajaxReturn($rew);
//	    	exit;					
//		}
//		
		if($res){
			$rew['status'] = 1;
    		$rew['data']['strstr'] = '添加成功！';
    		//$rew['data']['url'] =U('AdminBrandList/index');
    		$this->ajaxReturn($rew);
	    	exit;
		}else{
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '添加失败！';
    		//$rew['data']['url'] =U('AdminBrandList/index');
    		$this->ajaxReturn($rew);
	    	exit;
		
		}	   	
   	
	    
	}

	/**
	 * 将csv数据转成数组
	 * Enter description here ...
	 * @param $handle
	 */
	public function input_csv($handle) {
	    $out = array ();
	    $n = 0;
	    while ($data = fgetcsv($handle, 10000)) {
	        $num = count($data);
	        for ($i = 0; $i < $num; $i++) {
	        	$name = iconv('GB2312', 'UTF-8', $data[$i]);
	        	$name = htmlspecialchars($name);
	            $out[$n][$i] = $name; 
	        }
	        $n++;
	    }
	    return $out;
	}	
	
}	
	