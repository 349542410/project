<?php
/**
 * BC货品 客户端
 */
namespace Admin\Controller;
use Think\Controller;
class ProductController extends AdminbaseController{

	function _initialize(){
		parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/Product');		//读取、查询操作
        $this->client = $client;		//全局变量

        if(in_array(ACTION_NAME, array('index','add','edit'))){
        	$lineID     = (I('get.line')) ? I('get.line') : '';
        	
	        $cat_list = $client->cat_list($lineID);//查询所有类别
	        $cat_list = getTree($cat_list);//将普通数据转成树形结构

	        getTree(null);//重置静态变量
	        $this->cat_list = $cat_list;
        }
	}

	//获取线路所含顶级类别以及其二级类别
	public function get_list($lineID=''){

		$client = $this->client;
        $where['TranKd'] = array('like','%,'.$lineID.',%');
        $list = $client->top_cat_list($where);
        $list = getTree($list);//将普通数据转成树形结构
        getTree(null);//重置静态变量

        $naw = array();
        foreach($list as $k=>$item){
            if($item['level'] == '0'){
                $naw[$item['id']] = $item;
            }
            
        }

        foreach($naw as $k1=>$v1){
            foreach($list as $k2=>$v2){
                if($v2['fid'] == $v1['id']){
                    $naw[$k1]['child'][] = $v2;
                }
            }
        }
        // dump($naw);die;
        // foreach($list as $k=>$item){
        // 	$list[$k]['url'] = U('Category/index',array('line'=>$lineID,'cat'=>$item['id']));
        // 	// $list[$k]['add_child_url'] = U('Category/add',array('fid'=>$item['id']));
        // }
        return $naw;

	}

	public function index(){
        
		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		$cat        = (I('get.cat')) ? I('get.cat') : '';
		$lineID     = (I('get.line')) ? I('get.line') : '';
		$view       = (I('get.view')) ? I('get.view') : '';

		//分页显示的数量
		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');

		$this->assign($_GET);
		$this->assign('ePage',$ePage);
		
		$where = array();
		//按用户名搜索
		if(!empty($keyword) && !empty($searchtype)){
			$where['p.'.$searchtype]=array('like','%'.$keyword.'%');
		}

        $client = $this->client;

		//当线路ID是有值的时候
		if($lineID != ''){

			//左侧线路所属顶级类别列表
			$naw = $this->get_list($lineID);
			$naw = array_column($naw, NULL, 'id');//二维数组中的一维数组的id作为二维数组的键名
			$this->assign('naw',$naw);

			//类别ID不为空的时候
	    	if($cat != ''){
				$ids = $client->get_cat_list($cat);
		    	$where['p.cat_id'] = array('in',$ids);
		    	
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
		        
		        $tree = getTree($list);//将普通数据转成树形结构
		        $tree = array_column($tree, NULL, 'id');//二维数组以id字段做一维数组的键名

				$this->assign('list',$tree);
				$this->assign('cat_list',$this->cat_list);
	    	}
		}

		//只显示bc_state=1的线路，即BC报关管理为开启状态的线路
		$center = $client->get_center_list();
		$center = array_column($center, NULL, 'id');//二维数组中的一维数组的id作为二维数组的键名
		$this->assign('center',$center);
		$this->display();
	}

	//添加 视图
	public function add(){
        
        $this->assign('cat', I('cat')); //类别ID
		$this->assign('select_list',$this->cat_list);
		$this->display();
	}

	//添加 方法
	public function add_method(){

		$cat_id         = trim(I('post.cat_id'));
		$name           = trim(I('post.name'));
		$show_name      = trim(I('post.show_name'));
		$brand          = trim(I('post.brand'));
		$hs_code        = trim(I('post.hs_code'));
		$tariff_no      = trim(I('post.tariff_no'));
		$hgid           = trim(I('post.hgid'));
		$price          = trim(I('post.price'));
		$unit           = trim(I('post.unit'));
		$source_area    = trim(I('post.source_area'));
		$detail         = trim(I('post.detail'));
		$barcode        = trim(I('post.barcode'));
		$specifications = trim(I('post.specifications'));
		$net_weight     = trim(I('post.net_weight'));
		$rough_weight   = trim(I('post.rough_weight'));

		$data = array();
		$data['cat_id']         = $cat_id;
		$data['name']           = $name;
		$data['show_name']      = $show_name;
		$data['brand']          = $brand;
		$data['hs_code']        = $hs_code;
		$data['tariff_no']      = $tariff_no;
		$data['hgid']           = $hgid;
		$data['price']          = $price;
		$data['unit']           = $unit;
		$data['source_area']    = $source_area;
		$data['detail']         = $detail;
		$data['barcode']        = $barcode;
		$data['specifications'] = $specifications;
		$data['net_weight']     = $net_weight;
		$data['rough_weight']   = $rough_weight;
		$data['operator_id']    = session('admin')['adid'];//操作人ID
		$data['operator_name']  = session('admin.adtname');//操作人真实名字
		$data['ctime']          = date('Y-m-d H:i:s');

		$client = $this->client;

		$res = $client->_add_method($data);
        //更新redis 缓存
        \Lib11\Queue\CateCache::set_category_cache();
        //sleep(0.1);
		$this->ajaxReturn($res);
	}

	//编辑 视图
	public function edit(){

		$id = I('get.id');

		$client = $this->client;

		$info = $client->_edit($id);

		$info['TranKd'] = explode(',',$info['TranKd']);

// dump($this->cat_list);
// dump($info);
		self::assign('select_list',$this->cat_list);
		self::assign('info', $info);
		self::assign('id', $id);
		$this->display();
	}

	//编辑 方法
	public function edit_method(){
		$id = I('post.id');

		$cat_id         = trim(I('post.cat_id'));
		$name           = trim(I('post.name'));
		$show_name      = trim(I('post.show_name'));
		$brand          = trim(I('post.brand'));
		$hs_code        = trim(I('post.hs_code'));
		$tariff_no      = trim(I('post.tariff_no'));
		$hgid           = trim(I('post.hgid'));
		$price          = trim(I('post.price'));
		$unit           = trim(I('post.unit'));
		$source_area    = trim(I('post.source_area'));
		$detail         = trim(I('post.detail'));
		$barcode        = trim(I('post.barcode'));
		$specifications = trim(I('post.specifications'));
		$net_weight     = trim(I('post.net_weight'));
		$rough_weight   = trim(I('post.rough_weight'));

		$data = array();
		$data['cat_id']         = $cat_id;
		$data['name']           = $name;
		$data['show_name']      = $show_name;
		$data['brand']          = $brand;
		$data['hs_code']        = $hs_code;
		$data['tariff_no']      = $tariff_no;
		$data['hgid']           = $hgid;
		$data['price']          = $price;
		$data['unit']           = $unit;
		$data['source_area']    = $source_area;
		$data['detail']         = $detail;
		$data['barcode']        = $barcode;
		$data['specifications'] = $specifications;
		$data['operator_id']    = session('admin')['adid'];//操作人ID
		$data['operator_name']  = session('admin.adtname');//操作人真实名字
		$data['net_weight']     = $net_weight;
		$data['rough_weight']   = $rough_weight;
		
		$client = $this->client;

		$res = $client->_edit_method($id, $data);
        //更新redis 缓存
        \Lib11\Queue\CateCache::set_category_cache();
        //sleep(0.1);
		$this->ajaxReturn($res);
	}

	// 查看详细信息
	public function info(){
        
		$id = I('get.id');

		$client = $this->client;
		$info   = $client->_info($id);
		
		$this->assign('info',$info);
		$this->display();
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

			$this->ajaxReturn($result);

		}else{
			die('非法操作');
		}
	}

}