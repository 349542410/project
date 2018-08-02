<?php
/**
 * 财务流水 客户端
 */
namespace Admin\Controller;
use Think\Controller;
class FinanceController extends AdminbaseController{

    function _initialize() {
        parent::_initialize();
        $client = new \HproseHttpClient(C('RAPIURL').'/Finance');		//读取、查询操作
        $this->client = $client;		//全局变量

    }

	/**
	 * 视图
	 * @return [type] [description]
	 */
	public function index(){

		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		// $groupid = intval($_GET['groupid']);
		$starttime  = intval(I('get.starttime'));
		$endtime    = intval(I('get.endtime'));
		$line       = (I('line')) ? trim(I('line')) : ''; //线路ID
        $ilstate    = (I('get.mkkd')) ? I('get.mkkd') : '0';//按物流状态
        //echo $starttime;
        //exit;
		//分页显示的数量
		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');
        $download = I('get.download');

		$this->assign($_GET);
		$this->assign('ePage',$ePage);
		
		$where = array();

    	/* 20180307 jie */
        $PublicLineData = new \Admin\Controller\PublicLineDataController();
        $PublicLineData->line_id = true;

        $tcids = $PublicLineData->intersect();//一维数组

        // 如果不是 全部线路权限，则根据实际分配的线路权限，读取线路信息
        if($tcids !== true){
        	$map['t.TranKd'] = array('in',$tcids);
        }
        $center_list = $PublicLineData->get_lines();
        /* 20180307 jie */

		if($line != '') $where['t.TranKd'] = array('eq', $line);

        if($ilstate>0){
        	$where['a.IL_state'] = array('eq', $ilstate);
        }

		//按用户名搜索
		if(!empty($keyword) && !empty($searchtype)){
			if($searchtype == 'username'){
				$where["u.".$searchtype]=array('like','%'.$keyword.'%');
			}else if($searchtype == 'auto_Indent2'){
				$where["a.".$searchtype]=array('like','%'.$keyword.'%');
			}else{
				$where["t.".$searchtype]=array('like','%'.$keyword.'%');
			}
		}

		//按时间段搜索
		if($starttime && $endtime){
			$starttime = date('Y-m-d H:i:s',$starttime);
			$endtime   = date('Y-m-d H:i:s',$endtime);
			$where['r.paytime'] = array('between',array($starttime,$endtime));
		}else if(!$starttime && $endtime){
			$endtime = date('Y-m-d H:i:s',$endtime);
			$where['r.paytime'] = array('elt',$endtime);
		}else if($starttime && !$endtime){
			$starttime = date('Y-m-d H:i:s',$starttime);
			$where['r.paytime'] = array('egt',$starttime);
		}

		$where['t.pay_state'] = array('eq',1); //成功消费
		$where['t.print_state'] = array('eq',200); //打印成功的订单才有MKNO,STNO

        //搜索栏中的 物流状态
        $this->assign('ilstarr',C('ilstarr'));

        //$ilstarri中的0-9 是与 $ilstarr 中的key对应
        $this->assign('ilstarri',C('ilstarri'));
        $this->assign('ilst',$ilstate);

        $client = $this->client;
        if(!empty($download)){
            set_time_limit(0);
            $res = $client->consume($where);
        }else{
            $res = $client->count($where,$p,$ePage);
        }
		$count = $res['count'];
		$list  = $res['list'];

         //物品名称
		foreach ($list as $key => $value){
		    $wh['lid'] = $value['lid'];

            usleep(3);  //延时3毫秒执行
            $goods = $this->client->goods($wh);

		    if(!empty($goods)){
		        //$goods_name = array_column($goods, 'detail');
                foreach ($goods as $k => $v){
                    $goods_name[] = $v['detail'];

                }
		        $goods_str = implode(',', $goods_name);

		        $list[$key]['goods_name'] = $goods_str;
            }else{
                $list[$key]['goods_name'] = '';
            }

            $dataResult[$key]['id'] = $value['id'];
            $dataResult[$key]['username'] = $value['username'];
            $dataResult[$key]['auto_Indent2'] = $value['auto_Indent2'];
            $dataResult[$key]['STNO'] = $value['STNO'];
            $dataResult[$key]['paytime'] = $value['paytime'];
            $dataResult[$key]['cost_type'] = C('USER_PAY_STATUS')[$value['cost_type']];
            $dataResult[$key]['freight'] = $value['freight'];
            $dataResult[$key]['tax'] = $value['tax'];
            $dataResult[$key]['discount_amount'] = $value['discount_amount'];
            $dataResult[$key]['extra_fee'] = $value['extra_fee'];
            //if($value['optime']){
            $lstatus = '';
            !empty($value['optime']) ? $lstatus .=  $value['optime'] : '';
            //}
            $IL_state = C('ilstarr')[C('ilstarri')[$value['IL_state']]][1];
            !empty($IL_state) ? $lstatus .=  $IL_state : '状况不明';
            $lstatus .= '【'.$value['mode'].'】';

            $dataResult[$key]['Logistics_state'] = $lstatus;
            $logis_info = '';

            !empty($value['ex_time']) ? $logis_info .= $value['ex_time'] .'：' : '';
            !empty($value['ex_context']) ? $logis_info .= $value['ex_context'] : '';


            $dataResult[$key]['logis_info'] = $logis_info;
            $dataResult[$key]['point_name'] = $value['point_name'];
            $dataResult[$key]['operator'] = $value['operator'];
            $dataResult[$key]['terminal_name'] = $value['terminal_name'];

            $dataResult[$key]['weight'] = $value['weight'];
            $dataResult[$key]['goods_name'] = $list[$key]['goods_name'];




            $goods_name =array();
        }
        if($download){
            $title = "会员消费记录";
            //$filename = $title.".xls";
            $filename = $title;
                //$this->excelData($dataResult,$titlename,$headtitle,$filename);
            $headArr = array(
                '序号',
                '会员',
                '美快运单号',
                '运单号',
                '发货时间',
                '支付类型',
                '运费($)',
                '税费($)',
                '优惠金额($)',
                '附加费($)',
                '物流状态',
                '物流信息',
                '揽收点',
                '揽收人',
                '自助终端编号',
                '重量',
                '物品名称',

            );
            set_time_limit(30);
            //$headArr = explode(',',$title);
            set_time_limit(30);
            $exportexcel  = new \Libm\MKILExcel\ExcelOperation;//上线  使用的时候使用此加载
            $exportexcel->push($filename,$headArr,$dataResult);
            exit;
        }




		$page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

		// 过滤空格 20180410 jie
        foreach($page->parameter as $k1=>$v1){
            $page->parameter[$k1] = trim($v1);
        }

		$page->setConfig('prev', "上一页");//上一页
		$page->setConfig('next', '下一页');//下一页
		$page->setConfig('first', '首页');//第一页
		$page->setConfig('last', "末页");//最后一页
		$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
    		
		$show = $page->show(); // 分页显示输出
		$this->assign('page',$show);// 赋值分页输出

		$this->assign('list',$list);
		$this->assign('line_list',$center_list);//线路列表 搜索栏
		$this->display();
	}

	/**
	 * 弹出层
	 * @return [type] [description]
	 */
	public function recharge_info(){
        
		$id = I('get.id');
		$map['id'] = array('eq',$id);

		$client = $this->client;
		$info   = $client->_recharge_info($map);
		
		$this->assign('info',$info);
		$this->display();
	}

	public function recharge(){

		$keyword    = trim(I('get.keyword'));
		$searchtype = I('get.searchtype');
		// $groupid = intval($_GET['groupid']);
		$starttime  = intval(I('get.starttime'));
		$endtime    = intval(I('get.endtime'));
        $download   = I('get.download');

		//分页显示的数量
		$p = (I('p')) ? trim(I('p')) : '1';
		$ePage = (I('get.ePage')) ? trim(I('get.ePage')) : C('EPAGE');

		$this->assign($_GET);
		$this->assign('ePage',$ePage);
		
		//按用户名搜索
		if(!empty($keyword) && !empty($searchtype)){
			if($searchtype == 'username'){
				$where["u.".$searchtype]=array('like','%'.$keyword.'%');
			}else{
				$where["r.".$searchtype]=array('like','%'.$keyword.'%');
			}
		}

		//按时间段搜索
		if($starttime && $endtime){
			$starttime = date('Y-m-d H:i:s',$starttime);
			$endtime   = date('Y-m-d H:i:s',$endtime);
			$where['r.ordertime'] = array('between',array($starttime,$endtime));
		}else if(!$starttime && $endtime){
			$endtime = date('Y-m-d H:i:s',$endtime);
			$where['r.ordertime'] = array('elt',$endtime);
		}else if($starttime && !$endtime){
			$starttime = date('Y-m-d H:i:s',$starttime);
			$where['r.ordertime'] = array('egt',$starttime);
		}

		$where['r.pay_state'] = array('eq',200); //成功充值
        $client = $this->client;

		$res = $client->_charge($where,$p,$ePage);
		$count = $res['count'];
		$list  = $res['list'];
		$page = new \Think\Page($count,$ePage); // 实例化分页类 传入总记录数和每页显示的记录数(20)

		// 过滤空格 20180410 jie
        foreach($page->parameter as $k1=>$v1){
            $page->parameter[$k1] = trim($v1);
        }

        //hua 20180517 start
        if(!empty($download)) {
            set_time_limit(0);
            $list = $this->client->exportse($where);

            foreach ($list as $key => $val) {
                $dataResult[$key]['id'] = $val['id'];

                $dataResult[$key]['username'] = $val['username'];
                $dataResult[$key]['order_no'] = $val['order_no'];
                $dataResult[$key]['payno'] = $val['payno'];
                $dataResult[$key]['ordertime'] = $val['ordertime'];
                $dataResult[$key]['paytime'] = $val['paytime'];
                $dataResult[$key]['paykind'] = C('Pay_Kind')[$val['paykind']];
                $dataResult[$key]['amount_usa'] = $val['amount_usa'];
                $dataResult[$key]['user_balance_usa'] = $val['user_balance_usa'];
                $dataResult[$key]['operator'] = !empty($val['operator']) ? $val['operator'] : '会员充值';

            }
            $filename = "会员充值记录-";
            $headArr = array(
               '序号',
               '会员',
               '内部订单号',
               '支付单号',
               '创建时间',
               '支付时间',
               '支付方式',
               '充值金额($)',
               '账户余额($)',
               '操作员',
            );
            set_time_limit(30);
            $exportexcel  = new \Libm\MKILExcel\ExcelOperation;//上线  使用的时候使用此加载
            $exportexcel->push($filename,$headArr,$dataResult);
            exit;
        }

		$page->setConfig('prev', "上一页");//上一页
		$page->setConfig('next', '下一页');//下一页
		$page->setConfig('first', '首页');//第一页
		$page->setConfig('last', "末页");//最后一页
		$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
    		
		$show = $page->show(); // 分页显示输出
		$this->assign('page',$show);// 赋值分页输出

		$this->assign('list',$list);
		$this->display();
	}


    /**
     * 消费记录下载
     */
//	public function consume(){
//
//        $keyword    = trim(I('get.keyword'));
//        $searchtype = I('get.searchtype');
//        // $groupid = intval($_GET['groupid']);
//        $starttime  = intval(I('get.starttime'));
//        $endtime    = intval(I('get.endtime'));
//        $line       = (I('line')) ? trim(I('line')) : ''; //线路ID
//        $ilstate    = (I('get.mkkd')) ? I('get.mkkd') : '0';//按物流状态
//
//                $where = array();
//
//        /* 20180307 jie */
//        $PublicLineData = new \Admin\Controller\PublicLineDataController();
//        $PublicLineData->line_id = true;
//
//        $tcids = $PublicLineData->intersect();//一维数组
//
//        // 如果不是 全部线路权限，则根据实际分配的线路权限，读取线路信息
//        if($tcids !== true){
//            $map['t.TranKd'] = array('in',$tcids);
//        }
//
//        $center_list = $PublicLineData->get_lines();
//
//        /* 20180307 jie */
//
//        if($line != '') $where['t.TranKd'] = array('eq', $line);
//
//        if($ilstate>0){
//            $where['a.IL_state'] = array('eq', $ilstate);
//        }
//
//        //按用户名搜索
//        if(!empty($keyword) && !empty($searchtype)){
//            if($searchtype == 'username'){
//                $where["u.".$searchtype]=array('like','%'.$keyword.'%');
//            }else if($searchtype == 'auto_Indent2'){
//                $where["a.".$searchtype]=array('like','%'.$keyword.'%');
//            }else{
//                $where["t.".$searchtype]=array('like','%'.$keyword.'%');
//            }
//        }
//
//        //按时间段搜索
//        if($starttime && $endtime){
//            $starttime = date('Y-m-d H:i:s',$starttime);
//            $endtime   = date('Y-m-d H:i:s',$endtime);
//            $where['r.paytime'] = array('between',array($starttime,$endtime));
//        }else if(!$starttime && $endtime){
//            $endtime = date('Y-m-d H:i:s',$endtime);
//            $where['r.paytime'] = array('elt',$endtime);
//        }else if($starttime && !$endtime){
//            $starttime = date('Y-m-d H:i:s',$starttime);
//            $where['r.paytime'] = array('egt',$starttime);
//        }
//
//        $where['t.pay_state'] = array('eq',1); //成功消费
//        $where['t.print_state'] = array('eq',200); //打印成功的订单才有MKNO,STNO
//
////        //搜索栏中的 物流状态
////        $this->assign('ilstarr',C('ilstarr'));
////
////        //$ilstarri中的0-9 是与 $ilstarr 中的key对应
////        $this->assign('ilstarri',C('ilstarri'));
////        $this->assign('ilst',$ilstate);
//
//        $client = $this->client;
//
//        $res = $client->consume($where);
//        $list  = $res['list'];
//
//        foreach ($list as $key => $value){
//            $wh['lid'] = $value['id'];
//            $goods = $this->client->goods($wh);
//            if(!empty($goods)){
//                $goods_name = array_column($goods, 'detail');
//                $goods_str = implode(',', $goods_name);
//                $list[$key]['goods_name'] = $goods_str;
//            }else{
//                $list[$key]['goods_name'] = '';
//            }
//            $dataResult[$key]['id'] = $value['id'];
//            $dataResult[$key]['username'] = $value['username'];
//            $dataResult[$key]['auto_Indent2'] = $value['auto_Indent2'];
//            $dataResult[$key]['STNO'] = $value['STNO'];
//            $dataResult[$key]['paytime'] = $value['paytime'];
//            $dataResult[$key]['cost_type'] = C('USER_PAY_STATUS')[$value['cost_type']];
//            $dataResult[$key]['freight'] = $value['freight'];
//            $dataResult[$key]['tax'] = $value['tax'];
//            $dataResult[$key]['discount_amount'] = $value['discount_amount'];
//            $dataResult[$key]['extra_fee'] = $value['extra_fee'];
//            //if($value['optime']){
//            $lstatus = '';
//            !empty($value['optime']) ? $lstatus .=  $value['optime'] : '';
//            //}
//            $IL_state = C('ilstarr')[C('ilstarri')[$value['IL_state']]][1];
//            !empty($IL_state) ? $lstatus .=  $IL_state : '状况不明';
//            $lstatus .= '【'.$value['mode'].'】';
//
//            $dataResult[$key]['Logistics_state'] = $lstatus;
//            $logis_info = '';
//
//            !empty($value['ex_time']) ? $logis_info .= $value['ex_time'] .'：' : '';
//            !empty($value['ex_context']) ? $logis_info .= $value['ex_context'] : '';
//
//
//            $dataResult[$key]['logis_info'] = $logis_info;
//            $dataResult[$key]['point_name'] = $value['point_name'];
//            $dataResult[$key]['operator'] = $value['operator'];
//            $dataResult[$key]['terminal_name'] = $value['terminal_name'];
//
//            $dataResult[$key]['weight'] = $value['weight'];
//            $dataResult[$key]['goods_name'] = $list[$key]['goods_name'];
//
//
//
//        }
//        $title = "会员消费记录-". date('Y-m-d', time());
//        $headtitle= "<tr style='height:50px;border-style:none;><th border=\"0\" style='height:60px;width:270px;font-size:22px;' colspan='11' >{$headTitle}</th></tr>";
//        $titlename = "<tr>
//               <th style='width:100px;' align='center'>序号</th>
//               <th style='width:100px;' align='center'>会员</th>
//               <th style='width:200px;' align='center'>美快运单号</th>
//               <th style='width:200px;' align='center'>运单号</th>
//               <th style='width:200px;' align='center'>发货时间</th>
//               <th style='width:200px;' align='center'>支付类型</th>
//               <th style='width:100px;' align='center'>运费($)</th>
//               <th style='width:100px;' align='center'>税费($)</th>
//               <th style='width:100px;' align='center'>优惠金额($)</th>
//               <th style='width:100px;' align='center'>附加费($)</th>
//
//               <th style='width:200px;' align='center'>物流状态</th>
//               <th style='width:200px;' align='center'>物流信息</th>
//               <th style='width:200px;' align='center'>揽收点</th>
//               <th style='width:100px;' align='center'>揽收人</th>
//               <th style='width:100px;' align='center'>自助终端编号</th>
//               <th style='width:100px;' align='center'>重量</th>
//               <th style='width:100px;' align='center'>物品名称</th>
//
//           </tr>";
//        $filename = $title.".xls";
//        //$this->excelData($dataResult,$titlename,$headtitle,$filename);
//        $headArr = array(
//                '序号',
//                '会员',
//                '美快运单号',
//                '运单号',
//                '发货时间',
//                '支付类型',
//                '运费($)',
//                '税费($)',
//                '优惠金额($)',
//                '附加费($)',
//                '物流状态',
//                '物流信息',
//                '揽收点',
//                '揽收人',
//                '自助终端编号',
//                '重量',
//                '物品名称',
//
//        );
//        //$headArr = explode(',',$title);
//        $exportexcel  = new \Libm\MKILExcel\ExcelOperation;//上线  使用的时候使用此加载
//        $exportexcel->push($filename,$headArr,$dataResult);
//    }





    /**
     * 充值记录下载
     */
//	public function exportse(){
//
//        $keyword    = trim(I('get.keyword'));
//        $searchtype = I('get.searchtype');
//        // $groupid = intval($_GET['groupid']);
//        $starttime  = intval(I('get.starttime'));
//        $endtime    = intval(I('get.endtime'));
//        //按用户名搜索
//        if(!empty($keyword) && !empty($searchtype)){
//            if($searchtype == 'username'){
//                $where["u.".$searchtype]=array('like','%'.$keyword.'%');
//            }else{
//                $where["r.".$searchtype]=array('like','%'.$keyword.'%');
//            }
//        }
//
//        //按时间段搜索
//        if($starttime && $endtime){
//            $starttime = date('Y-m-d H:i:s',$starttime);
//            $endtime   = date('Y-m-d H:i:s',$endtime);
//            $where['r.paytime'] = array('between',array($starttime,$endtime));
//        }else if(!$starttime && $endtime){
//            $endtime = date('Y-m-d H:i:s',$endtime);
//            $where['r.paytime'] = array('elt',$endtime);
//        }else if($starttime && !$endtime){
//            $starttime = date('Y-m-d H:i:s',$starttime);
//            $where['r.paytime'] = array('egt',$starttime);
//        }
//
//        $where['r.pay_state'] = array('eq',200); //成功充值
//
//
//        $list = $this->client->exportse($where);
//
//        foreach ($list as $key => $val){
//            $dataResult[$key]['id'] = $val['id'];
//
//            $dataResult[$key]['username'] = $val['username'];
//            $dataResult[$key]['order_no'] = $val['order_no'];
//            $dataResult[$key]['payno'] = $val['payno'];
//            $dataResult[$key]['ordertime'] = $val['ordertime'];
//            $dataResult[$key]['paytime'] = $val['paytime'];
//            $dataResult[$key]['paykind'] = C('Pay_Kind')[$val['paykind']];
//            $dataResult[$key]['amount_usa'] = $val['amount_usa'];
//            $dataResult[$key]['user_balance_usa'] = $val['user_balance_usa'];
//            $dataResult[$key]['operator'] = $val['operator'];
//
//        }
//
//        //$dataResult = $list;      //todo:导出数据（自行设置）
//        //$headTitle = "XX保险公司 优惠券赠送记录";
//        $title = "会员充值记录-". date('Y-m-d', time());
//        $headtitle= "<tr style='height:50px;border-style:none;><th border=\"0\" style='height:60px;width:270px;font-size:22px;' colspan='11' >{$headTitle}</th></tr>";
//        $titlename = "<tr>
//               <th style='width:100px;' align='center'>序号</th>
//               <th style='width:100px;' align='center'>会员</th>
//               <th style='width:200px;' align='center'>内部订单号</th>
//               <th style='width:200px;' align='center'>支付单号</th>
//               <th style='width:200px;' align='center'>创建时间</th>
//               <th style='width:200px;' align='center'>支付时间</th>
//               <th style='width:100px;' align='center'>支付方式</th>
//               <th style='width:100px;' align='center'>充值金额($)</th>
//               <th style='width:100px;' align='center'>账户余额($)</th>
//               <th style='width:100px;' align='center'>操作员</th>
//           </tr>";
//        $filename = $title.".xls";
//        $this->excelData($dataResult,$titlename,$headtitle,$filename);
//
//
//    }




    /*
    *处理Excel导出
    *@param $datas array 设置表格数据
    *@param $titlename string 设置head
    *@param $title string 设置表头
    */
    public function excelData($datas,$titlename,$title,$filename){
        $str = "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\"\r\nxmlns:x=\"urn:schemas-microsoft-com:office:excel\"\r\nxmlns=\"http://www.w3.org/TR/REC-html40\">\r\n<head>\r\n<meta http-equiv=Content-Type content=\"text/html; charset=utf-8\">\r\n</head>\r\n<body>";
        $str .="<table border=1><head>".$titlename."</head>";
        //$str .= $title;
        foreach ($datas  as $key=> $rt )
        {
            $str .= "<tr>";
            foreach ( $rt as $k => $v )
            {
                $str .= "<td  align='center'>{$v}</td>";
            }
            $str .= "</tr>\n";
        }
        $str .= "</table></body></html>";
        header( "Content-Type: application/vnd.ms-excel; name='excel'" );
        header( "Content-type: application/octet-stream" );
        header( "Content-Disposition: attachment; filename=".$filename );
        header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
        header( "Pragma: no-cache" );
        header( "Expires: 0" );
        exit( $str );
    }





}