<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/27
 * Time: 15:37
 */
namespace Lib10\LineAmount;
class AddressDetails
{
    private $err_info;

    private $parameter;

    //过滤快递公司名称
    public function filterName($str)
    {
        $name = C('FILTER_COURIER_NAME'); // 获取全部要判断的名称

        $arr = explode("|",$name);//转为数组
        $nameArr = array_filter($arr);//去除空元素
        $content = trim($str); //去除空格

        foreach ($nameArr as $key=>$v) {
            $name = trim($v);
            if (strpos($content,$name) !== false){
                //$this->err_info = '详细地址中不能包含字符 '.$v;
                //$this->err_info = L('no_order_address').$v;
                $this->err_info = 'no_order_address';
                $this->parameter = array('no_order_address', $v);
                return false;
            }
        }
        return true; // 如果没有匹配到关键字就返回 true
    }


}




