<?php
namespace Admin\Controller;
use Think\Controller;
class TransitPtwoController extends AdminbaseController{

	public function _initialize(){
		parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/TransitPtwo');		//读取、查询操作
        $this->client = $client;		//全局变量

        $power = session('power');
        $this->power = $power;		//全局变量

	}

	/**
	 * 中转线路 列表
	 * @return [type] [description]
	 */
	public function center(){

		//$power = $this->power;
        //if($power['view_Transitline'] != 'on'){
        //    $this->display('Public/msg');
        //    exit;
        //}

		$keyword            = trim(I('get.keyword'));
		$searchtype         = I('get.searchtype');
		$cid                = I('get.cid');		//是否需上传身份证明
		$optional           = I('get.optional');	//是否会员可选
		$status             = I('get.status');//状态
		$bc_state           = I('get.bc_state');	//是否需报关分类及报关货品管理
		$member_sfpic_state = I('get.member_sfpic_state');	//是否会员需上传身份证
		
		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');
		$this->assign($_GET);
		$this->assign('ePage',$ePage);
		
		if(!empty($keyword) && !empty($searchtype)){
			$where[$searchtype]=array('like','%'.$keyword.'%');
		}

		if($cid                != '') $where['tc.cid'] = $cid;
		if($optional           != '') $where['tc.optional'] = $optional;
		if($status             != '') $where['tc.status'] = $status;
		if($bc_state           != '') $where['tc.bc_state'] = $bc_state;
		if($member_sfpic_state != '') $where['tc.member_sfpic_state'] = $member_sfpic_state;

        $client = $this->client;
        $res = $client->_center($where,$p,$ePage);
		
		$count = $res['count'];
		$list  = $res['list'];

		$page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

		$page->setConfig('prev', "上一页");//上一页
		$page->setConfig('next', '下一页');//下一页
		$page->setConfig('first', '首页');//第一页
		$page->setConfig('last', "末页");//最后一页
		$page->setConfig( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
    		
		$show = $page->show(); // 分页显示输出
		$this->assign('page',$show);// 赋值分页输出
		
	    $this->assign('three_nav', $this->three_nav);
	    $this->assign('ModulesName', $this->ModulesName);
		$this->assign('list',$list);

		$this->display();
	}
	
	
    /*====================================线路地区开		start=======================================================================================*/
    /**
     * 线路地区列表
     * Enter description here ...
     */
    public function line_area(){
    	$line_id = I('id');
    	if(empty($line_id)){
    		$this->error('该路线不存在！');
    	}
    	$area_id = I('area_id');
    	if(!empty($area_id)){
    		$data['area_id'] = $area_id;
    	}
    	$data['line_id'] = $line_id;

    	$res = $this->client->line_area($data);
    	//print_r($res);
    	//exit;
    	$line = $res['line'];
    	$area = $res['zcode_line'];
    	$transit = $res['transit_center'];
	    //print_r($area);
	    //exit;
    	$three_view = $this->filesname;
	   	//print_r($this->three_nav);
	   	//exit;
    	$this->assign('line_id', $line_id);
	   	$this->assign('transit', $transit);
	    $this->assign('three_nav', $this->three_nav);
	    $this->assign('ModulesName', $this->ModulesName);
	    $this->assign('line', $line);
	    $this->assign('area', $area);

	    
    	$this->display();
    	
    	
    }

    
    /**
     * 线路地区列表添加
     * Enter description here ...
     */
    public function line_area_add(){
    	
    
    }
    

    /**
     * 线路地区列表添加处理
     * Enter description here ...
     */
    public function line_area_handle(){
    	$data = I('post.');
    	if(empty($data['id'])){
    		$this->error('线路不存在！');
    		exit;
    	}
    	
    	$res['line_id'] 	= $data['id'];
    	$res['name'] 		= $data['name'];
    	$res['alias_name'] 	= $data['alias_name'];
    	$res['status'] 		= $data['status'];
    	$res['zipcode'] 	= $data['zipcode'];
    	$res['pid'] 		= empty($data['pid']) ? 0 : $data['pid'];
    	if(empty($res['name']) || empty($res['zipcode'])){
    		$this->error('请输入名称与邮编！');
    	}
		if(!empty($data['line_zipcode'])){
			$res['id'] = $data['line_zipcode'];
		}
		
    	$rek = $this->client->line_area_handle($res);
		if($rek){
     		$rew['status'] = 1;
    		$rew['data']['strstr'] = '更新成功';
    		
    		//$result = array('state'=>'yes', 'msg'=>'更新成功');
    	}else{
     		$rew['status'] = 2;
    		$rew['data']['strstr'] = '更新失败';
    		//$this->ajaxReturn($rew);
	    	//exit; 
    		//$result = array('state'=>'no', 'msg'=>'更新失败');
    	}			
		    	
    	$this->ajaxReturn($rew);
//    	if($rek){
//    		$this->success('添加成功！', U('Transit/center'), 2);
//    	}else{
//    		$this->error('添加失败！');
//    	}
//    	
    }
    
    
    /**
     * 线路地区列表修改
     * Enter description here ...
     */
    public function line_area_edit(){
    	$line_id = I('get.id');
    	$area_id = I('get.area_id');
    	$data['area_id'] = $area_id;
    	$data['line_id'] = $line_id;
    	$res = $this->client->line_area_edit($data);
    	$this->assign('line', $res['line']);
    	$this->assign('area', $res['area']);
    	
    	$this->display('area_edit');
    	
    }    
    
    
    /**
     * 线路地区列表删除
     * Enter description here ...
     */
    public function line_area_delete(){
    	$line_id = I('get.id');
    	$zcode_id = I('get.zcode_id');
    	$data['line_id'] = $line_id;
    	$data['zcode_id'] = $zcode_id;
		//print_r($data);
		//exit;
	    set_time_limit(0);
    	$this->client->setTimeout(12000000000000);
    	$res = $this->client->line_area_delete($data);
    	set_time_limit(30);
    	//print_r($res);
    	//exit;
    	if($res){
     		$rew['status'] = 1;
    		$rew['data']['strstr'] = '线路地区删除成功';
    		$rew['data']['url'] = U('TransitPtwo/line_area', array('id' => $line_id));
    		$this->ajaxReturn($rew);
	    	exit;   		
    		//$this->error('线路地区删除成功！', U('Transit/line_area',array('id' => $line_id)));
    		//exit;
    	}else{
    		$rew['status'] = 2;
    		$rew['data']['strstr'] = '线路地区删除失败';
    		$this->ajaxReturn($rew);
	    	exit;
    		//$this->error('线路地区删除失败！');
    		//exit;
    	}
    }    
    
    /**
     * 线路复制
     * Enter description here ...
     */
    public function line_copy(){
    	if(IS_AJAX){
    		$line_id = I('post.line_id');
    		$province = I('post.province');
    		$city = I('post.city');
    		$area = I('post.area');
    		if (!empty($province)){
    			$data['province'] = $province;
    		}
    		if(!empty($city)){
    			$data['city'] = $city;
    		}
    		//if(!empty($area)){
    		//	$data['area'] = $area;
    		//}
    		$data['line_id'] = $line_id;
    		$res = $this->client->line_copy($data);
    		
    		$this->ajaxReturn($res);
    	}
    
    }
    
    /**
     * 线路复制处理
     * Enter description here ...
     */
    public function line_copy_handle(){
    	$line_zcode 			= I('post.line_zcode');
    	$province_id 			= empty(I('post.province')) ? 0 : I('post.province');
    	$city_id 				= empty(I('post.city')) ? 0 : I('post.city');
    	$area_id 				= empty(I('post.area')) ? 0 : I('post.area');
    	$data['line_zcode'] 	= $line_zcode;
    	$data['province_id'] 	= $province_id;
    	$data['city_id'] 		= $city_id;
    	$data['area_id'] 		= $area_id;
    	$data['line_id'] 		= I('post.id'); 
    	//print_r($data);
    	//exit;
	    set_time_limit(0);
	    $this->client->setTimeout(12000000000);
    	$res = $this->client->line_copy_handle($data);
    	//print_r($res);
    	//exit;
    	set_time_limit(30);
    	if($res){
    		$rew['status'] = 1;
    		$rew['data']['strstr'] = '复制成功';
    		$rew['data']['url'] = U('TransitPtwo/line_area', array('id' => $data['line_id']));
    		$this->ajaxReturn($rew);
	    	exit;
    	}else{
    		$rew['status'] = 2;
    		$rew['data']['strstr'] = '复制失败';
    		$this->ajaxReturn($rew);
	    	exit;
    		
    	}
    }
    
    /**
     * 线路地区上传
     * Enter description here ...
     */    
    public function area_upload(){
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
//	    if (!copy($tmp_file, $file_n)) {
//	        $this->error('上传失败');
//	    }
	    //$ExcelToArrary = new ExcelToArrary();//实例化
	    //$res = $this->read(C('UPLOAD_DIR') . $file_name, "UTF-8", $file_type);//传参,判断office2007还是office2003
	    //echo dirname(dirname(__FILE__)).'\PHPExcle\PHPExcel\IOFactory.php';
	    //exit;
	    //echo $file_n;
	    //exit;
	    $res = $this->_readExcel($file_n);
	    //$res = $this->read($file_n);
//	    print_r($res);
//	    exit;
	    //现时中国大陆有四个直辖市，分别为北京市、天津市、上海市和重庆市
	    //1954年中华人民共和国宪法规定，分自治区、自治州和自治县（自治旗）三级
	    //内蒙古自治区（1947年5月1日）
		//新疆维吾尔自治区（1955年10月1日）
		//广西壮族自治区（1958年3月5日）
		//宁夏回族自治区（1958年10月25日）
		//西藏自治区（1965年9月1日）
	    
//	    30个自治州:
//		吉林延边朝鲜族自治州 
//		湖北恩施土家族苗族自治州
//		湖南湘西土家族苗族自治州
//		四川阿坝藏族羌族自治州
//		四川凉山彝族自治州
//		四川甘孜藏族自治州
//		贵州黔东南苗族侗族自治州
//		贵州黔南布依族苗族自治州
//		贵州黔西南布依族苗族自治州
//		云南西双版纳傣族自治州
//		云南文山壮族苗族自治州
//		云南红河哈尼族彝族自治州
//		云南德宏傣族景颇族自治州
//		云南怒江僳僳族自治州
//		云南迪庆藏族自治州
//		云南大理白族自治州
//		云南楚雄彝族自治州
//		甘肃临夏回族自治州
//		甘肃甘南藏族自治州
//		青海海北藏族自治州
//		青海黄南藏族自治州
//		青海海南藏族自治州
//		青海果洛藏族自治州
//		青海玉树藏族自治州
//		青海海西蒙古族藏族自治州
//		新疆昌吉回族自治州
//		新疆巴音郭楞蒙古自治州
//		新疆克孜勒苏柯尔克孜自治州
//		新疆博尔塔拉蒙古自治州
//		新疆伊犁哈萨克自治州

//    	自治旗/旗 /县
		//strstr
	    $province_str =	'北京市,上海市,天津市,重庆市,自治区,特别行政区,省';
	    $city_str = '自治州,市,盟,地区,林区,县,岛,新界';
	    $area_str = '自治县,县,市,区,旗,自治旗,林区,地区,乡,镇,州';
	    
	    foreach ($res as $key => $val){
	    	if ($key > 0){
	    		if(!empty($val)){
	    			trim($val[0], ' ');
	    			trim($val[1], ' ');
	    			trim($val[2], ' ');
	    			trim($val[3], ' ');
//	    			//检验省份
//	    			//检验直辖市
//	    			$pr = strpos($province_str, $val['0']);
//
//	    			//if(!strpos($province_str, $val['0'])){
//					if($pr != 0 && $pr == 'false'){
//	    				$p[$key][] = 0;
//	    			}else{
//	    				$p[$key][] = 1;
//	    			}
//
//	    			$strla = strlen($val['0'])/3;  //字符串长度
//	    			$strlb1 = $strla - 3;	//开始位置
//	    			$pd1 = mb_substr($val['0'], $strlb1, $strla, 'utf-8');
//	    			if(!strpos($province_str, $pd1)){
//						$p[$key][] = 0;
//	    			}else{
//	    				$p[$key][] = 1;
//	    			}
//	    			$strlb2 = $strla - 5;
//	    			$pd2 = mb_substr($val['0'], $strlb2, $strla, 'utf-8');
//
//	    			if(!strpos($province_str, $pd2)){
//						$p[$key][] = 0;
//	    			}else{
//	    				$p[$key][] = 1;
//	    			}
//	    			$strlb3 = $strla - 1;
//	    			$pd3 = mb_substr($val['0'], $strlb3, $strla, 'utf-8');
//	    			if(!strpos($province_str, $pd3)){
//						$p[$key][] = 0;
//	    			}else{
//	    				$p[$key][] = 1;
//	    			}
////	    			print_r($p);
////	    			exit;
//	    			if(!in_array(1, $p[$key])){
//	    				$prompt[$key]['errorstr'] = '第'.$key.'行省份名称不存在请修改';
//	    			}
//
//	    			//检验市
//	    			//检验直辖市
//
//	    			if(!strstr($city_str, $val['1'])){
//	    				$c[$key][] = 0;
//	    			}else{
//	    				$c[$key][] = 1;
//	    			}
//
//	    			$strlc =  strlen($val['1'])/3;
//	    			$strlc1 = $strlc - 3;
//	    			$cd1 = mb_substr($val['1'], $strlc1, $strlc, 'utf-8');
//
//	    			if(!strpos($city_str, $cd1)){
//						$c[$key][] = 0;
//	    			}else{
//	    				$c[$key][] = 1;
//	    			}
//	    			$strlc2 = $strlc - 2;
//	    			$cd2 = mb_substr($val['1'], $strlc2, $strlc, 'utf-8');
//	    			if(!strpos($city_str, $cd2)){
//						$c[$key][] = 0;
//	    			}else{
//	    				$c[$key][] = 1;
//	    			}
//	    			$strlc3 = $strlc - 1;
//	    			$cd3 = mb_substr($val['1'], $strlc3, $strlc, 'utf-8');
//	    			if(!strpos($city_str, $cd3)){
//						$c[$key][] = 0;
//	    			}else{
//	    				$c[$key][] = 1;
//	    			}
//
//	    			if(!in_array(1, $c[$key])){
//	    				$prompt[$key]['errorstr'] = '第'.$key.'行市名称不存在请修改';
//	    			}
//
//	    			//检验区
//	    			$strlsa  =  strlen($val['2'])/3;
//	    			$strlsa1 = $strlsa - 3;
//	    			$ad1 = mb_substr($val['2'], $strlsa1, $strlsa, 'utf-8');
//
//	    			if(!strpos($area_str, $ad1)){
//						$ad[$key][] = 0;
//	    			}else{
//	    				$ad[$key][] = 1;
//	    			}
//	    			$strlsa2 = $strlsa - 2;
//	    			$ad2 = mb_substr($val['2'], $strlsa2, $strlsa,'utf-8');
//	    			if(!strpos($area_str, $ad2)){
//						$ad[$key][] = 0;
//	    			}else{
//	    				$ad[$key][] = 1;
//	    			}
//	    			$strlsa3 = $strlsa - 1;
//	    			$ad3 = mb_substr($val['2'], $strlsa3, $strlsa, 'utf-8');
//	    			if(!strpos($area_str, $ad3)){
//						$ad[$key][] = 0;
//	    			}else{
//	    				$ad[$key][] = 1;
//	    			}
//	    			if(!in_array(1, $ad[$key])){
//	    				$prompt[$key]['errorstr'] = '第'.$key.'行区名称不存在请修改';
//	    			}
	    			//邮编
	    			$zipcode = strlen($val['3']);
	    			
	    			if(6 < $zipcode || $zipcode == 0){
	    				$prompt[$key]['errorstr'] = '第'.$key.'行邮编不正确请修改';
	    			}
	    			
	    		}else{
	    			$prompt[$key]['errorstr'] = '第'.$key.'行存在空格行,请删除'; 
	    		}
	    	}
	    }
	    set_time_limit(30);
	    if(!empty($prompt)){
	    	$rek['status'] = 1;
	    	$rek['data'] = $prompt;
	    	//print_r($rek);
	    	//exit;
	    	//$req = json_encode($rek);
	    	$this->ajaxReturn($rek);
	    	exit;
	    }else{
	    	$line_id = I('post.id');
	    	$pid = empty(I('post.line_zcode')) ? 0 : I('post.line_zcode');
	    	$rek['status'] = 2;
	    	$rek['data']['line_id'] = $line_id;
	    	$rek['data']['pid'] = $pid;
	    	$rek['data']['files'] = $file_name;
	    	
	    	$this->ajaxReturn($rek);
	    	exit;
	    }
	    
	    
	    //声明三个数组保存省市区  作用：验证是否需要提交
//	    $province = array();
//	    $city = array();
//	    $area = array();
//
//
//	    set_time_limit(0);
//	    $this->client->setTimeout(120000000);
//	    $i = 1;
//	    
//	    $rew = $this->client->line_area_up($res, $line_id, $pid);
//	    if($rew){
//	    	$this->error('上传成功', U('Transit/line_area', array('id' => $line_id)));
//	    	exit;
//	    }else{
//	    	$this->error('上传失败');
//	    	exit;
//	    }
//		set_time_limit(30);
	    
    }
    
    /**
     * 
     * 文件上传确认保存数据
     * Enter description here ...
     */
    public function area_upload_confirm(){
    	$line_id = I('post.line_id');
    	$pid = I('post.pid');
    	$file_name = I('post.files');
    	$savePath = C('UPLOAD_DIRS');
    	$files = $savePath . $file_name;
    	$res = $this->_readExcel($files);

	    set_time_limit(0);
	    $this->client->setTimeout(120000000);
	    $rew = $this->client->line_area_up($res, $line_id, $pid);

	    set_time_limit(30);
//	    $url = U('Transit/line_area', array('id' => $line_id));
//	    echo '<meta http-equiv="refresh" content="5;url='.$url.'" />'; 
	    
	    if($rew){
	    	
	    	$rek['status'] = 1;
	    	$rek['data']['strstr'] = '上传成功';
	    	$this->ajaxReturn($rek);
	    	exit;
	    	//$this->error('上传成功', U('Transit/line_area', array('id' => $line_id)));
	    	//exit;
	    }else{
	    	
	    	$rek['status'] = 2;
	    	$rek['data']['strstr'] = '上传失败';
	    	$this->ajaxReturn($rek);
	    	exit;
	    	
	    	//$this->error('上传失败');
	    	//exit;
	    }
		
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
  	
  	public function zcode_line_api(){
  		//第一  取得数据
		$dat = I('post.arr');

		//因为tp I() 影响json_decode()
		$da = str_replace('&quot;', '"', $dat);
		$das = base64_decode($da);
		
		$row = json_decode($das, true);
		
		//测试数据		
		//$row['line_id'] = 1;
		//$row['province'] = '广东';
		//$row['city'] = '广州';
		//$row['area'] = '越秀';
		//第二  验证数据
		$data['line_id']  = $row['line_id'];
  		if(!empty($row['province'])){
			$data['province'] = $row['province'];
  		}
  		if(!empty($row['city'])){
			$data['city'] 	  = $row['city'];
  		}
  		if($row['area']){
			$data['area']     = $row['area'];
  		}
  		//print_r($row);
  		//exit;
		if((empty($data['province']) && empty($data['city'])) || empty($data['area'])){
  			$lerror['status'] = false;
  			$lerror['errorstr'] = '地址信息不能为空或地址信息不全';
  			print_r($lerror);
  			exit;
  		}
  		
  		if(empty($data['line_id'])){
  			$lerror['status'] = false;
  			$lerror['errorstr'] = '线路信息不能为空';
  			print_r($lerror);
  			exit;
  		}
  		
  		
  		if((!empty($data['province']) && !empty($data['area'])) || (!empty($data['city']) && !empty($data['area'])) ){
  			//print_r($data);
  			//exit;
  			//$this->client->setTimeout(1200000);
  			$res = $this->client->zcode_line_api($data);
  			if(empty($res)){
  				$error['status'] = false;
  				$error['errorstr'] = '传入参数错误或者线路地区信息不存在';
  				print_r($error);
  				exit;
  			}else{
  				$ldata['status'] = true;
  				$ldata['data']['zcode']  = empty($res[0][2]['zipcode']) ? $res[0][1]['zipcode'] : $res[0][2]['zipcode'];
  				$ldata['data']['status'] = empty($res[0][2]['status']) ?  $res[0][1]['status'] : $res[0][2]['status'];
  				print_r($ldata);
  				exit;
  			}
  			
  			
  			
  		}else{
  			$lerror['status'] = false;
  			$lerror['errorstr'] = '地址信息不能全';
  			print_r($lerror);
  			exit;
  		
  		}
  		
  	
  	} 
  	
  	public function line_api(){
  		$line = $this->client->line_api();
  		$this->assign('transit', $line);
  		$this->display();
  	}
  	
  	public function line_api_handle(){
  		$line_id = I('post.line_id');
  		$province = I('post.province');
  		$city = I('post.city');
  		$area = I('post.area');
  		
		$arr['line_id'] 	 = $line_id;
		$arr['province'] 	 = $province;
		$arr['city'] 		 = $city;
		$arr['area']    	 = $area;
		
		//print_r($arr);
		//exit;
		
		$row = json_encode($arr);
		$rew = base64_encode($row);
		//$dat['arr'] = $row;
		$dat['arr'] = $rew;
		//print_r($dat);
		//exit;
		$url = 'http://admin.loc.mk:82' . U('TransitPtwo/zcode_line_api');
		//echo $url;
		//exit;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		// 把post的变量加上
		curl_setopt($ch, CURLOPT_POSTFIELDS, $dat);
		$output = curl_exec($ch);
		curl_close($ch);
		if ($output === FALSE){
		    echo 'cURL Error:'.curl_error($ch);
		}
		print_r($output);
		exit;
		//$res =  base64_decode($output);
		//$reh = 	json_decode($res, true);
  		//print_r($reh);
  		//exit;
  	}
  	
 
  	
    
    /*====================================线路地区开		end=======================================================================================*/

    /**
     *
     * 更新redis缓存
     */
	public function redis_line(){
//        $redis = new \Redis();
//        $redis ->connect("127.0.0.1",6379); //localhost也可以填你服务器的ip
//        $redis ->set("say","Hello World11");
//        echo $redis ->get("say");     //应输出Hello World
//        exit;


        $id = I('get.id');
        if (empty($id)){
            $this->error('线路不存在！');
            exit;
        }

        set_time_limit(0);
        $arr = \Lib11\Queue\CateCache::set_addr_cache($id);

        if ($arr['status']){
//            $this->assign('content', '线路缓存更新成功！');
//            $this->assign('status', true);
//            $cont = $this->fetch('Public:hints');
//            set_time_limit(30);
//            echo $cont;
//            exit;
            $url = $_SERVER["HTTP_REFERER"];

            echo "<script> alert('线路缓存更新成功！'); </script>";
            echo "<meta http-equiv='Refresh' content='1;URL=$url'>";
            exit;
        }else{
//            $this->assign('status', true);
//            $this->assign('content', '线路缓存更新失败！');
//            $cont = $this->fetch('Public:hints');
//            set_time_limit(30);
//            echo $cont;
//            exit;
            $url = $_SERVER["HTTP_REFERER"];

            echo "<script> alert('线路缓存更新失败！'); </script>";
            echo "<meta http-equiv='Refresh' content='1;URL=$url'>";
            exit;
        }
    }

    //线路模板下载
    public function  upload_template(){
        $id = I('get.id');
        $this->assign('id', $id);
	    $this->display();
    }




    //线路模板下载处理
    public function upload_add(){
	    //$arr = $_FILES;
	    //print_r($arr);
	    //exit;
        $id = I('post.id');
        $upload           = new \Think\Upload();// 实例化上传类
        $upload->maxSize  = 1048576*50 ;// 设置附件上传大小
        $upload->exts     = array('csv', 'xls', 'xlsx');// 设置附件上传类型
        $upload->rootPath = ADMIN_ABS_FILE; //设置文件上传保存的根路径
        $upload->savePath = C('UPLOADS'); // 设置文件上传的保存路径（相对于根路径）
        $upload->autoSub  = false; //自动子目录保存文件
        $upload->subName  = array('date','Ymd');
        $upload->saveName = array('uniqid',mt_rand()); //设置上传文件名

        $info = $upload->upload();

        if(!$info) {
            // 上传错误提示错误信息
            //$this->error($upload->getError());
            $err = $upload->getError();
            $rew['status'] = 0;
            $rew['data']['strstr'] = $err;
        }else{
            // 上传成功
            //print_r($info);
            //exit;
            $info['batch']['tmp_name'] = ADMIN_ABS_FILE . $info['batch']['savepath'] . $info['batch']['savename'];
            $info['delivery']['tmp_name'] = ADMIN_ABS_FILE . $info['delivery']['savepath'] . $info['delivery']['savename'];

            //复制文件
            $batch = TEMPLATE_FILE . '/' . $id . '.' . $info['batch']['ext'];
            $delivery = DELIVERY_FILE . '/' . $id . '.' .$info['delivery']['ext'];
            if(!file_exists(TEMPLATE_FILE)){
                mkdir(TEMPLATE_FILE,0777,true);
            }
            if(!file_exists(DELIVERY_FILE)){
                mkdir(DELIVERY_FILE,0777,true);
            }


            //判断线路文件是否存在， 存在删除
            if(file_exists($batch)){
                unlink($batch);
            }
            copy($info['batch']['tmp_name'], $batch);
            if(file_exists($delivery)){
                unlink($delivery);
            }
            copy($info['delivery']['tmp_name'], $delivery);

            $data['line_id'] = $id;
            //更新模板上传日志
            if(!empty($info['batch']['size'])){
                $data['whether_batch'] = 1;
            }
            if(!empty($info['delivery']['size'])){
                $data['is_goods'] = 1;
            }

            $res = $this->client->upload_add_log($data);
            if($res){
                $StateManagement = new \Api\Controller\StateManagementController();
                if (!empty($info['batch']['size'])){
                    $map = array(
                        'group' => 'batch_import_template',
                        'attr_one' => $id,
                    );
                    $StateManagement->del_view_status($map);
                }
            }
            unlink($info['batch']['tmp_name']);
            unlink($info['delivery']['tmp_name']);

//            $result = array('state'=>'yes','msg'=>'上传成功');
//            $this->ajaxReturn($result);
            $rew['status'] = 1;
            $rew['data']['strstr'] = '上传成功！';
            //$rew['data']['url'] = U('AdminAuth/index_authNav');


        }
        $this->ajaxReturn($rew);
        exit;

    }
}