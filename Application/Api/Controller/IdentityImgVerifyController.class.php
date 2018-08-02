<?php

namespace Api\Controller;
use Think\Controller\HproseController;

/**
 * 公共类
 * 验证身份证图片、姓名、身份证号码是否一致
 * 获取身份证图片上的信息
 */

class IdentityImgVerifyController extends HproseController
{
    
    private $err_info;

    private function getError()
    {
        return $this->err_info;
    }


    // 工具方法
    private function exec($name, $idno, $imgFront, $imgContrary)
    {
        if(empty($imgFront))
        {
            $this->err_info = 'idcard_no_national';
            return $this->infoReturn(false);
        }
        if(empty($imgContrary))
        {
            $this->err_info = 'idcard_no_national';
            return $this->infoReturn(false);
        }
        if(empty($name))
        {
            $this->err_info = 'idcard_name_not_empty';
            return $this->infoReturn(false);
        }

        $data = array(
            'name' => $name,
            'idno' => $idno,
            'imgFront' => $imgFront,
            'imgContrary' => $imgContrary,
        );
        if (empty($data['idno'])) {
        $this->err_info = 'idcard_not_empty';
        return $this->infoReturn(false);
         }
        // 验证身份证是否正确
        $idno = certificate($data['idno']);
        if($idno == false)
        {
            $this->err_info = 'idcard_wrong';
            return $this->infoReturn(false);
        }
        //验证身份证是否为空

        //识别身份证正面
        $obj = new \Lib10\Idcardali\AliIdcard();

        //识别身份证正面反面与名字和身份证号码
        $national = $data['imgFront'];
        $photos = $data['imgContrary'];
        $idcard_name = $data['name'];
        $idcard_idno = $data['idno'];

        $res = $obj->authentication_idcard($photos, $national, $idcard_name, $idcard_idno);
        if(!$res){
            $this->err_info = $obj->getError();
            return $this->infoReturn(false);
        }else{
            return $this->infoReturn(true);
        }
    }

    // 验证身份证和图片
    public function Verify($name, $idno, $data)
    {
        if(empty($name))
        {
            $this->err_info = 'idcard_name_not_empty';
            return $this->infoReturn(false);
        }
        if(empty($idno))
        {
            $this->err_info = 'idcard_not_empty';
            return $this->infoReturn(false);
        }
        if(empty($data))
        {
            $this->err_info = 'idcard_no_empty';
            return $this->infoReturn(false);
        }
        return $this->exec($name, $idno, $data['imgFront'], $data['imgContrary']);
    }


    // 私有工具方法
    private function infoReturn($status){
        if($status){
            return [
                'status' => true,
                'err_info' => '',
            ];
        }else{
            return [
                'status' => false,
                'err_info' => $this->getError(),
            ];
        }
    }

    /**
     * @param $idcard //身份证信息
     * @param $idcard_name  //身份证名字
     * @param $idcard_idno  //身份证号码
     * @return array
     */

    public function check_idno($idcard, $idcard_name,$idcard_idno)
    {
        if(empty($idcard))
        {
            //$this->err_info = '请上传身份证';
            $this->err_info = 'please_enter_your_idcard';
            return $this->infoReturn(false);
        }
        if(empty($idcard_name))
        {
            //$this->err_info = '请传入身份证号码';
            $this->err_info = 'idcard_name_not_empty';
            return $this->infoReturn(false);
        }
        if(empty($idcard_idno))
        {
            //$this->err_info = '请传入身份证国徽面图片';
            $this->err_info = 'idcard_not_empty';
            return $this->infoReturn(false);
        }
        $obj = new \Lib10\Idcardali\AliIdcard();
        $res = $obj->idcard_check($idcard, $idcard_name, $idcard_idno);
        if(!$res){
            $this->err_info = $obj->getError();
            return $this->infoReturn(false);
        }else{
            return $this->infoReturn(true);
        }
    }


}