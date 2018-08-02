<?php
/**
 * 文本日志 类   暂时对 新自助、揽收 使用
 */
namespace Libm\DataNotes;
class DataNote{

	public $save_dir     = '';//文件夹路径
	public $file_name    = '';//文件名
	public $RequestData  = '';//内容
	public $ResponseData = '';//内容

	public function save(){
		if(!is_dir($this->save_dir)) mkdir($this->save_dir, 0777, true);//第三个参数设置true 设置递归模式自动创建必需的目录文件夹

		$file_suffix = strtolower(end(explode('.', $this->file_name)));//获取文件名的后缀

		// 非txt文件后缀，直接默认txt
		if($file_suffix != 'txt'){
			$file_suffix = 'txt';
		}
		
		$file_prefix = current(explode('.', $this->file_name));//获取文件名的前缀

		$file_prefix .= '_'.date('Ymd');// 前缀拼接上当前日期

		$this->file_name = $file_prefix.".".$file_suffix;

		$content = "===================== ".date('Y-m-d H:i:s')." =====================\r\n\r\n-------- RequestData --------\r\n\r\n".json_encode($this->RequestData,JSON_UNESCAPED_UNICODE)."\r\n\r\n-------- ResponseData --------\r\n\r\n".json_encode($this->ResponseData,JSON_UNESCAPED_UNICODE)."\r\n\r\n";

		if(is_file($this->file_name)){
			file_put_contents($this->save_dir.$this->file_name, $content);
		}else{
			file_put_contents($this->save_dir.$this->file_name, $content, FILE_APPEND);
		}

		return $this->file_name;
	}
}