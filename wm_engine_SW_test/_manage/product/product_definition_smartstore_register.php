<?PHP

    use Wing\API\Naver\CommerceAPI;

	$category = numberOnly($_GET['category']);
	$no = numberOnly($_GET['no']);

	if($no > 0) {
		$data = $pdo->assoc("select * from `$tbl[store_summary]` where no='$no'");
		$data['datas'] = str_replace('/','\\',$data['datas']);
		$datas = json_decode($data['datas']);
		if(empty($category)) $category = $data['category'];
	}

	//상품정보고시 타이틀 리스트 불러오기
	$s_res = $pdo->iterator("select * from `$tbl[store_summary_type]` order by no asc");
    foreach ($s_res as $s_data) $summary_arr[$s_data['no']] = $s_data['content'];

	//상품정보고시 해당 내용 불러오기
	$fields = array();
	$list_res = $pdo->iterator("select * from `$tbl[store_summary_list]` where summary_no='".$category."' and essential='Y' order by no asc");
    foreach ($list_res as $l_data) $fields[$l_data['name']] = $l_data['summary'];

	$onchange = "location.href='?body=$body&category='+this.value";
	if($no) $onchange .= "+'&no=$no'";

    // 상품정보고시 추가 필드 체크
    if ($category) {
        $product_summary_type = $pdo->row("select summary from {$tbl['store_summary_type']} where no='$category'");
        $_SummeryTypes = CommerceAPI::getProductSummeryTypes()->SummaryType;
        $items = $_SummeryTypes->{$product_summary_type}->items;
        foreach ($items as $k => $v) {
            if (!array_key_exists($k, $fields)) {
                $_no = $pdo->row("select max(no) from {$tbl['store_summary_list']}")+1;
                $pdo->query(
                    "
                        insert into {$tbl['store_summary_list']}
                        (no, summary_no, name, summary, essential)
                        values (?, ?, ?, ?, ?)
                    ", array(
                        $_no, $category, $k, $v->name, ($v->required == true) ? 'Y' : 'N'
                    )
                 );
                $fields[$k] = $v->name;
            }
        }
    }

?>
<form name="storeFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" enctype="multipart/form-data" onSubmit="printLoading();">
	<input type="hidden" name="body" value="product@product_definition_smartstore_register.exe">
	<input type="hidden" name="exec" value="insert">
	<input type="hidden" name="category" value="<?=$category?>">
	<input type="hidden" name="no" value="<?=$data['no']?>">

	<div class="box_title first">
		<h2 class="title">네이버 스마트스토어 정보고시 등록</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">네이버 스마트스토어 정보고시 등록</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tbody>
			<tr>
				<th scope="row">상품군</th>
				<td>
					<?=selectArray($summary_arr, 'category', null, ':: 상품군을 선택해주세요 ::', $category, $onchange)?>
				</td>
			</tr>
			<?if(empty($category) == false) {?>
			<tr>
				<th scope="row">정보고시 제목</th>
				<td>
					<input type="text" name="title" value="<?=$data['title']?>" class="input input_full">
				</td>
			</tr>
		</tbody>
	</table>

	<div class="box_title">
		<h2 class="title">정보고시 내용</h2>
	</div>
	<table class="tbl_row">
		<tbody>
			<?foreach($fields as $key => $val) {?>
			<tr>
				<th class="left"><?=$val?></th>
			</tr>
			<tr>
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