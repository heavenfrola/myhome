<?PHP

	$_search_str = addslashes(trim($_GET['search_str']));
	$_search_key = addslashes($_GET['search_key']);
    $all_date = $_GET['all_date'];
	$admin_id = addslashes($_GET['admin_id']);
	if($_GET['admin_id']) $w .= "and m.`admin_id` = '$admin_id'";
	if($_search_key == 'name') $w .=" and p.`name` like '%$_search_str%'";
	elseif($_search_key == 'code') $w .=" and p.`code` like '%$_search_str%'";
	elseif($_search_key == 'hash') $w .=" and p.`hash` like '%$_search_str%'";
	elseif($_search_key && $_search_str) $w .= " and m.`$_search_key` like '%$_search_str%'";

	$_search_key = array(
		'content' => '내용',
		'name' => '상품명',
		'code' => '상품코드',
		'hash' => '시스템코드'
	);

	// 작성일
	$start_date = $_GET['start_date'];
	$finish_date = $_GET['finish_date'];
	if(!$start_date) $start_date = date('Y-m-d', strtotime('-1 months'));
	if(!$finish_date) $finish_date = date('Y-m-d');
	if($all_date != 'Y') {
		$_start_date = strtotime($start_date);
		$_finish_date = strtotime($finish_date)+86399;
		$w .= " and m.reg_date between $_start_date and $_finish_date";
	}


	// 정렬
	$qs_without_sort = '?'.preg_replace('/&sort=[^&]+/', '', $_SERVER['QUERY_STRING']);

	$sortarr = array("m.`reg_date` asc", "m.`reg_date` desc");
	$sortcnt = count($sortarr)/2;
	if($sort == null) $sort = 1;
	$_sort = $sortarr[$sort];
	for($i = 1; $i <= $sortcnt; $i++) {
		$var1 = ($i-1) * 2;
		$var2 = $var1 + 1;
		${'arrowcolor'.$i} = ($sort == $var1 || $sort == $var2) ? 'blue' : 'gray';
		${'arrowdir'.$i} = ($sort == $var2) ? 'down' : 'up';
		${'sort'.$i} = ($sort == $var1) ? $qs_without_sort.'&sort='.$var2 : $qs_without_sort.'&sort='.$var1;
	}

	$sql = "select m.`no`, m.`ono`, p.`name`, m.`admin_id`, m.`content`, p.`code`, p.`hash`, m.`reg_date`, p.`no` as pno
				from `$tbl[order_memo]` m inner join `$tbl[product]` p on m.ono = p.no where type=3 $w order by $_sort";
	$total = "select count(*) from `$tbl[order_memo]` m inner join `$tbl[product]` p on m.ono = p.no where type=3 $w";

	// 페이징 설정
	include_once $engine_dir."/_engine/include/paging.php";

	foreach($_GET as $key=>$val) {
		if($key!="page") $QueryString.="&".$key."=".$val;
	}

	if($page<=1) $page=1;
	$row=20;
	$block=10;

	$NumTotalRec = $pdo->row($total);
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pageRes=$PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

?>
<!-- 검색 폼 -->
<form id="prdFrm" method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<div class="box_title first">
		<h2 class="title">관리자메모조회</h2>
	</div>
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow full">
					<div class="select">
						<?=selectArray($_search_key,"search_key",2,"",$search_key)?>
					</div>
					<div class="area_input">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
					</div>
				</div>
			</div>
		</div>
		<table class="tbl_search">
			<caption class="hidden">관리자메모조회</caption>
			<colgroup>
				<col style="width:15%">
			</colgroup>
			<tr>
				<th scope="row">작성일</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체기간</label>
					<input type="text" name="start_date" value="<?=$start_date?>" size="10" class="input datepicker">
					~
					<input type="text" name="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">
					<?php
					$date_type=array("오늘" => "-0 day", "1주일" => "-1 week", "15일" => "-15 day", "1개월" => "-1 month", "3개월" => "-3 month");
					foreach($date_type as $key => $val) {

						$_btn_class=($val && !$all_date && $finish_date == date("Y-m-d", $now) && $start_date == date("Y-m-d", strtotime($val))) ? "blue" : "gray";
						$_sdate=$_fdate=null;
						if($val) {
							$_sdate=date("Y-m-d", strtotime($val));
							$_fdate=date("Y-m-d", $now);
						}
						?>
						<span class="box_btn_s <?=$_btn_class?>"><input type="button" value="<?=$key?>" onclick="setSearchDatee(this.form, 'start_date', 'finish_date', '<?=$_sdate?>', '<?=$_fdate?>', '<?=$_GET['body']?>');"></span>
						<?php
					}
					?>
					<script type="text/javascript">
						searchDate(document.getElementById('prdFrm'));
					</script>
				</td>
			</tr>
			<tr>
			<th scope="row">작성자</th>
				<td>
					<input type="text" name="admin_id" value="<?=$admin_id?>" class="input" size="12" readonly onclick="msearch.open();">
					<span class="box_btn_s"><input type="button" value="찾기" onclick="msearch.open()"></span>
					<span class="box_btn_s"><input type="button" value="검색취소" onclick="setAddr()"></span>
				</td>
			</tr>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
</form>
<!-- //검색 폼 -->
<!-- 검색 테이블 -->
<form id="memoFrm" target="hidden<?=$now?>" method="post" action="<?=$_SERVER['PHP_SELF']?>">
	<input type="hidden" name="body" value="order@order_memo.exe">
	<input type="hidden" name="exec">
	<div class="box_title">
		<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 메모가 검색되었습니다.
	</div>
	<div class="box_middle left">
		<ul class="list_msg">
			<li>상품에 관리자가 작성한 메모를 조회할 수 있습니다.</li>
			<li>관리자 메모는 상품관리에서 작성할 수 있습니다.</li>
		</ul>
	</div>
	<div class="box_sort">
		<label class="p_cursor"><input type="checkbox" onclick="$(':checkbox[name^=mno]').attr('checked', this.checked)"> 전체선택</label> |
		<a href="<?=$sort1?>">작성일 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir1?>.gif" class="arrow <?=$arrowcolor1?>"></a>
	</div>
	<div class="box_middle">
		<ul class="list_memo">
			<?php
                foreach ($res as $data) {
					$content=cnvTip($data['content'],500);
					$title=cnvTip($data['title'],500);
					$disabled = ($admin['admin_id'] != $data['admin_id'] && $admin['level'] > 2) ? 'disabled' : '';
			?>
			<li>
				<p class="check">
					<!-- <?=$idx?> -->
					<input type="checkbox" name="mno[]" id="mno" value="<?=$data['no']?>" <?=$disabled?>>
					<a href="?body=product@product_register&pno=<?=$data['pno']?>" target="_blank"><strong><?=cutStr(stripslashes($data['name']),30)?></strong> <img src="<?=$engine_url?>/_manage/image/shortcut2.gif" alt="새창"></a>
				</p>
				<div class="frame">
					<div class="contents">
						<a href="?body=product@product_register&pno=<?=$data['pno']?>" target="_blank"><?=stripslashes($data['content'])?></a>
					</div>
				</div>
				<div class="info">
					<p>작성자 : <?=$data['admin_id']?></p>
					<p>작성일 : <?=date("Y/m/d H:i",$data['reg_date'])?></p>
				</div>
			</li>
			<?php
				$idx--;
				}
			?>
		</ul>
	</div>
	<div class="box_bottom top_line">
		<?=$pageRes?>
		<?php if ($NumTotalRec) { ?>
		<div class="left_area">
			<span class="box_btn"><input type="button" value="선택 삭제" onclick="deleteMemo(this);"></span>
		</div>
		<?php } ?>
	</div>
</form>
<!-- //검색 테이블 -->
<script type="text/javascript">
	function deleteMemo() {
		var f = document.getElementById('memoFrm');
		if(!checkCB(document.getElementsByName('mno[]'),"삭제하실 메모를")) return;
		if(!confirm('선택한 메모를 삭제하시겠습니까?')) return;
		f.exec.value="delete";
		f.submit();
	}

	function setAddr(json) {
		$(':input[name=admin_id]').val('');
	}

	layerWindow.prototype.msel = function(json) {
		$(':input[name=admin_id]').val(json.admin_id);
		this.close();
	}

	var msearch = new layerWindow('intra@admin_inc.exe');

</script>