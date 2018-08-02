<?php
return array(
	'lng'		 => 'zh-cn',
	/* Member 控制器 */


	/* Order 控制器*/
	'PostNameMsg'     => '请填写寄件人名称', 
	'PostAddressMsg'  => '请填写寄件人地址',
	'PostPhoneMsg'    => '请填写寄件人手机号码',
	'PostCodeMsg'     => '请填写寄件人邮编',
	'RecNameMsg'      => '请填写收件人名称',
	'ProvinceMsg'     => '请选择省份',
	'CityMsg'         => '请选择地级市',
	'TownMsg'         => '请选择市、县、区',
	'RecAddressMsg'   => '请填写收件人详细地址',
	'RecPhoneMsg'     => '请填写收件人手机号码',
	'RecCodeMsg'      => '请填写收件人邮编',
	'TransferLineMsg' => '请选择中转路线',
	'GoodsListMsg'    => '请至少填写一个货品声明',
	'RecAddrMsg'      => '请填写完整的详细地址',

	/* Member 控制器*/
	'PrevPage'       => '上一页',
	'NextPage'       => '下一页',
	'FirstPage'      => '首页',
	'LastPage'       => '末页',
	'TotalPage'      => '共 {$n} 条记录',
	'DeleteSuccess'  => '删除成功',
	'DeleteFalse'    => '删除失败',
	'ErrorParameter' => '参数错误，请刷新再试',

//=======================================视图=========================================
	/* 包裹列表 */
	'UpTitle'              =>'美快国际物流-会员平台',
	'TitleMsg1'            =>'美快海外物流助手',
	'TitleMsg2'            =>'更智能更放心，从美国到中国，一键完成。',
	'MegaoLogistics'       =>'美快物流 版权所有',
	'PackageList'          =>'包裹列表',
	'OnlineOrder'          =>'在线下单',
	'Untreated'            =>'我的订单',
	'InTransit'            =>'运输中',
	'Finished'             =>'已完成',
	'Welcome'              =>'欢迎您',
	'LoginOut'             =>'退出',
	'AddressRec'           =>'收件人',
	'AddressRecPhone'      =>'收件人电话',
	'Createtime'           =>'创建时间',
	'RowsNum'              =>'行数',
	'Query'                =>'查询',
	'OrderNumber'          =>'序号',
	'OddNumbers'           =>'运单号',
	'ProvinceCity'         =>'收件人省市',
	'DeliverTime'          =>'发货时间',
	'LogisticsNewInfo'     =>'最新物流信息',
	'LogisticsNewInfoTime' =>'最新物流信息时间',
	'Operation'            =>'操作',
	'CheckOut'             =>'查看',
	'Modify'               =>'修改',
	'Delete'               =>'删除',
	'OrderAgain'           =>'再次下单',
	'AuditThrough'         =>'订单生效',
	'OrderOvertime'        =>'订单超时',
	'OrderStatus'          =>'订单状态',
	'UnFinished'           =>'未处理',
	'Accepted'             =>'已受理',
	'All'                  =>'全部',

	/* 在线下单 第一步 */
	'SenderInfo'       =>'寄件人信息',
	'RecInfo'          =>'收件人信息',
	'DetaileAddress'   =>'详细地址',
	'PleaseSelectLine' =>'请选择中转线路',
	'SelectLine'       =>'选择线路',
	'GoodsList'        =>'货品声明',
	'OnlineOrder'      =>'在线下单',
	'ProductName'      =>'货品名称',
	'BrandName'        =>'品牌',
	'ProCate'          =>'货品类别',
	'Customs'          =>'海关所属类别',
	'UnitPrice'        =>'单价',
	'Amount'           =>'数量',
	'Currency'         =>'货币',
	'Remarks'          =>'备注',
	'BtnSave'          =>'保存',

	/* 在线下单 第二步 */
	'InformationConfirmation' =>'信息确认',
	'RandomCode'              =>'凭证号',
	'ChangeBack'              =>'返回修改',
	'ContinueToOrder'         =>'继续下单',
	'FullName'                =>'姓名',
	'Address'                 =>'地址',
	'Phone'                   =>'手机号码',
	'ZipCode'                 =>'邮编',
	'TranLine'                =>'中转线路',
	'MKLINES'				  => array(  //Man 151119取代HongKong,TianJin
		'EMS' =>'美快优质线路一',
		'HK'  =>'美快优质线路二',
		'TJ'  =>'美快优质线路三',
	),
	'MKRemarks' 			  => array(
		'EMS' =>'由EMS直接进行运输',
		'GWHKSTOS'  =>'中转地点在香港，由申通进行后续运输',
		'TJ'  =>'中转地点在天津，需要上传身份证照片',
	),
	// 'HK' =>'美快优质线路一',	// '香港中转',
	// 'TJ' =>'美快优质线路二',	// '天津中转',//(需上传身份证)
	// 'TJ' =>'美快优质线路三',

	'Notice'                  =>'为保证您的货品正常发货，请认真核对信息！',

	/* 在线下单 第三步 */
	'InternationalLogistics' =>'美快国际物流',
	'PrintRandomCode'        =>'打印发货凭证',//'打印随机码',
	'Sender'                 =>'寄件人',
	'Print'                  =>'打印',

	/* edit info */
	'EditInfo'      =>'修改收件人信息',
	'RecAddress'    =>'收件人地址',
	'ToSubmit'      =>'提交',
	'ToClose'       =>'关闭',
	'OpTime'        =>'操作时间',
	'SenderPhone'   =>'寄件人电话',
	'Weight'        =>'重量',
	'Times'         =>'时间',
	'LogisticsInfo' =>'物流信息',
	'NoInfo'        =>'暂时没有数据',

	/* 登陆页 */
	'LoginIn'    =>'登 陆',
	'Register'   =>'注 册',
	'UserName'   =>'用户名',
	'Password'   =>'密　码',
	'VerifyCode' =>'验证码',
	'ForgotPwd'  =>'忘记密码',
	'Login'      =>'登 陆',

	//20151208
	'Notice'         =>'*注意：至少需要填写一条完整的货品声明',
	'Home'           =>'官网首页',
	'PersonalCenter' =>'个人中心',



	//** 首页 20161027 伦 **//
	'km_name'			=> '美快国际物流 美快转运',
	'HOME_VZ'			=> '首页',
	'FUNCTION_VZ'		=> '功能介绍',
	'SOLUTIONS_VZ'		=> '客户服务',
	'ABOUT_US_VZ'		=> '关于美快',
	'LOGIN_VZ'			=> '登录',
	'SIGN_UP_VZ'		=> '注册',
	'MyCenter_VZ'		=> '个人中心',
	'EXIT_VZ'			=> '退出',
	'Validity_VZ'		=> '周一至周五<br/>09:00-18:00',
	'HOTLINE_VZ'		=> '客服热线',
	'ONLINE_VZ'			=> '在线客服',
	'LOGISTICS_QUERY'	=> '物流查询',
	'SHIPPING_FEES'		=> '运费标准',
	'MIL_VZ'			=> '物流公司：美快国际物流',
	'IYTN_VZ'			=> '请输入您的快递单号',
	'SEARCH_VZ'			=> '查询',
	'NOTICE_VZ'			=> '★ 注意事项<br/>可使用Q开头的美快运单号或与美快合作的物流公司单号查询。',
	'Freight_Dsc'	    => '★ 温馨提示<br/>1、灰色部分为即将开通路线；<br>2、个人物品也可选择电商快件路线；<br>3、关务费包含税费、报关费、申报费、机场杂费、手续费等。',
	'DEPARTURE_VZ'		=> '出发地：美快美国加州仓',
	'DESTINATION_VZ'	=> '目的地',
	'SUD_VZ'			=> '请选择省市区',
	'WEIGHT_VZ'			=> '重　量',
	'SWEIGHT_VZ'		=> '请输入快件重量',
	'ADD_ID_VZ'			=> '补录身份证明',
	'RPAY_VZ'			=> '在线缴税',
	'CQUERY_VZ'			=> '在线关务查询',
	'MOBILE_VZ'			=> '移动端服务',
	'SCANNING_VZ'		=> '手机扫码随时随地获取资讯',
	'ACCOUNT_VZ'		=> '美快国际物流微信公众号',
	'LOGISTICS_VZ'		=> '美快国际物流手机端',
	'MEISTREET_VZ'		=> '美街国际时尚导购APP',
	'ULTIMATE_VZ'		=> '跨境电商供应链及<br />国际物流解决方案专家',
	'SPECIALIZE_VZ'		=> '为您提供一站式专业、精准、可靠、完善的国际物流配送服务',
	'GLOBAL_VZ'			=> '国际配送',
	'World_VZ'			=> '低成本跨境配送费用',
	'Stable_VZ'			=> '高效率全球配送速度',
	'Local_VZ'			=> '无忧虑国际配送品质',
	'Overseas_VZ'		=> '海外仓储',
	'Globals_VZ'		=> '遍布全球的服务网点'	,
	'Professional_VZ'	=> '一站式专业仓储服务',
	'Efficient_VZ'		=> '智能化海外仓储管理',
	'Bonded_VZ'			=> '关务辅助',
	'Major_VZ'			=> '包裹出入境海关申报',
	'Cross_border_VZ'	=> '便捷的在线缴税协助',
	'Customs_VZ'		=> '在线客服',
	'Overseasc_VZ'		=> '贴心的本地化客服团队',
	'China_VZ'			=> '专业的全程化人工服务',
	
	'Country_VZ'		=> '全球站点',
	'USA_VZ'			=> '美国/U.S',
	'Germany_VZ'		=> '德国/GER',
	'rance_VZ'			=> '法国/FRA',
	'Italy_VZ'			=> '澳大利亚/AUS',
	'UK_VZ'				=> '英国/U.K',
	'Japan_VZ'			=> '日本/JPN',
	'Useful_VZ'			=> '友情链接',
	'Express_VZ'		=> '快递查询',
	'Express_100'		=> '快递100',
	'Express_web'		=> '快递网',
	'Meikuai_VZ'		=> '在美快，速度是我们的终极目标！',
	'technology_VZ'		=> '广州美快软件开发有限公司',
	'home_kg'			=> '千克(kg)',
	'home_lb'			=> '磅(lb)',
	'alert_title'  		=> '美快提醒',
	"MK_no_alert1"		=> '单号必须不小于10位!',
	"MK_no_alert2"		=> '美快单号必须以MK开头！',

	'Route'             => '线路',
	'Calculation'       => '计算方式',
	'Unit_Price'        => '单价（美金/磅）',
	'Notice'            => '注意事项',
	'Selectd_Line_Three'=> '美快优选线路三',
	'Selectd_Line_CC'   => '顺丰CC个人快件',
	'Selectd_Line_BC'   => '顺丰BC电商快件',
	'Unit_Price_Cont'   => '单价*重量（美金/磅）*磅',
	'Notice_Cont_One'   => '最低收费16美金，低于最低收费按照最低收费计费',
	'Notice_Cont_Two'   => '最低收费5美金，低于最低收费按照最低收费计费',
	'Title_Print'       => '价格',
	'Requirement'       => '承运条件',
	'Cr_Partner'        => '合作伙伴',
	'Line_SF_P'         => '顺丰个人快件',
	'Line_SF_B'         => '顺丰电商快件',
	'Line_EMS_Pt'       => '广东EMS个人快件—特惠线路<br>（只限保健品/奶粉/食品）',
	'Line_EMS_P'        => '（优选）EMS个人快件',
	'Line_EMS_B'        => '广东EMS电商快件',
	'Five_Lb'           => '$5/lb',
	'Three_p_f_Lb'      => '$3.5/lb',
	'Three_Lb'          => '$3/lb',
	'Four_Lb'           => '$4/lb',
	'One_Rise'          => '1磅起',
	'Two_Rise'          => '2磅起',
	'Four_p_Lb'         => '$4.5/lb',
	'SF_Inter'          => '顺丰国际',
	'EMS_GD'            => '广东EMS',
	'First_Weight'      => '首重',
	'Additional_Weight' => '续重',
	'Delivery_Items'    => '配送物品',
	'HK_Ems'            => '香港E特快',
	'China_BC'          => '邮政电商快件',
	'Delivery_Text1'    => '文件、食品饮料、<br>箱包、衣服配饰、鞋靴等',
	'Delivery_Text2'    => '便宜衣服、鞋子（不收靴子）、<br>保健品、奶粉、食品饮料、洗护用品等',
	'Delivery_Text3'    => '护肤品、化妆品、首饰、<br>箱包、衣服配饰、鞋靴等',
	'Delivery_Text4'    => '箱包、衣服配饰、鞋靴',
	'Requirement_T1'    => '1磅起，<br>需提供身份<br>证号码及照片',
	'Requirement_T2'    => '1磅起，无需提供身份证',
	'Requirement_T3'    => '1磅起，<br>只需提供<br>身份证号码',




//  ** 客户服务页面 ** //

	'services_SE'  	   => '专业服务方案<br/>用心解决问题',
	'servicel_SE'  	   => '',
	'Identification_SE'=> '提交身份证明',
	'Payment_SE'  	   => '在线缴税'	,
	'Query_SE'  	   => '关务咨询',
	'Service_SE'  	   => '',
	'Submit_SE'  	   => '通过美快系统提交收件人申报资料，上传收件<br/>人身份证明，完成入境申报委托',
	'Access_SE'  	   => '关税在线缴费通道，在线补税通道',
	'Regulations_SE'   => '报关，报检的相关政策法规咨询，关务难题解决',
	'Looking_SE'  	   => '',

/*  功能介绍页面  */

	'Express_fu'		=> '<p class="title_fu" style=" text-align: left;">国际配送</p>',
	'Speedy_fu'			=> '“低成本、高效率、无忧虑”的国际物流配送一直是美快的宗旨，其中速度更是我们的终极目标。为了实现这一目标，</br>我们在保障商品质量的前提下，不断优化缩短各环节及环节间的处理时间，提高配送速度，力求在最短时间把包裹</br>交到收件人手上，降低收件人等待包裹的焦虑心情。',
	'distribution_fu' 	=> '高效率全球配送速度',
	'Multi_fu' 			=> '<p style="text-align: center;">创建丰富的跨境航空物流专线；系统化分拣打包缩短发货时间；</br>专业团队清关减少清关时间；配送路线优化缩短运输时间；</br>各项环节无缝对接，高效率配合</p>',
	'Processional_fu'	=> '无忧虑国际配送品质',
	'Meikuai_fu'		=> '<span style="text-align: center; display:inherit">标准妥善的防损坏打包措施；根据商品性质专业分类摆放；</br>提供实时、精准物流跟踪；美快全程呵护您的商品，</br>给您提供放心的国际物流体验</span>',
	'Oversea_fu'		=> '海外仓储',
	'addition_fu'		=> '我们在海外建有多个电商仓储中心，服务网点覆盖全球。</br>同时，我们还提供专业化的分拣包装服务和人性化、</br>智能化的远程虚拟仓储体系，有利于高效率、快速管理</br>仓储，从而减少配送时间，提高客户满意度。',
	'Overseac_fu'		=> '遍布全球的服务网点',
	'Meikuaiz_fu'		=> '<span style=" padding-left: 47px;letter-spacing: 1px;">美快现已在全球各地设立站点扩大<br/>服务版图，目前已开通美国-中国<br/>专线，美国货站已遍布全美</span>',

	'Meiquaiv_fu'		=> '一站式专业仓储服务',
	'expressv_fu'		=> '<span style=" padding-left: 47px;letter-spacing: 1px;">美快的经验团队为您提供专业的<br/>仓储、分拣、包装、配送等一站<br/>式服务，让您从繁琐的流程中解<br/>放出来，消费体验更加优质</span>',
	'Warehouse_fu'		=> '智能化海外仓储管理',
	'Equivalent_fu'		=> '<span style=" padding-left: 47px;letter-spacing: 1px;">完善的远程虚拟仓储体系，实时更<br/>新显示商品库存，随时进行入库出<br/>库等多种操作组合，方便管理发货<br/>方式和时间</span>',	

	'Bonded_fu'			=> '关务辅助',
	'BOffers_fu'		=> '清关作为国际物流中的核心一环，顺利清关与否将直接影响商品的整体配送时间。<br/>为了商品顺利通关，美快汲取多年的报关经验，时刻关注国内外清关政策，谨遵国家法规，<br/>结合政策导向和各报关口岸特色，为客户提供最专业的关务难题解决方案及最便捷的在线缴税服务。',

	'Customer_fu'		=> '包裹出入境海关申报',
	'CAim_fu'			=> '跨境包裹在出境及入境时均需向海关提交包裹申报，美快根据您的订单商品详情，为您的包裹进行申报',
	'Meiquaip_fu'		=> '便捷的在线缴税协助',
	'Moffer_fu'			=> '如您的包裹被海关抽检征税，美快的客服人员会通知您登陆美快物流官网进行在线补缴税',

	'Custom_fu'			=> '在线客服',
	'Clogistics_fu'		=> '您在使用美快国际物流中遇到的任何问题，包括注册资料审核、<br/>业务咨询、投诉建议等，均可在北京时间9:00-18：00联系我们的专业客服人员。<br/>如当时客服人员不在线，您可先留言，工作人员上班后会有专人跟您联系为您解决问题。',
	
	'Customsd_fu'		=> '贴心的本地化客服团队',
	'CWithout_fu'		=> '专业的中国本地化服务团队，无需跨<br/>时区跨语言沟通，第一时间为您解决问题',
	'Port_fu'			=> '专业的全程化人工服务',
	'With_fu'			=> '从业务咨询、订单开始到订单结<br/>束，美快为您提供全程的专业人工服务',

// *      关于美快页面 20161028 伦       * //
	'Industry_pr' 		=> '',
	'Industry_pr_s' 	=> '美快简介',
	'What_pr' 			=> '关于美快',
	'WMeikuai_pr' 		=> ' 美快国际物流成立于美国加州，隶属于广州美快软件开发有限公司，是一家集仓储、包装、转运、海空货运代理、航空快递、物流配送于一体的新型国际物流服务公司，在全球各地，包括美国、中国香港、中国内地均设有物流仓储中心。<br/><span style="display: inline-block;width:34px;"></span>我们致力于跨境电商物流解决方案的设计与执行，一直把“低成本、高效率、无忧虑”的物流体验作为宗旨，在保证商品安全的前提下，以最快速度把包裹完整地送到收件人手上，为目前日益增长的跨境电商市场提供高效率、正规、合法的国际快件包裹入境申报及配送服务。
',
	'Our_pr' 			=> '合作共赢',
	'Oproviding_pr' 	=> '在为客户提供仓储、物流服务的同时，我们整合产业上游的跨境电子商务需求、国际航空货运以及入境口岸资源，与相关政府部门深入合作，与UPS、EMS、顺丰、FEDEX等跨国物流公司保持长期稳定的合作关系，共同致力于跨境电子商务，为全球物流带来可行解决方案。',
	'Belives_pr' 		=> '企业口碑',
	'Bteam_pr' 			=> ' 我们已服务众多海外购物平台及店铺，包括：美街app、Instep、天猫国际、淘宝全球购、京东全球购、考拉、小红书、洋码头等，并且我们的客户名录还在与日俱增。“低成本、高效率、无忧虑”的物流体验大幅度提高客户满意度，为美快国际物流形成了良好的企业口碑。',
	'global_pr' 		=> '全球发布点',
	'California_pr' 	=> '<b>美国</b><br/>47913 Warm Spring <br/>Blvd, Fremont, CA 94539',
	'California_prs' 	=> '<b>美国</b><br/>40559 Encyclopedia <br/>Circle, Fremont, CA 94538',
	'Hong_pr' 			=> '<b>香港</b><br/>香港观塘九龙湾<br/>启兴道2号太平洋贸易中心',
	'Lirendong_pr' 		=> '<b>广州</b><br/>广东省广州市番禺<br/>里仁洞马庄金光大道南',
	'Port_pr' 			=> '口岸资源',
	'Crossing_pr' 		=> '美快通关口岸覆盖全国各地，目前以宁波、福建、厦门、香港、重庆、西安等热门海关关<br/>口为核心通关口岸，并积极开拓上海、大连、深圳等海关资源，轻松实现全程无忧的稳定配送。',

	/* 注册流程 语言 */
	'Per_r_re' 			=> '个人注册',
	'Welcome_re'		=> '欢迎您注册美快国际速递个人用户',
	'Completed_re'		=> '填写注册信息',
	'Email_re'			=> '验证注册邮箱',
	'Complete_re'		=> '完善个人信息',
	'Upload_re'			=> '上传证件',
	'Sign_re'			=> '签署授权',
	'Wait_f_re'			=> '等待确认',
	'Username_re'		=> '登录用户名',
	'Password_re' 		=> '密码',
	'Passwords_re'		=> '确认密码',
	'Email_s_re'		=> '电子邮箱',
	'Family_re'			=> '姓',
	'Given_re'			=> '名',
	'Verification_re' 	=> '验证码',
	'Next_re' 			=> '下一步',
		/* 注册提示 */
	'Please_nickname_re'=> '请输入昵称！',
	'Nickname_le_re'	=> '昵称至少6个字符,最多18个字符！',
	'Please_password_re'=> '请设置密码！',
	'Password_le_re'	=> '密码范围在6~16位之间',
	'Please_en_re' 		=> '请再输入一次密码！',
	'You_two_re'		=> '密码不一致！',
	'Please_e_re' 		=> '请输入电子邮箱！',
	'Email_ree_re'		=> '邮箱地址格式不对！',
	'Pleas_nc_re'		=> '请输入您的姓氏！',
	'Length_10_re' 		=> '长度不能超过10个字符！',
	'Please_name_re'	=> '请输入您的名字！',

	'User_exists_re'	=> '用户名已存在' ,
	'This_email_re'		=> '该邮箱已被注册',
	'Please_code_re'	=> '请输入验证码',
	'V_code_error_re'	=> '验证码错误',

	'Submit_err_re'		=> '提交失败,请联系客服！',
	'Com_name_re'		=> '公司名称',
	'Please_company_re' => '请输入公司名称！',


	// RegisterController.class.php  控制器 语言
	'Paved_rc' 			=> '道路铺设中...',
	'Page_not_rc'		=> '页面不存在',
	'ill_operation_rc' 	=> '非法操作',
	'personage_rc'		=> '个人',
	'enterprise_rc'		=> '企业',
	'Verification_c_h_rc' => '验证码已过期，请重新获取',
	'Validation_is_su_rc'=> '验证成功,正在跳转...',
	'Submit_su_rc'		=> '提交成功,正在跳转...',
	'Please_upload_rc'	=> '请上传身份证/驾照/护照',
	'Please_upload_2_rc'=> '请上传信用卡或银行账单',
	'The_s_4_rc'		=> '单个图片的大小不可超过4M',
	'Documents_m_rc'	=> '文件必须为图片！',
	'Upload_file_rc'	=> '上传失败，请重新再试',
	'Submit_su_1_rc' 	=> '提交成功',
	'Submit_er_2_rc' 	=> '提交失败',
	'Please_upload_bu_rc'	=> '请上传营业执照',
	'Registered_successfully_rc' => '注册成功',
	'Registration_failed_rc'	=> '注册失败',
	'Tuser_name_m_rc' 	=> '用户名是必填项!',
	'Please_fill2_rc'	=> '请填入字母或数字的组合,长度为6~16个字符',
	'have_access_to_rc'	=> '可以使用',

	// 验证注册邮箱
	'Complete_en' 		=> '完善',
	'information_en' 	=> '信息',
	'Dear_Meikuai_en'  	=> '尊敬的会员',
	'member_en'			=> '您好，<br>邮箱验证码已发送至您的邮箱',
	'please_fill_in_en' => '，请输入邮件中的验证码。<br>如果没有收到验证邮件，请到垃圾邮箱中找找看，或者点击',
	'Send_again_en' 	=> '重新获取',
	'Email_v_code_en' 	=> '邮箱验证码',
	'Submit_en'			=> '提交',
	'Send_in_3_en' 		=> '发送中...',
	'Sent_Items_en'		=> '已发送',	
	'Company_Register_en' => '企业注册',
	'Welcome_to_en'		=> '欢迎您注册美快国际速递企业用户',

	// 发送验证码 邮件内容
	're_head_text' 		=> '您好！<br/>欢迎加入美快国际物流',
	're_Dear'			=> '亲爱的 ',
	're_Your_ve'		=> '您的验证码如下：',
	're_which'			=> '此验证码30分钟内有效，请尽快完成注册',
	're_Thank_y'		=> '感谢您选择美快国际物流！',
	're_we_are'			=> '接下来的日子，我们将全力协助您，并与您携手一起用技术和创意改变世界！',
	're_Meikuai'		=> '美快国际物流',
	're_Email_v'		=> '邮箱验证',

	// 注册第三步 完善个人资料

	'Cr_You'			=> '您已完成邮箱验证',
	'Cr_To_b'			=> '请您进一步完善个人信息，以便我们更好的为您提供服务。',
	'Cr_next'			=> '继续',
	'Cr_You_have'		=> '您的邮箱已经通过验证，为了更好地为您提供服务，请进一步完善信息',
	'Cr_If_you'			=> '若您在填写的过程中有任何疑问，请点击页面右上角的QQ交谈在线联系或致电我们。',
	'Cr_Sender_in'		=> '发件人信息',
	'Cr_name'			=> '姓名',
	'Cr_Country_A'		=> '国家',
	'Cr_Province_City'	=> '省/州',
	'Cr_City'			=> '市',	
	'Cr_Select'			=> '请选择',
	'Cr_Identification'	=> '证件信息',
	'Cr_Select_id'		=> '请选择证件类型',
	'Cr_Id_number'		=> '证件号',
	'Cr_Address_mail'	=> '发件国退件地址(方便退件)',
	'Cr_Postal_mail'	=> '退件邮编',
	'Cr_Mobile' 		=> '手机',
	'Cr_Phone'			=> '固话',
	'Cr_Fill_number'	=> '手机、固话必填一个',
	'Cr_Contact_in'		=> '联系人信息',
	'Cr_Country'		=> '所在国家',
	'Cr_Address'		=> '详细地址',
	'Cr_Postal_code'	=> '邮编',
	'Cr_QQ_Wechat'		=> 'QQ/微信',
	'Cr_The_req'		=> '此项必填',
	'Cr_Driver_License'	=> '驾照',
	'Cr_passport'		=> '护照',
	'Cr_china'			=> '中国',

	//  注册 -- 上传页
	'Up_Upload_y'		=> '上传您的认证信息',
	'Up_According'		=> '根据进出口海关申报要求，请您上传以下证件的扫描件，美快仅在进出口报关使用，承诺不会向第三方提供此信息。',
	'Up_Document'		=> '单个上传文件最大为4M，支持JPG，PNG，GIF，JPEG',
	'Up_Upload_ID'		=> '上传身份证/驾照/护照',
	'Up_Upload_bank'	=> '上传信用卡或银行账单',
	'Up_Upload_water'	=> '上传水/电/煤气账单',
	'Up_Back'			=> '上一步',
	'Up_Please_1'		=> '请上传身份证/驾照/护照',
	'Up_Please_2'		=> '请上传信用卡或银行账单',
	'Up_Documents'		=> '文件必须为图片！',
	'Up_Please_fill'	=> '为必填项',

	// 剩余页面
	'Fe_Meikuai_will'	=> '美快会代您向海关提供进出口报关资质认证信息',
	'Fe_According_t'	=> '根据进出口国家法律要求，您的包裹在通关时会向当地海关提供发件人证明信息。请确保您填写的信息真实有效。',
	'Fe_I_have'			=> '我已阅读了两份授权书',
	'Fe_Confirm'		=> '确认授权完成注册',
	'Fe_Please_ma'		=> '请确认授权以便完成注册',
	'Fe_Through'		=> '通过信息验证!',

	// 企业注册页面
	'Co_Company_in'		=> '公司信息',
	'Co_Please_fo'		=> '请以您公司证件上的语言填写',
	'Co_Company_re'		=> '公司注册地址',
	'Co_Company_repr'	=> '公司代表人',
	'Co_To_speed'		=> '为了您的包裹能够合法、快捷的完成进出境海关申报，请进一步完善以下内容。',
	'Co_Business_l'		=> '营业执照编号',
	'Co_Tax_number'		=> '税号（EIN No.）',
	'Co_Address_remail'	=> '发件因退件地址（方便邮件账单及推荐送达服务）',
	'Co_Postalcode_mail'=> '退件邮编',
	'Co_Business'		=> '业务联系人信息',
	'Co_Contact_people'	=> '联系人',

	'Ul_icense' 		=> '上传营业执照',
	'Ul_statement'		=> '上传水/电/煤气/信用卡账单',
	'Ul_logo'			=> '上传logo',
	'Ul_According_refund'	=> 'LOGO将用于进出口申报发票的导出。根据发件国当地法律要求，发票可作为出口退税凭证',
	'Ul_Please'			=> '请上传营业执照!',

	// 完成注册
	'Su_Complete_in'	=> '完成资料填写',
	'Su_Congra'			=> '恭喜您，您已完成了资料填写',
	'Su_Our_customer'	=> '我们的客服会审核确认您填写的内容，一般需1-3个工作日。',
	'Su_Final_result'	=> '审核结果将会以邮件的形式通知您。',
	'Su_Learn_more'		=> '您可以先了解美快业务',
	'Su_Homepage'		=> '官网首页',
	'Su_Click_account'	=> '进入我的美快',
	'Su_You_can_also'	=> '通过联系我们的客服人员与我们取得联系。',


	// 补录身份证明及在线缴税页面
	'Pr_title' 			=> '补充身份证信息及在线缴税',
	'Pr_Delivery'		=> '快件信息',
	'Pr_complete'		=> '完成',
	'Pr_Meikuai_Number'	=> '美快单号',
	'Pr_Please_number'	=> '请输入美快单号',
	'Pr_Receiver'		=> '收件人',
	'Pr_Please_recipients'	=> '请输入收件人',
	'Pr_Re_Number'		=> '收件人电话',
	'Pr_Pleases_number'	=> '请输入收件人电话',
	'Pr_Submit'			=> '提交',
	'Pr_title_2'		=> '身份证信息及在线缴税',
	'Pr_Notice'			=> '温馨提示：',
	'Pr_Individual_mail'=> '个人行邮物品向海关申报时须提交收件人身份证信息，为确保货物顺利通关，请上传真实有效的收件人身份证号码和照片。',
	'Pr_Resident'		=> '1.中国大陆居民：居民身份证或临时居民身份证；',
	// 'Pr_Resident'		=> '1.中国大陆居民：居民身份证或临时居民身份证；<br/> 2.港澳居民：港澳居民往来内地通行证（回乡证）；<br/>3.台湾居民：台湾居民来往大陆通行证（台胞证）；<br/> 4.外国公民：护照。',
	'Pr_must'			=> '为必填项',
	'Pr_ID_number'		=> '身份证号',
	'Pr_Pleaseid_number'=> '请输入身份证号码',
	'Pr_Iden_type'		=> '证件类型',
	'Pr_ID_card'		=> '身份证',
	'Pr_Passport'		=> '护照',
	'Pr_Other'			=> '其他',
	'Pr_Back'			=> '上一步',
	'Pr_Submit_cl'		=> '提交通关文件',
	'Pr_Submit_success'	=> '您已成功提交资料',
	'Pr_Customs_duty' 	=> '应缴税额',
	'Pr_Pay_by_sender'	=> '寄件方缴交',
	'Pr_Check_the_log'	=> '查看物流',
	'Pr_Determine'		=> '确定返回？',

	'Pr_No_collection'	=> '无需保存资料',
	'Pr_save_failure'	=> '保存失败',
	'Pr_Id_wrong'		=> '身份证号码有误',


	// 找回密码 页面
	'Rd_Notice'			=> '提示信息：登陆密码已修改成功，请牢记您的新密码。',
	'Rd_You_can'		=> '您可以进行的操作有：',
	'Rd_Return_page'	=> '返回登陆页',

	'Rd_Re_Password'	=> '修改密码',
	'Rd_New_password'	=> '新密码',
	'Rd_New_password_s'	=> '确认新密码',
	'Rd_Verif_code'		=> '验证码',
	'Rd_CONFIRM'		=> '完成',
	'Rd_Click_refresh'	=> '点击刷新',
	'Rd_Veri_error'		=> '验证码错误',
	'Rd_Please_name'	=> '请输入用户名',

	'Rd_Reset_title'	=> '修改密码',
	'Rd_RET_PASSWORD'	=> '找回密码',
	'Rd_USERNAME'		=> '用户名',
	'Rd_EMAIL'			=> '邮箱',
	'Rd_SEND'			=> '发送验证',
	'Rd_Dear_member'	=> '尊敬的会员 您好,',
	'Rd_Your_Reset'		=> '重置密码信息已发送至您的邮箱',
	'Rd_please_login'	=> '请注意查收，密码修改请在',
	'Rd_minutes'		=> '分钟内',
	'Rd_minutes_s'		=> '完成。',
	'Rd_Please_here'	=> '*如果没有收到密码重置邮件，请到垃圾邮箱中找找看，或者点击',
	'Rd_to_reset_password'	=> '重置密码。',


	// 登录/注册 页面
	'Lr_title'			=> '登录/注册',
	'Lr_P_LOGIN_REGI'	=> '请登陆或注册',
	'Lr_PERS_REGISTER'	=> '个人注册',
	'Lr_COMPANY_REGISTER'=> '企业注册',
	'mk_pr_re'			=> '请同意协议',
	// 企业
	'Lr_You_are'		=> '您是',
	'Lr_Company_regi'	=> '注册于美国的企业<br/>从事跨境电商B2C相关业务公司',
	'Lr_You_just_ha'	=> '您只需提供以下信息',
	'Lr_Copies_of'		=> '<label></label>营业执照扫描件及编号税号的其中一种扫描件<br/><label></label>水，电，煤或信用卡账单其中一种扫描件',
	'Lr_SUBMT1'			=> '企业注册及其信息完善',
	// 个人
	'Lr_American_citiz'	=> '美国居民，留学生<br/>从事代购海淘等C2C跨境电商业务人员',
	'Lr_One_of_the_f'	=> '<label></label>个人身份ID，驾照或护照其中一种扫描件<br/><label></label>水，电，煤或信用卡账单其中一种扫描件',
	'Lr_SUBMT2'			=> '个人注册及其信息完善',

	// 登录
	'Lr_LOGIN'			=> '登陆',
	'Lr_USERNAME_EMAIL'	=> '用户名/邮箱',
	'Lr_PASSWORD'		=> '输入密码',
	'Lr_VERTI_CODE'		=> '验证码',
	'Lr_FORGET_PASSWORD'=> '忘记密码?',
	'Lr_Please_password'=> '请输入密码',
	'Lr_longin_failed'	=> '登录失败',	

	'Lr_Single_number'	=> '单号必须以MK开头，且必须是13位！',
	'Lr_WAYBILL_QUERY'	=> '运单查询',
	'Lr_No_such_results'=> '查无结果',
	'Lr_Reminder'		=> '提醒：请检查订单号格式或长度是否正确',
	'Lr_Note'			=> '注意：由于网络延迟或新订单可能会造成信息未及时更新，请稍候再进行查询',

	'Lr_Hot'			=> '热门',
	'Lr_province'		=> '省份',
	'Lr_city'			=> '市区',
	'tracking_number'	=> '单号',
	'Lr_Meikuai'		=> '美快',
	'Lr_Expert_in'		=> '你的跨境电商物流解决专家',


	// 客户服务页面补充页面
	'Pg_Prohibit_g'		=> '违禁品/税款条例',
	'Pg_Prohibit_goods'	=> '禁止、限制进出境物品表<br/><br/>',
	'Pg_Prohibit_goods_url'	=> 'http://www.customs.gov.cn/publish/portal0/tab517/info10510.htm',
	'Pg_china'			=> '《中华人民共和国进境物品归类表》和<br/>《中华人民共和国进境物品完税价格表》',
	'Pg_china_url'		=> 'http://www.customs.gov.cn/publish/portal0/tab65598/info793342.htm',
	'Pg_Regu_on_Duties'	=> '进出口关税条例<br/><br/>',
	'Pg_Regu_on_Duties_url'=> 'http://www.customs.gov.cn/publish/portal0/tab2748/info3487.htm',
	'Pg_Co_Suggestions'	=> '建议和投诉',
	'Pg_Co_Suggestions2'=> '（工作时间：周一至周五 北京时间9：00-18：00）',
	'Pg_Co_Suggs'		=> '建议与投诉',
	'Pg_width' 			=> 'style="width:220px;"',

	// 注册协议
	"MkPrtocol"			=> "美快国际物流服务协议",
	"MkPrtocol_Content"	=> '<p class="title">一、服务条款的确认和接受</p>
<p class="mk_c">欢迎使用美快国际物流提供的服务，为保障您的权益，请用户在注册前仔细阅读、充分理解本协议。无论您事实上是否在注册前认真阅读了本协议，您一旦选择使用美快国际物流为您提供的各项服务，即表示您已充分阅读、理解并同意本协议，且将受本协议的条款约束。
美快国际物流有权根据需要不时地制定、修改本协议，经修订的协议一经发布立即自动生效成为本协议的一部分。如用户不同意相关变更，必须马上停止使用美快国际物流提供的服务，如果您登录或继续使用将表示您接受经修订的协议。除另行明确声明外，任何使本服务范围扩大或功能增强的新内容均受本协议约束，发生争议或纠纷时，将以最新的服务协议为准。</p>
<p class="title">二、用户注册</p>
<p class="mk_c">1、用户注册时应按要求提供真实、准确、有效的注册信息，并及时更新注册资料，资料不完善将不能享受部分服务，且注册成功即默认确认同意本协议。如因注册信息不真实、不准确、无效而引起的任何后果由用户个人独自承担，美快国际物流将不负任何责任；</p>
<p class="mk_c">2、用户不应将其帐号、密码转让或出借给他人使用，由于用户自己对帐号的保管不当、不安全操作或使用所造成的一切纠纷和后果，由用户自行承担，美快国际物流不承担任何责任。如用户发现其帐号遭他人非法盗用、使用或发生其他危及账户安全的情形，应马上修改密码，如问题仍未得到解决，应立即联系美快国际物流，要求暂停或终止相关服务，美快国际物流将在合理时间内进行处理，但对在采取行动前已经产生的后果（包括但不限于用户的任何损失）不承担任何责任；</p>
<p class="mk_c">3、用户承诺遵守法律法规，社会主义制度，国家利益，公共秩序，社会道德风气等，不侵害他人合法权益，如有因违反法律等引起的一切后果，用户将独立承担所有带来的法律责任；</p>
<p class="mk_c">4、用户必须是具备完全民事行为能力的自然人，或者是具有合法经营资格的实体组织。若用户为18周岁以下的未成年人使用美快国际物流服务，需事先得到其家长或监护人的同意，且一切后果由用户及用户的监护人、或组织负责。</p>
<p class="title">三、隐私保护</p>
<p class="mk_c">本站保证不公开用户的真实姓名、地址、电子邮箱和联系电话等用户信息，除以下情况外：</p>
<p class="mk_c">1、用户授权本站透露这些信息；</p>
<p class="mk_c">2、相应的法律及程序要求本站提供用户的个人资料。</p>
<p class="title">四、用户权利</p>
<p class="mk_c">1、用户可在美快国际物流官网进行在线下单；</p>
<p class="mk_c">2、用户有权了解订单明细，包含但不限于发货单号、物流信息跟踪、运费金额明细等内容；</p>
<p class="mk_c">3、如有需要，用户可向美快国际物流客服请求协助，并提出相关建议。</p>
<p class="title">五、用户义务</p>
<p class="mk_c">1、用户应保证送寄递的个人快件包裹符合个人自用的标准，具体为：合理数量，合理价值（不超过1000人民币，不可分割的单一物品除外），禁止入境后出售或者出租，禁止拆货分单寄送等违背海关监管条例的行为；</p>
<p class="mk_c">2、用户应通过美快国际物流官网，积极配合提交真实、有效、正确、完整的资料或文件，包括但不限于寄送地址、货品申报、证件上传等，并协助美快国际物流完成清关、查验，缴税以及其他清关事项；</p>
<p class="mk_c">3、用户应遵守并正确履行国际货物运输服务相关的始发地和目的地国家和地区法律法规要求。因用户隐瞒或夹带违禁违规物品，用户应承担一切责任及后果；</p>
<p class="mk_c">4、因用户违反上述的任何规定，给本公司造成经济损失的，应由用户给予补偿并消除不良影响。</p>
<p class="title">六、美快国际物流义务</p>
<p class="mk_c">1、美快国际物流作为用户的货件转运者，应遵守并正确履行国际货物运输服务相关的始发地和目的地国家和地区法律法规要求，及时将货件安全地运输到目的地，提供空运，报关，清关，以及国内快递转运服务；</p>
<p class="mk_c">2、美快国际物流负责货件的清关服务，包括海关查验，缴税及其他清关服务，且有义务协助用户指定收件人准备及提交清关所需要的材料或凭证；</p>
<p class="mk_c">3、美快国际物流有义务遵循隐私政策保护个人信息，不向第三方提供具有可识别性的个人信息。</p>
<p class="title">七、免责声明</p>
<p class="mk_c">美快国际物流不承担任何间接、附带、特殊或衍生的损失（包括但不限于收入或利润损失）所产生的赔偿责任，不会就包括但不限于任何下列原因所引起或造成的遗失、损毁、延迟送达、误送、未送达、海关查扣、销毁、提供错误资料或未能提供资料负任何责任，亦不采取任何运费调整、退款或任何补偿行为：</p>
<p class="mk_c">1、因用户的行为、不履行责任或疏忽，包括但不限于不按要求上传身份证证件；</p>
<p class="mk_c">2、因托运货物的本质或瑕疵、特性或固有缺陷，如有明显破损等；</p>
<p class="mk_c">3、用户提供信息不完整或有误，包括但不限于货物故意申报不实，寄运货件地址提交不当或不完整导致无法联系收件人，造成的重复配送所产生的费用及相关后果；</p>
<p class="mk_c">4、发生空难事故、公敌入侵、政府当局、法律权限、海关或检疫所官员的行为或疏忽、暴动、罢工或其它地区性争执、民众骚乱、战争或天气所带来的危险、或由非美快国际物流所能控制事项所致的全国性或区域性空中或地面交通系统中断或通讯系统干扰或故障。在此情况下，美快国际物流仍会尽合理的努力，尽快运送至目的地并完成送件。但我们没有义务在出现此类情况时向您发出通知；</p>
<p class="mk_c">5、如果货件在送达时无破损且基本完整，收件人未提出异议发出书面破损记录，寄件人包装并封口的包裹内发生遗失或损毁；</p>
<p class="mk_c">6、因清关手续或其它主管机关规定手续所致的延误；</p>
<p class="mk_c">7、因甲方没有及时确认关税及税金支付所致的延误；</p>
<p class="mk_c">8、磁带、文件或其它存储媒体中数据的消除、或曝光底片中照相影像或音带的消除；</p>
<p class="mk_c">9、外包装完好，内件破损的包裹；</p>
<p class="mk_c">10、易碎品的破损及损坏，包括但不限于玻璃陶瓷材料及其制品，乐器，电子设备元件，照相器材等；</p>
<p class="mk_c">11、发件人或者购物网站未能提供能够支持多次转运需要的包装而造成的毁损；</p>
<p class="mk_c">12、本公司遵循您或其他寄、收件人的口头或书面送件指示行为导致；</p>
<p class="mk_c">13、寄送国家或相关部门规定的禁止或限制进出境的物品（详情请查看官网<a href="http://www.customs.gov.cn/publish/portal0/tab517/info10510.htm">《禁止、限制进出境物品表》</a>），或美快国际物流认为不适合运送的其他物品。</p>

<p class="title">八、法律管辖和适用</p>
<p class="mk_c">1、本协议的执行、解释及争议的解决均应适用中华人民共和国法律；</p>
<p class="mk_c">2、如双方就本协议内容或其执行发生任何争议，双方应尽量友好协商解决；协商不成时，任何一方均可向本公司所在地的人民法院提起诉讼；</p>
<p class="mk_c">3、本公司未行使或执行本服务协议任何权利或规定，不构成对前述权利或权利之放弃。如本协议中的任何条款无论因何种原因完全或部分无效或不具有执行力，本协议的其余条款仍有效并且有约束力。</p>',

	// 弹出框区域文本
	"cu_submit"	=> "确定",

	// 注册404页面 
	'Cannot_open_404' 	=> '<br/>无法打开页面',
	'Other_Use_404'		=> '您可以进行的操作有：',
	're_1_404'			=> '',
	're_2_404'			=> '返回登录',
	're_3_404'			=> '或',
	're_4_404'			=> '注册新用户',
	're_5_404'			=> '',
	'you_have_404'		=> '*如有任何疑问，请',
	'service_404'		=> '咨询客服',
	// 增加Profile/index语言包
	'Filetit'           => '证件上传',
	'Fileimg'           => '上传图片',
	'Filemsgz'          => '上传证件国徽面照片',
	'Filemsgf'          => '上传证件头像面照片',

	// 增加公告语言包
	'Title_annoumc' => '美快公告-美快国际物流 美快转运',
	'l_mei_not' => '美快公告',
	'l_view_deta' => '[查看详情]',
	'l_notice' => '公告',
	// 增加/Logistics/index语言包
	'l_pupwp' => '未揽收包裹，请耐心等待，或稍后再试',
	'l_m_wn' => '美快运单号',
	'l_m_pl' => '请输入美快运单号',

    'lack_of_parameters'			    => '缺少参数',
    'add_submit'					    => '您已成功提交资料',


    'l_receiver_id' => '收件人身份证',
    'l_reconrd_cont' => '填写的身份证号将同步到同一收件人的其它订单中',
    'l_upload_by_s' => '寄件人上传证件照',
    'l_upload_by_r' => '收件人上传证件照',
    'l_upload_cont' => '上传的身份证照片将同步到同一收件人的其它订单中',
    'l_front_id_card' => '身份证国徽面',
    'l_back_id_card' => '身份证头像面',
    'l_reconrd_two_cont' => '复制以下链接发送给收件人上传证件照片',
    'l_pc_upload_line' => '电脑端上传链接',
    'l_wap_upload_line' => '手机端上传链接',
    'l_copy_line' => '复制链接',
    'l_plan_unit' => '请输入单位',
    'l_is_allunit' => '是否套装',
    'l_id_Information' => '补录证件信息',
    'l_seach_orderid' => '批量导入订单号',
    'l_fill_msg' => '补填成功！',
    'l_m_img_title1' => '未补录',
    'l_m_img_title2' => '已补录',
    'l_m_img_title3' => '无需补录',
    'l_m_img_title4' => '未上传',
    'l_m_img_title5' => '已上传',
    'l_m_img_title6' => '无需上传',

    'order_excess'  				    => '订单总金额超出最大限制金额$',
    'no_order_address' 				=> '详细地址中不能包含字符',
    'id_card_error'					=> '身份证号码输入错误,请重新输入',
    'idcard_order_photo_route' 		=> '请传入身份证头像面图片路径',
    'idcard_order_photo' 			    => '请传入身份证头像面图片',
    'idcard_photo_failure'			=> '身份证头像面识别失败！',
    'idcard_order_national_route'	=> '请传入身份证国徽面图片路径',
    'idcard_order_national' 		    => '请传入身份证国徽面图片',
    'idcard_national_failure' 		=> '身份证国徽面识别失败',
    'idcard_name' 					    => '请传入身份证姓名',
    'idcard_number'					=> '请传入身份证号码',
    'inconsistency_of_names'  		=> '身份证名字与收件人姓名不一致 请检查填写是否有误',
    'number_inconsistencies'		    => '身份证号码与收件人身份证号码不一致 请检查填写是否有误',
    'idcard_inconsistencies'		    => '身份证头像面与身份证国徽面不一致 请检查上传是否正确',
    'idcard_no_photo'				    => '身份证头像面不能为空',
    'idcard_no_national'			    => '身份证国徽面不能为空',
    'idcard_name_not_empty'			=> '姓名不能为空',
    'idcard_not_empty'				=> '身份证不能为空',
    'idcard_wrong'					    => '身份证格式不对',
    'idcard_no_empty'				    => '身份证图片不能为空',
    'parcel_name'					    => '包裹名',
    'contain_illegal_characters'	    => '包含非法字符',
    'is_suit'						    => '是否为套装',
    'one_data'						    => '至少填写一行数据',
    'not_be_empty'					    => '单位不能为空',
    'goods_cannot_be_empty'			=> '货品名称不能为空',
    'please_enter_your_idcard'      => '请上传正确的身份证图片',

    'not_idcard'					    => '身份证号码不能为空',
    'not_order_exist'				    => '此订单不存在',
    'idcard_format'					=> '身份证号码格式不正确',
    'order_info_mistaken'			    => '订单信息有误',
    'order_in_idcard'				    => '订单已录入身份证信息',
    'idcard_photos'					=> '身份证照片不能为空',
    'line_not_empty'				    => '线路不能为空',
    'cue_contact'					    => '该单在打单时出现问题，请与店员或客服联系，给你带来不便，敬请原谅',
    'lack_of_parameters'			    => '缺少参数',
    'add_submit'					    => '您已成功提交资料',
    'picture_empty'                   => '图片域为空',
    'l_view_stat_tips'                => '下单模板有更新，请下载最新模板下单。',
    'idcard_succeed'                  => '身份证识别成功！',


    'l_code_format' => '格式不正确',

);