<?php
header('Content-type:text/html;charset=utf-8');
	/**
	 * 快递100 数据回调
	 */
    include 'config.php';
?>
<h1 style="width:460px;margin:0 auto;background-color:#ddd;padding:10px 20px 10px 20px;text-align:center;">数据列表</h1>
<table width=500 border="0" align="center" cellpadding="5" cellspacing="1" bgcolor="#add3ef">
<?php
//输出所有留言
$sql="select * from MIS_mk_logs order by id";
$query=mysql_query($sql);
while ($row=mysql_fetch_array($query)){
?>
<tr bgcolor="#eff3ff">
<td><?php echo $row['context'];?></td>
</tr>
<?php }?>
</table>