<?php
/**
 * 单号导入	
 */
namespace WebUser\Controller;
use Think\Controller;
class ImportController extends BaseController{

    public function _initialize() {
        parent::_initialize();

        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/WebImport');      //读取、查询操作
        $this->client = $client;
    }

    /**
     * 导入视图
     * @return [type] [description]
     */
	public function index(){

		$user_type = $this->user_type;
        if($user_type == 'person'){
            $this->display('Public/404');
            exit;
        }    
		$this->display();
	}

	/**
	 * 导入方法
	 * @return [type] [description]
	 */
	public function import_csv(){
    	G('begin');
		$filename = $_FILES;
		
		//上线时使用这个加载类
		import('Vendor.MKILExcel.MkilImportMarket');

		// 测试用以下方式加载
		// require_once "MkilImportMarket.class.php";
		//End

    	$importexcel = new \MkilImportMarket($filename);
    	$importexcel->Model_Type = '2';	//用于判断是否从文档的第一行开始读取，1表示默认是;另一个作用是，如果为1的时候，读取文档的所有单元格，即使该单元格为空

		if($importexcel->import()) $arr = $importexcel->import();
		//如果返回的数组是有status字段信息的,则$arr为错误信息,不是处理好的数据数组
		if(isset($arr['status'])){
			$this->ajaxReturn($arr);exit;
		}
		// dump($arr);

/*    	$client = $this->client;
    	$result = $client->_import_csv($arr);*/
    	$result = $this->_import_csv($arr);
    	G('end');
    	// $result['msg'] .= '耗时：'.G('begin','end').'s';	//耗时时间显示
    	$this->ajaxReturn($result);
	}

//====================================== 以下的无用  ========================================================

	/**
	 * 导入CSV
	 * @return [type] [description]
	 */
    public function _import_csv($arr){
		ini_set('max_execution_time', 0);
		ini_set('memory_limit','4088M');

		$data_values = array();	//合法数据
		$not_belong  = array();	//被筛选出来的不符合规则的数据组

		$fid = 0;	//用作判断
		$dd  = 0;	//用作判断
		//计算每个运单号的小计总数和小计金额
		foreach($arr as $key=>$item){
				
			$dd  = $item[0] == '' ? $dd : 0;
			$fid = $item[0] == '' ? $fid : $item[0];

            if($dd == 0){
            	$data_values[$key]['lit_amount'] = $item[22];	//小计总数
				$data_values[$key]['lit_count'] = $item[23];	//小计总价
				
				$data_values[$key][] = $arr[$key];	//装入新的数组中，形成三维数组
                $dd++;
            }else{
				$data_values[$key-$dd]['lit_amount'] += $item[22];
				if($item[23] == ''){
					$data_values[$key-$dd]['lit_count'] += $arr[$key-$dd][23];
					$arr[$key][23] = $arr[$key-$dd][23];	//补填单价
				}else{
					$data_values[$key-$dd]['lit_count'] += $item[23];
				}
				$data_values[$key-$dd][] = $arr[$key];	//装入新的数组中，形成三维数组
                $dd++;
            }

		}

		$m = 0;	//用作判断
		$n = 0;	//用作判断
		$g = 0;	//记录保存成功的数量
		$h = 0;	//记录保存失败的数量
		$fails  = array();	// 记录保存失败的序号
		$repeat = array();	// 记录重复的序号
		$before_count = count($data_values);	//执行以下规则之前的总数

		// 单号保存
		foreach($data_values as $kk=>$val){

			$map = array();
			$map['auto_Indent1'] = array('eq',$val[0][0]);
			$map['auto_Indent2'] = array('eq',$val[0][1]);

			//检查是否已经存在此单号
			$check[$kk] = M('TranList')->where($map)->find();

			if(!$check[$kk]){	//如果没有，则保存
				// echo '不存在<br>';

				//当身份证不为空的时候，进行身份证号码的验证
		    	if($val[0][14] != ''){
		    		$res = $this->certificate($val[0][14]);
		    	}
		    	// 邮政编码校验
		    	$Zipcode = $this->checkZipcode($val[0][12]);

		    	// 如果身份证号码验证 或者 邮政编码验证 不通过的时候，则筛选出不符合条件的数组
		    	if($res === false || $Zipcode === false){
		    		$not_belong[] = $kk;
		    		unset($data_values[$kk]);	//排除不符合的数组
		    	}else{
		    		// dump(count($val));
		    		for ($i=0; $i < (count($val)-2); $i++) {

		    			// 判断$m是否等于$n，如果等于则取$n的值，如果不等于则取$m的值($m的值会转变为当mk_tran_list保存数据成功后返回的id值)
		    			$m = $m == $n ? $n : $m;

						if($i == 0){
							// mk_tran_list
							$saveList['weight']        = $val[$i][2];
							$saveList['sender']        = $val[$i][3];
							$saveList['sendAddr']      = $val[$i][4];
							$saveList['sendTel']       = $val[$i][6];
							$saveList['sendcode']      = $val[$i][5];
							$saveList['auto_Indent1']  = $val[$i][0];
							$saveList['auto_Indent2']  = $val[$i][1];
							$saveList['receiver']      = $val[$i][7];
							$saveList['reAddr']        = $val[$i][8];
							$saveList['province']      = $val[$i][9];
							$saveList['city']          = $val[$i][10];
							$saveList['town']          = $val[$i][11];
							$saveList['postcode']      = $val[$i][12];
							$saveList['reTel']         = $val[$i][13];
							$saveList['notes']         = $val[$i][15];
							$saveList['premium']       = $val[$i][16];
							$saveList['sfid']          = $val[$i][14];
							$saveList['email']         = $val[$i][17];
							$saveList['category1st']   = $val[$i][18];
							$saveList['category2nd']   = $val[$i][19];
							$saveList['category3rd']   = $val[$i][20];
							$saveList['custom_code']   = $val[$i][21];
							$saveList['number']        = $val['lit_amount'];	//
							$saveList['price']         = $val['lit_count'];	//
							$saveList['specification'] = $val[$i][24];


							// mk_tran_order
							$saveOrder['detail']       = $val[$i][24];
							$saveOrder['number']       = $val[$i][22];
							$saveOrder['price']        = $val[$i][23];
							$saveOrder['weight']       = $val[$i][2];
							$saveOrder['coin']         = '2';
							$saveOrder['remark']       = $val[$i][14];
							$saveOrder['auto_Indent1'] = $val[$i][0];
							$saveOrder['auto_Indent2'] = $val[$i][1];
							// dump($saveList);
							// dump($saveOrder);

							$sList[$i] = M('TranList')->add($saveList);
							//如果mk_tran_list保存数据成功
							if($sList[$i]){
								$saveOrder['lid']       = $sList[$i];
								$sOrder[$i] = M('TranOrder')->add($saveOrder);
								$m = $sList[$i];	// $m的值被转化为数据库储存成功后的id值
								$g++;
							}else{	//如果保存失败
								$h++;	//如果保存失败则自增+1
								$fails[] = $kk;
								exit;
							}

						}else{
							// mk_tran_order
							$saveOrder['detail']       = $val[$i][24];
							$saveOrder['number']       = $val[$i][22];
							$saveOrder['price']        = $val[$i][23];
							$saveOrder['weight']       = $val[$i][2];
							$saveOrder['coin']         = '2';
							$saveOrder['remark']       = $val[0][15];
							$saveOrder['auto_Indent1'] = $val[0][0];
							$saveOrder['auto_Indent2'] = $val[0][1];
							// dump($saveOrder);

							//$m 此时如果不等于0，即等于mk_tran_list保存成功后返回的id值，即mk_tran_order.lid
							if($m != 0){
								$saveOrder['lid']       = $m;
								$sOrder[$i] = M('TranOrder')->add($saveOrder);
							}else{
								exit;
							}	
						}

		    		}

		    	}


			}else{	//检查重复，如果已经存在此单号，则不执行任何保存
				// echo '存在<br>';
				$repeat[] = $kk;

			}

		}

		$str_list   = implode($not_belong,'、');	//不符合的序号
		$str_fails  = implode($fails,'、');	//保存失败的序号
		$str_repeat = implode($repeat,'、');	//重复的序号
		// dump($str_list);
		// dump($data_values);
		$msg = '成功：'.$g.'个；失败：'.$h.'个；不符合：'.count($not_belong).'个；重复：'.count($repeat).'个；';//<br/>不符合的序号为：'.$str_list.'；<br/>重复的序号为：'.$str_repeat.'；';
		/*if(count($fails) > 0) $msg .= '；<br/>保存失败的序号为：'.$str_fails;*/
		//保存的数量 > 0，则成功
		if($g > 0){
			$backArr = array('status'=>'1', 'msg'=>'导入成功，'.$msg, 'not_belong'=>$not_belong, 'fails'=>$fails);
			return $backArr;
		}else{
			$backArr = array('status'=>'0', 'msg'=>'导入失败，'.$msg);
			return $backArr;
		}
    }

    /**
     * 邮政编码的校验
     * @param  [type] $code [description]
     * @return [type]       [description]
     */
	public function checkZipcode($code){
		//去掉多余的分隔符
		$code = preg_replace("/[\. -]/", "", $code);
		//包含一个6位的邮政编码 包含0开头的
		if(preg_match("/^[0-9]{6}$/", $code)){
			return true;
		}else{
			return false;
		}
	}

    /**
     * 身份证号码验证
     * @param  [type] $CID [身份证号码]
     * @return [type]      [description]
     */
    function certificate($CID){
        $reg = "/^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$/";

        //验证格式
        if(preg_match($reg, $CID) != true){
            return false;
        }

        $date    = substr($CID, 6, 8);  //获取身份证中的年月日
        $nowdate = date('Ymd'); //当前实际的年月日
        
        //如果身份证的年月日不超过当前实际日期
        if(intval($date) <= intval($nowdate)){
            $year  = substr($CID, 6, 4);
            $month = substr($CID, 10, 2);
            $day   = substr($CID, 12, 2);

            //如果身份证的月份大于实际月份,则报错
            if(intval($month) > 12){
                return false;
            }else{
                
                //判断身份证里面的月份是否属于润年
                $mday = $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);

                //如果身份证的 天数 大于 身份证的年月实际该有的天数,则报错
                if(intval($day) > intval($mday)){
                    return false;
                }else{
                    return true;
                }
            }
            
        }else{
            return false;
        }
        
    }
}