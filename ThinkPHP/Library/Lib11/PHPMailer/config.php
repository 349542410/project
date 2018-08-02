<?php

    return array(

        //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
        'SMTPDebug' => false,

        //链接域名邮箱的服务器地址
        // 'Host' => 'smtp.qq.com',
        'Host' => 'smtp.exmail.qq.com',

        //设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
        // 'Hostname' => '123@qq.com',

        //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
        'FromName' => '美快国际物流',

        //设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
        'CharSet' => 'UTF-8',

        //smtp登录的账号 这里填入字符串格式的qq号即可
        // 'Username' =>'541664399@qq.com',
        'Username' =>'m.all-purpose@megao.cn',

        //smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
        // 'Password' => 'dnfruizleuqlbdhi',
        'Password' => 'Purpose123',

        //设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
        // 'From' => '541664399@qq.com',
        'From' => 'm.all-purpose@megao.cn',

        //回复地址，可选，默认为发送地址
        // 'ReplyTo' => '535920719@qq.com',
        'ReplyTo' => 'dev@meiquick.com',

    );