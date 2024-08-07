<?php
/**
 * 크리마 API 연동 실행 파일
 * @박연경 <pyk87@wisa.co.kr>
 * @date 2016-01-20
 */

set_time_limit(0);
ini_set('memory_limit', -1);

include_once $engine_dir."/_engine/include/common.lib.php";
include $engine_dir."/_engine/include/rest_api.class.php";
include "class/crema.api.php";
include_once $engine_dir.'/_engine/include/JSON.php';

$crema = new cremaAPI();

if(!isTable('crema_matching')) {
	$sql="CREATE TABLE `crema_matching` (
	  `type` enum('o','op','p','r','c','rc','g') NOT NULL COMMENT 'o:주문,op:주문상품,p:상품,c:카테고리,r:리뷰, rc:리뷰댓글, g:회원등급',
	  `w_key` varchar(50) NOT NULL COMMENT '키값(주문번호,pk값등등)',
	  `crema_key` int(11) NOT NULL COMMENT '크리마 데이터 키값',
	  `reg_date` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '등록일',
	  KEY `type` (`type`),
	  KEY `w_key` (`w_key`),
	  KEY `crema_key` (`crema_key`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='크리마 매칭테이블'";
	$pdo->query($sql);
}

if(isTable('crema_matching')) {

	//전체 카테고리 전송
	$sql = "SELECT * FROM ".$tbl['category']." WHERE hidden='N' ORDER BY level, no";
	$rs = $pdo->iterator($sql);
    foreach ($rs as $row) {
		$crema->createCategory($row['no']);
	}

	//전체 회원등급 전송
	$sql = "SELECT * FROM ".$tbl['member_group']." WHERE use_group='Y' ORDER BY no";
	$rs = $pdo->iterator($sql);

    foreach ($rs as $row) {
		$crema->createGrade($row['no']);
	}

	//전체 상품 전송
	$sql = "SELECT * FROM ".$tbl['product']." WHERE wm_sc=0 order by no";
	$rs = $pdo->iterator($sql);

    foreach ($rs as $row) {
		if($row['no'] && $row['wm_sc']==0) {
			$crema->createProduct($row['no']);
		}
	}

	//주문(최근 1개월) 전송
	$date1s = strtotime("-1 month"); //1개월전
	$date1e = $now;//오늘

	$sql = "SELECT ono, member_no FROM wm_order where date1 between $date1s and $date1e and stat between 2 and 5 order by no";
	$rs = $pdo->iterator($sql);

    foreach ($rs as $row) {
		if($row['member_no']>0) {
			$crema->createOrder($row['ono']);
			$sql = "SELECT no FROM ".$tbl['order_product']." WHERE ono = '".$row['ono']."'";
			$rs2 = $pdo->iterator($sql);

		    foreach ($rs2 as $row2) {
				$crema->createOrderProduct($row2['no']);
			}
		}
	}

	//전체 리뷰 전송
	$sql = "SELECT * FROM wm_review WHERE stat>1 and member_no>0 order by no";
	$rs = $pdo->iterator($sql);

    foreach ($rs as $row) {
		$crema->createReview($row['no']);
		$sql = "SELECT no FROM ".$tbl['review_comment']." WHERE ref = '".$row['no']."'";
		$rs2 = $pdo->iterator($sql);

        foreach ($rs2 as $row2) {
			$crema->createRComment($row2['no']);
		}
	}

	msg('데이터 전송이 완료 되었습니다.');
}else {
	msg('잠시 후 다시 시도해주세요.');
}
?>