<?php
/**
 * 20180503
 * 美快BC优选/美快优选3 清关管理的公共方法
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class MkBcInfoController extends HproseController{
    
    /**
     * 批次号列表 
     * @param  [array]  $where  [查询条件]
     * @return [array] 
     */
    public function customsList($where){
        
        $list = M('TransitNo tn')
                ->field('tn.id,tn.no,tn.date,tn.airdatetime,tn.airno,tn.status,tc.name,tn.tcid')
                ->join('LEFT JOIN mk_transit_center tc ON tn.tcid = tc.id')
                ->where($where)
                ->order('tn.date desc')
                ->select();

        foreach($list as $key=>$item){
            $res = $this->each_count($item['id']);
            $list[$key]['all']  = $res['al']?$res['al']:0;
            $list[$key]['not']  = $res['one']?$res['one']:0;
            $list[$key]['done'] = $res['done']?$res['done']:0;
            $list[$key]['two']  = $res['two']?$res['two']:0;
            $list[$key]['four'] = $res['four']?$res['four']:0;
            
        }
        return array('list'=>$list);		
    }
            
    /**
     * 各状态 统计查询  
     * @param  [int]    $id     [tran_list.id]* 
     * @return [array]
     */
    public function each_count($id){

        $sql = "SELECT 
                sum(noid = $id) AS al,
                sum(custom_status = '0') AS one,
                sum(custom_status = '1') AS done,
                sum(custom_status = '200')AS two,
                sum(custom_status = '400')AS four 
                FROM `mk_tran_list` 
                WHERE `noid` = ".$id;

        $m = new \Think\Model();

        $arr = $m->query($sql);

        $backArr = $arr[0];

        return $backArr;
    }
}
