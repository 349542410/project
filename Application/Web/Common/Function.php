<?php
	
	//上传身份证
    function file_upload($file){
		if($file['error'] == 4){
			return array(
				'success'=>true,
				'info'=>'',
			);
		}else if($file['error'] != 0){
			return array(
				'success'=>false,
				'info'=>L('Upload_file_rc'),
			);
		}else{
            if(intval($file['size']) > 4200000){
                // echo '单个图片的大小不可超过4M';
                return array(
					'success'=>false,
					'info'=>L('The_s_4_rc'),
				);
            }

            //扩展名是否 ('jpg', 'png', 'jpeg')
            $type = explode("/",$file['type']); 
            if(!in_array($type['1'],array('jpg','png','jpeg'))){
                return array(
					'success'=>false,
					'info'=>L('Documents_m_rc'),
				);
            }

            //上传证件照
            $upload           = new \Think\Upload();            // 实例化上传类
            $upload->maxSize  = 4200000;                        // 设置附件上传大小  不超过800k 上面设置了大小限制，这里不需要
            $upload->exts     = array('jpg', 'png', 'jpeg');    // 设置附件上传类型
            $upload->rootPath = WU_ABS_FILE."/";                //设置文件上传保存的根路径
            $upload->savePath = C('UPLOADS_ID_IMG');            // 设置文件上传的保存路径（相对于根路径）
            $upload->autoSub  = true;                           //自动子目录保存文件
            $upload->subName  = array('date','Ymd');
            $upload->saveName = array('uniqid',mt_rand());      //设置上传文件名

            $res = $upload->uploadOne($file);
            if(!$res){
                return array(
					'success'=>false,
					'info'=>L('Upload_file_rc'),
				);
            }

            //上传成功
            return array(
				'success'=>true,
				'info'=> $res['savepath'] . $res['savename'],
			);
		}
	}