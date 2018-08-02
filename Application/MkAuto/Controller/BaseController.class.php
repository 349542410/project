<?php
/**
 * PDA专用
 */
/*Man 20150828*/
namespace MkAuto\Controller;
use Think\Controller;
header('Content-Type:text/html; charset=utf-8');
class BaseController extends Controller {
	public function _initialize(){

        //根据session检验登陆权限
        $MKinfo 		= session('MKinfo');
        $this->MKinfo 	= $MKinfo;       //全局
        $str 			= 'false';

        //验证是否属于指定的编码
        $check_arr = explode(',', $this->usertype);
        if(trim($MKinfo['tname'])=='' || trim($MKinfo['ssid'])==''){
        	$str 		= '您好久没用了，再登录一下吧！';
        }else if(!in_array($MKinfo['usertype'], $check_arr)){//验证是否属于指定的编码
        	$str 		= '您到了一个无人地带，重新进来吧！';
        }
        //$str 		= '你好久没使用了，再登录一下吧！';

        if($str !='false'){
            $data = array(
            	'mkno'		=> '',
            	'status'	=> '0',
            	'code'		=> -9,
            	'codestr'	=> $str,
            );  	
        	if(strtoupper(I('fm'))=='PDA'){
                $this->ajaxReturn($data);
            }else{
            	$this->error($str,U('Index/index'));
            }
            die();
        }
	}
}