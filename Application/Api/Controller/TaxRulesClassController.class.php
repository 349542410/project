<?php
/**
 * cc税则类别
 * 功能包括： 
 * 
 */
namespace Api\Controller;
use Think\Controller\HproseController;

class TaxRulesClassController extends HproseController
{
    public function _index($where,$p,$ePage){

        $m = M('TaxRulesClass');

        $list = $m->where($where)->order('sort')->page($p.','.$ePage)->field('id,hs_code,cname1,cname2,cname3,cname4,cname5,specifications,number,price,rate,status')->select();

        $count = $m->where($where)->count();

        return array('list'=>$list,'count'=>$count);
    }

    //添加类别
    public function _tax_add($data){

        $m = M('TaxRulesClass');

        $one = $m->where(array('hs_code'=>$data['hs_code']))->find();

        if($one){

            return array('state' => 'no','msg' => '该税号已添加');
        }

        //把数据加入mk_tax_rules_class表
        $add = $m->add($data);

        if($add){

            //添加成功要去mk_category_list表更新税率信息
            $this->category_default_value($data['hs_code'],$data['rate']);

            $result = array('state' => 'yes','msg' => '添加成功');
            
        }else{

            $result = array('state' => 'no','msg' => '添加失败');
        }

        return $result;
    }

    //编辑
    public function _tax_edit($id,$data){

        $one = $this->_tax_one($id);

        if(!$one){

            return array('state' => 'no','msg' => '参数错误，请刷新页面');
        }

        $m = M('TaxRulesClass');

        //判断编辑的税号是否是原来的  不是就继续判断是否重复
        if($one['hs_code'] != $data['hs_code']){

            $info = $m->where(array('hs_code'=>$data['hs_code']))->find();

            if($info){

                return array('state' => 'no','msg' => '该税号已存在，不能重复添加');
            }
        }

        $where['id'] = $id;

        //把数据更新入mk_tax_rules_class表
        $save = $m->where($where)->save($data);

        if($save === false){

            $result = array('state'=>'no','msg'=>'更新失败');

        }else if($save == 0){

            $result = array('state'=>'no', 'msg'=>'您没有修改任何数据');

        }else{
            
            //修改成功要去mk_category_list表更新税率信息
            $this->category_default_value($data['hs_code'],$data['rate']);

            $result = array('state'=>'yes','msg'=>'更新成功');
        }

        return $result;
    }

    public function category_default_value($hs_code,$rate){

        $m = M('CategoryList');

        $where['hs_code'] = $hs_code;

        $where['default_value'] = 0;

        $one = $m->where($where)->find();

        if(!$one){

            return false;
        }

        //税率未改变不用修改
        if($one['price'] == $rate){

            return false;
        }

        $data['price'] = $rate;

        $m->where($where)->save($data);
        
    }

    //查询单类别详情
    public function _tax_one($id){

        $where['id'] = $id;

        $one = M('TaxRulesClass')->where($where)->find();

        $one['admin_name'] = M('ManagerList')->where(array('id'=>$one['operator_id']))->getField('name');

        return $one;
    }

    /**
	 * 导入CSV
	 * @return [type] [description]
	 */
    public function _import_csv($arr,$adminid){
		ini_set('max_execution_time', 0);
		ini_set('memory_limit','4088M');

        $i = 0;	//保存失败数量
        
        $j = 0;	//保存成功数量

        $list = array();

        //要过滤的中文字符
        $char = "－∕¦‖—　〈〉「」‹›』『》《―¯＿￣﹢﹦﹤‐­﹨ˉ﹁﹂﹃﹄";

        //正则
        $pattern = array(
			// "/[[:punct:]]/i", //英文标点符号
			'/['.$char.']/u', //中文标点符号
			'/[ ]{2,}/'
		);

        //管理员id
        $msg['operator_id'] = $adminid;

        $msg['status'] = 1;
        
        unset($arr[0]);

        foreach ($arr as $key => $v) {
            
            if(empty($v[0])){
                
                unset($arr[$key]);

            }else{

                $msg['hs_code'] = $v[0];
                
                $msg['name_and_spec'] = preg_replace($pattern,' ', $v[1]);
    
                $msg['cname1'] = preg_replace($pattern,' ', $v[2]);
    
                $msg['cname2'] = preg_replace($pattern,' ', $v[3]);
    
                $msg['cname3'] = preg_replace($pattern,' ', $v[4]);
    
                $msg['cname4'] = preg_replace($pattern,' ', $v[5]);
    
                $msg['cname5'] = preg_replace($pattern,' ', $v[6]);
    
                $msg['specifications'] = preg_replace("/(，)/",',',$v[7]);
    
                $msg['number'] = preg_replace("/(，)/",',',$v[8]);                
    
                $msg['price'] = $v[9];
    
                $msg['rate'] = $v[10];
    
                $msg['sort'] = $key - 1;
    
                $list[] = $msg;
            }
        }

        //判断数组内部是否有重复的税号
        $tmp_arr = array();

        foreach ($list as $k => $v) {

            if(in_array($v['hs_code'], $tmp_arr)){

                unset($list[$k]);

            }else{

                $tmp_arr[] = $v['hs_code'];
            }
        }

        $tax_model = M('TaxRulesClass');
        
        //把mk_tax_rules_class表清空
        $tax_del = $tax_model->where('id > 0')->delete();

        foreach ($list as $key => $v) {

            $add = $tax_model->add($v);

            if($add){

                $j++;

            }else{

                $i++;
            }
        }

		$msg = '成功：'.$j.'个，失败：'.$i.'个';
		//保存的数量 > 0，则成功
		if($j > 0){

			$backArr = array('status'=>'1', 'msg'=>'导入成功，'.$msg);
            return $backArr;
            
		}else{
            
			$backArr = array('status'=>'0', 'msg'=>'导入失败，'.$msg);
			return $backArr;
		}
    }
}