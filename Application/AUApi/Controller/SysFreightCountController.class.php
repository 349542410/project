<?php
/**
 * 公共计费公式(运费)
 * 功能：1.获取 会员线路优惠配置， 2.运费的计算
 * 创建时间：2018-03-21
 * 创建人：jie
 */
namespace AUApi\Controller;
use Think\Controller;
class SysFreightCountController extends Controller{

	/**
	 * [index 运费计费公式]
	 * @param  [type] $sys_arr [包含：称重重量, 订单所属线路id, 订单所属会员id]
	 * @return [type]          [返回：计重运费、实际总消费（未包含税金）、原始消费金额、总优惠金额]
	 */
	public function index($sys_arr){

		$weight  = $sys_arr['weight']; //称重重量
		$line_id = $sys_arr['TranKd'];//订单所属线路id
		$user_id = $sys_arr['user_id'];//订单所属会员id

		$Web_Config = $this->_get_lines_configure($sys_arr);//线路价格配置与会员线路优惠

		if(isset($Web_Config['state']) && $Web_Config['state'] == 'no'){
			return $Web_Config;
		}

        $Charge           = $Web_Config['fee_service'];     //服务费，手续费
        $Price            = $Web_Config['fee_first'];       //首重价格
        $Heavy            = $Web_Config['weight_first'];    //首重重量
        $Unit             = $Web_Config['weight_next'];     //续重重量(原意：续重计费单位)
        $UnitPrice        = $Web_Config['fee_next'];        //续重单价(原意：续重每单位金额)
        $Discount_Service = $Web_Config['discount_service'];//服务费折扣 百分比
        $Discount_First   = $Web_Config['discount_first'];  //首重折扣  百分比
        $Discount_Next    = $Web_Config['discount_next'];   //续重折扣  百分比

        $real_charge = number_format(($Charge * $Discount_Service/100),2,'.','');//实收服务费

        // 实际称重>首重
        if($weight - $Heavy > 0){

            //超出的重量 = 实际重量 - 首重
            $surp = number_format(($weight - $Heavy),2,'.','');

            //续重计费数量 = 超出的重量 / 续重计费单位 -> 如果存在小数则+1
            $surp_num = number_format((ceil($surp / $Unit)),2,'.','');

            //续重价格 = 续重计费数量*续重每单位金额
            $amount = number_format(($UnitPrice * $surp_num),2,'.','');
            
            //总运费 = 首重价格*首重折扣 + 续重价格*续重折扣
            $freight = number_format((($Price * $Discount_First/100) + ($amount * $Discount_Next/100)),2,'.','');

            //实际消费金额 = 总运费 + 实收服务费(服务费*服务费折扣)
            $cost = number_format(($freight + $real_charge),2,'.','');
            
            //未计算所有折扣优惠的 消费金额 原价
            $original_price = number_format(($Price + $amount + $Charge),2,'.','');
        }else{

            // 由于实际称重重量没有超过首重，所有运费只收首重运费的金额
            //总运费 = 首重价格*首重折扣
            $freight = number_format(($Price * $Discount_First/100),2,'.','');

            // 实际消费金额 = 总运费 + 实收服务费(服务费*服务费折扣)
            $cost = number_format(($freight + $real_charge),2,'.','');

            //未计算所有折扣优惠的 消费金额 原价
            $original_price = number_format(($Price + $Charge),2,'.','');

        }       

        // 优惠金额 = 原价 - 实际消费金额
        $discount = number_format(($original_price - $cost),2,'.','');

        return array('freight'=>$freight, 'cost'=>$cost, 'original_price'=>$original_price, 'discount'=>$discount, 'Web_Config'=>$Web_Config, 'real_charge'=>$real_charge);
	}

    /**
     * 线路价格配置与会员线路优惠
     * @param  [type] $line_id [线路id]
     * @param  [type] $user_id [会员id]
     * @return [type]          [description]
     */
    public function _get_lines_configure($info){
        //根据线路ID 查询 线路配置信息
        $Web_Config = M('LinePrice')->field('fee_service,weight_first,fee_first,weight_next,fee_next,unit_currency,unit_weight')->where(array('line_id'=>$info['TranKd']))->find();

        if(!$Web_Config){
        	return array('state'=>'no', 'msg'=>'无法找到对应的线路价格配置');
        }

        //如果查询出错，find方法返回false，如果查询结果为空返回NULL，查询成功则返回一个关联数组（键值是字段名或者别名）
        $Web_Config = (is_array($Web_Config)) ? $Web_Config : '';

        // 判断传来的中转线路ID是否有对应的配置信息
        if($Web_Config == ''){
            return array('state'=>'no', 'msg'=>'该中转线路尚未配置', 'lng'=>'tranline_not_exist');
        }

        // 查询该会员是否有设置线路折扣
        $member_discount = M('LineDiscount')->field('discount_service,discount_first,discount_next')->where(array('user_id'=>$info['user_id'],'line_id'=>$info['TranKd']))->find();

        /* 检查会员线路优惠的配置 */
        // 如果该会员的线路优惠折扣尚未配置
        if(!$member_discount){
            $member_discount = array();
            $member_discount['discount_service'] = '100';
            $member_discount['discount_first']   = '100';
            $member_discount['discount_next']    = '100';
        }else{
            // 如果该会员的线路优惠折扣已配置，但是里面的值是空的或者非数字  20180125 jie
            if(empty(trim($member_discount['discount_service'])) || !is_numeric(trim($member_discount['discount_service']))){
                $member_discount['discount_service'] = '100';
            }
            if(empty(trim($member_discount['discount_first'])) || !is_numeric(trim($member_discount['discount_first']))){
                $member_discount['discount_first'] = '100';
            }
            if(empty(trim($member_discount['discount_next'])) || !is_numeric(trim($member_discount['discount_next']))){
                $member_discount['discount_next'] = '100';
            }
        }

        // 会员线路优惠折扣  最后保险设置  20180126 jie
        if(floatval(trim($member_discount['discount_service'])) < 60) $member_discount['discount_service'] = '100';
        if(floatval(trim($member_discount['discount_first'])) < 60) $member_discount['discount_first'] = '100';
        if(floatval(trim($member_discount['discount_next'])) < 60) $member_discount['discount_next'] = '100';

        $Web_Config = $Web_Config + $member_discount;

        // 日志
        if(!is_dir(UPFILEBASE.'/Upfile/freight_logs/')) mkdir(UPFILEBASE.'/Upfile/freight_logs/', 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹
        $file_name = 'freight_'.date('Ymd').'.txt';   //文件名     
        $content = "===================== ".date('Y-m-d H:i:s')." =====================\r\n\r\n-------- soapError --------\r\n\r\n".json_encode($Web_Config)."\r\n\r\n";
        file_put_contents(UPFILEBASE.'/Upfile/freight_logs/'.$file_name, $content, FILE_APPEND);

        /* 检查会员线路优惠的配置 end */
        return $Web_Config;
    }

}