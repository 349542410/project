<?php
/**
 * 顺丰物流管理  服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class SFLogisticsController extends HproseController{    
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

	/**
	 * 查总数
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
    public function count($where,$p,$ePage){
        $list = M('Trainer')->where($where)->order('CreateTime desc')->page($p.','.$ePage)->select();

    	$count = M('Trainer')->where($where)->count();
        
    	return array('count'=>$count, 'list'=>$list);
    }

    /**
     * 查看
     * @return [type] [description]
     */
    public function info($map){
        $info = M('Trainer')->where($map)->find();

        $msg = M('TrainerLogs')->where(array('LogisticsNo'=>$info['LogisticsNo']))->order('CreateTime desc')->select();
        return array($info,$msg);
    }

    /**
     * 删除
     */
    public function delete($id){
        $he = M('ExpressCompany')->where(array('id'=>$id))->find();

        //不同浏览器之间的误差操作
        if(!$he){
            $result = array('state'=>'no', 'msg'=>'参数错误，请刷新再试');
            return $result;
        }

        //检查mk_logs中是否已经正在使用该物流
        $check = M('logs')->where(array('tranid'=>$id))->select();
        if(count($check) > 0){
            $result = array('state'=>'no', 'msg'=>'该快递公司正被使用中，操作失败');
            return $result;
        }

        $del = M('ExpressCompany')->where(array('id'=>$id))->delete();
        if($del){
            $result = array('state'=>'yes', 'msg'=>'删除成功');
        }else{
            $result = array('state'=>'no', 'msg'=>'删除失败');
        }
        return $result;
    }

    /**
     * 导出 顺丰物流 某个批次号的，某个状态的所有订单信息
     * @return [type] [description]
     */
    public function _sf_csv($noid,$state){
        ini_set('max_execution_time', 0);
        ini_set('memory_limit','4088M');

        $where['IL_state']     = array('eq',$state);   //20 为已使用
        $where['noid']         = array('eq',$noid); //批次号id
        $where['pause_status'] = array('eq',0); //

        //获取数据  GROUP_CONCAT    DISTINCT 去掉重复
        $list = M('tran_list')
                ->field('MKNO,STNO,receiver,reTel,province,city,reAddr')
                ->where($where)->select();

        $str = array();

        $no = M('transit_no')->where(array('id'=>$noid))->getField('no');

        //设置csv表头
        $title = '序号,订单号,顺丰单号,收件人,收件人电话,省,市,区,详细地址';

        $arr = array();
        $i = 1; //初始序号
        //数组重构
        foreach($list as $item){
            $arr[$i]['no']       = $i;                  //序号
            $arr[$i]['MKNO']     = $item['MKNO'];       //订单号
            $arr[$i]['STNO']     = "\t".$item['STNO'];      //申通号
            $arr[$i]['receiver'] = $item['receiver'];   //收件人
            $arr[$i]['reTel']    = "\t".$item['reTel'];     //收件人电话
            $arr[$i]['province'] = $item['province'];   //省份
            $arr[$i]['city']     = $item['city'];       //城市
            
            $a                   = explode(" ",$item['reAddr']);    //从收件地址中获取 地区 信息
            
            $arr[$i]['area']     = $a[2];               //区
            $arr[$i]['reAddr']   = $item['reAddr'];     //收货地址
            // $arr[$i]['idno']     = "\t".trim($item['idno']);        //收件人证件号   20160912 Jie

            $i++;
            
        }

        return $tips = array('str'=>$arr, 'i'=>$i, 'title'=>$title, 'no'=>$no);

    }
}