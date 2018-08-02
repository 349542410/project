<?php
'aUpload' => array( //上传
    'savepath' => APP_PATH , //保存的绝对根位置(不要写相对位置)
    'savedir'  => 'attachs', //相对位置,只写文件夹名称
    'tmppath' => APP_PATH .'/attachs/tmp',  //上传临时位置必须存在,否则上传失败
    'filetype' => 'jpg,png,gif,bmp,rar,zip,mp3,wma,mid,doc,pdf',   //支持的格式
    'filesize' =>4194304, //4M  
    'fileinput' =>'filedata' ,//默认文件上传域
    'dirtype' => 4,  //文件夹保存格式
    'imgresize' => TRUE,  //图片文件自动创建缩略图
    'imgmask'   => TRUE,  //图片自动加水银
    'imgmaskmsg' => 'http://www.yunbian.org ', //水印文字
    'imgresizew' => 510, //缩略图比例宽度
   ),


/////////////////////////////////////////////////////////////////
   $upfile = spClass("uploadFile");
   $upfile->set_filetypes('jpg|png|jpge|bmp');  //动态设定了允许的上传文件
   $upfile->set_diydir('blog_1');   //动态的更改了上传文件夹保存位置
   $files = $upfile->fileupload();  //开始上传,可跟一个参数,动态改变上传文件的inputname
