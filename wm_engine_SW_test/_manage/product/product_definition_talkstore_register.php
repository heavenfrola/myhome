<?PHP

	require_once $engine_dir.'/_config/set.talkStore.php';

	$_types = array();
	foreach($_talkstore_announce as $key => $val) {
		$_types[$key] = stripslashes($val['name']);
	}

	$idx = numberOnly($_GET['idx']);
	$type = $_GET['type'];
	if($idx > 0) {
		$data = $pdo->assoc("select * from $tbl[product_talkstore_announce] where idx='$idx'");
		$datas = json_decode($data['datas']);
		if(!$type) $type = $data['type'];
	}

	$fields = $_talkstore_announce[$type]['fields'];
	$onchange = "location.href='?body=$body&type='+this.value";
	if($idx) $onchange .= "+'&idx=$idx'";

?>
<form method="post" action="?" target="hidden<?=$now?>" onSubmit="printLoading();">
	<input type="hidden" name="body" value="product@product_definition_talkstore_register.exe">
	<input type="hidden" name="idx" value="<?=$idx?>">

	<div class="box_title first">
		<h2 class="title">카카오톡 스토어 정보고시 등록</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">카카오톡 스토어 정보고시 등록</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th>상품군</th>
				<td>
					<?=selectArray($_types, 'type', false, ':: 상품군을 선택해주세요 ::', $type, $onchange)?>
				</td>
			</tr>
			<?if(is_array($fields)) {?>
			<tr>
				<th>정보고시 제목</th>
				<td><input type="text" name="title" value="<?=inputText(stripslashes($data['title']))?>" class="input input_full"></td>
			</tr>
		</tbody>
	</table>

	<div class="box_title">
		<h2 class="title">정보고시 내용</h2>
	</div>
	<table class="tbl_row">
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<?foreach($fields as $key => $val) {?>
			<tr>
				<th><?=$val?></th>
				<td><textarea name="datas[<?=$key?>]" class="txta" style="height:40px;"><?=$datas->$key?></textarea></td>
			</tr>
			<?}}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" value="취소" onclick="location.href='<?=$_SESSION['listURL']?>'"></span>
	</div>
</form>