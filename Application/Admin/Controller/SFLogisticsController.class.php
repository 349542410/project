<?php
/**
 * 顺丰物流管理 客户端
 */
namespace Admin\Controller;
use Think\Controller;
class SFLogisticsController extends AdminbaseController{

    function _initialize() {
        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/SFLogistics');		//读取、查询操作
        $this->client = $client;		//全局变量

    }

   	public function index(){

		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		$checkflg   = I('get.checkflg');
		$status     = I('get.status');
		$problem    = I('get.problem');//只显示问题件

		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');
		$this->assign($_GET);
		$this->assign('ePage',$ePage);
		
		if(!empty($keyword) && !empty($searchtype)){
			$where[$searchtype]=array('like','%'.$keyword.'%');
		}

        if($checkflg != ''){
            $where['CheckFlg'] = $checkflg;
        }

        if($status != ''){
            $where['Status'] = $status;
        }

        if($problem == 'on'){
        	// $where['CheckFlg'] = '0';//不通过
        	if($status == ''){
        		$where['Status'] = array('in','13,23,25,99');
        	}else{
        		$where['Status'] = array(array('in','13,23,25,99'),array('eq',$status), 'and');
        	}
        }

        $where['TranKd'] = array('eq',C('Transit_Type.SF_Transit'));//服务器上面的是5

        $client = $this->client;
        
		$res = $client->count($where,$p,$ePage);

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
		$this->assign('id',$list['id']);

    	$this->assign('state_arr',C('state_arr'));
		$this->display();
   	}

	/**
	 * 查看
	 * @return [type] [description]
	 */
	public function info(){
        
		$id = I('get.id');
		$map['id'] = array('eq',$id);

		$client = $this->client;
		$info   = $client->info($map);
		
		$this->assign('info',$info[0]);
		$this->assign('list',$info[1]);


    	$this->assign('state_arr',C('state_arr'));
		$this->display();

	}

	/**
	 * 编辑 视图
	 * @return [type] [description]
	 */
	public function edit(){

		$id = I('get.id');

        $client = $this->client;

		$info = $client->edit($id);

		$this->assign('info',$info);

		$this->display();

	}

	/**
	 * 更新数据
	 * @return [type] [description]
	 */
	public function update(){

		if(!IS_POST){
			die('非法操作');
		}

		$id = I('post.id');

		//如果被修改的id <= 50 ,抛出错误，终止操作
		if(intval($id) <= 50){
            $result = array('state' => 'no', 'msg' => '无法修改');
            $this->ajaxReturn($result);
            exit;
		}

		$client = $this->client;

		$data['company_name']   = trim(I('post.company_name'));
		$data['short_name']     = trim(I('post.short_name'));
		$data['express_way']    = trim(I('post.express_way'));
		$data['contact_person'] = trim(I('post.contact_person'));
		$data['contact_phone']  = trim(I('post.contact_phone'));
		$data['status']         = I('post.status');
		$data['remarks']        = trim(I('post.remarks'));

		$result = $client->update($id,$data);

		$this->ajaxReturn($result);
	}

	/**
	 * 单个删除 方法
	 * @return [type] [description]
	 */
	public function delete(){

		if(!IS_POST){
			die('非法操作');
		}

		$id = I('post.id');

		//如果被修改的id >= 50 ,抛出错误，终止操作
		if(intval($id) >= 50){
            $this->display('Public/msg');
            exit;
		}

        $client = $this->client;
		$result = $client->delete($id);

		$this->ajaxReturn($result);
	}

	/**
	 * 中转跟踪
	 * @return [type] [description]
	 */
	public function tran_track(){
        
		$type = (C('Transit_Type.SF_Transit')) ? C('Transit_Type.SF_Transit') : '';

		$list = R('Logarithm/index',array('request'=>true, 'type'=>$type));

		$this->assign('list',$list);
		$this->assign($_GET);
		$this->display('Public:logistics_strack');
	}

	/**
	 * 快递跟踪
	 * @return [type] [description]
	 */
	public function kd_track(){

		$type = (C('Transit_Type.SF_Transit')) ? C('Transit_Type.SF_Transit') : '';
		
		$list = R('Logarithm/pro_two',array('request'=>true, 'type'=>$type));

		$this->assign('list',$list);
		$this->assign($_GET);
		$this->display('Public:logistics_strack');
	}

	/**
	 * 导出 顺丰物流 某个批次号的，某个状态的所有订单信息
	 * @return [type] [description]
	 */
    public function sf_csv(){
		ini_set('max_execution_time', 0);
		ini_set('memory_limit','4088M');

    	$noid = trim(I('no'));
    	$state = (I('state')) ? trim(I('state')) : '1000';
        $client = $this->client;

		$tips = $client->_sf_csv($noid,$state);
		// dump($tips);die;
		$str = $tips['str'];
		$no = $tips['no'];
		$n   = $tips['i'] - 1;

	    $exportexcel  = new \Libm\MKILExcel\MkilExportMarket;
		$filename = $no.'-'.date('YmdHis')."(".$n.")";				//导出的文件名，无需后缀
		$fileurl  = ADMIN_ABS_FILE.'/SF_Export/'.$username.'/'.$filename;	//20170220 jie
		$exportexcel->SaveName   = $fileurl;	//包含路径+文件名;
		$exportexcel->Title      = $tips['title'];		//单元格表头
		$exportexcel->Data       = $str;		//导出数据数组
		$exportexcel->Format     = '2007';   	// 导出类型：csv, 2003表示excel2003, 2007表示excel2007
		$exportexcel->Model_Type = '1';   	// 是否进行省略操作
		$exportexcel->Sort       = false;   	// 是否带序号
		$exportexcel->OutPut 	 = true;
		$exportexcel->Title_Style 	 = true;
		$exportexcel->export();  				// 返回true,false
    }

}