<?php
/**
 * 自助打印终端---提供会员在ERP软件端（无需会员登录）
 * 包含：操作订单打印流程、获取线路价格配置
 */
namespace AUApi\Controller;
class TestsController {
	public function test(){
	    echo 123;
	    print_r(session('auth_item'));exit();

    }

}