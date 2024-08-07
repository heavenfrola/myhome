<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  로봇 변경이력
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	$sql = "select * from {$tbl['robots_log']} order by no desc";

	// 페이징
	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 3;
	if($row > 100) $row = 100;
	$block=10;

	$NumTotalRec = $pdo->row("select count(*) from {$tbl['robots_log']}");

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult = $PagingInstance->result($pg_dsn);

	$sql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$robot_res = $pdo->iterator($sql);

	$pg_res = preg_replace('/href="([^"]+)"/', 'href="javascript:" onclick="robotcall.open(\'$1\')"', $pg_res);

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">변경이력</div>
	</div>
	<div id="popupContentArea">
		<dl>
			<dd>
				<table class="tbl_inner full line">
					<caption class="hidden">최근 변경이력</caption>
					<colgroup>
						<col style="width:50px;">
						<col>
						<col style="width:130px;">
						<col>
					</colgroup>
					<thead>
					<tr>
						<th scope="row">번호</th>
						<th scope="row">내용</th>
						<th scope="row">일시</th>
						<th scope="row">작성자</th>
					</tr>
					</thead>
					<tbody>
					<?php
						$robot_idx = 0;
						if($NumTotalRec>0) {
                        foreach ($robot_res as $robot_data) {
							$robot_idx++;
							$admin_name = $pdo->row("select name from {$tbl['mng']} where admin_id='{$robot_data['admin_id']}'");
							$admin_str = $admin_name."(".$robot_data['admin_id'].")";
					?>
							<tr>
								<td><?=$robot_idx?></td>
								<td class="left">
									<?=nl2br($robot_data['content'])?>
								</td>
								<td><?=date('Y/m/d H:i', $robot_data['reg_date'])?></td>
								<td><?=$admin_str?></td>
							</tr>
					<?php }
					}else {?>
						<tr>
							<td colspan="4">변경이력이 없습니다.</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
				<div class="box_bottom">
					<?=$pg_res?>
				</div>
				<div class="pop_bottom">
					<span class="box_btn blue"><input type="button" onclick="$('.layerPop').hide();" value="확인"></span>
				</div>
			</dd>
		</dl>
	</div>
</div>