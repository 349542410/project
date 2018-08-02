

alter table mk_transit_center add no_limit_region varchar(200) default NULL comment '不限额地区';

alter table mk_transit_center add single_piece_limit double(10,2) default 0.00 comment '单件商品最大限额';