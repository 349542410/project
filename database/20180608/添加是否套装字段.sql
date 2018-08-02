alter table mk_tran_uorder add is_suit tinyint(1) default 0 comment '是否套装（默认为 0   0为非套装， 1为套装）';

ALTER TABLE mk_tran_order ADD is_suit TINYINT(1) DEFAULT 0 COMMENT  '是否套装（默认为 0   0为非套装， 1为套装）';