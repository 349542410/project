<?php
	require('./include.php');
	include 'config.php';

	use Tencentyun\ImageV2;
	use Tencentyun\Auth;
	use Tencentyun\Video;


	// V2增强版空间 带有空间和自定义文件名的示例
	// 上传图片
	if($_POST){
	$bucket = 'megao668'; // 自定义空间名称，在http://console.qcloud.com/image/bucket创建
	$fileid = $_FILES["upfile"]['name'];  // 自定义文件名

	$file = $_FILES["upfile"]["tmp_name"];
	// var_dump($file);
		// $delRet = ImageV2::del($bucket, $fileid);
		// var_dump('删除：',$delRet);die;
	$uploadRet = ImageV2::upload($file, $bucket, $fileid);


	echo '<pre>';
	// var_dump('upload',$uploadRet);

	if (0 === $uploadRet['code']) {
	    $fileid = $uploadRet['data']['fileid'];
	    $downloadUrl = $uploadRet['data']['downloadUrl'];

	    // 查询管理信息
	    $statRet = ImageV2::stat($bucket, $fileid);
	    // var_dump('stat',$statRet);
	    
		$filename        = $statRet['data']['fileid'];			//文件名
		$download_url    = $statRet['data']['downloadUrl'];		//下载地址
		$upload_time     = date('Y-m-d H:i:s',$statRet['data']['uploadTime']);		//上传时间


		$sql = "INSERT INTO mk_pictures_list (filename,download_url,download_amount,upload_time,uploader) VALUES ('$filename', '$download_url', 0, '$upload_time', NULL)";

		if(mysql_query($sql)){
			echo '上传成功';
		}else{
			echo '上传失败';
		}

	    // // 复制
	    // $copyRet = ImageV2::copy($bucket, $fileid);
	    // var_dump('copy', $copyRet);

	    // // 生成私密下载url
	    // $expired = time() + 999;
	    // $sign = Auth::getAppSignV2($bucket, $fileid, $expired);
	    // $signedUrl = $downloadUrl . '?sign=' . $sign;
	    // var_dump('downloadUrl:', $signedUrl);

	    // //生成新的单次签名, 必须绑定资源fileid，复制和删除必须使用，其他不能使用
	    // $fileid = $fileid.time().rand();  // 自定义文件名
	    // $expired = 0;
	    // $sign = Auth::getAppSignV2($bucket, $fileid, $expired);
	    // var_dump('signOne:',$sign);

	    // //生成新的多次签名, 可以不绑定资源fileid
	    // $fileid = '';
	    // $expired = time() + 999;
	    // $sign = Auth::getAppSignV2($bucket, $fileid, $expired);
	    // var_dump('singMore:',$sign);
		
		// $delRet = ImageV2::del($bucket, $fileid);
		// var_dump('删除：',$delRet);
	} else {
	    var_dump('已存在:',$uploadRet);
	    $delRet = ImageV2::del($bucket, $fileid);
		var_dump('删除：',$delRet);
	}
		echo '</pre>';
	}

?>