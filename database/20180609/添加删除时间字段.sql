alter table mk_tran_ulist add delete_time timestamp NULL DEFAULT NULL COMMENT '删除时间';

alter table mk_tran_uorder add delete_time timestamp NULL DEFAULT NULL COMMENT '删除时间';

alter table mk_user_sender add delete_time timestamp NULL DEFAULT NULL COMMENT '删除时间';

alter table mk_user_addressee add delete_time timestamp NULL DEFAULT NULL COMMENT '删除时间';

