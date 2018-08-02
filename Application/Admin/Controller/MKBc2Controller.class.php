<?php
/**
 * 美快BC优选2
 * 创建时间：2017-04-13
 * 修改时间：2017-04-19
 * created by Jie
 * 指导文档：中国邮政国内小包综合服务平台订单接口规范v1.0.0(4).doc
 * 
 * 功能包括： 订单操作
 * 美快BC优选2 订单对接状态(0未对接，1已对接，2订单更新(暂不使用)，4取消订单)
 * 订单操作--说明：
 * 1.点击批次号或总数进入订单详细列表；未对接的订单可以执行“发送订单”(order_opt_state=0)操作，成功对接则更改状态为“已对接”(order_opt_state=1)
 * ，否则不更新对接状态；
 * 2.“已对接”状态的订单可以执行“更改运单号”、“取消订单”的操作；所以执行“更改运单号”、“取消订单”操作之前要先检查订单是否“已对接”；
 * 执行“更改运单号”的时候，校验用户输入的原运单号是否跟数据库的意志，再检查该订单的对接状态是否已对接，或该订单是否已被取消，若是，则终止操作；执行“取消订
 * 单”的时候，同样需要检验；
 * 2.1.“更改运单号”如果执行成功，不需要更新order_opt_state；“取消订单”如果执行成功，则要更新order_opt_state为4(表示订单取消)；
 * 3.订单已取消的订单，除了“查看”操作，禁用其他操作；
 *
 * 使用数据表：mk_tran_list, mk_tran_order, mk_tran_list_state(新建); mk_transit_no, mk_transit_center
 */
namespace Admin\Controller;
use Think\Controller;
class MKBc2Controller extends AdminbaseController{

    protected $config = array(
        'ecCompanyId'    => 'SHAIBICIJIAJU',   //电商标识
        'partnered'      => 'IFQXpswT3Bsg',    //数字签名
        'url'            => 'http://211.156.200.111:5001/TAIWANLIANGAN/HttpService',   //发送地址
        'prov'           => '香港',  //发件人所在省
        'city'           => '九龙',  //发件人所在市县（区），市区中间用“,”分隔；注意有些市下面是没有区
        'exports_switch' => true,   //是否生成一个txt文件
        'xmlsave'        => API_ABS_FILE.'/MkBc2/',//生成xml保存到文件
        // 'xmlsave'        => (defined('API_ABS_FILE')) ? API_ABS_FILE.'/MkBc2/' : '',//生成xml保存到文件
    );

    function _initialize() {
        parent::_initialize();

        $client = new \HproseHttpClient(C('RAPIURL').'/MKBc2');     //读取、查询操作
        $this->client = $client;        //全局变量

        //受保护配置与公有配置数据合并
        $this->config = array_merge($this->config, C('MKBc2_config'));
        
    }


    // 订单操作
	public function index(){

        $keyword    = trim(I('get.keyword'));
        $searchtype = I('get.searchtype');
        $tcid       = (C('Transit_Type.MKBc2_Transit')) ? trim(C('Transit_Type.MKBc2_Transit')) : '';//20161116 jie   增加 TrandKd = 中转线路.id 的查询
        $starttime  = intval(I('starttime'));
        $endtime    = intval(I('get.endtime'));

        //按查询类型搜索
        if(!empty($keyword) && !empty($searchtype)){
            $map['tn.'.$searchtype] = array('like','%'.$keyword.'%');
        }else{
            $map['tn.send_report'] = array('eq','0');   //只列出尚未执行过预报订单的数据
        }

        if($tcid != ''){
            $map['tn.tcid'] = array('eq',$tcid);//标签A
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
        $list = $client->getList($map);

        $this->assign('list',$list);
        $this->assign($_GET);
        $this->display();
	}

    /**
     * 订单操作 视图  某个批次号的所有订单列表
     * @return [type] [description]
     */
    public function predict(){
        $p = I('get.p') ? I('get.p') : 1;   //当前页数，如果没有则默认显示第一页

        $id = I('get.id') ? I('get.id') : "";
        $kind = I('get.kind') ? I('get.kind') : "all";

        $client = $this->client;
        $res = $client->_predict($id, $kind, $p);

        $this->ilstarr = C('ilstarr');

        //$ilstarri中的0-9 是与 $ilstarr 中的key对应
        $this->ilstarri = C('ilstarri');

        $this->assign('list',$res[0]);  //数据列表

        $page = new \Think\Page($res[1],50); // 实例化分页类 传入总记录数和每页显示的记录数(20)
        $page->setConfig('prev', "上一页");//上一页
        $page->setConfig('next', '下一页');//下一页
        $page->setConfig('first', '首页');//第一页
        $page->setConfig('last', "末页");//最后一页
        $page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
            
        $show = $page->show(); // 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('tcid',trim(I('get.tcid'))); //20170119 jie

        $limit = $page->firstRow.','.$page->listRows;

        $this->display();
    }

    /**
     * 单个或多个订单
     * @return [type] [description]
     */
    public function toSend(){
        // if(!IS_AJAX){
        //     die('非法访问');
        // }

        ini_set('memory_limit','4088M');
        ini_set('max_execution_time', 0);

        $num = trim(I('num'));// MKNO集

        $nums   = explode(',', $num);//转成数组
        $scount = count($nums);

        $tcid = I('tcid');

        $client = $this->client;

        $et  = 0;// 计算成功的总数
        $msg = '';// 用于记录 返回的失败的单号对应的错误信息

        $ids = array(); //ID集

        //逐个MKNO发送
        foreach($nums as $key=>$item){
            $list = $client->getData($item);
            // dump($list);die;

            if(count($list) > 0){

                // 除了“未发送”之外，其他的都表示已经发送过
                if($list[0]['order_opt_state'] > 0){
                    $et++;
                    // unset($nums[$key]); //已经执行过发送的MKNO剔除出数组
                    // $msg .= '【'.$item.' (重复发送) 】；';

                }else{
                    
                    $res = $this->orderSend($list);
                    $res = xmlToArray($res);//xml转数组
                    $response = $res['responseItems']['response'];
                    // dump($res);die;

                    if($response['success'] == 'true'){
                        $et++;
                        $ids[] = $list[0]['id']; //只有返回true的才可以执行状态更新
                    }else{
                        // unset($nums[$key]); //请求失败的MKNO剔除出数组
                        $msg .= '【'.$item.' ('.MKBc2_code($response['reason']).') 】；';
                    }
                }

            }else{
                $msg .= '【'.$item.' (订单不存在) 】；';
            }
        }
// dump($ids);die;
        // 更新状态为已发送
        $save = $client->saveStatus($ids, 1);

        // $et  计算成功获取并保存物流信息的总数
        if($et == 0){
            $backArr = array('do'=>'no', 'msg'=>'操作失败 ；'.$msg);
        }else{
            // 批量订单操作的回复
            if($scount > 1) {
                $backArr = array('do'=>'yes', 'msg'=>'操作成功，请求总数：'.$scount.'个 ；成功执行：'.$et.'个 。操作失败：'.($scount-$et).'个 ( '.$msg.' )');
            }else{// 单个订单操作的回复
                $backArr = array('do'=>'yes', 'msg'=>'操作成功');
            }
        }

        $this->ajaxReturn($backArr);
    }

/*    // 根据批次号进行统一发送   暂时不可用
    public function readyPost(){
        $id = I('id');

        $client = $this->client;
        $nums = $client->_readyPost($id);
        dump($nums);

        $num = array();
        foreach($nums as $k=>$v){
            $num[$k] = $v['MKNO'];
        }
        $num = implode(",",$num);
        dump($num);
    }*/

    /**
     * 订单对接  方法
     * @param  [type] $list [二维数组  订单+商品]
     * @return [type]       [description]
     */
    public function orderSend($list){
        $info = $list[0];//取数组第一个作为订单信息

        //发货方信息
        $sender = array();
        $sender['name']     = htmlspecialchars($info['sender']);   //用户姓名
        $sender['postCode'] = $info['sendcode']; //用户邮编
        $sender['phone']    = $info['sendTel'];  //用户电话，包括区号、电话号码及分机号，中间用“-”分隔；   N
        $sender['mobile']   = $info['sendTel'];  //用户移动电话, 手机和电话两者必需提供一个   N
        $sender['prov']     = $this->config['prov'];  //用户所在省
        $sender['city']     = $this->config['city'];  //用户所在市县（区），市区中间用“,”分隔；注意有些市下面是没有区
        $sender['address']  = htmlspecialchars($info['sendAddr']);  //用户详细地址

        //收货方信息
        $receiver = array();
        $receiver['name']     = htmlspecialchars($info['receiver']);  //用户姓名
        $receiver['postCode'] = $info['postcode'];  //用户邮编
        $receiver['phone']    = $info['reTel'];     //用户电话，包括区号、电话号码及分机号，中间用“-”分隔；   N
        $receiver['mobile']   = $info['reTel'];     //用户移动电话, 手机和电话两者必需提供一个   N
        $receiver['prov']     = $info['province'];  //用户所在省
        $receiver['city']     = $info['city'].','.$info['town'];  //用户所在市县（区），市区中间用“,”分隔；注意有些市下面是没有区
        $receiver['address']  = htmlspecialchars($info['reAddr']);    //用户详细地址

        $total_amount = 0; //总价  根据tran_order里面的详细进行统计，不直接采用tran_list.price
        // 拼接商品信息
        $items = array();
        foreach($list as $key=>$item){
            $items[$key]['itemName']  = htmlspecialchars($item['detail']);  //商品名称
            $items[$key]['number']    = $item['dnumber']; //商品数量
            $items[$key]['itemValue'] = sprintf("%.2f", $item['dprice']);  //商品单价（单位：分 两位小数）

            //统计总价
            $total_amount += intval($item['dnumber']) * floatval($item['dprice']);
        }

        //拼接 订单信息
        $log = array();
        $log['ecCompanyId']        = $this->config['ecCompanyId']; //电商标识（如：TAOBAO，不同电商配置不同的电商标识，例如福满洪城FUMANHONGCHENG） 
        $log['logisticProviderID'] = 'POSTB'; //物流公司ID  固定值
        $log['customerId']         = '0';      //客户标识  固定值  N
        $log['txLogisticID']       = $info['MKNO'];   //物流订单号
        $log['tradeNo']            = '259';   //业务交易号（新业务类型待定：252国内小包） 固定值  N
        $log['mailNo']             = $info['STNO'];  //物流运单号 
        $log['orderType']          = '1';  //订单类型(0-COD 1-普通订单 3 - 退货单),标准接口默认设置为1  固定值
        $log['serviceType']        = '0';  //服务类型(0-自己联系 1-在线下单（上门揽收）4-限时物流 8-快捷COD 16-快递保障)，标准接口默认设置为0  固定值
        // $log['sendStartTime']   = '';   //物流公司上门取货时间段，通过“yyyy-MM-dd HH:mm:ss”格式化，本文中所有时间格式相同。 N
        // $log['sendEndTime']     = '';   //物流公司上门取货时间段，通过“yyyy-MM-dd HH:mm:ss”格式化，本文中所有时间格式相同。 N
        $log['goodsValue']         = sprintf("%.2f",$total_amount); //商品金额，包括优惠和运费，但无服务费
        $log['itemsValue']         = sprintf("%.2f",$total_amount); //
        $log['special']            = '0';  //商品类型（保留字段，暂时不用） N int
        $log['remark']             = htmlspecialchars($info['notes']); //备注  N
        $log['weight']             = $this->num_to_change(0.454 * floatval($info['weight']) * 1000); //商品重量（单位：克）
        // $log['totalServiceFee'] = '0';    //总服务费[COD]：（单位：分）  N
        // $log['buyServiceFee']   = '0';    //买家服务费[COD] ：（单位：分）  N
        // $log['codSplitFee']     = '0';    //物流公司分润[COD] ：（单位：分）  N
        
        $log['sender']             = $sender;
        $log['receiver']           = $receiver;
        $log['items']              = $items;

// dump($log);die;
        $order = array();
        $order['RequestOrder'] = $log;

        $xmlstr = arrayToXml($order, 'item');
        /*$xmlstr = '<?xml version="1.0" encoding="UTF-8"?>'.$xmlstr; //拼接xml报文的头部*/
// dump($xmlstr);die;

        //用“logistics_interface”字段表示要发送的XML内容
        $logistics_interface = $xmlstr;

        //用“data_digest”字段进行签名验证，签名的内容经过md5和base64后
        $data_digest = base64_encode(pack('H*',md5($xmlstr.$this->config['partnered'])));

        // 判断是否需要生成.txt日志文件
        if($this->config['exports_switch'] === true){
            createTxt($info['STNO'], $xmlstr, $this->config['xmlsave']);
        }

/*  指导文档要求的url加密指导有误，无需url加密
      //对xml内容进行URL编码
        $logistics_interface = urlencode($logistics_interface);

        //对签名的字符串进行URL编码
        $data_digest = urlencode($data_digest);*/

        $data = array(
            'logistics_interface' => $logistics_interface, //消息内容 
            'data_digest'         => $data_digest,  //消息签名
            'msg_type'            => 'ORDERCREATE',  // 创建订单  固定值
            'ecCompanyId'         => $this->config['ecCompanyId'], //电商标识
        );


        //调用 发送类
        $http = new \Org\MK\HTTP();
        $rs   = $http->post($this->config['url'], $data);

        return $rs;
    }

    //直接去除小数，保留整数
    private function num_to_change($n){

        $arr = explode('.',$n);

        if(isset($arr[1])){
            return sprintf("%.0f", floatval($arr[0]));
        }else{
            return sprintf("%.0f", $n);
        }
    }

    //取消订单 页面
    public function pre_cancel(){
        $MKNO = I('num');
        $client = $this->client;
        $info = $client->cel_info($MKNO);
        $this->assign('info',$info);
        $this->display();
    }

    // 取消订单 方法
    public function cancel(){
        $MKNO = I('num');   //MKNO
        $tno  = trim(I('tno'));  //操作员输入的运单号
        $note = trim(I('note'));

        $client = $this->client;

        $list = $client->cel_info($MKNO);

        //检验运单号以便确认是否正确操作
        if($list['STNO'] != $tno){
            $backArr = array('state'=>'no', 'msg'=>'运单号核对有误！');
            $this->ajaxReturn($backArr);
        }

        // 尚未对接的订单不允许操作
        if($list['order_opt_state'] == '0'){
            $backArr = array('state'=>'no', 'msg'=>'该运单号尚未对接，禁止此操作');
            $this->ajaxReturn($backArr);
        }

        //已经被取消的订单，不可重复操作
        if($list['order_opt_state'] == '4'){
            $backArr = array('state'=>'no', 'msg'=>'该运单号已被取消，请刷新再试');
            $this->ajaxReturn($backArr);
        }

        $info = array();
        $info[0] = $list;
        $res = $this->toChange($info,$note,'status');

        $res = xmlToArray($res);//xml转数组

        $response = $res['responseItems']['response'];
        // dump($res);die;

        if($response['success'] == 'true'){
            // 更新状态为取消订单
            $save = $client->saveStatus($list['id'], 4, 'single');

            $backArr = array('state'=>'yes', 'msg'=>'成功取消订单');

        }else{

            $backArr = array('state'=>'no', 'msg'=>'操作失败');
        }

        $this->ajaxReturn($backArr);
    }

    /**
     * [toChange 订单状态更改]
     * @param  [type] $arr  [description]
     * @param  [type] $note [description]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public function toChange($arr, $note, $type){
        $fieldList = array();

        foreach($arr as $key=>$item){
            $fieldList[$key]['txLogisticID'] = $item['STNO'];   //物流平台的物流号（不能为空）
            $fieldList[$key]['fieldName']    = $type;  //可更新字段：1、mailNo；2、weight；3、status
            $fieldList[$key]['fieldValue']   = ($type == 'status') ? 'WITHDRAW' : $note;    //字段新值
            $fieldList[$key]['remark']       = ($type == 'status') ? $note : '';   //取消订单、不接单、不揽收时，此字段用于填写原因
        }

        $log = array();
        $log['logisticProviderID'] = 'POSTB';
        $log['ecCompanyId']        = $this->config['ecCompanyId'];
        $log['fieldList']          = $fieldList;

        $UpdateInfo = array();
        $UpdateInfo['UpdateInfo'] = $log;

        $xmlstr = arrayToXml($UpdateInfo, 'field');
        $logistics_interface = $xmlstr;

        //用“data_digest”字段进行签名验证，签名的内容经过md5和base64后
        $data_digest = base64_encode(pack('H*',md5($xmlstr.$this->config['partnered'])));

        $data = array(
            'logistics_interface' => $logistics_interface, //消息内容 
            'data_digest'         => $data_digest,  //消息签名
            'msg_type'            => strtoupper("update"),  // 创建订单  固定值
            'ecCompanyId'         => $this->config['ecCompanyId'], //电商标识
        );

        //调用 发送类
        $http = new \Org\MK\HTTP();
        $rs   = $http->post($this->config['url'], $data);
        return $rs;
    }

    /**
     * 更改运单号   页面
     * @return [type] [description]
     */
    public function new_num(){
        $MKNO = I('num');
        $client = $this->client;
        $info = $client->cel_info($MKNO);
        $this->assign('info',$info);
        $this->display();
    }

    /**
     * 更改运单号   方法
     * @return [type] [description]
     */
    public function exchange(){
        $MKNO = I('num');   //MKNO
        $old  = trim(I('old'));  //操作员输入的运单号
        $new  = trim(I('new'));  //操作员输入的新运单号

        $client = $this->client;

        $list = $client->cel_info($MKNO);

        //检验运单号以便确认是否正确操作
        if($list['STNO'] != $old){
            $backArr = array('state'=>'no', 'msg'=>'运单号核对有误！');
            $this->ajaxReturn($backArr);
        }

        // 尚未对接的订单不允许操作
        if($list['order_opt_state'] == '0'){
            $backArr = array('state'=>'no', 'msg'=>'该运单号尚未对接，禁止此操作');
            $this->ajaxReturn($backArr);
        }

        //已经被取消的订单，不可重复操作
        if($list['order_opt_state'] == '4'){
            $backArr = array('state'=>'no', 'msg'=>'该运单号已被取消，请刷新再试');
            $this->ajaxReturn($backArr);
        }

        $info = array();
        $info[0] = $list;
        $res = $this->toChange($info, $new, 'mailNo');

        $res = xmlToArray($res);//xml转数组

        $response = $res['responseItems']['response'];
        // dump($res);die;

        if($response['success'] == 'true'){
            // 更新状态为取消订单
            $save = $client->newNUM($MKNO, $new);

            $backArr = array('state'=>'yes', 'msg'=>'成功更改运单号');

        }else{

            $backArr = array('state'=>'no', 'msg'=>'更改失败');
        }

        $this->ajaxReturn($backArr);
    }

//=============== 批号对数  ============
    /**
     * 中转跟踪
     * @return [type] [description]
     */
    public function tran_track(){
        
        $type = (C('Transit_Type.MKBc2_Transit')) ? C('Transit_Type.MKBc2_Transit') : '';

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

        $type = (C('Transit_Type.MKBc2_Transit')) ? C('Transit_Type.MKBc2_Transit') : '';
        
        $list = R('Logarithm/pro_two',array('request'=>true, 'type'=>$type));

        $this->assign('list',$list);
        $this->assign($_GET);
        $this->display('Public:logistics_strack');
    }

}