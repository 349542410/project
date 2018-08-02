<?php
/**
 * 顺丰BC订单管理 服务器端
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class SfBcOrderController extends HproseController{

    //查出 所有类别列表
    public function cat_list(){
        $cat_list = M('CategoryList')->field('id,fid,cat_name,price as tax_price')->where(array('is_show'=>'1','status'=>'1'))->select();
        return $cat_list;
    }

    /**
     * 根据上一级的类别ID找出对应的下一级分类
     * @param  [type] $id [上级ID]
     * @return [type]     [description]
     */
    public function _next_level($id){
        $next_list = M('CategoryList')->field('id,fid,cat_name,price as tax_price')->where(array('fid'=>$id))->select();
        $next_list = (count($next_list) > 0) ? $next_list : array(array('id'=>'','fid'=>'','cat_name'=>'无','price'=>'0'));
        return $next_list;
    }

    /**
     * 根据二级类别ID找出对应其对应的货品列表
     * @param  [type] $id      [上级ID]
     * @param  [type] $keyword [搜索关键字]
     * @return [type]          [description]
     */
    public function _product($id, $keyword){
        $map = array();
        $map['cat_id']  = array('eq',$id);//二级类别的id
        if($keyword != '') $map['name'] = array('like','%'.$keyword.'%');//关键字

        $next_list = M('ProductList')->field('id,name as text,cat_id,price as pro_price')->where($map)->order('ctime desc')->select();
        $next_list = (count($next_list) > 0) ? $next_list : array(array('id'=>'','text'=>'无','cat_id'=>'','price'=>'0'));
        return $next_list;
    }

    /**
     * 查总数
     * @param  [type] $where [description]
     * @return [type]        [description]
     */
    public function _count($map,$p,$ePage){
        
        //只显示bc_state=1的线路，即BC报关管理为开启状态的
        $center = M('TransitCenter')->field('id')->where(array('bc_state'=>'1'))->select();
        $ids = array_column($center, 'id');

        $map['TranKd'] = array('in',$ids); //只查询mk_transit_center.bc_state = 1

        $list = M('TranUlist')->where($map)->page($p.','.$ePage)->select();

        $count = M('TranUlist')->where($map)->count();
        
        return array('count'=>$count, 'list'=>$list);
    }

    /**
     * 获取列表  注销
     * @param  [type] $where [description]
     * @return [type]        [description]
     */
/*    public function getList($limit,$map){
        $list = M('TranUlist')->where($map)->limit($limit)->select();
        // $list = M('UserInfo')->join('RIGHT JOIN mk_user_list ON mk_user_list.id=mk_user_info.user_id')->order('reg_time desc')->where($where)->limit($limit)->select();
       
        // if(count($list) > 0){
        //     $ids = array();
        //     foreach($list as $key=>$item){
        //         $ids[] = $item['id'];
        //     }

        //     //订单ID集
        //     $ids = implode(',',$ids);

        //     $where['lid'] = array('in',$ids);
        //     $info = M('TranUorder')->where($where)->select();//根据ID集找出所有对应的商品

        //     //把对应的商品整合到订单中
        //     foreach($info as $k1=>$v1){

        //         foreach($list as $k2=>$v2){

        //             if($v1['lid'] == $v2['id']){
                        
        //                 $list[$k2]['goods'][$k1] = $v1;
        //                 sort($list[$k2]['goods']);//数组键值重新以升序方式对数组排序
        //             }
        //         }
        //     }
        // }

        return $list;
    }*/

    public function get_pro_list($id){
        $pro_list = M('TranUorder t')->field('t.*,c.price as tax_price')->join('left join mk_category_list c on c.id = t.category_two')->where(array('t.lid'=>$id))->select();//根据ID集找出所有对应的商品
        return $pro_list;
    }









}