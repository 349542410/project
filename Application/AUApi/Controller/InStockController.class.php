<?php
/*
  它方ERP系统确认发货，上传资料至我系统，并向它返回单号。打印面单
  对应确认发货JSON.txt
  151210 出现申通单号为-6，即保存不了uuid情况，作申通号有问题时返回出错
  160111 为避免无法返回物流信息至ERP增加检查CID,当天未更新
  160616 添加重庆EMS取单号功能
  170413 福州BC
  170707 湛江EMS
*/
namespace AUApi\Controller;
use Think\Controller\RestController;
Class InStockController extends RestController
{
//    protected $xmlsave = UPFILEBASE.'/Upfile/InStock_logs/';
    /**
     * 接受并回传确认发货信息
     * @param [type] [varname] [description]
     * @return [type] [description]
     */
    public function ttt()
    {
        $sfinfo = C('SF.info');
        //print_r($sfinfo);die();
        $res = array(
            'Order' => array(
                'orderid' => 'MK881000260US',  //客户订单号
                /*
                'j_company'     => '美购商城',
                'j_contact'     => '郑先生',
                'j_tel'         => '13920064421',
                'j_mobile'      => '13899524124',
                'j_country'     => '美国',
                'j_province'    => '加州',
                'j_city'        => '三番市',
                'j_county'      => '三番市',
                'j_address'     => '天安科技园',
                */
                'd_contact' => '何先生',
                'd_tel' => '13800138001',
                'd_mobile' => '13800138000',
                'd_country' => '中国',
                'd_province' => '广东省',
                'd_city' => '广州市',
                'd_county' => '',
                'd_address' => '番禺区大北路9号',
                'custid' => '',  //顺丰月结卡号
                'pay_method' => 1,   //付款方式： 1:寄方付 2:收方付 3:第三方付
                'parcel_quantity' => 1,   //包裹数量
                'express_type' => 1,   //业务类型 1.标准快递   2.顺丰特惠   3.电商特惠  7.电商速配
            )
        );
        //array_push($res['Order'], $sfinfo);
        $res['Order'] += $sfinfo;
        echo '<pre>';
        print_r($res);
        die();
        $Mkno = new \Org\MK\Tracking();
        //$data['MKNO'] = $Mkno->run();
        $sfkd = 'OrderService';
        $str = $Mkno->sfno($sfkd, $res);
        echo '<pre>';
        print_r($str);
    }

    //自定义log文件
    private function logger($txt){
        $dir = 'Application/Runtime/Logs/AUApi/';
        $log = date("y") . '_' . date("m") . '_' . date("d") . '_' ."read.log";
        if(!is_dir($dir)){
            mkdir($dir, 0777);
        }
        if(!file_exists($dir . $log)){
            fopen($dir . $log, "w") or die("Unable to open file!");;
        }
        $logUrl = $dir . $log;
        $result = file_put_contents($logUrl,"\n" . date("Y-m-d H:i:s") . "============================" . $txt, FILE_APPEND);
        return $result;
    }

    Public function read()
    {
        $jn = new \Org\MK\JSON;

        // log
//        if(!is_dir($xmlsave)) mkdir($xmlsave, 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹
//        $file_name = 'InStock'.date('Ymd').'.txt';  //文件名
//        $content = "=================== ".date('Y-m-d H:i:s')." ===================\r\n\r\n-------- Request --------\r\n\r\n".$jn."\r\n\r\n";
//        if(is_file($file_name)){
//            file_put_contents($xmlsave.$file_name, $content);
//        }else{
//            file_put_contents($xmlsave.$file_name, $content, FILE_APPEND);
//        }

        $js = $jn->get();
        $this->logger('请求接口开始' . json_encode($js, 320));
        if (!is_array($js)) {
            echo $jn->respons("", "", null, 0, L("SYSERROR0"));
            exit;
        }
        //判断使用哪个数据表
        if ($js['KD'] == 'toMKIL') {
            $table = "tran_list";
        } else {
            echo $jn->respons("", "", '', 3, L("SYSERROR3"));
            exit;
        }
        $me = $js['toMKIL'];
        for ($i = 0; $i < count($me); $i++) {   //NO.1 FOR
            $data = array();//Man160112
            if (is_array($me[$i])) {
                foreach ($me[$i] as $key => $value) {
                    $data[$key] = $value;
                }
            }
            //160111 为避免无法返回物流信息至ERP增加检查CID
            $_cid = isset($data['CID']) ? $data['CID'] : 0;
            $_cid *= 1;
            if ($_cid < 1) {
                echo $jn->respons("", "", '', 3, '列表内容中CID不正常:' . $_cid);
                exit;
            }

            $isSTO = (trim($me[$i]['TranKd']) * 1);

            // 记录日志
            if(!is_dir(UPFILEBASE.'/Upfile/Order_logs/')) mkdir(UPFILEBASE.'/Upfile/Order_logs/', 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹
            $content = "===================== ".date('Y-m-d H:i:s')." =====================\r\n\r\n-------- InStock接收到的Order资料 --------\r\n\r\n".json_encode($data['Order'])."\r\n\r\n";

            $file_name = 'Order_'.date('Ymd').'.txt';   //文件名

            file_put_contents(UPFILEBASE.'/Upfile/Order_logs/'.$file_name, $content, FILE_APPEND);
            // 记录日志

            if (!isset($data['Order'])) {
                echo $jn->respons("", "", '', 3, L("SYSERROR3_NOORDER"));
                exit;
            }
            $order = array_pop($data);//取出订单细节(货品详细描述)，mk_tran_order

            //160106Man 返回线路名称
            $tcrs = M('TransitCenter')->field('name,cid')->where(array('id' => $isSTO))->find();

            if (!$tcrs) {
                echo $jn->respons("", "", '', 3, L("Line_Stop"));
                exit;
            }
            $lname = $tcrs['name'];
            //身份证号粗略处理
            $sfneed = $tcrs['cid'] == 1;
            if ($sfneed) {
                $sfid = isset($data['sfid']) ? $data['sfid'] : '';
                $sfidl = strlen($sfid);
                //不能为空，但可为8，为8时表示由客人自己填写或身份证有误时,揽收短信要说明由客人补录
                if ($sfid == '' || ($sfidl <> 18 && $sfid <> '8')) {
                    echo $jn->respons("", "", '', 3, L("Need_sfID"));
                    exit;
                }
            } else {
                //方便发短信时判断
                $data['sfid'] = '';
            }
            //160106

            M()->startTrans();//开启事务
            $form = M($table);

            $mk['auto_Indent1'] = $data['auto_Indent1'];
            $mk['auto_Indent2'] = $data['auto_Indent2'];
            $orderNum = $form->where($mk)->find();
            $this->logger('查询是否有订单物流数据' . json_encode($orderNum, 320));
            if (is_array($orderNum)) {
                $ls[$i] = L('auto_Indent') . $data['auto_Indent1'] . L('and') . $data['auto_Indent1'] . L('existed');
                $suc[$i] = "false";
                $data['MKNO'] = $orderNum['MKNO'];

                //20150429增加返回申通单号Man
                //20151212改为无论何时都返回
                $data['STNO'] = $orderNum['STNO'];
                //20151214增加 快递补充资料，如顺丰除单号外还有其它资料
                $STEXT = trim($orderNum['STEXT']);

                /*//单独 获取热敏格式面单（推荐） PDF
                $PDF = '';
                if($isSTO == 21){
                    $Kdno = new \AUApi\Controller\KdnoConfig\Kdno21();
                    $PDF = $Kdno->get_labels($data['STNO']);
                }*/
                //160907直接返回base64
                //$STEXT  = ($STEXT<>'')?(json_decode(base64_decode($STEXT),true)):'';

                //160112，如果是顺丰 则返回顺丰资料
                if ($orderNum['TranKd'] == 2) {
                    $sfinfo = C('SF.info');
                }

            } else {
                $fetch = $form->data($data)->add();
                if ($fetch > 0) {
                    //生成并插入美快面单号
                    //$config = array('numlen'=>6,'PREFIX1'=>'MK','PREFIX2'=>'8','PREFIX3'=>'1'); //这是默认设置。
                    //$Mkno = new \Org\MK\Tracking($config);
                    $Mkno = new \Org\MK\Tracking();
                    $data['MKNO'] = $Mkno->run();

                    //20150428增加申通单号读取功能Man
                    $data['STNO'] = '';
                    //if($isSTO){ //20151212改为直接使用TranKd
                    $TOA = array(
                        1,
                        2,
                        5,
                        6,
                        9,
                        10,
                        11,
                        12,
                        13,
                        14,
                        15,
                        16,
                        17,
                        18,
                        19,
                        20,
                        21,
                        22,
                        23
                    ); //1为香港申通 2为天津顺丰 5为美国顺丰 6香港e特快 9厦门CC 10福建BC 11湛江EMS 12美国顺丰CC
                    if (in_array($isSTO, $TOA)) {
                        switch ($isSTO) {
                            case 1: //申通
                                $data['STNO'] = $Mkno->stno($data['MKNO']); //因申通系统问题，171025暂改为原来方式
                                //申通从171024开始改为先出单，导入申通系统，生成申通单号。再打单模式 Man
                                //$data['STNO'] = $data['MKNO'];
                                break;
/*                            case 11://线路11 ETK 的第二种面单模式不适合此判断
                                //170828改为先返回MK单号，打印MK格式的面单，有转单号时，再导入并回调ERP
                                $data['STNO'] = $data['MKNO'];//$Mkno->getexno($data['MKNO'],'zjnolist','STNO');
                                break;*/
                            case 2: //顺丰 20151214
                                $sfinfo = C('SF.info');
                                $sfdata = array(
                                    'Order' => array(
                                        'orderid' => $data['MKNO'],   // 客户订单号
                                        'd_contact' => $data['receiver'],
                                        'd_tel' => $data['reTel'],
                                        'd_mobile' => $data['reTel'],
                                        'd_country' => $data['中国'],
                                        'd_province' => $data['province'],
                                        'd_city' => $data['city'],
                                        'd_county' => $data['town'],
                                        'd_address' => $data['reAddr'],
                                        'pay_method' => 1,   //付款方式： 1:寄方付 2:收方付 3:第三方付
                                        'parcel_quantity' => 1,   //包裹数量
                                        'express_type' => 3,   //业务类型 1.标准快递   2.顺丰特惠   3.电商特惠  7.电商速配
                                    )
                                );
                                $sfdata['Order'] += $sfinfo;
                                $sf = $Mkno->sfno('OrderService', $sfdata);
                                $data['STNO'] = isset($sf['mailno']) ? $sf['mailno'] : '';
                                //160112说明：如果STNO为空，则证明返回顺丰资料有问题,因有时出现destcode为空但有单号的情况
                                $STEXT = isset($sf['mailno']) ? $sf : '';
                                //转为base64保存到数据库 20151214
                                $data['STEXT'] = isset($sf['mailno']) ? (base64_encode(json_encode($sf))) : '';
                                break;
                            //160617添加重庆EMS功能
                            case 4:
                                $data['STNO'] = $Mkno->emscqno($data['MKNO']);
                                break;
                            //福建与厦门取邮政号是一样的
                            case 9:
                            case 10:
                                $data['STNO'] = $Mkno->getexno($data['MKNO'], 'emsfjnolist', 'POSTNO');
                                break;
                            case 13://优选3肇庆邮政BC
                                $data['STNO'] = $Mkno->getexno($data['MKNO'], 'ems13nolist', 'POSTNO');
                                break;
                            case 14://优选3湛江邮政CC
                            case 16://优选3湛江邮政CC食品线
                                $data['STNO'] = $Mkno->getexno($data['MKNO'], 'ems14nolist', 'POSTNO');
                                break;
                            case 18://优选3西安邮政
                                $data['STNO'] = $Mkno->getexno($data['MKNO'], 'ems18nolist', 'POSTNO');
                                break;
                            case 23://EMS
                                $logisticsTransit = M('logistics_transit')->where(['transit_center_id' => 23,'status' => 0])->field('id,logistics_id')->find();
                                $no = M('logistics_no')->where(['id' => $logisticsTransit['logistics_id']])->field('id,no')->find();
                                $data['STNO'] = $no['no'];
                                //修改绑定线路
                                if(strlen($data['STNO']) > 6 && !empty($logisticsTransit['id']) && !empty($no['id']) && !empty($data['MKNO'])){
                                    M('logistics_transit')->where(['id' => $logisticsTransit['id']])->data(['status' => 20])->save();
                                    M('logistics_no')->where(['id' => $no['id']])->data(['status' => 20,'MKNO' => $data['MKNO'], 'use_time' => date("Y-m-d H:i:s")])->save();
                                }
                                break;
                            case 15: //优选3肇庆韵达BC
                                $data['STNO'] = $data['MKNO'];
                                break;
                            default:
                                //生成postdata.因$data不含货品内容
                                $postdata = $me[$i];
                                $postdata['MKNO'] = $data['MKNO'];
                                //require_once(C('KDNOPATH') . '/Kdno' . $isSTO . '.class.php');
                                //$Kdno = new \Kdno();
                                $this->logger('调用第三方线路开始' . $isSTO);
                                $mothod = '\AUApi\Controller\KdnoConfig\Kdno'.$isSTO;
                                $Kdno = new $mothod();
                                $postResult = $Kdno->data($postdata);
                                $this->logger('返回第三方线路结束' . json_encode($postResult, 320));
                                // 160907改为加密后返回  //返回快递其它内容，未64l加密，保存时使用$data['STEXT'],
                                $STEXT = $Kdno->get();
                                $data['STNO'] = $Kdno->no();    // 返回快递号码
                                $data['STEXT'] = $STEXT; //($STEXT<>'')?(json_decode(base64_decode($STEXT),true)):'';
                                $this->logger('返回快递号码' . json_encode($data, 320));
                                break;
                        }

                        //20151210增加STNO号如果小于5位，则返回错误
                        if (strlen($data['STNO']) < 6) {
                            //EXIT; 20151210改为以下内容
                            $error = '获取快递单号有错，请重新扫描!!' . $data['STNO'];
                            if (isset($data['STEXT'])) {
                                $errorstr = base64_decode($data['STEXT']);
                                $errora = json_decode($errorstr, true);
                                if (is_array($errora)) {
                                    $error = (isset($errora['ErrorStr'])) ? $errora['ErrorStr'] : $error;
                                }
                            }
                            $ls[$i] = $error . '，';// '获取快递单号有错，请重新扫描!!'.$data['STNO'];
                            $fetch = -1;
                        }
                    }
                    //Man 20141215 判断返回的单号是否正常,正常则需返回错误提示，并事务回滚
                    if (strlen($data['MKNO']) < 6) {
                        //M()->rollback();//事务回滚
                        //EXIT; 20151210改为以下内容
                        $ls[$i] = '获取' . ($fetch == -1 ? '快递单号和' : '') . '物流号有错，请重新扫描!!或线路未设置';
                        $fetch = -1;
                    }
                    //更新美快与 （申通单号,20150429）
                    if ($fetch > 0) { //20151210防止取快递号与美快号有误，加这个
                        $con['id'] = $fetch;

                        //160112
                        $setData = array('MKNO' => $data['MKNO'], 'STNO' => $data['STNO']);
                        if (isset($data['STEXT'])) {
                            $bstr = base64_decode($data['STEXT']);
                            $barr = json_decode($bstr, true);
                            $stext = json_decode(base64_decode($data['STEXT']),true);

                            //保存对接的物流方，返回的第三方运单号，例如卓志，有自己的运单号和中通号
                            if(isset($barr['traceCode'])){
                                $setData['traceCode'] = $barr['traceCode'];
                            }

                            //过滤PDF打印码，不保存到数据库
                            $PDF = (isset($barr['PDF'])) ? $barr['PDF'] : '';//面单打印码
                            unset($barr['PDF']);//面单打印码长度过长，过滤，不保存到数据库
                            $setData['STEXT'] = base64_encode((json_encode($barr)));;//$data['STEXT'];
                        }
                        $update = $form->where($con)->setField($setData);
                        //将json."Order"内容保存到mk_tran_order中
                        for ($j = 0; $j < count($order); $j++) {
                            $order[$j]['lid'] = $fetch;
                            $addOrder = M('tran_order')->data($order[$j])->add();
                        }
                    }
                }
                if (($fetch > 0) && ($update > 0) && ($addOrder > 0)) {
                    M()->commit();//事务提交
                    $suc[$i] = "true";
                    $ls[$i] = L('succeed');
                } else {
                    M()->rollback();//事务回滚
                    $suc[$i] = "false";
                    $ls[$i] .= L('insert_error');

                    //man20141215 清空两个号码，
                    $data['MKNO'] = '';
                    $data['STNO'] = '';
                }
            }

            //回传的单据信息
            $back[$i] = Array(
                "auto_Indent1" => $data['auto_Indent1'],
                "auto_Indent2" => $data['auto_Indent2'],
                "MKNO" => $data['MKNO'],
                "STNO" => $data['STNO'],
                'LineName' => $lname,       //Man 160106线路名称
                "STEXT" => isset($STEXT) ? $STEXT : '',
                'sfinfo' => isset($sfinfo) ? $sfinfo : '',
                'PDF' => isset($PDF) ? $PDF : '',//面单打印码
                'jdate' => date('Y-m-d'), //Man160107 寄件日期
                "Success" => $suc[$i],
                "LOGSTR" => $ls[$i],
                'CID' => $data['CID'], //160111返回时带上列表中的CID (注意：不是软件使用者CID)
                "traceCode" => $setData['traceCode'],//物流方返回的第三方运单号，例如卓志，有自己的运单号和中通号
            );
            $this->logger('回传的单据信息' . json_encode($back, 320));
        }
        \Think\Log::write('dev返回MKNO单号' . json_encode($jn->respons($js['KD'], $js['CID'], $back), 320));
        echo $jn->respons($js['KD'], $js['CID'], $back);
        exit();
        //$json_back=json_encode($sendBack);
    }//END OF FUNCTION READ
}