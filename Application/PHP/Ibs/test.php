<?php  
class Person {  
    //默认配置
    protected $config = array(
        'SaveName'   => '',          //文件路径+文件名
        'Title'      => '',          //csv表头
        'Data'       => '',          //需要导出的数据数组
        'Format'     => 'csv',       //默认导出文件为csv，输出类型,以此作为判断是导出excel2003,2007,2012,csv
        'Clear_List' => array(),     //默认执行清空值的字段
        'Model_Type' => '1',         //导出csv的模板类型，默认1为申通csv模式，2为顺丰csv模式
    );

    public function __construct($config = array()){
        /* 获取配置 */
        // $this->config = array_merge($this->config, $config);
        var_dump($this->config);
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

    public function index(){

    }
}

$p1 = new Person();
$p1->SaveName = 'sss';

$p1->index();                 // 返回true,false