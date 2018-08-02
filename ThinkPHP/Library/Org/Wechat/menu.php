<?php
// 创建默认菜单，美购正式使用后，将默认的设为美购相关的，测试时是用美快的
//$btn_url    = 'http://ad.mg.megao.hk/Advert';
$btn_url    = 'https://vip.megao.cn';
$button0 = array(
    "button"=>array(
                array(
                    "type" => "view", 
                    "name" => "物流查询", 
                    "url" => "http://m.meiquick.com/Index/search", 
                ), 
                array(
                    "type" => "view", 
                    "name" => "美快国际", 
                    "url" => "http://m.meiquick.com", 
                )
    )
);
// 创建会员菜单
$button1 = array(
    'button'    => array(
            array(
                'type'  => 'view',
                'name'  => '美购商城',
                'url'   => $btn_url,
            ),
            array(
                'name'  => '共创事业',
                'sub_button' => array(
                    array(
                    "type"=>"view",
                    "name"=>"事业说明",
                    "url"=>$btn_url."/Boss/"
                    ),
                    array(
                    "type"=>"view",
                    "name"=>"事业加盟",
                    "url"=>$btn_url."/Shop/shoppingcart/"        
                    ),
                    array(
                    "type"=>"view",
                    "name"=>"购物车",
                    "url"=>$btn_url."/Shop/shoppingcart/"                                                     
                    ),
                    array(
                    "type"=>"view",
                    "name"=>"成功案例",
                    "url"=>$btn_url  
                    ),
                    array(
                    "type"=>"view",
                    "name"=>"我的订单",
                    "url"=>$btn_url."/Order/order_list"  
                    ),
                ),
            )
    ),
    'matchrule' => array(
        "group_id"=>"102"
    )
);
// 创建分销商菜单
$button2_ = array(
    'button'    => array(
            array(
                'type'  => 'view',
                'name'  => '美购商城',
                'url'   => $btn_url,
            ),
            array(
                'name'  => '东家中心',
                'sub_button' => array(
                    array(
                    "type"=>"view",
                    "name"=>"东家中心",
                    "url"=>$btn_url."/Boss/boss_core"
                    ),
                    array(
                    "type"=>"view",
                    "name"=>"购物车",
                    "url"=>$btn_url."/Shop/shoppingcart/"  
                    ),
                    array(
                    "type"=>"view",
                    "name"=>"我的订单",
                    "url"=>$btn_url."/Order/order_list"  
                    ),
                    array(
                    "type"=>"view",
                    "name"=>"推广二维码",
                    "url"=>$btn_url."/Publicize/qrcode"                                                   
                    ),
                    array(
                    "type"=>"view",
                    "name"=>"推荐阅读",
                    "url"=>$btn_url."/Article/artlist"
                    )
                ),
            )
    ),/*
    'matchrule' => array(
        "group_id"=>"103"
    )*/
);

// 分销商菜单20160504版
$button2 = array(
    'button'    => array(
            array(
                'type'  => 'view',
                'name'  => '美街商城',
                'url'   => $btn_url,
            ),
            array(
                'name'  => '分享',
                'sub_button' => array(
                    array(
                    "type"=>"view",
                    "name"=>"推广二维码",
                    "url"=>$btn_url."/Publicize/qrcode"
                    ),
                    array(
                    "type"=>"view",
                    "name"=>"推荐分享",
                    "url"=>$btn_url."/Article/artlist"
                    ),
                    array(
                    "type"=>"view",
                    "name"=>"我的分享",
                    "url"=>$btn_url."/Boss/boss_core/sub/8"
                    ),
                    array(
                    "type"=>"view",
                    "name"=>"商品分类",
                    "url"=>$btn_url."/Allcate/all_cate_list"
                    ),
                    array(
                    "type"=>"view",
                    "name"=>"商城介绍",
                    "url"=>$btn_url."/Article/read/artid/18"
                    ),
                )
            ),
            array(
                'name'  => '我',
                'sub_button' => array(
                    array(
                    "type"=>"view",
                    "name"=>"客似云来",//有凤来仪
                    "url"=>$btn_url."/Boss/boss_core/sub/5"
                    ),
                    array(
                    "type"=>"view",
                    "name"=>"日进斗金",
                    "url"=>$btn_url."/Boss/boss_core/sub/3"
                    ),                    
                    array(
                    "type"=>"view",
                    "name"=>"购物车",
                    "url"=>$btn_url."/Shop/shoppingcart"
                    ),
                    array(
                    "type"=>"view",
                    "name"=>"我的订单",
                    "url"=>$btn_url."/Order/order_list"
                    ),
                    array(
                    "type"=>"view",
                    "name"=>"会员中心",
                    "url"=>$btn_url."/Shop/user"
                    )
                ),
            )
    ),
);
$button 	= array($button2);//array($button0,$button1,$button2);

/*
{
    "id": 0, 
    "name": "未分组", 
    "count": 16
}, 
{
    "id": 1, 
    "name": "黑名单", 
    "count": 0
}, 
{
    "id": 2, 
    "name": "星标组", 
    "count": 1
}, 
{
    "id": 100, 
    "name": "MKIL", 
    "count": 6
}, 
{
    "id": 102, 
    "name": "美购分销会员", 
    "count": 0
}, 
{
    "id": 103, 
    "name": "美购经销商", 
    "count": 0
}
*/