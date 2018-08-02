<?php

    /*
    *   下载文件
    *   liao ya di
    *   create time : 2018-1-19
    */

    namespace WebUser\Controller;

    class DownloadController extends BaseController{

        private $mimetypes = array(

            'doc' => 'application/msword',

            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    
            'xls' => 'application/vnd.ms-excel',

            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    
            'ppt' => 'application/vnd.ms-powerpoint',

            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    
            'pdf' => 'application/pdf',
    
            'txt' => 'text/plain',

    
            'pgn' => 'application/x-chess-pgn',

            'swf' => 'application/x-shockwave-flash',

            'bmp' => 'image/bmp',
    
            'gif' => 'image/gif',
    
            'jpeg' => 'image/jpeg',
    
            'jpg' => 'image/jpeg',
    
            'png' => 'image/png',


            'mp3' => 'audio/mpeg',
    
            'rm' => 'audio/x-pn-realaudio',
    
            'wav' => 'audio/x-wav',

            'mov' => 'video/quicktime',

            'avi' => 'video/x-msvideo',
    
            'movie' => 'video/x-sgi-movie',
            
    
            'css' => 'text/css',
    
            'html' => 'text/html',

            'xhtml' => 'application/xhtml+xml',

            'xsl' => 'text/xml',
    
            'xml' => 'text/xml',
    

            'exe' => 'application/octet-stream',

            'zip' => 'application/zip',
    
    );
    
        //下载文件
        public function fileUrl(){

            // name => 下载时文件名
            if(!empty($_GET['name']) && !empty($_GET['type']) && !empty($this->mimetypes[trim(I('get.type'))])){

                if(empty(session('user_id'))){
                    $platform = '/guest/';
                }else{
                    $platform = '/user/';
                }

                $file_name =  trim(I('get.name')) . '.' . trim(I('get.type'));
                
                // 获取文件路径
                $path = C('tmp_download_path') . '/Download' . $platform . $file_name;
                // 打开文件
                $files = file_get_contents($path);

                // dump($files);
                // die;

                if(!empty($files) && strlen($files)>0){

                    // 设置mime类型
                    header("Content-Type: " . $this->mimetypes[trim(I('get.type'))]);
                    // 设置数据单位
                    header("Accept-Ranges: bytes");
                    // 设置文件长度
                    header("content-length:" . strlen($files));
                    // 设置文件名
                    header("Content-Disposition: attachment;filename=" . $file_name );

                    //开始下载
                    echo $files;
                    die;

                }
      
            }

        }

        //下载模版
        public function download_order_goods(){

            // 获取文件路径
//            $path = dirname(__FILE__) . '/../../../File/webuser/download/' . C('environment') . '/order_goods/' . 'order_goods.xlsx';
//            $files = file_get_contents($path);
//
//            if(!empty($files) && strlen($files)>0){
//
//                // 设置mime类型
//                header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
//                // 设置数据单位
//                header("Accept-Ranges: bytes");
//                // 设置文件长度
//                header("content-length:" . strlen($files));
//                // 设置文件名
//                header("Content-Disposition: attachment;filename=会员后台订单-货品声明导入模板.xlsx" );
//
//                //开始下载
//                echo $files;
//                die;
//
//            }

            if(!empty($_GET['line_id']) && !empty($_GET['name'])){

                // 获取文件路径
//                $path = dirname(__FILE__) . '/../../../File/webuser/download/' . C('environment') . '/import_tmp/' . trim(I('get.line_id')) . '.xlsx';
                $path = WU_ABS_FILE . '/webuser/download/' . C('environment') . '/order_goods';
                $file_name = $path . '/' . trim(I('get.line_id')) . '.xlsx';;
//                dump(WU_ABS_FILE . '/webuser/download/' . C('environment') . '/import_tmp/' . trim(I('get.line_id')) . '.xlsx' );
//                dump($path);die;

                if(!file_exists($path)){
//                    dump(mkdir($path, 0777, true));
                    mkdir($path, 0777, true);
                    // $path =  C('tmp_download_path') . '/mklinetemplate/template.xlsx'; //不存在使用默认模板
                    die;
                }
                // 打开文件
                $files = file_get_contents($file_name);

                if(!empty($files) && strlen($files)>0){

                    // 设置mime类型
                    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
                    // 设置数据单位
                    header("Accept-Ranges: bytes");
                    // 设置文件长度
                    header("content-length:" . strlen($files));
                    // 设置文件名
                    header("Content-Disposition: attachment;filename=" . trim(I('get.name')) . ".xlsx" );

                    //开始下载
                    echo $files;
                    die;

                }

            }

        }


        public function test(){

            $data = array(
                'receiver' => '收件人姓名',
                'reTel' => '收件人电话',
                'MknoKey' => '随机码',
                'time' => time(),
            );

            $conf = array(
                'Host'=>'',
                'Port'=>'',
                'Auth'=>'',
                'overtime'=>'',
            );

            $queue = new \Lib11\Queue\JoinQueue();
            dump($queue->join_queue($data,$conf));
            die;

            $data = array(
                'aaa' => 111,
                'bbb' => 333,
                'test_01' => '0',
                'test_02' => 4567777777,
                'test_03' => 789,
                'test_04' => '101112',
                'ggg' => 'erer'
            );

            $check = new \WebUser\Check\ExampleCheck();

            $res = $check->create($data,false);


            dump($res);

            $success = $check->specific_testing($res);

            dump($success);

            dump($check->getError());

        }


        public function test2(){

            // dump(WU_FILE . );

//            $lis = new \Lib82\LineArea\lineApi();
//            $data['line_id'] = 3;
//            $da = $lis->linearea($data);
//
//            dump($da);

//            $cos = new \Lib11\TencentCOS\Cos();
//            dump($cos -> upload('1'));

            
            // $batch_id = rand(100,999) . date("YmdHis", time()) . rand(100,999);

            // dump($batch_id);
            // echo ord('A');
            // echo chr(ord('A'));

            // echo strlen('AA');

            // $str = "ABCDEFG1H";

            // dump(substr($str, -1, 1));

            
            // dump(substr($str, 0, strlen($str)-1));

            // $data = array(
            //     array('0'=>1, '1'=>2, '2'=>3),
            //     array(),
            //     array(),
            //     array('3'=>1, '4'=>2, '5'=>3),
            // );

            // $excel = new \WebUser\PHPExcel\PHPExcel();
            // $res = $excel->write($data);

        }

        /**
         * 检测阿里身份证识别方法
         */
        public function test_ali_idno(){

            $obj = new \Lib10\Idcardali\AliIdcard();

            $this->display("test/test_ali_idno");

            if($_FILES['file_two']['error']==0 && $_FILES['file_one']['error']==0){
                $res = $obj->authentication($_FILES['file_one']['tmp_name'],$_FILES['file_two']['tmp_name']);
                if(!$res){
                    dump($obj->getError());
                }else{
                    dump($res);
                }
            }

        }

        public function test_session(){
            
            dump(date('Ymd', time() + 14*ONE_DAY));
            dump(date('Ymd', time()) + 14);
            // session('mkuser', null);
        }

        public function check_session(){
            if(empty(session('mkuser'))){
                echo json_encode(array('status'=>true));
            }else{
                echo json_encode(array('status'=>false));
            }
        }

        public function test_idcard_auth(){

            $obj = new \Lib10\Idcardno\AliIdcardno();
            $name = ((!empty($_GET['name'])) ? $_GET['name'] : '');
            $idcardno = ((!empty($_GET['idcardno'])) ? $_GET['idcardno'] : '');
            if(!empty($name) && !empty($idcardno)){
                $result = $obj->IdentificationCard($name, $idcardno);
            }

            dump($obj->getError());
            dump($result);

        }
    
    }