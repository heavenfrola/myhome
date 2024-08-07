<?PHP

	$tbl_schema['sbscr'] = "
	CREATE TABLE IF NOT EXISTS `$tbl[sbscr]` (
	  `no` int(10) NOT NULL AUTO_INCREMENT,
	  `sbono` varchar(18) NOT NULL COMMENT '정기배송 주문번호',
	  `mobile` enum('Y','N','A') DEFAULT 'N' COMMENT '주문매체',
	  `stat` varchar(2) NOT NULL DEFAULT '' COMMENT '상태값',
	  `member_no` int(10) DEFAULT NULL COMMENT '주문 회원 번호',
	  `member_id` varchar(100) DEFAULT NULL COMMENT '주문 회원 아이디',
	  `date1` int(10) NOT NULL DEFAULT '0' COMMENT '주문일시',
	  `date2` int(10) NOT NULL DEFAULT '0' COMMENT '결제 발생 일시',
	  `date3` int(10) NOT NULL DEFAULT '0' COMMENT '진행중 일시',
	  `date5` int(10) NOT NULL DEFAULT '0' COMMENT '진행종료 일시',
	  `date_ext` int(10) NOT NULL DEFAULT '0' COMMENT '취소 발생 일시',
	  `buyer_name` varchar(50) NOT NULL DEFAULT '' COMMENT '주문자 명',
	  `buyer_email` varchar(150) NOT NULL DEFAULT '' COMMENT '주문자 이메일',
	  `buyer_phone` varchar(30) NOT NULL DEFAULT '' COMMENT '주문자 연락처',
	  `buyer_cell` varchar(30) NOT NULL DEFAULT '' COMMENT '주문자 휴대폰 번호',
	  `addressee_name` varchar(50) NOT NULL DEFAULT '' COMMENT '수령자 명',
	  `addressee_phone` varchar(30) NOT NULL DEFAULT '' COMMENT '수령자 연락처',
	  `addressee_cell` varchar(30) NOT NULL DEFAULT '' COMMENT '수령자 휴대폰 번호',
	  `addressee_zip` varchar(7) NOT NULL DEFAULT '' COMMENT '수령자 우편번호',
	  `addressee_addr1` varchar(150) NOT NULL DEFAULT '' COMMENT '수령자 주소',
	  `addressee_addr2` varchar(255) NOT NULL DEFAULT '' COMMENT '수령자 상세 주소',
	  `dlv_memo` text NOT NULL COMMENT '주문자 메모',
	  `mng_memo` text NOT NULL COMMENT '관리자 메모',
	  `pay_type` varchar(2) DEFAULT NULL COMMENT '결제방식',
	  `pay_sbscr` enum('N','Y') DEFAULT 'N' COMMENT '정기결제 여부',
	  `s_total_prc` double(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '정기주문 총 합산 금액',
	  `s_pay_prc` double(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '정기주문 총 실 결제 금액',
	  `s_prd_prc` double(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '정기주문 총 상품 금액',
	  `s_dlv_prc` double(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '정기주문 총 배송 금액',
	  `s_sale_prc` double(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '정기주문 총 할인 된 금액',
	  `s_total_milage` double(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '정기주문 총 지급 적립금',
	  `bank` varchar(100) NOT NULL DEFAULT '' COMMENT '무통자 입금은행',
	  `bank_name` varchar(50) NOT NULL DEFAULT '' COMMENT '무통장 입금자명',
	  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '정기배송 상품요약',
	  `sms_send` enum('Y','N') DEFAULT 'N' COMMENT '문자 알림 발송 여부',
	  `mail_send` enum('Y','N') DEFAULT 'N' COMMENT '이메일 알림 발송 여부',
	  `conversion` varchar(200) NOT NULL DEFAULT '' COMMENT '광고 유입 현황',
	  `billing_key` varchar(100) NOT NULL DEFAULT '' COMMENT '정기배송 빌링키',
	  PRIMARY KEY  (`no`),
	  UNIQUE KEY `sbono` (`sbono`),
	  KEY `mobile` (`mobile`),
	  KEY `member_no` (`member_no`),
	  KEY `member_id` (`member_id`),
	  KEY `date1` (`date1`),
	  KEY `date2` (`date2`),
	  KEY `date_ext` (`date_ext`),
	  KEY `buyer_name` (`buyer_name`),
	  KEY `pay_type` (`pay_type`),
	  KEY `pay_sbscr` (`pay_sbscr`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
	";

	$tbl_schema['sbscr_product'] = "
	CREATE TABLE IF NOT EXISTS `$tbl[sbscr_product]` (
	  `no` int(10) NOT NULL AUTO_INCREMENT,
	  `sbono` varchar(18) NOT NULL COMMENT '정기배송 주문번호',
	  `pno` int(10) NOT NULL DEFAULT '0' COMMENT '주문 상품 번호',
	  `stat` varchar(2) NOT NULL DEFAULT '1' COMMENT '상태값',
	  `name` varchar(150) DEFAULT NULL COMMENT '주문 상품 명',
	  `sell_prc` double(12,2) NOT NULL COMMENT '상품 낱개 가격(할인전)',
	  `milage` double(10,2) NOT NULL COMMENT '낱개 지급 적립금',
	  `buy_ea` int(5) NOT NULL DEFAULT '0' COMMENT '1회당 주문 수량',
	  `total_prc` double(12,2) NOT NULL COMMENT '총 상품 가격(할인전)',
	  `total_milage` double(10,2) NOT NULL COMMENT '총 지급 적립금',
	  `pay_prc` double(12,2) NOT NULL COMMENT '총 상품 가격(할인후)',
	  `option` varchar(300) NOT NULL DEFAULT '' COMMENT '선택한 옵션명',
	  `option_prc` varchar(300) NOT NULL DEFAULT '' COMMENT '옵션 추가가격',
	  `option_idx` varchar(300) NOT NULL DEFAULT '' COMMENT '옵션 코드',
	  `complex_no` int(10) NOT NULL COMMENT '윙포스코드',
	  `dlv_start_date` date NOT NULL COMMENT '첫 배송일',
	  `dlv_finish_date` date NOT NULL COMMENT '마지막 배송일',
	  `dlv_total_cnt` smallint(3) NOT NULL DEFAULT '0' COMMENT '총 배송 횟수',
	  `dlv_week` varchar(20) NOT NULL DEFAULT '0' COMMENT '총 배송요일',
	  `prd_dlv_prc` double(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '상품별 개별 배송비',
	  `stop` enum('N', 'Y') NOT NULL DEFAULT 'N' COMMENT '기간없을 경우 체크',
	  `partner_no` int(10) NOT NULL DEFAULT '0' COMMENT '입점사 코드',
	  `fee_rate` double(4,1) NOT NULL DEFAULT '0.0' COMMENT '입점사 수수료율',
	  `fee_prc` double(12,2) NOT NULL DEFAULT '0.00' COMMENT '입점사 수수료',
	  `cno` int(11) NOT NULL DEFAULT '0' COMMENT 'cart 번호',
	  `sale0` double(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '결제방식별 할증',
	  `sale1` double(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '세트 할인',
	  `sale2` double(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '이벤트 할인',
	  `sale3` double(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '타임 세일',
	  `sale4` double(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '회원 할인',
	  `sale5` double(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '쿠폰 할인',
	  `sale6` double(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '거래금액 기준 할인',
	  `sale7` double(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '개별 쿠폰 할인',
	  `sale8` double(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '정기배송 할인',
	  `sale9` double(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '수량 할인',
	  PRIMARY KEY  (`no`),
	  KEY `sbono` (`sbono`),
	  KEY `pno` (`pno`),
	  KEY `complex_no` (`complex_no`),
	  KEY `partner_no` (`partner_no`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
	";

	$tbl_schema['sbscr_schedule'] = "
	CREATE TABLE IF NOT EXISTS `$tbl[sbscr_schedule]` (
	  `no` int(10) NOT NULL AUTO_INCREMENT,
	  `sbono` varchar(18) NOT NULL COMMENT '정기배송 주문번호',
	  `date` date NOT NULL COMMENT '배송일자',
	  `date_org` date NOT NULL COMMENT '원 배송일자',
	  `product_cnt` smallint(3) NOT NULL DEFAULT '0' COMMENT '당일 배송나가는 상품 수',
	  `total_prc` double(12,2) NOT NULL COMMENT '당일 배송 총 금액',
	  `prd_prc` double(12,2) NOT NULL DEFAULT '0.00' COMMENT '당일 배송 상품의 금액',
	  `dlv_prc` double(7,2) NOT NULL DEFAULT '0.00' COMMENT '당일 발생하는 배송비',
	  PRIMARY KEY  (`no`),
	  KEY `sbono` (`sbono`),
	  KEY `date` (`date`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
	";

	$tbl_schema['sbscr_schedule_product'] = "
	CREATE TABLE IF NOT EXISTS `$tbl[sbscr_schedule_product]` (
	  `no` int(10) NOT NULL AUTO_INCREMENT,
	  `schno` int(10) NOT NULL DEFAULT '0' COMMENT '정기배송 스케쥴번호',
	  `sbono` varchar(18) NOT NULL DEFAULT '0' COMMENT '정기배송 주문번호',
	  `sbpno` int(10) NOT NULL DEFAULT '0' COMMENT '정기배송 상품번호',
	  `pno` int(10) NOT NULL DEFAULT '0' COMMENT '상품번호',
	  `partner_no` int(10) NOT NULL DEFAULT '0' COMMENT '입점사 코드',
	  `delivery_type` char(1) NOT NULL DEFAULT '' COMMENT '입점사 배송설정',
	  `delivery_base` char(1) NOT NULL DEFAULT '' COMMENT '배송비 기준(주문/결제)',
	  `delivery_free_limit` double(10,2) NOT NULL DEFAULT '0.00' COMMENT '입점사 무료배송 금액',
	  `delivery_fee` double(8,2) NOT NULL DEFAULT '0.00' COMMENT '입점사 기본 배송비',
	  `stat` varchar(2) NOT NULL DEFAULT '' COMMENT '부킹상태',
	  `ono` varchar(14) NOT NULL DEFAULT '' COMMENT '생성된 주문 번호',
	  `opno` int(10) NOT NULL DEFAULT '0' COMMENT '생성된 주문상품 번호',
	  `make_date` datetime NOT NULL COMMENT '주문서 생성일시',
	  PRIMARY KEY  (`no`),
	  KEY `schno` (`schno`),
	  KEY `sbono` (`sbono`),
	  KEY `sbpno` (`sbpno`),
	  KEY `pno` (`pno`),
	  KEY `partner_no` (`partner_no`),
	  KEY `stat` (`stat`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
	";

	$tbl_schema['sbscr_set'] = "
	CREATE TABLE IF NOT EXISTS `$tbl[sbscr_set]` (
	  `no` int(10) NOT NULL AUTO_INCREMENT,
	  `name` varchar(100) NOT NULL COMMENT '세트명',
	  `dlv_period` varchar(20) NOT NULL COMMENT '배송주기',
	  `dlv_week` varchar(20) NOT NULL COMMENT '배송요일',
	  `dlv_type` enum('N','Y') NOT NULL default 'N' COMMENT '배송기간',
	  `dlv_ea` varchar(30) NOT NULL COMMENT '배송기간-특정회차',
	  `dlv_end` int(10) NOT NULL COMMENT '배송기간-특정기간지정',
	  `sale_use` enum('N','Y') NOT NULL default 'N' COMMENT '할인사용여부',
	  `sale_ea` int(10) NOT NULL COMMENT '회차 이상',
	  `sale_percent` int(10) NOT NULL COMMENT '할인율',
	  `default` enum('N','Y') NOT NULL default 'N' COMMENT '기본세트',
	  `admin_id` varchar(100) null default '' COMMENT '관리자 아이디',
	  `reg_date` int(10) NOT NULL COMMENT '등록날짜',
	  PRIMARY KEY (`no`),
	  KEY `name` (`name`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
	";

	$tbl_schema['sbscr_set_product'] = "
	CREATE TABLE IF NOT EXISTS `$tbl[sbscr_set_product]` (
	  `no` int(10) NOT NULL AUTO_INCREMENT,
	  `setno` int(10) NOT NULL COMMENT '세트번호',
	  `pno` int(10) NOT NULL COMMENT '상품번호',
	  `use` enum('N','Y') NOT NULL default 'N' COMMENT '정기배송 사용여부',
	  `reg_date` int(10) NOT NULL COMMENT '등록날짜',
	  PRIMARY KEY (`no`),
	  KEY `setno` (`setno`),
	  KEY `pno` (`pno`),
	  KEY `use` (`use`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
	";

	$tbl_schema['sbscr_holiday'] = "
	CREATE TABLE IF NOT EXISTS `wm_sbscr_holiday` (
	  `no` int(10) NOT NULL AUTO_INCREMENT,
	  `is_holiday` enum('Y','N') NOT NULL DEFAULT 'N',
	  `timestamp` int(10) NOT NULL DEFAULT 0,
	  `datestring` date NOT NULL,
	  `description` varchar(200) DEFAULT NULL,
	  `admin_id` varchar(50) NOT NULL DEFAULT '0',
	  `mod_date` datetime NOT NULL,
	  PRIMARY KEY (`no`),
	  UNIQUE KEY `datestring` (`datestring`),
	  UNIQUE KEY `timestamp` (`timestamp`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
	";

	$tbl_schema['sbscr_cart'] = "
	CREATE TABLE IF NOT EXISTS `wm_sbscr_cart` (
	  `no` int(10) NOT NULL AUTO_INCREMENT,
	  `pno` int(10) NOT NULL DEFAULT 0,
	  `option` varchar(1000) DEFAULT NULL,
	  `option_prc` varchar(1000) DEFAULT NULL,
	  `option_idx` varchar(1000) DEFAULT NULL,
	  `complex_no` int(10) NOT NULL DEFAULT 0,
	  `buy_ea` mediumint(5) NOT NULL DEFAULT '0',
	  `date_list` text NOT NULL COMMENT '배송리스트',
	  `dlv_cnt` mediumint(5) NOT NULL DEFAULT '0' COMMENT '배송회차',
	  `period` varchar(20) NOT NULL DEFAULT '0' COMMENT '주기',
	  `week` varchar(20) NOT NULL DEFAULT '0' COMMENT '요일',
	  `start_date` int(10) NOT NULL DEFAULT '0' COMMENT '시작날짜',
	  `end_date` int(10) NOT NULL DEFAULT '0' COMMENT '끝날짜',
	  `member_no` int(11) NOT NULL default '0',
	  `guest_no` varchar(32) NOT NULL DEFAULT '',
	  `reg_date` int(10) NOT NULL DEFAULT 0,
	  PRIMARY KEY (`no`),
	  KEY `pno` (`pno`),
	  KEY `guest_no` (`guest_no`),
	  KEY `member_no` (`member_no`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
	";

?>