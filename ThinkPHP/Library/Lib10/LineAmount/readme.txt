//验证线路订单最大限制金额
$obj = new \Lib10\LineAmount\LineCost();

请求方法
$line_id = '线路ID';
$goods = array(
    [0]     => array(
            'cid'       => '分类ID',
            'number'    => '数量',
            'price'     => '单价',
            'is_suit' => '0|1',//0非套装；1套装
    ),
);
$recipient_arr = array(
    ['province'] => '收件人的省',
    ['city'] => '收件人的市',
    ['town'] => '收件人的区',
    ['address'] => '收件人的详细地址',
);
$result = $obj->cost($line_id, $goods,$recipient_arr);
正确返回
$result = true;

错误返回
$result = false;
错误信息
$obj->getError();

========================================================================
//验证线路订单详细地址
$obj = new \Lib10\LineAmount\AddressDetails();

请求方法
$str = '收件人详细地址';
$result = $obj->filterName($str);
正确返回
$result = true;

错误返回
$result = false;
错误信息
$obj->getError();

