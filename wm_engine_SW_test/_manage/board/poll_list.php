<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  설문조사 관리
	' +----------------------------------------------------------------------------------------------+*/

	$sql="select * from `$tbl[poll_config]` order by `no` desc";

// 페이징 설정
	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page<=1) $page=1;
	if(!$row) $row=20;
	$block=20;

	$NumTotalRec = $pdo->row("select count(*) from `$tbl[poll_config]`");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

?>
<form name="prdFrm" method="get" action="./">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="">
	<div class="box_title first">
		<h2 class="title">설문조사 관리</h2>
	</div>
	<table class="tbl_col">
		<caption class="hidden">설문조사 관리</caption>
		<colgroup>
			<col style="width:60px">
			<col style="width:60px">
			<col>
			<col style="width:80px">
			<col style="width:80px">
			<col style="width:200px">
			<col style="width:120px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.prdFrm.check_pno,this.checked)"></th>
				<th scope="col">번호</th>
				<th scope="col">제목</th>
				<th scope="col">문항</th>
				<th scope="col">총참여자</th>
				<th scope="col">기간</th>
				<th scope="col">등록일</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$today=date("Y-m-d");
                foreach ($res as $data) {
					$rclass=($idx%2==0) ? "tcol2" : "tcol3";
					if($data[sdate] <= $today && $data[fdate] >= $today) $data[title]="<b>".$data[title]."</b>";
			?>
			<tr>
				<td><input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data[no]?>"></td>
				<td><?=$idx?></td>
				<td class="left"><a href="./?body=board@poll_frm&no=<?=$data[no]?>"><?=stripslashes($data[title])?></a></td>
				<td><?=$data[total_item]?></td>
				<td><?=number_format($data[total_vote])?></td>
				<td><?=$data[sdate]?> ~ <?=$data[fdate]?></td>
				<td title="<?=date("Y/m/d H:i:s",$data[reg_date])?>"><?=date("Y/m/d",$data[reg_date])?></td>
			</tr>
			<?php
					$idx--;
				}
			?>
		</tbody>
	</table>
	<div class="box_bottom">
		<?=$pg_res?>
		<div class="left">
			<span class="box_btn blue"><input type="button" value="설문추가" onclick="location.href='./?body=board@poll_frm'"></span>
			<span class="box_btn gray"><input type="button" value="선택삭제" onclick="delPoll();"></span>
		</div>
	</div>
</form>

<script language="JavaScript">
	function delPoll(){
		f=document.prdFrm;
		if(!checkCB(f.check_pno, "삭제하실 설문을")) return;
		if (!confirm('선택한 설문을 삭제하시겠습니까?')) {
			return;
		}
		f.method='post';
		f.target='hidden<?=$now?>';
		f.body.value='board@poll.exe';
		f.exec.value='delete';
		f.submit();
	}
</script>