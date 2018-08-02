<?php

    namespace Lib11\Alimail;

    class Alimail{

        private static $accessKey;
        private static $accessSecret;

        private static $action;

        //public
        private static $AccountName;            //控制台创建的发信地址
        private static $AddressType = 1;        //取值范围 0~1: 0 为随机账号，1 为发信地址

        //SingleSendMail
        private static $ReplyToAddress;     //使用管理控制台中配置的回信地址（状态必须是验证通过）
        private static $FromAlias;          //发信人昵称，长度小于 15 个字符
        private static $ToAddress;          //目标地址，多个 email 地址可以用逗号分隔，最多100个地址
        private static $Subject;            //邮件主题
        private static $Content;            //邮件正文
        private static $Type;               //邮件正文的类型，html或者text

        //BatchSendMail
        private static $TemplateName;        //预先创建且通过审核的模板名称
        private static $ReceiversName;       //预先创建且上传了收件人的收件人列表名称
        private static $TagName;             //控制台创建的标签

        public static function init($action='SingleSendMail',$argument=array()){

            // $auth = array(
            //     'accessKey' => '',
            //     'accessSecret' => '',
            // );

            // $action = 'SingleSendMail';     //发送一封邮件
            // $action = 'BatchSendMail';      //批量发送

            // $argument = array(
            //     'AccountName' => '',
            //     'ReplyToAddress' => '',
            //     'FromAlias' => '',
            //     'ToAddress' => '',
            //     'Subject' => '',
            //     'Body' => array(
            //         'Type' => '',
            //         'Content' => '',
            //     ),
            // );

            // $argument = array(
            //     'AccountName' => '',
            //     'TemplateName' => '',
            //     'ReceiversName' => '',
            //     'TagName' => '',
            // );

            include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "conf.php";

            self::$accessKey = $conf['accessKey'];
            self::$accessSecret = $conf['accessSecret'];
            self::$AccountName = $conf['AccountName'];      //控制台创建的发信地址

            if($action === 'BatchSendMail'){
                self::$action = $action;
            }else{
                self::$action = 'SingleSendMail';
            }

            if(self::$action === 'SingleSendMail'){
                self::$ReplyToAddress = $conf['ReplyToAddress'];
                self::$FromAlias = $argument['FromAlias'];
                self::$ToAddress = $argument['ToAddress'];
                self::$Subject = $argument['Subject'];
                self::$Content = $argument['Body']['Content'];
                self::$Type = $argument['Body']['Type'];
            }else{
                self::$TemplateName = $argument['TemplateName'];
                self::$ReceiversName = $argument['ReceiversName'];
                self::$TagName = $argument['TagName'];
            }

            
        }

        public static function exec(){

            include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . self::$action . '.php';

        }

    }