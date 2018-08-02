<?php
	include 'config.php';

	$sql = "SELECT * FROM mk_pictures_list ORDER BY 'upload_time' DESC";

	$res = mysql_query($sql);

	$count = mysql_num_rows($res);	//总数
	echo '<pre>';
	// var_dump($count);

	// while($list = mysql_fetch_assoc($res)){
	// 	// echo $list["download_url"].'<br>';
	// }



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<title>主页</title>
</head>
<body>
	<div style="width:1024px;margin:0 auto;">
		<div style="width:800px;min-height:500px;border:1px solid black;">
			<h3 style="text-align:center;">空间管理</h3>
			<hr />
			<form name="frm" method="post" action="demo.php?index" enctype="multipart/form-data">
			<font style="letter-spacing:1px" color="#FF0000">*只允许上传jpg|png|bmp|jpeg|gif格式的图片</font><br/>
			<input type="file" name="upfile" />
			<input name="btn" type="submit" value="上传" /><br />
			</form>
			<hr />
			<?php while($list = mysql_fetch_assoc($res)){?>
			<img style="width:15%;height:15%;" src="<?php echo $list["download_url"];?>" alt="" /><a href="">删除</a>
			<?php }?>
		</div>
	</div>
</body>
</html>