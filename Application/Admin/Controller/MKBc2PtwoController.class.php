<?php
/**
 * 美快BC优选2
 * 创建时间：2017-04-21
 * 修改时间：2017-04-21
 * created by Jie
 * 指导文档：纵腾跨境贸易通关辅助系统API文档.pdf  的  1. 提交初始订单
 * 功能包括： 报关 功能之一(预计分3种报关方式，这是其一)
 * 
 */
namespace Admin\Controller;
use Think\Controller;
class MKBc2PtwoController extends AdminbaseController{
	
    function _initialize() {
        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/MKBc2Ptwo');     //读取、查询操作
        $this->client = $client;        //全局变量
    }

//====================================================
// 报关(订单报备)
//====================================================
    /**
     * 报关 列表
     * @return [type] [description]
     */
    public function apply_customs(){

        $keyword    = trim(I('get.keyword'));
        $searchtype = I('get.searchtype');
        $tcid       = (C('Transit_Type.MKBc2_Transit')) ? trim(C('Transit_Type.MKBc2_Transit')) : '';//20161116 jie   增加 TrandKd = 中转线路.id 的查询
        $starttime  = intval(I('starttime'));
        $endtime    = intval(I('get.endtime'));

        //按查询类型搜索
        if(!empty($keyword) && !empty($searchtype)){
            $map['tn.'.$searchtype] = array('like','%'.$keyword.'%');
        }

        // 必须符合这两个状态条件
        // $map['tn.send_report'] = array('eq','1'); //1 已执行发货通知

        if($tcid != ''){
            $map['tn.tcid'] = array('eq',$tcid);//标签A
        }

        //按时间段搜索
        if(!empty($starttime) && !empty($endtime)){
            $starttime = date('Y-m-d H:i:s',$starttime);
            $endtime   = date('Y-m-d H:i:s',$endtime);
            $map['tn.date'] = array('between',$starttime.",".$endtime);

        }else if(!$starttime && $endtime){
            $endtime = date('Y-m-d H:i:s',$endtime);
            $map['tn.date'] = array('elt',$endtime);

        }else if($starttime && !$endtime){
            $starttime = date('Y-m-d H:i:s',$starttime);
            $map['tn.date'] = array('egt',$starttime);
        }

        $transit = new \HproseHttpClient(C('RAPIURL').'/MkBcInfo');
        $res = $transit->customsList($map);

        $this->assign('list',$res['list']);
        $this->assign($_GET);
        $this->display();
    }

    /**
     * 获取报关状态
     * @return [type] [description]
     */
    public function getStatus(){
        $id = I('id');

        // 批次号所属的线路id
        $tcid = (C('Transit_Type.MKBc2_Transit')) ? trim(C('Transit_Type.MKBc2_Transit')) : '';

        $client = $this->client;

        $res = $client->_getStatus($id, $tcid);

        $this->ajaxReturn($res);
    }

    /**
     * orderList页面  视图  数据查询
     * @return [type] [description]
     */
    public function orderList(){
        $p = I('get.p')?I('get.p'):1;   //当前页数，如果没有则默认显示第一页

        $stype  = I('get.kind')?I('get.kind'):""; //done 已发送； not 未发送
        $noid   = I('get.nid')?I('get.nid'):""; //tran_list.noid

        $client = $this->client;
        $res = $client->_orderList($noid, $stype, $p);

        $this->ilstarr = C('ilstarr');

        //$ilstarri中的0-9 是与 $ilstarr 中的key对应
        $this->ilstarri = C('ilstarri');

        $this->assign('list',$res['0']);    //数据列表
        // dump($res['0']);die;
        $page = new \Think\Page($res['1'],30); // 实例化分页类 传入总记录数和每页显示的记录数(20)
        $page->setConfig('prev', "上一页");//上一页
        $page->setConfig('next', '下一页');//下一页
        $page->setConfig('first', '首页');//第一页
        $page->setConfig('last', "末页");//最后一页
        $page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
            
        $show = $page->show(); // 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('mstr',$res['2']);// 
        $this->assign('record_list',$res['3']);// 
        $this->assign('sel_list',C('LOGARTHM_SELECT')); //20160511 Jie
        $this->assign('custom_list',C('CustomStateList')); //20160511 Jie

        $this->assign('nid',trim(I('get.nid'))); //20170119 jie

        $this->display();
    }

    /**
     * 报关  分别是 已发送/未发送 的 单个或多个订单 进行推送
     * @return [type] [description]
     */
    public function post_order(){
        if(!IS_AJAX){
            echo '非法访问';die;
        }

        ini_set('memory_limit','4088M');
        ini_set('max_execution_time', 0);

        $nos    = trim(I('num'));   //tran_list.id集
        $nid    = trim(I('nid'));   //transit_no.id
        $tcid   = (C('Transit_Type.MKBc2_Transit')) ? trim(C('Transit_Type.MKBc2_Transit')) : '';//transit_center.id

        $client = $this->client;

        $res = $client->_post_order($nos, $nid, $tcid);
        $this->ajaxReturn($res);
    }

    /**
     * 报关 --- 资料导出(导出成功后直接提成用户下载)
     * 导出文件，是用于手动形式报关，处理结果跟“发送”形式的报关一致
     * @return [type] [description]
     */
    public function toExport(){
    	$noid= I('id');
		$client   = $this->client;
		$getlist  = $client->_export_file($noid);

		$nos = array();//MKNO数组集

		// 设置为文本无科学计数
		foreach($getlist as $key=>$item){

			$nos[$key] = $item['f20']; //STNO数组集
			// unset($getlist[$key]['f20']); //STNO数组集 获取得到STNO之后，清除此项

	        /* 去除详细地址中的省市区和空格 */
            $getlist[$key]['f7'] = str_replace($getlist[$key]['f11'],'',$getlist[$key]['f7']);
            $getlist[$key]['f7'] = str_replace($getlist[$key]['f10'],'',$getlist[$key]['f7']);
	        $getlist[$key]['f7'] = str_replace($getlist[$key]['f9'],'',$getlist[$key]['f7']);
	        $getlist[$key]['f7'] = trim(str_replace(' ','',$getlist[$key]['f7']));

			foreach($item as $k=>$it){
				if(in_array($k, array('f17'))){
					$getlist[$key][$k] = "\t".$it;	// 符合规则的，则在字段前面添加"\t"
				}
			}
        }

		$nos = array_filter($nos);//移除数组中的空值，并返回结果为数组
        // dump($nos);
        $nos = array_unique($nos);//移除数组中的重复的值，并返回结果为数组

		// dump($nos);die;
        $client->saveState($nos, C('Transit_Type.MKBc2_Transit'));

		$title = "原始单号,证件号码,总费用,订单时间,收货人,收货地址,收货人电话,省,市,县,邮编,备注,店铺货号,数量,单价,支付单号,订单号,订购人姓名,STNO,重量(lb),商品名称,海关商品报备编码";

		$fpd = 'MkBc2'; // 20160912 jie 文件名前缀
		
		$filename = $fpd."-".date('YmdHis');				//导出的文件名
		// $fileurl  = K(C('CSVURL').'/'.$fpd.'/'.$filename);	//20170220 jie

        $exportexcel  = new \Libm\MKILExcel\MkilExportMarket;

		$exportexcel->SaveName      = $filename;		//文件名;直接输出到浏览器的时候不需要包含路径
		$exportexcel->Title         = $title;		//单元格表头
		$exportexcel->Data          = $getlist;		//导出数据数组
		$exportexcel->Format        = '2003';   	// 导出类型：csv, 2003表示excel2003, 2007表示excel2007
		// $exportexcel->Clear_List = array();   	// 需执行清空字段数组 没有则不需要填写
		$exportexcel->Model_Type    = '1';   	// 是否进行省略操作
		$exportexcel->Sort          = false;   	// 是否带序号，false为不需要序号
		$exportexcel->OutPut        = true;   	// 是否直接输出到浏览器。
		$exportexcel->Title_Style   = true;   	// 单元格表头是否需要样式设计

		$exportexcel->export();  				// 返回true,false
		// dump($exportexcel->export());
		
    }

    public function apply_goods(){
    	$this->display();
    }
}