<?php
/**
 * 推送身份证照片给顺丰 客户端
 * 
 */
namespace Admin\Controller;
use Think\Controller;
class SendIdImgController extends AdminbaseController {

    function _initialize() {
        parent::_initialize();
    }

    /**
     * 手动推送身份证图片(单个或多个)
     * 注意：由快件管理--对数进入的查询，暂不提供此操作
     * @return [type] [description]
     */
    public function index(){
    	if(!IS_AJAX){
    		echo '非法访问';die;
    	}

		ini_set('memory_limit','4088M');
		ini_set('max_execution_time', 0);

		$nos    = trim(I('no'));
		$tcid   = trim(I('tcid'));
		$type   = (I('type')) ? trim(I('type')) : '';
		$nos    = explode(',',$nos);
		$scount = count($nos);
    	// dump($nos);

    	// 20170119 jie 根据线路id判断哪个线路可以执行推送操作
    	$line_arr = array(
			C('Transit_Type.ST_Transit')     => '',
			C('Transit_Type.SF_Transit')     => '',
			C('Transit_Type.HkEms_Transit')  => '',
			C('Transit_Type.MKBc2_Transit')  => '',
			C('Transit_Type.MKBcCC_Transit') => 'true',//可以推送
			C('Transit_Type.GdEms_Transit')  => '',
    	);

    	$url = $line_arr[$tcid];

    	if($url == ''){
    		$backXML = array('do'=>'no', 'msg'=>'该线路的访问尚未开通');
    		$this->ajaxReturn($backXML);
    	}
    	// 20170119 End

		$et  = 0;// 计算成功推送的总数
		$msg = '';// 用于记录 返回的失败的单号的各自的单号及其对应的错误信息

    	foreach($nos as $k=>$item){
    		// dump($item);die;
    		vendor('Hprose.HproseHttpClient');
	        $client = new \HproseHttpClient(C('RAPIURL').'/SendIdImg');

	        $res[$k] = $client->_index($item,$type);

			if($res[$k]['result'] == 'true'){
				$et++;
			}else{
				$msg .= '单号：'.$item.'(ERROR：'.$res[$k]['message'].') ；';
			}
    	}

    	// dump($msg);die;
		// $et  计算成功获取并保存物流信息的总数
		if($et == 0){
			$backXML = array('do'=>'no', 'msg'=>'操作失败 ；'.$msg);
		}else{
			// 批量操作的回复
			if($scount > 1) {
				$backXML = array('do'=>'yes', 'msg'=>'操作成功，请求总数：'.$scount.'个 ；成功执行：'.$et.'个 。操作失败：'.($scount-$et).'个 【'.$msg.'】');
			}else{
				$backXML = array('do'=>'yes', 'msg'=>'操作成功');
			}
		}

		// dump($backXML);die;
		$this->ajaxReturn($backXML);
    }

}