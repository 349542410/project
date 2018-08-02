<?php
/**
 * 发送邮件功能，但不包括附件的发送
 * 如果只是需要邮件的发送功能，则可以使用此文件
 */
namespace Libm\Common;
class PHPMailer{

    public $Host;//SMTP服务器
    public $Port;//邮件发送端口
	public $Username      = '';//你的邮箱
	public $Password      = '';//你的密码
	public $Subject       = '';//邮件标题
	public $From          = '';//发件人地址（也就是你的邮箱）
	public $FromName      = '';//发件人姓名
	
	public $Body          = '';//正文
	public $Receiver      = '';//收件人
	public $Address       = '';//添加收件人（地址，昵称）设置收件的地址($receiver可随意) 
	public $SMTPSecure    = 'ssl';// 安全协议，默认ssl
	
	public $CharSet       = "UTF-8";   //字符集
	public $Encoding      = "base64";  //编码方式
	public $IsHTML        = true;      //支持html格式内容
	public $SMTPAuth      = true;      //启用SMTP认证

    public function Send(){
    	require_once ("class.phpmailer.php"); //载入PHPMailer类
    	$mail = new \PHPMailer(); //实例化

        $mail->Host     = $this->Host; //SMTP服务器
        $mail->Port     = $this->Port;  //邮件发送端口
        $mail->Username = $this->Username;  //你的邮箱
        $mail->Password = $this->Password;  //你的密码
        $mail->Subject  = $this->Subject;      //邮件标题
        $mail->From     = $this->From;     //发件人地址（也就是你的邮箱）
        $mail->FromName = $this->FromName;       //发件人姓名
        // $mail->AddReplyTo(" 回复地址 ","from");     //设置回复的收件人的地址(from可随意)

        $mail->Body = $this->Body;//邮件正文内容
        $mail->AddAddress($this->Address,$this->Receiver);//添加收件人（地址，昵称）设置收件的地址($receiver可随意) 
        // $mail->AddAttachment($fileurl,$filename); // 添加附件,并指定名称  上传多个文件则调用多次即可
        // $mail->AddEmbeddedImage("logo.jpg", "my-attach", "logo.jpg"); //设置邮件中的图片

        $mail->SMTPAuth = $this->SMTPAuth;  //启用SMTP认证
        $mail->CharSet  = $this->CharSet; //字符集
        $mail->Encoding = $this->Encoding; //编码方式
        $mail->IsHTML($this->IsHTML); //支持html格式内容
        $mail->IsSMTP();//设置采用SMTP方式发送邮件  
        // $mail->SMTPSecure = 'tls';
        $mail->SMTPSecure = $this->SMTPSecure;   // 安全协议
		// dump($mail);die;
		// var_dump($mail -> ErrorInfo);  //查看发送的错误信息 
        
        //发送
        if(!$mail->Send()) {
            return false;
        } else {
            return true;
        }
       

    }






}