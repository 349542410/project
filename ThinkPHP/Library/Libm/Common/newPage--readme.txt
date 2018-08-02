<?php
新分页类的使用说明:

用途： 支持同一页面展示多个分页功能

加载方式：
//$count       总记录数
//$listRows    每页显示行数
//$var_page    分页参数名
$Page = new \Libm\Common\Page($count, $listRows, $var_page);

1.单个分页 详细使用方法参考下面的例子：

//第一种：利用Page类和limit方法

$User = M('User'); // 实例化User对象
$count      = $User->where('status=1')->count();// 查询满足要求的总记录数
$Page       = new \Libm\Common\Page($count,25,"p1");// 实例化分页类 传入总记录数、每页显示的记录数、分页参数名
$show       = $Page->show();// 分页显示输出
 // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
$list = $User->where('status=1')->order('create_time')->limit($Page->firstRow.','.$Page->listRows)->select();
$this->assign('list',$list);// 赋值数据集
$this->assign('page',$show);// 赋值分页输出
$this->display(); // 输出模板


//第二种：分页类和page方法的实现

$User = M('User'); // 实例化User对象
 // 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
$list = $User->where('status=1')->order('create_time')->page($_GET['p'].',25')->select();
$this->assign('list',$list);// 赋值数据集
$count      = $User->where('status=1')->count();// 查询满足要求的总记录数
$Page       = new \Libm\Common\Page($count,25,"p1");// 实例化分页类 传入总记录数、每页显示的记录数、分页参数名
$show       = $Page->show();// 分页显示输出
$this->assign('page',$show);// 赋值分页输出
$this->display(); // 输出模板


2.2个或多个分页的写法：

//以利用Page类和limit方法为例:
$User = M('User'); // 实例化User对象
 // 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
$list = $User->where('status=1')->order('create_time')->page($_GET['p'].',25')->select();
$this->assign('list',$list);// 赋值数据集
$count      = $User->where('status=1')->count();// 查询满足要求的总记录数
$Page       = new \Libm\Common\Page($count,25,"p1");// 实例化分页类 传入总记录数、每页显示的记录数、分页参数名
$show       = $Page->show();// 分页显示输出
$this->assign('page',$show);// 赋值分页输出


$Member = M('Member'); // 实例化User对象
 // 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
$list2 = $Member->where('status=1')->order('create_time')->page($_GET['p'].',25')->select();
$this->assign('list2',$list2);// 赋值数据集
$count2      = $Member->where('status=1')->count();// 查询满足要求的总记录数
$Page2       = new \Libm\Common\Page($count2,25,"p2");// 实例化分页类 传入总记录数、每页显示的记录数、分页参数名
$show2       = $Page2->show();// 分页显示输出
$this->assign('page2',$show2);// 赋值分页输出


$this->display(); // 输出模板