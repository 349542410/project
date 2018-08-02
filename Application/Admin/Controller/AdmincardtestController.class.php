<?php
namespace Admin\Controller;
use Think\Controller;
class AdmincardtestController extends AdminbaseController{

    public function idcard(){

        $this->display();
    }

    public  function upload_add(){
//        dump($_FILES);
//        //exit;
//        $upload           = new \Think\Upload();// 实例化上传类
//        //$upload->maxSize  = 1048576*50 ;// 设置附件上传大小
//        $upload->exts     = array('csv', 'xls', 'xlsx');// 设置附件上传类型
//        $upload->rootPath = ADMIN_ABS_FILE; //设置文件上传保存的根路径
//        $upload->savePath = C('UPLOADS'); // 设置文件上传的保存路径（相对于根路径）
//        $upload->autoSub  = false; //自动子目录保存文件
//        $upload->subName  = array('date','Ymd');
//        $upload->saveName = array('uniqid',mt_rand()); //设置上传文件名
//
//        $info = $upload->upload();
        $lang = str_replace('\\', '/', dirname(dirname(dirname(__FILE__))));
        $lang =  $lang .'/WebUser/Lang/zh-cn.php';
        $lang = require_once($lang);
        //$lngname = $mkl['MKLINES'];
        //print_r($lngname);
        //exit;
        $obj = new \Lib10\Idcardali\AliIdcard();
        if($_FILES['batch']['error']==0){
            $res = $obj->photo($_FILES['batch']['tmp_name']);
            if(!$res){
                dump($lang[$obj->getError()]);
            }else{
                dump($res);
            }
        }
        if ($_FILES['delivery']['error']==0){
            $res = $obj->national_emblem($_FILES['delivery']['tmp_name']);
            if(!$res){
                dump($lang[$obj->getError()]);
            }else{
                dump($res);
            }
        }

//        $res =  $obj->authentication_idcard($_FILES['batch']['tmp_name'], $_FILES['delivery']['tmp_name'], '单春华', '130229198902105047');
//        if(!$res){
//            dump($lang[$obj->getError()]);
//        }else{
//            dump($res);
//        }

        $obj = new \Lib10\Idcard\idcard();
        if($_FILES['batch']['error']==0){
            $res = $obj->photo($_FILES['batch']['tmp_name']);
            if(!$res){
                dump($lang[$obj->getError()]);
            }else{
                dump($res);
            }
        }
        if ($_FILES['delivery']['error']==0){
            $res = $obj->national_emblem($_FILES['delivery']['tmp_name']);
            if(!$res){
                dump($lang[$obj->getError()]);
            }else{
                dump($res);
            }
        }




        //print_r($info);
        exit;


    }


}





