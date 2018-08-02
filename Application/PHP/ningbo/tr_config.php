<?php
	$userid    = 'megaoshop';// 客户编码 必需
	$pwd       = '203e11cd-947a-4c61-add5-c9a78f61e897';// 系统为客户分配的密钥 必需
	$customs   = '3109';// 关区代码  栎社机场 必需
	$msgtype   = 'cnec_jh_decl_byupdatetime';// 消息类型
	$url       = "http://api.trainer.kjb2c.com/dsapi/dsapi.do";// 请求地址 必需
	$switch    = true;//当反馈的结果NextPage=F的时候，是否重新开始查询
	$erp_url   = "http://yong.megao.hk/Other/GetMultMKState.ashx";
	$TranKd    = 2;// 要与后台系统db.php中的SF_Transit的值一致