<?php
namespace AUApi\Controller\KdnoConfig;
/*
	版本号：V1.0
	创建人：Jie
	创建日期：2017-12-01
	修改日期：2017-12-01
	用途：1.香港E特快 对接下单
	指导文档：物流对接汇总/2017-12-01 香港E特快/eexpressAPI.PDF
 */
require_once('Kdno11.function.php'); //功能函数
class Kdno11{
    protected $_soapError='';				// soap的错误信息
    protected $_response;				// 请求结果
    protected $_funcy;
    protected $_ZplShipmentLabel = '';	// Zpl类型的标签
    protected $STEXT        = '';		// 返回快递其他内容
    protected $no           = '';		// 返回快递号码 mailno
    protected $config       = array();
    protected $Order_config = array();

    function _initialize(){
        ini_set('memory_limit','500M');
        set_time_limit(0);
        header('Content-type:text/html;charset=UTF-8');	//设置输出格式

        require_once('Kdno11.conf.php');	//载入配置信息

        $this->config       = $config;
        $this->Order_config = $Order_config;
    }

    public function data($data){
        return $this->Shipment_Label_Zpl($data);
    }

    // 客户下单
    public function Shipment_Label_Zpl($data){
        self::_initialize();
        $order = $data['Order'];

        //生成 香港E特快 物流格式数据
        $log = array(
            'OrderNo'         => $data['MKNO'], //订单号
            'RefNumber'       => $data['MKNO'], //参考编号 Y
            'ReceiveName'     => $data['receiver'],//收件人姓名   Y
            'ReceiveAddress'  => $data['reAddr'],//收件人地址   Y
            'ReceiveState'    => $data['province'],//收件人省份   Y
            'ReceiveCity'     => $data['city'],//收件人城市   Y
            'ReceiveZipcode'  => $data['postcode'],//收件人邮编   Y
            'ReceiveMobile'   => $data['reTel'],//收件人电话   Y
            'TotalWeight'     => num_to_change(0.454 * floatval($data['weight'])),//总重，不超过10KG   Y
            'Currency'        => $data['coin'],//货币（USD,CNY,HKD,EUR）
            'DeclareValue'    => ($data['premium'] == 0) ? $this->Order_config['dValue'] : $data['premium'],//申报价值
            'PayTaxe'         => $this->Order_config['duty_paid'],//代付税金
        );
        // 货物信息列表
        foreach($order as $key=>$v){
            $key++;
            $log['ProductName'.$key] = $v['catname'];		//商品详细描述信息	Y
            $log['UnitPrice'.$key]   = sprintf("%.2f", C('US_TO_RMB_RATE') * $v['price']);//商品申报价格  Y 转人民币
            $log['Count'.$key]       = $v['number'];		//商品件数  Y
        }

        $this->_funcy = __FUNCTION__;

        $res = $this->useSoap($log, $this->config, $this->_funcy);

        $txt = json_decode($this->_responseData,TRUE);
        // dump($log);die;
        if(is_array($txt) && isset($txt)){

            if(strtolower($txt['Result']) == 'false' || strtolower($txt['Result']) == 'f'|| strtolower($txt['Result']) == 'fail'){

                $this->STEXT = array('ErrorStr'=>'EMS反馈：'.$txt['Message']);//20171019 Jie

                // if(preg_match('/Duplicate Consignee/', $txt['Message'])){
                // 	require_once('Kdno11.save.php'); //保存数据的类
                // 	$SA = new \save();
                // 	$info = $SA->getData($data['MKNO']);//保存数据

                // 	if(!$info){
                // 		$this->STEXT = array('ErrorStr'=>'EMS反馈：重复下单，但美快查无此单');//20171019 Jie
                // 	}

                // 	$this->no = $info['STNO'];

                // 	$this->CustomerGetLabel();//获取打印编码

                // 	// 返回快递其它内容
                // 	$this->STEXT = json_decode(base64_decode($info['STEXT']), true);
                // 	$this->STEXT['zpl'] = $this->_ZplShipmentLabel;// 打印编码

                // }else{

                // 	$this->STEXT = array('ErrorStr'=>'EMS反馈：'.$txt['Message']);//20171019 Jie
                // }

            }else{

                // 打印编码为空
                if(trim($txt['ZplShipmentLabel']) == ''){
                    $this->STEXT = array('ErrorStr'=>'EMS反馈：该订单打印编码为空');
                }else{
                    $this->no = (isset($txt['ShipmentNumber'])) ? $txt['ShipmentNumber'] : '';	// 分配到的跟踪号码 mailno

                    // 返回快递其它内容
                    $this->STEXT = array(
                        'destcode'   => (isset($txt['Areacode'])) ? $txt['Areacode'] : '',// 20171019 jie
                        'mailno'     => (isset($txt['ShipmentNumber'])) ? $txt['ShipmentNumber'] : '',// 分配到的跟踪号码 20171019 jie
                        'origincode' => (isset($txt['originCode'])) ? $txt['originCode'] : '',
                        'orderid'    => $data['MKNO'],
                        'custid'     => (isset($txt['ParcelId'])) ? $txt['ParcelId'] : '', // 创建成功的包裹编号 20171019 jie
                        'zpl'        => (isset($txt['ZplShipmentLabel'])) ? base64_encode(trim($txt['ZplShipmentLabel'])) : '', // 打印编码
                    );
                }

            }

            return $this->_response;// 把返回的原始json直接返回给ERP系统
        }else{
            $this->STEXT = array('ErrorStr'=>'EMS反馈：该订单下单无反馈');
        }
    }

    // 包裹标签下载 未可使用
    public function CustomerGetLabel(){
        // self::_initialize();

        $arr = array(
            'ShipmentNumber' => $this->no,//HKETK返回的邮政单号
            'LabelType'      => $this->config['LabelType'],//需要下载的格式类型
        );

        $this->_funcy = __FUNCTION__;

        $this->useSoap($arr, $this->config, $this->_funcy);

        $txt = json_decode($this->_responseData,TRUE);
        // print_r($txt);die;
        if($txt['Result'] == 'Success'){
            $this->_ZplShipmentLabel = base64_encode($txt['ZplShipmentLabel']);
        }
        // return $txt;
    }
//======================================================================

    /**
     * 用soap 发送请求
     * @param  [type] $data        [base64加密之后的报文]
     * @param  [type] $validateStr [校验码]
     * @return [type]              [description]
     */
    public function useSoap($data, $config, $funcy){

        if(!is_dir($config['xmlsave'])) mkdir($config['xmlsave'], 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹
        $file_name = 'Kdno11_'.$funcy.'_'.date('Ymd').'.txt';   //文件名     

        try {
            $args = array('strJson' => json_encode($data));
            try {

                // $soap = new \SoapClient($config['pmsLoginAction']);//网络服务请求地址
                // 关闭soap缓存
                $soap = new \SoapClient($config['pmsLoginAction'], array('soap_version'=>SOAP_1_1, 'trace'=>1, 'cache_wsdl'=>WSDL_CACHE_NONE,' wsdl_cache_ttl'=>WSDL_CACHE_NONE, 'encoding'=>'UTF-8', 'wsdl_cache_limit'=>0,'wsdl_cache_enabled'=>0));

            } catch (\SoapFault $e) {

                $this->_soapError = sprintf('1.SoapFault: (%s) %s', $e->getCode(), $e->getMessage());

                $content = "===================== ".date('Y-m-d H:i:s')." =====================\r\n\r\n-------- soapError --------\r\n\r\n".json_encode($this->_soapError)."\r\n\r\n";

                if(is_file($file_name)){
                    file_put_contents($config['xmlsave'].$file_name, $content);
                }else{
                    file_put_contents($config['xmlsave'].$file_name, $content, FILE_APPEND);
                }

                echo $this->_soapError;
                die();

            }

            // 设置 soap header 认证
            $authvalues = array('CustomerCode'=>$config['CustomerCode'], 'Key'=>$config['Key']);

            $header = new \SoapHeader('http://tempuri.org/','MySoapHeader',$authvalues,true);
            $soap->__setSoapHeaders($header);

            //调取需要的webservice方法

            // $functions = $soap->__getFunctions ();
            // dump ($functions);
            // echo '<pre>';
            // print_r($soap);
            // die;

            try {

                $result = $soap->__soapCall($funcy, array($args));

                $this->_setResponse($result);//处理请求结果

            } catch (SoapFault $exception) {

                $this->_soapError = sprintf('2.SoapFault: (%s) %s', $exception->getCode(), $exception->getMessage());

                $content = "===================== ".date('Y-m-d H:i:s')." =====================\r\n\r\n-------- soapError --------\r\n\r\n".json_encode($this->_soapError)."\r\n\r\n";

                if(is_file($file_name)){
                    file_put_contents($config['xmlsave'].$file_name, $content);
                }else{
                    file_put_contents($config['xmlsave'].$file_name, $content, FILE_APPEND);
                }

                echo $this->_soapError;
                die();
            }

        } catch (\SoapFault $e) {
            $this->_soapError = sprintf('3.SoapFault: (%s) %s', $e->getCode(), $e->getMessage());
            
            $content = "===================== ".date('Y-m-d H:i:s')." =====================\r\n\r\n-------- soapError --------\r\n\r\n".json_encode($this->_soapError)."\r\n\r\n";

            if(is_file($file_name)){
                file_put_contents($config['xmlsave'].$file_name, $content);
            }else{
                file_put_contents($config['xmlsave'].$file_name, $content, FILE_APPEND);
            }

            echo $this->_soapError;
            die();

        } catch (\Exception $e) {
            $this->_soapError = sprintf('4.Exception: (%s) %s', $e->getCode(), $e->getMessage());

            $content = "===================== ".date('Y-m-d H:i:s')." =====================\r\n\r\n-------- soapError --------\r\n\r\n".json_encode($this->_soapError)."\r\n\r\n";

            if(is_file($file_name)){
                file_put_contents($config['xmlsave'].$file_name, $content);
            }else{
                file_put_contents($config['xmlsave'].$file_name, $content, FILE_APPEND);
            }

            echo $this->_soapError;
            die();
        }

        // 是否输出txt文件 20171208 jie
        if($config['exports_switch'] === true){

            if(!is_dir($config['xmlsave'])) mkdir($config['xmlsave'], 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

            $file_name = 'Kdno11_'.$funcy.'_'.date('Ymd').'.txt';	//文件名

            $content = "===================== ".date('Y-m-d H:i:s')." =====================\r\n\r\n-------- RequestData --------\r\n\r\n".json_encode($data)."\r\n\r\n-------- ResponseData --------\r\n\r\n".json_encode($this->_responseData)."\r\n\r\n";

            if(is_file($file_name)){
                file_put_contents($config['xmlsave'].$file_name, $content);
            }else{
                file_put_contents($config['xmlsave'].$file_name, $content, FILE_APPEND);
            }
        }

        // return $result;

    }

    protected function _setResponse($result)
    {
        if (is_object($result)) {
            $attribute = $this->_funcy . 'Result';
            $this->_responseData = $result->$attribute;
            $this->_response = $result;
            // if($this->_funcy != 'CustomerSubmitOrder') $this->_ZplShipmentLabel = $result->$attribute;
        }
    }

	// 提供途径去下载 包裹标签  可以使用，但未上线
	public function toGetLabel($no){
		self::_initialize();

		$arr = array(
			'ShipmentNumber' => $no,//HKETK返回的邮政单号
			'LabelType'      => 1,//需要下载的格式类型  0，Zpl; 1，Pdf
		);

		$this->_funcy = 'CustomerGetLabel';

		$this->useSoap($arr, $this->config, $this->_funcy);
		$txt = json_decode($this->_responseData,true);

        // print_r($txt);die;
		if($txt['Result'] == 'True'){
			return (isset($txt['ShipmentLabel'])) ? trim($txt['ShipmentLabel']) : '';
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