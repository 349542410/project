<?php

    namespace Lib11\PHPMailer;

    use Think\Log;

    class PHPMailerTools{

        public function sendMail($args){

            // $args = array(
            //     'to' => array(
            //         '947661456@qq.com',     //收件人地址，可填写多个
            //     ),
            //     'FromName' => '',    //发件人姓名，可选
            //     'title' => 'test',
            //     'content' => 'hello world',
            //     'Subject' => '',     //主题,可选
            //     'attachment' => array(
            //         '',      //附件的路径，可选
            //     ),
            // );


            //引入PHPMailer的核心文件

            require("./ThinkPHP/Library/Lib11/PHPMailer/phpmailer/PHPMailerAutoload.php");
            $config = require("./ThinkPHP/Library/Lib11/PHPMailer/config.php");
//
//             print_r($result);
//             die;

            PHPMailerAutoload('phpmailer');
            PHPMailerAutoload('smtp');
            //实例化PHPMailer核心类
            $mail = new \PHPMailer();
            // $mail = new PHPMailer(true);
            // dump($mail);
            // die;

                

                //Server settings

                $mail->SMTPDebug = $config['SMTPDebug'];              // Enable verbose debug output
                $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Host = $config['Host'];                        // Specify main and backup SMTP servers
                $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
                $mail->Username = $config['Username'];                // SMTP username
                $mail->Password = $config['Password'];                // SMTP password
                $mail->Port = 465;                                    // TCP port to connect to
                if(!empty($args['Subject'])){
                    $mail->Subject = $args['Subject'];
                }

                $mail->FromName = ( (!empty($args['FromName'])) ? $args['FromName'] : $config['FromName'] );        //发件人名称
                $mail->From = $config['From'];                        // 以下From地址失败：root @ localhost：MAIL FROM命令失败，邮件地址必须与授权用户相同

                //Recipients
                // $mail->setFrom('from@example.com', 'Mailer');         // 设置发件人地址
                // $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
                // $mail->addAddress('ellen@example.com');               // Name is optional
                foreach($args['to'] as $k=>$v){
                    $mail->addAddress($v);
                }
                $mail->addReplyTo($config['ReplyTo'],$config['FromName']);          // 增加一个回复地址(别人回复时的地址)
                
                // $mail->addCC('cc@example.com');                      // 抄送地址
                if(!empty($args['CC'])){
                    foreach($args['CC'] as $k=>$v){
                        $mail->addCC($v);
                    }
                }
                
                // $mail->addBCC('bcc@example.com');                    // 密送地址
                if(!empty($args['BCC'])){
                    foreach($args['BCC'] as $k=>$v){
                        $mail->addBCC($v);
                    }
                }

                //Attachments
                // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
                // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
                if(!empty($args['attachment'])){
                    foreach($args['attachment'] as $k=>$v){
                        $mail->addAttachment($v);
                    }
                }

                //Content
                if(strtolower($args['type'])=='html'){
                    $mail->isHTML(true);                                  // Set email format to HTML
                    $mail->Body = $args['content'];
                }else{
                    $mail->isHTML(false);
                    $mail->AltBody = $args['content'];
                }
                // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
                $mail->CharSet = $config['CharSet'];
                $mail->Subject = $args['title'];
                $mail->XMailer = "Author: 541664399@qq.com";


                $sign = $mail->send();

                if($sign){
                    return array(
                        'success' => true,
                        'error' => '',
                    );
                }else{
                    return array(
                        'success' => false,                 //成功返回true，失败返回false
                        'error' => $mail->ErrorInfo,        //成功返回空字符串，失败返回错误信息
                    );
                }

        }

    }

    