<?PHP

	$_search_str = addslashes(trim($_GET['search_str']));
	$_search_key = addslashes($_GET['search_key']);
    $all_date = $_GET['all_date'];
	if($_search_str) {
		if($_search_key == 'name') $w .=" and m2.name like '%$_search_str%'";
		elseif($_search_key == 'member_id') $w .=" and m2.member_id like '%$_search_str%'";
		elseif($_search_key && $_search_str) $w .= " and m.$_search_key like '%$_search_str%'";
	}

	$_search_key = array(
		'name' =>'회원이름',
		'member_id' =>'회원아이디',
		'content' =>'내용',
		'admin_id' =>'작성자 아이디'
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
	$sort = numberOnly($_GET['sort']);
	if(isset($_GET['sort']) == false) $sort = 1;
	$_sort = $sortarr[$sort];
	for($i = 1; $i <= $sortcnt; $i++) {
		$var1 = ($i-1) * 2;
		$var2 = $var1 + 1;
		${'arrowcolor'.$i} = ($sort == $var1 || $sort == $var2) ? 'blue' : 'gray';
		${'arrowdir'.$i} = ($sort == $var2) ? 'down' : 'up';
		${'sort'.$i} = ($sort == $var1) ? $qs_without_sort.'&sort='.$var2 : $qs_without_sort.'&sort='.$var1;
	}

	$sql = "select m.no, m2.name, m.admin_id, m.content, m2.member_id, m2.no as member_no, m.reg_date, m.type
				from `$tbl[order_memo]` m inner join `$tbl[member]` m2 on m.ono=m2.member_id where type=2 $w order by $_sort";
	$total = "select count(*) from `$tbl[order_memo]` m inner join `$tbl[member]` m2 on m.ono=m2.member_id where type=2 $w";

	// 페이징 설정
	include_once $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	if($page<=1) $page=1;
	$row=20;
	$block=10;

	$NumTotalRec = $pdo->row($total);
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pageRes=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1))+1;

?>
<form id="searchFrm" method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<div class="box_title first">
		<h2 class="title">회원 메모 조회</h2>
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
			<caption class="hidden">회원 메모 조회 검색</caption>
			<colgroup>
				<col style="width:15%">
			</colgroup>
			<tr>
				<th scope="row">작성일</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체기간</label>
					<input type="text" name="start_date" value="<?=$start_date?>" size="10" class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">
					<?
						$date_type=array("오늘" => "-0 day", "1주일" => "-1 week", "15일" => "-15 day", "1개월" => "-1 month", "3개월" => "-3 month");
						foreach($date_type as $key => $val) {
							$_btn_class=($val && !$all_date && $finish_date == date("Y-m-d", $now) && $start_date == date("Y-m-d", strtotime($val))) ? "blue" : "gray";
							$_sdate=$_fdate=null;
							if($val) {
								$_sdate=date("Y-m-d", strtotime($val));
								$_fdate=date("Y-m-d", $now);
							}
					?>
					<span class="box_btn_s <?=$_btn_class?> strong"><input type="button" value="<?=$key?>" onclick="setSearchDatee(this.form, 'start_date', 'finish_date', '<?=$_sdate?>', '<?=$_fdate?>', '<?=$_GET['body']?>');"></span>
					<?
						}
					?>
					<script type="text/javascript">
						searchDate(document.getElementById('searchFrm'));
					</script>
				</td>
			</tr>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET['body']?>'"></span>
		</div>
	</div>
</form>
<form id="memoFrm" target="hidden<?=$now?>" method="post" action="<?=$_SERVER['PHP_SELF']?>">
<input type="hidden" name="body" value="order@order_memo.exe">
<input type="hidden" name="exec">
	<div class="box_title">
		<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 메모가 검색되었습니다.
	</div>
	<div class="box_sort">
		<label class="p_cursor"><input type="checkbox" onclick="$(':checkbox[name^=mno]').prop('checked',this.checked);"> 전체선택</label> |
		<a href="<?=$sort1?>">작성일 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir1?>.gif" class="arrow <?=$arrowcolor1?>"></a>
	</div>
	<div class="box_middle">
		<ul class="list_memo">
			<?php
                foreach ($res as $data) {
					$disabled = ($admin['admin_id'] != $data['admin_id'] && $admin['level'] > 2) ? 'disabled' : '';
					$idx--;

                    $neko_id = 'memo_'.$data['type'].'_'.$data['no'];
                    $files = $pdo->row("select count(*) from {$tbl['neko']} where neko_id='$neko_id'");

                    // 리스트에서 개인정보 마스킹
                    $data['member_id_v'] = $data['member_id'];
                    if ($scfg->comp('use_member_list_protect', 'Y') == true) {
                        $data['name'] = strMask($data['name'], 2, '＊');
                        $data['member_id_v'] = strMask($data['member_id'], 5, '***');
                    }
			?>
			<li>
				<p class="check">
					<!-- <?=$idx?> -->
					<input type="checkbox" name="mno[]" id="mno" value="<?=$data['no']?>" <?=$disabled?>>
					<a href="#" onClick="viewMember('<?=$data['member_no']?>', '<?=$data['member_id']?>'); return false"><strong><?=$data['name']?></strong> (<?=$data['member_id_v']?>) <img src="<?=$engine_url?>/_manage/image/shortcut2.gif" alt="새창"></a>
				</p>
				<div class="frame">
					<div class="contents">
						<a href="#" onClick="viewMember('<?=$data['member_no']?>', '<?=$data['member_id']?>', 'memo'); return false"><?=stripslashes(nl2br($data['content']))?></a>
					</div>
				</div>
				<div class="info">
					<p>작성자 : <?=$data['admin_id']?></p>
					<p>작성일 : <?=date("Y/m/d H:i",$data['reg_date'])?></p>
                    <p>첨부파일 : <?=$files?></p>
				</div>
			</li>
			<?}?>
		</ul>
	</div>
	<div class="box_bottom top_line">
		<?=$pageRes?>
		<?if($NumTotalRec){?>
		<span class="box_btn gray left_area"><input type="button" value="선택 삭제" onclick="deleteMemo(this);"></span>
		<?}?>
	</div>
</form>

<script type="text/javascript">
	function deleteMemo() {
		var f = document.getElementById('memoFrm');
		if(!checkCB(document.getElementsByName('mno[]'),"삭제하실 메모를 선택해주세요.")) return;
		if(!confirm('선택한 메모를 삭제하시겠습니까?')) return;
		f.exec.value = "delete";
		f.submit();
	}
</script>