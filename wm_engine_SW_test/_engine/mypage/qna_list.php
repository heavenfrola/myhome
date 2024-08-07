<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  My qna 리스트
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";

	memberOnly(1,"");

	$addq="and `member_no`='".$member[no]."'";
	$sql="select * from `".$tbl[qna]."` where 1 ".$addq." order by `no` desc";
	$contentRes = $pdo->iterator($sql);
	$NumTotalRec=$qna_idx=$pdo->row("select count(*) from `".$tbl['qna']."` where 1 ".$addq);

	$GLOBALS[total_content]=$NumTotalRec;

	function contentList(){
		global $tbl,$qna_idx, $contentRes;

		$data = $contentRes->current();
        $contentRes->next();
		if($data == false) return false;

		$data=qnaOneData($data);
		$data[qna_idx]=$qna_idx;
		$qna_idx--;
		return $data;
	}

	common_header();
?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/R2Na/R2Na.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/smartEditor/js/HuskyEZCreator.js"></script>
<?
	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>