<?php
require_once("ExpressServiceApi.php");
class QueryCategory extends ExpressServiceApi
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
