<?php
/**
 * 调用邮件发送功能
 */
// namespace Admin\Controller;

class MkilMailer{

  /**
   * 默认配置
   * @var array
   */
  private $config = array(
    //默认配置
    'Encoding'      =>  'base64', //编码方式
    'FromName'      =>  '美快物流', //发件人姓名,用于显示
    'CharSet'       =>  'utf-8',  //设置邮件的字符编码，不然中文乱码

    /*写法二所需配置*/
    // 'Port'          =>  '', //邮件发送端口
    // 'Host'          =>  '', //SMTP服务器 以QQ邮箱为例子
    // 'Username'      =>  '', //发送人邮箱
    // 'Password'      =>  '', //发送人邮箱密码
    // 'From'          =>  '', //发送人地址（也就是你的邮箱）
    // 'Subject'       =>  '', //邮件标题
    // 'Body'          =>  '', //邮件正文
    // 'AddAddress'    =>  array(), //收件人邮箱地址
    // 'AddAttachment' =>  array(), //附件

  );

  public function __construct($config = array()){
    /* 获取配置 */
    $this->config   =   array_merge($this->config, $config);
    
    //检查配置项
    $this->check($this->config);
    // dump($config);die;
  }

  /**
   * 使用 $this->name 获取配置
   * @access public     
   * @param  string $name 配置名称
   * @return multitype    配置值
   */
  public function __get($name) {
    return $this->config[$name];
  }

  /**
   * 设置邮件配置
   * @access public     
   * @param  string $name 配置名称
   * @param  string $value 配置值     
   * @return void
   */
  public function __set($name,$value){
    if(isset($this->config[$name])) {
      $this->config[$name] = $value;
    }
  }

  /**
   * 检查配置
   * @access public     
   * @param  string $name 配置名称
   * @return bool
   */
  public function __isset($name){
    return isset($this->config[$name]);
  }

  /**
   * 调用邮件发送功能
   */
  public function Send(){

    Vendor('PHPMailer.PHPMailerAutoload');
    $mail = new \PHPMailer(); //实例化
    //默认配置值
    $mail->IsSMTP(); // 启用SMTP
    $mail->IsHTML(true); // 是否HTML格式邮件
    $mail->WordWrap = 50; //设置每行字符长度
    $mail->SMTPAuth = true; //启用smtp认证
    $mail->AltBody = "这份邮件包括HTML代码，您需要更换浏览器才能正常查看"; //邮件正文不支持HTML的备用显示
    $mail->Encoding      = $this->Encoding;//编码方式
    $mail->FromName      = $this->FromName; //发件人姓名
    $mail->CharSet       = $this->CharSet; //设置邮件编码
    //传入改变值
    $mail->Host          = $this->HOST;//'smtp.exmail.qq.com'; //smtp服务器的名称（这里以QQ邮箱为例）
    $mail->Port          = $this->PORT;//端口
    $mail->Username      = $this->EMAIL_USER; //你的邮箱名
    $mail->Password      = $this->EMAIL_PWD; //邮箱密码
    $mail->From          = $this->EMAIL_USER; //发件人地址（也就是你的邮箱地址）
    $mail->Subject       = $this->Subject; //邮件主题
    $mail->Body          = $this->Content; //邮件内容  

    $sendto              = $this->config['AddAddress']; //收件人
    foreach($sendto as $k => $v){
      $mail->AddAddress($v[0], $v[1]);
    }

    $addAttachment = $this->config['AddAttachment'];  //附件
    foreach($addAttachment as $ko => $vo){
      $mail->AddAttachment($vo[0],$vo[1]);  //附件地址,附件名字
    }

    return($mail->Send());
  }

  /**
   * 调用邮件发送功能
   */
  public function anotherSend(){

    Vendor('PHPMailer.PHPMailerAutoload');
    $mail = new \PHPMailer(); //实例化
    //默认配置值
    $mail->IsSMTP(); // 启用SMTP
    $mail->IsHTML(true); // 是否HTML格式邮件
    $mail->WordWrap = 50; //设置每行字符长度
    $mail->SMTPAuth = true; //启用smtp认证
    $mail->AltBody = "这份邮件包括HTML代码，您需要更换浏览器才能正常查看"; //邮件正文不支持HTML的备用显示
    $mail->Encoding      = $this->Encoding;//编码方式
    $mail->FromName      = $this->FromName; //发件人姓名
    $mail->CharSet       = $this->CharSet; //设置邮件编码
    //传入改变值
    $mail->Host          = $this->HOST;//'smtp.exmail.qq.com'; //smtp服务器的名称（这里以QQ邮箱为例）
    $mail->Port          = $this->PORT;//端口
    $mail->Username      = $this->ANOTHER_EMAIL_USER; //你的邮箱名
    $mail->Password      = $this->ANOTHER_EMAIL_PWD; //邮箱密码
    $mail->From          = $this->ANOTHER_EMAIL_USER; //发件人地址（也就是你的邮箱地址）
    $mail->Subject       = $this->Subject; //邮件主题
    $mail->Body          = $this->Content; //邮件内容  

    $sendto              = $this->config['AddAddress']; //收件人
    foreach($sendto as $k => $v){
      $mail->AddAddress($v[0], $v[1]);
    }

    $addAttachment = $this->config['AddAttachment'];  //附件
    foreach($addAttachment as $ko => $vo){
      $mail->AddAttachment($vo[0],$vo[1]);  //附件地址,附件名字
    }
    return($mail->Send());
  }

  /**
   * 检查
   * @param  [type] $name [description]
   * @return [type]       [description]
   */
  protected function check($name){

    //校验是否存在或为空
    if(!isset($this->config['HOST'])){
      die('HOST参数不存在');
    }
    if(!isset($this->config['PORT'])){
      die('PORT参数不存在');
    }
    if(!isset($this->config['EMAIL_USER'])){
      die('EMAIL_USER参数不存在');
    }
    if(!isset($this->config['EMAIL_PWD'])){
      die('EMAIL_PWD参数不存在');
    }
    if(!isset($this->config['Subject'])){
      die('Subject参数不存在');
    }
    if(!isset($this->config['Content'])){
      die('Content参数不存在');
    }
    if(!isset($this->config['AddAddress'])){
      die('AddAddress参数不存在');
    }
    if(!isset($this->config['AddAttachment'])){
      die('AddAttachment参数不存在');
    }

    //检验是否为二维数组
    if(is_array($this->config['AddAddress'])){
      foreach($this->config['AddAddress'] as $v){
        if(!is_array($v)){
          die('AddAddress必须以二维数组形式传递');
        }
      }
    }else{
      die('AddAddress必须以二维数组形式传递');
    }

    //检验是否为二维数组
    if(is_array($this->config['AddAttachment'])){
      foreach($this->config['AddAttachment'] as $v){
        if(!is_array($v)){
          die('AddAttachment必须以二维数组形式传递');
        }
      }
    }else{
      die('AddAttachment必须以二维数组形式传递');
    }
  }
}

?>