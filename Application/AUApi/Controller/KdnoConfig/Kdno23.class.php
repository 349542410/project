<?php
namespace AUApi\Controller\KdnoConfig;
/*
	版本号：V1.0
	创建人：xieyiyi
	创建日期：2018-07-17
	用途：1.线路23推送订单
 */
class Kdno23
{
    private $url;
    private $userAccount;
    private $key;
    private $sign;

    protected $log = UPFILEBASE.'/Upfile/kdno23_logs/';//生成日志的路径

    protected $config = array(
        'clearanceDestinationCode' => 'GUANGZHOU',//清关目的地
        'logisticsCompany'         => 'ems',//快递公司
        'orderProxyFlag'           => 'y',//代理订单推送标志
        'packageType'              => '5H4',//包装类型
        'shipperCountryCode'       => '502',//发件人国别
        'shipperCity'              => 'Fremont',//发件人城市
    );

    public function __construct()
    {
        //获取推送的接口地址/账号/key
        $model = M('AdminConfig');
        $this->url         = $model->where(array('name'=>'gz_ems.order_submit_url'))->getField('value');
        $this->userAccount = $model->where(array('name'=>'gz_ems.account'))->getField('value');
        $this->key         = $model->where(array('name'=>'gz_ems.key'))->getField('value');
    }

    public function data($data)
    {
        $order = $data['order'];
        $order_goods = $order['order_goods'];
        $order_id = $order['id'];

        //如果推送成功  不可以推送
        $pushInfo = M('tran_list_state')->where(array('lid'=>$order_id,'ems_state'=>1))->find();
        if($pushInfo){
            return array('state'=>'no','msg'=>'该订单已经推送成功，不可重复推送');
        }


        //订单商品信息
        foreach($order_goods as $korder=>$vorder){
            $tmps['barcode']     = $vorder['barcode'];
            $tmps['buyCountStr'] = (string)$vorder['number'];
            $tmps['salePrice']   = (string)$vorder['price'];

            $detail_order[] = $tmps;
        }

        $detail['masterWayBillNo']          = $data['lading_no'];
        $detail['clearanceDestinationCode'] = $this->config['clearanceDestinationCode'];
        $detail['logisticsCompany']         = $this->config['logisticsCompany'];
        $detail['orderProxyFlag']           = $this->config['orderProxyFlag'];
        $detail['outOrderNo']               = $order['MKNO'];
        $detail['wayBillNo']                = $order['STNO'];
        $detail['packageType']              = $this->config['packageType'];
        $detail['receiverName']             = $order['receiver'];
        $detail['receiverMobile']           = $order['reTel'];
        $detail['receiverAddress']          = $order['reAddr'];
        $detail['receiverProvince']         = $order['province'];
        $detail['receiverCity']             = $order['city'];
        $detail['receiverDistrict']         = $order['town'];
        $detail['receiverCardNo']           = $order['idno'];
        $detail['shipperCountryCode']       = $this->config['shipperCountryCode'];
        $detail['shipperName']              = $order['sender'];
        $detail['shipperMobile']            = $order['sendTel'];
        $detail['shipperCity']              = $this->config['shipperCity'];
        $detail['shipperAddress']           = $order['sendAddr'];
        $detail['productListString']        = json_encode($detail_order);

        //签名
        $this->sign($detail);

        $json_detail = json_encode($detail);

        $result = $this->post($json_detail);

        $state_model = M('TranListState');
        if($result === false){
            if($this->judgeRepeat($order_id) === true){//添加数据
                $state_model->add(array('lid' => $order_id, 'ems_state' => 0,'ems_return'=>'连接接口出错'));
                return array('state'=>'no','msg'=>'连接接口出错');
            }else{
                $state_model->where(array('lid'=>$order_id))->save(array('ems_state' => 0,'ems_return'=>'连接接口出错'));
                return array('state'=>'no','msg'=>'连接接口出错');
            }
        }

        $arr = json_decode($result,true);

        if($arr['code'] == 1000){
            if($this->judgeRepeat($order_id) === true){//添加数据
                $state_model->add(array('lid' => $order_id, 'ems_state' => 1,'ems_return'=>$result));
                return array('state'=>'yes','msg'=>'成功');
            }else{
                $state_model->where(array('lid'=>$order_id))->save(array('ems_state' => 1,'ems_return'=>$result));
                return array('state'=>'yes','msg'=>'成功');
            }
        }else{
            if($this->judgeRepeat($order_id) === true){//添加数据
                $state_model->add(array('lid' => $order_id, 'ems_state' => 0,'ems_return'=>$result));
                return array('state'=>'no','msg'=>$arr['desc']);
            }else{
                $state_model->where(array('lid'=>$order_id))->save(array('ems_state' => 0,'ems_return'=>$result));
                return array('state'=>'no','msg'=>$arr['desc']);
            }
        }
    }

    //签名
    protected function sign($data)
    {
        ksort($data);
        $sign = '';

        foreach ($data as $kList => $vList) {
            $sign .= $kList . '=' . $vList.'&';
        }
        $sign .= 'key='.$this->key;

        $this->sign = $sign;
    }

    protected function post($data)
    {
        //加密 及 请求
        $postData = array(
            'userAccount' => $this->userAccount,
            'sign' => md5($this->sign),
            'data' => $this->encrypt($data, $this->key),
        );
        $result = $this->postUrls($this->url, json_encode($postData));

        //记录日志
        $file_name = 'Kdno23_orderSubmit_'.date('Ymd').'.txt';	//文件名

        $content = "===================== ".date('Y-m-d H:i:s')." =====================\r\n\r\n-------- RequestData --------\r\n\r\n".$data."\r\n\r\n-------- ResponseData --------\r\n\r\n".$result."\r\n\r\n";

        if(is_file($file_name)){
            file_put_contents($this->log.$file_name, $content);
        }else{
            file_put_contents($this->log.$file_name, $content, FILE_APPEND);
        }

        return $result;
    }

    protected function postUrls($url, $postData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $output 		= curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpErr 		= curl_error($ch);
        curl_close($ch);

        $restr = '';
        if($httpStatusCode==200){
            $restr = trim($output);
        }else{
//				return 'Error:'.$httpErr;
            $restr = false;
        }

        return $restr;
    }

    /**
     * [encrypt aes加密]
     * @param    [type]                   $input [要加密的数据]
     * @param    [type]                   $key   [加密key]
     * @return   [type]                          [加密后的数据]
     */
    protected function encrypt($input, $key)
    {
        $key = $this->randomKey($key);

        $data = openssl_encrypt($input, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        $data = base64_encode($data);
        return $data;
    }
    /**
     * [decrypt aes解密]
     * @param    [type]                   $sStr [要解密的数据]
     * @param    [type]                   $sKey [加密key]
     * @return   [type]                         [解密后的数据]
     */
    protected function decrypt($sStr, $sKey)
    {
        $key = $this->randomKey($sKey);
        $decrypted = openssl_decrypt(base64_decode($sStr), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        return $decrypted;
    }

    /**
     * 随机key
     * @param $key
     */
    protected function randomKey($key)
    {
        return substr(openssl_digest(openssl_digest($key, 'sha1', true), 'sha1', true), 0, 16);
    }

    /**
     * 判断tran_list_state表数据订单id是否存在
     * @param int $orderId  订单id
     * @return bool false|true 没有数据返回true  否则返回false
     */
    protected function judgeRepeat($orderId)
    {
        //获取一条信息
        $info = M('tran_list_state')->where(array('lid'=>$orderId))->find();
        if(empty($info)){
            return true;
        }else{
            return false;
        }
    }
}