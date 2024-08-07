<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원 - 접속로그
	' +----------------------------------------------------------------------------------------------+*/

	$sql="select * from `$tbl[member_log]` where `member_id`='$amember[member_id]' order by `log_date` desc";

	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	if($page<=1) $page=1;
	$row=10;
	$block=10;

	$NumTotalRec = $pdo->row("select count(*) from {$tbl['member_log']} where member_id='{$amember['member_id']}'");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$res = $pdo->iterator($sql);
	$idx=$NumTotalRec-($row*($page-1));

?>
<table class="tbl_col">
	<caption class="hidden">접속로그</caption>
	<colgroup>
		<col style="width:50px;">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">순서</th>
			<th scope="col">일시</th>
			<th scope="col">결과</th>
			<th scope="col">IP</th>
		</tr>
	</thead>
	<tbody>
        <?php foreach ($res as $data) {?>
		<tr>
			<td><?=$idx?></td>
			<td><?=date("Y/m/d H:i:s",$data[log_date])?></td>
			<td><?=$_login_result[$data[login_result]]?></td>
			<td><a href="http://www.apnic.net/apnic-bin/whois.pl?searchtext=<?=$data[ip]?>" target="_blank"><?=$data[ip]?></a></td>
		</tr>
		<?$idx--;}?>
	</tbody>
</table>
<div class="pop_bottom"><?=$pg_res?></div>