<?PHP

	if(addField($tbl['product_field_set'], 'default_value', 'text not null') == true) {
		$pdo->query("alter table {$tbl['product_field_set']} CHANGE COLUMN name name varchar(100) not null default '' COMMENT '항목명'");
	}

	$no = numberOnly($_GET['no']);
	$data = $pdo->assoc("select no, name, code from {$tbl['category']} where no='$no'");

	// 기존 정보고시 호환
	$fields = array();
	$field_values = array();
	if($no) {
		$res = $pdo->iterator("select * from {$tbl['product_field_set']} where category='$no' order by sort asc");
        foreach ($res as $fd) {
			$fields[] = array(
				stripslashes($fd['name']), 'N', $fd['no'], stripslashes($fd['default_value']), stripslashes($fd['soptions']), $fd['ftype']
			);
			$field_values[$fd['name']] = ($fd['ftype'] == '2') ? stripslashes($fd['soptions']) : stripslashes($fd['default_value']);
		}
	}

	// 상품군 템플릿
	$template = comm('https://redirect.wisa.co.kr/productDefinition.json');
	$template = json_decode($template);
	$_code = array();
	foreach($template as $key => $val) {
		$_code[] = $key;
	}
	$code = ($_GET['code']) ? $_GET['code'] : $data['code'];
    if ($_GET['code']) {
        if($code && is_array($template->{$code}) == true) { // 필드 불러오기
            $fields = $template->{$code};
        }
    }

	$onchange = "location.href='".makeQueryString(true, 'code')."&code='+this.value";

	function parseField(&$res, &$fidx) {
		global $field_values;

		$field = current($res);
		if($field == false) return false;
		next($res);

        if (is_null($fidx) == true) $fidx = -1;
        $fidx++;

		$field['no'] = $field[2];
		$field['name'] = $field[0];
		$field['essential'] = $field[1];
		$field['default_value'] = $field[3];
		$field['soptions'] = $field[4];
        $field['ftype'] = $field[5];
        $field['fidx'] = $fidx;

		if(empty($field['default_value']) == true) {
			if(isset($field_values[$field['name']]) == true) $field['default_value'] = $field_values[$field['name']];
		}

		return $field;
	}

?>
<form method="post" action="?" target="hidden<?=$now?>" onSubmit="printLoading();">
	<input type="hidden" name="body" value="product@product_definition_register.exe">
	<input type="hidden" name="exec" value="insert">
	<input type="hidden" name="code" value="<?=$code?>">
	<input type="hidden" name="no" value="<?=$data['no']?>">

	<div class="box_title first">
		<h2 class="title">상품정보제공고시 등록</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상품정보제공고시 등록</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">상품군</th>
                <?php if ($_GET['no']) { ?>
                <td>
                    <input type="hidden" name="code" value="<?=$code?>"> <?=$code?>
                </td>
                <?php } else { ?>
				<td>
					<?=selectArray($_code, 'code', true, ':: 상품군을 선택해주세요 ::', $code, $onchange)?>
				</td>
                <?php } ?>
			</tr>
			<tr>
				<th scope="row">정보고시 제목</th>
				<td>
					<input type="text" name="name" value="<?=$data['name']?>" class="input input_full">
				</td>
			</tr>
		</tbody>
	</table>

	<?if(count($fields) > 0) {?>
	<div class="box_title">
		<h2 class="title">정보고시 내용</h2>
	</div>
	<table class="tbl_row">
		<tbody>
			<?php while($field = parseField($fields, $fidx)) { ?>
			<tr>
				<th class="left">
                        <?=$field['name']?>
                    <div style="float: right"><input type="checkbox" name="ftype[<?=$field['fidx']?>]" value="Y" <?=checked($field['ftype'], '2')?>> 선택형</div>
                </th>
			</tr>
			<tr>
				<td>
					<input type="hidden" name="fn[<?=$field['fidx']?>]" value="<?=$field['name']?>">
					<input type="hidden" name="fno[<?=$field['fidx']?>]" value="<?=$field['no']?>">
					<textarea name="default_value[<?=$field['fidx']?>]" class="txta field" style="height:40px;"><?=$field['default_value']?></textarea>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?}?>

	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" value="취소" onclick="location.href='<?=getListURL('product_definition')?>'"></span>
	</div>
</form>