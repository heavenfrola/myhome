<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품정렬 설정
	' +----------------------------------------------------------------------------------------------+*/

	function selPrdSort($sel,$key) {
		global $total_sorts;
		$sort_str="<select name=\"sort[$key]\">\n";
		for($ii=1; $ii<=$total_sorts; $ii++) {
			$sort_str.="<option value=\"$ii\" ".checked($sel,$ii,1).">$ii</option>\n";
		}
		$sort_str.="</select>";
		return $sort_str;
	}

	$_psort[1]['query']="`reg_date` desc";
	$_psort[1]['name']=__lang_sort_info_1__;
	$_psort[2]['query']="`edt_date` desc";
	$_psort[2]['name']=__lang_sort_info_2__;
	$_psort[3]['query']="binary(`name`)";
	$_psort[3]['name']=__lang_sort_info_3__;
	$_psort[4]['query']="`hit_sales` desc";
	$_psort[4]['name']=__lang_sort_info_4__;
	$_psort[5]['query']="`hit_sales` asc";
	$_psort[5]['name']=__lang_sort_info_5__;
	$_psort[6]['query']="`sell_prc` desc";
	$_psort[6]['name']=__lang_sort_info_6__;
	$_psort[7]['query']="`sell_prc` asc";
	$_psort[7]['name']=__lang_sort_info_7__;
	$_psort[8]['query']="`hit_view` desc";
	$_psort[8]['name']=__lang_sort_info_8__;
	$_psort[9]['query']="`hit_view` asc";
	$_psort[9]['name']=__lang_sort_info_9__;
	$_psort[10]['query']="`rev_avg` desc";
	$_psort[10]['name']=__lang_sort_info_10__;
	$_psort[11]['query']="`rev_cnt` desc, `rev_avg` desc";
	$_psort[11]['name']=__lang_sort_info_11__;
	$total_sorts = count($_psort);

?>
<form method="post" action="./" target="hidden<?=$now?>" onSubmit="return checkPrdSort(this)">
	<input type="hidden" name="body" value="product@product_sort_set.exe">
	<div class="box_title first">
		<h2 class="title">상품정렬 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상품정렬 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">형태</th>
			<td>
				<label class="p_cursor"><input type="radio" name="prd_sort_type" value="1" <?=checked($cfg['prd_sort_type'],1).checked($cfg['prd_sort_type'],'')?>> 콤보박스</label>
				<label class="p_cursor"><input type="radio" name="prd_sort_type" value="2" <?=checked($cfg['prd_sort_type'],2)?>> 라디오 버튼</label>
				<label class="p_cursor"><input type="radio" name="prd_sort_type" value="3" <?=checked($cfg['prd_sort_type'],3)?>> 사용자 지정(스킨)</label>
			</td>
		</tr>
		<tr>
			<th scope="row">품절상품 진열</th>
			<td>
				<label class="p_cursor"><input type="radio" name="prd_sort_soldout" value="N" <?=checked($cfg['prd_sort_soldout'], 'N')?>> 정렬 순서대로 보여주기</label>
				<label class="p_cursor"><input type="radio" name="prd_sort_soldout" value="Y" <?=checked($cfg['prd_sort_soldout'], 'Y')?>> 리스트 끝으로 보내기</label>
				<label class="p_cursor"><input type="radio" name="prd_sort_soldout" value="H" <?=checked($cfg['prd_sort_soldout'], 'H')?>> 노출안함</label>
			</td>
		</tr>
	</table>
	<table class="tbl_col" style="border-top:0;">
		<caption class="hidden">상품정렬 설정 리스트</caption>
		<colgroup>
			<col style="width:100px">
			<col>
			<col style="width:100px">
			<col style="width:100px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">사용</th>
				<th scope="col">항목명</th>
				<th scope="col">순서</th>
				<th scope="col">기본선택</th>
			</tr>
		</thead>
		<tbody>
			<?PHP

				$idx = 0;
				foreach($_psort as $key=>$val) {
					$idx++;
					if(!$val['query'] || !$val['name']) {
						continue;
					}
					$data = $pdo->assoc("select * from $tbl[product_sort] where no='$key'");
					if(!$data['no']) {
						$pdo->query("INSERT INTO `$tbl[product_sort]` ( `no` , `query` , `name` , `use` , `sort` , `real_use` ) VALUES ('$key','$val[query]','$val[name]','N','$key','Y')");
						$data = $pdo->assoc("select * from $tbl[product_sort] where no='$key'");
					}
					$inputNameText = ($data['name'] == $val['name']) ? '' : inputText($data['name']);

			?>
			<tr>
				<td>
					<input type="hidden" name="no[<?=$data[no]?>]" value="<?=$data[no]?>">
					<input type="checkbox" name="use[<?=$data[no]?>]" value="Y" <?=checked($data['use'],"Y")?>>
				</td>
				<td class="left"><input type="text" name="name[<?=$data['no']?>]" value="<?=$inputNameText?>" class="input" size="30" placeholder="<?=$val['name']?>"></strong></td>
				<td><?=selPrdSort($data['sort'],$data[no])?></td>
				<td><input type="radio" name="prd_sort_def" value="<?=$data[no]?>" <?=checked($cfg['prd_sort_def'],$data[no])?>></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" value="초기데이터 복원" onclick="restoreSort();"></span>
	</div>
</form>
<script type="text/javascript">
function restoreSort() {
	if(confirm('모든 입력사항을 삭제하고 초기 데이터로 복원하시겠습니까?')) {
		$.post('?body=product@product_sort_set.exe', {'exec':'reset'}, function() {
			location.reload();
		});
	}
}
</script>