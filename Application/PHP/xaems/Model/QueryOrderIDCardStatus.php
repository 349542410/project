<?php
require_once("ExpressServiceApi.php");
class QueryOrderIDCardStatus extends ExpressServiceApi
{
    /**
     * @Assert\NotBlank()
     *
     */
    public $Data;

    /**
     * 实体类中的接口Data数据集
     */
    public function getEntityData()
    {
        return  $this->Data;
    }
}
