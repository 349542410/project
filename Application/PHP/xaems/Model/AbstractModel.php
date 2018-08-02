<?php

abstract class AbstractModel
{

    //private $_wsdl = 'http://202.104.134.94:8002/PackageService.svc?wsdl';
    //private $_wsdl = 'http://202.104.134.94:50081/PackageService.svc?wsdl';
    private $_wsdl = 'https://chongqing-api.11183.hk/packageService.svc?wsdl';

    private $_soapError;


    private $_soapConfig = array(
        'trace' => true,
        'login' => '',
        'password' => '',       
        'connection_timeout' => 120,
        'keep_alive' => true,
    );


    private $_version = '1.0';
    private $_request;
    private $_requestData;
    private $_response;
    private $_responseData;
    private $_runtime;


    public function __construct($config = array())
    {
        ini_set('soap.wsdl_cache_enabled', 1);
        ini_set('soap.wsdl_cache_ttl', 86400);
        ini_set('soap.wsdl_cache', 1);
        $this->_request = new \stdClass();
        $this->_response = new \stdClass();
        $this->_runtime = new runtime();
        $this->_runtime->start();

        //程序运行时间
        foreach ($config as $name => $value) $this->$name = $value;

    }


    /**
     * 实体类中的接口Data数据集
     *
     * @return array
     */
    abstract public function getEntityData();


    /**
     * 获取webService的服务器地址
     */
    public function getServiceBaseUrl()
    {
        return str_replace('/PackageService.svc?wsdl', '', $this->_wsdl);
    }


    /**
     * 根据模型类名称取得接口服务名称
     *
     * @return string
     *
     * 测试 批到导入 强制修改 ImportExpressBillTest 为 ImportExpressBillTest
     */
    protected function getServiceName()
    {
        $class = explode('\\', get_class($this));
        $serviceClass = end($class);
        $serviceClass = $serviceClass == 'ImportExpressBillTest' ? 'ImportExpressBill' : $serviceClass;
        return $serviceClass;
    }


    /**
     * 数据中心的API错误代码信息表
     *
     * @return array
     */
    protected function getErrorCodeToMessage($errorCode = null)
    {
        $arr = array(
            '0x00000004' => 'InvalidEmail',
            '0x00000005' => 'MergedOrderCanNotLower2',
            '0x00000006' => 'FreightCompanyCanNotBeBlank',
            '0x00000007' => 'InvalidOrderNumber',
            '0x00000008' => 'EmailAlreadyExists',
            '0x00000009' => 'FreightNumberCanNotBeEmpty',
        );
        return (null === $errorCode) ? $arr : $arr[$errorCode];
    }


    /**
     * 提交数据到数据中心
     *
     * @return bool
     */
    public function soapSubmit()
    {
        try {

            $this->_setRequest();

            $func = $this->getServiceName();

            $args = array('request' => $this->getRequest());

            try {


                $client = new \SoapClient($this->_wsdl, $this->_soapConfig);
         
            } catch (\SoapFault $e) {

                $this->_soapError = sprintf('1.SoapFault: (%s) %s', $e->getCode(), $e->getMessage());
                echo $this->_soapError;
                die();

            }

            $client->__setSoapHeaders($this->_header());


            try {

                $result = $client->__soapCall($func, array($args));

            } catch (SoapFault $exception) {

                $this->_soapError = sprintf('2.SoapFault: (%s) %s', $exception->getCode(), $exception->getMessage());
                echo $this->_soapError;
                die();
            }


            $this->_setResponse($result);

            $this->_runtime->stop();

            return true;

        } catch (\SoapFault $e) {

            $this->_soapError = sprintf('SoapFault: (%s) %s', $e->getCode(), $e->getMessage());

        } catch (\Exception $e) {

            $this->_soapError = sprintf('Exception: (%s) %s', $e->getCode(), $e->getMessage());

        }
        return false;
    }


    /**
     * 每个接口都要求有header认证
     */
    private function _header()
    {
        $_ns = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
        $_ws = $this->_getSoapPortalAuthInfo($this->getServiceName());
        //var_dump($_ws);
        $_u = new \SoapVar($_ws['Username'], XSD_STRING, null, null, 'Username', $_ns);
        $_p = new \SoapVar($_ws['Password'], XSD_STRING, null, null, 'Password', $_ns);
        $token = new \SoapVar(array($_u, $_p), SOAP_ENC_OBJECT, null, null, 'UsernameToken', $_ns);
        $security = new \SoapVar(array($token), SOAP_ENC_OBJECT, null, null, null, $_ns);
        return new \SoapHeader($_ns, 'Security', $security);
    }


    /**
     * 几个特殊的接口使用通用soap认证访问，其它接口使用login后的portal信息访问
     */
    private function _getSoapPortalAuthInfo($serviceName)
    {
        return array('Username' => "meiquick", 'Password' => "6d89fbc4b1e9");
    }


    /**
     * 模型的错误信息数组对象
     *
     * @return array
     */
    public function getError()
    {
        return (object)array('code' => $this->getResponseErrorCode(), 'message' => $this->getResponseErrorMessage());
    }


    /**
     * 接口request部分
     */
    public function getRequest()
    {
        return $this->_request;
    }


    public function getRequestData()
    {
        return $this->_requestData;
    }


    public function setRequestData($data)
    {
        $this->_requestData = $data;
    }


    private function _setRequest()
    {

        $headArgs['Version'] = $this->_version;
        preg_match('/0\.(\d+) (\d+)/', microtime(), $p);
        $headArgs['RequestTime'] = sprintf('%s.%sZ', date('Y-m-d\TH:i:s', $p[2]), $p[1]);
        preg_match('/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/', md5(session_id()), $p);
        $headArgs['RequestId'] = sprintf('%s-%s-%s-%s-%s', 'wonroads', $p[2], $p[3], $p[4], $p[5]);
        $headArgs['Data'] = $this->_requestData;

        $this->_request = $headArgs;

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


    public function getResponseSuccess()
    {
        return isset($this->_response->ResponseResult) and $this->_response->ResponseResult == 'Success';
    }


    public function getResponseErrorCode()
    {
        if ($this->_soapError) return '0x012345';
        return isset($this->_response->ResponseError->Code) ? $this->_response->ResponseError->Code : null;
    }


    public function getResponseErrorMessage()
    {
        if ($this->_soapError) return $this->_soapError;
        return isset($this->_response->ResponseError->LongMessage) ? $this->_response->ResponseError->LongMessage : null;
    }


    public function getResponseShortMessage()
    {
        return isset($this->_response->ResponseError->ShortMessage) ? $this->_response->ResponseError->ShortMessage : null;
    }


    private function _setResponse($result)
    {
        if (is_object($result)) {
            $attribute = $this->getServiceName() . 'Result';
            if (isset($result->$attribute) and is_object($result->$attribute)) {
                $this->_response = $result->$attribute;
                $this->_responseData = $this->_response->Data;
            }
        }
    }


    /**
     * 时间转化成浮点数时间(精确到微妙)
     * @param unknown $date
     * @return string
     */
    public function strtomicrotime($date)
    {
        list($usec, $sec) = explode(".", $date);
        return strtotime($usec) . '.' . $sec;
    }


    /**
     * 接口以外的属性
     */
    public function __get($name)
    {
    }


    public function __set($name, $value)
    {
    }

}


class runtime
{
    private $StartTime = 0;
    private $StopTime = 0;

    function get_microtime()
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }

    function start()
    {
        $this->StartTime = $this->get_microtime();
    }

    function stop()
    {
        $this->StopTime = $this->get_microtime();
    }

    function spent()
    {
        return round(($this->StopTime - $this->StartTime) * 1000, 1);
    }
}
