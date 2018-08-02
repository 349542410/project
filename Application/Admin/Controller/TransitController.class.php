<?php
namespace Admin\Controller;
use Think\Controller;
class TransitController extends AdminbaseController{

	public function _initialize(){
		parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/Transit');		//读取、查询操作
        $this->client = $client;		//全局变量
	}

	/**
	 * 中转线路 列表
	 * @return [type] [description]
	 */
	public function center(){

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
		//添加语言20180608  start
        $lang = str_replace('\\', '/', dirname(dirname(dirname(__FILE__))));
        $lang =  $lang .'/WebUser/Lang/zh-cn.php';
        $mkl = require_once($lang);
        $lngname = $mkl['MKLINES'];
        foreach ($list as $key => $value){
            $list[$key]['lngname_name'] = $lngname[$value['lngname']];
        }
        //添加语言20180608  end


        $page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

		$page->setConfig('prev', "上一页");//上一页
		$page->setConfig('next', '下一页');//下一页
		$page->setConfig('first', '首页');//第一页
		$page->setConfig('last', "末页");//最后一页
		$page->setConfig( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
    		
		$show = $page->show(); // 分页显示输出
		$this->assign('page',$show);// 赋值分页输出

		$this->assign('list',$list);

		$this->display();
	}

	/**
	 * 中转线路 查看某个详情
	 * @return [type] [description]
	 */
	public function infoLine(){

		$id = I('get.id');
		$map['tc.id'] = array('eq',$id);

		$client = $this->client;
		$info   = $client->center_info($map);

		$this->assign('info',$info);

		$this->display();
	}

	/**
	 * 添加中转线路 视图&方法
	 */
	public function addLine(){

		$client  = $this->client;
		if(IS_POST){

            $arr = I('post.');

            if(strlen($arr['prename']) > 3){
            	$this->ajaxReturn(array('state'=>'no', 'msg'=>'“前缀”的长度不可超过3位字符'));
            }

            // bc报关和cc报关只能二选一
            if(trim($arr['bc_state']) == '1' && trim($arr['cc_state']) == '1'){
                $this->ajaxReturn(array('state'=>'no', 'msg'=>'bc报关和cc报关只能二选一'));
            }

            if(trim($arr['bc_state']) == '1' && trim($arr['tax_kind']) == '1'){
            	$this->ajaxReturn(array('state'=>'no', 'msg'=>'bc报关的情况下，税金形式只支持"固定值"的计算'));
            }

            // // 如果允许身份证为空，则必然不需要上传身份证
            // if(trim($arr['input_idno']) == '0' && trim($arr['member_sfpic_state']) == '1'){
            //     $this->ajaxReturn(array('state'=>'no', 'msg'=>'身份证号码非必填的情况下，上传身份证照片不可选'));
            // }
        
            $creater = session('admin.adtname');

            //处理不限额地区信息 start
            $no_limit_region = $arr['no_limit_region'];
            //$name = str_replace('', ',', $name);
            $region = explode("\r\n", $no_limit_region);
            //删除空值
            $region = array_filter($region); //array_filter
            //删除重复值
            $region = array_unique($region);
            //处理两端空建
            foreach ($region as $key => $val){
                if($val){
                    //去除两端空值
                    $reg[$key] = trim($val, ' ');
                    //去除字符串空格
                    $reg[$key] = str_replace(' ', '', $reg[$key]);
                    //$reg[$key] = preg_replace("/\s+/", '', $val);//去除空格
                    //将中文逗号替换成英文逗号
                    $reg[$key] = str_replace('，', ',', $reg[$key]);
                    //去除两端逗号
                    $reg[$key] = trim($reg[$key], ',');
                }
            }

            $arr['no_limit_region'] = implode('|', $reg);


            //处理不限额地区信息 end

            $result  = $client->center_add($arr,$creater);
            $this->ajaxReturn($result);

		}else{

			$elist  = $client->ec();
			
			$this->assign('elist',$elist);
			$this->display();
		}
	}

	/**
	 * 中转线路 修改 视图&方法
	 */
	public function editLine(){

		$client = $this->client;

		if(IS_POST){	//方法

            $arr = I('post.');

            if(strlen($arr['prename']) > 3){
            	$this->ajaxReturn(array('state'=>'no', 'msg'=>'“前缀”的长度不可超过3位字符'));
            }
                
            // bc报关和cc报关只能二选一
            if(trim($arr['bc_state']) == '1' && trim($arr['cc_state']) == '1'){
                $this->ajaxReturn(array('state'=>'no', 'msg'=>'bc报关和cc报关只能二选一'));
            }

            if(trim($arr['bc_state']) == '1' && trim($arr['tax_kind']) == '1'){
            	$this->ajaxReturn(array('state'=>'no', 'msg'=>'bc报关的情况下，税金形式只支持"固定值"的计算'));
            }
            
            // // 如果允许身份证为空，则必然不需要上传身份证
            // if(trim($arr['input_idno']) == '0' && trim($arr['member_sfpic_state']) == '1'){
            //     $this->ajaxReturn(array('state'=>'no', 'msg'=>'身份证号码非必填的情况下，上传身份证照片不可选'));
            // }

            //处理不限额地区信息 start
            $no_limit_region = $arr['no_limit_region'];
            //$name = str_replace('', ',', $name);
            $region = explode("\r\n", $no_limit_region);
            //删除空值
            $region = array_filter($region); //array_filter
            //删除重复值
            $region = array_unique($region);
            //处理两端空建
            foreach ($region as $key => $val){

                if($val){
                    //去除两端空值
                    $reg[$key] = trim($val, ' ');
                    //去除字符串空格
                    $reg[$key] = str_replace(' ', '', $reg[$key]);
                    //$reg[$key] = preg_replace("/\s+/", '', $val);//去除空格
                    //将中文逗号替换成英文逗号
                    $reg[$key] = str_replace('，', ',', $reg[$key]);
                    //去除两端逗号
                    $reg[$key] = trim($reg[$key], ',');
                }
            }

            $arr['no_limit_region'] = implode('|', $reg);


            //处理不限额地区信息 end


            $result  = $client->center_edit($arr);
            $this->ajaxReturn($result);

		}else{	//视图

			$id = I('get.id');
			$map['id'] = array('eq',$id);

			$info   = $client->center_info($map,2);
			$elist  = $client->ec();
            $info['no_limit_region'] = str_replace('|', '&#13;&#10;', $info['no_limit_region']);    //换行输出

			$this->assign('elist',$elist);
			$this->assign('info',$info);
			$this->display();

		}
	}

	/**
	 * 中转单号	列表
	 * @return [type] [description]
	 */
	public function no(){

		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		$cpname     = I('get.cpname');
		
		if($searchtype == 'creater') $searchtype = "tc.".$searchtype;

		if($cpname != '') $where['ec.company_name'] = $cpname;

		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');
		$this->assign($_GET);
		$this->assign('ePage',$ePage);
		
		if(!empty($keyword) && !empty($searchtype)){
			$where[$searchtype]=array('like','%'.$keyword.'%');
		}

        $client = $this->client;

		$res = $client->no_count($where,$p,$ePage);

		$count = $res['count'];
		$list  = $res['list'];

		$page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

		$page->setConfig('prev', "上一页");//上一页
		$page->setConfig('next', '下一页');//下一页
		$page->setConfig('first', '首页');//第一页
		$page->setConfig('last', "末页");//最后一页
		$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
    		
		$show = $page->show(); // 分页显示输出
		$this->assign('page',$show);// 赋值分页输出

		$elist  = $client->ec();
			
		$this->assign('elist',$elist);
		$this->assign('list',$list);

		$this->display();
	}

	/**
	 * 中转单号 修改
	 * @return [type] [description]
	 */
	public function editNo(){

		$client = $this->client;

		if(IS_POST){	//方法

            $arr = I('post.');
            
            $result  = $client->no_edit($arr);
            $this->ajaxReturn($result);

		}else{	//视图

			$id = I('get.id');

			$info = $client->no_info($id);
			// dump($info);
			$this->assign('info',$info);
			$this->display();
		}
	}

	/**
	 * 批次号 列表
	 * @return [type] [description]
	 */
	public function no2(){
        
		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		
		if($searchtype == 'creater') $searchtype = "tc.".$searchtype;
		if($searchtype == 'company_name') $searchtype = "ec.".$searchtype;

		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');
		$this->assign($_GET);
		$this->assign('ePage',$ePage);
		
		if(!empty($keyword) && !empty($searchtype)){
			$where[$searchtype]=array('like','%'.$keyword.'%');
		}

        $client = $this->client;

		$res = $client->no2_count($where,$p,$ePage);
		$count = $res['count'];
		$list  = $res['list'];

		$page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

		$page->setConfig('prev', "上一页");//上一页
		$page->setConfig('next', '下一页');//下一页
		$page->setConfig('first', '首页');//第一页
		$page->setConfig('last', "末页");//最后一页
		$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
    		
		$show = $page->show(); // 分页显示输出
		$this->assign('page',$show);// 赋值分页输出

		$this->assign('list',$list);

		$this->display();
	}

	/**
	 * 授权码列表
	 * @return [type] [description]
	 */
	public function authCode(){

		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		
		if($searchtype == 'line_name') $searchtype = "tc.name";
		if($searchtype == 'creater') $searchtype = "ml.tname";

		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');
		$this->assign($_GET);
		$this->assign('ePage',$ePage);
		
		if(!empty($keyword) && !empty($searchtype)){
			$where[$searchtype]=array('like','%'.$keyword.'%');
		}

        $client = $this->client;

		$res = $client->code_count($where,$p,$ePage);
		$count = $res['count'];
		$list  = $res['list'];
		
		$page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

		$page->setConfig('prev', "上一页");//上一页
		$page->setConfig('next', '下一页');//下一页
		$page->setConfig('first', '首页');//第一页
		$page->setConfig('last', "末页");//最后一页
		$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
    		
		$show = $page->show(); // 分页显示输出
		$this->assign('page',$show);// 赋值分页输出

		$this->assign('list',$list);
		$this->display();
	}

	/**
	 * 授权码修改 视图
	 * @return [type] [description]
	 */
	public function code_edit(){

		$id = I('id');
		$client = $this->client;
		
		$res = $client->_code_edit($id);
		$this->assign('elist',$res[0]);
		$this->assign('info',$res[1]);
		$this->display();
	}
	
	/**
	 * 授权码修改 方法
	 * @return [type] [description]
	 */
	public function update_code(){
		if(IS_POST){

			$arr = I('post.');

			$client = $this->client;
			$res = $client->_update_code($arr);
			$this->ajaxReturn($res);
		}else{
			die('非法操作');
		}
	}

	/**
	 * 授权码添加 视图
	 * @return [type] [description]
	 */
	public function code_add(){

		$client = $this->client;
		
		$elist = $client->_code_add();
		$this->assign('elist',$elist);
		$this->display();
	}

	/**
	 * 授权码添加 方法
	 * @return [type] [description]
	 */
	public function create_code(){
		if(IS_POST){

            $arr = I('post.');
            $createid = session('admin')['adid'];

            $client = $this->client;
            $result  = $client->_create_code($arr,$createid);
            $this->ajaxReturn($result);
			
		}else{
			die('非法操作');
		}
	}

	/**
	 * 授权码删除 方法
	 * @return [type] [description]
	 */
	public function delete_code(){
		if(!IS_POST){
			die('非法操作');
		}

		$id = I('post.id');

        $client = $this->client;
		$result = $client->_delete_code($id);

		$this->ajaxReturn($result);
	}

//======================================
	//定义线路价格
	public function line_price(){
		$line_id = I('id');//线路ID

		$client = $this->client;
		$info = $client->_line_price($line_id);

		self::assign('info',$info);
		self::assign('line_id',$line_id);
		$this->display();
	}

	public function edit_line_price(){
		$id = I('post.id');

		$data = array();
		$data['line_id']       = trim(I('line_id'));
		$data['fee_service']   = trim(I('fee_service'));
		$data['fee_first']     = trim(I('fee_first'));
		$data['fee_next']      = trim(I('fee_next'));
		$data['weight_first']  = trim(I('weight_first'));
		$data['weight_next']   = trim(I('weight_next'));
		$data['unit_currency'] = trim(I('unit_currency'));
		$data['unit_weight']   = trim(I('unit_weight'));
		$data['remark']        = trim(I('remark'));

		//验证数值字段
		$check_field = array(
			'fee_service' => '服务费',
			'fee_first'   => '首重价格',
			'fee_next'    => '续重单价',
		);

		foreach($check_field as $key=>$item){
			if(check_money_rule($data[$key])['state'] == 'no'){
				$this->ajaxReturn(array('state'=>'no','msg'=>'"'.$item.'"必须为大于0的数字'));
			}
		}

		$client = $this->client;
		$result = $client->_edit_line_price($id,$data);
		$this->ajaxReturn($result);
	}
}