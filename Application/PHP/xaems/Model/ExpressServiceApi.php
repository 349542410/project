<?php
require_once("AbstractModel.php");
abstract class ExpressServiceApi extends AbstractModel {

    /**
     * @var array
     * 基类配置
     */

    protected $base_config = array(
        '_version' => '1.0',
        '_soapUsername' => 'meiquick',
        '_soapPassword' => '6d89fbc4b1e9',
        //'_wsdl' => 'http://202.104.134.94:8002/PackageService.svc?wsdl',
        //'_wsdl' => 'http://202.104.134.94:50081/PackageService.svc?wsdl',
        '_wsdl' => 'https://chongqing-api.11183.hk/packageService.svc?wsdl',
        //'_wsdl' => 'http://192.168.106.227:65213/PackageService.svc?wsdl',
    );

    public function __construct($config = array()) {
        $config = array_merge($this->base_config,$config);
        parent::__construct($config);
    }

    /**
     * 获取webService的服务器地址
     */
    public function getServiceBaseUrl()
    {
        return str_replace('/ExpressService.svc?wsdl','',$this->base_config['_wsdl']);
    }
}