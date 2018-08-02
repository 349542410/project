<?php
//订单上传
	$backArr = '{
    "Status":"success",
    "ErrorMessage":"",
    "Result":[
        {
            "ReferenceId":"MK81000053US",
            "Status":" success",
            "ParcelId":"D1",
            "TrackingNo":"EL11111111111CN",
            "Error":""
        }
    ]
	}';

// 删除
	// $backArr = '{
	// "Status": true,
	// "ErrorMessage": "",
	// "Result": [
	// {
	// "ReferenceId": "MK81000053US",
	// "Status": "success",
	// "ErrorMessage": ""
	// }
	// ]
	// }';	
	



















	echo $backArr;//用json形式返回便于 请求方 能够“获取”到返回结果实体形态