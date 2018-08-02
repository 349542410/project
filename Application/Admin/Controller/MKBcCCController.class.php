<?php
/**
 * 美快优选CC物流管理 客户端
 * 证件上传的功能按钮分别布置在：快件管理，美快优选CC物流
 */
namespace Admin\Controller;
use Think\Controller;
class MKBcCCController extends AdminbaseController{

    function _initialize() {
        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/MKBcCC');		//读取、查询操作
        $this->client = $client;		//全局变量
    }

   	public function index(){

		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		$status     = I('get.status');
		$problem    = I('get.problem');//只显示问题件

		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');
		$this->assign($_GET);
		$this->assign('ePage',$ePage);
		
        //按查询类型搜索
        if(!empty($keyword) && !empty($searchtype)){
            if($searchtype == 'no'){
            	$where['l.'.$searchtype]=array('like','%'.$keyword.'%');
            }else{
            	$where['t.'.$searchtype]=array('like','%'.$keyword.'%');
            }
        }

        if($status != ''){
            $where['e.img_state'] = $status;
        }

        // if($problem == 'on'){
        // 	// $where['CheckFlg'] = '0';//不通过
        // 	if($status == ''){
        // 		$where['e.img_state'] = array('in','13,23,25,99');
        // 	}else{
        // 		$where['e.img_state'] = array(array('in','13,23,25,99'),array('eq',$status), 'and');
        // 	}
        // }

        $where['t.TranKd'] = array('eq',C('Transit_Type.MKBcCC_Transit'));//服务器上面的是12

        $client = $this->client;
		$res = $client->_count($where,$p,$ePage);
		$count = $res['count'];
		$list  = $res['list'];

		$page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

	$page->setConfig('prev', "上一页");//上一页
	$page->setConfig('next', '下一页');//下一页
	$page->setConfig('first', '首页');//第一页
	$page->setConfig('last', "末页");//最后一页
	$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
    		
		$show = $page->show(); // 分页显示输出
		$this->assign('page',$show);// 赋值分页输出

		$this->assign('list',$list);
		// $this->assign('id',$list['id']);

		$this->display();
   	}

   	//上传证件照 视图
   	public function upLoadView(){
   		// echo ADMIN_FILE.'/test2017';
   		$this->display();
   	}

   	// 上传证件照 方法
   	public function upLoad(){
   		$id = trim(I('id'));

   		$client = $this->client;
   		$user = $client->checkIdno($id);
   		if($user['idno'] == '' || strlen($user['idno']) != 18){
   			$this->error('请先补填身份证号码，再执行证件照上传！');
   		}


   		$upload           = new \Think\Upload();// 实例化上传类
		$upload->maxSize  = 819200;// 设置附件上传大小  不超过800k
		$upload->exts     = array('jpg', 'png', 'jpeg');// 设置附件上传类型
		$upload->rootPath = ADMIN_ABS_FILE; //设置文件上传保存的根路径
		$upload->savePath = C('UPLOADS_ID_IMG'); // 设置文件上传的保存路径（相对于根路径）
		$upload->autoSub  = true; //自动子目录保存文件
		$upload->subName  = array('date','Ymd');
		$upload->saveName = array('uniqid',mt_rand()); //设置上传文件名

		$info = $upload->upload();


	    if(!$info) {// 上传错误提示错误信息
	        $this->error($upload->getError());
	    }else{// 上传成功

			$file = $info['file']['savepath'] . $info['file']['savename'];
			// dump($file);die;

			$saveData = $client->saveData($id, $file);

			if($saveData !== false){
				$this->success('上传成功！');
			}else{
				//数据保存失败，则删除此次上传的图片，等用户再次上传
				unlink(K(ADMIN_ABS_FILE) . $info['file']['savepath'] . $info['file']['savename']);
				$this->error('数据保存失败，请重新上传！');
			}

	        
	    }
   	}
	/**
	 * 中转跟踪
	 * @return [type] [description]
	 */
	public function tran_track(){
        
		$type = (C('Transit_Type.MKBcCC_Transit')) ? C('Transit_Type.MKBcCC_Transit') : '';

		$list = R('Logarithm/index',array('request'=>true, 'type'=>$type));

		$this->assign('list',$list);
		$this->assign($_GET);
		$this->display('Public:logistics_strack');
	}

	/**
	 * 快递跟踪
	 * @return [type] [description]
	 */
	public function kd_track(){

		$type = (C('Transit_Type.MKBcCC_Transit')) ? C('Transit_Type.MKBcCC_Transit') : '';
		
		$list = R('Logarithm/pro_two',array('request'=>true, 'type'=>$type));

		$this->assign('list',$list);
		$this->assign($_GET);
		$this->display('Public:logistics_strack');
	}
}