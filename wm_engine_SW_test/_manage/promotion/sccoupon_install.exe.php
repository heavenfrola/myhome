<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  소셜쿠폰 db 셋팅
	' +----------------------------------------------------------------------------------------------+*/

	if(!defined("sccoupon")) return;
	if($cfg['sccoupon']) return;

	$sql="
	CREATE TABLE IF NOT EXISTS `wm_social_coupon_info` (
	  `no` int(11) unsigned not null auto_increment,
	  `is_type` char(1) not null default '1' comment '1:적립금 2:쿠폰',
	  `name` varchar(200) not null default '' comment '쿠폰명',
	  `milage_prc` int(11) not null default 0 comment '교환적립금',
	  `cno` int not null default 0 comment '교환쿠폰번호',
	  `date_type` char(1) not null default '1' comment '날짜구분 1:무제한 2:제한',
	  `start_date` varchar(10) not null default '' comment '시작일',
	  `finish_date` varchar(10) not null default '' comment '종료일',
	  `reg_date` int(11) not null default '0' comment '등록일자',
	  `memo` text not null default '' comment '관리자메모',
	  PRIMARY KEY  (`no`),
	  KEY `is_type` (`is_type`),
	  KEY `date_type` (`date_type`,`start_date`,`finish_date`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=euckr COMMENT='소셜쿠폰정보';
	";
	$r=$pdo->query($sql);

	$sql="
	CREATE TABLE IF NOT EXISTS `wm_social_coupon_code` (
	  `no` int(11) unsigned not null auto_increment,
	  `scno` int(11) not null default 0 comment '소셜쿠폰정보번호',
	  `code` varchar(50) not null default '' comment '쿠폰코드',
	  `use` char(1) not null default '1' comment '1:사용안함 2:사용',
	  `reg_date` int(11) NOT NULL default '0' COMMENT '생성일자',
	  PRIMARY KEY  (`no`),
	  KEY `scno` (`scno`),
	  KEY `code` (`code`),
	  KEY `use` (`use`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=euckr COMMENT='소셜쿠폰개별코드';
	";
	$pdo->query($sql);

	$sql="
	CREATE TABLE IF NOT EXISTS `wm_social_coupon_use` (
	  `no` int(11) unsigned not null auto_increment,
	  `scno` int(11) not null default 0 comment '소셜쿠폰정보번호',
	  `code` varchar(50) not null default '' comment '소셜쿠폰코드',
	  `milage_prc` int(11) not null default 0 comment '교환적립금',
	  `cno` int not null default 0 comment '교환쿠폰번호',
	  `member_no` int(11) not null default 0 comment '회원번호',
	  `member_id` varchar(20) not null default '' comment '회원아이디',
	  `member_name` varchar(10) not null default '' comment '회원이름',
	  `reg_date` int(11) NOT NULL default '0' COMMENT '교환일자',
	  PRIMARY KEY  (`no`),
	  KEY `scno` (`scno`),
	  KEY `code` (`code`),
	  KEY `cno` (`cno`),
	  KEY `member_no` (`member_no`, `member_id`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=euckr COMMENT='소셜쿠폰사용내역';
	";
	$pdo->query($sql);

	$sql="
	CREATE TABLE IF NOT EXISTS `wm_social_coupon_log` (
	  `no` int(11) unsigned not null auto_increment,
	  `type` char(1) not null default '1' comment '1:쿠폰정보 2:쿠폰코드',
	  `stat` char(1) not null default '1' comment '실행구분',
	  `scno` int(11) not null default 0 comment '소셜쿠폰정보번호',
	  `code` varchar(50) not null default '' comment '소셜쿠폰코드',
	  `name` varchar(100) not null default '' comment '쿠폰명',
	  `admin_id` varchar(20) not null default '' comment '관리자아이디',
	  `admin_no` int(11) not null default 0 comment '관리자번호',
	  `content` text not null default '' comment '실행결과',
	  `ip` varchar(15) not null default '' comment '실행아이피',
	  `reg_date` int(11) NOT NULL default '0' COMMENT '실행일자',
	  PRIMARY KEY  (`no`),
	  KEY `type` (`type`),
	  KEY `stat` (`stat`),
	  KEY `scno` (`scno`),
	  KEY `code` (`code`)
	) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=euckr COMMENT='소셜쿠폰실행로그';
	";
	$pdo->query($sql);

?>
<form name="sccouponFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="sccoupon" value="1">
	<input type="hidden" name="no_reload_config" value="1">
</form>
<script type="text/javascript">
	document.sccouponFrm.submit();
</script>
