<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원 - 예치금내역
	' +----------------------------------------------------------------------------------------------+*/
	if($cfg[emoney_use] != "Y") {

?>
<div class="box_middle">
	현재 예치금 설정이 되어 있지 않습니다
	<span class="box_btn_s"><a href="<?=$root_url?>/_manage/?body=config@emoney" target="_blank">예치금 설정</a></span>
</div>
<?
	return;
	}

	$milage_title[8] = str_replace($cfg['milage_name'], '예치금', $milage_title[8]);

?>
<!-- 예치금 지급, 반환 -->
<div id="emoneyDiv" style="display:none;">
	<?if($cfg[emoney_use] == "Y") {?>
	<form method="post" action="./" target="hidden<?=$now?>">
		<input type="hidden" name="body" value="member@member_update.exe">
		<input type="hidden" name="exec" value="emoney">
		<input type="hidden" name="mno" value="<?=$mno?>">
		<table class="tbl_row">
			<caption class="hidden">예치금 지급, 반환</caption>
			<colgroup>
				<col style="width:15%;">
				<col>
			</colgroup>
			<tr>
				<th scope="row">사유</th>
				<td class="left"><input type="text" name="mtitle" value="" class="input" size="13"></td>
			</tr>
			<tr>
				<th scope="row">예치금</th>
				<td class="left"><input type="text" name="mprc" value="" class="input" size="13"></td>
			</tr>
			<tr>
				<th scope="row">지급/반환</th>
				<td class="left">
					<label class="p_cursor"><input type="radio" name="exec2" value="+" checked> 지급</label>
					<label class="p_cursor"><input type="radio" name="exec2" value="-"> 반환</label>
				</td>
			</tr>
		</table>
		<div class="box_bottom">
			<span class="box_btn blue"><input type="button" value="적용" onClick="putMilage(this.form, 2);"></span>
			<span class="box_btn gray"><input type="button" value="닫기" onClick="layTgl2('emoneyDiv');"></span>
		</div>
	</form>
	<?} else {?>
	<div>
		<p>예치금이 기능이 설정되어있지 않습니다</p>
		<span class="box_btn_s blue"><a href="<?=$root_url?>/_manage/?body=config@emoney" target="_blank">예치금 설정하기</a></span>
	</div>
	<?}?>
</div>
<!-- //예치금 지급, 반환 -->
<table class="tbl_col">
	<caption class="hidden">적립금내역</caption>
	<colgroup>
		<col style="width:50px;">
		<col style="width:120px;">
		<col>
		<col span="4" style="width:100px;">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">순서</th>
			<th scope="col">날짜</th>
			<th scope="col">적요</th>
			<th scope="col">적립</th>
			<th scope="col">사용</th>
			<th scope="col">소계</th>
			<th scope="col">처리자</th>
		</tr>
	</thead>
	<tbody>
		<?php
			$sql="select * from `$tbl[emoney]` where `member_no`='$mno' $id_where order by `no` desc";
			// 페이징 설정
			include $engine_dir."/_engine/include/paging.php";

			$page = numberOnly($_GET['page']);
			if($page<=1) $page=1;
			$row=20;
			$block=10;

			$NumTotalRec = $pdo->row("select count(*) from {$tbl['emoney']} where member_no='$mno' $id_where");
			$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
			$PagingInstance->addQueryString(makeQueryString('page'));
			$PagingResult=$PagingInstance->result($pg_dsn);
			$sql.=$PagingResult[LimitQuery];

			$pg_res=$PagingResult[PageLink];
			$res = $pdo->iterator($sql);
			$idx=$NumTotalRec-($row*($page-1));

            foreach ($res as $data) {
				$rclass=($idx%2==0) ? "tcol2" : "tcol3";

				$data[mtitle]=$milage_title[$data[mtype]];
				if($data[title]) $data[mtitle].=" (".$data[title].")";

				$data[minus]=0;
				$data[plus]=0;
				if($data[ctype]=="+") $data[plus]="<b>".number_format($data[amount],$cfg['currency_decimal'])."</b>";
				else $data[minus]="<b>".number_format($data[amount],$cfg['currency_decimal'])."</b>";
		?>
		<tr>
			<td><?=$idx?></td>
			<td title="<?=date("Y/m/d H:i:s",$data[reg_date])?>"><?=date("Y/m/d",$data[reg_date])?></td>
			<td class="left <?=$rclass?>"><?=stripslashes($data[mtitle])?></td>
			<td>+<?=$data[plus]?> <?=$cfg['currency_type']?></td>
			<td>-<?=$data[minus]?> <?=$cfg['currency_type']?></td>
			<td><?=number_format($data[member_emoney],$cfg['currency_decimal'])?> <?=$cfg['currency_type']?></td>
			<td><?=$data[admin_id]?></td>
		</tr>
		<?
			$idx--;
			}
			if($NumTotalRec == 0) {
		?>
		<tr>
			<td colspan="7">예치금 내역이 없습니다</td>
		</tr>
		<?}?>
	</tbody>
</table>
<div class="pop_bottom"><?=$pg_res?></div>