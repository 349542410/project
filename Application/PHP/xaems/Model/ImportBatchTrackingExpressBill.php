<?php
require_once("ExpressServiceApi.php");
require_once("../Vendor/phpexcel/Classes/PHPExcel/IOFactory.php");
class ImportBatchTrackingExpressBill extends ExpressServiceApi {
	//phpExcelObject
	private $phpExcelObject = null;

    //允许上传的最大条数
    private $allowMaxRow = 3001;//原来是2000

	//是否检查总行数 和允许上传的最大条数 相关联
    private $ischeckhighestRow = true;

    //将数组拆分条数(400条数组拆分为100一组)
    private $pagesize = 100;//原来是200

    //取得当前 excel 总行数
    private $highestRow = 0;

   	//取得当前 excel 总列数
    private $highestColumn = 0;

    // 返回处理后的数据
    private $returnData = array();

    //数据结构
    private $dataStructure = array();

    //用户唯一标示
    private $customerIdentity;

    //普通转运和小标签包裹转运 GeneralTranshipment  SmallLabelTranshipment
    private $transhipmentType;

    //货站编码
    private $trackingCenterCode;

     //渠道编码
    private $channelCode;

    //错误信息  toString
    public $errorMessage = '';

    // $notice 信息 一般错误信息，不影响流程(默认关闭)
    private $notice = false;

	//notice 错误信息  toString
    private $noticeMessage = '';

    //notice 允许为空字段
    private $allowEmpty = [
       'SmallLabelTranshipment' => [
           'TrackingNumber',
           'FreightCompany'
       ],
        'GeneralTranshipment' => []
    ];

    private $init_data;

    //手动 A-AD 转化为数字，写死。因为excel字段不会超过太多
	public static $AAD = array(1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F', 7 => 'G', 8 => 'H', 9 => 'I', 10 => 'J', 11 => 'K', 12 => 'L', 13 => 'M', 14 => 'N', 15 => 'O', 16 => 'P', 17 => 'Q', 18 => 'R', 19 => 'S', 20 => 'T', 21 => 'U', 22 => 'V', 23 => 'W', 24 => 'X', 25 => 'Y', 26 => 'Z', 27 => 'AA', 28 => 'AB', 29 => 'AC', 30 => 'AD', 31 => 'AE', 32 => 'AF', 33 => 'AG', 34 => 'AH', 35 => 'AI');
    /**
     * 数据结构,数据结构自定义，这样很大程度应变数据结构变化,可以调整数据结构保持对应
     */
    private $structure = [
            'AllowAutoExchange' => '智能换箱',//
            'CustomerOrderNumber' => '客户订单号',//
            'FirmType' => '普通加固与特殊加固',//
            'FirmType1' => '普通加固',
            'FirmType2' => '特殊加固',
            'FreightCompany' => '承运公司',//
            'HasPrepaid' => '是否代缴关税',//
            'HasReplaceUploadIdCard' => '是否代传身份证',//
            'IdCardNumber' => '身份证编号',//
            'IsInsure' => '是否投保',//
            'IsReplaceOuterBox' => '外箱更换',//
            'IsRemovedInvoice' => '发票取出',//
            'IsVerifyGoods' => '开箱清点',//
            'Origin' => '原产地（发件国）',//
            'TrackingNumber' => '预报跟踪号',//


            'FromName' => '寄件人',
            'FromTelphone' => '寄件人电话号码',
            'FromToZIP' => '寄件人邮编',
            'FromAddressee' => '寄件人地址',


            'ToProvince' => '收件省/直辖市',
            'ToProvinceCode' => '收件省/直辖市',
            'ToCity' => '收件城市',
            'ToName' => '收件人',
            'ToZIP' => '收件人邮编',
            'ToEmail' => '收件人邮箱',
            'ToAddressee' => '收件人地址',
            'ToTelphone' => '收件人电话号码',


            'Brand' => '品牌',
            'GoodsName' => '物品名称',
            'MinCategoryCode' => '品类',
            'CurrencyCode' => '币种',
            'Unit' => '计量单位',
            'Quantity' => '物品数量',
            'Price' => '物品单价',
            'TariffNumber' => '行邮税号',
            'ModelNo' => '物品型号'
        ];


	//设置 excel 中，两个字段相等
    private $_equalStructure = [
        'ProvinceCode' => 'ToCity',
        'FromCity' => 'ToCity'
    ];
    // 缺省值 补充
    private $_default = [
        'FirmType' => 'NoFirm',
        'FirmType1' => 'NoFirm',
        'FirmType2' => 'NoFirm',
    ];

    //设置 excel 中，匹配类型
    private $_matchStructure = [
        'ProvinceCode' => [
            1 =>'北京市',
            2 =>'天津市',
            3 =>'河北省',
            4 =>'山西省',
            5 =>'内蒙古自治区',
            6 =>'辽宁省',
            7 =>'吉林省',
            8 =>'黑龙江省',
            9 =>'上海市',
            10 =>'江苏省',
            11 =>'浙江省',
            12 =>'安徽省',
            13 =>'福建省',
            14 =>'江西省',
            15 =>'山东省',
            16 =>'河南省',
            17 =>'湖北省',
            18 =>'湖南省',
            19 =>'广东省',
            20 =>'广西壮族自治区',
            21 =>'海南省',
            22 =>'重庆市',
            23 =>'四川省',
            24 =>'贵州省',
            25 =>'云南省',
            26 =>'西藏自治区',
            27 =>'陕西省',
            28 =>'甘肃省',
            29 =>'青海省',
            30 =>'宁夏回族自治区',
            31 =>'新疆维吾尔自治区',
            32 =>'香港特别行政区',
            33 =>'澳门特别行政区',
        ],
        'FirmType' => [
        	'NoFirm' => '不加固',
            'BasicFirm' => '普通加固',
            'SpecialFirm' => '特殊加固',
        ],
    ];
	// 特殊选择结构
    private $_specialStructure = [
        'HasPrepaid' => [1 => '是', 0 => '否'],
        'IsInsure' => [1 => '是', 0 => '否'],
        'IsReplaceOuterBox' => [1 => '是', 0 => '否'],
        'AllowAutoExchange' => [1 => '是', 0 => '否'],
        'IsVerifyGoods' => [1 => '是', 0 => '否'],
        'IsRemovedInvoice' => [1 => '是', 0 => '否'],
        'HasReplaceUploadIdCard' => [1 => '是', 0 => '否'],
    ];
	// 收件人信息
    private $_addressDetailStructure = [
    	'Area' => 'ToProvince',
    	'City' => 'ToCity',
    	'Email' => 'ToEmail',
    	'Name' => 'ToName',
    	'Province' => 'ToProvince',
        'ProvinceCode' => 'ToProvinceCode',
        'Street' => 'ToAddressee',
        'Telphone' => 'ToTelphone',
        'ZIP' => 'ToZIP',
    ];


	// 发件人地址信息
    private $_sendAddressDetailStructure = [
    	'Name' => 'FromName',
        'Street' => 'FromAddressee',
        'Telphone' => 'FromTelphone',
        'ZIP' =>'FromToZIP',
    ];

	 // 商品分类结构
    private $_goodsDetailStructure = [
        'Brand',
        'CurrencyCode',
        'GoodsName',
    	'MinCategoryCode',
        'ModelNo',
        'Price',
        'Quantity',
        'TariffNumber',
        'Unit',
    ];

	//合并字段结构(一维数组避免再次循环)
    private $_mergeStructure = [
    	'FirmType1' => 'FirmType',
    	'FirmType2' => 'FirmType',
    ];

    private $consistencyStructure = [
        'AllowAutoExchange',
        'CustomerOrderNumber',
        'FirmType',
        'FreightCompany',
        'IsInsure',
        'IsRemovedInvoice',
        'IsReplaceOuterBox',
        'IsVerifyGoods',

        'FromName',
        'FromTelphone',
        'FromToZIP',
        'FromAddressee',

        'ToProvince',
        'ProvinceCode',
        'ToCity',
        'ToName',
        'ToZIP',
        'ToAddressee',
        'Telphone',


    ];

	//指定字段类型结构
    private $_excelDataType = [
        'AllowAutoExchange' => ['Select', 'Boolean'],
        'CustomerOrderNumber' => ['String'],
        'FreightCompany' => ['String'],
        'HasPrepaid' => ['Select', 'Boolean'],
        'HasReplaceUploadIdCard' => ['Select', 'Boolean'],
        'IdCardNumber' => ['String'],
        'IsInsure' => ['Select', 'Boolean'],
        'IsRemovedInvoice' => ['Select', 'Boolean'],
        'IsReplaceOuterBox' => ['Select', 'Boolean'],
        'IsVerifyGoods' => ['Select', 'Boolean'],
        'Origin' => ['String'],
        'TrackingNumber' => ['String'],

        'Brand' => ['String'],
        'GoodsName' => ['String'],
        'MinCategoryCode' => ['Select', 'Int'],
        'Unit' => ['String'],
        'Quantity' => ['Float'],
        'Price' => ['Float'],
        'ModelNo' => ['String'],

        'FromName' => ['String'],
        'FromTelphone' => ['String'],
        'FromToZIP' => ['String'],
        'FromCity'	=> ['String'],
        'FromAddressee' => ['String'],


        'ToProvince' => ['String'],
        'ToCity' => ['String'],
        'ProvinceCode' => ['String'],
        'ToName' => ['String'],
        'ToZIP' => ['String'],
        'ToAddressee' => ['String'],
        'Telphone' => ['String'],

    ];

    function __construct($init_data = array()) {
        $this->init_data = $init_data;
        parent::__construct();
    }

    /**
     * 实现接口方法，不会返回实体数据
     */
	public function getEntityData() {
		return $this->resultData;
	}

    /**
     * 动态设置 select
     */
    public function dynamicSetSelect($field, $data) {
		$tmp[$field] = $data;
		$this->_specialStructure = array_merge($this->_specialStructure, $tmp);
	}
    /**
     * 读取 excel 数据
     * 方案: 指定表头信息对应的键值作为获取数据的根据(例如:“收件人”对应位置在 excel 的 F 行 则,F 作为 收件人的值)
     * 错误信息：
     * @access private
	 * @param  file
     * @return array
     */
    public function readPackage($file) {
        $inputFileType = \PHPExcel_IOFactory::identify($file);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true);
        $this->phpExcelObject = $objReader->load($file);
        $objWorksheet = $this->phpExcelObject->getSheet(0);
        $this->highestRow = $objWorksheet->getHighestRow();// 取得总行数
        //是否开启检查最大行数
     	if($this->ischeckhighestRow) {
	         if($this->highestRow > $this->allowMaxRow) {
	            $this->setError('excel数据量过大,请拆分为3000条然后分批上传!');
	            return false;
	        }
     	}
        $this->highestColumn = $objWorksheet->getHighestColumn();// 取得总列数坐标AF
		/*
        if($objWorksheet->getHighestColumn() != 'Y') {
                $this->setError('请下载最新转运导入模板!');
	            return false;
        }
		*/
        $this->dataStructure = $this->getPosition($objWorksheet);
        $resultData = $this->planTwo($objWorksheet);
        if(Count($resultData) > 300) {
            $this->setError('上传单数最大不得超过300单');
            return false;
        }
        if(!$resultData) {
            return false;
        }
      	return $this->splitData();
    }
     /**
     * 设置 userid
	 * @param  int userid
     * @access public
     */
     public function setUserId($uid = 0) {
     	$this->UserID = $uid;
     }
     /**
     * 设置 userid
	 * @param  int userid
     * @access public
     */
	 public function setCustomerIdentity($value) {
		$this->customerIdentity = $value;
	 }

    /**
     * 设置 普通转运和小标签包裹转运
     * @param $transhipmentType
     */
    public function setTranshipmentType($transhipmentType) {
        $this->transhipmentType = $transhipmentType;
    }
     /**
     * 设置货站编码 trackingcentercode
	 * @param  string trackingcentercode
     * @access public
     */
	 public function setTrackingCenterCode($value) {
		$this->trackingCenterCode = $value;
	 }

    /**
     * 设置渠道编码 channelCode
	 * @param  string channelCode
     * @access public
     */
	 public function setChannelCode($value) {
		$this->channelCode = $value;
	 }
     /**
     * 设置是否开启检查
	 * @param  bool $bool
     * @access public
     */
     public function setCheckHighestRow($bool = false) {
     	$this->ischeckhighestRow = $bool;
     }
     /**
     * 超出了制定的最大行数
     * @access private
     * @return bool
     */
     private function checkHighestRow() {
     	if($this->ischeckhighestRow) {
	         if($this->highestRow > $this->allowMaxRow) {
	            $this->setError('excel数据量过大,请拆分为3000条然后分批上传!');
	            return false;
	        }
     	}
     }
     /**
     * 拆分数据
     * @access private
     * @return array or bool
     */
     private function splitData() {
 		$resultData = array_chunk($this->returnData, $this->pagesize, true);
        return empty($resultData) ? false : $resultData;
     }
     /**
     * 根据表头 对应读取数据
     * @access private
     * @return array
     */
    private function planTwo($objWorksheet) {
        //$rShipTmp 临时存放单号集合
        $newData = $returnData = $newResponseData = $rShipTmp = [];
		$normatArray = self::$AAD;
		$flipArray = array_flip($normatArray);
		$count = $flipArray[$this->highestColumn];
        $trans_type = $this->init_data['transhipmentType'];
        // 转运 小标签不需要验证 CustomerOrderNumber

        if((isset($this->init_data['transhipmentType'])) && ($this->init_data['transhipmentType'] == "SmallLabelTranshipment")) {
            unset($this->consistencyStructure[0]);
        }

        for($i = 2; $i <= $this->highestRow; $i++)
        {
            //初始化 innergoods //初始化 address //初始化 sendaddress
            /**
             * 初始化 innergoods address sendaddress
             * $isEmpty 查整列是否为空，如果是则 return 跳过该列
             * $rowTile记录可以为空的坐标
             */
            $innerGoods = $toAddress =  $sendAddress = $isEmpty = $rowTile = [];

            for($k = 1; $k <= $count; $k++)
            {
                $tmpj = $normatArray[$k];
                if(in_array($tmpj, $this->dataStructure)) {
                    $address = $tmpj . $i;
                    $tmpData = $objWorksheet->getCell($address)->getValue();
                    if(is_null($tmpData) || empty($tmpData)) {
                        $isEmpty[] = '';
                    } else {
                        $isEmpty[] = $tmpData;
                    }
                }
            }
            $bar = implode('', $isEmpty);
            if(empty($bar)) {
                continue;
            }
            unset($isEmpty);
            for($j = 1; $j <= $count; $j++)
            {
            	$tmpj = $normatArray[$j];
                if(in_array($tmpj, $this->dataStructure)) {
                    // 单元格坐标
                    $address = $tmpj . $i;
                    $cellvalue = $objWorksheet->getCell($address)->getValue();
                    $key = array_search($tmpj,$this->dataStructure);


                    //小标签允许为空 无需弹出验证
                    if(!in_array($key, $this->allowEmpty[$trans_type])) {
                        //小标签
                        if(is_null($cellvalue) || empty($cellvalue)) {
                            // 记录 notice ,想想还是算了,值为空跳过该条记录
                            // return $newResponseData;
                            $this->errorMessage = '第'.$i.'行第'.$j.'列数据为空.';
                            return false;
                        }
                    }


					//需要转化
					if(in_array($key, array_keys($this->_excelDataType)))
					{
						$tmpFunction = 'set';
						foreach ($this->_excelDataType[$key] as $k => $v)
						{
							$tmpFunction = 'set'.$v;
							$cellvalue = $this->$tmpFunction($cellvalue, $key);
						}
					}
					//需要合并
                    if(in_array($key, $this->_goodsDetailStructure))
                    {
                        $innerGoods[$key] = $cellvalue;
                    }
                    else if(in_array($key, array_values($this->_addressDetailStructure)))
					{
						$newKey = array_flip($this->_addressDetailStructure);
						// 城市匹配获取 城市ID
					 	if(isset($newKey[$key]) && $newKey[$key] == 'Province') {
					      	$toAddress['ProvinceCode'] = $this->findProvinceId($cellvalue);
							$toAddress['Area'] = 'ToProvince';
					    }
						$toAddress[$newKey[$key]] = $cellvalue;
					}
                    else if(in_array($key, array_values($this->_sendAddressDetailStructure)))
					{
						$newKey = array_flip($this->_sendAddressDetailStructure);
						// 城市匹配获取 城市ID
					 	if(isset($newKey[$key]) && $newKey[$key] == 'Province') {
					      $sendAddress['ProvinceCode'] = $this->findProvinceId($cellvalue);
						  $sendAddress['Area'] = 'ToProvince';
					    }
						$sendAddress[$newKey[$key]] = $cellvalue;
					}
					else
					{
                        $newData[$key] = $cellvalue;
                    }
                }
            }
            if(!isset($newData['CustomerOrderNumber'])) return array();
			// 合并字段
			if(!empty($this->_mergeStructure))
			{
				//获取结构
				foreach ($this->_mergeStructure as $mk => $mv) {
					if(in_array($mk, array_keys($this->structure)))
					{
                       if($newData['FirmType1'] == '是' && $newData['FirmType2'] == '是') {
                            $this->errorMessage = '[普通加固]和[特殊加固]不能同时为是';
                            return false;
                       }
						//确定当前列
						$lineName = $this->structure[$mk];
						if($newData[$mk] == '是') {
							$tmpData = array_search($lineName, $this->_matchStructure[$mv]);
                            // 处理缺省值
                            $_default = isset($this->_default[$tmpData]) ? $this->_default[$tmpData] : 0;
							$tmpData = $tmpData ? $tmpData : $_default;
							$newData[$mv] = $tmpData;
							break;
						} else {
                            // 处理缺省值
                            $_default = isset($this->_default[$mk]) ? $this->_default[$mk] : 0;
							$newData[$mv] = $_default;
						}
					}
				}
				$this->deleteMergeStructure($newData);
			}
			$newData['CustomerIdentity'] = $this->customerIdentity;
            $newData['TranshipmentType'] = $this->transhipmentType;
            $newData['TrackingCenterCode'] = $this->trackingCenterCode;
            $newData['ChannelCode'] = $this->channelCode;
			// MD5去重 合拼数据
            $md5 = md5(json_encode($newData['TrackingNumber']));

            $arr[$md5]['base'] = $newData;
			$arr[$md5]['Address'] = $toAddress;
			$arr[$md5]['SendAddress'] = $sendAddress;
            $arr[$md5]['Goods'][] = $innerGoods;
            // 相同的客户订单号 判断其他数据的一致性
            if(isset($this->returnData[$md5])) {

                foreach($this->returnData[$md5] as $k => $v) {
                    // 需要保持数据一致性的比较
                    if(in_array($k, $this->consistencyStructure)) {
                        if($v != $newData[$k]) {
                            $this->errorMessage = '客户订单号为:'.$newData['CustomerOrderNumber'].'的包裹，对应的 "'.$this->structure[$k].'" 不一致,请检查！';
                            return false;
                        }
                    }
                }
            }
            $this->returnData[$md5] = $arr[$md5]['base'];
			$this->returnData[$md5]['Address'] = $arr[$md5]['Address'];
			$this->returnData[$md5]['SendAddress'] = $arr[$md5]['SendAddress'];
            $this->returnData[$md5]['Goods'] = $arr[$md5]['Goods'];

        }
        $delwithData = array_values($this->returnData);
        if(empty($delwithData)) {
            $this->errorMessage = '很抱歉,没有检索到数据,或者未按照excel模板格式书写.';
            return false;
        }
        /*foreach($delwithData as $k => $value) {
            $innerGoodsValue = 0;
            $declaredValue = sprintf("%.3f",$value['DeclaredValue']);//申报总价值
            foreach($value['Goods'] as $goodsvalue) {
                $innerGoodsValue += $goodsvalue['Price']*$goodsvalue['Quantity'];//内件总价值和
                $innerGoodsValue = sprintf("%.3f",$innerGoodsValue);
            }
            if((String)$innerGoodsValue !== (String)$declaredValue) {
                $this->errorMessage = '预报跟踪号为:'.$value['TrackingNumber'].'的包裹.'.'申报总价值与内件总价值(申报总价值=∑单价*数量)不符，请检查！';
            return false;
            }

        }*/

        return $delwithData;
    }
     /**
     * 确定当前 行的坐标
     * @access private
     * @return array
     */
     public function findProvinceId($city) {
        foreach($this->_matchStructure['ProvinceCode'] as $k => $word) {
            $levenshtein = $this->LevenshteinDistance($city, $word);
            $levenshteinData[$k] = $levenshtein;
        }
        asort($levenshteinData);
        $provinceid = array_values(array_flip($levenshteinData));
        return isset($provinceid[0]) ? $provinceid[0] : 1;
     }
     /**
     * 删除数组元素
     * @access private
     * @return array
     */
    private function deleteMergeStructure(&$dataStructure) {
		foreach (array_keys($this->_mergeStructure) as $key => $value) {
			unset($dataStructure[$value]);
		}
    }
     /**
     * 确定当前 行的坐标
     * @access private
	 * @param  object objWorksheet  == excel 对象
     * @return array
     */
    private function getPosition($objWorksheet) {
        //计算数据结构的长度
        $dataStructure = [];
		$normatArray = self::$AAD;
		$flipArray = array_flip($normatArray);
		$count = $flipArray[$this->highestColumn];
        for($j = 1; $j <= $count; $j++) {
            $position = $normatArray[$j]. 1;  // 获取表头对应的坐标
            $cellvalue = $objWorksheet->getCell($position)->getValue();
            if(in_array($cellvalue, $this->structure)) {
                $key = array_search($cellvalue,$this->structure);
                $dataStructure[$key] = $normatArray[$j];
            }
        }
        return $dataStructure;
    }
    /**
     * 字符串截取，支持中文和其他编码
     * @static
     * @access public
     * @param string $str 需要转换的字符串
     * @param string $start 开始位置
     * @param string $length 截取长度
     * @param string $charset 编码格式
     * @param string $suffix 截断显示字符
     * @return string
     */
    function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
        if(function_exists("mb_substr"))
            $slice = mb_substr($str, $start, $length, $charset);
        elseif(function_exists('iconv_substr')) {
            $slice = iconv_substr($str,$start,$length,$charset);
            if(false === $slice) {
                $slice = '';
            }
        }else{
            $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("",array_slice($match[0], $start, $length));
        }
        return $suffix ? $slice.'...' : $slice;
    }
    /**
     * 强制转化为 Int 类型
     * @static
     * @access public
     * @return int
     */
    public function setInt($data, $key)
    {
    	return (int)$data;
    }
    /**
     * 强制转化为 Float 类型
     * @static
     * @access public
     * @return float
     */
    public function setFloat($data, $key)
    {
    	return (float)$data;
    }
    /**
     * 强制转化为 String 类型
     * @static
     * @access public
     * @return string
     */
    public function setString($data, $key)
	{
		//return (string)trim($data);
        return (string)preg_replace('#\s+#','',trim(strip_tags($data)));
	}
    /**
     * 强制转化为 Boolean 类型
     * @static
     * @access public
     * @return string
     */
    public function setBoolean($data, $key)
	{
		return (bool)$data;
	}
    /**
     * 强制转化为 SELECT 类型
     * @static
     * @access public
	 * @param  string  data
	 * @param  int	   key		当前字段对应的特殊结构
     * @return string
     */
    public function setSelect($data, $key)
	{
		$cellvalue = '';
		if(in_array($data, $this->_specialStructure[$key])) {
		    $cellvalue = array_search($data,$this->_specialStructure[$key]);
		} else {
			// 缺省选择第一个
            var_dump($key);
		    $keys = array_keys($this->_specialStructure[$key]);
		    $cellvalue = $keys[1];
		}
		return $cellvalue;
	}
    /**
     * 合拼 excel 表格字段
     * @static
     * @access public
	 * @param  string  targetField	目标字段
	 * @param  string  fieldKey		当前传入字段
	 * @param  array   mergeFields	要合并的字段
     * @return string
     */
   	public function setMerge($data, $key)
	{
		$arr = [];
		if(in_array($key, array_keys($this->_mergeStructure)))
		{
			$arr[] = [$this->_mergeStructure[$key] => $data];
		}
		return $arr;
	}
     /**
     *  相似度比较
     * @access private
     * @return array
     */
    private function LevenshteinDistance($s1, $s2)
    {
        $nLeftLength = strlen($s1);
        $nRightLength = strlen($s2);
        if ($nLeftLength >= $nRightLength)
        {
          $sLeft = $s1;
          $sRight = $s2;
        } else {
          $sLeft = $s2;
          $sRight = $s1;
          $nLeftLength += $nRightLength;  //  arithmetic swap of two values
          $nRightLength = $nLeftLength - $nRightLength;
          $nLeftLength -= $nRightLength;
        }
        if ($nLeftLength == 0)
          return $nRightLength;
        else if ($nRightLength == 0)
          return $nLeftLength;
        else if ($sLeft === $sRight)
          return 0;
        else if (($nLeftLength < $nRightLength) && (strpos($sRight, $sLeft) !== FALSE))
          return $nRightLength - $nLeftLength;
        else if (($nRightLength < $nLeftLength) && (strpos($sLeft, $sRight) !== FALSE))
          return $nLeftLength - $nRightLength;
        else {
          $nsDistance = range(0, $nRightLength);
          for ($nLeftPos = 1; $nLeftPos <= $nLeftLength; ++$nLeftPos)
          {
            $cLeft = $sLeft[$nLeftPos - 1];
            $nDiagonal = $nLeftPos - 1;
            $nsDistance[0] = $nLeftPos;
            for ($nRightPos = 1; $nRightPos <= $nRightLength; ++$nRightPos)
            {
              $cRight = $sRight[$nRightPos - 1];
              $nCost = ($cRight == $cLeft) ? 0 : 1;
              $nNewDiagonal = $nsDistance[$nRightPos];
              $nsDistance[$nRightPos] =
                min($nsDistance[$nRightPos] + 1,
                    $nsDistance[$nRightPos - 1] + 1,
                    $nDiagonal + $nCost);
              $nDiagonal = $nNewDiagonal;
            }
          }
          return $nsDistance[$nRightLength];
        }
    }
    /**
     * 设置错误信息
     * @static
     * @access public
     * @return array
     */
    public function setError($m) {
        $this->errorMessage = $m;
    }
    /**
     * 获取错误信息
     * @static
     * @access public
     * @return array
     */
     public function getError() {
        return $this->errorMessage;
     }
    /**
     * 释放 phpExcelObject
     * @static
     * @access public
     */
	 public function __destruct() {
	 	$this->phpExcelObject->disconnectWorksheets();
		unset($this->phpExcelObject);
	 }
}