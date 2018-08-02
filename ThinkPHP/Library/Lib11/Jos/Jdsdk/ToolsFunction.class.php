<?php

    namespace Lib11\Jos\Jdsdk;

    class ToolsFunction{

        public function postCurl($url,$body,$header = array(),$type="POST",$debug='false'){

            // 创建一个curl资源
            $ch = curl_init();

            // 设置URL和相应的选项
            curl_setopt($ch,CURLOPT_URL,$url);//设置url

            //1)设置请求头
                // $header = array(
                //     'Content-Type:application/json;charset=utf-8',
                //     'Accept:application/json'
                // );
                // array_push($header, 'Accept:application/json');
                // array_push($header,'Accept-Charset:utf-8');
                // array_push($header, 'Content-Type:application/json;charset=utf-8');
                // array_push($header, 'Content-Length:' . strlen($body));

            //加入重定向
            curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);

            // 启用时会将头文件的信息作为数据流输出
            curl_setopt($ch,CURLOPT_HEADER,0);

            // 设置超时限制防止死循环
            curl_setopt ($ch, CURLOPT_TIMEOUT,30);

            if(strpos($url,'https')!==false){
                // 跳过证书检查
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                // 从证书中检查SSL加密算法是否存在
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            }

            // 设置发起连接前的等待时间，如果设置为0，则无限等待。
            // curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);

            // 将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //加入gzip解析
            curl_setopt($ch,CURLOPT_ENCODING,'gzip');
            // curl_setopt($ch, CURLOPT_ENCODING, "");

            // 设置提交方式
            switch(strtoupper($type)){
                case "GET":
                    curl_setopt($ch,CURLOPT_HTTPGET,true);
                    break;
                case "POST":
                    curl_setopt($ch,CURLOPT_POST,true);
                    break;
                case "PUT":
                    curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"PUT");
                    break;
                case "PATCH":
                    curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"PATCH");
                    break;
                // case "DELETE":
                //     curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"DELETE");
                //     break;
                default:
                    curl_setopt($ch,CURLOPT_POST,true);
                    break;
            }

            // 设置请求体
            if (!empty($body)&&strtoupper($type)!='GET') {  //如果对GET请求设置CURLOPT_POSTFIELDS，则会报405错误
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
                // 全部数据使用HTTP协议中的"POST"操作来发送。
                // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            }

            // 设置请求头
            if(count($header)>0){
                curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
            }

            // "User-Agent: "头的字符串。
            curl_setopt($ch, CURLOPT_USERAGENT, 'SSTS Browser/1.0');
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
            curl_setopt ( $ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)' );

            // 抓取URL并把它传递给浏览器
            $document=curl_exec($ch);
            $document=mb_convert_encoding($document, 'utf-8', 'GBK,UTF-8,ASCII');

            $result = array();
            if(!curl_errno($ch)){
                $result = array(
                    'success' => true,
                    'data' => $document,
                    // 'data' => json_decode($document),
                    'error' => ''
                );
            }else{
                $result = array(
                    'success' => false,
                    'data' => null,
                    'error' => curl_error($ch)
                );
            }

            curl_close($ch);

            return $result;

        }



        //获取自定义header数据
        public function get_http_headers(){

            // 忽略获取的header数据
            $ignore = array('host','accept','content-length','content-type','accept-encoding','user-agent');

            $headers = array();

            foreach($_SERVER as $key=>$value){
                if(substr($key, 0, 5)==='HTTP_'){
                    $key = substr($key, 5);
                    $key = str_replace('_', ' ', $key);
                    $key = str_replace(' ', '-', $key);
                    $key = strtolower($key);

                    if(!in_array($key, $ignore)){
                        $headers[$key] = $value;
                    }
                }
            }

            return $headers;

        }



        //获取所有提交内容
        public function get_http_content(){

            $content = array(
                'GET' => $_GET,
                'POST' => $_POST,
                'HTTP_RAW_POST_DATA' => $GLOBALS['HTTP_RAW_POST_DATA'],
                'input' => file_get_contents("php://input"),
            );

            // dump(file_get_contents("php://input"));
            // dump($content);

            return $content;

        }


        // 组装url参数列表
        public function ass_parameters($url_parameters=array()){

            if(!empty($url_parameters)){
                $arr = array();
                foreach($url_parameters as $k=>$v){
                    $arr[] = $k . '=' . $v;
                }
                return implode('&',$arr);
            }else{
                return '';
            }

        }


        // //php文件锁  高并发
        // public function lock($obj,$callback){

        //     $fp = fopen(APP_PATH . "/Lib11/RB/Tool/write.lock",'r');

        //     //加锁
        //     flock($fp,LOCK_EX);

        //     //回调方法
        //     $obj->$callback();

        //     //释放锁
        //     flock($fp,LOCK_UN);
        //     fclose($fp);

        // }

    }