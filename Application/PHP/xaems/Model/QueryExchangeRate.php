<?php
require_once("ExpressServiceApi.php");
class QueryExchangeRate extends ExpressServiceApi
{
   /**
     *##币种，不填，表示返回所有；否则，按照币种返回。
     *
     */
    public $Currency;

    /**
     * 实体类中的接口Data数据集
     */
    public function getEntityData()
    {
       return [
               'Currency'=>$this->Currency,
       ];
    }
}