<?php
/**
 * PDA
 */
namespace MkAuto\Controller;
use Think\Controller;
class TextController extends Controller {

    public function index(){
        $tm     = array(
                array(
                    "MKNO"=>"MK881235467US",
                    "weight"=>"2.0" 
                    ),
            );
        // $jn     = new \Org\MK\JSON; 
        $newJSON = new \Org\MK\JSON(C('jconf'));
        $ja     = $jn->build("MKILWeigh","2","20","美国加州",$tm);

        //$curl_url = "api.megao.us/api/PushWeigh";
        $curl_url = "mkapi.meiquick.cn/Api/PushWeigh";
        $post_data = array("MKIL"=>urlencode($newMsg));
        $jas = $jn->post(0,$curl_url,$post_data);
        var_dump($jas);
    }
}