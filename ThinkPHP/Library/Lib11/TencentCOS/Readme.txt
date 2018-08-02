配置文件：

    app_id          开发者访问 COS 服务时拥有的用户维度唯一资源标识，用以标识资源
    secret_id       开发者拥有的项目身份识别 ID，用以身份认证
    secret_key      开发者拥有的项目身份密钥

    以上三项登陆腾讯云帐户可获取

    region          bucket所属地域：华北 'tj' 华东 'sh' 华南 'gz'
    timeout         请求超时时间
    bucket          COS 中用于存储数据的容器


    在 COS 中，bucket相当于一个项目名，然后项目下面可以创建文件夹和文件
    如需使用 bucket 内所有文件可下载，需要对其设置权限为公有读私有写，也可单独的对 bucket 内的文件夹或者文件进行设置
    另外需要关闭防盗链




    
示例：

    // 上传文件示例

    $args = array(
        'src' => './hello.txt',         // 待上传的文件，必须是文件在服务器上的路径（可以使用js上传到服务器的临时文件）   
        'dst' => '/test/hello.txt',     // 上传到配置文件中的 bucket 下的 test 文件夹里，存储为 hello.txt（可更改名字）
    );

    $obj = new \Lib11\TencentCOS\Cos();
    $result = $obj->upload($args);
    dump($result);



    // 下载文件示例

    $args = array(
        'src' => '/hehe/hello.txt',     // 下载配置文件中的 bucket 下的 test 文件夹里 hello.txt 文件
    );

    $obj = new \Lib11\TencentCOS\Cos();
    $result = $obj->download($args);
    dump($result);