<?php
/**
 * CC类别 客户端
 */
namespace Admin\Controller;
use Think\Controller;
class CategoryForCcController extends AdminbaseController{

	function _initialize(){
		parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/CategoryForCc');		//读取、查询操作
        $Wclient = new \HproseHttpClient(C('RAPIURL').'/Category');			//读取、查询操作
        $this->client = $client;		//全局变量
        $this->Wclient = $Wclient;		//全局变量

        if(in_array(ACTION_NAME, array('index','add','edit','get_list'))){
        	$lineID     = (I('get.line')) ? I('get.line') : '';
        	
	        $cat_list = $client->cat_list($lineID);//查询所有类别
	        $cat_list = getTree($cat_list);//将普通数据转成树形结构

	        getTree(null);//重置静态变量
	        $this->cat_list = $cat_list;
        }

	}

	//获取线路所含顶级类别
	public function get_list($lineID=''){

		$client = $this->client;
        $where['TranKd'] = array('like','%,'.$lineID.',%');
        $list = $client->top_cat_list($where);
        foreach($list as $k=>$item){
        	$list[$k]['url'] = U('CategoryForCc/index',array('line'=>$lineID,'cat'=>$item['id'],'view'=>'on'));
        	// $list[$k]['add_child_url'] = U('CategoryForCc/add',array('fid'=>$item['id']));
        }
        return $list;

	}

	public function index(){
        
		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		$lineID     = (I('get.line')) ? I('get.line') : '';
		$cat        = (I('get.cat')) ? I('get.cat') : '';
		$view       = (I('get.view')) ? I('get.view') : '';

		//分页显示的数量
		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');

		$this->assign($_GET);
		$this->assign('ePage',$ePage);
		
		$where = array();
		//按用户名搜索
		if(!empty($keyword) && !empty($searchtype)){
			$where['c.'.$searchtype]=array('like','%'.$keyword.'%');
		}

		$client = $this->client;

		//当线路ID是有值的时候
		if($lineID != ''){

			//左侧线路所属顶级类别列表
			$top_level = $this->get_list($lineID);
			$top_level = array_column($top_level, NULL, 'id');//二维数组中的一维数组的id作为二维数组的键名
			$this->assign('top_level',$top_level);

			$where['c.TranKd'] = array('like','%,'.$lineID.',%');

			//当分类ID是有值的时候
	    	if($cat != ''){
				$ids = $client->get_cat_list($cat);
		    	$where['c.id'] = array('in',$ids);
	    	}

	    	//当$view == on 的时候才显示数据
	    	if($view == 'on'){

				$res = $client->_count($where,$p,$ePage);
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

		        $cats = $this->cat_list;//所有类别列表

		        foreach($cats as $key2=>$item2){

		        	foreach($list as $key1=>$item1){
		        		// echo $item2;
		        		if($item2['id'] == $item1['id']){
		        			$list[$key1]['level'] = $item2['level'];
		        		}
		        	}
		        }

		        //$cat_list 是所有类别的总集
		        $cat_list = array_column($this->cat_list, NULL, 'id');//二维数组中的一维数组的id作为二维数组的键名
				self::assign('list',$list);
				self::assign('cat_list',$cat_list);
			}
		}

		//只显示cc_state=1的线路，即BC报关管理为开启状态的线路
		$center = $client->get_center_list();
		$center = array_column($center, NULL, 'id');//二维数组中的一维数组的id作为二维数组的键名
		$this->assign('center',$center);

		$this->display();
	}

	//添加 视图
	public function add(){

		//判断是否直接在某个类别上，点击“添加子类”
		$fid = (I('get.fid')) ? trim(I('get.fid')) : '0';

		$client = $this->client;
		$res = $client->_add();

		$lineId = trim(I('line'));
		self::assign('line',$lineId);
		
		self::assign('fid',$fid);
		self::assign('line_list',$res);
		self::assign('select_list',$this->cat_list);
		$this->display();
	}

	//添加 方法
	public function add_method(){

		$cat_id   = (I('post.cat_id')) ? trim(I('post.cat_id')) : '0';
		$cat_name = trim(I('post.cat_name'));
		$hs_code  = trim(I('post.hs_code'));
		$hgid     = trim(I('post.hgid'));
		$price    = (I('post.price')) ? trim(I('post.price')) : 0;
		$remarks  = trim(I('post.remarks'));
		$lines    = trim(I('post.lines'));
		$is_show  = trim(I('post.is_show'));
		$status   = trim(I('post.status'));
		$sort     = trim(I('post.sort'));
		$default_value = I('post.rate');//判断是否使用默认税金

        // 验证金额是否为数字
        if(!is_numeric($price)){
            $result = array('state'=>'no', 'msg'=>'金额格式必须为数字');
            $this->ajaxReturn($result);exit;
        }

        //验证金额格式
        $chemon = "/^\d+(\.{0,1}\d+){0,1}$/";
        if(!preg_match($chemon,$price)){
            $result = array('state'=>'no', 'msg'=>'错误的金额格式');
            $this->ajaxReturn($result);exit;
        }

		//数据处理
		$lines = ','.$lines;//在最左侧也添加一个逗号

		$data = array();
		$data['cat_name']      = $cat_name;
		$data['hs_code']       = $hs_code;
		$data['hgid']          = $hgid;
		$data['remarks']       = $remarks;
		$data['is_show']       = $is_show;
		$data['status']        = $status;
		$data['sort']          = $sort;
		$data['operator_id']   = session('admin.adid');//操作人ID
		$data['operator_name'] = session('admin.adtname');//操作人真实名字
		$data['ctime']         = date('Y-m-d H:i:s');
		$data['default_value'] = $default_value;

		$Wclient = $this->Wclient;

		//顶级类别
		if($cat_id == '0'){
			if(strlen(trim($lines,",")) == 0){
				$result = array('state'=>'no', 'msg'=>'至少选择一条线路');
	            $this->ajaxReturn($result);exit;
			}
			$data['fid']        = 0;
			$data['TranKd']     = $lines;
			$data['price']      = 0;

			$res = $Wclient->_add_top_method($data);//顶级类别添加

		}else{
			$data['fid']        = $cat_id;
			$data['TranKd']     = '';
			$data['price']      = $price;

			$res = $Wclient->_add_method($data);//二级类别添加
		}

		//20180515 xieyiyi 把数据写入redis  不返回
		if($res['state'] == 'yes'){
			
			$redis = \Lib11\Queue\CateCache::set_category_cache();
		}

		$this->ajaxReturn($res);
	}

	//编辑 视图
	public function edit(){

		$id = I('get.id');

		$client = $this->client;

		$res = $client->_edit($id);

		$info = $res[1];

		$info['TranKd'] = explode(',',$info['TranKd']);
		
		$lineId = trim(I('line'));

		//获取税号的默认税率
		$rate_res = $client->_rate_moren($info['hs_code']);

		if($rate_res['state'] == 'yes'){

			$rate_moren = $rate_res['msg'];

		}else{

			$rate_moren = '';
		}

		self::assign('line',$lineId);
		self::assign('line_list',$res[0]);
		self::assign('select_list',$this->cat_list);
		self::assign('info', $info);
		self::assign('id', $id);
		self::assign('cname_code', $res[2]);
		self::assign('rate_moren', $rate_moren);

		
						
		$this->display();
	}

	//编辑 方法
	public function edit_method(){

		$id = I('post.id');
		$lineId = I('post.lineId');//当前操作界面中的选中的线路ID

		$cat_id   = (I('post.cat_id')) ? trim(I('post.cat_id')) : '0';
		$cat_name = trim(I('post.cat_name'));
		$hs_code  = trim(I('post.hs_code'));
		$hgid     = trim(I('post.hgid'));
		$price    = (I('post.price')) ? trim(I('post.price')) : 0;
		$remarks  = trim(I('post.remarks'));
		$lines    = trim(I('post.lines'));
		$is_show  = trim(I('post.is_show'));
		$status   = trim(I('post.status'));
		$sort     = trim(I('post.sort'));
		$default_value = I('post.rate');

        // 验证金额是否为数字
        if(!is_numeric($price)){
            $result = array('state'=>'no', 'msg'=>'金额格式必须为数字');
            $this->ajaxReturn($result);exit;
        }

        //验证金额格式
        $chemon = "/^\d+(\.{0,1}\d+){0,1}$/";
        if(!preg_match($chemon,$price)){
            $result = array('state'=>'no', 'msg'=>'错误的金额格式');
            $this->ajaxReturn($result);exit;
        }

		//数据处理
		$lines = ','.$lines;//在最左侧也添加一个逗号

		$data = array();
		$data['cat_name']      = $cat_name;
		$data['hs_code']       = $hs_code;
		$data['hgid']          = $hgid;
		$data['remarks']       = $remarks;
		$data['is_show']       = $is_show;
		$data['status']        = $status;
		$data['sort']          = $sort;
		$data['operator_id']   = session('admin.adid');//操作人ID
		$data['operator_name'] = session('admin.adtname');//操作人真实名字
		$data['default_value'] = $default_value;

		$Wclient = $this->Wclient;

		//顶级类别
		if($cat_id == '0'){
			if(strlen(trim($lines,",")) == 0){
				$result = array('state'=>'no', 'msg'=>'至少选择一条线路');
	            $this->ajaxReturn($result);exit;
			}
			$data['fid']        = 0;
			$data['TranKd']     = $lines;
			$data['price']      = 0;

			$res = $Wclient->_edit_top_method($id, $data, $lineId);

		}else{//二级类别
			$data['fid']        = $cat_id;
			$data['TranKd']     = '';
			$data['price']      = $price;

			$res = $Wclient->_edit_method($id, $data);
		}

		//20180515 xieyiyi 把数据写入redis  不返回
		if($res['state'] == 'yes'){
			
			$redis = \Lib11\Queue\CateCache::set_category_cache();
		}
		
		$this->ajaxReturn($res);
	}

	/**
	 * 单个删除 方法
	 * @return [type] [description]
	 */
	public function delete(){

		if(IS_AJAX){
			$id = I('post.id');

	        $client = $this->client;
			$result = $client->delete($id);

			//20180515 xieyiyi 把数据写入redis  不返回
			if($result['state'] == 'yes'){
				
				$redis = \Lib11\Queue\CateCache::set_category_cache();
			}

			$this->ajaxReturn($result);

		}else{
			die('非法操作');
		}
	}

	/**
	 * 20180425 xieyiyi 
	 * 实时搜索税则表
	*/
	public function livesearch(){

		if(IS_POST && IS_AJAX){

			//要搜索的内容
			$hs_code = I('post.str');

			if(!is_numeric($hs_code)){

				$this->ajaxReturn(array('state'=>'no','msg'=>'请输入数字'));
			}

			$list = $this->client->livesearch($hs_code);

			if($list){

				$res = array('state'=>'yes','msg'=>$list);

			}else{

				$res = array('state'=>'no','msg'=>'');				
			}

			$this->ajaxReturn($res);

		}else{

			$this->ajaxReturn(array('state'=>'no', 'msg'=>'非法提交'));
		}
	}

	/**
	 * 20180426 xieyiyi
	 * 根据税号判断默认税率
	*/
	public function rate_moren(){

		if(IS_POST && IS_AJAX){

			$hs_code = I('post.code');
			
			if(!is_numeric($hs_code)){

				$this->ajaxReturn(array('state'=>'no','msg'=>'请输入数字'));
			}

			$res = $this->client->_rate_moren($hs_code);

			$this->ajaxReturn($res);

		}else{

			$this->ajaxReturn(array('state'=>'no', 'msg'=>'非法提交'));			
		}
	}

	/**
	 * 20180517 xieyiyi  手动加入redis缓存
	*/
	public function redis(){

		$redis = \Lib11\Queue\CateCache::set_category_cache();

		$this->ajaxReturn($redis);
	}
}