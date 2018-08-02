<?php
require_once("ExpressServiceApi.php");
class QueryTrackingCenter extends ExpressServiceApi
{
    /**
     * @Assert\NotBlank(message="不能为空.")
     *
     */
    public $CustomerIdentity;

    /**
     * 实体类中的接口Data数据集
     */
    public function getEntityData()
    {
        return $this->CustomerIdentity;
    }
}
