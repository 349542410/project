<?php
/**
 * 美快优选3(湛江EMS)
 * 功能包括： 支付清关，快递号导入，批号对数
 * 
 */
namespace Admin\Controller;
use Think\Controller;
class MKBc3Controller extends AdminbaseController{

    function _initialize() {
        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/MKBc3');     //读取、查询操作
        $this->client = $client;        //全局变量
    }

    //清关
	public function index(){

        $keyword    = trim(I('get.keyword'));
        $searchtype = I('get.searchtype');
        $tcid       = (C('Transit_Type.MKBc3_Transit')) ? trim(C('Transit_Type.MKBc3_Transit')) : '';//20161116 jie   增加 TrandKd = 中转线路.id 的查询
        $starttime  = intval(I('starttime'));
        $endtime    = intval(I('get.endtime'));
        $tid        = (I('get.tid')) ? trim(I('get.tid')) : '';//搜索传入的ID

        $map = array();

        // 20171226 新增的查询条件 标签A(只显示airdatetime 20天内 status<60)
        $map['tn.status']      = array('lt',60);
        $map['tn.airdatetime'] = array('egt',date("Y-m-d",strtotime("-20 day")));//只显示airdatetime 20天内

        //按查询类型搜索
        if(!empty($keyword) && !empty($searchtype)){
            
            // 20171226 搜索中转号(无需支持Like搜索)时，不受 标签A 的限制
            if($searchtype == 'no'){
                $map['tn.'.$searchtype] = array('eq', $keyword);
                unset($map['tn.airdatetime']);
                unset($map['tn.status']);
            }else{
                $map['tn.'.$searchtype] = array('like','%'.$keyword.'%');
            }
        }

        if($tcid != ''){
            /* 20180307 jie  根据分配的线路权限获取线路相关的订单等信息  */
            $PublicLineData = new \Admin\Controller\PublicLineDataController();
            $PublicLineData->line_id = $tcid;

            $tcids = $PublicLineData->intersect();//一维数组

            $center_list = $PublicLineData->get_lines();
            // dump($center_list);die;
            /* 20180307 jie 根据分配的线路权限获取线路相关的订单等信息 */

            // Transit_Typ.MKBc3_Transit 比较特殊，是同时设置了多条线路，有别于其他设置
            // $tcids = explode(",",$tcid);
            $map['tn.tcid'] = array('in',$tcids);//配置中定义的id集

            $arr_none['TranKd'] = $tcids;//配置中定义的id集  20180307  jie
        }

        //如果是搜索请求，则使用此条件
        if($tid != ''){
            $map['tn.tcid'] = array('eq',$tid);//搜索栏传入的线路ID
            $arr_none['TranKd'] = $tid;//配置中定义的id集  20180307  jie
        }

        //按时间段搜索
        if(!empty($starttime) && !empty($endtime)){
            $starttime = date('Y-m-d H:i:s',$starttime);
            $endtime   = date('Y-m-d H:i:s',$endtime);
            $map['tn.date'] = array('between',$starttime.",".$endtime);

        }else if(!$starttime && $endtime){
            $endtime = date('Y-m-d H:i:s',$endtime);
            $map['tn.date'] = array('elt',$endtime);

        }else if($starttime && !$endtime){
            $starttime = date('Y-m-d H:i:s',$starttime);
            $map['tn.date'] = array('egt',$starttime);
        }

        $transit = new \HproseHttpClient(C('RAPIURL').'/MkBcInfo');
        $res = $transit->customsList($map);

        $this->assign('list',$res['list']);//数据
        $this->assign('center_list',$center_list);//搜索栏的 线路列表
        $this->assign($_GET);

        /* 20180307 jie */
        // 获取没有批次号的数据 20180307 jie
        $none_transit = new \Admin\Controller\NoneTransitController();
        $none_tlist = $none_transit->index($arr_none);
        $this->assign('none_tlist', $none_tlist);
        /* 20180307 jie */
        
        $this->display();
	}

    /**
     * 快递号导入 视图
     * @return [type] [description]
     */
    public function express(){

        $keyword     = trim(I('get.keyword'));
        $searchtype  = I('get.searchtype');
        $erp_state   = I('get.erp_state');
        $kd100_state = I('get.kd100_state');
        $starttime   = intval(I('get.starttime'));
        $endtime     = intval(I('get.endtime'));
        $Signed      = trim(I('get.Signed')); // 是否仅仅显示未签收的数据
        if($Signed == 'on'){
            $where['l.IL_state'] = array('neq','1003'); //筛选出
        }
        $p = (I('p')) ? trim(I('p')) : '1';
        $ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');
        $this->assign($_GET);

        $this->assign('ePage',$ePage);
        
        if(!empty($keyword) && !empty($searchtype)){

            $where['t.'.$searchtype]=array('like','%'.$keyword.'%');
        }

        if($starttime && $endtime){
            $where['t.ctime'] = array('between',array(date('Y-m-d H:i:s',$starttime),date('Y-m-d H:i:s',$endtime)));
        }else if(!$starttime && $endtime){
            $where['t.ctime'] = array('elt',date('Y-m-d H:i:s',$endtime));
        }else if($starttime && !$endtime){
            $where['t.ctime'] = array('egt',date('Y-m-d H:i:s',$starttime));
        }

        if($erp_state != ''){
            if($erp_state == '0'){
                $where['t.erp_state'] = array(array('eq',$erp_state),array('exp','is NULL'), 'or') ;
            }else{
                $where['t.erp_state'] = array('eq',$erp_state);
            }
        }

        if($kd100_state != ''){
            if($kd100_state == '0'){
                $where['t.kd100_state'] = array(array('eq',$kd100_state),array('exp','is NULL'), 'or') ;
            }else{
                $where['t.kd100_state'] = array('eq',$kd100_state);
            }
        }

        // Transit_Typ.MKBc3_Transit 比较特殊，是同时设置了多条线路，有别于其他设置
        $tcid  = C('Transit_Type.MKBc3_Transit');
        // $tcids = explode(",",$tcid);
        // $where['t.tcid'] = array('in',$tcids);

        /* 20180307 jie 根据分配的线路权限获取线路相关的订单等信息  */
        $PublicLineData = new \Admin\Controller\PublicLineDataController();
        $PublicLineData->line_id = $tcid;

        $tcids = $PublicLineData->intersect();//一维数组

        $where['t.tcid'] = array('in',$tcids);//配置中定义的id集
        /* 20180307 jie */

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

        $this->display();
    }

    //导入EMS单号 视图
    public function transfer(){

        $tcid = (C('Transit_Type.MKBc3_Transit')) ? C('Transit_Type.MKBc3_Transit') : '';

        $tcids = explode(",",$tcid);
        $client = $this->client;
        $center_list = $client->_center_list($tcids);

        $this->assign('center_list',$center_list);
        
        $this->display();
    }

    /**
     * 导入EMS单号 方法   导入CSV(按照批次号归类的，即同一个csv文件里面全是同一个批次号的单号)
     * @return [type] [description]
     */
    public function import_csv(){
        ini_set('memory_limit','4088M');
        ini_set('max_execution_time', 0);

        G('begin');
/*      功能使用一段时间后没问题的话，这段代码可以删除 20180109 jie  
        $upload           = new \Think\Upload();// 实例化上传类
        $upload->maxSize  = 1048576*50 ;// 设置附件上传大小
        $upload->exts     = array('csv', 'xls', 'xlsx');// 设置附件上传类型
        $upload->rootPath = K(ADMIN_ABS_FILE); //设置文件上传保存的根路径
        $upload->savePath = C('UPLOADS'); // 设置文件上传的保存路径（相对于根路径）
        $upload->autoSub  = true; //自动子目录保存文件
        $upload->subName  = array('date','Ymd');
        $upload->saveName = array('uniqid',mt_rand()); //设置上传文件名

        $info = $upload->upload();

        // MkilImportMarket这个类需要读取这个参数
        $info['file']['tmp_name'] = K(ADMIN_ABS_FILE . $info['file']['savepath'] . $info['file']['savename']);
        // dump($info);die;
        if(!$info) {// 上传错误提示错误信息
            // $this->error($upload->getError());
            $result = array('status'=>'0','msg'=>$upload->getError());
            $this->ajaxReturn($result);exit;
        }

      // if (file_exists(UPLOAD_PATH . $_FILES["file"]["name"])) { 
      //       // echo $_FILES["file"]["name"] . " already exists. "; 
      //   } else { 
      //       move_uploaded_file($_FILES["file"]["tmp_name"], 
      //       UPLOAD_PATH . $_FILES["file"]["name"]); 
      //       // echo "Stored in: " . UPLOAD_PATH . $_FILES["file"]["name"]; 
      //   // $filename  = $_FILES;//上传的csv文件
      //   }
        $filename  = $info;//上传的csv文件*/

        $tran_type   = I('tran_type');//转发快递之后，承接运单号的快递公司
        $sure_post   = I('sure_post') ? I('sure_post') : '';//是否推送给快递100
        $force_kd100 = I('force_kd100') ? I('force_kd100') : '';//强制推送给快递100
        $force_erp   = I('force_erp') ? I('force_erp') : '';//强制推送给ERP
        $kind        = I('lineId');  //线路ID
        $first_run   = I('first_run') ? trim(I('first_run')) : 'first';  //是否初次提交

        // dump($first_run);die;
        // 20180109 jie
        $importexcel = new \Libm\MKILExcel\MkilImportMarket;
        $importexcel->inputFileName  = $_FILES['file']['tmp_name'];
        $arr = $importexcel->import();

        // dump($arr);die;
        //如果返回的是false
        if($arr === false){
            $this->ajaxReturn(array('status'=>'0','msg'=>$importexcel->getError()));exit;
        }
        unset($arr[0]);//根据实际情况，去除数组第一个分支
        // 20180109 jie end

        // 判断处理后的数组是否为空
        $len_result = count($arr);
        if($len_result == 0){
            $result = array('status'=>'0','msg'=>'没有任何数据！');
            $this->ajaxReturn($result);exit;
        }
        // dump($result);die;
        $client = $this->client;

        // $kind = C('Transit_Type.MKBc3_Transit');//线路ID  //由于MKBc3_Transit可能包含多个线路ID，所以此处改为由页面点击的时候传入线路ID

        $result = $client->_index($arr,$tran_type,$sure_post,$force_kd100,$force_erp,$kind,$first_run);
        //dump($result);exit();
        G('end');
        $result['msg'] .= '。<br />耗时：'.G('begin','end').'s';  //耗时时间显示
        $this->ajaxReturn($result);
        // echo G('begin','end').'s';
        // dump($result);
    }

    //按批次号推送服务商
    public function log_list(){

        $keyword    = trim(I('get.keyword'));
        $searchtype = I('get.searchtype');
        $tcid       = (C('Transit_Type.MKBc3_Transit')) ? trim(C('Transit_Type.MKBc3_Transit')) : '';//20161116 jie   增加 TrandKd = 中转线路.id 的查询
        $starttime  = intval(I('starttime'));
        $endtime    = intval(I('get.endtime'));
        $tid        = (I('get.tid')) ? trim(I('get.tid')) : '';//搜索传入的ID

        //按查询类型搜索
        if(!empty($keyword) && !empty($searchtype)){
            $map['tn.'.$searchtype] = array('like','%'.$keyword.'%');
        }

        if($tcid != ''){
            // Transit_Typ.MKBc3_Transit 比较特殊，是同时设置了多条线路，有别于其他设置
            $tcids = explode(",",$tcid);
            $map['tn.tcid'] = array('in',$tcids);//配置中定义的id集
        }

        //如果是搜索请求，则使用此条件
        if($tid != ''){
            $map['tn.tcid'] = array('eq',$tid);//搜索栏传入的线路ID
        }

        //按时间段搜索
        if(!empty($starttime) && !empty($endtime)){
            $starttime = date('Y-m-d H:i:s',$starttime);
            $endtime   = date('Y-m-d H:i:s',$endtime);
            $map['tn.date'] = array('between',$starttime.",".$endtime);

        }else if(!$starttime && $endtime){
            $endtime = date('Y-m-d H:i:s',$endtime);
            $map['tn.date'] = array('elt',$endtime);

        }else if($starttime && !$endtime){
            $starttime = date('Y-m-d H:i:s',$starttime);
            $map['tn.date'] = array('egt',$starttime);
        }

        $client = $this->client;
        $res = $client->customsList($map, $tcids);

        $this->assign('list',$res['list']);
        $this->assign('center_list',$res['center_list']);//搜索栏的 线路列表
        $this->assign($_GET);
        $this->display();
    }

    /**
     * 按批次号进行推送，推送到物流服务商
     * @return [type] [description]
     */
    public function toPost(){
        header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
        header("Cache-Control: no-cache, must-revalidate" ); 
        $id = I('id');//mk_transit_no.id

        $kd = (I('kd')) ? I('kd') : ''; //是否 强制推送到物流服务商

        $client = $this->client;

        $kind = I('tcid');//mk_transit_center.id  //C('Transit_Type.MKBc3_Transit');//由于MKBc3_Transit可能包含多个线路ID，所以此处改为由页面点击的时候传入线路ID

        // 20171106 jie 属于指定的线路的时候，需要检查该批次号是否已经成功进行了第一个 节点推送
        $allow = (C('Logistics_Node_Set.MKBc3_Transit')) ? C('Logistics_Node_Set.MKBc3_Transit') : '';

        $company = 'ems';

        if($kind == $allow){
            $check_allow = $client->check_allow($id, $allow);

            if($check_allow['sort'] < 1 || $check_allow['status'] != 200){

                $this->ajaxReturn(array('status'=>'0', 'msg'=>'尚未成功推送节点'));
            }

            $company = 'zhongtong';
        }
        //20171106 end

        $res = $client->post_by_noid($id, $kind, $kd, $company);
        $this->ajaxReturn($res);

    }
//=============== 批号对数  ============
    /**
     * 中转跟踪
     * @return [type] [description]
     */
    public function tran_track(){
        
        $tcid = (C('Transit_Type.MKBc3_Transit')) ? C('Transit_Type.MKBc3_Transit') : '';

/*可以删除        $tcids = explode(",",$tcid);
        $client = $this->client;
        $center_list = $client->_center_list($tcids);

        $this->assign('center_list',$center_list);*/

        $list = R('Logarithm/index',array('request'=>true, 'type'=>$tcid));

        $this->assign('list',$list);
        $this->assign($_GET);
        $this->display('Public:logistics_strack');
    }

    /**
     * 快递跟踪
     * @return [type] [description]
     */
    public function kd_track(){

        $tcid = (C('Transit_Type.MKBc3_Transit')) ? C('Transit_Type.MKBc3_Transit') : '';

/*可以删除        $tcids = explode(",",$tcid);
        $client = $this->client;
        $center_list = $client->_center_list($tcids);

        $this->assign('center_list',$center_list);*/

        $list = R('Logarithm/pro_two',array('request'=>true, 'type'=>$tcid));

        $this->assign('list',$list);
        $this->assign($_GET);
        $this->display('Public:logistics_strack');
    }
}