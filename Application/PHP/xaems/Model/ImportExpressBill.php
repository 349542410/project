<?php
require_once("CreatedNeedleBillServiceApi.php");
require_once("../Vendor/phpexcel/Classes/PHPExcel/IOFactory.php");
class ImportExpressBill extends CreatedNeedleBillServiceApi
{
    private $em;

    private $connection;
    /**
     * 允许上传的最大条数
     */
    private $allowMaxRow = 3001;//原来是2000
    /**
     * 拆分条数
     */
    private $pagesize = 100;//原来是200
    /**
     * 取得总行数
     */
    private $highestRow = 0;
    /**
     * 取得总列数
     */
    private $highestColumn = 0;
    /**
     * 总条数
     */
    private $returnData;
    /**
     * 数据结构
     */
    private $dataStructure = [];
    /**
     * 用户ID
     */
    public $UserID;
    /**
     * 客户标识
     */
    private $customerIdentity;
    /**
     * 货站编码
     */
    private $trackingCenterCode;
    /**
     * 渠道编码
     */
    private $channelCode;
    /**
     * 临时表ID
     */
    private $tmpTableId;
    /**
     * 错误信息
     */
    public $errorMessage = '';
    /**
     * 一般错误信息，不影响流程
     */
    private $notice = false;

    private $noticeMessage = '';

    private $_equalStructure = [
        'ProvinceId' => 'ToCity'
    ];
    /**
     * 匹配类型
     */
    private $_matchStructure = [
        'ProvinceId' => [
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
        ]
    ];

    private $_specialStructure = [
        'HasPrepaid' => [1 => '是', 2 => '否'],
        'InsureStatus' => [1 => '是', 0 => '否'],
        'HasReplaceUploadIdCard' => [1 => '是', 0 => '否'],
    ];
    private $_goodsDetailStructure = [
        'Brands',
        'GoodsName',
        'MinCategoryCode',
        'Price',
        'Qty',
        'TariffNumber',
        'Unit',
        'ModelNo',
    ];
    private $_excelDataType = [
        'RelationShipNumber' => 'STRING',
        'FromName' => 'STRING',
        'FromTelphone' => 'STRING',
        'FromZip' => 'STRING',
        'FromAddressee' => 'STRING',
        'ToProvince' => 'STRING',
        'ToCity' => 'STRING',
        'ProvinceId' => 'STRING',
        'ToName' => 'STRING',
        'ToZIP' => 'STRING',
        'ToAddressee' => 'STRING',
        'ToTelphone' => 'STRING',
        'Weight' => 'NUMBER_FORMAT',
        'DeclaredValue' => 'STRING',
        'Origin' => 'STRING',
        'HasPrepaid' => 'SELECT',
        'InsureStatus' => 'SELECT',
        'HasReplaceUploadIdCard' => 'SELECT',
        'IdCardNumber' => 'STRING',
        'Brands' => 'STRING',
        'GoodsName' => 'STRING',
        'MinCategoryCode' => 'SELECT',
        'Price' => 'FLOAT',
        'Qty' => 'STRING',
        'TariffNumber' => 'STRING',
        'Unit' => 'STRING',
        //'Currency' => 'STRING',
        'ModelNo' => 'STRING',
    ];

    private $consistencyStructure = [
        'FromName',
        'FromTelphone',
        'FromZip',
        'FromAddressee',
        'ToProvince',
        'ToCity',
        'ProvinceId',
        'ToName',
        'ToZIP',
        'ToAddressee',
        'ToTelphone',
		'InsureStatus',
    ];
    public function __construct() {
        parent::__construct();
    }
    public function getEntityData()
    {
        return;
    }
    /**
     * 设置客户标识
     */
	public function setCustomerIdentity($value) {
		$this->customerIdentity = $value;
	}
    /**
     * 设置货站编码
     */
    public function setTrackingCenterCode($value) {
		$this->trackingCenterCode = $value;
	 }
     /**
     * 设置渠道编码
     */
    public function setChannelCode($value) {
		$this->channelCode = $value;
	 }
    /**
     * 数据结构,数据结构自定义，这样很大程度应变数据结构变化,可以调整数据结构保持对应
     */
    private $getStructure = [
            'RelationShipNumber' => '客户订单号',
            'FromName' => '寄件人',
            'FromTelphone' => '寄件人电话号码',
            'FromZip' => '寄件人邮编',
            'FromAddressee' => '寄件人地址',
            'ToProvince' => '收件省/直辖市',
            'ProvinceId' => '收件省/直辖市',
            'ToCity' => '收件城市',
            'ToName' => '收件人',
            'ToZIP' => '收件人邮编',
            'ToAddressee' => '收件人地址',
            'ToTelphone' => '收件人电话号码',
            'HasReplaceUploadIdCard' => '是否代传身份证',
            'IdCardNumber' => '身份证编号',
            'Weight' => '实际重量(kg)',
            'DeclaredValue' => '总申报价值',
            'Origin' => '原产地(发件国)',
            'HasPrepaid' => '是否代缴关税',
            'InsureStatus' => '是否投保',
            'Brands' => '品牌',
            'GoodsName' => '物品名称',
            'MinCategoryCode' => '品类',
            'Price' => '物品单价',
            'Qty' => '物品数量',
            'TariffNumber' => '行邮税号',
            'Unit' => '计量单位',
            //'Currency' => '币种',
            'ModelNo' => '物品型号',
        ];
    /**
     * 读取 package 数据,读取数据分 2 个方案:
     * 方案1: 一一对应数据(比较笨的方法)
     * 方案2: 指定表头信息对应的键值作为获取数据的根据(例如:“收件人”对应位置在 excel 的 F 行 则,F 作为 收件人的值)
     * 错误信息：
     *  1. excel 数据超过设置的数据
     * @access private
     * @return array
     */
    public function readPackage($file) {
        $inputFileType = \PHPExcel_IOFactory::identify($file);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
        $objReader->setReadDataOnly(true);
        $phpExcelObject = $objReader->load($file);
        $objWorksheet = $phpExcelObject->getSheet(0);
        $this->highestRow = $objWorksheet->getHighestRow();// 取得总行数
        // 是否超出了制定的最大行数
        if($this->highestRow > $this->allowMaxRow) {
            $this->setError('excel数据量过大,请拆分为3000条然后分批上传!');
            return false;
        }
        $this->highestColumn = $objWorksheet->getHighestColumn();// 取得总列数坐标

        $this->dataStructure = $this->getPosition($objWorksheet);
        $resultData = $this->planTwo($objWorksheet);
        if(Count($resultData) > 300) {
            $this->setError('上传单数最大不得超过300单');
            return false;
        }
        if(!$resultData) {
            return false;
        }
        $resultData = array_chunk($resultData, $this->pagesize, true);
        return empty($resultData) ? false : $resultData;
    }
    /**
     * 动态设置 select
     */
    public function dynamicSetSelect($field, $data) {
		$tmp[$field] = $data;
		$this->_specialStructure = array_merge($this->_specialStructure, $tmp);
	}


    function multiInsert($table, $inserts) {
        $fields = "`".  implode("`,`", array_keys(current($inserts)))."`";
        foreach($inserts as $insert) {
            $insert = array_map('addslashes', $insert);
            $values[] = "\"".  implode("\",\"", $insert)."\"";
        }
        $valueStr = implode("),(", $values);
        $sql = "INSERT IGNORE INTO $table ($fields) VALUES ($valueStr)";

        return $sql;
    }


     /**
     * 方案二 根据表头 对应读取数据
     * @access private
     * @return array
     */
    private function planTwo($objWorksheet) {
        $newData = [];
        $returnData = [];
        $newResponseData = [];
        //临时存放单号集合
        $rShipTmp = [];
        for($i = 2; $i <= $this->highestRow; $i++)
        {
            //初始化 innergoods
            $innerGoods = [];
            //检查整列是否为空，如果是则 return 跳过该列
            $isEmpty = array();

            for($k = 'A'; $k <= $this->highestColumn; $k++)
            {
                if(in_array($k, $this->dataStructure)) {
                    // 单元格坐标
                    $address = $k . $i;
                    $cellvalue = $objWorksheet->getCell($address)->getValue();
                    if(is_null($cellvalue) || empty($cellvalue)) {
                        $isEmpty[] = "";
                    } else {
                        $isEmpty[] = $cellvalue;
                    }
                }
            }
            $bar = implode('', $isEmpty);
            if(empty($bar)) {
                continue;
            }
            unset($isEmpty);
            for($j = 'A'; $j <= $this->highestColumn; $j++)
            {   
                if(in_array($j, $this->dataStructure)) {
                    // 单元格坐标
                    $address = $j . $i;
                    $cellvalue = $objWorksheet->getCell($address)->getValue();

                    //这里需要加上允许为空的坐标       
                    if(is_null($cellvalue) || empty($cellvalue)) {    
                        // 记录 notice ,想想还是算了
                        //return $newResponseData;
                        if($j != 'M'){
                          $this->errorMessage = '第'.$i.'行第'.$j.'列数据为空.';
                          return false;
                        }
                        // break 2;
                    }
                    $key = array_search($j,$this->dataStructure);
                    //处理类型
                    switch($this->_excelDataType[$key])
                    {
                        case 'FLOAT':
                        $cellvalue = (float)$cellvalue;
                            break;
                        case "INT":
                        $cellvalue = (int)$cellvalue;
                            break;
                        case "STRING":
                        $cellvalue = (string)$cellvalue;
                            break;
                        case "NUMBER_FORMAT" :
                        $cellvalue = number_format($cellvalue, 4);
                            break;
                        case "SELECT":
                            if(in_array($cellvalue, $this->_specialStructure[$key])) {
                                $cellvalue = array_search($cellvalue,$this->_specialStructure[$key]);
                            } else {
                                $keys = array_keys($this->_specialStructure[$key]);
                                $cellvalue = $keys[1];
                            }
                        break;
                    }
                    if(in_array($key, $this->_goodsDetailStructure))
                    {
                        $innerGoods[$key] = $cellvalue;
                    }
                    else{
                        $newData[$key] = $cellvalue;
                    }
                }
            }
            if(!isset($newData['RelationShipNumber'])) return array();
            $newData['UserID'] = $this->UserID;
			$newData['CustomerIdentity'] = $this->customerIdentity;
            $newData['TrackingCenterCode'] = $this->trackingCenterCode;
            $newData['ChannelCode'] = $this->channelCode;
           
            if(isset($newData['ToProvince']) && !empty($newData['ToProvince'])) {
                $newData['ProvinceId'] = $this->findProvinceId($newData['ToProvince']);
            }

            $md5 = md5(json_encode($newData['RelationShipNumber']));

            //判断关联单号身份证是否相同
            /*
            if(empty($rShipTmp)) {
                $rShipTmp[$newData['RelationShipNumber']] = $newData['IdCardNumber'];
            } else {
              if(in_array($newData['RelationShipNumber'], array_keys($rShipTmp))) {
                  if($rShipTmp[$newData['RelationShipNumber']] != $newData['IdCardNumber']) {
                      $this->errorMessage = '同一客户订单号的身份证号码不相同！';
                      return false;                           
                  }              
              } else {
                  $rShipTmp[$newData['RelationShipNumber']] = $newData['IdCardNumber'];
              }
            }
            */
            $arr[$md5]['base'] = $newData;
            $arr[$md5]['InnerGoods'][] = $innerGoods;
            // 相同的客户订单号 判断其他数据的一致性

            if(isset($returnData[$md5])) {
                //特殊提示单独拿出来
                if($returnData[$md5]['DeclaredValue'] != $newData['DeclaredValue']) {
                    $this->errorMessage = '客户订单号为:'.$newData['RelationShipNumber'].'的包裹.'.'申报总价值与内件总价值(申报总价值=∑单价*数量)不符，请检查！';
                    return false;
                }
                foreach($returnData[$md5] as $k => $v) {
                    // 需要保持数据一致性的比较
                    if(in_array($k, $this->consistencyStructure)) {
                        if($v != $newData[$k]) {
                            $this->errorMessage = '客户订单号为:'.$newData['RelationShipNumber'].'的包裹，对应的 "'.$this->getStructure[$k].'" 不一致,请检查！';
                            return false;
                        }
                    }
                }
            }
            $returnData[$md5] = $arr[$md5]['base'];
            $returnData[$md5]['InnerGoods'] = $arr[$md5]['InnerGoods'];

            $newResponseData = array_values($returnData);
        }

        if(empty($newResponseData)) {
            $this->errorMessage = '很抱歉,没有检索到数据,或者未按照excel模板格式书写.';
            return false;
        }
        foreach($newResponseData as $value) { 
            $innerGoodsValue = 0;
            $declaredValue = $value['DeclaredValue'];//申报总价值
            if(!is_numeric($value['Weight'])) {
                $this->errorMessage = '客户订单号为:'.$value['RelationShipNumber'].'的包裹重量为数字类型！';
                return false;
            }
            foreach($value['InnerGoods'] as $goodsvalue) {
                $innerGoodsValue += $goodsvalue['Price']*$goodsvalue['Qty'];//内件总价值和
            }
            if($declaredValue != (String)$innerGoodsValue){
                $this->errorMessage = '客户订单号为:'.$value['RelationShipNumber'].'的包裹.'.'申报总价值与内件总价值(申报总价值=∑单价*数量)不符，请检查！';
                return false;
            }         
        }      
        for($i=0;$i<count($newResponseData);$i++) {
            if(!preg_match('|^[0-9a-zA-Z]+$|', trim($newResponseData[$i]['RelationShipNumber']))) {
                $this->errorMessage = '客户订单号为:'.$newResponseData[$i]['RelationShipNumber'].'不正确.(客户订单号由数字和字母组成)';
                return false;
            }
        }

        var_dump($newResponseData);
        var_dump($newResponseData[1]['InnerGoods']);

        return $newResponseData;
    }
     /**
     * 确定当前 行的坐标
     * @access private
     * @return array
     */
     public function findProvinceId($city) {
        foreach($this->_matchStructure['ProvinceId'] as $k => $word) {
            $levenshtein = $this->LevenshteinDistance($city, $word);
            $levenshteinData[$k] = $levenshtein;
        }
        asort($levenshteinData);
        $provinceid = array_values(array_flip($levenshteinData));
        return isset($provinceid[0]) ? $provinceid[0] : 1;
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
     * 确定当前 行的坐标
     * @access private
     * @return array
     */
    private function getPosition($objWorksheet) {
        //计算数据结构的长度
        $structure = $this->getStructure;
        $dataStructure = [];
        for($j = 'A'; $j <= $this->highestColumn; $j++) {
            $position = $j . 1; // 获取表头对应的坐标
            $cellvalue = $objWorksheet->getCell($position)->getValue();
            if(in_array($cellvalue, $structure)) {
                $key = array_search($cellvalue,$structure);
                $dataStructure[$key] = $j;
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
     * 三维数组去重
     * @static
     * @access private
     * @param  array  $array3D  数组
     * @return array
     */
    private function unique_arr($my_array)
    {
        $tmp_array = array();
        $new_array = array();
        foreach($my_array as $k => $val){
            $hash = md5(json_encode($val));
            if (!in_array($hash, $tmp_array)) {
                $tmp_array[] = $hash;
                $new_array[] = $val;
            }
        }
        return $new_array;
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
}
//李征宇 2015/1/15 9:37:43
class CeWebExcelReaderFilter implements \PHPExcel_Reader_IReadFilter
{
    public $ns = __CLASS__;

    // 当前行列游标 // TODO: 目前*_col只支持A-Z字符
    private $_col = 'A';
    private $_row = 1;

    // 矩形起点：A1
    public $min_col = 'A';
    public $min_row = 1;

    // 矩形终点：Z1
    public $max_col = 'U';
    public $max_row = 10000;

	public function readCell($col, $row, $worksheetName = '')
    {
        $this->set_me($col, $row);
        if ($this->skip_col() or $this->skip_row()) return false;
        else return true;
	}

    public function set_me($col, $row)
    {
        $this->_col = $col;
        $this->_row = $row;
    }

    public function skip_col()
    {
        return $this->_col < $this->min_col or $this->_col > $this->max_col;
    }

    public function skip_row()
    {
        return $this->_row < $this->min_row;
    }

    public function skip_all()
    {
        return $this->_row > $this->max_row;
    }
}