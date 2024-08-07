<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원 - 블랙리스트 변경내역
	' +----------------------------------------------------------------------------------------------+*/

	$sql="select * from `$tbl[blacklist_log]` where `member_id`='$mid' order by `no` desc";

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	if($page<=1) $page=1;
	$row=20;
	$block=10;

	$NumTotalRec = $pdo->row("select count(*) from {$tbl['blacklist_log']} where member_id='$mid'");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

?>
<table class="tbl_col tbl_col_bottom">
	<caption class="hidden">블랙리스트 변경내역</caption>
	<colgroup>
		<col style="width:50px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">순서</th>
			<th scope="col">일시</th>
			<th scope="col">상태</th>
			<th scope="col">변경사유</th>
			<th scope="col">처리자 ID</th>
		</tr>
	</thead>
	<tbody>
		<?php
            foreach ($res as $data) {
				$blacklist=($data[blacklist]!='0') ? "블랙리스트 회원" : "일반회원";
		?>
		<tr>
			<td><?=$idx?></td>
			<td><?=date("Y/m/d H:i:s", $data[log_date])?></td>
			<td><?=$blacklist?></td>
			<td><?=$data[black_reason]?></td>
			<td><?=$data[admin_id]?></td>
		</tr>
		<?
			$idx--;
			}
		?>
	</tbody>
</table>
<div class="pop_bottom"><?=$pg_res?></div>