<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/5
 * Time: 19:27
 */
namespace Lib10\LineAmount;
class LineCost{
    private $err_info;
    private $parameter = array();
    public function __construct()
    {

    }

    /**
     * 错误提示多语言
     * @return mixed
     */
    public function getError(){
        return $this->err_info;
    }

    /**
     *错误提示参数
     */
    public function parameter(){
        return $this->parameter;
    }


    /**
     * @param int $line_id 线路id
     * @param array   $goods   里面存放 单件/数量/类别id 组成的二维数组
     * @param array   $recipient_arr  收件人的省市区详细地址 一维数组
     * @return  bool  true|false
    */
    public function cost($line_id, $goods,$recipient_arr)
    {
        //判断收件人详细地址中是否有快递公司名称
        $courier = $this->filterName($recipient_arr['address']);
        if(!$courier){
            return $courier;
        }

        //获取线路配置信息
        $where['id'] = $line_id;

        $no_limit_region = M('transit_center')->where($where)->getField('no_limit_region');//不限额地区

        //判断收件人地址是否在不限额地区 $no_limit_region为空则全部需要有限额
        //$no_limit_region的数据表填写格式为：省,市,区|省,市,区
        if(! empty($no_limit_region)) {
            //切割数组
            $zone_null_arr = explode('|',$no_limit_region);

            //去除空元素
            $zone_arr = array_filter($zone_null_arr);

            $zone_arr_count = count($zone_arr);

            if($zone_arr_count >= 1) {
                foreach ($zone_arr as $key => $val) {
                    //把省市区切割成数组
                    $zone[] = explode(',',$val);
                }

                //先把$zone里面的省全部获取出来  即键值为0的
                $province_zone = array();
                foreach ($zone as $key=>$v) {
                    $province_zone[$key] = trim($v[0]);
                }

                //匹配全部的省
                $recipient_zone_arr = array();
                foreach ($province_zone as $key=>$v){
                    if($recipient_arr['province'] == $v){
                        $recipient_zone_arr[$key] = $v;
                    }else{
                        //在模糊匹配一次
                        if(mb_strlen($recipient_arr['province']) > 2) {
                            $sheng_name = mb_substr($recipient_arr['province'],0,2);//用前面两个字符去匹配
                        }else{
                            $sheng_name = $recipient_arr['province'];
                        }

                        if(strpos($v,$sheng_name) !== false) {//
                            $recipient_zone_arr[$key] = $v;
                        }
                    }
                }

                $province_count = count($recipient_zone_arr);

                if($province_count < 1){ //数组总数小于0 没有匹配  就是有限额
                    return $this->priceLimit($line_id, $goods);
                }elseif ($province_count == 1){ //只有一个符合
                    foreach ($recipient_zone_arr as $key=>$v){
                        $success_arr = $zone[$key];
                    }
                    return $this->oneProvince($recipient_arr,$success_arr,$line_id, $goods);
                }else{//有多个
                    //获取所有的省市区
                    foreach ($recipient_zone_arr as $key=>$v){
                        $success_arr[] = $zone[$key];
                    }
                    return $this->multipleProvince($recipient_arr,$success_arr,$line_id, $goods);
                }
            }
        }
        return $this->priceLimit($line_id,$goods);
    }

    //只有一个省是符合的
    //$recipient_arr 收件人的省市区
    //$zone  符合条件的从数据库提取的省市区
    protected function oneProvince($recipient_arr,$zone,$line_id, $goods)
    {
        //匹配市
        $city = trim($zone[1]);

        if(empty($city)) {//收件人的市不做限额
            return true;
        }else{
            if($recipient_arr['city'] != $city) {//去切割字符 再一次匹配
                if(mb_strlen($recipient_arr['city']) > 2) {
                    $shi_name = mb_substr($recipient_arr['city'],0,-1);//去除最后一个字符去匹配
                }else{
                    $shi_name = $recipient_arr['city'];
                }

                if(strpos($city,$shi_name) === false) {//没有匹配  就是有限额
                    return $this->priceLimit($line_id,$goods);
                }
            }
        }

        //匹配区
        $town = trim($zone[2]);

        if(empty($town)) {//收件人的区不做限额
            return true;
        }else{
            if($recipient_arr['town'] != $town) {//去切割字符 再一次匹配
                if(mb_strlen($recipient_arr['town']) > 2) {
                    $qu_name = mb_substr($recipient_arr['town'],0,-1);//去除最后一个字符去匹配
                }else{
                    $qu_name = $recipient_arr['town'];
                }

                if(strpos($town,$qu_name) === false) {//没有匹配  就是有限额
                    return $this->priceLimit($line_id,$goods);
                }
            }
            return true;
        }
    }

    //有多个省符合
    //$recipient_arr 收件人的省市区 一维数组
    //$zone  符合条件的从数据库提取的省市区  二维数组
    protected function multipleProvince($recipient_arr,$zone,$line_id, $goods)
    {
        //匹配市
        //先把所有的市提取出来 即键值为1的
        //先把$zone里面的省全部获取出来
        $city_zone = array();
        foreach ($zone as $key=>$v) {
            if(! empty($v)){
                $city_zone[$key] = trim($v[1]);
            }
        }

        if(empty($city_zone)){return true;}

        //去匹配
        foreach ($city_zone as $key=>$v) {
            if($v != $recipient_arr['city']) {
                if(mb_strlen($recipient_arr['city']) > 2) {
                    $shi_name = mb_substr($recipient_arr['city'],0,-1);//去除最后一个字符去匹配
                }else{
                    $shi_name = $recipient_arr['city'];
                }
                if(strpos($v,$shi_name) === false) {//没有匹配  就删除数组
                    unset($city_zone[$key]);
                }
            }
        }

        $city_count = count($city_zone);
        if($city_count < 1) { //收件人地址要限额
            return $this->priceLimit($line_id, $goods);
        }else if($city_count == 1){ //只有一个数组还要匹配
            foreach ($city_zone as $key=>$v){//获取符合条件的数组
                $zone_arr = $zone[$key];
            }
            //判断区是否符合
            if(empty($zone_arr[2])){return true;}
            //先全名匹配
            if($recipient_arr['town'] != $zone_arr[2]){
                if(mb_strlen($recipient_arr['town']) > 2) {
                    $qu_name = mb_substr($recipient_arr['town'],0,-1);//去除最后一个字符去匹配
                }else{
                    $qu_name = $recipient_arr['town'];
                }
                if(strpos($zone_arr[2],$qu_name) === false) {//没有匹配  就是限额
                    return $this->priceLimit($line_id, $goods);
                }
            }
            return true;

        }else{//还有多个数组要匹配
            $zone_arr = array();
            foreach ($city_zone as $key=>$v){//获取全部符合条件的数组
                if(! empty($v)){
                    $zone_arr[] = $zone[$key];
                }
            }

            if(empty($zone_arr)){return true;}

            //去匹配
            foreach ($zone_arr as $key=>$v) {
                if($v != $recipient_arr['town']) {
                    if(mb_strlen($recipient_arr['town']) > 2) {
                        $qu_name = mb_substr($recipient_arr['town'],0,-1);//去除最后一个字符去匹配
                    }else{
                        $qu_name = $recipient_arr['town'];
                    }
                    if(strpos($v,$qu_name) === false) {//没有匹配  就删除数组
                        unset($zone_arr[$key]);
                    }
                }
            }

            $town_count = count($zone_arr);
            if($town_count < 1){//没有匹配  要限额
                return $this->priceLimit($line_id, $goods);
            }else{
                return true;
            }
        }
    }

    //过滤快递公司名称
    protected function filterName($str)
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

    //限额
    protected function priceLimit($line_id, $goods)
    {
        //获取汇率
        $rate = C('US_TO_RMB_RATE');
        //获取线路配置信息
        $where['id'] = $line_id;
        $line = M('transit_center')->field('maxamount, taxthreshold, single_piece_limit')->where($where)->find();
        $maxamount          = $line['maxamount'];//最高限额
        $single_piece_limit = $line['single_piece_limit'];//单件限额
        $taxthreshold       = $line['taxthreshold'];//税金起征额

        //计算用户的商品总价 商品总量
        $amount = 0;
        $number = 0;
        foreach ($goods as $key => $val){
            $amount += $val['price'] * $val['number'];
            $number += $val['number'];
        }

        //判断单件限额
        //$single_piece_limit 单件限额值  0为不限  大于0是金额
        //$number  全部的数量
        //$goods[0]['is_suit']  是否套装商品  0不是
        if(($single_piece_limit > 0) && ($number == 1) && ($goods[0]['is_suit'] == 0)) {
            //转换汇率 并且  取整
            $single_floor = floor($single_piece_limit / $rate);

            if($amount > $single_floor) {
                //$this->err_info = '订单总金额超出最大限制金额$'.$single_floor;
                //$this->err_info = 'order_excess'.$single_floor;
                $this->err_info = 'order_excess';
                $this->parameter = array('order_excess', $single_floor);
                return false;
            }else{
                return true;
            }
        }

        //判断是否超出线路最大限制金额
        if($maxamount > 0){
            //转换汇率 并且 取整
            $max_floor = floor($maxamount / $rate);

            if ($amount > $max_floor){
                //$this->err_info = '订单总金额超出最大限制金额$'.$max_floor;
                //$this->err_info = L('order_excess').$max_floor;
                $this->err_info = 'order_excess';
                $this->parameter = array('order_excess', $max_floor);

                return false;
            }
        }


        return true;
    }


}
