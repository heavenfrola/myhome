<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원 - 적립금내역
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/milage.lib.php';
	expireMilage($mid);

    // 수동적립금발송 SMS 사용 여부
    $use_milage_sms = $pdo->row("select use_check from {$tbl['sms_case']} where `case`='39'");

?>
<!-- 적립금 지급, 반환 -->
<div id="mileDiv" style="display:none;">
	<form method="post" action="./" target="hidden<?=$now?>">
		<input type="hidden" name="body" value="member@member_update.exe">
		<input type="hidden" name="exec" value="milage">
		<input type="hidden" name="mno" value="<?=$mno?>">
		<table class="tbl_row">
			<caption class="hidden">적립금 지급, 반환</caption>
			<colgroup>
				<col style="width:15%;">
				<col>
			</colgroup>
			<tr>
				<th scope="row">구분</th>
				<td class="left">
					<label class="p_cursor"><input type="radio" name="exec2" value="+" checked> 지급</label>
					<label class="p_cursor"><input type="radio" name="exec2" value="-"> 반환</label>
				</td>
			</tr>
			<tr>
				<th scope="row">사유</th>
				<td class="left"><input type="text" name="mtitle" value="" class="input" size="13"></td>
			</tr>
			<tr>
				<th scope="row"><?=$cfg[milage_name]?></th>
				<td class="left"><input type="text" name="mprc" value="" class="input" size="13"></td>
			</tr>
            <?php if ($use_milage_sms == 'Y') { ?>
            <tr class="tr_milage_sms">
                <th scope="row">알림</th>
                <td class="left"><label><input type="checkbox" name="milage_sms" value="Y"> 적립금 지급 SMS 발송</label></td>
            </tr>
            <?php } ?>
		</table>
		<div class="box_bottom">
			<span class="box_btn blue"><input type="button" value="적용" onClick="putMilage(this.form);"></span>
			<span class="box_btn gray"><input type="button" value="닫기" onClick="layTgl2('mileDiv');"></span>
		</div>
	</form>
</div>
<!-- //적립금 지급, 반환 -->
<table class="tbl_col">
	<caption class="hidden">적립금내역</caption>
	<colgroup>
		<col style="width:50px;">
		<col style="width:90px;">
		<col>
		<col span="5" style="width:90px;">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">순서</th>
			<th scope="col">날짜</th>
			<th scope="col">적요</th>
			<th scope="col">적립</th>
			<th scope="col">사용</th>
			<th scope="col">소계</th>
			<th scope="col">만료예정일</th>
			<th scope="col">처리자</th>
		</tr>
	</thead>
	<tbody>
		<?php
			$sql="select * from `$tbl[milage]` where `member_no`='$mno' $id_where order by `no` desc";
			// 페이징 설정
			include $engine_dir."/_engine/include/paging.php";

			$page = numberOnly($_GET['page']);
			if($page<=1) $page=1;
			$row=20;
			$block=10;

			$NumTotalRec = $pdo->row("select count(*) from {$tbl['milage']} where member_no='$mno' $id_where");
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
				if($data[title]) $data[mtitle].=" (".strip_tags($data[title]).")";

				$data[minus]=0;
				$data[plus]=0;
				if($data[ctype]=="+") $data[plus]="<b>".number_format($data[amount],$cfg['currency_decimal'])."</b>";
				else $data[minus]="<b>".number_format($data[amount],$cfg['currency_decimal'])."</b>";

				if($data['expire'] == 'Y') $data['mtitle'] = "<s>$data[mtitle]</s>";
		?>
		<tr>
			<td><?=$idx?></td>
			<td title="<?=date("Y/m/d H:i:s",$data[reg_date])?>"><?=date("Y/m/d",$data[reg_date])?></td>
			<td class="left"><?=stripslashes($data[mtitle])?></td>
			<td>+<?=$data[plus]?> <?=$cfg['currency_type']?></td>
			<td>-<?=$data[minus]?> <?=$cfg['currency_type']?></td>
			<td><?=number_format($data[member_milage],$cfg['currency_decimal'])?> <?=$cfg['currency_type']?></td>
			<?if($data['ctype'] == '+') {?>
			<td><?=($data['expire_date'] > 0) ? date('Y/m/d', $data['expire_date']) : '무제한'?></td>
			<?} else {?>
			<td>-</td>
			<?}?>
			<td><?=$data[admin_id]?><?if($admin[level]==1){?><a href="./?body=main@milage_check&no=<?=$data[no]?>" target="_blank"></a><?}?></td>
		</tr>
		<?
			$idx--;
			}
			if($NumTotalRec == 0) {
		?>
		<tr>
			<td colspan="8">적립금 내역이 없습니다</td>
		</tr>
		<?}?>
	</tbody>
</table>
<div class="pop_bottom"><?=$pg_res?></div>
<script>
$(function() {
    $(':radio[name=exec2]').on('change', function() {
        if (this.value == '-') $('.tr_milage_sms').hide();
        else $('.tr_milage_sms').show();
    });
});
</script>