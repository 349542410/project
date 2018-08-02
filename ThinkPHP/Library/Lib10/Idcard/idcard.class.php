<?php
namespace Lib10\Idcard;
require_once __DIR__ . '/index.php';
use QcloudImage\CIClient;
class idcard{
    private $appid;
    private $secretId;
    private $secretKey;
    private $bucket;
    private $client;
    private $code;

    private $err_info;

    public function __construct(){
        $this->appid       = C('OCR')['app_id'];
        $this->secretId    = C('OCR')['secret_id'];
        $this->secretKey   = C('OCR')['secret_key'];
        $this->bucket      = C('OCR')['bucket'];
        $this->client = new CIClient($this->appid, $this->secretId, $this->secretKey, $this->bucket);
        $this->client->setTimeout(30);
        $this->code = require_once __DIR__ . '/code.php';
    }

    public function getError(){
        return $this->err_info;
    }



    // 智能识别身份证头像面
    public function photo($files){
        $photo[]              = $files;
        if (empty($photo)){
            //$this->err_info = '请传入身份证头像面图片路径';
            $this->err_info = 'idcard_order_photo_route';
            return false;
        }
        $res_photo = $this->client->idcardDetect(array('files'=>$photo), 0);
        $res = json_decode($res_photo, true);
        if ($res['result_list']['0']['code'] != 0){
            $this->err_info = $this->code[$res['result_list']['0']['code']];
            return false;
        }else{
            $row['name']          = $res['result_list']['0']['data']['name'];
            $row['sex']           = $res['result_list']['0']['data']['sex'];
            $row['nation']        = $res['result_list']['0']['data']['nation'];
            $row['birth']         = str_replace('/', '-', $res['result_list']['0']['data']['birth']);
            $row['address']       = $res['result_list']['0']['data']['address'];
            $row['idcard']        = $res['result_list']['0']['data']['id'];
            return $row;
        }
    }

    // 智能识别身份证国徽面
    public function national_emblem($files){
        $national_emblem[] = $files;
        if (empty($national_emblem)){
            //$this->err_info = '请传入身份证国徽面图片路径';
            $this->err_info = 'idcard_order_national_route';
            return false;
        }
        $res_nation = $this->client->idcardDetect(array('files'=>$national_emblem), 1);
        $res = json_decode($res_nation, true);
        if ($res['result_list']['0']['code'] != 0){
            $this->err_info = $this->code[$res['result_list']['0']['code']];
            return false;
        }else{
            $valid_date = explode('-',$res['result_list']['0']['data']['valid_date']);
            $valid_date_start = str_replace('.', '-', $valid_date['0']);
            $valid_date_end = str_replace('.', '-', $valid_date['1']);
            $row['authority']          = $res['result_list']['0']['data']['authority'];
            $row['valid_date_start']  = $valid_date_start;
            $row['valid_date_end']    = $valid_date_end;
            return $row;
        }
    }

    // 检验身份证图片是否与名字+身份证ID
    public function authentication_idcard($photos, $national, $idcard_name, $idcard_idno){
        $photo[]              = $photos;
        $national_emblem[]    = $national;
        if (empty($photo)){
            //$this->err_info = '请传入身份证头像面图片路径';
            $this->err_info = 'idcard_order_photo_route';
            return false;
        }
        if (empty($national_emblem)){
            //$this->err_info = '请传入身份证国徽面图片路径';
            $this->err_info = 'idcard_order_national_route';
            return false;
        }
        if (empty($idcard_name)){
            //$this->err_info = '请传入身份证姓名';
            $this->err_info = 'idcard_name';
            return false;
        }
        if (empty($idcard_idno)){
            //$this->err_info = '请传入身份证号码';
            $this->err_info = 'idcard_number';
            return false;
        }

        $row = $this->pub_idcard($photo, $national_emblem);

        if(!$row){
            return false;
        }
        if ($row['name'] != $idcard_name){
            //$this->err_info = '身份证名字与收件人姓名不一致 请检查填写是否有误';
            $this->err_info = 'inconsistency_of_names';
            return false;
        }
        if($row['idcard'] != strtoupper($idcard_idno)){
            //$this->err_info = '身份证号码与收件人身份证号码不一致 请检测填写是否有误';
            $this->err_info = 'number_inconsistencies';
            return false;
        }
        //验证正反面是否一致
        $security = strrpos($row['authority'], '公安');
        $city = substr($row['authority'], 0, $security);
        if(strpos($row['address'], $city)  === false){
            //$this->err_info = '身份证头像面与身份证国徽面不一致 请检查上传是否正确';
            $this->err_info = 'idcard_inconsistencies';
            return false;
        };

        return $row;
    }

    // 检验身份证图片是否与名字一致
    public function authentication($photos, $national){
        $photo[]              = $photos;
        $national_emblem[]    = $national;
        if (empty($photo)){
            //$this->err_info = '请传入身份证头像面图片路径';
            $this->err_info = 'idcard_order_photo_route';
            return false;
        }
        if (empty($national_emblem)){
            //$this->err_info = '请传入身份证国徽面图片路径';
            $this->err_info = 'idcard_order_national_route';
            return false;
        }
        $row = $this->pub_idcard($photo, $national_emblem);
        //验证正反面是否一致
        $security = strrpos($row['authority'], '公安');
        $city = substr($row['authority'], 0, $security);
        if(strpos($row['address'], $city)  === false){
            //$this->err_info = '身份证头像面与身份证国徽面不一致 请检查上传是否正确';
            $this->err_info = 'idcard_inconsistencies';
            return false;
        };

        return $row;
    }


    private function pub_idcard($photo, $national_emblem){
        $res_photo = $this->client->idcardDetect(array('files'=>$photo), 0);
        $res = json_decode($res_photo, true);
        if ($res['result_list']['0']['code'] != 0){
            $this->err_info = $this->code[$res['result_list']['0']['code']];
            return false;
        }
        $row['name']        = $res['result_list']['0']['data']['name'];
        $row['sex']         = $res['result_list']['0']['data']['sex'];
        $row['nation']      = $res['result_list']['0']['data']['nation'];
        $row['birth']       = str_replace('/', '-', $res['result_list']['0']['data']['birth']);
        $row['address']     = $res['result_list']['0']['data']['address'];
        $row['idcard']      = $res['result_list']['0']['data']['id'];

        $res_nation = $this->client->idcardDetect(array('files'=>$national_emblem), 1);
        $res = json_decode($res_nation, true);

        if ($res['result_list']['0']['code'] != 0){
            $this->err_info = $this->code[$res['result_list']['0']['code']];
            return false;
        }
        $valid_date = explode('-',$res['result_list']['0']['data']['valid_date']);
        $valid_date_start = str_replace('.', '-', $valid_date['0']);
        $valid_date_end = str_replace('.', '-', $valid_date['1']);
        $row['authority']          = $res['result_list']['0']['data']['authority'];
        $row['valid_date_start']  = $valid_date_start;
        $row['valid_date_end']    = $valid_date_end;

        return $row;
    }


}