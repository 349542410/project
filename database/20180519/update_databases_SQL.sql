alter table mk_order_goods_excel add spec_unit varchar(15) DEFAULT NULL COMMENT '规格单位';

alter table mk_order_goods_excel change unit num_unit varchar(15) NOT NULL DEFAULT '' COMMENT '数量单位';

alter table mk_tran_uorder add spec_unit varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '规格的单位';

alter table mk_tran_uorder add num_unit varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '数量的单位';

alter table mk_order_excel add package_id varchar(200) NOT NULL DEFAULT '0' COMMENT '包裹号';

alter table mk_order_excel add batch_id varchar(40) NOT NULL DEFAULT '0' COMMENT '批次号';

alter table mk_order_excel add parsing_addr varchar(200) DEFAULT NULL COMMENT '解析之前的地址';

alter table mk_order_excel add export_count tinyint(4) NOT NULL DEFAULT '0' COMMENT '导出次数';

alter table mk_user_sender add is_default tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否为默认地址';

alter table mk_user_addressee add is_default tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否为默认地址';
