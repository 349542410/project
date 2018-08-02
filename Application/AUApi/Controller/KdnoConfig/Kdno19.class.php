<?php
namespace AUApi\Controller\KdnoConfig;
/*
	版本号：V1.0
	创建人：Jie
	创建日期：2017-12-19
	修改日期：2017-12-21
	用途：1.中邮(新的西安ems) 对接下单
	指导文档：物流对接汇总/2017-12-18 中邮/中邮接口说明文档V1.7.docx
 */
require_once('Kdno19.function.php'); //功能函数

class Kdno19{
    protected $_soapError;				// soap的错误信息
    protected $_requestData;			//请求的数据
    protected $_response;				//请求结果
    protected $_responseData;			//请求结果中的PDFStream字段
    protected $_funcy;
    protected $_version;				// soap版本
    protected $STEXT        = '';		// 返回快递其他内容
    protected $no           = '';		// 返回快递号码 mailno
    protected $config       = array();
    protected $Order_config = array();
    protected $OrderesBoxed_config = array();

    // soap 认证配置
    private $_soapConfig = array(
        'trace'              => true,
        'login'              => '',
        'password'           => '',
        'connection_timeout' => 120,
        'keep_alive'         => true,
    );

    function _initialize(){
        ini_set('memory_limit','500M');
        set_time_limit(0);
        header('Content-type:text/html;charset=UTF-8');	//设置输出格式
        // libxml_disable_entity_loader(false);
        require_once('Kdno19.conf.php');	//载入配置信息

        $this->config              = $config;
        $this->Order_config        = $Order_config;
        $this->OrderesBoxed_config = $OrderesBoxed_config;
        $this->_version            = $config['Version'];
        // foreach ($config as $name => $value) $this->$name = $value;
    }

    // 创建并打印面单
    public function data($data){
        self::_initialize();
        $order = $data['Order'];

        //商品列表
        $detail = array();

        // 内件详情
        foreach($order as $key=>$v){
            $detail[$key]['CategoryCode'] = '';//$v['catname'];		//品类 至少填写一个，以TariffNumber为准	Y
            $detail[$key]['TariffNumber'] = (!empty(trim($v['tariff_no']))) ? ((is_numeric(trim($v['tariff_no'])) && strlen(trim($v['tariff_no'])) == 8) ? $v['tariff_no'] : $this->Order_config['TariffNumber']) : $this->Order_config['TariffNumber'];					//行邮税号 至少填写一个，以TariffNumber为准	Y
            $detail[$key]['GoodsName']    = $v['detail'];		//申报名称	Y
            $detail[$key]['Brands']       = $v['brand'];		//品牌	Y
            $detail[$key]['ModelNo']      = (!empty(trim($v['specifications']))) ? $v['specifications'] : '500粒';//$v['specifications'];		//型号	Y
            $detail[$key]['Qty']          = $v['number'];		//数量	Y
            $detail[$key]['Unit']         = $v['unit'];		    //单位	Y
            $detail[$key]['Price']        = sprintf("%.2f", ($v['price'] / $this->config['rmb_rate'] * $this->config['percent']));		//申报单价	Y
        }

        // 只显示发件人的省市区，其余用 * 隐藏
        $data['sendAddr'] = $this->Order_config['FromProvince']." ".$this->Order_config['FromCity']." ".$this->Order_config['FromArea']." *****";

        //生成 物流格式数据
        $log = array(
            'Goods' => $detail,//内件详情
            //发件人信息
            'Sender' => array(
                'FromAddress'  => $data['sendAddr'],//地址 Y
                'FromMobile'   => hidtel($data['sendTel']),//移动电话 Y
                'FromName'     => substr_cut($data['sender']),//姓名 Y
                'FromZIP'      => $data['sendcode'],//邮编 Y
                'FromArea'     => $this->Order_config['FromArea'],//区/县 Y
                'FromCity'     => $this->Order_config['FromCity'],//城市 Y
                'FromEmail'    => $this->Order_config['FromEmail'],//邮箱 Y
                'FromProvince' => $this->Order_config['FromProvince'],//省份 Y
            ),
            //收件人信息
            'Addressee' => array(
                // 只显示收件人的省市区，其余用 * 隐藏
                'ToAddress'      => $data['province']." ".$data['city']." ".$data['town']." *****",//$data['reAddr'],//地址 Y
                'ToArea'         => $data['town'],//区/县 Y
                'ToCity'         => $data['city'],//城市 Y
                'ToEmail'        => (!empty($data['email'])) ? $data['email'] : 'dev@megao.cn',//邮箱 Y
                'ToMobile'       => hidtel($data['reTel']),//移动电话 Y
                'ToName'         => substr_cut($data['receiver']),//姓名 Y
                'ToZIP'          => $data['postcode'],//邮编 Y
                'ToProvince'     => $data['province'],//省份
                'ToProvinceCode' => province_code($data['province']),//省份编码 Y
            ),
            'ChannelCode'            => $this->Order_config['ChannelCode'],//渠道 Y
            'CustomerIdentity'       => $this->Order_config['CustomerIdentity'],//客户标识 Y
            'TrackingCenterCode'     => $this->Order_config['TrackingCenterCode'],//货站编码 Y
            'HasPrepaid'             => $this->Order_config['HasPrepaid'],//是否代缴关税 Y
            'HasReplaceUploadIdCard' => $this->Order_config['HasReplaceUploadIdCard'],//是否代传身份证 Y
            // 'InsureStatus'           => $this->Order_config['InsureStatus'],//是否投保 Y
            'Length'                 => $this->Order_config['Length'],//长 Y
            'Width'                  => $this->Order_config['Width'],//宽 Y
            'Height'                 => $this->Order_config['Height'],//高 Y
            'Origin'                 => $this->Order_config['Origin'],//原产地 Y
            'Weight'                 => $data['weight'],//包裹重量 Y
            // 'EMSTrackingNumber'      => '',//物流跟踪号
            'IdCardNumber'           => '',//身份证号码
        );

        // 决定某些字段是否显示/隐藏
        if(!$this->config['hide_or_not']){
            $log['InsureStatus'] = $this->Order_config['InsureStatus'];//是否投保 Y
        }

        $this->_requestData = $log;
        $postData = $this->_setRequest();
        // dump($postData);
        // die;
        $this->_funcy = 'CreatedAndPrintOrder';

        $this->useSoap($postData, $this->config, $this->_funcy);

        $res = $this->_response; //请求结果

        // print_r($res);
        // print_r($this->_responseData);
        // dump($res);
        // die;

        // 如果返回的数据是对象类型
        if(is_object($res)){
            //对象 解释为 数组
            $res = json_decode(json_encode($res),TRUE);
        }else{
            $this->STEXT = array('ErrorStr'=>'返回的数据类型无法识别');
        }

        // dump($res);
        // die;

        // 如果soap错误信息不为空
        if(!empty(trim($this->_soapError))){
            $this->STEXT = array('ErrorStr'=>$this->_soapError);
        }

        $txt = $res['CreatedAndPrintOrderResult'];

        if(isset($txt) && is_array($txt)){

            if($txt['ResponseResult'] == 'Failure'){
                $this->STEXT = array('ErrorStr'=>'中邮反馈：'.(!empty($txt['ResponseError']['LongMessage'])?$txt['ResponseError']['LongMessage']:$txt['ResponseError']['ShortMessage']));//20171019 Jie

            }else{

                // 返回快递其它内容
                $this->STEXT = array(
                    'destcode'   => (isset($txt['Mark'])) ? $txt['Mark'] : '',// 20171019 jie
                    'mailno'     => (isset($txt['Data']['TrackingNumber'])) ? $txt['Data']['TrackingNumber'] : '',// 分配到的跟踪号码
                    'origincode' => (isset($txt['originCode'])) ? $txt['originCode'] : '',
                    'orderid'    => $data['MKNO'],
                    'custid'     => (isset($txt['ParcelId'])) ? $txt['ParcelId'] : '', // 创建成功的包裹编号 20171019 jie
                );
                // print_r($this->STEXT);die;
                $this->no = (isset($txt['Data']['TrackingNumber'])) ? $txt['Data']['TrackingNumber'] : '';	// 分配到的跟踪号码 mailno
            }

            return $result;// 把返回的原始json直接返回给ERP系统
        }else{
            $this->STEXT = array('ErrorStr'=>'中邮反馈：该订单下单无反馈');
        }

    }

    /**
     * 订单装板
     * 该接口功能为根据客户订单号或 EMS 订单号对订单装板
     */
    public function OrderesBoxed($data, $type=''){
        self::_initialize();

        // 字符串转成数组
        if(is_string($data)){
            if(strlen($data) == 0){
                return array('state'=>'0', 'msg'=>'订单号集合为空');
            }

            // 把空格、换行符、中文逗号等替换成英文逗号
            $data = preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)|(\.)|(\/)|(\\\)|(、)|(；)|(;)/", ',', $data);

            $data = trim($data);

            $data = explode("," , $data);
        }

        if(count($data) == 0){
            return array('state'=>'0', 'msg'=>'订单号集合为空');
        }

        $OrderesBoxedModel = array(
            'OrderNumbers'       => $data,//EMS 物流追踪号或客户订单号集合  Y
            'NumberType'         => ($type != '') ? $type : $this->OrderesBoxed_config['NumberType'],//号码类型，1-客户订单号,2-EMS 跟踪号  Y
            'CustomerIdentity'   => $this->Order_config['CustomerIdentity'],//客户标识  Y
            'Weight'             => $this->OrderesBoxed_config['Weight'],//重量 KG   Y
            'Length'             => $this->OrderesBoxed_config['Length'],//长度 CM   Y
            'Height'             => $this->OrderesBoxed_config['Height'],//高度 CM   Y
            'Width'              => $this->OrderesBoxed_config['Width'],//宽度 CM   Y
            'ChannelCode'        => $this->Order_config['ChannelCode'],//渠道编码   Y
            'TrackingCenterCode' => $this->Order_config['TrackingCenterCode'],//货站编码   Y
        );
// dump($OrderesBoxedModel);die;
        $this->_requestData = $OrderesBoxedModel;
        $postData = $this->_setRequest();

        $this->_funcy = 'OrderesBoxed';

        $this->useSoap($postData, $this->config, $this->_funcy);

        $res = $this->_response; //请求结果
        return json_decode(json_encode($res),TRUE);
    }

    // 订单装板查询
    public function QueryOrderBoxedInfo($data){
        self::_initialize();

        $QueryOrderBoxedInfoMode = array(
            'OrderNumber' => 'BZ002416085US',//$data,
            'NumberType' => 2,
            'CustomerIdentity' =>$this->Order_config['CustomerIdentity'],//客户标识  Y
        );

        $this->_requestData = $QueryOrderBoxedInfoMode;
        $postData = $this->_setRequest();

        $this->_funcy = 'QueryOrderBoxedInfo';

        $this->useSoap($postData, $this->config, $this->_funcy);

        $res = $this->_response; //请求结果
        return json_decode(json_encode($res),TRUE);

    }

//======================================================================
    /**
     * 每个接口都要求有header认证
     */
    private function _header()
    {
        $_ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
        $_ws = array('Username' => $this->config['Username'], 'Password' => $this->config['Password']);
        //var_dump($_ws);
        $_u = new \SoapVar($_ws['Username'], XSD_STRING, null, null, 'Username', $_ns);
        $_p = new \SoapVar($_ws['Password'], XSD_STRING, null, null, 'Password', $_ns);
        $token = new \SoapVar(array($_u, $_p), SOAP_ENC_OBJECT, null, null, 'UsernameToken', $_ns);
        $security = new \SoapVar(array($token), SOAP_ENC_OBJECT, null, null, null, $_ns);
        return new \SoapHeader($_ns, 'Security', $security);
    }

    private function _setRequest()
    {
        $headArgs['Version'] = $this->_version;
        preg_match('/0\.(\d+) (\d+)/', microtime(), $p);
        $headArgs['RequestTime'] = sprintf('%s.%sZ', date('Y-m-d\TH:i:s', $p[2]), $p[1]);
        preg_match('/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/', md5(session_id()), $p);
        $headArgs['RequestId'] = sprintf('%s-%s-%s-%s-%s', 'wonroads', $p[2], $p[3], $p[4], $p[5]);
        $headArgs['Data'] = $this->_requestData;
        return $headArgs;
    }

    /**
     * 用soap 发送请求
     * @param  [type] $data        [base64加密之后的报文]
     * @param  [type] $validateStr [校验码]
     * @return [type]              [description]
     */
    public function useSoap($data, $config, $funcy){

        try {
            $args = array('request' => $data);
            try {

                $soap = new \SoapClient($config['pmsLoginAction'], $this->_soapConfig);//网络服务请求地址

            } catch (\SoapFault $e) {

                $this->_soapError = sprintf('1.SoapFault: (%s) %s', $e->getCode(), $e->getMessage());
                echo $this->_soapError;
                die();

            }

            $soap->__setSoapHeaders($this->_header());

            // $functions = $soap->__getFunctions ();
            // dump ($functions);
            // echo '<pre>';
            // print_r($args);
            // die;

            try {

                $result = $soap->__soapCall($funcy, array($args));
                // echo '<pre>';
                // // print_r($result);
                // $result->CreatedAndPrintOrderResult->Data->PDFStream = '';
                // dump($result);
                // dump(json_encode($result));die;
                // die;

            } catch (\SoapFault $exception) {

                $this->_soapError = sprintf('2.SoapFault: (%s) %s', $exception->getCode(), $exception->getMessage());
                echo $this->_soapError;
                die();
            }

            $this->_setResponse($result);//处理请求结果

        } catch (\SoapFault $e) {
            $this->_soapError = sprintf('3.SoapFault: (%s) %s', $e->getCode(), $e->getMessage());
            echo $this->_soapError;
            die();

        } catch (\Exception $e) {
            $this->_soapError = sprintf('4.Exception: (%s) %s', $e->getCode(), $e->getMessage());
            echo $this->_soapError;
            die();
        }

        // 是否输出txt文件 20171208 jie
        if($config['exports_switch'] === true){

            if(!is_dir($config['xmlsave'])) mkdir($config['xmlsave'], 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

            $file_name = 'Kdno19_'.$funcy.'_'.date('Ymd').'.txt';	//文件名

            $content = "===================== ".date('Y-m-d H:i:s')." =====================\r\n\r\n-------- RequestData --------\r\n\r\n".json_encode($data)."\r\n\r\n-------- ResponseData --------\r\n\r\n".json_encode($this->_response)."\r\n\r\n";

            if(is_file($file_name)){
                file_put_contents($config['xmlsave'].$file_name, $content);
            }else{
                file_put_contents($config['xmlsave'].$file_name, $content, FILE_APPEND);
            }
        }

        // return $result;

    }

    /**
     * 接口response部分
     */
    public function getResponse()
    {
        return $this->_response;
    }

    public function getResponseData()
    {
        return $this->_responseData;
    }

    protected function _setResponse($result)
    {
        if (is_object($result)) {
            $attribute = $this->_funcy . 'Result';
            if (isset($result->$attribute) and is_object($result->$attribute)) {
                if($this->_funcy == 'CreatedAndPrintOrder'){
                    $this->_responseData = $result->$attribute->Data->PDFStream;
                    $result->$attribute->Data->PDFStream = '已被临时屏蔽';//PDFStream字段是数据流，长度太大，屏蔽处理
                }
                $this->_response = $result;
            }
        }
    }

//===============================================================

    /**
     * 返回快递其他内容 20160907 jie
     * @return [type] [description]
     */
    public function get(){
        if($this->STEXT != ''){
            return base64_encode((json_encode($this->STEXT)));
        }else{
            return $stext = '';
        }
    }

    /**
     * 返回快递号码 mailno  20160907 jie
     * @return [type] [description]
     */
    public function no(){
        return $this->no;//直接返回mailno
    }

}