<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  EMS배송비 설정
	' +----------------------------------------------------------------------------------------------+*/

	if(!fieldExist($tbl['bank_accout'], 'type')) {
		$pdo->query("alter table `$tbl[bank_account]` add `type` enum('', 'int') not null default ''");
		$pdo->query("alter table `$tbl[bank_account]` add index `type`(`type`)");
	}

	if(file_exists($root_dir.'/_data/config/ems_prc.php')) {
		include_once $root_dir."/_data/config/ems_prc.php"; // 무게별 EMS 배송비 설정
	}
	if(file_exists($root_dir.'/_data/config/ems_nation.php')) {
		include_once $root_dir."/_data/config/ems_nation.php"; // 국가별 EMS 등급 설정
	} else {
		include_once $engine_dir."/_config/set.ems_nation.default.php"; // 국가별 EMS 등급 설정
	}
	include_once $engine_dir.'/_config/set.country.php'; // 국가정보
	sort($_nations);

?>
<form method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="code" value="ems_use">
	<div class="box_title first">
		<h2 class="title">EMS 사용설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">EMS 사용설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">EMS 사용</th>
			<td>
				<label class="p_cursor"><input type="radio" name="use_ems" value="Y" <?=checked($cfg['use_ems'], 'Y')?>> 사용</label>
				<label class="p_cursor"><input type="radio" name="use_ems" value="N" <?=checked($cfg['use_ems'], 'N')?><?=checked($cfg['use_ems'], '')?>> 사용안함</label><br><br>
				<ul>
					<li>EMS 사용상태에서 각 상품의 등록/수정시 상품무게를 입력하셔야 합니다.</li>
					<li><strong>무게가 입력된 상품과 입력되지 않은 상품을 같이 구매</strong>할 경우 EMS 배송비가 책정되지 않으며, 메일/연락/후불 등의 방법으로 처리하셔야 합니다.</li>
					<li>
						상품 주문시 <strong>박스무게</strong>로
						<input type="text" name="ems_box_weight" class="input right" size="10" value="<?=$cfg['ems_box_weight']?>"> 그램
						이 추가되어 배송비가 책정됩니다.
					</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<form id="dlv_ems_kg" method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@dlv_ems.exe">
	<input type="hidden" name="exec" value="prc">
	<div class="box_title">
		<h2 class="title">무게별 EMS 배송비 입력</h2>
	</div>
	<table class="tbl_col">
		<caption class="hidden">무게별 EMS 배송비 입력</caption>
		<thead>
			<tr>
				<th scope="col" style="width:70px;">무게(kg)</th>
				<th scope="col">1 town</th>
				<th scope="col">2 town</th>
				<th scope="col">3 town</th>
				<th scope="col">4 town</th>
				<th scope="col">Japan(P1)</th>
				<th scope="col">HongKong(P2)</th>
				<th scope="col">China(P3)</th>
				<th scope="col">australia(P4)</th>
				<th scope="col">Ameria(P5)</th>
				<th scope="col">France(P6)</th>
			</tr>
		</thead>
		<tbody>
			<?for($i = 0.5; $i <= 15; $i += 0.5) {?>
			<?$code = addZero($i * 10, 2);?>
			<tr>
				<th><?=sprintf("%0.1f", $i)?></th>
				<?for($x = 1; $x <= 4; $x++) {?>
				<td><input type="text" name="ems_<?=$code?>_<?=$x?>" class="input right" style="width:80%;" value="<?=$ems_prc['ems_'.$code.'_'.$x]?>"></td>
				<?}?>
				<?for($x = 1; $x <= 6; $x++) {?>
				<td><input type="text" name="emsp_<?=$code?>_<?=$x?>" class="input right" style="width:80%;" value="<?=$ems_prc['emsp_'.$code.'_'.$x]?>"></td>
				<?}?>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="배송비 저장"></span>
	</div>
</form>
<form id="dlv_ems_gr" method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@dlv_ems.exe">
	<input type="hidden" name="exec" value="grade">
	<div class="box_title">
		<h2 class="title">국가별 배송등급 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">국가별 배송등급 설정</caption>
		<colgroup>
			<col style="width:25%">
			<col style="width:100px">
			<col>
		</colgroup>
		<?
			foreach ($_nations as $key => $val) {
				$cconfig = $ems_nation[$val];
				$color = (!$cconfig) ? "color:#999; font-weight: normal; text-decoration:line-through" : "";
		?>
		<tr>
			<th rowspan="2" class="nation"><?=$val?></th>
			<td>일반</td>
			<td>
				<label class="p_cursor"><input type="radio" name="grade[<?=$val?>]" value="1" <?=checked($cconfig, "1")?>> 1 등급</label>
				<label class="p_cursor"><input type="radio" name="grade[<?=$val?>]" value="2" <?=checked($cconfig, "2")?>> 2 등급</label>
				<label class="p_cursor"><input type="radio" name="grade[<?=$val?>]" value="3" <?=checked($cconfig, "3")?>> 3 등급</label>
				<label class="p_cursor"><input type="radio" name="grade[<?=$val?>]" value="4" <?=checked($cconfig, "4")?>> 4 등급</label>
				<label class="p_cursor"><input type="radio" name="grade[<?=$val?>]" value="" <?=checked($cconfig, "")?>> 착불</label>
			</td>
		</tr>
		<tr>
			<td style="background:#f2f2f2;">프리미엄</td>
			<td style="background:#f2f2f2;">
				<label class="p_cursor"><input type="radio" name="grade[<?=$val?>]" value="P1" <?=checked($cconfig, "P1")?>> 1 등급</label>
				<label class="p_cursor"><input type="radio" name="grade[<?=$val?>]" value="P2" <?=checked($cconfig, "P2")?>> 2 등급</label>
				<label class="p_cursor"><input type="radio" name="grade[<?=$val?>]" value="P3" <?=checked($cconfig, "P3")?>> 3 등급</label>
				<label class="p_cursor"><input type="radio" name="grade[<?=$val?>]" value="P4" <?=checked($cconfig, "P4")?>> 4 등급</label>
				<label class="p_cursor"><input type="radio" name="grade[<?=$val?>]" value="P5" <?=checked($cconfig, "P5")?>> 5 등급</label>
				<label class="p_cursor"><input type="radio" name="grade[<?=$val?>]" value="P6" <?=checked($cconfig, "P6")?>> 6 등급</label>
			</td>
		</tr>
		<?}?>
	</table>
	<div id="dlvbtn"><span class="box_btn blue"><input type="submit" value="배송등급 저장"></span></div>
</form>
<script type='text/javascript'>
	$(window).scroll(function(){
		var el = document.body.scrollTop > 0 ? document.body : document.documentElement;
		var top = el.scrollTop + (document.body.offsetHeight) - 50;
		if(el.scrollTop > $('#dlv_ems_gr').offset().top-500) {
			$('#dlvbtn').show();
		} else {
			$('#dlvbtn').hide();
		}
	});
</script>