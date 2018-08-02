<?php
/**
 * 管理员管理 客户端
 */
namespace Admin\Controller;
use Think\Controller;
class TestController extends Controller{

	protected $checkword = 'CANTA5Pxqk';
	protected $pmsLoginAction = 'http://52.53.199.43:8090/api';

	public function demo(){
		$xml = trim($_POST['xml']);

		$up_to_low = strtolower($xml);//通知内容（xml/json）全部转化为小写
		$with_key = $up_to_low.$this->checkword;//加密钥：上一步得到的字符串追加密钥
		$with_md5 = md5($with_key);//将上一步得到的字符串进行MD5

		$sendData = array();
		$sendData['content']     = $xml;//要发送的XML内容
		$sendData['cryptograph'] = $with_md5;//数据验证密文
		$sendData['partnerName'] = 'meijie';//合作商名称
		$sendData['version']     = '1.0';//API版本号
		$sendData['messageType'] = 'SubmitTracking';//发送的消息类型
		$sendData['format']      = 'xml';//要发送内容的数据格式，目前支持xml，默认值为xml

		// var_dump($xml);die;

		$MK = new \Org\MK\HTTP();
		$result = $MK->post($this->pmsLoginAction, $sendData);

		// 输出txt文件 
		$xmlsave = (defined('API_ABS_FILE')) ? API_ABS_FILE.'/kdno17/' : '';

		$file_name = 'Kdno17_ZT_'.time().'.txt';	//文件名

		$content = "======== Request =========\r\n\r\n".$xml."\r\n\r\n======== Response =========\r\n\r\n".$result;

		if(!is_dir($xmlsave)) mkdir($xmlsave, 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

		file_put_contents($xmlsave.$file_name, $content);

		dump($result);
	}

	public function index(){
		$this->display();
	}

	public function mkno(){
		$this->display();
	}

	public function getInfo(){
		$mkno = trim(I('mkno'));
		vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Test');

        $res = $client->postInfo($mkno);
        dump($res);
	}

//===================== 无用 ==================
	//测试通过，可以使用此方法发送邮件
	public function test(){
    	echo 'megao-'.$type.'-'.$this->create_guid();
	}

    /**
     * 标识码生成方法
     * @return [type] [description]
     */
    public function create_guid(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = //chr(123)// "{"
                    substr($charid, 0, 8).$hyphen
                    .substr($charid, 8, 4).$hyphen
                    .substr($charid,12, 4).$hyphen
                    .substr($charid,16, 4).$hyphen
                    .substr($charid,20,12);
                    //.chr(125);// "}"
            return $uuid;
        }
    }

    //保留一位小数，小数点第二位直接去掉
    private function num_to_change($n){
        $num = floatval($n) * 10;
        $arr = explode('.',$num);
        return $arr;
        if($arr[0] > 0){
            return sprintf("%.1f", floatval($arr[0])/10);
        }else{
            return '0.1';
        }
    }

	// // public function index(){
	// // 	$filename = dirname(__FILE__);
	// // 	echo $filename;
	// // }
	// public function index(){
	// 	$arr = array();
	// 	for($i = 1;$i<=33;$i++){
	// 	    $arr['key'.$i] = 'value'.$i;
	// 	}

	// 	// $arr2 = array_splice($arr, round(count($arr)/2)); //四舍五入，取约一半的数据


	// 	// // $arr2 = array_splice($arr, 15 , 1); //从第五个开始,取十个

	// 	// dump($arr);   //原数组
	// 	// dump($arr2); //新数组

	// 	$arr2 = array_chunk($arr, 15);
	// 	dump($arr2); //新数组
		
	// 	// $this->save($arr2[**]);
		
	// }

	// public function save(){
	// 	return true;
	// }

    function MKBc2_code($code='S01'){
    	//业务错误信息
    	$arr1 = array(
    		'B99' => '非法的物流订单号','B01' => '不能进行操作，当前状态是：等待确认','B02' => '不能进行操作，当前状态是：接单','B03' => '不能进行操作，当前状态是：不接单','B04' => '不能进行操作，当前状态是：揽收成功','B05' => '不能进行操作，当前状态是：揽收失败','B06' => '不能进行操作，当前状态是：签收成功','B07' => '不能进行操作，当前状态是：签收失败','B08' => '不能进行操作，当前状态是：订单已取消','B09' => '不能进行操作，运单号为空','B10' => '不能进行操作，签收信息为空(包括运单号、签收姓名、签收时间不能为空)',
    	);

    	//系统错误信息
    	$arr2 = array(
    		'S01' => '非法的XML格式','S02' => '非法的数字签名','S03' => '非法的物流公司','S04' => '非法的通知类型','S05' => '非法的通知内空','S07' => '系统异常，请重试','S08' => '非法的电商平台',
    	);
    	
    	$arr = array_merge($arr1 , $arr2);
    	dump($arr);
    	echo ($arr[$code] != '') ? $arr[$code] : '未知错误';

    }

    public function demo2(){
        $newJSON = new \Org\MK\JSON;
        dump($newJSON);
    }

    public function demo3(){
        $no = I('get.no');
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Test');

        $res = $client->_demo3($no);
        dump($res);
    }

    public function demo4(){
        $no = I('get.no');
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Test');

        $res = $client->_demo4($no);
        dump($res);    
    }

    public function demo5(){
        $no = I('get.no');
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Test');

        $res = $client->_demo5($no);
        dump($res);    
    }

    public function demo6(){
        $no = I('get.no');
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Test');

        $res = $client->_demo6($no);
        dump($res);    
    }

    public function demo7(){
        $no = I('get.no');
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/Test');

        $res = $client->_demo7($no);
        dump($res);    
    }

    // 根据运单号 (审单)推送节点 给中通
    public function toPush(){
    	$arr = array(
    		'STNO'       => '120256083174',
    		'push_state' => 'Verified',
    		'airno'      => '',
    		'data'       => array('MKNO'=>'MK883031221US','STNO'=>'120256083174'),
    	);
    	require_once('D:\wwwroot\tp323\app83\Api\Controller/Kdno17.class.php');
    	$Kdno = new \Kdno();
    	dump($Kdno->SubmitTracking($arr)) ;
    }

    public function duck(){
        phpinfo();
    }

    public function tr(){

        $arr = array(
                'data'=>array(
                    'uname' => 'a123456',//账户名
                    'ucode' => 'e10adc3949ba59abbe56e057f20f883e', //用户密码md5加密
                    'key' => '5b93c650e7c718cfcc70ae2132ec249a',//密匙，生成方式见下方
                ),
                'type'=>'login',
            );
        $data['info'] = base64_encode(urlencode(json_encode($arr)));
        $url = 'http://mkauto.loc.mk:83/PrintAndReceive/console?l=zh-cn';
        
        echo $this->posturl($url, $data);
    }

    /**
     * curl函数发送数据到ERP
     * @param  [type] $url       [description]
     * @param  [type] $post_data [description]
     * @return [type]            [description]
     */
    function posturl($url,$post_data){
        //通过curl函数发送
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        //当CURLOPT_RETURNTRANSFER设置为1时，如果成功只将结果返回，不自动输出返回的内容。如果失败返回FALSE；
        //若不使用这个选项：如果成功只返回TRUE，自动输出返回的内容。如果失败返回FALSE
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function dd(){
        M('SelfTerminalList')->where(array('computer_name'=>'DEVERP3','type'=>'receive'))->find();
        echo M()->getLastSql();
    }

    public function dd5(){
        $list = M('tran_uorder')->where(array('lid'=>'1030'))->select();
        dump($list);
    }

    public function dd2(){
        $TaxCount = new \Libm\TaxCount\TaxCountController;
        $no = 'MQ41349256012 ';
        $res = $TaxCount->caltax($no);
        dump($res);
    }

    public function dd3(){
        $TaxCount = new \Libm\TaxCount\TaxCountController;

        $arr = array(
            array(
                'uuid'=>'1',//调用者生成的一个唯一标识码，用于返回数据时区分显示位置 
                'cid'=>'37',//第二类别id
                'number'=>'5',//数量
                'price'=>'5',//单价
            ),
            array(
                'uuid'=>'2',//调用者生成的一个唯一标识码，用于返回数据时区分显示位置 
                'cid'=>'31',//第二类别id
                'number'=>'3',//数量
                'price'=>'10',//单价
            ),
        );

        $tcid = '5';
        $res = $TaxCount->caltax2($arr, $tcid);
        dump($res);
    }

    public function dd8(){
        $l = ("left join mk_il_logs l ON(l.id=(select max(id) from mk_il_logs where MKNO=t.MKNO))");
        $list = M('TranList t')->field('t.MKNO,t.ex_time,t.ex_context,t.optime')->join($l)->join('LEFT JOIN mk_logs g ON t.MKNO = g.MKNO AND g.state = 20')->where($map)->order('optime desc')->limit($limit)->select(); 
        dump($list);
    }

    public function dd4(){
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient('http://api.gwj.bd/Test/Getcode');

        $res = $client->getOrder();
        dump($res);   
    }

    public function phpinfo(){
       echo phpinfo();
    }

    public function session(){

        session_start();
        $_SESSION['test'] = 'test132465';
        echo $_SESSION['test'];

    }

    public function sessionTest(){
        session_start();
        session('name1','55555');
        echo session('name1');
    }

    public function sessionTe(){
        session('name2','555558888');
        echo session('name2');
    }

    function curl($mkno,$letter=''){
        //Man161019
        $astr   = array('','a','b','c','d','e','f','g','h','i','j','k','l','m','n');
        $sstr   = intval($mkno[4]);
        $bstr   = $astr[$sstr];
        if(strlen(trim($letter))>0 && $letter[0]=='z'){
            $bstr = 'z'.$bstr;
        }
        
        $number = str_replace(array("MK88".$sstr,"US") ,'' ,$mkno); //把符合数组中的数据替换为空

        $number = intval($number);  //转换整数类型

        $cback['url'] = C("MESSAGE.CREATE_URL").$bstr.$number;

        return $cback;
    }

    public function dd9(){

        $ids = array('13112','13113','13114');
        $xa_bnum = 'YPB1804120226';
        $type = 'PNO';
        $noid = '271';
        vendor('Hprose.HproseHttpClient');
        $client = new \HproseHttpClient(C('RAPIURL').'/AdminOrdersBoxed');

        $res = $client->save($ids, $xa_bnum, $type, $noid);
        dump($res);
    }
}