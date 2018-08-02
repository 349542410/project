<?php
namespace Lib10\Idcardali;
class AliIdcard{
    private $appcode;
    private $err_info;
    private $url;
    public function __construct()
    {
        $this->appcode = C('ALIOCR')['appcode'];
        $this->url = C('ALIOCR')['URL'];
    }

    public function getError(){
        return $this->err_info;
    }

    // 智能识别身份证头像面
    /**
     * @param $files    身份证头像面
     * @return bool
     */
    public function photo($files){
        if (empty($files)){
            //$this->err_info = '请传入身份证头像面图片路径';
            $this->err_info = 'idcard_order_photo_route';
            return false;
        }
        if (!file_exists($files)){
            //$this->err_info = '请传入身份证头像面图片';
            $this->err_info = 'idcard_order_photo';
            return false;
        }

        $type = 'face';     //身份证正反面类型:face/back
        $res_photo = $this->pub_idcard($files, $type);
        $res = json_decode($res_photo, true);
        //return $res;
        if ($res['success'] == 1){
            $row['name']          = $res['name'];
            $row['sex']           = $res['sex'];
            $row['nation']        = $res['nationality'];
            $birth = $res['birth'];
//            $year = substr($birth, 0,4);
//            $month = substr($birth,4, 2);
//            $day = substr($birth,6, 2);
//            $row['birth']         = $year .'-' . $month . '-' . $day;
            $row['birth']         = $birth;
            $row['address']       = $res['address'];
            $row['idcard']        = $res['num'];
            return $row;

        }else{
            //$this->err_info = '身份证头像面识别失败！';
            $this->err_info = 'idcard_photo_failure';
            return false;
        }
    }


    // 智能识别身份证国徽面
    /**
     * @param $files    身份证国徽面
     * @return bool
     */
    public function national_emblem($files){
        if (empty($files)){
            //$this->err_info = '请传入身份证国徽面图片路径';
            $this->err_info = 'idcard_order_national_route';
            return false;
        }
        if (!file_exists($files)){
            //$this->err_info = '请传入身份证国徽面图片';
            $this->err_info = 'idcard_order_national';
            return false;
        }
        $type = 'back';     //身份证正反面类型:face/back
        $res_photo = $this->pub_idcard($files, $type);
        $res = json_decode($res_photo, true);
        if ($res['success']){
            $row['authority']          = $res['issue'];
            $start_date = $res['start_date'];
//            $year = substr($start_date, 0,4);
//            $month = substr($start_date,4, 2);
//            $day = substr($start_date,6, 2);
//            $row['valid_date_start']         = $year .'-' . $month . '-' . $day;
            $row['valid_date_start']         = $start_date;
//            $year = substr($end_date, 0,4);
//            $month = substr($end_date,4, 2);
//            $day = substr($end_date,6, 2);
//            $row['valid_date_end']         = $year .'-' . $month . '-' . $day;
            if($res['end_date'] == '长期'){
                $end_date = '20990101';
            }else{
                $end_date = $res['end_date'];
            }
            $row['valid_date_end']         = $end_date;
            return $row;

        }else{
            //$this->err_info = '身份证国徽面识别失败！';
            $this->err_info = 'idcard_national_failure';
            return false;
        }
    }


    // 检验身份证图片是否与名字+身份证ID

    /**
     * @param $photos   身份证头像面
     * @param $national     身份证国徽面
     * @param $idcard_name  身份证姓名
     * @param $idcard_idno  身份证号码
     * @return array|bool
     */
    public function authentication_idcard($photos, $national, $idcard_name, $idcard_idno){

        if (empty($photos)){
            //$this->err_info = '请传入身份证头像面图片路径';
            $this->err_info = 'idcard_order_photo_route';
            return false;
        }
        if (!file_exists($photos)){
            //$this->err_info = '请传入身份证头像面图片';
            $this->err_info = 'idcard_order_photo';
            return false;
        }
        if (empty($national)){
            //$this->err_info = '请传入身份证国徽面图片路径';
            $this->err_info = 'idcard_order_national_route';
            return false;
        }
        if (!file_exists($national)){
            //$this->err_info = '请传入身份证国徽面图片';
            $this->err_info = 'idcard_order_national';
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

        $photo = $this->photo($photos);
        if(!$photo){
            return false;
        }
        $national_emblem = $this->national_emblem($national);
        if(!$national_emblem){
            return false;
        }
        $row = array_merge($photo, $national_emblem);

        if ($row['name'] != $idcard_name){
            //$this->err_info = '身份证名字与收件人姓名不一致 请检查填写是否有误';
            $this->err_info = 'inconsistency_of_names';
            return false;
        }
        if($row['idcard'] != strtoupper($idcard_idno)){
            //$this->err_info = '身份证号码与收件人身份证号码不一致 请检查填写是否有误';
            $this->err_info = 'number_inconsistencies';
            return false;
        }
        //验证正反面是否一致
        if(!empty($row['authority'])){
            $security = strrpos($row['authority'], '公安');
            $city = substr($row['authority'], 0, $security);
            if(strpos($row['address'], $city) === false){
                //$this->err_info = '身份证头像面与身份证国徽面不一致 请检查上传是否正确';
                $this->err_info = 'idcard_inconsistencies';
                return false;
            };
        }
        if(empty($row['valid_date_start']) || empty($row['valid_date_end'])){
            $this->err_info = 'idcard_inconsistencies';
            return false;
        }
        return $row;
    }

    // 检验身份证图片是否正确

    /**
     * @param $photos   身份证头像面
     * @param $national 身份证国徽面
     * @return array|bool
     */
    public function authentication($photos, $national){
        if (empty($photos)){
            //$this->err_info = '请传入身份证头像面图片路径';
            $this->err_info = 'idcard_order_photo_route';
            return false;
        }
        if (!file_exists($photos)){
            //$this->err_info = '请传入身份证头像面图片';
            $this->err_info = 'idcard_order_photo';
            return false;
        }
        if (empty($national)){
            //$this->err_info = '请传入身份证国徽面图片路径';
            $this->err_info = 'idcard_order_national_route';
            return false;
        }
        if (!file_exists($national)){
            //$this->err_info = '请传入身份证国徽面图片';
            $this->err_info = 'idcard_order_national';
            return false;
        }

        $photo = $this->photo($photos);
        if(!$photo){
            return false;
        }
        $national_emblem = $this->national_emblem($national);
        if(!$national_emblem){
            return false;
        }
        $row = array_merge($photo, $national_emblem);
        //验证正反面是否一致
        if (!empty($row['authority'])){
            $security = strrpos($row['authority'], '公安');
            $city = substr($row['authority'], 0, $security);
            if(strpos($row['address'], $city) === false){
                //$this->err_info = '身份证头像面与身份证国徽面不一致 请检查上传是否正确';
                $this->err_info = 'idcard_inconsistencies';
                return false;
            };
        }
        if(empty($row['valid_date_start']) || empty($row['valid_date_end'])){
            $this->err_info = 'idcard_inconsistencies';
            return false;
        }


        return $row;
    }

    /**
     * 校验身份证识别数据与名字跟身份证号码是否一致
     * @param $idcard   身份识别数据
     * @param $idcard_name  姓名
     * @param $idcard_idno  身份证号码
     */
    public function idcard_check($idcard, $idcard_name, $idcard_idno){
        if(empty($idcard)){
            //$this->err_info = '请上传身份证';
            $this->err_info = 'please_enter_your_idcard';
            return false;
        }

        if ($idcard['name'] != $idcard_name){
            //$this->err_info = '身份证名字与收件人姓名不一致 请检查填写是否有误';
            $this->err_info = 'inconsistency_of_names';
            return false;
        }
        if($idcard['idcard'] != strtoupper($idcard_idno)){
            //$this->err_info = '身份证号码与收件人身份证号码不一致 请检查填写是否有误';
            $this->err_info = 'number_inconsistencies';
            return false;
        }
        //验证正反面是否一致
        if(!empty($idcard['authority'])){
            $security = strrpos($idcard['authority'], '公安');
            $city = substr($idcard['authority'], 0, $security);
            if(strpos($idcard['address'], $city) === false){
                //$this->err_info = '身份证头像面与身份证国徽面不一致 请检查上传是否正确';
                $this->err_info = 'idcard_inconsistencies';
                return false;
            };
        }
        if(empty($idcard['valid_date_start']) || empty($idcard['valid_date_end'])){
            $this->err_info = 'idcard_inconsistencies';
            return false;
        }
        return true;
    }



    /**
     * @param $files    身份证号图片
     * @param $type     查询类型  face/back  face为头像面  back为国徽面
     * @return bool|string
     */
    private function pub_idcard($files, $type){
        $url = $this->url;
        $appcode = $this->appcode;
        $file = $files;
        //如果输入带有inputs, 设置为True，否则设为False
        $is_old_format = false;
        //如果没有configure字段，config设为空
        $config = array(
            "side" => $type
        );
        //$config = array()


        if($fp = fopen($file, "rb", 0)) {
            $binary = fread($fp, filesize($file)); // 文件读取
            fclose($fp);
            $base64 = base64_encode($binary); // 转码
        }
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        //根据API的要求，定义相对应的Content-Type
        array_push($headers, "Content-Type".":"."application/json; charset=UTF-8");
        $querys = "";
        if($is_old_format == TRUE){
            $request = array();
            $request["image"] = array(
                "dataType" => 50,
                "dataValue" => "$base64"
            );

            if(count($config) > 0){
                $request["configure"] = array(
                    "dataType" => 50,
                    "dataValue" => json_encode($config)
                );
            }
            $body = json_encode(array("inputs" => array($request)));
        }else{
            $request = array(
                "image" => "$base64"
            );
            if(count($config) > 0){
                $request["configure"] = json_encode($config);
            }
            $body = json_encode($request);
        }
        $method = "POST";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$url, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        $result = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $rheader = substr($result, 0, $header_size);
        $rbody = substr($result, $header_size);

        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        if($httpCode == 200){
            if($is_old_format){
                $output = json_decode($rbody, true);
                $result_str = $output["outputs"][0]["outputValue"]["dataValue"];
            }else{
                $result_str = $rbody;
            }
            return $result_str;
            //printf("result is :\n %s\n", $result_str);
        }else{
            $rew['httpCode'] = $httpCode;
            $rew['rbody'] = $rbody;
            $rew['rheader'] = $rheader;
            return $rew;
//            printf("Http error code: %d\n", $httpCode);
//            printf("Error msg in body: %s\n", $rbody);
//            printf("header: %s\n", $rheader);
        }
    }


}