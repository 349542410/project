
alter table mk_user_extra_info add sex varchar(2) default NULL comment '性别';


alter table mk_user_extra_info add nation varchar(6) default NULL comment '民族';


alter table mk_user_extra_info add birth varchar(15) default NULL comment '出生日期';


alter table mk_user_extra_info add address varchar(50) default NULL comment '身份证地址';


alter table mk_user_extra_info add authority varchar(50) default NULL comment '身份证发证机关';


alter table mk_user_extra_info add valid_date_start varchar(15) default NULL comment '身份证证件有效期开始时间';


alter table mk_user_extra_info add valid_date_end varchar(15) default NULL comment '身份证证件有效期结束时间';


alter table mk_user_extra_info add idcard_status tinyint(1) default 1 comment '身份证失效状态 1 为可用 2为不可用 (注:距离证件结束时间14天的为失效，身份证信息不可用)';