<?PHP

	if($cfg['opmk_api']) {
		$opmkey = 0;
		$res = $pdo->iterator("select * from $tbl[openmarket_cfg] order by sort asc");

		function parseOpenmarket($res) {
            $data = $res->current();
            $res->next();
			if($data == false) return false;

			$data = array_map('stripslashes', $data);

			return $data;
		}
	}

	if(!$cfg['openmarket_scrap_order']) $cfg['openmarket_scrap_order'] = 40;

?>
<form method="post" enctype="multipart/form-data" action="./index.php" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@openmarket.exe">
	<input type="hidden" name="config_mode" value="openmarket">
	<input type="hidden" name="config_code" value="openmarket">

	<div class="box_title first">
		<h2 class="title">오픈마켓 연동 설정</h2>
	</div>
	<table class="tbl_row multi_shop">
		<caption class="hidden">오픈마켓 연동 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">연동서비스</th>
			<td>
				<select name="opmk_api">
					<option value="">사용안함</option>
					<option value="shopLinker" <?=checked($cfg['opmk_api'], 'shopLinker', true)?>>샵링커</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">샵링커 사용자아이디</th>
			<td>
				<input type="text" name="shoplinker_id" class="input" size="20" value="<?=$cfg['shoplinker_id']?>">
			</td>
		</tr>
		<tr>
			<th scope="row">샵링커 고객사코드</th>
			<td>
				<input type="text" name="shoplinker_cd" class="input" size="20" value="<?=$cfg['shoplinker_cd']?>">
			</td>
		</tr>
		<tr>
			<th scope="row">수집후 주문상태</th>
			<td>
				<input type="radio" name="openmarket_scrap_order" value="2" <?=checked($cfg['openmarket_scrap_order'], 2)?>> 입금완료
				<input type="radio" name="openmarket_scrap_order" value="40" <?=checked($cfg['openmarket_scrap_order'], 40)?>> 등록대기
				<ul class="list_msg">
					<li>입금완료 선택시 즉시 일반 주문과 함께 배송이 가능합니다.</li>
					<li>등록대기 선택시 '등록대기' 상태로 수집되며, 등록처리 후 배송이 가능합니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">송장등록 연동</th>
			<td>
				<input type="radio" name="openmarket_dlv" value="N" <?=checked($cfg['openmarket_dlv'], 'N')?>> 연동안함
				<input type="radio" name="openmarket_dlv" value="Y" <?=checked($cfg['openmarket_dlv'], 'Y')?>> 즉시연동
				<ul class="list_msg">
					<li>관리자모드에서 송장번호를 입력시 즉시 판매몰로 송장번호가 연동되며 배송처리 됩니다.</li>
					<li>쇼핑몰과 연동시 시간이 소요되므로 대량 처리시 시간이 오래 소요될수 있습니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form><br>

<?php if (!$cfg['opmk_api']) return; ?>

<form method="post" enctype="multipart/form-data" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@openmarket.exe">
	<input type="hidden" name="config_code" value="openmarket_linkage">

	<div class="box_title first">
		<h2 class="title">연동마켓 설정</h2>
	</div>
	<table class="tbl_col multi_shop">
		<caption class="hidden">오픈마켓 연동 설정</caption>
		<colgroup>
			<col style="width:5%">
			<col style="width:15%">
			<col style="width:12%">
			<col style="width:12%">
			<col style="width:6%">
			<col>
			<col style="width:6%">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">순번</th>
				<th scope="col">서비스명</th>
				<th scope="col">쇼핑몰코드</th>
				<th scope="col">연결계정</th>
				<th scope="col">사용여부</th>
				<th scope="col">메모</th>
				<th scope="col">삭제</th>
			</tr>
		</thead>
		<tbody>
			<?php while($data = parseOpenmarket($res)) { ?>
			<tr>
				<td><?=($opmkey+1)?></td>
				<td>
					<input type="hidden" name="no[<?=$opmkey?>]" value="<?=$data['no']?>">
					<input type="text" name="name[<?=$opmkey?>]" class="input" size="20" value="<?=inputText($data['name'])?>">
				</td>
				<td><input type="text" name="api_code[<?=$opmkey?>]" class="input" size="15" value="<?=inputText($data['api_code'])?>"></td>
				<td><input type="text" name="account_id[<?=$opmkey?>]" class="input" size="15" value="<?=inputText($data['account_id'])?>"></td>
				<td>
					<label><input type="checkbox" name="is_active[<?=$opmkey?>]" value="Y" <?=checked($data['is_active'], 'Y')?>> 사용함</label>
				</td>
				<td><input type="text" name="content[<?=$opmkey?>]" class="input input_full" size="50" value="<?=inputText($data['content'])?>"></td>
				<td>
					<span class="box_btn_s gray"><input type="button" value="삭제" onclick="removeOpenMarket(<?=$data['no']?>);"></span>
				</td>
			</tr>
			<?php $opmkey++;} ?>
			<tr>
				<td>신규</td>
				<td>
					<input type="hidden" name="no[<?=$opmkey?>]" value="">
					<input type="text" name="name[<?=$opmkey?>]" class="input" size="20" value="">
				</td>
				<td><input type="text" name="api_code[<?=$opmkey?>]" class="input" size="15" value="<?=inputText($data['api_code'])?>"></td>
				<td><input type="text" name="account_id[<?=$opmkey?>]" class="input" size="15" value="<?=inputText($data['account_id'])?>"></td>
				<td>
					<label><input type="checkbox" name="is_active[<?=$opmkey?>]" value="Y" <?=checked($data['is_active'], 'Y')?>> 사용함</label>
				</td>
				<td><input type="text" name="content[<?=$opmkey?>]" class="input input_full" size="50" value="<?=inputText($data['content'])?>"></td>
				<td></td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="7">
					<ul class="list_msg">
						<li>쇼핑몰코드는 오픈마켓연동 솔루션에서 확인하실수 있습니다.</li>
					</ul>
				</td>
			</tr>
		</tfoot>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
function removeOpenMarket(no) {
	if(confirm('선택한 정보를 삭제하시겠습니까?')) {
		$.post('./?body=config@openmarket.exe', {'exec':'remove', 'no':no}, function(r){
			location.reload();
		});
	}
}
</script>