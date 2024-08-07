<?PHP

	$tbl_schema=array();

	$tbl_schema['config'] = "
	CREATE TABLE IF NOT EXISTS `wm_config` (
		name varchar(50) not null,
		value varchar(300),
		primary key name (name),
		reg_date int (10),
		edt_date int (10),
		admin_id varchar(20)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
	";

	$tbl_schema['card_ready']="CREATE TABLE `$tbl[card_ready]` (
`no` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`ono` VARCHAR( 15 ) NOT NULL ,
`value` TEXT NOT NULL ,
PRIMARY KEY ( `no` ) ,
INDEX ( `ono` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	$tbl_schema['emoney']="CREATE TABLE `$tbl[emoney]` (
  `no` int(11) NOT NULL auto_increment,
  `member_no` int(11) NOT NULL default '0',
  `member_id` varchar(20) NOT NULL default '',
  `member_name` varchar(10) NOT NULL default '',
  `title` varchar(100) NOT NULL default '',
  `amount` int(5) NOT NULL default '0',
  `ctype` enum('-','+') NOT NULL default '+',
  `mtype` varchar(2) NOT NULL default '',
  `member_emoney` int(5) NOT NULL default '0',
  `reg_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`no`),
  KEY `member_no` (`member_no`,`member_id`,`member_name`,`reg_date`),
  KEY `mtype` (`mtype`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	// 관리자 메뉴 2006-10-12
	$tbl_schema['manage_menu_static_total']="CREATE TABLE `$tbl[manage_menu_static_total]` (
`no` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`menu` VARCHAR( 77 ) NOT NULL ,
`hit` INT( 11 ) NOT NULL ,
PRIMARY KEY ( `no` ) ,
INDEX ( `menu` , `hit` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	$tbl_schema['manage_menu_static_day']="CREATE TABLE `$tbl[manage_menu_static_day]` (
  `no` int(11) NOT NULL auto_increment,
  `menu` varchar(77) NOT NULL default '',
  `hit` int(11) NOT NULL default '0',
  `date` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`no`),
  KEY `menu` (`menu`,`hit`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	$tbl_schema['order_no']="CREATE TABLE `$tbl[order_no]` (
`no` INT( 13 ) NOT NULL AUTO_INCREMENT ,
`ono1` VARCHAR( 8 ) NOT NULL ,
`ono2` VARCHAR( 5 ) NOT NULL ,
PRIMARY KEY ( `no` ) ,
UNIQUE (
`ono2`
)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	$tbl_schema['mng_log']="CREATE TABLE `$tbl[mng_log]` (
  `no` int(13) NOT NULL,
  `member_id` varchar(16) NOT NULL default '',
  `login_result` char(1) NOT NULL default '',
  `log_date` int(11) NOT NULL default '0',
  `ip` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`no`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	$tbl_schema['mari_board_day']="CREATE TABLE `mari_board_day` (
  `no` int(11) NOT NULL auto_increment,
  `member_no` int(11) NOT NULL default '0',
  `member_id` varchar(16) NOT NULL default '',
  `db` varchar(20) NOT NULL default '',
  `date` varchar(10) NOT NULL default '',
  `write` int(5) NOT NULL default '0',
  `comment` int(5) NOT NULL default '0',
  PRIMARY KEY  (`no`),
  KEY `member_no` (`member_no`,`member_id`,`db`,`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	 // 2007-01-08 : 주문상태로그
	$tbl_schema['order_stat_log']="CREATE TABLE `$tbl[order_stat_log]` (
`no` int(11) NOT NULL auto_increment,
`ono` varchar(15) NOT NULL,
`stat` varchar(2) NOT NULL,
`ori_stat` varchar(2) NOT NULL,
`member_id` varchar(20) NOT NULL,
`member_no` int(11) NOT NULL,
`member_name` varchar(10) NOT NULL,
`admin_id` varchar(20) NOT NULL,
`admin_no` int(11) NOT NULL,
`reg_date` int(11) NOT NULL default '0',
`system` enum('Y', 'N') NOT NULL default 'N',
PRIMARY KEY  (`no`),
KEY `ono` (`ono`),
KEY `stat` (`stat`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	// 2007-02-06 : 설문조사 테이블
	$tbl_schema['poll_config']="CREATE TABLE `$tbl[poll_config]` (
`no` int(11) NOT NULL auto_increment,
`title` varchar(255) NOT NULL default '',
`dupl` char(1) NOT NULL default '',
`auth` char(1) NOT NULL default '',
`sdate` varchar(10) NOT NULL default '',
`fdate` varchar(10) NOT NULL default '',
`stat` char(1) NOT NULL default '',
`total_vote` int(5) NOT NULL default '0',
`total_item` tinyint(4) NOT NULL default '0',
`content` text NOT NULL,
`upfile1` varchar(100) NOT NULL,
`reg_date` int(11) NOT NULL default '0',
`voted` text NOT NULL,
PRIMARY KEY  (`no`),
KEY `fdate` (`fdate`,`stat`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	// 2007-02-06 : 설문조사 아이템 테이블
	$tbl_schema['poll_item']="CREATE TABLE `$tbl[poll_item]` (
`no` int(11) NOT NULL auto_increment,
`ref` int(11) NOT NULL default '0',
`title` varchar(255) NOT NULL default '',
`total` int(11) NOT NULL default '0',
`sort` tinyint(5) NOT NULL default '0',
PRIMARY KEY  (`no`),
KEY `ref` (`ref`,`sort`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	// 2007-02-06 : 설문조사 댓글 테이블
	$tbl_schema['poll_comment']="CREATE TABLE `$tbl[poll_comment]` (
`no` int(11) NOT NULL auto_increment,
`ref` int(11) NOT NULL default '0',
`name` varchar(12) NOT NULL default '',
`member_id` varchar(12) NOT NULL default '',
`member_no` int(11) NOT NULL,
`pwd` varchar(20) NOT NULL default '',
`content` text NOT NULL default '',
`ip` varchar(15) NOT NULL default '',
`reg_date` int(11) NOT NULL default '0',
PRIMARY KEY  (`no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	// 2007-03-02 : 출석체크 이벤트
	$tbl_schema['attend']="CREATE TABLE `$tbl[attend]` (
`no` int(11) not null auto_increment,
`title` varchar(100) not null,
`sdate` varchar(10) not null,
`fdate` varchar(10) not null,
`milage` int(11) not null,
`charge` enum('Y', 'N') not null default 'N',
`charge_date` int(11) not null,
`total` int(11) not null,
`reg_date` int(11) not null,
PRIMARY KEY  (`no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	// 2007-03-02 : 출석체크 현황
	$tbl_schema['attend_member']="CREATE TABLE `$tbl[attend_member]` (
`no` int(11) not null auto_increment,
`ano` int(11) not null,
`member_no` int(11) not null,
`total` int(11) not null,
`last_date` varchar(10) not null,
PRIMARY KEY  (`no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	// 2007-04-02 : 세트할인
	$tbl_schema['product_saleset']="CREATE TABLE `$tbl[product_saleset]` (
`no` int(11) not null auto_increment,
`name` varchar(100) NOT NULL,
`per` varchar(5) NOT NULL,
`use_this` enum('N', 'Y') NOT NULL default 'Y',
`sdate` varchar(10) NOT NULL,
`fdate` varchar(10) NOT NULL,
`prdA` text NOT NULL,
`prdB` text NOT NULL,
`sort` int(11) not null,
PRIMARY KEY  (`no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	// 2007-04-05 : 지역별 배송료
	$tbl_schema['delivery_area']="CREATE TABLE `$tbl[delivery_area]` (
`no` int(11) not null auto_increment,
`area` text NOT NULL,
`price` int(5) NOT NULL,
PRIMARY KEY  (`no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	// 2007-04-19 : 주문 수량및 옵션변경 기록
	$tbl_schema['order_product_log']="CREATE TABLE `$tbl[order_product_log]` (
`no` int(10) not null auto_increment,
`ono` varchar(20) NOT NULL,
`opno` varchar(20) NOT NULL,
`pno` int(10) NOT NULL,
`stat` int(2) not null,
`ori_stat` int(2) not null,
`dlv_no` int(10) null default '0',
`dlv_code` varchar(30) null default '',
`admin_id` varchar(100) null default '',
`member_id` varchar(100) null default '',
`reg_date` int(10) not null,
PRIMARY KEY  (`no`),
KEY `ono` (`ono`),
KEY `opno` (`opno`),
KEY `pno` (`pno`),
KEY `crema` (`stat`, `reg_date`),
KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	$tbl_schema['trigger_orderStatUpdate'] = "CREATE TRIGGER `orderStat`
AFTER UPDATE ON `wm_order_product`
FOR EACH ROW
BEGIN

	IF(old.stat != new.stat or old.dlv_code != new.dlv_code) THEN
		insert into wm_order_product_log
		(ono, opno, pno, stat, ori_stat, dlv_no, dlv_code, admin_id, member_id, reg_date)
		values
		(old.ono, old.no, old.pno, new.stat, old.stat, new.dlv_no, new.dlv_code, @admin_id, @member_id, unix_timestamp(now()));
	END IF;

END;";

	$tbl_schema['trigger_orderStatInsert'] = "CREATE TRIGGER `orderStatMake`
AFTER INSERT ON `wm_order_product`
FOR EACH ROW
BEGIN

	insert into wm_order_product_log
	(ono, opno, pno, stat, ori_stat, dlv_no, dlv_code, admin_id, member_id, reg_date)
	values
	(new.ono, new.no, new.pno, new.stat, 0, 0, '', @admin_id, @member_id, unix_timestamp(now()));

END;";

	// 2007-06-29 : 현금영수증 발급신청테이블 - Richie
	$tbl_schema['cash_receipt']=
"CREATE TABLE `$tbl[cash_receipt]` (
`no` int(10) unsigned NOT NULL auto_increment,
`ono` varchar(15) NOT NULL default '',
`stat` tinyint(1) NOT NULL default '1',
`tsdtime` varchar(12) NOT NULL default '',
`amt1` int(11) NOT NULL default '0',
`amt2` int(11) NOT NULL default '0',
`amt3` int(11) NOT NULL default '0',
`amt4` int(11) NOT NULL default '0',
`b_num` varchar(10) NOT NULL,
`cash_reg_num` varchar(20) NOT NULL default '',
`pay_type` varchar(2) NOT NULL default '',
`mcht_name` varchar(255) NOT NULL default '',
`prod_name` varchar(255) NOT NULL default '',
`cons_name` varchar(50) NOT NULL default '',
`cons_tel` varchar(15) NOT NULL default '',
`cons_email` varchar(50) NOT NULL default '',
`authno` varchar(9) NOT NULL default '',
`mtrsno` varchar(12) NOT NULL default '',
`reg_date` int(11) NOT NULL default '0',
PRIMARY KEY  (`no`),
KEY `stat` (`stat`),
KEY `ono` (`ono`),
KEY `reg_date` (`reg_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

	// 2007-10-29 : 포인트내역
	$tbl_schema['point']="CREATE TABLE `$tbl[point]` (
  `no` int(11) NOT NULL auto_increment,
  `member_no` int(11) NOT NULL default '0',
  `member_id` varchar(20) NOT NULL default '',
  `member_name` varchar(10) NOT NULL default '',
  `title` varchar(100) NOT NULL default '',
  `amount` int(5) NOT NULL default '0',
  `ctype` enum('-','+') NOT NULL default '+',
  `mtype` varchar(2) NOT NULL default '',
  `member_point` int(5) NOT NULL default '0',
  `reg_date` int(11) NOT NULL default '0',
  `admin_id` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`no`),
  KEY `member_no` (`member_no`,`member_id`,`member_name`,`reg_date`),
  KEY `mtype` (`mtype`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";


//2009-01-30 : 이미지 업로더
$tbl_schema['neko']="CREATE TABLE `wm_neko` (
  `no` int(16) NOT NULL auto_increment,
  `member_id` varchar(32) default NULL,
  `neko_id` varchar(64) NOT NULL,
  `neko_gr` varchar(64) NOT NULL,
  `updir` varchar(64) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `size` int(10) NOT NULL default '0',
  `width` int(5) default '0',
  `height` int(5) default '0',
  `lock` enum('N','Y') NOT NULL default 'N',
  `hidden` enum('N','Y') NOT NULL default 'N',
  `regdate` int(16) NOT NULL default '0',
  KEY `no` (`no`),
  KEY `neko_id` (`neko_id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";


//2008-05-13 : 삭제로그
$tbl_schema['delete_log']="
CREATE TABLE `wm_delete_log` (
  `no` int(16) NOT NULL auto_increment,
  `type` enum('P','O') NOT NULL default 'P',
  `deleted` varchar(64) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `admin` varchar(32) NOT NULL default '',
  `deldate` int(12) NOT NULL default '0',
  KEY `no` (`no`),
  KEY `admin` (`admin`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

// 2008-07-18 : 회원엑셀출력로그
$tbl_schema['member_xls_log']="
CREATE TABLE `$tbl[member_xls_log]` (
  `no` int(11) NOT NULL auto_increment,
  `admin_no` int(11) NOT NULL,
  `admin_id` varchar(20) NOT NULL,
  `admin_level` int(1) NOT NULL,
  `reg_date` int(11) NOT NULL,
  PRIMARY KEY  (`no`),
  INDEX `admin_id` (`admin_id`),
  INDEX `admin_no` (`admin_no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

// 2008-08-04 : 위사 고객센터 접근로그
$tbl_schema['mng_cs_log']="
CREATE TABLE `$tbl[mng_cs_log]` (
  `no` int(11) NOT NULL auto_increment,
  `admin_no` int(11) NOT NULL,
  `admin_id` varchar(20) NOT NULL,
  `admin_level` int(1) NOT NULL,
  `send_site_info` text NOT NULL,
  `req_now` int(11) NOT NULL,
  `success` enum('N', 'Y') default 'N',
  `reg_date` int(11) NOT NULL,
  PRIMARY KEY  (`no`),
  INDEX `req_now` (`req_now`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

// 2008-08-06 : 인트라넷 사내게시판
$tbl_schema['intra_board']="
CREATE TABLE `$tbl[intra_board]` (
  `no` int(11) NOT NULL auto_increment,
  `db` varchar(20) NOT NULL default '',
  `ref` int(11) NOT NULL default '0',
  `step` tinyint(4) NOT NULL default '0',
  `level` tinyint(4) NOT NULL default '0',
  `member_id` varchar(12) default NULL,
  `name` varchar(12) NOT NULL default '',
  `homepage` varchar(100) NOT NULL,
  `member_no` mediumint(9) NOT NULL,
  `member_level` varchar(2) NOT NULL,
  `ip` varchar(15) NOT NULL default '',
  `title` varchar(200) NOT NULL default '',
  `content` text NOT NULL,
  `hit` smallint(5) NOT NULL default '0',
  `total_comment` tinyint(4) NOT NULL default '0',
  `secret` enum('Y','N') NOT NULL default 'N',
  `html` enum('1','2','3') NOT NULL default '1',
  `notice` enum('Y','N') default 'N',
  `upfile1` varchar(50) NOT NULL,
  `upfile2` varchar(50) NOT NULL,
  `ori_upfile1` varchar(200) NOT NULL,
  `ori_upfile2` varchar(200) NOT NULL,
  `updir` varchar(50) default NULL,
  `view_member` text NOT NULL,
  `reg_date` int(11) NOT NULL,
  PRIMARY KEY  (`no`),
  KEY `db` (`db`),
  KEY `ref` (`ref`),
  KEY `step` (`step`),
  KEY `level` (`level`),
  KEY `reg_date` (`reg_date`),
  KEY `notice` (`notice`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

// 2008-08-06 : 인트라넷 사내게시판 설정
$tbl_schema['intra_board_config']="
CREATE TABLE `$tbl[intra_board_config]` (
  `no` int(11) NOT NULL auto_increment,
  `db` varchar(20) NOT NULL default '',
  `title` varchar(50) NOT NULL default '',
  `total_content` int(11) NOT NULL default '0',
  `auth_list` varchar(2) NOT NULL default '3',
  `auth_write` varchar(2) NOT NULL default '3',
  `auth_view` varchar(2) NOT NULL default '3',
  `auth_reply` varchar(2) NOT NULL default '3',
  `auth_comment` varchar(2) NOT NULL default '3',
  `auth_upload` varchar(2) NOT NULL default '2',
  `team` text NOT NULL,
  `upfile_ext` varchar(50) default 'jpg|jpeg|gif',
  `upfile_size` varchar(10) default '1024',
  PRIMARY KEY (`no`),
  KEY `db` (`db`),
  KEY `title` (`title`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

// 2008-08-07 : 인트라넷 댓글
$tbl_schema['intra_comment']="
CREATE TABLE `$tbl[intra_comment]` (
  `no` int(11) NOT NULL auto_increment,
  `db` varchar(20) NOT NULL default '',
  `ref` int(11) NOT NULL default '0',
  `name` varchar(12) NOT NULL default '',
  `member_id` varchar(12) NOT NULL default '',
  `member_no` int(11) NOT NULL default '0',
  `content` text NOT NULL,
  `ip` varchar(15) NOT NULL default '',
  `reg_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`no`),
  KEY `db` (`db`),
  KEY `ref` (`ref`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

// 2008-08-08 : 인트라넷 사원 조직도
$tbl_schema['intra_group']="
CREATE TABLE `$tbl[intra_group]` (
  `no` int(11) NOT NULL auto_increment,
  `name` varchar(20) NOT NULL default '',
  `ref` int(11) NOT NULL default '0',
  `level` int(11) NOT NULL default '0',
  PRIMARY KEY  (`no`),
  KEY `ref` (`ref`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

// 2008-08-11 : 인트라넷 출석체크
$tbl_schema['intra_day_check']="
CREATE TABLE `$tbl[intra_day_check]` (
  `no` int(11) NOT NULL auto_increment,
  `member_no` int(11) NOT NULL,
  `date` varchar(10) NOT NULL,
  `stime` int(11) NOT NULL,
  `etime` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL default '',
  `late` enum('Y','N') NOT NULL default 'Y',
  `re_modi` enum('Y','N') NOT NULL default 'N',
  `mod_date` int(11) NOT NULL default '0',
  `late_detail` text NOT NULL,
  PRIMARY KEY  (`no`),
  KEY `date` (`date`),
  KEY `stime` (`stime`),
  KEY `late` (`late`),
  KEY `member_no` (`member_no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

// 2008-08-11 : 인트라넷 일정
$tbl_schema['intra_schedule']="
CREATE TABLE `$tbl[intra_schedule]` (
  `no` int(11) NOT NULL auto_increment,
  `date` varchar(10) NOT NULL,
  `title` varchar(50) NOT NULL default '',
  `alarm` enum('Y','N') NOT NULL default 'N',
  `font_color` varchar(7) NOT NULL,
  `content` text NOT NULL,
  `reg_date` int(11) NOT NULL,
  PRIMARY KEY  (`no`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";


// 2008-10-30 : 새로운 출첵 기능
$tbl_schema['new_attend'] = "
	CREATE TABLE `wm_attend_day` (
		`no` int(11) NOT NULL auto_increment,
		`member_no` int(11) NOT NULL default '0',
		`_date` varchar(10) default NULL,
		`charge` enum('N','Y') NOT NULL default 'N',
		PRIMARY KEY  (`no`),
		KEY `member_no`(`member_no`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";

// 2008-11-07 : 상품상태변경 로그
$tbl_schema['product_stat_log'] = "
CREATE TABLE `$tbl[product_stat_log]` (
  `no` int(11) NOT NULL auto_increment,
  `pno` int(11) NOT NULL,
  `stat` int(2) NOT NULL,
  `ori_stat` int(2) NOT NULL,
  `admin_id` varchar(20) NOT NULL,
  `admin_no` int(11) NOT NULL,
  `ono` varchar(20) NOT NULL,
  `reg_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`no`),
  KEY `pno` (`pno`),
  KEY `ono` (`ono`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";

// 2008-11-07 : 쿠폰 로그
$tbl_schema['coupon_log'] = "
CREATE TABLE `$tbl[coupon_log]` (
  `no` int(11) NOT NULL auto_increment,
  `cno` int(11) NOT NULL,
  `is_type` enum('A', 'B') default 'A',
  `cname` varchar(100) NOT NULL,
  `stat` enum('1', '2', '3', '4') NOT NULL,
  `admin_id` varchar(20) NOT NULL,
  `admin_no` int(11) NOT NULL,
  `content` text NOT NULL,
  `ip` varchar(20) NOT NULL,
  `reg_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`no`),
  KEY `cno` (`cno`),
  KEY `stat` (`stat`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";

// 2008-11-07 : 상품 로그
$tbl_schema['product_log'] = "
CREATE TABLE `$tbl[product_log]` (
  `no` int(11) NOT NULL auto_increment,
  `pno` int(11) NOT NULL,
  `pname` varchar(100) NOT NULL,
  `stat` enum('1', '2', '3', '4') NOT NULL,
  `admin_id` varchar(20) NOT NULL,
  `admin_no` int(11) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `reg_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`no`),
  KEY `pno` (`pno`),
  KEY `stat` (`stat`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";

// 2008-11-10 : 비밀번호 변경 로그
$tbl_schema['pwd_log'] = "
CREATE TABLE `$tbl[pwd_log]` (
  `no` int(11) NOT NULL auto_increment,
  `stat` enum('1', '2') NOT NULL default '1',
  `member_no` int(11) NOT NULL,
  `member_id` varchar(16) NOT NULL,
  `member_name` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `key` varchar(100) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `reg_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`no`),
  KEY `stat` (`stat`),
  KEY `member_id` (`member_id`),
  KEY `key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";

// 2008-11-14 : 실명확인 로그
$tbl_schema['namecheck_log'] = "
CREATE TABLE `$tbl[namecheck_log]` (
  `no` int(11) NOT NULL auto_increment,
  `name` varchar(10) NOT NULL,
  `jumin` varchar(14) NOT NULL,
  `res_cd` varchar(5) NOT NULL,
  `res_dcd` varchar(5) NOT NULL,
  `res_msg` varchar(200) NOT NULL,
  `minor` varchar(5) NOT NULL,
  `reason` varchar(5) NOT NULL,
  `foreigner` varchar(5) NOT NULL,
  `dupeInfo` varchar(5) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `reg_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`no`),
  KEY `name` (`name`),
  KEY `jumin` (`jumin`),
  KEY `res_cd` (`res_cd`),
  KEY `reg_date` (`reg_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";

// 2008-11-18 : 카드거래취소 로그
$tbl_schema['card_cc_log'] = "
CREATE TABLE `$tbl[card_cc_log]` (
  `no` int(11) NOT NULL auto_increment,
  `cno` int(11) NOT NULL,
  `stat` enum('1', '2') NOT NULL default '1',
  `ono` varchar(15) NOT NULL,
  `price` int(11) NOT NULL,
  `tno` varchar(50) NOT NULL,
  `res_cd` varchar(10) NOT NULL,
  `res_msg` varchar(100) NOT NULL,
  `admin_id` varchar(20) NOT NULL,
  `admin_no` int(11) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `reg_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`no`),
  KEY `cno` (`cno`),
  KEY `ono` (`ono`),
  KEY `reg_date` (`reg_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";

// 2008-12-29 : 인트라넷 사원 세부 권한
$tbl_schema['mng_auth'] = "
CREATE TABLE `$tbl[mng_auth]` (
  `no` int(11) NOT NULL auto_increment,
  `admin_no` int(11) NOT NULL,
  `config` text NOT NULL,
  `product` text NOT NULL,
  `order` text NOT NULL,
  `income` text NOT NULL,
  `member` text NOT NULL,
  `board` text NOT NULL,
  `design` text NOT NULL,
  `log` text NOT NULL,
  `extension` text NOT NULL,
  `media` text NOT NULL,
  `mod_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`no`),
  KEY `admin_no` (`admin_no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";

$tbl_schema['log_search_engine'] = "
CREATE TABLE `wm_log_search_engine` (
  `no` int(11) NOT NULL auto_increment,
  `yy` int(4) NOT NULL,
  `mm` int(2) NOT NULL,
  `dd` int(2) NOT NULL,
  `engine` varchar(16) NOT NULL,
  `keyword` varchar(64) NOT NULL,
  `hit` int(8) NOT NULL,
  PRIMARY KEY  (`no`),
  KEY `date` (`yy`,`mm`,`dd`),
  KEY `engine` (`engine`),
  KEY `keyword` (`keyword`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['order_memo'] = "
CREATE TABLE `wm_order_memo` (
  `no` int(10) NOT NULL auto_increment,
  `admin_no` int(10) NOT NULL,
  `admin_id` varchar(16) NOT NULL,
  `ono` varchar(14) NOT NULL,
  `content` text NOT NULL,
  `type` enum('1','2') NOT NULL default '1' COMMENT '1.주문메모 2.회원메모',
  `reg_date` int(10) NOT NULL,
  PRIMARY KEY  (`no`),
  KEY `admin_no` (`admin_no`,`admin_id`),
  KEY `ono` (`ono`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['provider'] = "
CREATE TABLE `wm_provider` (
  `no` int(10) NOT NULL auto_increment,
  `provider` varchar(128) NOT NULL,
  `ptel` varchar(14) NOT NULL,
  `pcell` varchar(14) NOT NULL,
  `account1` varchar(100) NOT NULL,
  `account2` varchar(100) NOT NULL,
  `pceo` varchar(16) NOT NULL,
  `plocation` varchar(128) NOT NULL,
  `content` text NOT NULL,
  `reg_date` int(10) NOT NULL,
  KEY `no` (`no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['domain_expire'] = "
CREATE TABLE `wm_domain_expire` (
  `no` int(10) NOT NULL auto_increment,
  `domain` varchar(50) NOT NULL,
  `expire` varchar(30) NOT NULL,
  `reg_date` int(10) NOT NULL,
  PRIMARY KEY  (`no`),
  UNIQUE KEY `domain` (`domain`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['pbanner'] = "
CREATE TABLE `wm_pbanner` (
  `no` int(10) NOT NULL auto_increment,
  `ref` int(10) NOT NULL COMMENT '그룹코드',
  `name` varchar(100) NOT NULL COMMENT '배너명',
  `link` varchar(255) NOT NULL COMMENT '링크 URL',
  `reg_date` int(10) NOT NULL COMMENT '등록일',
  PRIMARY KEY  (`no`),
  KEY `ref` (`ref`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
";

$tbl_schema['pbanner_group'] = "
CREATE TABLE `wm_pbanner_group` (
  `no` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL COMMENT '프로모션명',
  `code` varchar(50) NOT NULL COMMENT '프로모션 코드',
  `visited` int(10) NOT NULL COMMENT '클릭횟수',
  `icon` varchar(100) NOT NULL COMMENT '아이콘',
  `reg_date` int(10) NOT NULL COMMENT '등록일',
  PRIMARY KEY  (`no`),
  UNIQUE KEY `code_2` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
";

// 2010-03-09 : 아이핀 로그
$tbl_schema['ipin_log'] = "
CREATE TABLE `$tbl[ipin_log]` (
  `no` int(11) NOT NULL auto_increment,
  `niceId` varchar(25) NOT NULL,
  `ordNo` varchar(25) NOT NULL,
  `trNo` varchar(25) NOT NULL,
  `retCd` varchar(25) NOT NULL,
  `retDtlCd` varchar(25) NOT NULL,
  `message` varchar(200) NOT NULL,
  `paKey` varchar(25) NOT NULL,
  `niceNm` varchar(25) NOT NULL,
  `birthday` varchar(8) NOT NULL,
  `sex` varchar(1) NOT NULL,
  `foreigner` varchar(1) NOT NULL,
  `dupeInfo` varchar(64) NOT NULL,
  `join_no` int(11) NOT NULL,
  `coInfo` varchar(5) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `reg_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`no`),
  KEY `ordNo` (`ordNo`),
  KEY `niceNm` (`niceNm`),
  KEY `join_result` (`dupeInfo`, `join_no`),
  KEY `reg_date` (`reg_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";

$tbl_schema['product_option_img'] = "
CREATE TABLE `wm_product_option_img` (
  `no` int(10) NOT NULL auto_increment,
  `pno` int(10) NOT NULL,
  `opno` int(10) NOT NULL,
  `idx` int(3) NOT NULL,
  `updir` varchar(64) NOT NULL,
  `upfile1` varchar(64) NOT NULL,
  `upfile2` varchar(64) NOT NULL,
  `w1` int(4) NOT NULL,
  `w2` int(4) NOT NULL,
  `h1` int(4) NOT NULL,
  `h2` int(4) NOT NULL,
  `size2` int(10) NOT NULL,
  `size1` int(10) NOT NULL,
  `reg_date` int(10) NOT NULL,
  PRIMARY KEY  (`no`),
  KEY `pno` (`pno`,`opno`,`idx`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['product_option_item'] = "
CREATE TABLE `wm_product_option_item` (
  `no` int(10) NOT NULL auto_increment,
  `pno` int(10) NOT NULL,
  `opno` int(10) NOT NULL,
  `iname` varchar(30) NOT NULL,
  `add_price` int(10) NOT NULL COMMENT '옵션별 상품 추가금액',
  `ea` int(5) NOT NULL COMMENT '한정 사용시 수량',
  `sort` int(3) NOT NULL,
  `reg_date` int(10) NOT NULL,
  PRIMARY KEY  (`no`),
  KEY `pno` (`pno`,`opno`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['erp_account'] = "
CREATE TABLE `erp_account` (
  `account_no` int(11) NOT NULL auto_increment,
  `account_date` date NOT NULL,
  `sno` int(11) NOT NULL,
  `pay_amt` int(11) NOT NULL,
  `unpay_amt` int(11) NOT NULL,
  `memo` varchar(200) NOT NULL,
  `reg_date` datetime NOT NULL,
  `reg_user` varchar(16) NOT NULL,
  `upd_date` datetime NOT NULL,
  `upd_user` varchar(16) NOT NULL,
  `remote_ip` varchar(15) NOT NULL,
  PRIMARY KEY  (`account_no`),
  KEY `sno` (`sno`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";


$tbl_schema['erp_complex_option'] = "
CREATE TABLE `erp_complex_option` (
  `complex_no` int(11) NOT NULL auto_increment,
  `barcode` varchar(30) NOT NULL,
  `pno` int(11) NOT NULL,
  `opts` varchar(150) NOT NULL,
  `auto_create_yn` char(1) NOT NULL default 'Y',
  `base_stock_qty` int(11) NOT NULL default '0',
  `safe_stock_qty` int(11) NOT NULL default '0',
  `force_soldout` enum('Y','N') NOT NULL default 'N',
  `del_yn` char(1) NOT NULL default 'N',
  `reg_user` varchar(16) default NULL,
  `reg_date` datetime default NULL,
  `upd_user` varchar(16) default NULL,
  `upd_date` datetime default NULL,
  `remote_ip` varchar(15) default NULL,
  PRIMARY KEY  (`complex_no`),
  UNIQUE KEY `barcode` (`barcode`),
  KEY `pno` (`pno`),
  KEY `opts` (`opts`),
  KEY `del_yn` (`del_yn`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['erp_inout'] = "
CREATE TABLE `erp_inout` (
  `inout_no` int(11) NOT NULL auto_increment,
  `complex_no` int(11) NOT NULL,
  `inout_kind` char(1) NOT NULL,
  `qty` int(11) NOT NULL,
  `remark` varchar(200) NOT NULL,
  `reg_user` varchar(16) NOT NULL,
  `reg_date` datetime NOT NULL,
  `remote_ip` varchar(15) NOT NULL,
  `sno` int(11) NOT NULL,
  `in_price` int(11) NOT NULL,
  `order_dtl_no` int(11) NOT NULL,
  PRIMARY KEY  (`inout_no`),
  KEY `complex_no` (`complex_no`),
  KEY `inout_kind` (`inout_kind`),
  KEY `reg_date` (`reg_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['erp_order'] = "
CREATE TABLE `erp_order` (
  `ono` int(11) NOT NULL auto_increment,
  `order_no` varchar(12) NOT NULL,
  `order_date` date NOT NULL,
  `sno` int(11) NOT NULL,
  `order_stat` varchar(1) NOT NULL,
  `total_qty` int(11) NOT NULL,
  `total_amt` int(11) NOT NULL,
  `reg_user` varchar(16) NOT NULL,
  `reg_date` datetime NOT NULL,
  `upd_user` varchar(16) NOT NULL,
  `upd_date` datetime NOT NULL,
  `remote_ip` varchar(15) NOT NULL,
  PRIMARY KEY  (`ono`),
  KEY `order_date` (`order_date`),
  KEY `sno` (`sno`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['erp_order_dtl'] = "
CREATE TABLE `erp_order_dtl` (
  `order_dtl_no` int(11) NOT NULL auto_increment,
  `order_no` varchar(12) NOT NULL,
  `sno` int(11) NOT NULL,
  `complex_no` int(11) NOT NULL,
  `order_target_qty` int(11) NOT NULL,
  `order_qty` int(11) NOT NULL,
  `order_price` int(11) NOT NULL,
  `remark` varchar(100) NOT NULL,
  PRIMARY KEY  (`order_dtl_no`),
  KEY `order_no` (`order_no`),
  KEY `sno` (`sno`),
  KEY `complex_no` (`complex_no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_func=array();

$tbl_func['curr_stock'] = "
CREATE FUNCTION curr_stock (icomplex_no int) RETURNS int
BEGIN
	DECLARE stock_qty int;
		select sum(if(inout_kind in ('I','U'), qty, -qty)) into stock_qty
		from erp_inout
		where complex_no = icomplex_no;
	RETURN stock_qty;
END
";

$tbl_schema['cash_receipt_log']="
CREATE TABLE `$tbl[cash_receipt_log]` (
  `no` int(11) NOT NULL auto_increment,
  `cno` int(11) NOT NULL,
  `ono` varchar(15) NOT NULL,
  `stat` tinyint(1) NOT NULL,
  `ori_stat` tinyint(1) NOT NULL,
  `price` int(11) NOT NULL,
  `cash_reg_num` varchar(20) NOT NULL,
  `mtrsno` varchar(50) NOT NULL,
  `b_num` varchar(10) NOT NULL,
  `admin_id` varchar(20) NOT NULL,
  `admin_no` int(11) NOT NULL,
  `system` enum('Y', 'N') NOT NULL default 'N',
  `remote_addr` varchar(15) not null default '',
  `reg_date` int(10) NOT NULL,
  PRIMARY KEY  (`no`),
  KEY `cno` (`cno`),
  KEY `ono` (`ono`),
  KEY `cash_reg_num` (`cash_reg_num`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";

$tbl_schema['blacklist_log'] = "
CREATE TABLE IF NOT EXISTS `wm_blacklist_log` (
  `no` int(11) NOT NULL auto_increment,
  `member_no` int(11) NOT NULL default '0',
  `member_id` varchar(16) NOT NULL,
  `admin_id` varchar(15) NOT NULL,
  `blacklist` int(2) NOT NULL default '0',
  `black_reason` text,
  `log_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`no`),
  KEY `member_no` (`member_no`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['delivery_area_detail'] = "
CREATE TABLE IF NOT EXISTS `wm_delivery_area_detail` (
 `no` INT(10) NOT NULL AUTO_INCREMENT ,
 `name` VARCHAR(50) NOT NULL ,
 `sido` VARCHAR(20) NOT NULL ,
 `gugun` VARCHAR(20) NOT NULL ,
 `dong` TEXT NOT NULL ,
 `addprc` INT(10) NOT NULL ,
 `reg_date` INT(10) NOT NULL ,
 `sort` INT(10) NOT NULL ,
  PRIMARY KEY (`no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['session'] = "
CREATE TABLE IF NOT EXISTS `wm_session` (
  `session_id` varchar(64) NOT NULL,
  `data` text NOT NULL,
  `remote_addr` varchar(15) NOT NULL,
  `page` varchar(100) NOT NULL,
  `admin_no` int(10) NOT NULL,
  `member_no` int(10) NOT NULL,
  `regdate` int(10) NOT NULL,
  `accesstime` int(10) NOT NULL,
  PRIMARY KEY  (`session_id`),
  KEY `admin_no` (`admin_no`),
  KEY `member_no` (`member_no`),
  KEY `accesstime` (`accesstime`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";

$tbl_schema['join_sms'] = "
CREATE TABLE IF NOT EXISTS `wm_join_sms` (
  `no` int(10) NOT NULL auto_increment,
  `phone` varchar(100) NOT NULL COMMENT '전송전화번호',
  `reg_code` varchar(100) NOT NULL COMMENT '생성인증코드',
  `reg_date` int(10) NOT NULL COMMENT '생성일자',
  PRIMARY KEY  (`no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
";

$tbl_schema['join_sms_new'] = "
CREATE TABLE IF NOT EXISTS `wm_join_sms_new` (
  `no` int(10) NOT NULL auto_increment,
  `type` enum('1', '2') default '1',
  `phone` varchar(100) NOT NULL COMMENT '전송전화번호',
  `session_id` varchar(100) NOT NULL COMMENT '세션아이디',
  `reg_code` varchar(100) NOT NULL COMMENT '생성인증코드',
  `reg_date` int(10) NOT NULL COMMENT '생성일자',
  PRIMARY KEY  (`no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

";

$tbl_schema['order_config_prdprc'] = "
create table $tbl[order_config_prdprc] (
	no int(10) not null auto_increment,
	prd_prc int(10) not null default '0',
	per int(10) not null default '0',
	unit char(1) not null default 'p',
	reg_date int(10) not null default '0',
	primary key (no),
	index (prd_prc)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
";

$tbl_schema['product_refprd'] = "
create table if not exists $tbl[product_refprd] (
  `no` int(10) NOT NULL auto_increment COMMENT 'idx',
  `pno` int(10) NOT NULL COMMENT '본상품번호',
  `group` int(2) NOT NULL COMMENT '그룹번호',
  `refpno` int(11) NOT NULL COMMENT '관련상품번호',
  `sort` int(4) NOT NULL COMMENT '정렬순서',
  `reg_date` int(10) NOT NULL COMMENT '등록일시',
  PRIMARY KEY  (`no`),
  KEY `pno` (`pno`,`group`),
  KEY `refpno` (`refpno`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 auto_increment=1 ;
";

$tbl_schema['member_checker'] = "
create table if not exists $tbl[member_checker] (
	`no` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'idx',
	`name` VARCHAR(32) NOT NULL COMMENT '특별그룹회원이름' COLLATE 'utf8_general_ci',
	`members` INT(11) NOT NULL DEFAULT '0' COMMENT '대상회원수',
	`reg_date` INT(11) NOT NULL COMMENT '등록일시',
	`no_milage` ENUM('N','Y') NULL DEFAULT 'N' COLLATE 'utf8_general_ci',
	`no_sale` ENUM('N','Y') NULL DEFAULT 'N' COLLATE 'utf8_general_ci',
	`no_discount` ENUM('Y','N') NOT NULL DEFAULT 'N' COLLATE 'utf8_general_ci',
	`no_coupon` ENUM('Y','N') NOT NULL DEFAULT 'N' COLLATE 'utf8_general_ci',
	`no_pg` ENUM('N','Y') NULL DEFAULT 'N' COLLATE 'utf8_general_ci',
	`deny` ENUM('N','Y') NULL DEFAULT 'N' COLLATE 'utf8_general_ci',
	`homepage` VARCHAR(100) NOT NULL COLLATE 'utf8_general_ci',
	`login_msg_type` CHAR(1) NULL DEFAULT 'N' COLLATE 'utf8_general_ci',
	`login_msg` TEXT NOT NULL COLLATE 'utf8_general_ci',
	PRIMARY KEY (`no`) USING BTREE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
";

$tbl_schema['attend_new'] = "
create table if not exists $tbl[attend_new] (
  `no` int(10) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL COMMENT '이벤트명',
  `start_date` int(10) NOT NULL COMMENT '시작일',
  `finish_date` int(10) NOT NULL COMMENT '종료일',
  `event_type` char(1) NOT NULL COMMENT '1.누적 2.연속',
  `complete_day` int(5) NOT NULL COMMENT '총 필요 출석일',
  `prize_type` char(1) NOT NULL COMMENT '1.쿠폰 2.적립금 3.포인트',
  `prize_cno` int(10) NOT NULL COMMENT '지급 쿠폰번호',
  `prize_milage` int(8) NOT NULL COMMENT '지급 적립금',
  `prize_point` int(8) NOT NULL COMMENT '지급 포인트',
  `repeat_type` char(1) NOT NULL COMMENT '1.한번지급 2.중복지급',
  `check_type` char(1) NOT NULL COMMENT '1.출석체크시 2.로그인시',
  `check_cnt` int(10) NOT NULL COMMENT '총 참여횟수',
  `prize_cnt` int(10) NOT NULL COMMENT '총 지급횟수',
  `check_use` char(1) NOT NULL COMMENT '사용여부',
  `reg_date` int(10) NOT NULL COMMENT '등록일시',
  PRIMARY KEY  (`no`),
  KEY `start_date` (`start_date`),
  KEY `finish_date` (`finish_date`),
  KEY `check_use` (`check_use`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['attend_list'] = "
CREATE TABLE IF NOT EXISTS `wm_attend_list` (
  `no` int(10) NOT NULL auto_increment,
  `eno` int(10) NOT NULL COMMENT '출석체크 번호',
  `member_no` int(10) NOT NULL COMMENT '회원번호',
  `check_date` varchar(10) NOT NULL COMMENT '출첵일',
  `total_cnt` int(4) NOT NULL COMMENT '총 참여수',
  `straight_cnt` int(4) NOT NULL COMMENT '연속 참여수',
  `prize_cno` int(10) NOT NULL COMMENT '지급 쿠폰',
  `prize_milage` int(8) NOT NULL COMMENT '지급 적립금',
  `prize_point` int(8) NOT NULL COMMENT '지급 포인트',
  `reg_date` int(10) NOT NULL COMMENT '출석체크일',
  PRIMARY KEY  (`no`),
  UNIQUE KEY `eno_2` (`eno`,`member_no`,`check_date`),
  KEY `eno` (`eno`),
  KEY `total_cnt` (`total_cnt`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['order_payment'] = "
CREATE TABLE IF NOT EXISTS `wm_order_payment` (
  `no` int(10) NOT NULL AUTO_INCREMENT,
  `ono` varchar(26) DEFAULT NULL,
  `pno` varchar(100) NOT NULL comment '정상상품',
  `pno2` varchar(100) NOT NULL comment '취소상품',
  `pay_type` int(2) NOT NULL,
  `amount` double(10,2) NOT NULL comment '변동금액',
  `dlv_prc` double(8,2) NOT NULL comment '생성배송비',
  `add_dlv_prc` double(8,2) NOT NULL comment '주문금액 변환 추가 배송비',
  `ex_dlv_type` char(1) NOT NULL comment '교환반송배송비 1.편도 2.왕복',
  `ex_dlv_prc` double(8,2) NOT NULL comment '교환반송배송비',
  `emoney_prc` double(10,2) NOT NULL comment '사용 예치금',
  `milage_prc` double(10,2) NOT NULL comment '사용 적립금',
  `repay_emoney` double(10,2) NOT NULL comment '환불 예치금',
  `repay_milage` double(10,2) NOT NULL comment '환불 적립금',
  `cpn_no` int(10) NOT NULL comment '취소 쿠폰코드',
  `type` char(1) NOT NULL comment '결제 타입',
  `stat` char(1) NOT NULL comment '1.미승인 2.승인 3.취소/복구',
  `reason` varchar(60) NOT NULL,
  `comment` text NOT NULL,
  `bank` varchar(50) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `bank_account` varchar(100) NOT NULL,
  `reg_id` varchar(15) NOT NULL,
  `reg_date` int(10) NOT NULL,
  `confirm_id` varchar(15) NOT NULL,
  `confirm_date` int(10) NOT NULL,
  PRIMARY KEY (`no`),
  KEY `ono` (`ono`),
  KEY `pay_type` (`pay_type`),
  KEY `type` (`type`),
  KEY `stat` (`stat`),
  KEY `reg_id` (`reg_id`),
  KEY `confirm_date` (`confirm_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['claim_reasons'] = "
CREATE TABLE IF NOT EXISTS `wm_claim_reasons` (
  `no` int(10) NOT NULL AUTO_INCREMENT,
  `reason` varchar(100) NOT NULL,
  `sort` int(11) NOT NULL,
  `admin_id` varchar(30) NOT NULL,
  `reg_date` int(10) NOT NULL,
  PRIMARY KEY (`no`),
  UNIQUE KEY `no` (`no`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['member_deleted'] = "
CREATE TABLE IF NOT EXISTS `wm_member_deleted` (
  `no` int(10) NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `name` varchar(40) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `cell` varchar(20) NOT NULL,
  `zip` varchar(7) NOT NULL,
  `addr1` varchar(100) NOT NULL,
  `addr2` varchar(200) NOT NULL,
  `birth` varchar(10) NOT NULL,
  `birth2` int(8) NOT NULL,
  `gender` varchar(3) NOT NULL,
  `milage` int(10) NOT NULL,
  `emoney` int(10) NOT NULL,
  `reg_date` int(10) NOT NULL,
  PRIMARY KEY (`no`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
";

$tbl_schema['excel_preset'] = "
CREATE TABLE IF NOT EXISTS `wm_excel_preset` (
  `no` int(10) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) not null,
  `name` varchar(30) not null,
  `data` varchar(200) not null,
  `sort` int(3) not null,
  `reg_date` int(10) NOT NULL,
  PRIMARY KEY (`no`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
";

$tbl_schema['off_store'] = "
CREATE TABLE IF NOT EXISTS `wm_store` (
  `no` int(10) NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `reg_date` int(10) NOT NULL,
  PRIMARY KEY (`no`),
  KEY `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";
// 해외 배송비 테이블
$tbl_schema['os_delivery_area'] = "
CREATE TABLE `wm_os_delivery_area` (
  `no` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_com` int(11) NOT NULL COMMENT '배송사 no',
  `name` varchar(50) NOT NULL COMMENT '지역명',
  `order` int(11) DEFAULT NULL COMMENT '출력순서',
  PRIMARY KEY (`no`),
  KEY `delivery_com` (`delivery_com`),
  KEY `order` (`order`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='해외배송 지역설정';
";

$tbl_schema['os_delivery_country'] = "
CREATE TABLE `wm_os_delivery_country` (
  `no` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_com` int(11) NOT NULL COMMENT '배송사 no',
  `area_no` int(11) NOT NULL COMMENT '배송지역 no',
  `country_code` varchar(5) DEFAULT NULL COMMENT '나라코드',
  PRIMARY KEY (`no`),
  KEY `delivery_com` (`delivery_com`),
  KEY `area_no` (`area_no`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='배송지역별 국가';

";

$tbl_schema['os_delivery_prc'] = "
CREATE TABLE `wm_os_delivery_prc` (
  `no` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_com` int(11) NOT NULL COMMENT '배송사 no',
  `area_no` int(11) NOT NULL COMMENT '지역 no',
  `weight` double(10,2) DEFAULT NULL COMMENT '무게(kg)',
  `price` double(10,2) unsigned DEFAULT NULL COMMENT '금액',
  `order` int(11) DEFAULT NULL,
  PRIMARY KEY (`no`),
  KEY `delivery_com` (`delivery_com`),
  KEY `area_no` (`area_no`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='무게별 해외배송비';
";

// hs code 관리 테이블
$tbl_schema['hs_code'] = "
CREATE TABLE `wm_hs_code` (
  `no` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'HS code 항목 명',
  `hs_code` varchar(15) NOT NULL COMMENT 'HS code',
  `regdate` int,
  PRIMARY KEY (`no`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='HS Code'";

$tbl_schema['product_link'] = "
CREATE TABLE IF NOT EXISTS `wm_product_link` (
  `idx` int(10) NOT NULL AUTO_INCREMENT,
  `ctype` char(1) NOT NULL,
  `nbig` int(5) NOT NULL,
  `nmid` int(5) NOT NULL,
  `nsmall` int(5) NOT NULL,
  `pno` int(10) NOT NULL,
  `sort_big` int(5) NOT NULL,
  `sort_mid` int(5) NOT NULL,
  `sort_small` int(5) NOT NULL,
  PRIMARY KEY (`idx`),
  UNIQUE KEY `pno` (`pno`,`nbig`,`nmid`,`nsmall`),
  KEY `ctype` (`ctype`),
  KEY `big` (`nbig`),
  KEY `mid` (`nmid`),
  KEY `small` (`nsmall`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['partner_shop'] = "
CREATE TABLE IF NOT EXISTS `wm_partner_shop` (
  `no` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '고유번호',
  `stat` char(1) not null default '1' comment '1.신청 2.정상 3.보류 4.만료',
  `corporate_name` varchar(100) DEFAULT NULL COMMENT '업체명',
  `biz_num` varchar(20) NOT NULL DEFAULT '' COMMENT '사업자번호',
  `com_num` varchar(20) NOT NULL DEFAULT '' COMMENT '통신판매신고번호',
  `service_type1` varchar(20) NOT NULL DEFAULT '' COMMENT '업태',
  `service_type2` varchar(20) NOT NULL DEFAULT '' COMMENT '업종',
  `ceo` varchar(20) NOT NULL DEFAULT '' COMMENT '대표자',
  `zipcode` varchar(7) NOT NULL DEFAULT '' COMMENT '우편번호',
  `addr1` varchar(150) NOT NULL DEFAULT '' COMMENT '주소지1',
  `addr2` varchar(150) NOT NULL DEFAULT '' COMMENT '주소지2',
  `email` varchar(50) NOT NULL DEFAULT '' COMMENT '이메일',
  `cell` varchar(30) NOT NULL DEFAULT '' COMMENT '연락처',
  `siteurl` varchar(50) NOT NULL DEFAULT '' COMMENT '사이트주소',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '제목',
  `content` text NOT NULL DEFAULT '' COMMENT '내용',
  `bank_name` varchar(20) NOT NULL DEFAULT '' COMMENT '계좌명의자',
  `bank` varchar(20) NOT NULL DEFAULT '' COMMENT '은행명',
  `bank_account` varchar(20) NOT NULL DEFAULT '' COMMENT '계좌번호',
  `partner_rate` varchar(50) NOT NULL DEFAULT '' COMMENT '수수료율',
  `dates` int(10) NOT NULL DEFAULT '0' COMMENT '계약시작일',
  `datee` int(10) NOT NULL DEFAULT '0' COMMENT '계약종료일',
  `account_dates` varchar(20) NOT NULL DEFAULT '' COMMENT '정산일자',
  `updir` varchar(25) NOT NULL DEFAULT '' COMMENT '파일경로',
  `upfile1` varchar(255) NOT NULL DEFAULT '' COMMENT '파일명',
  `reg_date` int(11) NOT NULL DEFAULT '0' COMMENT '등록일',
  PRIMARY KEY (`no`),
  KEY `corporate_name` (`corporate_name`),
  KEY `reg_date` (`reg_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='입점사 정보' AUTO_INCREMENT=1 ;
";

$tbl_schema['partner_delivery'] = "
CREATE TABLE IF NOT EXISTS `wm_partner_delivery` (
	no int(11) unsigned NOT NULL AUTO_INCREMENT,
	partner_no int(10) not null comment '입점파트너 코드',
	delivery_type char(1) not null default '3' comment '1.무료 2.착불 3.금액별',
	dlv_fee2 double(10,2) not null default '0' comment '착불배송비',
	delivery_base char(1) not null default '1' comment '1.주문금액 2.결제금액',
	delivery_free_limit double(10,2) not null default '0' comment '무료배송 기준금액',
	delivery_fee double(10,2) not null default '0' comment '배송비',
	delivery_free_milage char(1) not null default 'Y' comment '결제금액 적립금 정책',
	delivery_prd_free char(1) not null default 'N' comment '무료배송상품 사용여부',
	delivery_prd_free2 char(1) not null default 'N' comment '고정배송 무료배송상품 사용여부',
	free_delivery_area char(1) not null default 'N' comment '무료배송시 지역별 배송비 여부',
  PRIMARY KEY (`no`),
  KEY partner_no (partner_no)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['partner_product_log'] = "
CREATE TABLE IF NOT EXISTS `wm_partner_product_log` (
	no int(11) unsigned NOT NULL AUTO_INCREMENT,
	partner_no int(10) not null comment '입점파트너 코드',
	pno int(10) not null default '0' comment '수정상품코드',
	ori_no int(10) not null default '0' comment '원본상품코드',
	stat char(1) not null default '1' comment '1.신청 2.승인 3.반려',
	name varchar(100) not null default '' comment '상품명',
	content text not null default '' comment '변경사유',
	content2 text not null default '' comment '관리자 코멘트',
	reg_date int(10) not null default '0' comment '신청일시',
	admin_id varchar(50) not null default '' comment '신청관리자',
	confirm_date int(10) not null default '0' comment '승인날짜',
  PRIMARY KEY (`no`),
  KEY partner_no (partner_no),
  KEY pno (pno),
  KEY ori_no (ori_no)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['order_account'] = "
CREATE TABLE IF NOT EXISTS `wm_order_account` (
  `no` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `startdate` int(11) NOT NULL DEFAULT '0' COMMENT '입금일',
  `finishdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '입금일',
  `partner_no` int(10) NOT NULL DEFAULT '0' COMMENT '파트너코드',
  `prd_prc` double(10,2) NOT NULL DEFAULT '0.00' COMMENT '총상품금액',
  `dlv_prc` double(8,2) NOT NULL DEFAULT '0.00' COMMENT '업체별배송비',
  `fee_prc` double(10,2) NOT NULL DEFAULT '0.00' COMMENT '총정산금액',
  `input_prc` double(10,2) NOT NULL DEFAULT '0.00' COMMENT '현재정산액',
  `cpn_tot` double(10,2) NOT NULL DEFAULT '0.00' COMMENT '총 쿠폰할인금액',
  `cpn_master` double(10,2) NOT NULL DEFAULT '0.00' COMMENT '쿠폰 본사 부담액',
  `cpn_partner` double(10,2) NOT NULL DEFAULT '0.00' COMMENT '쿠폰 파트너 부담액',
  `stat` char(1) NOT NULL DEFAULT '1' COMMENT '1.정산전 2.신청 3.승인 4.부분정산, 5.정산완료 6.취소',
  `request_date` int(10) NOT NULL DEFAULT '0' COMMENT '정산요청일',
  `confirm_date` int(10) NOT NULL DEFAULT '0' COMMENT '정산승인일',
  `complete_date` int(10) NOT NULL DEFAULT '0' COMMENT '정산완료일',
  PRIMARY KEY (`no`),
  KEY `ono` (`startdate`),
  KEY `partner_no` (`partner_no`),
  KEY `stat` (`stat`),
  KEY `request_date` (`request_date`),
  KEY `confirm_date` (`confirm_date`),
  KEY `complete_date` (`complete_date`),
  KEY `startdate` (`startdate`,`finishdate`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";

$tbl_schema['order_account_refund'] = "
CREATE TABLE IF NOT EXISTS `wm_order_account_refund` (
	`no` INT(10) NOT NULL AUTO_INCREMENT,
	`ono` VARCHAR(30) NOT NULL,
	`payment_no` INT(10) NOT NULL DEFAULT '0',
	`partner_no` INT(10) NOT NULL,
	`prd_prc` DOUBLE(12,2) NOT NULL DEFAULT '0.00',
	`dlv_prc` DOUBLE(10,2) NOT NULL DEFAULT '0.00',
	`dlv_prc_return` DOUBLE(10,2) NOT NULL DEFAULT '0.00',
	`total_prc` DOUBLE(12,2) NOT NULL DEFAULT '0.00',
	`fee_prc` DOUBLE(10,2) NOT NULL DEFAULT '0.00',
	`cpn_fee_m` DOUBLE(10,2) NOT NULL DEFAULT '0.00',
	`cpn_fee` DOUBLE(10,2) NOT NULL DEFAULT '0.00',
	`admin_id` VARCHAR(50) NOT NULL,
	`reg_date` DATETIME NOT NULL,
	`account_idx` INT(10) NOT NULL DEFAULT '0',
	`del_yn` ENUM('Y','N') NOT NULL DEFAULT 'N',
	PRIMARY KEY (`no`),
	INDEX `ono` (`ono`),
	INDEX `partner_no` (`partner_no`),
	INDEX `account_idx` (`account_idx`),
	INDEX `del_yn` (`del_yn`)
)
COMMENT='정산 후 취소 주문'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
";

$tbl_schema['order_account_log'] = "
CREATE TABLE IF NOT EXISTS `wm_order_account_log` (
  `no` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account_idx` int(10) unsigned NOT NULL default '0',
  `title` varchar(100) NOT NULL DEFAULT '',
  `prd_prc` double(10,2) NOT NULL DEFAULT '0.00' COMMENT '총상품금액',
  `dlv_prc` double(8,2) NOT NULL DEFAULT '0.00' COMMENT '업체별배송비',
  `fee_prc` double(10,2) NOT NULL DEFAULT '0.00' COMMENT '총정산금액',
  `input_prc` double(10,2) NOT NULL DEFAULT '0.00' COMMENT '현재정산액',
  `cpn_tot` double(10,2) NOT NULL DEFAULT '0.00' COMMENT '총 쿠폰할인금액',
  `cpn_master` double(10,2) NOT NULL DEFAULT '0.00' COMMENT '쿠폰 본사 부담액',
  `cpn_partner` double(10,2) NOT NULL DEFAULT '0.00' COMMENT '쿠폰 파트너 부담액',
  `stat` char(1) NOT NULL DEFAULT '1' COMMENT '1.정산전 2.신청 3.승인 4.부분정산, 5.정산완료 6.취소',
  `reg_date` int(10) NOT NULL DEFAULT '0' COMMENT '로그생성일',
  `admin_id` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`no`),
  KEY `account_idx` (`account_idx`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";

$tbl_schema['product_option_colorchip'] = "
CREATE TABLE IF NOT EXISTS `wm_product_option_colorchip` (
	no int(11) unsigned NOT NULL AUTO_INCREMENT,
	name varchar(50) not null,
	updir varchar(50) not null,
	upfile1 varchar(50) not null,
	w1 int(4) not null,
	h1 int(4) not null,
	reg_date int(10) not null,
  PRIMARY KEY (`no`),
  KEY name (name)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['coupon_auth_code'] = "
CREATE TABLE IF NOT EXISTS `wm_coupon_auth_code` (
	no int(11) unsigned NOT NULL AUTO_INCREMENT,
	cno int(10) not null default '0',
	auth_code varchar(30) not null default '',
  PRIMARY KEY (`no`),
  UNIQUE KEY `auth_code` (`auth_code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['openmarket_cfg'] = "
CREATE TABLE `wm_openmarket_cfg` (
  `no` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(90) NOT NULL DEFAULT '' COMMENT '마켓명',
  `account_id` varchar(30) NOT NULL DEFAULT '' COMMENT '접속 아이디',
  `api_code` varchar(20) NOT NULL DEFAULT '' COMMENT '쇼핑몰코드',
  `is_active` enum('N', 'Y') NOT NULL DEFAULT 'N' COMMENT '연동 활성화 여부',
  `content` text NOT NULL COMMENT '업체 메모',
  `sort` int(3) unsigned NOT NULL DEFAULT '0' COMMENT '정렬순서',
  `reg_date` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`no`),
  KEY `name` (`name`) COMMENT 'name',
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='오픈마켓별 설정테이블';
";

$tbl_schema['product_openmarket'] = "
CREATE TABLE `wm_product_openmarket` (
  `no` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pno` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '상품번호',
  `api_code` varchar(20) NOT NULL DEFAULT '' COMMENT '오픈마켓 쇼핑몰코드',
  `is_active` enum('N','Y') NOT NULL DEFAULT 'Y' COMMENT '연동여부',
  `sell_prc` double(10,2) NOT NULL DEFAULT '0.00' COMMENT '오픈마켓 판매가격',
  `mall_product_id` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`no`),
  KEY `pno` (`pno`),
  KEY `api_code` (`api_code`),
  KEY `malL_product_id` (`mall_product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='오픈마켓 상품 매칭 테이블';
";

$tbl_schema['openmarket_api_log'] = "
CREATE TABLE `wm_openmarket_api_log` (
  `no` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `method` varchar(30) NOT NULL DEFAULT '',
  `req_data` text NOT NULL,
  `ret_data` text NOT NULL,
  `reg_date` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`no`),
  KEY `method` (`method`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='오픈마켓 API 실행 로그';
";

$tbl_schema['event'] = "
CREATE TABLE IF NOT EXISTS `wm_event` (
  `no` int(10) NOT NULL auto_increment,
  `event_name`   varchar(100) NOT NULL DEFAULT '' COMMENT '이벤트명',
  `event_begin`  int(10)  NOT NULL DEFAULT 0  COMMENT '이벤트시작일',
  `event_finish` int(10)  NOT NULL DEFAULT 0  COMMENT '이벤트종료일',
  `event_use` char(1) NOT NULL DEFAULT 'N' COMMENT '이벤트사용여부 Y.사용 N.미사용',
  `event_min_pay` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '최소결제금액',
  `event_obj`  char(1) NOT NULL DEFAULT '1' COMMENT '대상 1.전체 2.회원 3.기업회원',
  `event_type` char(1) NOT NULL DEFAULT '1' COMMENT '이벤트방식 1.적립 2.할인',
  `event_milage_addable`  char(1) NOT NULL DEFAULT 'N' COMMENT '상품별적립금 적립 시 Y.적립함 N.적립안함',
  `event_milage_addable2` char(1) NOT NULL DEFAULT 'N' COMMENT '상품별적립금 할인 시 Y.적립함 N.적립안함',
  `event_ptype` char(1) NOT NULL DEFAULT '0' COMMENT '결제수단 0.모든결제 2.현금 결제일때만',
  `event_per`   int(10)  NOT NULL DEFAULT 0 COMMENT '할인률',
  `event_round` varchar(10) NOT NULL DEFAULT '10' COMMENT '절사단위',
  `reg_date` int(10) NOT NULL COMMENT '이벤트등록일',
  PRIMARY KEY        (`no`),
  KEY `event_begin`  (`event_begin`),
  KEY `event_finish` (`event_finish`),
  KEY `event_use`    (`event_use`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['sns_join'] = "
CREATE TABLE IF NOT EXISTS `wm_sns_join` (
  `no` int(11) NOT NULL auto_increment,
  `type` varchar(10) NOT NULL COMMENT 'SNS 가입타입 nvr,fb,kko',
  `cid` varchar(100) NOT NULL COMMENT 'SNS id',
  `member_no` int(11) NOT NULL COMMENT '회원번호',
  `member_id` varchar(50) NOT NULL COMMENT '회원아이디',
  `name` varchar(50) DEFAULT NULL COMMENT 'sns 이름',
  `email` varchar(300) DEFAULT NULL COMMENT 'sns 이메일',
  `reg_date` int(11) DEFAULT NULL COMMENT '등록일자 ',
  `data` varchar(2000) DEFAULT NULL COMMENT '넘어온값 serialize',
  PRIMARY KEY (`no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['order_dlv_prc'] = "
CREATE TABLE IF NOT EXISTS `wm_order_dlv_prc` (
  `no` int(10) NOT NULL AUTO_INCREMENT,
  `ono` varchar(26) DEFAULT NULL,
  `partner_no` int(10) NOT NULL comment '파트너번호',
  `dlv_prc` int(10) NOT NULL comment '배송비',
  `first_prc` int(10) NOT NULL comment '업체별 주문 첫 배송비',
  `account_idx` int(10) NOT NULL comment '정산코드',
  PRIMARY KEY (`no`),
  KEY `ono` (`ono`),
  KEY `partner_no` (`partner_no`),
  KEY `account_idx` (`account_idx`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['intercept_ip'] = "
CREATE TABLE IF NOT EXISTS `wm_intercept_ip` (
  `intercept_ip` varchar(15) NOT NULL COMMENT 'IP 리스트',
  `intercept_adm_yn` char(1) NOT NULL DEFAULT 'N' COMMENT '관리자 IP여부 Y.관리자 N.유저',
  `reg_date` int(11) DEFAULT NULL COMMENT '등록일자 ',
   KEY (`intercept_ip`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";

$tbl_schema['mkt_script'] = "
CREATE TABLE `wm_mkt_script` (
	`no` int(10) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
	`name` varchar(200) NOT NULL COMMENT '광고 명',
	`use_yn`enum('N','Y')  NOT NULL COMMENT '사용여부',
	`info` varchar(200)  NOT NULL  COMMENT '담당자정보',
	`memo` text  NOT NULL COMMENT '메모',
	`scr_header` text  NOT NULL  COMMENT '공통헤더 스크립트',
	`scr_top` text  NOT NULL COMMENT '공통산단 스크립트',
	`scr_bottom` text  NOT NULL COMMENT '공통하단 스크립트',
	`scr_detail` text  NOT NULL COMMENT '상품상세 스크립트',
	`scr_cart` text  NOT NULL COMMENT '장바구니 스크립트',
	`scr_cartlist` text  NOT NULL COMMENT '장바구니 상품반복 스크립트',
	`scr_order` text  NOT NULL COMMENT '주문 스크립트',
	`scr_orderlist` text  NOT NULL COMMENT '주문 상품반복 스크립트',
	`scr_join` text  NOT NULL COMMENT '회원가입 완료 스크립트',
	`reg_date` int(10)  NOT NULL COMMENT '등록 일시',
	PRIMARY KEY (`no`),
	KEY `use_yn` (`use_yn`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['common_trashbox'] = "
CREATE TABLE `wm_common_trashbox` (
	`no` int(10) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
	`tblname` varchar(20) NOT NULL DEFAULT '' COMMENT '삭제DB',
	`db` varchar(20) NOT NULL DEFAULT '' COMMENT '게시판삭제일 경우 DB명',
	`title` varchar(200) NOT NULL DEFAULT '' COMMENT '삭제 레코드의 제목',
	`name` varchar(20) NOT NULL DEFAULT '' COMMENT '삭제 레코드의 작성자',
	`data` mediumtext,
	`reg_date` int(10) NOT NULL DEFAULT '0' COMMENT '삭제 레코드의 등록일시',
	`del_date` int(10) NOT NULL DEFAULT '0' COMMENT '삭제일시',
	PRIMARY KEY (`no`),
	KEY `tblname` (`tblname`, `db`),
	KEY `del_date` (`del_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['product_image_tmp'] = "
CREATE TABLE IF NOT EXISTS `wm_product_image_tmp` (
	no int(11) unsigned NOT NULL AUTO_INCREMENT,
	tmpkey varchar(100) not null default '',
	imgtype varchar(2) not null default '1',
	updir varchar(50) not null default '',
	filename varchar(100) not null default '',
	width int(5) not null default 0,
	height int(5) not null default 0,
	size int(10) not null default 0,
	stat char(1) not null default '1',
  PRIMARY KEY (`no`),
  index tmpkey (tmpkey),
  index stat (stat)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['deny_ip'] = "
CREATE TABLE `wm_deny_ip` (
	`no` int(10) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
	`ip` varchar(15) NOT NULL DEFAULT '' COMMENT '차단IP',
	`title` varchar(200) NOT NULL DEFAULT '' COMMENT '차단사유',
	`admin_id` varchar(50) NOT NULL DEFAULT '' COMMENT '등록자',
	`admin_no` int(10) NOT NULL DEFAULT '0' COMMENT '등록자번호',
	`reg_date` int(10) NOT NULL DEFAULT '0' COMMENT '등록일시',
	PRIMARY KEY (`no`),
	INDEX `ip` (`ip`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['alimtalk_template'] = "
CREATE TABLE `wm_alimtalk_template` (
  `no` int(10) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
  `sms_case` int(2) NOT NULL DEFAULT '0',
  `templateCode` varchar(50) NOT NULL DEFAULT '' COMMENT '템플릿 코드',
  `templateName` varchar(50) NOT NULL DEFAULT '' COMMENT '템플릿 네임',
  `templateContent` text NOT NULL COMMENT '템플릿내 용',
  `buttonType` char(1) NOT NULL DEFAULT '' COMMENT 'C:버튼사용, N:없음',
  `buttonName` varchar(50) NOT NULL DEFAULT '' COMMENT '버튼네임',
  `reg_status` varchar(5) NOT NULL DEFAULT '검수상태',
  `tmp_status` char(1) NOT NULL DEFAULT '' COMMENT '템플릿상태',
  `use_yn` enum('N','Y') NOT NULL DEFAULT 'N' COMMENT '적용 여부',
  `reg_date` int(10) NOT NULL DEFAULT '0' COMMENT '등록일시',
  PRIMARY KEY (`no`),
  UNIQUE KEY `templateCode` (`templateCode`),
  KEY `use_yn` (`use_yn`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
";

$tbl_schema['erp_storage'] = "
CREATE TABLE `wm_erp_storage` (
  `no` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(62) NOT NULL,
  `big` int(10) NOT NULL DEFAULT '0',
  `mid` int(10) NOT NULL DEFAULT '0',
  `small` int(10) NOT NULL DEFAULT '0',
  `depth4` int(10) NOT NULL DEFAULT '0',
  `dam` varchar(20) NOT NULL,
  `updir` varchar(50) NOT NULL,
  `upfile1` varchar(100) NOT NULL,
  `upfile2` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `reg_date` int(10) NOT NULL,
  PRIMARY KEY (`no`),
  UNIQUE KEY `cate` (`big`,`mid`,`small`,`depth4`),
  KEY `name`(`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
";

$tbl_schema['urlshortenter'] = "
CREATE TABLE IF NOT EXISTS `$tbl[urlshortenter]` (
  `no` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `shortUrl` varchar(50) NOT NULL,
  `longUrl` varchar(200) NOT NULL,
  `pcode` int(10) NOT NULL,
  `reg_date` int(10) NOT NULL,
  `admin_id` varchar(30) NOT NULL,
  PRIMARY KEY (`no`),
  UNIQUE KEY `shortUrl` (`shortUrl`,`pcode`),
  KEY `pcode` (`pcode`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['cfg_confirm_list'] = "
CREATE TABLE `wm_cfg_confirm_list` (
	`no` int(10) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
	`name` varchar(50) NOT NULL DEFAULT '' COMMENT '설정명',
	`use_yn` enum('Y', 'N') NOT NULL default 'N' COMMENT '사용/사용안함',
	`code` varchar(30) NOT NULL DEFAULT '' COMMENT '페이지코드',
	PRIMARY KEY (`no`),
	INDEX `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['order_addr_log'] = "
CREATE TABLE `wm_order_addr_log` (
	`no` int(10) NOT NULL AUTO_INCREMENT,
	`ono` varchar(30) NOT NULL,
	`opno` int(10) NOT NULL,
	`org_name` varchar(30) NOT NULL,
	`org_zip` varchar(7) NOT NULL,
	`org_addr1` varchar(150) NOT NULL,
	`org_addr2` varchar(150) NOT NULL,
	`org_phone` varchar(30) NOT NULL,
	`org_cell` varchar(30) NOT NULL,
	`new_name` varchar(30) NOT NULL,
	`new_zip` varchar(7) NOT NULL,
	`new_addr1` varchar(150) NOT NULL,
	`new_addr2` varchar(150) NOT NULL,
	`new_phone` varchar(30) NOT NULL,
	`new_cell` varchar(30) NOT NULL,
	`admin_id` varchar(20) NOT NULL,
	`reg_date` int(10) NOT NULL,
	PRIMARY KEY (`no`),
	KEY `ono` (`ono`),
	KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
";

$tbl_schema['partner_sms'] = "
CREATE TABLE `wm_partner_sms` (
	`no` int(10) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
	`case` varchar(2) NOT NULL DEFAULT '' COMMENT '케이스',
	`msg` text NOT NULL DEFAULT '' COMMENT 'sms내용',
	`use_check` enum('Y', 'N') NOT NULL default 'N' COMMENT '사용여부(Y, N)',
	`sms_night` enum('Y', 'H' ,'N') NOT NULL default 'N' COMMENT '발송제한 (N:즉시발송, H:예약발송, Y:발송중단)',
	`alimtalk_code` varchar(50) NOT NULL DEFAULT '',
	`partner_no` int(10) NOT NULL,
	PRIMARY KEY (`no`),
	INDEX `partner_no` (`partner_no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['search_preset'] = "
CREATE TABLE `wm_search_preset` (
	`no` int(10) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
	`menu` varchar(20) NOT NULL DEFAULT '' COMMENT '메뉴명',
	`admin_no` int(10) NOT NULL DEFAULT '0' COMMENT '관련 관리자 번호',
	`title` varchar(50) NOT NULL DEFAULT '' COMMENT '단축검색명',
	`querystring` text NOT NULL DEFAULT '' COMMENT '현재검색쿼리',
	`content` varchar(100) NOT NULL DEFAULT '' COMMENT '요약설명',
	`reg_date` int(10) NOT NULL DEFAULT '0' COMMENT '등록일시',
	`sort` int(10) NOT NULL DEFAULT '0' COMMENT '정렬순서',
	PRIMARY KEY (`no`),
	INDEX `menu` (`menu`),
	INDEX `admin_no` (`admin_no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['promotion_link'] = "
CREATE TABLE IF NOT EXISTS `wm_promotion_link` (
  `no` int(10) NOT NULL AUTO_INCREMENT,
  `prm_no` int(10) NOT NULL COMMENT '프로모션 기획전 그룹 번호',
  `pgrp_no` int(10) NOT NULL COMMENT '프로모션 상품 그룹 번호',
  `sort` int(4) NOT NULL COMMENT '정렬순서',
  PRIMARY KEY (`no`),
  KEY `prm_no` (`prm_no`),
  KEY `pgrp_no` (`pgrp_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

$tbl_schema['promotion_list'] = "
CREATE TABLE IF NOT EXISTS `wm_promotion_list` (
  `no` int(10) NOT NULL AUTO_INCREMENT COMMENT '프로모션 번호',
  `promotion_nm` varchar(200) NOT NULL COMMENT '프로모션 명',
  `content` text NOT NULL COMMENT '본문',
  `m_content` text NOT NULL COMMENT '모바일 본문',
  `use_m_content` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Y:모바일 본문 사용함, N:모바일 본문 사용 안함',
  `period_type` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Y:기한 있음, N:무제한',
  `date_start` datetime NOT NULL COMMENT '진행기간 시작일시',
  `date_end` datetime NOT NULL COMMENT '진행기간 종료일시',
  `use_yn` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Y:사용함, N:사용안함',
  `sort` int(5) NOT NULL COMMENT '정렬순서',
  `admin_no` int(10) NOT NULL COMMENT '등록 관리자 번호',
  `admin_id` varchar(50) NOT NULL COMMENT '등록 관리자 아이디',
  `reg_date` datetime NOT NULL COMMENT '등록일시',
  PRIMARY KEY (`no`),
  KEY `promotion_nm` (`promotion_nm`),
  KEY `period` (`date_start`,`date_end`),
  KEY `period_type` (`period_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='프로모션 기획전';
";

$tbl_schema['promotion_pgrp_link'] = "
CREATE TABLE IF NOT EXISTS `wm_promotion_pgrp_link` (
  `no` int(11) NOT NULL AUTO_INCREMENT,
  `pgrp_no` int(11) NOT NULL COMMENT '프로모션 상품그룹 번호',
  `pno` int(11) NOT NULL COMMENT '연결 상품코드',
  `sort` int(11) NOT NULL COMMENT '정렬 순서',
  PRIMARY KEY (`no`),
  KEY `grp_no` (`pgrp_no`),
  KEY `pno` (`pno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='프로모션 상품그룹에 상품 연결';
";

$tbl_schema['promotion_pgrp_list'] = "
CREATE TABLE IF NOT EXISTS `wm_promotion_pgrp_list` (
  `no` int(10) NOT NULL AUTO_INCREMENT COMMENT '프로모션 상품그룹 번호',
  `pgrp_nm` varchar(200) NOT NULL COMMENT '프로모션 그룹명',
  `banner_text` text COMMENT '띄배너 명',
  `updir` varchar(25) DEFAULT NULL COMMENT '',
  `upfile1` varchar(80) DEFAULT NULL COMMENT '띄배너 이미지',
  `upfile2` varchar(80) DEFAULT NULL COMMENT '띄배너 이미지(모바일)',
  `admin_no` int(10) NOT NULL COMMENT '등록 관리자 번호',
  `admin_id` varchar(100) NOT NULL COMMENT '등록 관리자 아이디',
  `reg_date` datetime NOT NULL COMMENT '등록일시',
  PRIMARY KEY (`no`),
  KEY `group_nm` (`pgrp_nm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='프로모션 기획전 - 상품그룹 목록';
";

$tbl_schema['bank_customer'] = "
CREATE TABLE `wm_bank_customer` (
	`no` int(10) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
	`bank` varchar(20) NOT NULL DEFAULT '' COMMENT '기관명',
	`code` varchar(3) NOT NULL DEFAULT '0' COMMENT '은행코드',
	`reg_date` int(10) NOT NULL DEFAULT '0' COMMENT '등록일시',
	PRIMARY KEY (`no`),
	INDEX `bank` (`bank`),
	INDEX `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['privacy_view_log'] = "
CREATE TABLE IF NOT EXISTS `wm_privacy_view_log` (
  `no` int(10) NOT NULL AUTO_INCREMENT,
  `admin_no` int(10) NOT NULL,
  `admin_id` varchar(20) NOT NULL,
  `page_id` varchar(20) NOT NULL,
  `page_type` varchar(20) NOT NULL,
  `target_id` varchar(100) NOT NULL,
  `target_cnt` int(10) NOT NULL,
  `reg_date` int(10) NOT NULL,
  `ip` varchar(15) NOT NULL default '',
  PRIMARY KEY (`no`),
  KEY `admin_id` (`admin_id`),
  KEY `page_type` (`page_type`),
  KEY `reg_date` (`reg_date`),
  KEY `page_id` (`page_id`),
  KEY `admin_no` (`admin_no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['bitly_shortenter'] = "
CREATE TABLE IF NOT EXISTS `$tbl[bitly_shortenter]` (
  `no` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `shortUrl` varchar(50) NOT NULL,
  `longUrl` varchar(200) NOT NULL,
  `reg_date` int(10) NOT NULL,
  `admin_id` varchar(30) NOT NULL,
  PRIMARY KEY (`no`),
  UNIQUE KEY `shortUrl` (`shortUrl`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['robots_log'] = "
CREATE TABLE IF NOT EXISTS `$tbl[robots_log]` (
  `no` int(10) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `admin_id` varchar(30) NOT NULL,
  `reg_date` int(11) NOT NULL,
  PRIMARY KEY (`no`),
  KEY `admin_id` (`admin_id`),
  KEY `reg_date` (`reg_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

$tbl_schema['often_comment'] = "
CREATE TABLE IF NOT EXISTS `wm_often_comment` (
  `no` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `cate` varchar(10) NOT NULL,
  `reg_date` int(10) NOT NULL,
  PRIMARY KEY (`no`),
  index `cate` (`cate`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";

// 재입고 알림 관리
$tbl_schema['notify_restock'] = "
CREATE TABLE `wm_notify_restock` (
	`no` INT(11) NOT NULL AUTO_INCREMENT,
	`pno` INT(11) NULL DEFAULT NULL,
	`member_no` INT(11) NULL DEFAULT NULL,
	`complex_no` INT(11) NULL DEFAULT NULL,
	`buyer_cell` VARCHAR(30) NULL DEFAULT '',
	`option` VARCHAR(1000) NULL DEFAULT '',
	`reg_date` INT(11) NULL DEFAULT NULL,
	`update_date` INT(11) NULL DEFAULT NULL,
	`send_date` INT(11) NULL DEFAULT NULL,
	`stat` INT(11) NOT NULL DEFAULT '1',
	`del_stat` ENUM('Y','N') NOT NULL DEFAULT 'N',
	PRIMARY KEY (`no`),
	INDEX `pno` (`pno`),
	INDEX `member_no` (`member_no`),
	INDEX `complex_no` (`complex_no`),
	INDEX `stat` (`stat`),
	INDEX `buyer_cell` (`buyer_cell`)
)
COMMENT='재입고알림요청 stat = 1:신청완료, 2:알림완료, 3:신청취소, 4:알림기간만료'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
";

$tbl_schema['seo_config'] = "
CREATE TABLE `$tbl[seo_config]` (
	`no` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`tag_type` VARCHAR(20) NOT NULL COMMENT 'meta, opengraph 등',
	`page` VARCHAR(20) NOT NULL COMMENT '페이지 종류',
	`title` VARCHAR(200) NOT NULL COMMENT '타이틀',
	`description` TEXT NOT NULL COMMENT '대표설명',
	`keyword` TEXT NOT NULL COMMENT '키워드',
	`image_use` CHAR(1) NOT NULL COMMENT '이미지 사요여부 또는 사용할 이미지',
	`updir` VARCHAR(50) NOT NULL COMMENT '업로드 이미지 경로',
	`upfile1` VARCHAR(100) NOT NULL COMMENT '업로드 이미지 파일',
	`admin_id` VARCHAR(100) NOT NULL COMMENT '최종 수정자',
	`edt_date` DATETIME NOT NULL COMMENT '수정일시',
	`reg_date` DATETIME NOT NULL COMMENT '등록일시',
	PRIMARY KEY (`no`),
	UNIQUE INDEX `tag_type_page` (`tag_type`, `page`)
)
COMMENT='SEO 설정'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;
";

$tbl_schema['product_content_log'] = "
CREATE TABLE `wm_product_content_log` (
	`no` INT(10) NOT NULL AUTO_INCREMENT,
	`pno` INT(10) NOT NULL,
	`content2` LONGTEXT NOT NULL,
	`mode` ENUM('1','2') NOT NULL DEFAULT '1' COMMENT '1.일반 2.복구 시 이전 내역',
	`admin_id` VARCHAR(30) NOT NULL,
	`mobile` ENUM('P','M') NOT NULL DEFAULT 'P',
	`reg_date` INT(10) NOT NULL,
	`edt_date` INT(10) NOT NULL,
	PRIMARY KEY (`no`),
	INDEX `pno` (`pno`),
	INDEX `mobile` (`mobile`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
";
$tbl_schema['product_talkstore'] = "
CREATE TABLE `$tbl[product_talkstore]` (
	`no` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`pno` INT(10) UNSIGNED NOT NULL COMMENT '상품 번호',
	`productId` INT(12) UNSIGNED NOT NULL COMMENT '카카오톡 스토어 상품번호',
	`useYn` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT '연동 여부',
	`categoryId` VARCHAR(20) NOT NULL COMMENT '리프 카테고리 ID',
	`categoryName` VARCHAR(200) NOT NULL,
	`taxType` ENUM('TAX','DUTYFREE','SMALL') NOT NULL DEFAULT 'TAX' COMMENT '부가세 타입 코드',
	`productCondition` ENUM('NEW','OLD','STOCKED','REFURBISH','DISPLAY') NOT NULL DEFAULT 'NEW' COMMENT '상품 상태 코드',
	`displayStatus` ENUM('OPEN','HIDDEN') NOT NULL DEFAULT 'OPEN' COMMENT '전시상태코드',
	`talkstore_prc` INT(10) NOT NULL DEFAULT '0' COMMENT '톡스토어 판매가',
	`originAreaContent` VARCHAR(100) NOT NULL COMMENT '혼합/기타 표시내용',
	`originAreaCode` VARCHAR(10) NOT NULL COMMENT '원산지 상세 지역코드',
	`originAreaType` VARCHAR(20) NOT NULL COMMENT '원산지 종류',
	`announcementType` VARCHAR(50) NOT NULL COMMENT '사품정보고시 상품군 코드',
	`deliveryMethodType` ENUM('DELIVERY','DIRECT') NOT NULL DEFAULT 'DELIVERY' COMMENT '배송 방법',
	`shippingAddressId` INT(10) NOT NULL COMMENT '출고지 주소 코드',
	`returnAddressId` INT(10) NOT NULL COMMENT '반품교환지 주소 코드',
	`asPhoneNumber` VARCHAR(20) NOT NULL COMMENT 'AS 연락처',
	`asGuideWords` VARCHAR(200) NOT NULL COMMENT 'AS 안내',
	`representImage` VARCHAR(200) NOT NULL COMMENT '기본 이미지',
	`edt_date` DATETIME NOT NULL,
	PRIMARY KEY (`no`),
	UNIQUE INDEX `pno` (`pno`),
	INDEX `categoryId` (`categoryId`),
	INDEX `productId` (`productId`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
";

$tbl_schema['product_talkstore_announce'] = "
CREATE TABLE `$tbl[product_talkstore_announce]` (
	`idx` INT(11) NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(50) NOT NULL,
	`type` VARCHAR(30) NOT NULL,
	`datas` TEXT NOT NULL,
	`reg_date` DATETIME NOT NULL,
	PRIMARY KEY (`idx`)
)
COMMENT='상품정보고시 세트'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
";

$tbl_schema['talkstore_api_log'] = "
CREATE TABLE `$tbl[talkstore_api_log]` (
	`idx` INT(10) NOT NULL AUTO_INCREMENT,
	`api` VARCHAR(50) NOT NULL DEFAULT '',
	`param` TEXT NOT NULL,
	`ret` TEXT NOT NULL,
	`reg_date` DATETIME NOT NULL,
	PRIMARY KEY (`idx`)
)
ENGINE=InnoDB
;
";

$tbl_schema['member_level_log'] = "
CREATE TABLE `{$tbl['member_level_log']}` (
	`idx` INT(10) NOT NULL AUTO_INCREMENT,
	`member_no` int(10) NOT NULL,
	`member_id` VARCHAR(50) NOT NULL,
	`level` int(2) NOT NULL,
	`ori_level` int(2) NOT NULL,
	`ref` varchar(10) NOT NULL DEFAULT '',
	`admin_id` varchar(50) NOT NULL DEFAULT '',
	`reg_date` DATETIME NOT NULL,
	PRIMARY KEY (`idx`),
	INDEX(member_no),
	INDEX(member_id)
)
ENGINE=InnoDB
;
";

	$tbl_schema['trigger_memberLevelUpdate'] = "CREATE TRIGGER `memberLevel`
AFTER UPDATE ON `wm_member`
FOR EACH ROW
BEGIN

	IF(@member_chg_ref IS NULL) THEN
		SET @member_chg_ref='';
	END IF;
	if(@admin_id IS NULL) THEN
		SET @admin_id = '';
	END IF;

	IF(OLD.level != NEW.level) THEN
		insert into wm_member_level_log
		(member_no, member_id, level, ori_level, ref, admin_id, reg_date)
		values
		(OLD.no, OLD.member_id, NEW.level, OLD.level, @member_chg_ref, @admin_id, now());
	END IF;

END;";


$tbl_schema['product_price_log'] = "
CREATE TABLE `wm_product_price_log` (
	`no` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`is_rollback` ENUM('Y','N') NOT NULL DEFAULT 'N',
	`pno` INT(10) UNSIGNED NOT NULL,
	`ori_sell_prc` DOUBLE(10,2) UNSIGNED NOT NULL DEFAULT '0.00',
	`new_sell_prc` DOUBLE(10,2) UNSIGNED NOT NULL DEFAULT '0.00',
	`runid` INT(10) UNSIGNED NULL DEFAULT NULL,
	`admin_id` VARCHAR(50) NULL DEFAULT '',
	`reg_date` DATETIME NOT NULL,
	PRIMARY KEY (`no`),
	INDEX `pno` (`pno`),
	INDEX `grp_idx` (`runid`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
";

	$tbl_schema['trigger_productUpdateAfter'] = "CREATE TRIGGER `productUpdateAfter`
AFTER UPDATE ON `wm_product`
FOR EACH ROW
BEGIN

	IF(old.stat>1 and old.sell_prc != new.sell_prc) THEN
		IF(isnull(@is_price_rollback)) THEN
			SET @is_price_rollback = 'N';
		END IF;

		insert into wm_product_price_log
		(pno, is_rollback, ori_sell_prc, new_sell_prc, runid, admin_id, reg_date)
		values
		(old.no, @is_price_rollback, old.sell_prc, new.sell_prc, @runid, @admin_id, now());
	END IF;

END;";

$tbl_schema['review_recommend'] = "
CREATE TABLE `$tbl[review_recommend]` (
	`no` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`member_no` INT(10) NOT NULL COMMENT '추천 한 회원번호',
	`rno` INT(10) NOT NULL COMMENT '상품후기 번호',
	`value` ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Y:추천, N:비추천',
	`reg_date` DATETIME NOT NULL COMMENT '최종 응답날짜',
	PRIMARY KEY (`no`),
	INDEX `member_no_rno` (`rno`, `member_no`)
)
COMMENT='상품후기 추천 테이블'
ENGINE=InnoDB
;
";


// 스마트스토어
$tbl_schema['product_nstore']="CREATE TABLE `wm_product_nstore` (
`no` int(11) not null auto_increment,
`pno` int(11) not null,
`product_id` varchar(100) not null,
`n_statustype` varchar(10) not null COMMENT '상태',
`n_custom_made` enum('Y','N') NOT NULL default 'N' COMMENT '주문제작',
`n_name` varchar(150) not null,
`n_category_big` varchar(11) not null default '0' COMMENT '카테고리(대)',
`n_category_mid` varchar(11) not null default '0' COMMENT '카테고리(중)',
`n_category_small` varchar(11) not null default '0' COMMENT '카테고리(소)',
`n_category_depth` varchar(11) not null default '0' COMMENT '카테고리(세분류)',
`n_origin_big` varchar(11) not null default '0' COMMENT '원산지(대)',
`n_origin_mid` varchar(11) not null default '0' COMMENT '원산지(중)',
`n_origin_small` varchar(11) not null default '0' COMMENT '원산지(소)',
`n_importer` varchar(60)  not null COMMENT '수입사명칭',
`n_sell_price` int(11) not null,
`EstimatedDeliveryTime` varchar(10) not null COMMENT '부가세',
`n_taxtype` varchar(10) not null COMMENT '부가세',
`n_infant` enum('Y','N') NOT NULL default 'N' COMMENT '미성년자 구매가능',
`n_image` varchar(200) not null COMMENT '이미지',
`n_content` text NOT NULL COMMENT '상세정보',
`n_as_tel` varchar(100) not null COMMENT 'as 전화번호',
`n_as_comment` text not null COMMENT 'as 내용',
`n_qty` varchar(100) not null COMMENT '재고',
`n_summary_no` int(11) not null COMMENT '상품정보고시 key',
`n_summary` text not null COMMENT '상품정보고시',
`n_option_set` text not null COMMENT '옵션정보',
`n_option_item` text not null COMMENT '옵션항목리스트',
`n_delivery_check` enum('Y','N') NOT NULL default 'N' COMMENT '배송 가능 불가능 여부',
`n_delivery_type` int(11) not null COMMENT '배송방법 1:택배 소포 등기, 2:직접 배송 ( 화물 배달 )',
`n_paytype` int(11) not null COMMENT '1:착불 , 2:선결제, 3:착불 또는 선결제',
`n_delivery_limit` int(11) not null COMMENT '1:무료, 2:조건부 무료, 3:유료',
`n_delivery_limit_prc` int(11) not null COMMENT '조건부 배송일 경우 조건 금액 입력',
`n_delivery_prc` int(11) not null COMMENT '기본배송비',
`n_delivery_parcel` int(11) not null COMMENT '교환/반품 택배사',
`n_delivery_return_prc` int(11) not null COMMENT '반품 배송비',
`n_delivery_change_prc` int(11) not null COMMENT '교환 배송비',
`n_kc_flag` varchar(20) not null COMMENT 'KC 인증 flag',
`n_review_check` enum('Y','N') NOT NULL default 'N' COMMENT '리뷰 노출 여부',
`n_child_certified` enum('Y','N') NOT NULL default 'N' COMMENT '어린이상품 인증 대상 제외 여부',
`n_green_certified` enum('Y','N') NOT NULL default 'N' COMMENT '친환경 인증대상 제외 여부',
`n_isbn`varchar(100) not null COMMENT 'ISBN13 국제표준도서번호',
`n_publication` enum('Y','N') NOT NULL default 'N' COMMENT '독립출판물 여부',
`n_culture_tax` enum('Y','N') NOT NULL default 'N' COMMENT '문화비 소득공제 여부',
`timestamp`varchar(100) not null COMMENT '시간',
`insert_date` varchar(20) not null,
`insert_id` varchar(100) not null,
`insert_ip` varchar(20) not null,
`update_date` varchar(20) not null,
`update_id` varchar(100) not null,
`update_ip` varchar(20) not null,
PRIMARY KEY  (`no`),
KEY `pno` (`pno`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

$tbl_schema['store_summary'] = "CREATE TABLE IF NOT EXISTS `wm_store_summary` (
  `no` int(11) NOT NULL auto_increment,
  `title` varchar(150) NOT NULL,
  `datas` text NOT NULL,
  `category` int(11) NOT NULL,
  `reg_date` varchar(30) NOT NULL,
  `insert_id` varchar(100) NOT NULL,
  `insert_ip` varchar(30) NOT NULL,
  PRIMARY KEY  (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='스마트스토어 상품정보고시 리스트'"; //2019-07

$tbl_schema['log_schedule'] = "CREATE TABLE `{$tbl['log_schedule']}` (
	`no` INT(10) NOT NULL AUTO_INCREMENT,
	`query` TEXT NOT NULL,
	`reg_date` DATETIME NOT NULL,
	PRIMARY KEY (`no`)
)
COMMENT='로그 데이터 일괄 처리'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;";


$tbl_schema['product_delivery_set'] = "CREATE TABLE `wm_product_delivery_set` (
	`no` INT(10) NOT NULL AUTO_INCREMENT,
	`partner_no` INT(10) NOT NULL DEFAULT '0' COMMENT '파트너코드',
	`set_name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '세트명',
	`delivery_type` CHAR(1) NOT NULL DEFAULT '3' COMMENT '3.금액별배송 4.금액별 차등 배송 5.수량별 차등 배송 6.고정배송',
	`delivery_base` ENUM('1','2') NOT NULL DEFAULT '1' COMMENT '1.주문금액 기준 2.결제금액 기준',
	`delivery_loop_type` ENUM('N','Y') NOT NULL DEFAULT 'N' COMMENT '가격 반복 패턴 사용',
	`delivery_free_limit` TEXT NOT NULL COMMENT '배송비',
	`free_delivery_area` ENUM('Y','N','X') NOT NULL DEFAULT 'X' COMMENT '도서산간 배송비 Y.무료배송일때도 부과, N.무료배송일 경우 미부과, X.무조건 미부과',
	`free_yn` ENUM('N','Y') NOT NULL DEFAULT 'N' COMMENT '무료배송 이벤트, 회원무료배송시 개별배송비가 무료가 됩니다.',
	`edt_date` DATETIME NOT NULL,
	`reg_date` DATETIME NOT NULL,
	`admin_id` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '등록관리자',
	PRIMARY KEY (`no`),
	INDEX `partner_no` (`partner_no`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;";

$tbl_schema['product_timesale_set'] = "
CREATE TABLE `{$tbl['product_timesale_set']}` (
	`no` INT(5) NOT NULL AUTO_INCREMENT,
	`ts_use` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT '프리셋 사용 여부' COLLATE 'utf8_general_ci',
	`name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '프리셋 이름' COLLATE 'utf8_general_ci',
	`ts_dates` DATETIME NOT NULL COMMENT '타임세일 시작일시',
	`ts_datee` DATETIME NOT NULL COMMENT '타임세일 종료일시',
	`ts_event_type` ENUM('1','2') NOT NULL DEFAULT '1' COMMENT '할인/적립 여부' COLLATE 'utf8_general_ci',
	`ts_saleprc` DOUBLE(10,2) NOT NULL DEFAULT '0.00' COMMENT '할인 금액(%)',
	`ts_saletype` ENUM('price','percent') NOT NULL DEFAULT 'price' COMMENT '할인 단위' COLLATE 'utf8_general_ci',
	`ts_cut` INT(5) NOT NULL DEFAULT '1' COMMENT '절사 단위',
	`ts_state` INT(2) NOT NULL DEFAULT '0' COMMENT '시간종료 후 상태',
	`reg_date` INT(10) NOT NULL DEFAULT '0' COMMENT '프리셋 등록 일시',
	PRIMARY KEY (`no`) USING BTREE,
	INDEX `dates_datee` (`ts_dates`, `ts_datee`) USING BTREE,
	INDEX `use_yn` (`ts_use`) USING BTREE
)
COMMENT='타임세일 프리셋'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
";

$tbl_schema['partner_config'] = "CREATE TABLE IF NOT EXISTS `wm_partner_config` (
		name varchar(50) not null,
		value varchar(300),
		reg_date int (10),
		edt_date int (10),
		partner_no int (10),
		admin_id varchar(20)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";

$tbl_schema['member_auto_login'] = "
CREATE TABLE `{$tbl['member_auto_login']}` (
	`cookie_id` VARCHAR(64) NOT NULL,
	`member_no` INT(11) NOT NULL,
	`ip` INT(11) NOT NULL,
	`reg_date` DATETIME NOT NULL,
	`last_access` DATETIME NOT NULL,
	PRIMARY KEY (`cookie_id`),
	INDEX `member_no` (`member_no`)
)
COMMENT='자동 로그인 토큰 테이블'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
";

$tbl_schema['erp_api'] = "
CREATE TABLE `{$tbl['erp_api']}` (
	`idx` SMALLINT(5) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '제공 업체 명' COLLATE 'utf8_general_ci',
	`apikey` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'API 키' COLLATE 'utf8_general_ci',
	`is_active` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT '사용 여부' COLLATE 'utf8_general_ci',
	`reg_date` DATETIME NOT NULL COMMENT '작성일시',
	PRIMARY KEY (`idx`) USING BTREE,
	UNIQUE INDEX `apikey` (`apikey`) USING BTREE
)
COMMENT='ERP 연동 API Key'
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
";

$tbl_schema['kakaopaybuy_info'] = "
CREATE TABLE `{$tbl['kakaopaybuy_info']}` (
	`idx` INT NOT NULL AUTO_INCREMENT,
	`pno` INT NOT NULL DEFAULT '0' COMMENT '상품 번호',
	`annoucement_idx` INT NOT NULL DEFAULT 0 COMMENT '정보 고시',
	PRIMARY KEY (`idx`) USING BTREE
)
COLLATE='utf8_general_ci'
;
";

$tbl_schema['npay_api_log'] = "
CREATE TABLE `wm_npay_api_log` (
	`idx` INT(11) NOT NULL AUTO_INCREMENT,
	`operation` VARCHAR(30) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`args` TEXT NOT NULL COLLATE 'utf8_general_ci',
	`args1` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`post_vars` TEXT NOT NULL COLLATE 'utf8_general_ci',
	`get_vars` TEXT NOT NULL COLLATE 'utf8_general_ci',
	`result` TEXT NOT NULL COLLATE 'utf8_general_ci',
	`errors` TEXT NOT NULL COLLATE 'utf8_general_ci',
	`admin_id` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`start_date` DATETIME NOT NULL,
	`end_date` DATETIME NOT NULL,
	`retry_date` DATETIME NOT NULL,
	PRIMARY KEY (`idx`) USING BTREE,
	INDEX `operation` (`operation`) USING BTREE,
	INDEX `args1` (`args1`) USING BTREE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;
";

$tbl_schema['subscription_key'] = "
CREATE TABLE `{$tbl['subscription_key']}` (
	`idx` INT NOT NULL AUTO_INCREMENT,
	`pg` VARCHAR(20) NULL DEFAULT NULL,
	`ono` VARCHAR(20) NULL DEFAULT NULL,
	`reserveId` VARCHAR(50) NULL DEFAULT NULL,
	`tempReceiptId` VARCHAR(50) NULL DEFAULT NULL,
	`recurrentId` VARCHAR(50) NULL DEFAULT NULL,
	`stat` ENUM('1','2') NULL DEFAULT '1' COMMENT '1. 정상, 2. 취소' COLLATE 'utf8_general_ci',
	`reg_date` DATETIME NULL,
	PRIMARY KEY (`idx`),
	UNIQUE INDEX `pg_ono` (`pg`, `ono`),
	INDEX `recurrentId` (`recurrentId`)
)
COLLATE='utf8_general_ci';
";

$tbl_schema['product_book'] = "
CREATE TABLE `{$tbl['product_book']}` (
	`no` INT NOT NULL default '0',
    `is_used` ENUM('N','Y') NOT NULL default 'N' COMMENT '중고여부',
    `isbn` varchar(13) NOT NULL default '' COMMENT 'isbn',
    `title` varchar(200) NOT NULL default '' COMMENT '도서명',
    `number` varchar(20) NOT NULL default '' COMMENT '권호 정보',
    `version` varchar(20) NOT NULL default '' COMMENT '버전정보',
    `subtitle` varchar(200) NOT NULL default '' COMMENT '부재',
    `original_title` varchar(200) NOT NULL default '' COMMENT '원서 제목',
    `author` varchar(100) NOT NULL default '' COMMENT '작가명',
    `publisher` varchar(100) NOT NULL default '' COMMENT '출판사명',
    `publish_day` date NOT NULL COMMENT '출간일',
    `size` varchar(50) NOT NULL default '' COMMENT '크기/판형',
    `pages` varchar(20) NOT NULL default '' COMMENT '쪽수',
    `description` text NOT NULL default '' COMMENT '목차 또는 책 소개',
	PRIMARY KEY (`no`)
)
COLLATE='utf8_general_ci';
";

$tbl_schema['mng_auth_log'] = "
CREATE TABLE `{$tbl['mng_auth_log']}` (
	`idx` INT(10) NOT NULL AUTO_INCREMENT,
    `target_no` int(10) not null,
    `target_id` varchar(50) not null,
	`admin_no` INT(10) NOT NULL,
	`admin_id` VARCHAR(100) NOT NULL DEFAULT '',
    `category` varchar(20) NOT NULL DEFAULT '' COMMENT '대메뉴',
	`auth1` TEXT NOT NULL COMMENT '빠진 메뉴 권한',
	`auth2` TEXT NOT NULL COMMENT '추가된 메뉴 권한',
	`auth_d1` TEXT NOT NULL COMMENT '빠진 세부 권한',
	`auth_d2` TEXT NOT NULL COMMENT '추가된 세부 권한',
    `remote_addr` varchar(15) not null,
	`reg_date` DATETIME NOT NULL,
	PRIMARY KEY (`idx`),
	KEY `admin_no_admin_id` (`admin_no`, `admin_id`),
    KEY `category` (`category`),
    KEY `reg_date` (`reg_date`)
)
COLLATE='utf8_general_ci'
;
";

$tbl_schema['work_log'] = "
CREATE TABLE `wm_work_log` (
	`no` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`page` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '수정 된 기능' COLLATE 'utf8_general_ci',
	`pkey` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '수정한 데이터의 pkey',
	`title` VARCHAR(200) NOT NULL DEFAULT '0' COLLATE 'utf8_general_ci',
	`snapshot` MEDIUMTEXT NULL DEFAULT NULL COMMENT '복구용 스냅샷' COLLATE 'utf8_general_ci',
	`difference` MEDIUMTEXT NULL DEFAULT NULL COMMENT '변경 된 필드 내용' COLLATE 'utf8_general_ci',
	`reg_date` DATETIME NOT NULL COMMENT '처리 일시',
	`post_args` MEDIUMTEXT NOT NULL COLLATE 'utf8_general_ci',
	`get_args` TEXT NOT NULL COLLATE 'utf8_general_ci',
	`timestamp` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '동시 처리 체크용 타임스탬프',
	`admin_id` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '처리 관리자 아이디' COLLATE 'utf8_general_ci',
	`admin_no` INT(10) UNSIGNED NOT NULL COMMENT '처리 관리자 번호',
	`remote_addr` VARCHAR(15) NOT NULL DEFAULT '' COMMENT '처리 아이피' COLLATE 'utf8_general_ci',
	PRIMARY KEY (`no`) USING BTREE,
	INDEX `admin_id_admin_no` (`admin_id`, `admin_no`) USING BTREE,
	INDEX `page_pkey` (`page`, `pkey`) USING BTREE,
	INDEX `timestamp` (`timestamp`) USING BTREE,
	INDEX `reg_date` (`reg_date`) USING BTREE
)
COMMENT='작업 및 복구 로그'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=130
;
";

$tbl_schema['delivery_range'] = "
CREATE TABLE {$tbl['delivery_range']} (
	`no` INT(10) NOT NULL AUTO_INCREMENT,
	`type` ENUM('D','A') NOT NULL COMMENT 'D: 배송 불가 지역, A: 배송 가능 지역' COLLATE 'utf8mb4_general_ci',
	`name` VARCHAR(50) NOT NULL COMMENT '배송지 별칭' COLLATE 'utf8mb4_general_ci',
	`sido` VARCHAR(20) NOT NULL COMMENT '시도' COLLATE 'utf8mb4_general_ci',
	`gugun` VARCHAR(20) NOT NULL COMMENT '구군' COLLATE 'utf8mb4_general_ci',
	`dong` TEXT NOT NULL COMMENT '동' COLLATE 'utf8mb4_general_ci',
	`ri` TEXT NOT NULL COMMENT '리' COLLATE 'utf8mb4_general_ci',
	`reason` TEXT NOT NULL COMMENT '배송 불가 사유' COLLATE 'utf8mb4_general_ci',
	`reg_date` DATETIME NOT NULL COMMENT '등록일시',
	`partner_no` INT(10) NOT NULL COMMENT '입점사 코드',
	PRIMARY KEY (`no`) USING BTREE,
	INDEX `partner_no` (`partner_no`) USING BTREE,
	INDEX `type` (`type`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;";

$tbl_schema['privacy_policy'] = "
CREATE TABLE `wm_privacy_policy` (
  `no` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contents` text DEFAULT NULL COMMENT '처리방침내용',
  `admin` varchar(50) DEFAULT NULL COMMENT '등록/최종수정 관리자',
  `effective_date` int(11) unsigned DEFAULT NULL COMMENT '시행일',
  `reg_date` int(11) unsigned DEFAULT NULL COMMENT '등록일시',
  `hidden` enum('Y','N') DEFAULT 'Y' COMMENT '숨김여부',
  `deleted` enum('Y','N') DEFAULT 'N' COMMENT '삭제여부',
  `default_yn` enum('Y','N') DEFAULT 'N' COMMENT '기본게시물여부',
  PRIMARY KEY (`no`)
)
COMMENT='개인정보처리방침'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;";

$tbl_schema['member_address'] = "
CREATE TABLE `{$tbl['member_address']}` (
	`idx` INT(11) NOT NULL AUTO_INCREMENT,
	`member_no` INT(11) NOT NULL,
	`member_id` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_general_ci',
	`title` VARCHAR(100) NOT NULL COMMENT '주소록 명' COLLATE 'utf8mb4_general_ci',
	`name` VARCHAR(100) NOT NULL COMMENT '수령자명' COLLATE 'utf8mb4_general_ci',
	`phone` VARCHAR(30) NOT NULL COMMENT '수령자 전화번호' COLLATE 'utf8mb4_general_ci',
	`cell` VARCHAR(30) NOT NULL COMMENT '수령자 모바일 번호' COLLATE 'utf8mb4_general_ci',
	`zip` VARCHAR(7) NOT NULL COMMENT '우편번호' COLLATE 'utf8mb4_general_ci',
	`addr1` VARCHAR(150) NOT NULL DEFAULT '' COMMENT '주소' COLLATE 'utf8mb4_general_ci',
	`addr2` VARCHAR(250) NOT NULL DEFAULT '' COMMENT '상세 주소' COLLATE 'utf8mb4_general_ci',
	`addr3` VARCHAR(150) NOT NULL DEFAULT '' COMMENT '해외 주소 추가필드 1' COLLATE 'utf8mb4_general_ci',
	`addr4` VARCHAR(150) NOT NULL DEFAULT '' COMMENT '해외 주소 추가필드 2' COLLATE 'utf8mb4_general_ci',
	`source` ENUM('order','member','input') NOT NULL COMMENT '등록 방법' COLLATE 'utf8mb4_general_ci',
	`is_default` ENUM('N','Y') NOT NULL DEFAULT 'N' COMMENT '기본 주소' COLLATE 'utf8mb4_general_ci',
	`sort` INT(4) NOT NULL DEFAULT '0' COMMENT '정렬순서',
	`reg_date` DATETIME NOT NULL DEFAULT current_timestamp() COMMENT '등록일',
	PRIMARY KEY (`idx`) USING BTREE,
	INDEX `sort` (`sort`) USING BTREE,
	INDEX `member_no_member_id` (`member_no`, `member_id`) USING BTREE
)
COMMENT='회원 주문 주소록'
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;

";

$tbl_schema['member_cert'] = "
CREATE TABLE `wm_member_cert` (
	`no` INT(10) UNSIGNED NOT NULL,
	`member_id` VARCHAR(100) NOT NULL,
	`birth` DATE NOT NULL COMMENT '생년월일',
	`gender` ENUM('M','F') NOT NULL DEFAULT 'M' COMMENT '성별',
	`is_foreign` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT '내외국인 구분',
	`DI` VARCHAR(64) NOT NULL,
	`CI` VARCHAR(88) NOT NULL,
	`reg_date` DATETIME NOT NULL,
	PRIMARY KEY (`no`),
	UNIQUE INDEX `no_member_id` (`no`, `member_id`)
)
COLLATE='utf8mb4_general_ci'
";

//[매장지도] 스키마
$tbl_schema['store_location'] = "
CREATE TABLE `{$tbl['store_location']}` (
	`no` INT(13) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
	`partner_no` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '입점사 번호',
	`stat` TINYINT(4) UNSIGNED NOT NULL DEFAULT '1' COMMENT '매장 상태',
	`hidden` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT '노출 여부' COLLATE 'utf8mb3_general_ci',
	`title` VARCHAR(100) NOT NULL COLLATE 'utf8mb3_general_ci',
	`location` VARCHAR(10) NOT NULL COMMENT '지역' COLLATE 'utf8mb3_general_ci',
	`owner` VARCHAR(10) NOT NULL COMMENT '대표자명' COLLATE 'utf8mb3_general_ci',
	`cell` VARCHAR(20) NOT NULL COMMENT '휴대전화' COLLATE 'utf8mb3_general_ci',
	`phone` VARCHAR(20) NOT NULL COMMENT '전화번호' COLLATE 'utf8mb3_general_ci',
	`email` VARCHAR(150) NOT NULL COMMENT '이메일' COLLATE 'utf8mb3_general_ci',
	`facility` VARCHAR(50) NOT NULL COMMENT '시설안내' COLLATE 'utf8mb3_general_ci',
	`sido` VARCHAR(10) NOT NULL COMMENT '지역' COLLATE 'utf8mb3_general_ci',
	`zipcode` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8mb3_general_ci',
	`addr1` VARCHAR(150) NOT NULL COLLATE 'utf8mb3_general_ci',
	`addr2` VARCHAR(50) NOT NULL COLLATE 'utf8mb3_general_ci',
	`lat` VARCHAR(50) NOT NULL COMMENT '위도' COLLATE 'utf8mb3_general_ci',
	`lng` VARCHAR(50) NOT NULL COMMENT '경도' COLLATE 'utf8mb3_general_ci',
	`icons` VARCHAR(100) NULL DEFAULT '' COMMENT '매장 아이콘' COLLATE 'utf8mb3_general_ci',
	`content` TEXT NULL DEFAULT NULL COMMENT '기타 내용' COLLATE 'utf8mb3_general_ci',
	`reg_date` INT(11) NOT NULL DEFAULT '0' COMMENT '실행일',
	`admin_id` VARCHAR(150) NULL DEFAULT '' COLLATE 'utf8mb3_general_ci',
	`edit_date` INT(11) NOT NULL DEFAULT '0' COMMENT '수정일',
	`edit_id` VARCHAR(150) NULL DEFAULT '' COLLATE 'utf8mb3_general_ci',
	`ip` VARCHAR(15) NOT NULL DEFAULT '' COMMENT '아이피' COLLATE 'utf8mb3_general_ci',
	`updir` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8mb3_general_ci',
	`upfile1` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb3_general_ci',
	`upfile2` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb3_general_ci',
	`upfile3` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb3_general_ci',
	`upfile4` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb3_general_ci',
	PRIMARY KEY (`no`) USING BTREE,
	INDEX `no` (`no`) USING BTREE,
	INDEX `sido` (`sido`) USING BTREE,
	INDEX `title` (`title`) USING BTREE,
	INDEX `lat` (`lat`) USING BTREE,
	INDEX `facility` (`facility`) USING BTREE,
	INDEX `addr2` (`addr2`) USING BTREE,
	INDEX `addr1` (`addr1`) USING BTREE,
	INDEX `icons` (`icons`) USING BTREE
)
COMMENT='매장 지도 API'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

";

//[매장지도] 운영시간 스키마
$tbl_schema['store_operate'] = "
CREATE TABLE `{$tbl['store_operate']}` (
	`no` INT(11) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
	`sno` INT(11) NOT NULL DEFAULT '0' COMMENT 'operate_time_key',
	`partner_no` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '입점사 번호',
	`otype` CHAR(2) NULL DEFAULT '' COMMENT '타입' COLLATE 'utf8mb3_general_ci',
	`edit_date` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '수정일시 (Timestamp)' COLLATE 'utf8mb3_general_ci',
	`edit_id` VARCHAR(150) NULL DEFAULT '' COLLATE 'utf8mb3_general_ci',
	`reg_date` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '등록일시 (Timestamp)' COLLATE 'utf8mb3_general_ci',
	`admin_id` VARCHAR(150) NULL DEFAULT '' COLLATE 'utf8mb3_general_ci',
	`ip` VARCHAR(20) NULL DEFAULT '0' COLLATE 'utf8mb3_general_ci',
	PRIMARY KEY (`no`) USING BTREE,
	INDEX `partner_no` (`partner_no`) USING BTREE,
	INDEX `sno` (`sno`) USING BTREE,
	INDEX `no` (`no`) USING BTREE,
	INDEX `otype` (`otype`) USING BTREE
)
COMMENT='영업시간 설정'
COLLATE='utf8mb3_general_ci'
ENGINE=InnoDB
;
";

//[매장지도] 영업 시간 리스트
$tbl_schema['store_operate_time'] = "
CREATE TABLE `{$tbl['store_operate_time']}` (
	`no` INT(11) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
	`sono` INT(11) NOT NULL DEFAULT '0' COMMENT '영업 시간 설정 번호',
	`all_time` ENUM('Y','N') NOT NULL DEFAULT 'N' COLLATE 'utf8mb3_general_ci',
	`week` VARCHAR(20) NOT NULL DEFAULT '' COLLATE 'utf8mb3_general_ci',
	`buse` ENUM('Y','N') NOT NULL DEFAULT 'N' COLLATE 'utf8mb3_general_ci',
	`otype` CHAR(2) NOT NULL DEFAULT '' COMMENT '타입' COLLATE 'utf8mb3_general_ci',
	`shour` VARCHAR(20) NULL DEFAULT '' COMMENT '시작 시간' COLLATE 'utf8mb3_general_ci',
	`ehour` VARCHAR(20) NULL DEFAULT '' COMMENT '마감 시간' COLLATE 'utf8mb3_general_ci',
	`edit_date` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '수정일시 (Timestamp)' COLLATE 'utf8mb3_general_ci',
	`edit_id` VARCHAR(150) NULL DEFAULT '' COLLATE 'utf8mb3_general_ci',
	`reg_date` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '등록일시 (Timestamp)' COLLATE 'utf8mb3_general_ci',
	`admin_id` VARCHAR(150) NULL DEFAULT '' COLLATE 'utf8mb3_general_ci',
	`ip` VARCHAR(20) NULL DEFAULT '0' COLLATE 'utf8mb3_general_ci',
	PRIMARY KEY (`no`) USING BTREE,
	INDEX `no` (`no`) USING BTREE,
	INDEX `sno` (`sono`) USING BTREE,
	INDEX `otype` (`otype`) USING BTREE,
	INDEX `buse` (`buse`) USING BTREE,
	INDEX `week` (`week`) USING BTREE
)
COMMENT='영업시간 설정'
COLLATE='utf8mb3_general_ci'
ENGINE=InnoDB
;
";

//[매장지도] 운영시간 브레이크 타임
$tbl_schema['store_operate_break'] = "
CREATE TABLE `{$tbl['store_operate_break']}` (
	`no` INT(11) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
	`sono` INT(11) NOT NULL DEFAULT '0' COMMENT '오프라인 번호',
	`stno` INT(11) NOT NULL DEFAULT '0' COMMENT '영업 시간 번호',
	`shour` VARCHAR(20) NULL DEFAULT '' COMMENT '시작 시간' COLLATE 'utf8mb3_general_ci',
	`ehour` VARCHAR(20) NULL DEFAULT '' COMMENT '마감 시간' COLLATE 'utf8mb3_general_ci',
	`edit_date` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '수정일시 (Timestamp)' COLLATE 'utf8mb3_general_ci',
	`edit_id` VARCHAR(150) NULL DEFAULT '' COLLATE 'utf8mb3_general_ci',
	`reg_date` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '등록일시 (Timestamp)' COLLATE 'utf8mb3_general_ci',
	`admin_id` VARCHAR(150) NULL DEFAULT '' COLLATE 'utf8mb3_general_ci',
	`ip` VARCHAR(20) NULL DEFAULT '0' COLLATE 'utf8mb3_general_ci',
	PRIMARY KEY (`no`) USING BTREE,
	INDEX `no` (`no`) USING BTREE,
	INDEX `shour` (`shour`) USING BTREE,
	INDEX `sono` (`sono`) USING BTREE,
	INDEX `stno` (`stno`) USING BTREE
)
COMMENT='영업시간 휴식 시간  설정'
COLLATE='utf8mb3_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=37
;
";

//[매장지도] 시설안내 스키마
$tbl_schema['store_facility_set'] = "
CREATE TABLE `{$tbl['store_facility_set']}` (
	`no` INT(5) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
	`name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '항목명' COLLATE 'utf8mb3_general_ci',
	`content` VARCHAR(255) NULL DEFAULT NULL COMMENT '설명' COLLATE 'utf8mb3_general_ci',
	`updir` VARCHAR(255) NULL DEFAULT NULL COMMENT '이미지 경로' COLLATE 'utf8mb3_general_ci',
	`upfile1` VARCHAR(40) NULL DEFAULT NULL COMMENT '이미지' COLLATE 'utf8mb3_general_ci',
	`sort` INT(5) NOT NULL DEFAULT '0' COMMENT '정렬',
	`reg_date` VARCHAR(12) NOT NULL DEFAULT '' COMMENT '등록시간' COLLATE 'utf8mb3_general_ci',
	`edt_date` VARCHAR(12) NOT NULL DEFAULT '' COMMENT '수정시간' COLLATE 'utf8mb3_general_ci',
	`ip` VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'ip' COLLATE 'utf8mb3_general_ci',
	PRIMARY KEY (`no`) USING BTREE,
	INDEX `no` (`no`) USING BTREE,
	INDEX `sort` (`sort`) USING BTREE
)
COMMENT='시설안내 설정'
COLLATE='utf8mb3_general_ci'
ENGINE=InnoDB
;
";

//[매장지도] 관심 매장 스키마
$tbl_schema['store_wish'] = "
CREATE TABLE  `{$tbl['store_wish']}` (
	`no` INT(11) NOT NULL AUTO_INCREMENT COMMENT '고유번호',
	`sno` INT(11) NOT NULL DEFAULT '0' COMMENT '오프라인 매장 번호',
	`member_no` INT(11) NULL DEFAULT NULL COMMENT '회원 고유번호',
	`reg_date` INT(11) NOT NULL DEFAULT '0' COMMENT '등록일시',
	`ip` VARCHAR(20) NULL DEFAULT NULL COMMENT 'IP 정보' COLLATE 'utf8mb3_general_ci',
	PRIMARY KEY (`no`) USING BTREE,
	INDEX `no` (`no`) USING BTREE,
	INDEX `member_no` (`member_no`) USING BTREE,
	INDEX `sno` (`sno`) USING BTREE
)
COMMENT='오프라인 관심 매장'
COLLATE='utf8mb3_general_ci'
ENGINE=InnoDB
;
";
?>