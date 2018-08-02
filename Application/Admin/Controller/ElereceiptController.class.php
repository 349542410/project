<?php
namespace Admin\Controller;
use Think\Controller;
class ElereceiptController extends AdminbaseController{
	public function index(){

	}
	public function show(){
		/*
			设计要求：
			1.传入参数有三种方式
				a:userid,userpwd,现暂时使用 userid=mg01 userpwd=mg01001,mkno（这个要在 官方实现，暂无）
				b:mkno,防伪码 （这个要在 官方实现）
				c:直接在后台打开,传入mkno
			2.不管使用何种传入方式，
				a:检查是否已生成过防伪码,存在则直接读取，否则生成并保存到数据库中 mkno,防伪码,方式(admin后台,web),操作人(后台打开)
				b:查询tran_list 读取相关资料并显示，查询logs，state=8 的 mstr1,optime
				c:后台的用弹窗显示,web的直接打开一个新窗口独立显示
		*/
		$this->display();
	}
}
