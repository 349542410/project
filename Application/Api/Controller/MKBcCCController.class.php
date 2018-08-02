<?php
/**
 * 美快优选CC物流管理(也是顺丰的)
 * 功能包括：身份证照片上传
 */
namespace Api\Controller;
use Think\Controller\HproseController;
class MKBcCCController extends HproseController{    
    protected $crossDomain =    true;
    protected $P3P         =    true;
    protected $get         =    true;
    protected $debug       =    true;

	public function _count($map,$p,$ePage){
		$list = M('TranList t')->field('t.id,t.MKNO,t.STNO,t.receiver,t.reTel,t.province,t.city,l.no,t.TranKd as tcid,ifnull(e.img_state,0) as status')->join('left join mk_user_extra_info e on e.idno=t.idno')->join('LEFT JOIN mk_transit_no l ON l.id = t.noid')->where($map)->order('ctime asc')->page($p.','.$ePage)->select();
		
		$count = M('TranList t')->join('left join mk_user_extra_info e on e.idno=t.idno')->join('LEFT JOIN mk_transit_no l ON l.id = t.noid')->where($map)->count();
		return array('count'=>$count, 'list'=>$list);
	}

	//检查订单中的身份证字段是否已经填写
	public function checkIdno($id){
		$user = M('TranList')->where(array('id'=>$id))->find();
		return $user;
	}

	//保存上传的证件照相关信息
	public function saveData($id, $file){
		$user = M('TranList')->where(array('id'=>$id))->find();

		//订单不存在
		if(!$user){
			return false;
		}

		$checkInfo = M('UserExtraInfo')->where(array('idno'=>$user['idno']))->find();

		$data = array();
		$data['id_img'] = $file;

		//已存在，则更新
		if($checkInfo){
			$save = M('UserExtraInfo')->where(array('idno'=>$user['idno']))->save($data);

		}else{//不存在，则新增

			$data['idno']    = $user['idno'];
			$save = M('UserExtraInfo')->add($data);
		}

		if($save !== false){
			return true;
		}else{
			return false;
		}
	}
}