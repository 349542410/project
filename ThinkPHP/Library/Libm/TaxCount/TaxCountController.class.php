<?php
namespace Libm\TaxCount;
use Think\Controller;
class TaxCountController extends Controller{

	//protected $US_TO_RMB_RATE = '6.45';// 配置到 config.php中
	protected $US_TO_RMB_RATE;	 //汇率
	protected $RMB_Free_Duty;
    public function __construct()
    {
        $this->US_TO_RMB_RATE = C('US_TO_RMB_RATE');    //汇率
        $this->RMB_Free_Duty =  $GLOBALS['globalConfig']['RMB_Free_Duty'];   //人民币最低免税金额额度
    }
	
	/**
	 * [caltax 根据订单号查询 订单的免税额 和 订单中包含的各列商品的税金]
	 * @param  [type] $orderno [mk_tran_ulist.order_no]
	 * @return [type]          [description]
	 */
	public function caltax($orderno){
		$orderno = trim($orderno);

		if($orderno == ''){
			return array('state'=>'no', 'msg'=>'订单号为空');
		}
		
		$info = M('tran_ulist')->where(array('order_no'=>$orderno))->find();
		if(!$info){
			return array('state'=>'no', 'msg'=>'查无此单信息');
		}

		//查询该线路信息
		$center = M('TransitCenter')->where(array('id'=>$info['TranKd']))->find();
		// dump($center);
		
		// if($center['taxthreshold'] == '0'){
		// 	return array('state'=>'no', 'msg'=>'线路的税金起征额尚未设置');
		// }

		$map = array();
		$map['t.lid'] = array('eq', $info['id']);

        $sys_tax_arr = array();
		$sys_tax_arr['center']         = $center;
		$sys_tax_arr['map']            = $map;
		//$sys_tax_arr['RMB_Free_Duty']  = '50';//$center['taxthreshold'];//税金起征额(未超过此金额税金为0，超过此金额按实际税金收取)
		$sys_tax_arr['RMB_Free_Duty']  = floor($center['taxthreshold']) <= 0 ? $this->RMB_Free_Duty : $center['taxthreshold'];//税金起征额(未超过此金额税金为0，超过此金额按实际税金收取)
		$sys_tax_arr['US_TO_RMB_RATE'] = $this->US_TO_RMB_RATE;//人民币与美元的汇率

		$SysTaxCount = new \Libm\TaxCount\SysTaxCountController();
        $SysTax = $SysTaxCount->index($sys_tax_arr);

        // 只保留商品id 和 商品税金额
        $goods_list = array();
        foreach($SysTax['goods'] as $k=>$v){
        	$good_list[$k]['oid'] = $v['oid'];//商品id
        	$good_list[$k]['tax_price'] = $v['tax_price'];//商品税金额
        }

        $redata = array();
		$redata['total'] = $SysTax['tax'];//总税金额，免税的时候为0，否则为总税金
		$redata['list']  = $good_list;//订单商品资料

		return $redata;
	}

	/**
	 * [caltax2 description]
	 * @param  [type] $goods  [二维数组，包含：商品id，第二类别id，number/数量，price/单价]
	 * @param  [type] $kdid   [线路id]
	 * @return [type]         [description]
	 */
	public function caltax2($goods, $kdid){
		//查询该线路信息
		$center = M('TransitCenter')->where(array('id'=>$kdid))->find();
        
        $ids = array_column($goods,'cid');
        // return $ids;

        $map = array();
        $map['id'] = array('in', $ids);
        $list = M('category_list')->field('id,price as tax_rate')->where($map)->select();
        // dump($goods);
        // dump($list);

        foreach($goods as $k1=>$v1){
	        foreach($list as $k2=>$v2){
	        	if($v1['cid'] == $v2['id']){
	        		$goods[$k1]['tax_rate'] = $v2['tax_rate'];
	        	}
	        }
	    } 
        // dump($goods);
        // die;

        // $RMB_Free_Duty  = $center['taxthreshold'];//税金起征额(未超过此金额税金为0，超过此金额按实际税金收取)
        $RMB_Free_Duty  = 50;       //暂时为50
        $US_TO_RMB_RATE = $this->US_TO_RMB_RATE;//人民币与美元的汇率
        
        $tax = 0;//税金总金额  总计

        //检查该线路的 bc_state 是否为1
        if($center['bc_state'] == '1'){

            foreach($goods as $k=>$item){
                $tax_price = number_format(($item['number'] * $item['tax_rate']),2,'.','');//统计税金 以便保存
                $goods[$k]['tax_price'] = $tax_price;//各列商品自身的税金额小计

                $tax += $tax_price;
            }
            
        }else if($center['cc_state'] == '1'){

            //tax_rate 为百分比
            if($center['tax_kind'] == '1'){

                //需要根据二级类别的税率计算税金
                foreach($goods as $k=>$item){
                    $tax_price = number_format(($item['number'] * $item['tax_rate'] * $item['price'] / 100),2,'.','');
                    $goods[$k]['tax_price'] = $tax_price;//各列商品自身的税金额小计

                    $tax += $tax_price;//统计税金 以便保存
                }

                //根据汇率计算出美元免税的额度
                $free_duty = $RMB_Free_Duty / $US_TO_RMB_RATE;

                // 2017-09-19  整单税金<=7美元的时候，直接免税；>7的直接显示所计算得到的税金（不用减7）
                if(sprintf("%.2f", $tax) <= sprintf("%.2f", $free_duty)){
                    $tax = '0';
                }
                
            }else{//tax_rate 为固定值

                //需要根据二级类别的税值计算税金
                foreach($goods as $k=>$item){
                    $tax_price = number_format(($item['number'] * $item['tax_rate']),2,'.','');
                    $goods[$k]['tax_price'] = $tax_price;//各列商品自身的税金额小计
                    
                    $tax += $tax_price;//统计税金 以便保存
                }
            }

        }else{
            foreach($goods as $k=>$item){
                $goods[$k]['tax_price'] = '0';//各列商品自身的税金额小计
            }
        }

        // dump($goods);
        // dump($tax);

        $good_list = array();
        foreach($goods as $item){
        	$good_list[$item['uuid']] = $item['tax_price'];
        }

        $redata = array();
		$redata['total'] = number_format($tax,2,'.','');//总税金额，免税的时候为0，否则为总税金
		$redata['list']  = $good_list;//订单商品资料

		return $redata;

	}
}