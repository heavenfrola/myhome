<?PHP

	printAjaxHeader();

	$_search_type['promotion_nm'] = '프로모션 기획전명';
	$_search_type['pgrp_nm'] = '프로모션 상품그룹명';

	$_nowdate = date("Y-m-d H:i:s", $now);

	$search_type = $_GET['search_type'];
	$search_str = addslashes(trim($_GET['search_str']));
	if($_search_type[$search_type] && $search_str != "") {
		$_tmp = explode(',', $search_str);
		$w .= " and `$search_type` like '%$search_str%'";
	}

	if($_GET['use_yn']) {
		$use_yn = ($_GET['use_yn'] == 'Y') ? 'Y' : 'N';
		$w .= " and `use_yn` = '$use_yn'";
	}
	
	if($_GET['stat']) {
		switch($_GET['stat']) {
			case '2' : // 대기
				$w .= " and period_type='Y' and date_start>'$_nowdate'";
			break;
			case '3' : // 진행중
				$w .= " and (period_type='Y' and date_start<='$_nowdate' and date_end>='$_nowdate') or period_type='N'";
			break;
			case '4' : // 종료
				$w .= " and period_type='Y' and date_end<'$_nowdate'";
			break;
		}
	}

	$sql = "select a.*,b.*,c.*,a.reg_date as pgrp_date, a.no as old_pgrp_no, a.admin_id as pgrp_admin_id, a.admin_no as pgrp_admin_no from $tbl[promotion_pgrp_list] as a left join $tbl[promotion_link] as c on a.no=c.pgrp_no left join $tbl[promotion_list] as b on c.prm_no=b.no  where 1 $w group by old_pgrp_no order by old_pgrp_no desc";

	// 페이징
	include_once $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if($row > 30) $row = 30;
	$block=5;

	$NumTotalRec = $pdo->rowCount($sql);

	if($delete_btn=="Y") {
		if(!$row) $row = 20;
		$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
		$PagingInstance->addQueryString(makeQueryString('page'));
		$PagingResult = $PagingInstance->result('admin');
	}else {
		if(!$row) $row = 5;
		$PagingInstance = new Paging($NumTotalRec, $page, $row, $block, '', 'prm_prd_search');
		$PagingInstance->addQueryString(makeQueryString('page'));
		$PagingResult = $PagingInstance->result('ajax_admin');
	}
	$sql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

	$chk_frm = ($delete_btn=="Y") ? "prmlFrm":"callFrm";

?>
<table class="tbl_col" id="call_data_list">
	<caption class="hidden">프로모션 리스트</caption>
	<colgroup>
		<col style="width:50px">
		<col style="width:50px">
		<col>
		<col style="width:330px">
		<col style="width:80px">
		<col style="width:130px">
		<?if($delete_btn=="Y") {?>
			<col style="width:80px">
		<?}?>
	</colgroup>
	<thead>
		<tr>
			<th scope="col"><input type="checkbox" onclick="checkAll(document.<?=$chk_frm?>.check_pgrpno,this.checked)"></th>
			<th scope="col">번호</th>
			<th scope="col">프로모션 상품그룹명</th>
			<th scope="col">프로모션 기획전/상태</th>
			<th scope="col">작성자</th>
			<th scope="col">등록일시</th>
			<?if($delete_btn=="Y") {?>
				<th scope="col">관리</th>
			<?}?>
		</tr>
	</thead>
	<tbody>
    <?php
        foreach ($res as $data) {
			$res2 = $pdo->iterator("select * from $tbl[promotion_link] as c left join $tbl[promotion_list] as b on c.prm_no=b.no where pgrp_no='$data[old_pgrp_no]'");
			$p_total = $res2->rowCount();
			if($p_total == 0) $p_total = 1;
			$admin_name = stripslashes($pdo->row("select name from $tbl[mng] where no='$data[pgrp_admin_no]'"));
			$datetime = new DateTime($data['pgrp_date']);
			$reg_date = $datetime->format('Y-m-d H:i');
?>
			<tr id="<?=$data['old_pgrp_no']?>" >
				<td><input type="checkbox" name="check_pgrpno[]" id="check_pgrpno" value="<?=$data['old_pgrp_no']?>"></td>
				<td><?=$idx?></td>
				<td class="left" ><a onclick="searchGroup(this, '<?=$data['old_pgrp_no']?>')" class="p_cursor"><?=stripslashes($data['pgrp_nm'])?></a></td>
				<td class="left" >
					<table class="tbl_inner full line">
						<caption class="hidden">프로모션 기획전명/상태</caption>
						<colgroup>
							<col>
							<col style="width:80px">
						</colgroup>
						<tbody>
						<?php
						$i=1;
                        foreach ($res2 as $ldata) {
							$stat_text = "";
							if($ldata['period_type']=='Y' && $ldata['date_start']>$_nowdate) {
								$stat_text = "대기";
							}else if(($ldata['period_type']=='Y' && $ldata['date_start']<=$_nowdate && $ldata['date_end']>=$_nowdate) || $ldata['period_type']=='N') {
								$stat_text = "진행중";
							}else if($ldata['period_type']=='Y' && $ldata['date_end']<$_nowdate) {
								$stat_text = "종료";
							}
							$ldata['promotion_nm'] = strip_tags(stripslashes($ldata['promotion_nm']));
						?>
							<tr>
								<td class="left"><?=$ldata['promotion_nm']?></td>
								<td><?=$stat_text?></td>
							</tr>
						<?}?>
						</tbody>
					</table>
				</td>
				<td><?if($admin_name){?><?=$admin_name?><?}?></td>
				<td><?=$reg_date?></td>
			<?if($delete_btn=="Y") {?>
				<td><span class="box_btn_s"><input type="button" value="수정" onclick="searchGroup(this, '<?=$data['old_pgrp_no']?>');"></span></td>
			<?}?>
			</tr>
<?PHP
		$idx--;
		}
?>
	</tbody>
</table>
<div class="box_bottom">
	<?=$pg_res?>
</div>