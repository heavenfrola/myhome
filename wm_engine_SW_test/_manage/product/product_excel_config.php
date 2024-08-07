<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품정보엑셀 설정
	' +----------------------------------------------------------------------------------------------+*/

	$prd_excel_fd=array(
		'idx' => '번호', 'name' => '상품명', 'code' => '상품코드', 'keyword' => '키워드', 'reg_date' => '등록일',
		'hash' => '시스템코드',
		'cate' => '카테고리', 'big' => '대분류', 'mid' => '중분류', 'small' => '소분류', 'depth4' => '세분류',
		'sell_prc' => $cfg['product_sell_price_name'], 'normal_prc' => $cfg['product_normal_price_name'], 'origin_prc' => '구입원가', 'milage' => '적립금',
		'ea' => '재고수량', 'min_ord' => '최소주문한도', 'max_ord' => '최대주문한도',
		'seller' => '사입처', 'arcade' => '사입처상가', 'plocation' => '사입처위치', 'floor' => '사입처층', 'ptel' => '사입처전화번호', 'pcell' => '사입처휴대폰', 'origin_name' => '장기명',
		'content_html' => '상세설명(HTML 코드포함)', 'content_text' => '상세설명(HTML 코드제외)', 'content1' => '요약설명',
		'stat'=>'상품상태', 'no_interest' => '무이자', 'event_sale' => '이벤트', 'member_sale' => '회원혜택', 'free_delivery' => '무료배송', 'dlv_alone' => '단독배송',
		'upfile1' => '대이미지', 'w1' => '대이미지 가로사이즈', 'h1' => '대이미지 세로사이즈',
		'upfile2' => '중이미지', 'w2' => '중이미지 가로사이즈', 'h2' => '중이미지 세로사이즈',
		'upfile3' => '소이미지', 'w3' => '소이미지 가로사이즈', 'h3' => '소이미지 세로사이즈',
		'updir' => '이미지경로',
		'hit_view' => '조회수', 'hit_order' => '주문수', 'hit_sales' => '판매수', 'hit_cart' => '장바구니', 'hit_wish' => '위시리스트',
		'opt_info' => '상품옵션(줄바꿈기호 포함)', 'optinfo' => '상품옵션', 'm_content_html' => '모바일상세설명(HTML 코드포함)', 'm_content_text' => '모바일상세설명(HTML 코드제외)',
		'storage_name' => '창고명', 'storage_loc' => '창고위치',
	 );

	if($cfg['max_cate_depth'] < 4) {
		unset($prd_excel_fd['depth4']);
	}

	// 상품정보 무게 추가
	if(fieldExist($tbl['product'],'weight')) $prd_excel_fd = array_merge($prd_excel_fd,array('weight'=>'무게'));
	// 네이버쇼핑 오늘출발
	if($cfg['compare_today_start_use']=="Y") $prd_excel_fd = array_merge($prd_excel_fd,array('compare_today_start'=>'오늘출발'));

	// 추가항목 필드 추가
	$fdres = $pdo->iterator("select * from $tbl[product_field_set] where category='0' order by sort asc");
    foreach ($fdres as $fddata) {
		$prd_excel_fd = array_merge(
			$prd_excel_fd,
			array('#'.$fddata['no'] => stripslashes($fddata['name']))
		);
	}

	// 구버전 데이터 마이그레이션
	if(!isTable($tbl['excel_preset'])) {
		include $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['excel_preset']);
	}

	if($pdo->row("select count(*) from $tbl[excel_preset] where type='product'") == 0) {
		$prd_excel_fd_default = 'idx,name,cate,hash,code,sell_prc,milage,ea,reg_date,content_html,m_content_html';
		$pdo->query("insert into $tbl[excel_preset] (type, name, data, sort, reg_date) values ('product', '기본', '$prd_excel_fd_default', '1', '$now')");

		if(file_exists($root_dir.'/'.$dir['upload'].'/mng_excel_set/product_set')) {
			$file = file($root_dir.'/'.$dir['upload'].'/mng_excel_set/product_set.php');
			foreach($file as $key => $val) {
				$sort = $key+2;
				list($name, $set_data) = explode('@', addslashes(preg_replace('/^@|@#/' ,'', $val)));
				$pdo->query("insert into $tbl[excel_preset] (type, name, data, sort, reg_date) values ('product', '$name', '$set_data', '$sort', '$now')");
			}
		}
	}

	$xls_set = numberOnly($_GET['xls_set']);
	if($_REQUEST['xls_set_temp']) $xls_set = $_REQUEST['xls_set_temp'];

	$res = $pdo->iterator("select * from $tbl[excel_preset] where type='product' order by sort asc");
    foreach ($res as $set) {
		$_prd_excel_set[$set['no']] = $set['data'];
		$_prd_excel_set_name[$set['no']] = stripslashes($set['name']);
		if(!$xls_set) $xls_set = $set['no'];
	}
	$_prd_excel_fd_selected = explode(',', $_prd_excel_set[$xls_set]);
	if(is_null($_prd_excel_set[$xls_set])) $xls_set = 0;

	if(strchr($body, 'product@product_excel.exe') || strchr($body, 'product@product_list') || strchr($body, 'product@product_special_list') || strchr($body, 'wmb@product_special_list')) {
		return;
	}

?>
<form name="frm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return saveSet(this);">
	<input type="hidden" name="body" value="config@excel_config.exe" />
	<input type="hidden" name="type" value="product" />
	<input type="hidden" name="exec" value="saveSet" />
	<input type="hidden" name="xls_set" value="<?=$xls_set?>" />
	<input type="hidden" name="set_data" value="" />

	<div class="box_title first">
		<h2 class="title">상품정보엑셀 설정</h2>
	</div>
	<div class="box_middle left">
		<ul class="list_msg">
			<li>원하시는 EXCEL 파일의 내용을 하단 오른쪽필드로 순서를 지정해주시기 바랍니다.</li>
		</ul>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상품정보엑셀 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">세트 선택</th>
			<td>
				<select name="tmp_set">
					<option value="">======선택======</option>
					<?foreach($_prd_excel_set as $key=>$val){?>
					<option value="<?=$key?>" <?=checked($key, $xls_set, true)?>><?=$_prd_excel_set_name[$key]?></option>
					<?}?>
				</select>
				<span class="box_btn_s"><input type="button" value="불러오기" onClick="loadSet(this.form);"></span>
				<span class="box_btn_s"><input type="button" value="새양식추가" onClick="makeSet(this.form);"></span>
			</td>
		</tr>
		<tr>
			<th scope="row">세트명</th>
			<td>
				<input type="text" name="set_name" value="<?=$_prd_excel_set_name[$xls_set]?>" class="input">
				<?if($xls_set > 0){?>
				<span class="box_btn_s gray"><input type="button" value="삭제하기" onClick="delSet(<?=$xls_set?>);"></span>
				<?}?>
			</td>
		</tr>
	</table>
	<div class="box_middle2">
		<div class="add_fld">
			<div class="fld_list">
				<h3>추가할 필드 선택</h3>
				<select id="sel1" class="select_n" name="fd_list" size="25" multiple>
					<?foreach($prd_excel_fd as $key=>$val) {?>
						<option value='<?=$key?>'><?=$val?></option>
					<?}?>
				</select>
			</div>
			<div class="add">
				<span class="box_btn_s blue"><input type="button" value="추가하기" onclick="select2.addFromSelect(select1);"></span>
			</div>
			<div class="add_list">
				<h3>파일내용</h3>
				<select id="sel2" class="select_n" name="fd_list_selected" size="25" multiple>
					<?foreach($_prd_excel_fd_selected as $key=>$val){?>
					<option value='<?=$val?>'><?=$prd_excel_fd[$val]?></option>
					<?}?>
				</select>
				<span class="box_btn_s icon delete"><input type="button" value="삭제" onclick="select2.remove();"></span>
				<span class="box_btn_s icon up"><input type="button" value="위로" onclick="select2.move(-1);"></span>
				<span class="box_btn_s icon down"><input type="button" value="아래로" onclick="select2.move(1);"></span>
			</div>
		</div>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript" src='<?=$engine_url?>/_engine/common/R2Select.js?ver=20200821'></script>
<script type="text/javascript">
	var select1 = new R2Select('sel1');
	var select2 = new R2Select('sel2');

	function loadSet(f) {
		location.href = './?body=<?=$body?>&xls_set='+f.tmp_set.value;
	}

	function delSet(xls_set) {
		if(confirm('선택한 세트를 삭제하시겠습니까?')) {
			$.post('./index.php?body=config@excel_config.exe', {'exec':'remove', 'no':xls_set}, function(r) {
				location.reload();
			});
		}
	}

	function makeSet(f) {
		$.post('./index.php?body=config@excel_config.exe', {'exec':'make', 'type':f.type.value}, function(r) {
			location.href = './index.php?body=product@product_excel_config&xls_set='+r;
		});
	}

	function saveSet(f) {
		var tmp = '';
		$(f.fd_list_selected).find('option').each(function() {
			if(tmp) tmp += ',';
			tmp += this.value;
		});
		f.set_data.value = tmp;
	}
</script>