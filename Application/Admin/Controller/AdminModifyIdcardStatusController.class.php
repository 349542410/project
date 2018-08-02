<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/29
 * Time: 9:33
 */
namespace Admin\Controller;
use Think\Controller;
class AdminModifyIdcardStatusController extends AdminbaseController
{
    public $client;
    public $writes;

    function __construct()
    {
        parent::__construct();
        vendor('Hprose.HproseHttpClient');
        $this->client = new \HproseHttpClient(C('RAPIURL') . '/AdminModifyIdcardStatus');        //读取、查询操作
        //$this->writes = new \HproseHttpClient(C('WAPIURL').'/AdminCollectPoint');		//添加，修改

    }
    //
    public function index(){

        $this->display();
    }


    public function update(){
        $type = I('type');
        $p = I('get.p');

        $data['type'] = !empty($type) ? $type : 0;
        //$data['epage'] = C('EPAGE');
        $data['epage'] = 1000;
        $data['p'] = $p;
        set_time_limit(0);
        $res = $this->client->update($data);

        $count = $res['count'];
        $list = $res['list'];

//        UPDATE yoiurtable
//        SET dingdan = CASE id
//            WHEN 1 THEN 3
//            WHEN 2 THEN 4
//            WHEN 3 THEN 5
//            END
//        WHERE id IN (1,2,3)
        $sqlstr = 'UPDATE mk_tran_ulist SET id_img_status = CASE id';
        foreach ($list as $key => $value){
            $front_id_img = WU_FILE .$value['front_id_img'];
            $back_id_img = WU_FILE .$value['back_id_img'];

            $front = @file_get_contents($front_id_img);
            $back = @file_get_contents($back_id_img);

            if(strlen($front) == 0 || strlen($back) == 0){
                //身份证图片未上传，检验身份证图片上传状态
                if($value['id_img_status'] == 100){
                    //$data[$key]['id'] = $value['id'];
                    //$data[$key]['id_img_status'] = 0;
                    $da[] = $value['id'];
                    $sqlstr .= ' WHEN '.$value['id'].' THEN 0 ';
                }
            }else{
                if($value['id_img_status'] != 100){
                    //$data[$key]['id'] = $value['id'];
                    //$data[$key]['id_img_status'] = 100;
                    $da[] = $value['id'];
                    $sqlstr .= ' WHEN '.$value['id'].' THEN 100 ';
                }
            }
        }

        //判断数组是否为空，不为空可以更新
        if(!empty($da)){
            $id = implode(',',  $da);
            $sqlstr .= ' END WHERE id IN ('.$id.')';
            usleep(3);  //延时3毫秒执行
            $res = $this->client->update_data($sqlstr);

        }
        $nub = $count / C('EPAGE');
        if(!is_int($nub)){
            $nub += 1;
        }
        if($nub == $p){
            set_time_limit(30);
            echo '订单审核状态修改成功！';
            exit;
        }else{
            $p++;
            usleep(3);  //延时3毫秒执行
            $random = 'random_' . str_pad(mt_rand(0, 99999999), 8, "0", STR_PAD_BOTH); //生成8位不一样的随机数，作用:解决浏览器循环访问限制
            $this->redirect('AdminModifyIdcardStatus/update', array('type' => $type, 'p' => $p, $random => 1),0);
        }

    }

}