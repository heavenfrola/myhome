<?

	// 2009-01-08 : 테이블 구조 통일 - Han

	include_once $engine_dir."/_config/tbl_schema.php";

	// 테이블추가
	if(!isTable($tbl[product_saleset])) $pdo->query($tbl_schema[product_saleset]);
	if(!isTable($tbl[coordi_category])) $pdo->query($tbl_schema[coordi_category]);
	if(!isTable($tbl[coordi_set])) $pdo->query($tbl_schema[coordi_set]);
	if(!isTable($tbl[coordi_image])) $pdo->query($tbl_schema[coordi_image]);
	if(!isTable($tbl[delivery_area])) $pdo->query($tbl_schema[delivery_area]); // 2007-04-05 : 지역별 배송료 설정검사 - Han
	if(!isTable($tbl[order_no])) $pdo->query($tbl_schema[order_no]);
	if(!isTable('mari_board_day')) $pdo->query($tbl_schema[mari_board_day]);
	if(!isTable($tbl[cash_receipt])) $pdo->query($tbl_schema[cash_receipt]);
	if(!isTable($tbl[intra_day_check])) $pdo->query($tbl_schema[intra_day_check]);
	if(!isTable($tbl[point])) $pdo->query($tbl_schema[point]);
	if(!isTable($tbl[order_product_log])) $pdo->query($tbl_schema[order_product_log]);
	if(!isTable($tbl[poll_config])) $pdo->query($tbl_schema[poll_config]);
	if(!isTable($tbl[poll_item])) $pdo->query($tbl_schema[poll_item]);
	if(!isTable($tbl[poll_comment])) $pdo->query($tbl_schema[poll_comment]);
	if(!isTable($tbl[attend])) $pdo->query($tbl_schema[attend]);
	if(!isTable($tbl[attend_member])) $pdo->query($tbl_schema[attend_member]);
	if(!isTable($tbl[emoney])) $pdo->query($tbl_schema[emoney]);
	if(!isTable($tbl[mng_auth])) $pdo->query($tbl_schema[mng_auth]);
	if(!isTable($tbl[neko])) $pdo->query($tbl_schema[neko]);
	if(!isTable($tbl[attend_day])) $pdo->query($tbl_schema[new_attend]);
	if(!isTable($tbl[product_annex])) $pdo->query($tbl_schema[product_annex]);
	if(!isTable($tbl[mng_cs_log])) $pdo->query($tbl_schema[mng_cs_log]);
	if(!isTable($tbl[product_stat_log])) $pdo->query($tbl_schema[product_stat_log]);
	if(!isTable($tbl[member_xls_log])) $pdo->query($tbl_schema[member_xls_log]); // 2008-07-18 : 회원 엑셀 출력 로그 - Han
	if(!isTable($tbl[delete_log])) $pdo->query($tbl_schema[delete_log]);
	if(!isTable($tbl[mng_log])) $pdo->query($tbl_schema[mng_log]);
	if(!isTable($tbl[namecheck_log])) $pdo->query($tbl_schema[namecheck_log]);
	if(!isTable($tbl[product_log])) $pdo->query($tbl_schema[product_log]);
	if(!isTable($tbl[card_cc_log])) $pdo->query($tbl_schema[card_cc_log]);
	if(!isTable($tbl[order_stat_log])) $pdo->query($tbl_schema[order_stat_log]);
	if(!isTable($tbl[pwd_log])) $pdo->query($tbl_schema[pwd_log]);
	if(!isTable($tbl[coupon_log])) $pdo->query($tbl_schema[coupon_log]);
	if(!isTable($tbl[log_search_engine])) $pdo->query($tbl_schema[log_search_engine]);
	if(!isTable($tbl[provider])) $pdo->query($tbl_schema[provider]); // 2009-05-12 사입처 관리 by zardsama
	if(!isTable($tbl[pbanner])) $pdo->query($tbl_schema[pbanner]);
	if(!isTable($tbl[pbanner_group])) $pdo->query($tbl_schema[pbanner_group]);
	if(!isTable($tbl[ipin_log])) $pdo->query($tbl_schema[ipin_log]); // 2010-03-09 : 아이핀 로그 - Han
	if(!isTable($tbl[blacklist_log])) $pdo->query($tbl_schema[blacklist_log]); //2011-04-04 : 블랙리스트 로그 - Jung

	// 필드추가
	addField("mari_board", "rep_no", "TEXT not null"); // 2007-01-25 : 계층형답글의 부모값들 필드 - Han
	addField("mari_config","top_use","enum('Y','N') DEFAULT 'N'");
	addField("mari_config","top_content","TEXT NOT NULL");
	addField("mari_config","point_write","INT(5) NOT NULL");
	addField("mari_config","point_comment","INT(5) NOT NULL");
	addField("mari_config","day_write","INT(5) NOT NULL");
	addField("mari_config","day_comment","INT(5) NOT NULL");
	addField("mari_config","use_edit","ENUM( 'Y', 'N' ) DEFAULT 'Y' NOT NULL");
	addField("mari_config","use_del","ENUM( 'Y', 'N' ) DEFAULT 'Y' NOT NULL");
	addField("mari_config","use_comment_edit","ENUM( 'Y', 'N' ) DEFAULT 'Y' NOT NULL");
	addField("mari_config","use_comment_del","ENUM( 'Y', 'N' ) DEFAULT 'Y' NOT NULL");
	addField('mari_config', 'use_scallback', 'enum("N","Y") not null default "N"');
	addField('mari_config', 'use_mcallback', 'enum("N","Y") not null default "N"');

	addField($tbl[card],"env_info","VARCHAR(50) NOT NULL");
	addField($tbl[card], "cpn_no", "int(10) not null");
	addField($tbl[card], "cpn_auth_code", "varchar(20) not null");
	addField($tbl[vbank],"env_info","VARCHAR(50) NOT NULL");
	addField($tbl[vbank], "cpn_no", "int(10) not null");
	addField($tbl[vbank], "cpn_auth_code", "varchar(20) not null");
	addField($tbl[vbank], "pg", "varchar(10) not null");

	addField($tbl[cart], "anx_no", "int(11) not null"); // 2007-03-06 : 부속상품필드체크

	addField($tbl[coupon],"auto_cpn","VARCHAR(1) NOT NULL");
	addField($tbl[coupon],"down_hit","INT(5) NOT NULL"); // 발급횟수 증가 2006-06-28
	addField($tbl[coupon], "down_type", "enum('A', 'B', 'C') default 'A'");
	addField($tbl[coupon], "down_grade", "int(3) not null");
	addField($tbl[coupon], "down_gradeonly", "enum('Y', 'N') default 'Y'");
	addField($tbl[coupon],"download_limit","VARCHAR(1) NOT NULL"); // 제한 2006-06-27
	addField($tbl[coupon],"download_limit_ea","INT(5) NOT NULL");
	addField($tbl[coupon],"release_limit","VARCHAR(1) NOT NULL");
	addField($tbl[coupon],"release_limit_ea","INT(5) NOT NULL");
	addField($tbl[coupon], "auth_code", "text not null");
	addField($tbl[coupon], "is_type", "enum('A', 'B') not null default 'A'"); // 2007-02-07 : 온/오프라인 구분 추가 - Han
	addField($tbl[coupon_download],"stype","char(1) NOT NULL");
	addField($tbl[coupon_download],"auto_cpn","VARCHAR(1) NOT NULL"); // 2006-06-28 자동쿠폰
	addField($tbl[coupon_download], "is_type", "enum('A', 'B') not null default 'A'");
	addField($tbl[coupon_download], "auth_code", "varchar(20) not null");

	addField($tbl[milage],"admin_id","VARCHAR(20) NOT NULL"); // 적립금 조작 관리자 기록 2006-11-07

	addField($tbl[product],"coupon","VARCHAR(255) NOT NULL");
	addField($tbl[product], "free_delivery", "enum('Y','N') default 'N'"); // 2007-01-29 : 무료배송필드 추가 - Han
	addField($tbl[product], "xmid", " int(11) not null default '0'  after `xbig`"); // 2007-11-08 by zardsama 2분류 3단 카테고리
	addField($tbl[product], "xsmall", " int(11) not null default '0'  after `xmid`");
	addField($tbl[product], "ymid", " int(11) not null default '0'  after `ybig`"); // 2007-11-08 by zardsama 3분류 3단 카테고리
	addField($tbl[product], "ysmall", " int(11) not null default '0'  after `ymid");
	addField($tbl[product],"seller", "varchar(100) default NULL after `name`"); // 2008-08-22 장끼명 처리  by zardsama
	addField($tbl[product],"origin_name", "varchar(255) default NULL after `seller`");
	addField($tbl[product],"gift_use","enum('N','Y') NOT NULL default 'N'");
	addField($tbl[product],"wm_sc","int(11) NOT NULL default '0'");
	addField($tbl[product],"mng_memo","text NOT NULL");
	addField($tbl[product], "sortbig", "int(11) not null");
	addField($tbl[product], "sortmid", "int(11) not null");
	addField($tbl[product], "sortsmall", "int(11) not null");
	$pdo->query("alter table `$tbl[product]` add  KEY `sortbig` (`sortbig`)");
	$pdo->query("alter table `$tbl[product]` add  KEY `sortmid` (`sortmid`)");
	$pdo->query("alter table `$tbl[product]` add  KEY `sortsmall` (`sortsmall`)");
	addField($tbl[product_option_set],"ea_ck","enum('Y','N') default 'N'"); // 2006-12-01 : 옵션재고체크여부 필드와 수량이 들어갈 필드 - Han
	addField($tbl[product_option_set],"items_ea", "text");
	addField($tbl[product_option_set],"out_hide", "enum('Y','N') default 'N'");
	addField($tbl[product_option_set],"upfile1","varchar(100) not null");
	addField($tbl[product_gift],"delete","enum('N','Y') NOT NULL default 'N'"); // 2007-10-18 : 삭제될 경우 주문서에서 조회가 안되므로 숨기는방식 - Han
	addField($tbl[product_gift],"point_limit", "int(11) not null"); // 2007-02-20 : 포인트로 사은품 구매 - Han

	addField($tbl[coordi_set], "caption", "text not null");

	addField($tbl[member],"point","INT(7) NOT NULL");
	addField($tbl[member],"point1","INT(7) NOT NULL");
	addField($tbl[member],"point2","INT(7) NOT NULL");
	addField($tbl[member],"point3","INT(7) NOT NULL");
	addField($tbl[member],"point4","INT(7) NOT NULL");
	addField($tbl[member],"point_use","INT(7) NOT NULL");
	addField($tbl[member],"birth","varchar(12) not null");
	addField($tbl[member],"birth_type","varchar(5) not null");
	addField($tbl[member],"sex","varchar(5) not null");
	addField($tbl[member], "nick", " varchar(16) default NULL"); // 2008-11-27 닉네임 처리 by zardsama
	addField($tbl[member],"emoney","INT(7) NOT NULL");
	addField($tbl[member],"mng_memo","text NULL");
	addField($tbl[member],"attend","INT(4) NOT NULL");
	addField($tbl[member_group],"upfile1","VARCHAR(40) NOT NULL");
	addField($tbl[member_group],"updir","VARCHAR(25) NOT NULL");
	addField($tbl[member_group],"content","TEXT NOT NULL");
	addField($tbl[member_group],"move_point","INT( 11 ) NOT NULL");
	addField($tbl[member_group],"move_add_point","INT( 11 ) NOT NULL");
	addField($tbl[member_group],"free_delivery","enum('Y','N') NOT NULL default 'N'");

	addField($tbl[order],"emoney_prc","INT(6) NOT NULL");
	addField($tbl[order],"emoney_recharge","ENUM( 'Y', 'N' ) DEFAULT 'N' NOT NULL");
	addField($tbl[order],"emoney_recharge_date","INT (11) NOT NULL");
	addField($tbl[order],"milage_recharge","ENUM( 'Y', 'N' ) DEFAULT 'N' NOT NULL");
	addField($tbl[order],"milage_recharge_date","INT (11) NOT NULL");
	addField($tbl[order],'ext_date','INT (11) NOT NULL');
	addField($tbl[order],"receive","char (1) NOT NULL");
	addField($tbl[order],"receive_date","int (11) NOT NULL");
	addField($tbl[order],"bk_no","INT(11) NOT NULL");
	addField($tbl[order], "sale6", "int(5) not null");
	addField($tbl[order],"extra1","INT (11) NULL");
	addField($tbl[order], "free_delivery", "varchar(255) not null"); // 2007-01-29 : 무료배송 상품 아이값을담는다 - Han
	addField($tbl[order], "point_use", "int(7) not null");
	addField($tbl[order],"card_fail"," enum('Y', '') default ''");
	addField($tbl[order],"stat2","varchar(100) NOT NULL");
	addField($tbl[order],"repay_prc","int(11) NOT NULL default 0");
	addField($tbl[order],"repay_milage","int(11) NOT NULL default 0");
	addField($tbl[order],"repay_date","int(10) NOT NULL default 0");
	addField($tbl[order],"title2","VARCHAR(255) NOT NULL"); // title2 필드 추가 2007-02-21 Jin
	addField($tbl[order],'print','INT (5) NOT NULL');
	addField($tbl['order'], "memo_cnt", "int(3) NOT NULL default 0");
	addField($tbl[order],"prd_nums","VARCHAR(100) NOT NULL");
	addField($tbl[order_product],"repay_prc","int(11) NOT NULL default 0");
	addField($tbl[order_product],"repay_milage","int(11) NOT NULL default 0");
	addField($tbl[order_product],"repay_date","int(10) NOT NULL default 0");
	addField($tbl[order_product], "anx_no", "int(11) not null"); // 2007-03-06 : 부속상품필드추가
	addField($tbl[order_product],"dlv_no","INT(11) NOT NULL");
	addField($tbl[order_product],"dlv_code","varchar(30) NOT NULL");

	addField($tbl[qna], "ip", "varchar(15) not null");
	addField($tbl[qna],"notice","ENUM( 'Y', 'N' ) DEFAULT 'N' NOT NULL"); // 공지 필드 추가 2006-09-21
	addField($tbl[qna],"cate","VARCHAR(30) NOT NULL"); // 분류 필드 추가 2006-09-21
	addField($tbl[qna],'mng_memo','text default NULL after `answer_date`'); // 2008-09-25 관리자메모 추가 by zardsama
	addField($tbl[qna],"hit","smallint(5) not null");
	addField($tbl[qna],"answer_ok","enum('N','Y') NOT NULL default 'N'");

	addField($tbl[review],"milage","INT (5) NOT NULL");
	addField($tbl[review],"milage_date","INT (11) NOT NULL");
	addField($tbl[review],"ono","VARCHAR(15) NOT NULL");
	addField($tbl[review],"notice","ENUM( 'Y', 'N' ) DEFAULT 'N' NOT NULL"); // 공지 필드 추가 2006-10-18
	addField($tbl[review],"hit","smallint(5) not null");
	addField($tbl[review],"cate","varchar(30) not null");

	addField($tbl[cs],"reply_ok","enum('N','Y') NOT NULL default 'N'");

	addField($tbl[mng], "name", "varchar(20) NOT NULL");
	addField($tbl[mng], "team1", "int(11) NOT NULL");
	addField($tbl[mng], "team2", "int(11) NOT NULL");
	addField($tbl[mng], "position", "varchar(20) NOT NULL");
	addField($tbl[mng], "birth", "varchar(15) NOT NULL");
	addField($tbl[mng], "phone", "varchar(15) NOT NULL");
	addField($tbl[mng], "cell", "varchar(15) NOT NULL");
	addField($tbl[mng], "email", "varchar(20) NOT NULL");
	addField($tbl[mng], "address", "varchar(255) NOT NULL");
	addField($tbl[mng], "reg_date", "int(11) NOT NULL");
	addField($tbl[mng], "membersearch", "text NOT NULL");
	addField($tbl[mng], "ordersearch", "text NOT NULL");

	addField($tbl[msg],"admin_no","int(11) NOT NULL");
	addField($tbl[msg],"admin_id","varchar(20) NOT NULL");
	addField($tbl[msg],"admin_name","varchar(20) NOT NULL");

	addField($tbl[banner], "cate", "varchar(1) not null");
	addField($tbl[banner], "name", "varchar(255) not null");
	addField($tbl[banner], "maptext", "text not null");

	addField($tbl[delivery_url], "sort", "int(11) not null"); // 2007-03-08 : 배송사 정렬 - Han

	addField($tbl[popup],"layer","ENUM( 'Y', 'N' ) DEFAULT 'N' NOT NULL");

	addField($tbl[poll_config], "milage", "int(10) default '0'"); // 2008-05-27 설문 참여 마일리지 추가 by zardsama
	addField($tbl[poll_config], "upfile1", "varchar(100) NOT NULL");
	addField($tbl[poll_config], "content", "text NOT NULL");

	addField($tbl[product],"seller","varchar(100)"); // 장기정보 추가 2009-01-21 by zardsama
	addField($tbl[product],"origin_name","varchar(255)");

	// 필드속성변경
	$pdo->query("alter table `$tbl[order]` modify `order_gift` varchar(200) not null"); // 2007-02-28 : 사은품 복수선택이 가능하므로 필드속성을 변경합니다 - Han
	$pdo->query("alter table `$tbl[mng]` add INDEX `name` (`name`)");
	$pdo->query("alter table `$tbl[mng]` add INDEX `team1` (`team1`)");
	$pdo->query("alter table `$tbl[mng]` add INDEX `team2` (`team2`)");
	$pdo->query("alter table `$tbl[mng]` modify `auth` varchar(255) NOT NULL");
	$pdo->query("alter table `$tbl[product]` add KEY `wm_sc` (`wm_sc`)");
	$pdo->query("alter table `$tbl[member]` modify `jumin` varchar(40) not null");

	// 2009-02-12 - promotion conversion check field by zardsama
	addField($tbl[order], "conversion", "varchar(200) NOT NULL");
	addField($tbl[member], "conversion", "varchar(200) NOT NULL");
	addField($tbl[log_count], "conversion", "varchar(200) NOT NULL");

	// 2009-02-18 포털 검색어 분석 로그
	addField($tbl[log_count],"engine","varchar(16) NOT NULL");
	addField($tbl[log_count],"keyword","varchar(64) NOT NULL");
	$pdo->query("alter table `$tbl[log_count]` add index `keyword` (`keyword`)");

	// 2009-04-09 코디셋 추가이미지 by zardsama
	addField($tbl[coordi_set], "upfile2", "varchar(100) NOT NULL");

	// 2009-04-24 개인결제창 by zardsama
	addField($tbl[category], "private", "ENUM( 'N', 'Y' ) NOT NULL DEFAULT 'N'");

	// 2009-05-07 참눈 by zardsama
	addField($tbl[product], "charmnoon", "ENUM( 'N', 'Y' ) NOT NULL DEFAULT 'N'");

	// 2009-05-13 주문서 분리 by zardsama
	addField($tbl[order], "parent", "VARCHAR( 16 ) NOT NULL AFTER `ono`");
	$pdo->query("alter table `wm_order` add index `parent` ( `parent` )");
	addField($tbl[order_stat_log], "spt", "VARCHAR( 15 ) NOT NULL AFTER `ono`");

	// 2009-06-12 QNA 제목필드 by zardsama
	addField($tbl[qna], "answer_title", "varchar(255) not null after `answer`");

	// 2009-10-07 메시지 중복 발송 방지
	addField($tbl['order'], "sms_history", "varchar(32) not null after `mail_send`");

	// 2009-10-14 도메인 만료일 관리 테이블
	$pdo->query($tbl_schema[domain_expire]);

	// 2009-12-01 카테고리 차단 메시지 by zardsama
	addField($tbl['category'], "no_access_msg", "varchar(255) not null after `no_access_page`");


	// 2010-01-08 로그인방식 변경 by zardsama
	if(!fieldExist($tbl['mng'], "ver")) addField($tbl['mng'], "ver", "enum('1','2') not null default '1'");

	// 2010-01-29 wdisk 추가 by zardsama
	$pdo->query("alter table `$tbl[product_image]` change `filetype` `filetype` char(1) NOT NULL DEFAULT '2'");

	// 2010-04-28 ssfw 통합 by zardsama
	$pdo->query($tbl_schema['product_option_img']);
	addField('mari_config', 'use_editor', "enum('1','2','3') NOT NULL default '3' COMMENT '에디터 사용여부' after `use_comment`");

	// 2010-05-10 네이버 체크아웃 by zardsama
	addField($tbl['product'], 'checkout', "enum('N','Y') not null default 'N'");

	// 2010-05-17 WingPos 적용 by zardsama
	$pdo->query("ALTER TABLE `$tbl[product_option_set]` CHANGE `necessary` `necessary` ENUM( 'Y', 'N', 'C' ) NOT NULL DEFAULT 'N' COMMENT 'Y: 필수항목, N:비필수항목, C:복합재고';");
	$pdo->query("ALTER TABLE `$tbl[product]` ADD `seller_idx` INT( 10 ) NOT NULL COMMENT '사입처 코드' AFTER `seller`;");
	$pdo->query("ALTER TABLE `$tbl[cart]` ADD `option_idx` VARCHAR( 255 ) NOT NULL AFTER `option_prc`;");
	$pdo->query("ALTER TABLE `$tbl[cart]` ADD `complex_no` INT( 10 ) NOT NULL AFTER `option_idx`;");
	$pdo->query("ALTER TABLE `$tbl[cart]` ADD INDEX `complex_no` ( `complex_no` ) ;");
	$pdo->query("ALTER TABLE `$tbl[order_product]` ADD `option_idx` VARCHAR( 255 ) NOT NULL AFTER `option_prc`;");
	$pdo->query("ALTER TABLE `$tbl[order_product]` ADD `complex_no` INT( 10 ) NOT NULL AFTER `option_prc`;");
	$pdo->query("ALTER TABLE `$tbl[order_product]` ADD INDEX `complex_no` (`complex_no`);");
	$pdo->query("ALTER TABLE `$tbl[provider]` ADD `arcade` VARCHAR(100) NOT NULL AFTER `provider`;");
	$pdo->query("ALTER TABLE `$tbl[provider]` ADD `floor` VARCHAR(100) NOT NULL AFTER `arcade`;");
	$pdo->query("alter table `$tbl[order]` ADD `safety_date` datetime NOT NULL");
	$pdo->query("alter table `$tbl[order]` ADD `barcode_date` datetime NOT NULL");
	$pdo->query("alter table `$tbl[order]` ADD `postpone_yn` char(1) NOT NULL default 'N'");
	$pdo->query("alter table `$tbl[order]` ADD `postpone_date` datetime NOT NULL");

	$pdo->query($tbl_schema['product_option_item']);
	$pdo->query($tbl_schema['erp_account']);
	$pdo->query($tbl_schema['erp_complex_option']);
	$pdo->query($tbl_schema['erp_inout']);
	$pdo->query($tbl_schema['erp_order']);
	$pdo->query($tbl_schema['erp_order_dtl']);
	$pdo->query($tbl_schema['erp_stock']);
	$pdo->query($tbl_schema['erp_stock']);
	$pdo->query($tbl_func['opt_name']);
	$pdo->query($tbl_func['curr_stock']);

	$pdo->query("ALTER TABLE `erp_order` DROP INDEX `order_date`");
	$pdo->query("ALTER TABLE `erp_order` ADD INDEX `order_date` (`order_date`)");

	$pdo->query("alter table `erp_complex_option` ADD `force_soldout` enum('Y','N','L') NOT NULL default 'N' after `base_stock_qty`");
	$pdo->query("alter table `erp_complex_option` CHANGE `force_soldout` `force_soldout` enum('Y','N','L') not null default 'N'");
	$pdo->query("alter table `erp_complex_option` ADD `safe_stock_qty` int(11) NOT NULL default '0' after `base_stock_qty`");
	$pdo->query("alter table `erp_stock` ADD `adjust_soldout` ENUM('S','Y','N') NOT NULL DEFAULT 'S' AFTER `adjust_gap`");

	$pdo->query("alter table $tbl[provider] add account1 varchar(100) NOT NULL AFTER `pcell");
	$pdo->query("alter table $tbl[provider] add account1_bank varchar(10) not null after account1;");
	$pdo->query("alter table $tbl[provider] add account1_name varchar(50) not null after account1_bank;");
	$pdo->query("alter table $tbl[provider] add account2 varchar(100) NOT NULL AFTER `pcell");
	$pdo->query("alter table $tbl[provider] add account2_bank varchar(10) not null after account2;");
	$pdo->query("alter table $tbl[provider] add account2_name varchar(50) not null after account2_bank;");
	$pdo->query("alter table `$tbl[review]` change `stat` `stat` ENUM('1','2','3','4') NOT NULL DEFAULT '1'");
	$pdo->query("alter table `$tbl[card]` change `tno` `tno` varchar(40) NOT NULL ");

	// 2010-08-06 : MMS 기능 추가로 인해 텍스트 길이 변경 - Han
	$pdo->query("alter table `$tbl[sms_case]` modify `msg` text NOT NULL");

	// 2010-10-15 상품옵션 업로드 경로 추가 by zardsama
	$pdo->query("alter table $tbl[product_option_set] add updir varchar(50) not null default '' after out_hide");

	// 2010-11-09 : 현금영수증 발급 로그 - Han
	$pdo->query("alter table `$tbl[cash_receipt]` modify `mtrsno` varchar(50) NOT NULL");
	$pdo->query("alter table `$tbl[cash_receipt]` add `b_num` varchar(10) NOT NULL after `amt4`");
	$pdo->query($tbl_schema[cash_receipt_log]);
	$pdo->query("alter table `$tbl[order]` add `mobile` enum('Y', 'N') default 'N' after `ono`");

	// 2011-01-05 쿠폰 다운로드 방식 추가 by zardsama
	$pdo->query("alter table `$tbl[coupon]` change `down_type` `down_type` ENUM('A','B','C','D') NULL DEFAULT 'A'");
	addField($tbl['coupon'], 'use_limit', "char(1) NOT NULL COMMENT '쿠폰사용제한' after `download_limit_ea`");
	addField($tbl['coupon_download'], 'use_limit', "char(1) NOT NULL COMMENT '쿠폰사용제한'");

	if(!fieldExist($tbl['product'], 'tax_free')) addField($tbl['product'], "tax_free", " enum('N','Y') not null default 'N' after free_delivery");

	//2011-04-04 : 블랙리스트 회원필드 추가 - Jung
	$pdo->query("ALTER TABLE `wm_member` ADD `blacklist` INT( 2 ) NOT NULL DEFAULT '0'");
	$pdo->query("ALTER TABLE `wm_member` ADD `black_reason` TEXT NULL");

	//2011-04-11 예치금 처리자 ID 로그 저장 Jung
	$pdo->query("ALTER TABLE `$tbl[emoney]` ADD `admin_id` varchar(20) NOT NULL");

	//2011-05-03 회원그룹로그인시메시지 Jung
	$pdo->query("alter table `wm_member_group` add `group_msg` text not null");

	//2011-05-12 상품qna파일첨부 Cham
	$pdo->query("alter table `wm_qna` add `updir` varchar(25) not null");
	$pdo->query("alter table `wm_qna` add `upfile1` varchar(255) not null");
	$pdo->query("alter table `wm_qna` add `upfile2` varchar(255) not null");
	$pdo->query("alter table `wm_qna` add `ori_file1` varchar(255) not null");
	$pdo->query("alter table `wm_qna` add `ori_file2` varchar(255) not null");

	//2011-06-08 상품 추가항목 이미지 Jung
	$pdo->query("ALTER TABLE `wm_product_filed_set` ADD `updir` VARCHAR( 25 ) NULL");
	$pdo->query("ALTER TABLE `wm_product_filed_set` ADD `upfile1` VARCHAR( 40 ) NULL");

	//2011-06-22 상품추가이미지 파일타입8추가
	$pdo->query("ALTER TABLE `wm_product_image` modify `filetype` char(1) NOT NULL default '2'");

	//2011-08-19 상품 수정시 상태,시간정보 업데이트
	$pdo->query("ALTER TABLE `wm_product` ADD `ep_stat` VARCHAR(255) NOT NULL");
	$pdo->query("ALTER TABLE `wm_product` ADD `edt_date2` INT(11) NULL");

	// 2012-02-06 체크아웃 api4 by zardsama
	$pdo->query("alter table `$tbl[order]` add `checkout` ENUM('N','Y') not null DEFAULT'N' comment '체크아웃상품여부', add `checkout_last` INT( 10) not null comment '최종상품수정시간';");
	$pdo->query("alter table `$tbl[order_product]` add `checkout_ono` VARCHAR(30) not null comment '체크아웃 주문상품번호';");
	$pdo->query("alter table `$tbl[order_product]` add index `checkout_ono` (`checkout_ono`);");

	// 2012-04-05 주문상품별 배송지연 by zardsama
	$pdo->query("alter table $tbl[order_product] add dlv_hold ENUM('N','Y') not null default 'N' comment '배송지연'");
	$pdo->query("alter table $tbl[order_product_log] add dlv_hold ENUM('N','Y') not null default 'N'");
	$pdo->query("alter table $tbl[order_product_log] add ori_hold ENUM('N','Y') not null default 'N'");

	$pdo->query("alter table $tbl[order_memo] add type enum('1','2') NOT NULL default '1' COMMENT '1.주문메모 2.회원메모' after `content`");

	// 2012-05-11 DB세션 by zardsama
	if(!isTable($tbl['session'])) $pdo->query($tbl_schema['session']);

	if(!fieldExist($tbl['member'], 'reg_email')) { // 2012-08-13
		$pdo->query("alter table $tbl[member] add reg_email enum('N','W','Y') not null default 'N' comment '이메일인증'");
		$pdo->query("alter table $tbl[member] add reg_sms enum('N','Y') not null default 'N' comment '휴대폰인증'");
		$pdo->query("alter table $tbl[member] add reg_code varchar(64) not null default '' comment '인증코드'");
	}

	// 이니시스 이니라이트 by destiny
	$pdo->query("alter table `wm_card` add pg_version varchar(20) not null default '' after pg");
	$pdo->query("alter table `wm_vbank` add pg_version varchar(20) not null default '' after pg");
	$pdo->query("alter table `wm_order` add confirm_yn char(1) not null default ''");

	if(!fieldExist($tbl['product'], 'weight')) { // 2012-08-20 해외배송 by zardsama
		$pdo->query("alter table `$tbl[product]` add `weight` int(10) not null default '0' after origin_prc");
		$pdo->query("alter table `$tbl[order]` add `nations` varchar(30) not null");
		$pdo->query("alter table `$tbl[order]` add `cart_weight` int(10) not null default '0'");
		$pdo->query("alter table `$tbl[order]` add index `nations` (`nations`)");
		$pdo->query("alter table `$tbl[bank_account]` add `type` enum('', 'int') not null default ''");
		$pdo->query("alter table `$tbl[bank_account]` add index `type`(`type`)");
	}

	if(!fieldExist('mari_config', 'auto_secret')) {
		$pdo->query("alter table mari_config add `auto_secret` enum('N','Y') not null default 'N' after `upfile_size`");
	}

	if(!fieldExist($tbl['qna'], 'email')) {
		addField($tbl['qna'], 'email', "varchar(100) not null default ''");
		addField($tbl['qna'], 'cell', "varchar(13) not null default ''");
	}

	if(fieldExist($tbl['coupon'], 'attachtype') == false) {
		addField($tbl['coupon'], 'attachtype', 'int(1)');
		addField($tbl['coupon'], 'attach_items', 'text');

		$res = $pdo->iterator("select * from $tbl[coupon] where stype='2'");
        foreach ($res as $cpn) {
			$attach_items = '';
			$pres = $pdo->iterator("select no from $tbl[product] where coupon like '%@$cpn[no]@%' order by no asc");
            foreach ($pres as $prd) {
				$attach_items .= "[$prd[no]]";
			}
			$pdo->query("update $tbl[coupon] set code='conv_$cpn[no]', stype=1, attachtype=2, attach_items='$attach_items' where no='$cpn[no]'");
			$pdo->query("update $tbl[coupon_download] set stype=1, attachtype=2 where cno='$cpn[no]'");
		}
		$result = 'Y';
	}

	if(!fieldExist($tbl['member_group'], 'milage2')) {
		addField($tbl['member_group'], 'milage2', 'int(2) after milage');
		if($cfg['member_event_type'] == 1) {
			$pdo->query("update $tbl[member_group] set milage2=milage, milage='0'");
		}
	}

	if(!fieldExist($tbl['product'], 'show_mobile')) {
		$pdo->query("alter table `$tbl[product]` add `show_mobile` enum('Y','N') not null default 'Y' after `checkout`");
	}

	if(!fieldExist($tbl['product_option_item'], 'max_val')) {
		addField($tbl['product_option_item'], 'max_val', 'int(10)');
		addField($tbl['product_option_item'], 'min_val', 'int(10)');
		addField($tbl['product_option_item'], 'add_price_option', 'int(10)');
		addField($tbl['product_option_item'], 'min_area', 'int(10)');
		addField($tbl['product_option_item'], 'min_area_option', 'enum("N","Y") default "N"');
		$pdo->query("alter table `$tbl[product_option_set]` change `how_cal` `how_cal` enum('1', '2', '3', '4') not null default '1' ");

		addField($tbl['product_option_set'], 'desc', 'text');
	}

	if(!fieldExist($tbl['product_filed_set'], 'sort')) {
		addField($tbl['product_filed_set'], "sort", "INT( 5 ) NOT NULL DEFAULT '0' COMMENT '정렬'");
	}

	addField('mari_board', 'pno', "varchar(100) not null default ''");
	addField('mari_board', 'w1', "int(5) not null default '0'");
	addField('mari_board', 'h1', "int(5) not null default '0'");
	addField('mari_board', 'w2', "int(5) not null default '0'");
	addField('mari_board', 'h2', "int(5) not null default '0'");

	addField($tbl['coupon'], 'device', 'varchar(10) not null default "" comment "사용가능 디바이스"');
	addField($tbl['coupon_download'], 'device', 'varchar(10) not null default "" comment "사용가능 디바이스"');
	addField($tbl['popup'], 'device', 'varchar(6)');

	if(!$pdo->row("select count(*) from $tbl[popup_frame] where title='모바일 전체화면 팝업틀'")) {
		$pdo->query("INSERT INTO `$tbl[popup_frame]` (`title`, `content`, `html`, `reg_date`, `upfile1`, `upfile2`, `upfile3`, `updir`) VALUES('모바일 전체화면 팝업틀', '<div style=\'margin: 10px; padding: 10px; background: #fff;\'>\r\n    <div style=\'padding:5px\' class=\'pop100\'>{내용}</div>\r\n    <div style=\'margin: 0 5px; padding: 10px 0; text-align: right; font-size: 11px; border-top: solid 1px #ccc;\'>\r\n        <a href=\"{하루창}\">하루 동안 열지 않기</a> | \r\n        <a href=\"{창닫기}\">Close</a></</a>\r\n    </div>\r\n</div>', '2', 1424927631, '', '', '', '');");
	}
	if(!$pdo->row("select count(*) from $tbl[popup_frame] where title='모바일 기본 팝업틀'")) {
		$pdo->query("INSERT INTO `$tbl[popup_frame]` (`title`, `content`, `html`, `reg_date`, `upfile1`, `upfile2`, `upfile3`, `updir`) VALUES('모바일 기본 팝업틀', '<div style=\'position:absolute;\'>\r\n    {내용}\r\n    <div><a href=\"{하루창}\">□ 하루 동안 열지 않기</a></div>\r\n    <div style=\'position: absolute; top:5px; right: -25px;\'><a href=\"{창닫기}\" style=\'font-size: 25px;\'>ⓧ</a></div>\r\n</div>', '2', 1424932004, '', '', '', '');");
	}



	/* +----------------------------------------------------------------------------------------------+
	' |  스마트윙
	' +----------------------------------------------------------------------------------------------+*/
	// 불필요 필드 정리
	$pdo->query("alter table $tbl[product] drop safety_stock_qty");
	$pdo->query("alter table $tbl[product] drop yahoo_fss_ctg");
	$pdo->query("alter table $tbl[product] drop prc_text");
	$pdo->query("alter table $tbl[product] drop ipay");
	$pdo->query("alter table $tbl[product] drop charmnoon");

	if(!isTable($tbl['order_payment'])) $pdo->query($tbl_schema['order_payment']);
	if(!isTable($tbl['claim_reasons'])) $pdo->query($tbl_schema['claim_reasons']);
	if(!isTable($tbl['member_deleted'])) $pdo->query($tbl_schema['member_deleted']);
	if(!isTable($tbl['excel_preset'])) $pdo->query($tbl_schema['excel_preset']);
	if(!isTable($tbl['config'])) $pdo->query($tbl_schema['config']);
	if(!isTable($tbl['off_store'])) $pdo->query($tbl_schema['off_store']);

	addField($tbl['order'], "x_order_id", "varchar(20) after ono");
	addField($tbl['order'], "s_order_id", "varchar(20) after x_order_id");
	addField($tbl['order'], "opay_prc", "int(10) after pay_prc");
	addField($tbl['order_product'], "ostat", "int(2) after stat");
	addField($tbl['order_product'], "sale2", "int(10)");
	addField($tbl['order_product'], "sale4", "int(10)");
	addField($tbl['order_product'], "sale5", "int(10)");
	addField($tbl['order_product'], "sale6", "int(10)");
	addField($tbl['order_product'], "dooson_pno", "varchar(50)");
	addField($tbl['order_product'], "ex_pno", "varchar(10)");
	addField($tbl['order_product'], "ex_type", "int(2)");
	addField($tbl['order_product'], "s_order_id", "varchar(20)");
	addField($tbl['order_product'], 'event_milage', 'int(5) not null default "0" after member_milage');
	$pdo->query("
		alter table $tbl[order_product]
			add `r_name` VARCHAR(50) NOT NULL,
			add `r_zip` VARCHAR(7) NOT NULL,
			add `r_addr1` VARCHAR(100) NOT NULL,
			add `r_addr2` VARCHAR(200) NOT NULL,
			add `r_phone` VARCHAR(20) NOT NULL,
			add `r_cell` VARCHAR(20) NOT NULL,
			add `r_message` TEXT NOT NULL
	");
	addField($tbl['order_stat_log'], "content", "text");
	addField($tbl['order_stat_log'], "pno", "varchar(200)");
	addField($tbl['order_stat_log'], "payment_no", "int(10)");
	addField($tbl['coupon'], "pay_type", "int(1) not null default 1");
	addField($tbl['coupon_download'], "pay_type", "int(1) not null default 1");
	addField($tbl['sccoupon_use'], 'code_no', 'int(10) not null default 0 after code');
	$pdo->query("alter table $tbl[sccoupon_use] add index code_no(code_no)");
	addField($tbl['member'], 'last_order', "int(10) not null default '0' after last_con");
	addField($tbl['member'], 'birthcpn', "char(4) not null default ''");
	addField($tbl['product'], 'stock_yn', "enum('N','Y') after ea");
	addField($tbl['product_option_item'], 'hidden', "enum('N','Y') not null default 'N'");
	addField($tbl['product_option_item'], 'ds_opt', "varchar(20)");
	addField($tbl['coupon'], 'place', 'varchar(10) not null default ""');
	addField($tbl['coupon_download'], 'place', 'varchar(10) not null default ""');
	addField('erp_complex_option', 'is_soldout', "enum('N', 'Y') not null default 'N'");
	addField('erp_complex_option', 'qty', "int(10) not null default '0' after pno");
	addField('erp_complex_option', 'sku', 'VARCHAR(50) NOT NULL AFTER `barcode`');
	addField('erp_complex_option', 'color_cd', 'VARCHAR(20)');
	addField('erp_complex_option', 'size_cd', 'VARCHAR(20)');
	addField('erp_complex_option', 'opts', 'varchar(200) not null default ""');
	$pdo->query("drop function if exists `curr_stock`");
	$pdo->query($tbl_func['curr_stock']);

	$pdo->query("alter table $tbl[order] add index x_order_id(x_order_id)");
	$pdo->query("alter table $tbl[order_product] add index dooson_pno(dooson_pno)");
	$pdo->query("alter table $tbl[order_stat_log] change stat stat varchar(3) not null default ''");
	$pdo->query("ALTER TABLE `erp_complex_option` ADD INDEX sku (`sku`)");
	$pdo->query("alter table wm_product drop acs_prd;");
	$pdo->query("alter table wm_product drop sold_reserve;");
	$pdo->query("
		alter table erp_complex_option change  `force_soldout` `force_soldout` ENUM('Y', 'N', 'L') NOT NULL DEFAULT  'N',
		change `del_yn` `del_yn` CHAR(1) NOT NULL DEFAULT  'N',
		change `is_soldout` `is_soldout` ENUM( 'N', 'Y') NOT NULL DEFAULT  'N';
	");

	$pdo->query("alter table $tbl[order] drop aff, drop aff_time");
	$pdo->query("alter table $tbl[order] drop opay_prc");
	$pdo->query("alter table $tbl[order] drop gift_type, gift_msg, gift_name");
	$pdo->query("alter table $tbl[order] drop barcode_date, gift_msg, gift_name");

	$pdo->query("alter table $tbl[order] change ono ono varchar(30) character set utf8 collate utf8_general_ci NOT NULL");
	$pdo->query("alter table $tbl[order_product] change ono ono varchar(30) character set utf8 collate utf8_general_ci NOT NULL");
	$pdo->query("alter table $tbl[order_memo] change ono ono varchar(30) character set utf8 collate utf8_general_ci NOT NULL");
	$pdo->query("alter table $tbl[order_payment] change ono ono varchar(30) character set utf8 collate utf8_general_ci NOT NULL");
	$pdo->query("alter table $tbl[order_stat_log] change ono ono varchar(30) character set utf8 collate utf8_general_ci NOT NULL");
	$pdo->query("alter table $tbl[coupon_download] change ono ono varchar(30) character set utf8 collate utf8_general_ci NOT NULL");
	$pdo->query("alter table $tbl[cash_receipt] change ono ono varchar(30) character set utf8 collate utf8_general_ci NOT NULL");
	$pdo->query("alter table $tbl[order_memo] change type type enum('1', '2', '3') character set utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1'");

	addField($tbl['cs'], 'phone', 'varchar(14) not null');
	addField($tbl['cs'], 'email', 'varchar(150) not null');

	$pdo->query("
		alter table  $tbl[product] change sell_prc sell_prc DOUBLE(10, 2 ) UNSIGNED NOT NULL DEFAULT  '0.00',
 								   change normal_prc normal_prc DOUBLE(10, 2 ) UNSIGNED NULL DEFAULT NULL,
 								   change origin_prc origin_prc DOUBLE(10, 2 ) UNSIGNED NULL DEFAULT NULL,
 								   change milage milage DOUBLE(8, 2 ) UNSIGNED NULL DEFAULT NULL ;
	");
	$pdo->query("
		alter table $tbl[order] change total_prc total_prc double(10,2) unsigned not null default '0.00',
                                change milage_prc milage_prc double(10,2) unsigned not null default '0.00',
                                change emoney_prc emoney_prc double(10,2) unsigned not null default '0.00',
                                change pay_prc pay_prc double(10,2) unsigned not null default '0.00',
                                change prd_prc prd_prc double(10,2) unsigned not null default '0.00',
                                change dlv_prc dlv_prc double(10,2) unsigned not null default '0.00',
                                change repay_prc repay_prc double(10,2) unsigned not null default '0.00',
                                change repay_milage repay_milage double(10,2) unsigned not null default '0.00',
                                change sale1 sale1 double(8,2) unsigned not null default '0.00',
                                change sale2 sale2 double(8,2) unsigned not null default '0.00',
                                change sale3 sale3 double(8,2) unsigned not null default '0.00',
                                change sale4 sale4 double(8,2) unsigned not null default '0.00',
                                change sale5 sale5 double(8,2) unsigned not null default '0.00',
                                change sale6 sale6 double(8,2) unsigned not null default '0.00',
                                change total_milage total_milage double(8,2) unsigned not null default '0.00',
                                change member_milage member_milage double(8,2) unsigned not null default '0.00';
	");

	$pdo->query("
		alter table $tbl[order_product] change sell_prc sell_prc double(10,2) unsigned not null default '0.00',
		                                change total_prc total_prc double(10,2) unsigned not null default '0.00',
		                                change milage milage double(8,2) unsigned not null default '0.00',
		                                change total_milage total_milage double(8,2) unsigned not null default '0.00',
		                                change repay_prc repay_prc double(10,2) unsigned not null default '0.00',
										change sale2 sale2 double(8,2) unsigned not null default '0.00',
										change sale4 sale4 double(8,2) unsigned not null default '0.00',
										change sale5 sale5 double(8,2) unsigned not null default '0.00',
										change sale6 sale6 double(8,2) unsigned not null default '0.00',
		                                change repay_milage repay_milage double(8,2) unsigned not null default '0.00';
	");


	$pdo->query("
		alter table $tbl[order_payment] change amount amount double(10,2) not null default '0.00',
										change dlv_prc dlv_prc double(8,2) unsigned not null default '0.00',
										change add_dlv_prc add_dlv_prc double(8,2) unsigned not null default '0.00',
										change ex_dlv_prc ex_dlv_prc double(8,2) unsigned not null default '0.00',
										change emoney_prc emoney_prc double(10,2) unsigned not null default '0.00',
										change milage_prc milage_prc double(10,2) unsigned not null default '0.00',
										change repay_emoney repay_emoney double(10,2) unsigned not null default '0.00',
										change repay_milage repay_milage double(10,2) unsigned not null default '0.00';
	");

	// member_id 필드
	$pdo->query("ALTER TABLE mari_board MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE mari_board_day MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE mari_comment MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_blacklist_log MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_coupon_download MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_cs MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_emoney MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_intra_board MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_intra_comment MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_member MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_member_deleted MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_member_log MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_milage MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_mng_log MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_msg MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_neko MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_order MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_order_stat_log MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_point MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_poll_comment MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_pwd_log MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_qna MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_review MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_review_comment MODIFY member_id varchar(50)");
	$pdo->query("ALTER TABLE wm_social_coupon_use MODIFY member_id varchar(50)");

	// m_sell_prc, m_normal_prc
	$pdo->query("ALTER TABLE wm_product add m_sell_prc double(10,2) unsigned not null after sell_prc");
	$pdo->query("ALTER TABLE wm_product add m_normal_prc double(10,2) unsigned after normal_prc");

	// 해외 배송 관련 테이블
	if(!isTable($tbl['os_delivery_area'])) $pdo->query($tbl_schema['os_delivery_area']);
	if(!isTable($tbl['os_delivery_country'])) $pdo->query($tbl_schema['os_delivery_country']);
	if(!isTable($tbl['os_delivery_prc'])) $pdo->query($tbl_schema['os_delivery_prc']);

	$pdo->query("alter table wm_delivery_url add transfer_price enum('W','N','Y') default 'W' comment '해외배송 환산여부'");
	$pdo->query("alter table wm_delivery_url add overseas_delivery enum('O','D') default 'D' comment '국내/해외배송여부'");
	$pdo->query("alter table wm_product modify weight double(10,2)");

	// HS Code
	if(!isTable($tbl['hs_code'])) $pdo->query($tbl_schema['hs_code']);
	$pdo->query("alter table wm_product add hs_code varchar(15) comment 'HS Code'");

	// 주문 테이블 해외배송시 배송 업체 필드 추가
	$pdo->query("alter table wm_order add delivery_com int comment '배송업체(해외배송 사용시)'");

	// 주문테이블 무게 필드 수정
	$pdo->query("alter table wm_order modify cart_weight double(10,2)");

	// 기획전 정렬 테이블
	$pdo->query($tbl_schema['product_link']);

	// 카드로그 금액 필드 수정
	$pdo->query("alter table wm_card modify wm_price double(10,2)");
	$pdo->query("alter table wm_vbank modify wm_price double(10,2)");
	$pdo->query("alter table wm_card add currency varchar(5) comment '결제화폐단위'");

	// 참고 상품명 필드 추가
	$pdo->query("alter table wm_product add name_referer varchar(150) comment '참고용 상품명'");

	// pay_type 필드
	$pdo->query("alter table wm_order modify pay_type char(2)");

	// 카드 로그 금액 필드 수정
	$pdo->query("alter table wm_card_cc_log modify price double(10,2)");

	// 입점몰 관련 필드
	addField($tbl['delivery_url'], 'partner_no', 'varchar(20) not null default ""');
	addField($tbl['excel_preset'], 'partner_no', 'int(10) not null default "0"');
	$pdo->query("alter table $tbl[delivery_url] add index partner_no (partner_no)");

	//카테고리 분류(매장)코드 필드 수정
	$pdo->query("alter table wm_category modify `code` varchar(30)");

	// 적립금 기간 만료 최초 설정
	if(!fieldExist($tbl['milage'], 'expire_date')) {
		addField($tbl['milage'], 'expire_date', 'int(10) not null default 0');
		addField($tbl['milage'], 'use_amount', 'int(10) not null default 0 after amount');
		addField($tbl['milage'], 'expire', 'enum("N","Y") not null default "N"');
		$pdo->query("alter table $tbl[milage] add index expire(expire);");

		$res = $pdo->iterator("select sum(amount) as amount, member_no from $tbl[milage] where ctype='-' group by member_no");
        foreach ($res as $data) {
			$mil_temp = $data['amount'];
			$res2 = $pdo->iterator("select no, amount, use_amount from $tbl[milage] where ctype='+' and member_no='$data[member_no]' and amount>use_amount order by reg_date asc");
            foreach ($res2 as $data2) {
				$tmp = $data2['amount']-$data2['use_amount'];
				$tmp = ($tmp <= $mil_temp) ? $tmp : $mil_temp;
				$mil_temp -= $tmp;

				$pdo->query("update $tbl[milage] set use_amount=use_amount+'$tmp' where no='$data2[no]'");
				if($mil_temp < 1) break;
			}
		}
	}

	// 상품정보고시
	if(!fieldExist($tbl['product_field_set'], 'category')) {
		$pdo->query("ALTER TABLE `$tbl[product_field_set]` ADD `category` INT(10) NOT NULL COMMENT '정보고시그룹코드' AFTER `no`, ADD INDEX `category` (`category`);");
	}
	addField($tbl['product'], 'fieldset', 'int(10) not null default "0" comment "사용 필드셋(상품고시) 번호" after content5');

	// 한정옵션 기능 삭제 윙포스로 통합
	$pdo->query("update $tbl[product] set ea_type=1 where ea_type=2;");
	$pdo->query("update $tbl[product_option_set] set necessary='Y' where necessary='C'");

	// 컬러칩
	if(!isTable($tbl['product_option_colorchip'])) $pdo->query($tbl_schema['product_option_colorchip']);
	addField($tbl['product_option_item'], 'chip_idx', 'int(10) not null default "0" comment "컬러칩"');
	$pdo->query("alter table $tbl[product_option_item] add index chip_idx (chip_idx)");

	// 단독배송상품
	addField($tbl['product'], 'dlv_alone', "enum('N','Y') not null default 'N' comment '단독배송여부' after free_delivery");
	$pdo->query("alter table $tbl[product] drop no_interest");

	// 상품 q&a Notice default 'N'
	$pdo->query("alter table $tbl[qna] modify notice enum('Y','N') default 'N' not null");

	// 페이팔/알리페이 관련 필드 추가
	addField($tbl['order'], "tax", "double(10,2)");
	addField($tbl['card'], "ref_info", "text");
	addField($tbl['card'], "shipping_info", "text");
?>