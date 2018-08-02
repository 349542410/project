<?php
namespace Api\Controller;
use Think\Controller\HproseController;
class AdminRecipientController extends HproseController{

	/**
	 * 未处理身份证证件
	 * Enter description here ...
	 */
	public  function ntreated_documents($data){

    	//$point = M('user_addressee')->field('name, id_card_front_small, id_card_back_small, sys_time, cre_num')->where('status = 0 or status = 2 ')->select();
    	$where['status'] = array(array('eq', '0'), array('eq', '2'), 'OR');
    	if(!empty($data['name'])){
    		$where['true_name'] = array('like',array('%'.$data['name'].'%',));
    	}
    	if(!empty($data['cre_num'])){
    		$where['idno'] = $data['cre_num'];
    	}
       	//$count = M('user_addressee')->where('status = 0 or status = 2 ')->count();
    	$count = M('user_extra_info')->where($where)->count();
    	
//		$page=new \Think\Page($count,$data['epage']);
//		//%FIRST% 表示第一页的链接显示 
//		//%UP_PAGE% 表示上一页的链接显示 
//		//%LINK_PAGE% 表示分页的链接显示 
//		//%DOWN_PAGE% 表示下一页的链接显示 
//		//%END% 表示最后一页的链接显示 
//		
//		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
//		$page -> setConfig('prev', '上一页');
//		$page -> setConfig('next','下一页');
//		$page -> setConfig('last','末页');
//		$page -> setConfig('first','首页');
//		
//		$show = $page->show();

		//$list = M('user_addressee')->where('status = 0 or status = 2 ')->limit($page->firstRow.','.$page->listRows)->order('sys_time desc')->select();
    	//$list = M('user_addressee')->where('status = 0 or status = 2 ')->page($data['p'],$data['epage'])->order('sys_time desc')->select();
    	$list = M('user_extra_info')->where($where)->page($data['p'],$data['epage'])->order('idno desc')->select();
    	
		return array('count'=>$count, 'list'=>$list);
	
	}
	
	/**
	 * 获取收件人信息
	 * Enter description here ...
	 * @param $data
	 */
	public function user_addressee($data){
		$res = M('user_extra_info')->where($data)->find();
		return $res;
	}
	
	/**
	 * 查询所有身份证号码相同的收件人信息
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public function more($data){
		$where['idno'] = $data['idno'];
		$wh['cre_num'] = $data['idno'];
		//$where['rul.idno'] = $data['idno'];
		//$where['ua.cre_num'] = $data['idno'];
		$idno = $data['idno'];
		// ue.true_name as name, ue.idno, ue.front_id_img, ue.back_id_img, ua.name, ua.cre_num, ua.id_card_front as front_id_img, ua.id_card_back as back_id_img, rul.true_name as name, rul.idno, rul.front_id_img, rul.back_id_img
		//$res = M('user_extra_info as ue, receiver_upload_list as rul, user_addressee as ua')->where($where)->select();
		//$res = M()->query('select  ue.true_name as name, ue.idno, ue.front_id_img, ue.back_id_img, ua.name as name_one, ua.cre_num as indo_one, ua.id_card_front as front_id_img_one, ua.id_card_back as back_id_img_one, rul.true_name as name_two, rul.idno as idno_two, rul.front_id_img as front_id_img_two, rul.back_id_img as back_id_img_two FROM mk_user_extra_info as ue, mk_receiver_upload_list as rul, mk_user_addressee as ua where ue.idno = rul.idno = ua.cre_num = '.$idno.' ');
		//$res = 	M('user_extra_info as ue')->field(' ue.true_name as name, ue.idno, ue.front_id_img, ue.back_id_img, ua.name as name_one, ua.cre_num as indo_one, ua.id_card_front as front_id_img_one, ua.id_card_back as back_id_img_one, rul.true_name as name_two, rul.idno as idno_two, rul.front_id_img as front_id_img_two, rul.back_id_img as back_id_img_two')
		//		->join('full join mk_receiver_upload_list as rul ON ue.idno = rul.idno ')
		//		->join('full join mk_user_addressee as ua ON ue.idno = ua.cre_num')
		//		->where('ue.idno = '.$idno.' ');
		
		$res_one = M('user_extra_info')->alias('ue')->field('ue.true_name as name, ue.idno, ue.front_id_img, ue.back_id_img, ue.small_front_img, ue.small_back_img, ue.sys_time AS rtime ')->where($where)->select();
		$res_two = M('receiver_upload_list')->alias('rul')->field('rul.true_name as name, rul.idno, rul.front_id_img, rul.back_id_img, rul.small_front_img, rul.small_back_img, rul.rtime ')->where($where)->select();
		$res_three = M('user_addressee')->alias('ua')->field('ua.name, ua.cre_num as idno, ua.id_card_front as front_id_img, ua.id_card_back as back_id_img, ua.id_card_front_small as small_front_img, ua.id_card_back_small as small_back_img, ua.sys_time AS rtime')->where($wh)->select();
		$res['res_one'] = $res_one;
		$res['res_two'] = $res_two;
		$res['res_three'] = $res_three;
		
		return $res;
	}
	
	
	/**
	 * 覆盖身份证号码+名字相同的信息
	 * Enter description here ...
	 */
	public  function morehaddle($data){
		$where['true_name'] = $data['name'];
		$where['idno'] = $data['idno'];
		$res = M('user_extra_info')->where($where)->save($data);
		$row = M('receiver_upload_list')->where($where)->save($data);
		
		$wh['name'] = $data['name'];
		$wh['cre_num'] = $data['idno'];
		
		$da['id_card_front'] = $data['front_id_img'];
		$da['id_card_back'] = $data['back_id_img'];
		$da['id_card_front_small'] = $data['small_front_img'];
		$da['id_card_back_small'] = $data['small_back_img'];
		
		$rek = M('user_addressee')->where($wh)->save($da);
		if($res || $row || $rek){
			$rew['status'] = 1;
    		$rew['data']['strstr'] = '收件人信息修改成功！';
    		return $rew;
		}else{
			$rew['status'] = 0;
    		$rew['data']['strstr'] = '收件人信息修改失败！';
    		return $rew;
		}
		
		
	}
	
	
	
	
	/**
	 * 添加修改流官网订单收件人额外资料表
	 * Enter description here ...
	 * @param unknown_type $data
	 */
	public function cardpic($data){
//		if('add' == $data['type_h']){
//			unset($data['type_h']);
//			$res = M('user_extra_info')->add($data);
//		}elseif ('edit' == $data['type_h']){
//			unset($data['type_h']);
//			$res = M('user_extra_info')->where('id = '.$data['id'].' ')->save($data);
//		}
		//修改 user_extra_info 状态
		
		$w['true_name']         = $data['name'];
		$w['idno']               = $data['cre_num'];
		$w['id']                 = $data['id'];
		$info['status']          = $data['status'];

		if(isset($data['idcard_status'])){
            $info['idcard_status']  = $data['idcard_status'];
        }
		if(!empty($data['id_img'])){
			$info['id_img'] = $data['id_img'];
		}
		$res = M('user_extra_info')->where($w)->save($info);
//		if($data['status'] == 10){
//			//检验身份证号码  + 名字
//			$where['name'] = $data['name'];
//			$where['cre_num'] = $data['cre_num'];
//			$id = M('receiver_idinfo_list')->field('id')->where($where)->find();
//			//id存在就更新 不存在就添加
//			if($data['status'] = 10){
//				//$data['num_status'] = 10;
//			}
//			if($id){
//				$res = M('receiver_idinfo_list')->where($id)->save($data);
//			}else{
//				$res = M('receiver_idinfo_list')->add($data);
//			}
//			//如果已经审核  就修改mk_user_addressee状态
//			if(isset($data['address'])){
//				$wh['name'] 	= $data['name'];
//				$wh['cre_num'] 	= $data['cre_num'];
//				$rak['status']  = $data['status'];
//				M('user_addressee')->where($wh)->save($rak);
//			}
//		}
		
		return $res;
	}

	public function card_edit($data){
	    $where['id'] = $data['id'];
        unset($data['id']);
	    $res = M('user_extra_info')->where($where)->save($data);
	    return $res;
    }


    /**
     * 收件人身份证重新识别保存
     */
	public function extra_info_edit($where, $data){

	    $res = M('user_extra_info')->where($where)->save($data);

		return $res;
//	    return $res;
    }



	/**
	 * 
	 * Enter description here ...
	 */
	public function audit($data){
		$where['id'] = $data['id'];
		$res = M('user_extra_info')->where($where)->save($data);
		return $res;
	}
	
	
	/**
	 * 获取订单收件人信息
	 * Enter description here ...
	 */
	public function extra_info($data){
		//$where['id'] = $data['id'];
		$res = M('user_extra_info')->where($data)->find();
		
		return $res;
	}


	
	/**
	 * 已处理身份证证件
	 * Enter description here ...
	 */
//	public function processed($data){
//    	//$point = M('user_addressee')->field('name, id_card_front_small, id_card_back_small, sys_time, cre_num')->where('status = 0 or status = 2 ')->select();
//	    //$where['ua.status'] = 10;
//    	if(!empty($data['name'])){
//    		$where['ua.name'] = array('like',array('%'.$data['name'].'%',));
//    	}
//    	if(!empty($data['cre_num'])){
//    		$where['ua.cre_num'] = $data['cre_num'];
//    	}
//    	if(isset($where)){
//    		$count = M('receiver_idinfo_list')->alias('ua')->where($where)->count();
//    	}else{
//    		$count = M('receiver_idinfo_list')->count();
//    	}
//
////		$page=new \Think\Page($count,$data['epage']);
////		//%FIRST% 表示第一页的链接显示
////		//%UP_PAGE% 表示上一页的链接显示
////		//%LINK_PAGE% 表示分页的链接显示
////		//%DOWN_PAGE% 表示下一页的链接显示
////		//%END% 表示最后一页的链接显示
////
////		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
////		$page -> setConfig('prev', '上一页');
////		$page -> setConfig('next','下一页');
////		$page -> setConfig('last','末页');
////		$page -> setConfig('first','首页');
////
////		$show = $page->show();
//
//		//$list = M('user_addressee')->field('ua.*, ue.id_img, ue.id AS ueid')->alias('ua')->join('left join mk_user_extra_info AS ue ON ua.id = ue.addressee_id ')->where('status = 10')->limit($page->firstRow.','.$page->listRows)->order('sys_time desc')->select();
//    	if(isset($where)){
//    		$list = M('receiver_idinfo_list')->field('ua.*')->alias('ua')->where($where)->page($data['p'],$data['epage'])->order('id desc')->select();
//    	}else{
//    		$list = M('receiver_idinfo_list')->page($data['p'],$data['epage'])->order('id desc')->select();
//    	}
//
//    	return array('count'=>$count, 'list'=>$list);
//
//	}


    /**
     * 未处理身份证证件
     * Enter description here ...
     */
    public  function processed($data){

        //$point = M('user_addressee')->field('name, id_card_front_small, id_card_back_small, sys_time, cre_num')->where('status = 0 or status = 2 ')->select();
        $where['status'] = array('eq', '10');
        if(!empty($data['name'])){
            $where['true_name'] = array('like',array('%'.$data['name'].'%',));
        }
        if(!empty($data['cre_num'])){
            $where['idno'] = $data['cre_num'];
        }
        //$count = M('user_addressee')->where('status = 0 or status = 2 ')->count();
        $count = M('user_extra_info')->where($where)->count();

//		$page=new \Think\Page($count,$data['epage']);
//		//%FIRST% 表示第一页的链接显示
//		//%UP_PAGE% 表示上一页的链接显示
//		//%LINK_PAGE% 表示分页的链接显示
//		//%DOWN_PAGE% 表示下一页的链接显示
//		//%END% 表示最后一页的链接显示
//
//		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
//		$page -> setConfig('prev', '上一页');
//		$page -> setConfig('next','下一页');
//		$page -> setConfig('last','末页');
//		$page -> setConfig('first','首页');
//
//		$show = $page->show();

        //$list = M('user_addressee')->where('status = 0 or status = 2 ')->limit($page->firstRow.','.$page->listRows)->order('sys_time desc')->select();
        //$list = M('user_addressee')->where('status = 0 or status = 2 ')->page($data['p'],$data['epage'])->order('sys_time desc')->select();
        $list = M('user_extra_info')->where($where)->page($data['p'],$data['epage'])->order('idno desc')->select();

        return array('count'=>$count, 'list'=>$list);

    }




    /**
	 * 所有身份证证件
	 * Enter description here ...
	 */
	public function add_documents($data){
		
	    if(!empty($data['name'])){
    		$where['ua.true_name'] = array('like',array('%'.$data['name'].'%',));
    	}
    	if(!empty($data['cre_num'])){
    		$where['ua.idno'] = $data['cre_num'];
    	}		
		if(isset($where)){
			$count = M('user_extra_info')->alias('ua')->where($where)->count();
		}else{
			$count = M('user_extra_info')->count();
		}
       	
//		$page=new \Think\Page($count,$data['epage']);
//		//%FIRST% 表示第一页的链接显示 
//		//%UP_PAGE% 表示上一页的链接显示 
//		//%LINK_PAGE% 表示分页的链接显示 
//		//%DOWN_PAGE% 表示下一页的链接显示 
//		//%END% 表示最后一页的链接显示 
//		
//		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
//		$page -> setConfig('prev', '上一页');
//		$page -> setConfig('next','下一页');
//		$page -> setConfig('last','末页');
//		$page -> setConfig('first','首页');
//		
//		$show = $page->show();

		//$list = M('user_addressee')->field('ua.*, ue.id_img, ue.id AS ueid')->alias('ua')->join('left join mk_user_extra_info AS ue ON ua.id = ue.addressee_id ')->limit($page->firstRow.','.$page->listRows)->order('sys_time desc')->select();
    	if(isset($where)){
    		$list = M('user_extra_info')->field('ua.*')->alias('ua')->where($where)->page($data['p'],$data['epage'])->order('id desc')->select();
    	}else{
			$list = M('user_extra_info')->field('ua.*')->alias('ua')->page($data['p'],$data['epage'])->order('id desc')->select();
    	}
		
    	return array('count'=>$count, 'list'=>$list);		
		
	}
	
	
	/**
	 * 下载图片
	 * Enter description here ...
	 */
	public function download($data){
		$where['id'] = $data['id'];
		$rew = M('user_extra_info')->field('id, idno, id_img, front_id_img, back_id_img')->where($where)->find();
		return $rew;
	}
	
	/**
	 * 获取收件人证件信息
	 * Enter description here ...
	 * @param unknown_type $where
	 */
	public function backstage($where){
		$w['ua.id'] = $where['id'];
		$res = M('user_addressee')->alias('ua')->field('ua.name, ua.cre_num, ua.id_card_front, ua.id_card_back, ua.id_card_front_small, ua.id_card_back_small, ue.id_img')
				->join('left join mk_user_extra_info AS ue ON ua.id = ue.addressee_id ')->where($w)->find();
		return $res;
	
	}
	
	/**
	 * 检验后台收件人证件信息
	 * Enter description here ...
	 */
	public function backstage_query($w){
		$res = M('receiver_idinfo_list')->where($w)->find();
		if($res){
			return false;
		}else{
			return true;
		}
		
	}
	
	/**
	 * 添加已处理收件人证件信息
	 * Enter description here ...
	 * @param unknown_type $w
	 */
	public function backstage_add($w){
		$res = M('receiver_idinfo_list')->add($w);
		return $res;
	}
	
	
	/**
	 * 查询获取已处理收件人证件信息
	 * Enter description here ...
	 */
	public function info_processed($w){
		$res = M('receiver_idinfo_list')->where($w)->find();
		return $res;
		
	}
	
	/**
	 * 已处理证件图片下载
	 * Enter description here ...
	 */
	public function download_proc($data){
		$where['id'] = $data['id'];
		$rew = M('receiver_idinfo_list')->where($where)->find();
		return $rew;
	}

	
	/**
	 * 身份证信息API  
	 * Enter description here ...
	 */	
	public function idcard($where){
		$res = M('receiver_idinfo_list')->where($where)->find();
		return $res;
	}
	
	
	public function audit_processed($data){
		//检验身份证号码审核状态   状态为10 不能修改
		$w['id'] = $data['id'];
		$juge = M('receiver_idinfo_list')->field('status, num_status')->where($w)->find();

		if(empty($juge)){
			$row['status'] = false;
			$row['errorstr'] = '身份证号码信息不存在';
			return $row;
		}
		if($juge['num_status'] == 10){
			$row['status'] = false;
			$row['errorstr'] = '身份证号码已确认，不能修改状态';
			return $row;
		}
		elseif($juge['status'] != 10){
			$row['status'] = false;
			$row['errorstr'] = '身份证图片未确认';
			return $row;
		}
		else {
			$rew = M('receiver_idinfo_list')->where($w)->save($data);
			if($rew){
				$row['status'] = true;
				$row['strstr'] = '修改成功';
				return $row;
			}else{
				$row['status'] = false;
				$row['errorstr'] = '修改失败';
				return $row;
			}
		}
		
	}
	
	
	/**
	 * 保存收件人信息
	 * Enter description here ...
	 */
	public function process($data){
		
		$res = M('user_extra_info')->add($data);
		//$a  = M('receiver_idinfo_list')->getLastSql();
		return $res;
		
	}
	
	
	
	
}
	