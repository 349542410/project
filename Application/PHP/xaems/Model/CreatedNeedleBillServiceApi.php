<?php
require_once("AbstractModel.php");
abstract class CreatedNeedleBillServiceApi  extends AbstractModel {

    /**
     * @var array
     * 基类配置
     */
    protected $base_config = [
        '_version' => '1.0',
        '_soapUsername' => '',
        '_soapPassword' => '',
        '_wsdl' => 'http://202.104.134.94:8002/PackageService.svc?wsdl',
    ];

    public function __construct($config = []) {
        $config = array_merge($this->base_config,$config);
        parent::__construct($config);
    }

} 