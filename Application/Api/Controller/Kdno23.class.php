<?php
/*
	版本号：V1.0
	创建人：Jie
	创建日期：2018-06-07
	修改日期：2018-06-28
	用途：1.与 广州邮政（傲石） 对接下单
	指导文档：物流对接汇总/2018-05-20 广州邮政（傲石）综合清关开放平台/20180606ems线路对接/综合清关服务平台BC直邮开放Api.docx
 */
class Kdno{

    protected $_requestData;			//请求的数据
    protected $_response;				//请求结果
    protected $_responseData;			//请求结果中的PDFStream字段
    protected $_funcy;                  //接口地址
    protected $sign;                    //签名
    protected $data_arr = array();
    protected $STEXT        = '';		// 返回快递其他内容
    protected $no           = '';		// 返回快递号码 mailno
    protected $config       = array();
    protected $Order_config = array();

    function _initialize()
    {
        ini_set('memory_limit','500M');
        set_time_limit(0);
        header('Content-type:text/html;charset=UTF-8');	//设置输出格式
        // libxml_disable_entity_loader(false);
        require_once(dirname(__FILE__).'\Kdno23.conf.php');	//载入配置信息

        $this->config              = $config;
        $this->Order_config        = $Order_config;
    }

    //订单提交
    public function submitDeliveryOrder($data)
    {
        self::_initialize();

        $log = array(
            'deliveryOrderNo'          => $data['MKNO'],//todo 报关单号、提单号、总运单号(暂时填美快单号 有错以后再改)Y
            'clearanceDestinationCode' => $this->Order_config['clearanceDestinationCode'],//清关目的地	Y
            'masterOrderTotalNum'      => 1,//todo 订单总数(现在是处理一单的)  Y
            'masterOrderTotalAmount'   => $data['price'],//订单总金额  Y
//			'expressCompanyCode'       => 'ems',//快递公司代码  N
            'totalGrossWeight'         => $data['weight'],//todo 提单总毛重 Y
            'totalNetWeight'           => $data['weight'],//todo 提单总的净重 Y
            'transportType'            => 1,//todo 运输工具类型   Y
            'transportCode'            => 5,//todo 运输工具代码 详情在海关代码表  Y
            'transportNo'              => '001',//todo 运输工具编号 如果是飞机填写航班号,公路则填写车牌号 Y
//            'flightNo'                 => '',//航空号  N
            'loadingCountry'           => '502',//todo 启运国 填写国家或者地区代码 详情在海关代码表  Y
            'loadingPort'              => '502',//todo 起运港 详情在海关代码表 Y
            'startFlyTime'             => '2018-06-10 23:59:59',//todo 航班起飞时间 格式:YYYY-MM-DD HH:mm:ss Y
            'arrivePlanDate'           => '2018-06-15 23:59:59',//todo 抵达时间    格式:YYYY-MM-DD HH:mm:ss Y
        );

        $this->_funcy = '/order/submitDeliveryOrder';

        $this->filename = 'submitDeliveryOrder_'.date('Ymd').'.txt';	//文件名

        $this->sign($log);

        $this->_setRequest();

        $res = $this->getResponseData();//请求结果

        //返回的是json
        $arr = json_decode($res,true);

        if(isset($arr) && is_array($arr)){

            if($arr['code'] != '1000'){

                $this->STEXT = array('ErrorStr'=>'邮政反馈：'.$arr['desc']);

            }else{
                $this->submit($data);
            }
        }else{
            //20180620因为成功的时候$arr还是一个json格式(即成功返回的不是意义上的json数据) 所有进一步解析 以后跟EMS公司沟通了再改
            $arr = json_decode($arr,true);

            if(is_array($arr)){
                $this->submit($data);
            }else{
                $this->STEXT = array('ErrorStr'=>'邮政反馈：该订单提交无反馈');
            }
        }
    }

    // 订单推送
    public function submit($data)
    {
        $order = $data['Order'];

        $detail = array();//商品列表

        // 内件详情
        foreach($order as $key=>$v){
            $detail[$key]['barcode']         = $v['barcode'];		//商品条形码     Y
            $detail[$key]['buyCountStr']     = $v['number'];		//商品数量	Y
            $detail[$key]['salePrice']       = $v['price'];		//单价，单位：元	Y

            // 以下字段不需要填写
//            $detail[$key]['itemName']        = $v['detail'];		//物品名称   Y
//            $detail[$key]['originCountry']   = $v['source_area'];		//原产国	Y
//            $detail[$key]['skuCode']         = $v['hgid'];		//商品货号     Y
//            $detail[$key]['productUnit']     = $v['unit'];	//计量单位：如瓶，个，件等	Y
//            $detail[$key]['itemtNetWeight']  = $v['att5'];//todo 商品的净重  不确定是否是该字段 Y
//            $detail[$key]['itemGrossWeight'] = $v['weight'];//todo 商品的毛重  不确定是否是该字段 Y
//            $detail[$key]['specifications']  = $v['specifications'];		//规格型号，如120粒/瓶	Y
//            $detail[$key]['taxNo']           = $v['hs_code'];		//行邮税号	Y
//            $detail[$key]['buyCount']        = $v['number'];		//商品数量	Y
//            $detail[$key]['lawUnit1']        = $v['unit'];		// 第一法定单位	Y
//            $detail[$key]['lawCount1']       = $v['number'];		// 第一法定数量	Y
//            $detail[$key]['itemEnName']      = $v[''];	// todo ??? 英文品名	Y
//            $detail[$key]['brandName']       = $v['brand'];		//品牌	Y
//            $detail[$key]['inspectionId']    = $v[''];//todo ??? 商检备案号,商品备案时,返回	Y
            $detail[$key]['itemImg']         = $this->base64_image($v['att6']);//商品图片的Base64数据  todo 要求发送  N
        }

        $detail_json = json_encode($detail);


//        $data['tran_no'];//todo 批次号

        //生成 物流格式数据
        $log = array(
            'masterWayBillNo'          => $data['MKNO'],//每批订单生成一个总运单号(暂时填mkno 有错以后改) Y
            'clearanceDestinationCode' => $this->Order_config['clearanceDestinationCode'],//清关目的地	Y
            'logisticsCompany'         => $this->Order_config['logisticsCompany'],//快递公司  Y
            'orderProxyFlag'           => $this->Order_config['orderProxyFlag'],//代理订单推送标志  Y
            'outOrderNo'               => $data['auto_Indent2'],//客户电商平台的订单号  Y
//			'payAmount'                => $data['price'],//实际支付金额  N
//			'totalTax'                 => $this->Order_config['totalTax'],//实际总税费  N
//			'postAmount'               => $this->Order_config['postAmount'],//实际运费  N
            'packageType'              => $data['packageType'],//包装类型 详情见海关代码表 Y
//			'PayEntNo'                 => $data['paykind'],//支付企业代码  N
//			'payEntName'               => $data['paykind'],//支付企业名称  N
//			'payNo'                    => $data['payno'],//支付流水号  N
            'shipperCountryCode'       => $this->Order_config['shipperCountryCode'],//发件人国家【溯源必填】   Y
            'shipperName'              => $data['sender'],//发件人姓名    Y
            'shipperMobile'            => $data['sendTel'],//发件人电话  Y
            'shipperCity'              => $this->Order_config['shipperCity'],//发件人城市  Y
            'shipperAddress'           => $data['sendAddr'],//发件人地址   Y
            'receiverName'             => $data['receiver'],//收件人姓名 Y
            'receiverMobile'           => $data['reTel'],//收件人手机
            'receiverAddress'          => $data['reAddr'],//收件人地址 Y
            'receiverProvince'         => $data['province'],//省份
            'receiverCity'             => $data['city'],//城市 Y
            'receiverDistrict'         => $data['town'],//区/县 Y
            'receiverCardNo'           => $data['idno'],//收件人证件号码（身份证）  Y
            'productListString'        => $detail_json,//订单明细列表	Y
        );

        $this->_funcy = '/order/submit';

        $this->filename = 'submit_'.date('Ymd').'.txt';	//文件名

        $this->sign($log);

        $this->_setRequest();

        $res = $this->getResponseData();//请求结果

        //返回的是json
        $arr = json_decode($res,true);

        if(isset($arr) && is_array($arr)){

            if($arr['code'] != '1000'){
                $this->STEXT = array('ErrorStr'=>'邮政反馈：'.$arr['desc']);
            }else{
                $this->STEXT = array('success'=>'邮政反馈：推单模拟成功');
//                $txt = $arr['order'];
//
//                // 返回快递其它内容
//                $this->STEXT = array(
//                    'destcode'   => (isset($txt['Mark'])) ? $txt['Mark'] : '',// 20171019 jie
//                    'mailno'     => (isset($txt['logisticsCode'])) ? $txt['logisticsCode'] : '',// 分配到的跟踪号码
//                    'origincode' => (isset($txt['originCode'])) ? $txt['originCode'] : '',
//                    'orderid'    => $data['MKNO'],
//                    'custid'     => (isset($txt['ParcelId'])) ? $txt['ParcelId'] : '', // 创建成功的包裹编号 20171019 jie
//                    'traceCode'  => (isset($txt['traceCode'])) ? $txt['traceCode'] : '',
//                    'title'      => (isset($txt['title'])) ? $txt['title'] : '',
//                    'package'    => (isset($txt['package'])) ? $txt['package'] : '',
//                );
//                // print_r($this->STEXT);die;
//                $this->no = (isset($txt['logisticsCode'])) ? $txt['logisticsCode'] : '';	// 分配到的跟踪号码 mailno
            }

            return $this->getResponseData();// 把返回的原始xml直接返回给ERP系统
        }else{
            $this->STEXT = array('ErrorStr'=>'邮政反馈：该订单下单无反馈');
        }

	}

	public function get_logistics(){
		//生成 物流格式数据
		$log = array(
			'siteCode' => $this->config['sitecode'],//仓库标识，由系统分配   Y
			'orderBy' => $this->config['orderBy'],//仓库标识，由系统分配   Y
			//发件人信息
			'order' => array(
				$data['sender'],//发件人姓名    Y
			),

		);

		$data_arr['RequestOrder'] = $log;
	}




//===============================================================

    protected function arrToStr(){

    }
    /**
     * 接口response部分
     */
    protected function getResponse()
    {
        return $this->_response;
    }

    protected function getResponseData()
    {
        return $this->_responseData;
    }

    // 安全及数据完整性
    protected function _setRequest()
    {
        $this->_requestData['userAccount'] = $this->config['cuscode'];
        $this->_requestData['sign']        = $this->sign;
        $this->_requestData['data']        = $this->data;

        $this->json = json_encode($this->_requestData);

        $this->post();
    }

    // 发送数据
    protected function post()
    {
        $MK = new \Org\MK\HTTP();
        $this->_responseData = $MK->post($this->config['pmsLoginAction'].$this->_funcy, $this->json, 1200);
        // 是否输出txt文件
        if($this->config['exports_switch'] === true){

            $content = "\r\n\r\n-------- RequestData --------\r\n\r\n".$this->json."\r\n\r\n-------- return --------\r\n\r\n".$this->_responseData."\r\n\r\n";
            file_put_contents($this->config['xmlsave'].$this->filename, $content,FILE_APPEND);
        }
    }
    /**
     * 返回快递其他内容 20160907 jie
     * @return [type] [description]
     */
    public function get(){

        if($this->STEXT === true){
            return true;
        }else if($this->STEXT != ''){
            return base64_encode((json_encode($this->STEXT)));
        }else{
            return $stext = '';
        }
    }

    /**
     * 生成签名 并  返回正确的数组
     * @param array  参数
     * @param json   要替换的值
     * @return array
     */
    protected function sign($data)
    {
        //把$data按照键名排序
        ksort($data);
        $str="";
        foreach ($data as $key => $val) {
            $str.="$key=$val&";
        }
        $str .= 'key='.$this->config['sitecode'];

        //生成签名
        $this->sign = md5($str);

        //把$data转成json
        $json = json_encode($data);

        //把json写入日志
        // 是否输出txt文件
        if($this->config['exports_switch'] === true){

            if(!is_dir($this->config['xmlsave'])) mkdir($this->config['xmlsave'], 0777, true);

            $content = "===================== ".date('Y-m-d H:i:s')." =====================\r\n\r\n-------- 接口名称 --------\r\n\r\n".$this->_funcy."\r\n\r\n-------- data的参数串 --------\r\n\r\n".$json."\r\n\r\n-------- 签名串 --------\r\n\r\n".$str;

            file_put_contents($this->config['xmlsave'].$this->filename, $content,FILE_APPEND);
        }

        //加密
        $this->data = $this->encrypt($json,$this->config['sitecode']);
    }

    /**
     * 返回快递号码 mailno  20160907 jie
     * @return [type] [description]
     */
    public function no(){
        return $this->no;//直接返回mailno
    }

    //加密
    protected function encrypt($encrypt, $key)
    {
        $blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $paddedData = $this->_pkcs5Pad($encrypt, $blockSize);
        $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);
        $key2 = substr(openssl_digest(openssl_digest($key, 'sha1', true), 'sha1', true), 0, 16);
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key2, $paddedData, MCRYPT_MODE_ECB, $iv);
        return base64_encode($encrypted);
    }

    protected function _pkcs5Pad($text, $blockSize)
    {
        $pad = $blockSize - (strlen($text) % $blockSize);
        return $text . str_repeat(chr($pad), $pad);
    }

    /**
     * 对base64图片进行处理
     * @param string $base64  原来的图片编码
     * @return string  压缩后的图片base64编码
     */
    protected function base64_image($base64)
    {
        //获取图片大小  图片大于50 就压缩
        $size = file_get_contents($base64);
        $len = floor(strlen($size)/1024);
        if($len <= 30){
            return $base64;
        }else if($len > 30 && $len <= 50){
            $percent = 0.7;  //压缩比率
        }else{
            $percent = 0.5;  //压缩比率
        }

        //切割数据  获取图片类型
        preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result);
        $type = $result[2];

        //图片名
        $imageName = 'Kdno23_'.date("YmdHis",time())."_".rand(1111,9999).'.'.$type;

        $image_base64 = $base64;

        if (strstr($base64,",")){
            $base64 = explode(',',$base64);
            $base64 = $base64[1];
        }

        $path = $this->config['xmlsave'];
        if (!is_dir($path)){ //判断目录是否存在 不存在就创建
            mkdir($path,0777,true);
        }

        $imageSrc=  $path."/". $imageName;  //图片路径
        $r = file_put_contents($imageSrc, base64_decode($base64));//返回的是字节数

        if (!$r) {
            return $base64;
        }else{
            require_once(dirname(__FILE__).'\Kdno23.php');	//载入图片压缩类
//            $source =  $imageSrc;//原图片名称
            $dst_img = $path.'/s_'.date("YmdHis",time())."_".rand(1111,9999).'.'.$type;//压缩后图片的名称

            $image = (new imgcompress($imageSrc,$percent))->compressImg($dst_img);

            //判断文件是否存在
            if(file_exists($dst_img)){
                //转码
                $s_base64 = $this->fileToBase64($dst_img);

                //删除原图
                if(file_exists($imageSrc)){
                    unlink($imageSrc);
                }

                //删除压缩图
                if(file_exists($dst_img)){
                    unlink($dst_img);
                }
                return $s_base64;
            }else{
                //删除原图
                if(file_exists($imageSrc)){
                    unlink($imageSrc);
                }
                return $base64;
            }
        }
    }

    //把图片转base64编码
    protected function fileToBase64($file){
        $base64_file = '';
        if(file_exists($file)){
            $mime_type= mime_content_type($file);
            $base64_data = base64_encode(file_get_contents($file));
            $base64_file = 'data:'.$mime_type.';base64,'.$base64_data;
        }
        return $base64_file;
    }

}