<?php
namespace Lib10\Idcardno;
class AliIdcardno{
    private $appcode;
    private $host;
    private $err_info;
    public function __construct()
    {
        $this->appcode = C('ALIIDCARD')['appcode'];
        $this->host =  C('ALIIDCARD')['URL'];
    }
    public function getError(){
        return $this->err_info;
    }

    /**
     * @param $name     身份证姓名
     * @param $idcardno 身份证号码
     * @return bool
     */
    public function  IdentificationCard($name, $idcardno){
        //检验身份证号码格式是否正确
        $idno =  $this->is_idcard($idcardno);
        if(!$idno){
            //$this->err_info = '身份证号码输入错误,请重新输入';
            $this->err_info = L('id_card_error');
            return false;
        }
        $res = $this->pub_idcard($name, $idcardno);
        if ($res['resp']['code'] == 0){
            $row['name']          = $name;
            $row['sex']           = $res['data']['sex'];
            $row['birth']         = $res['data']['birthday'];
            $row['address']       = $res['data']['address'];
            $row['idcard']        = $idcardno;
            return $row;
        }else{
            $this->err_info = $res['resp']['desc'];
            return false;
        }
        //return $res;
    }

    private function pub_idcard($name, $idcardno){
        //$host = "http://idcard.market.alicloudapi.com";
        //$path = "/lianzhuo/idcard";
        $method = "GET";
        $appcode = $this->appcode;
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = 'cardno='.$idcardno.'&name='.$name.'';
        $bodys = "";
        $url = $this->host . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$this->host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $result = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $rheader = substr($result, 0, $header_size);
        $rbody = substr($result, $header_size);

        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        if($httpCode == 200) {
            $output = json_decode($rbody, true);

            return $output;
        }
        return $result;
    }


    /**
     *
     * Enter description here ...
     * @param $id	身份证号码
     */
    function is_idcard($id)
    {
        $id = strtoupper($id);
        $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
        $arr_split = array();
        if(!preg_match($regx, $id))
        {
            return false;
        }
        if(15==strlen($id)) //检查15位
        {
            $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";

            @preg_match($regx, $id, $arr_split);
            //检查生日日期是否正确
            $dtm_birth = "19".$arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];
            if(!strtotime($dtm_birth))
            {
                return false;
            } else {
                return true;
            }
        }
        else      //检查18位
        {
            $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
            @preg_match($regx, $id, $arr_split);
            $dtm_birth = $arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];
            if(!strtotime($dtm_birth)) //检查生日日期是否正确
            {
                return false;
            }
            else
            {
                //检验18位身份证的校验码是否正确。
                //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
                $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
                $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
                $sign = 0;
                for ( $i = 0; $i < 17; $i++ )
                {
                    $b = (int) $id{$i};
                    $w = $arr_int[$i];
                    $sign += $b * $w;
                }
                $n = $sign % 11;
                $val_num = $arr_ch[$n];
                if ($val_num != substr($id,17, 1))
                {
                    return false;
                } //phpfensi.com
                else
                {
                    return true;
                }
            }
        }

    }



}


