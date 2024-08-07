<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문정보엑셀 설정
	' +----------------------------------------------------------------------------------------------+*/

	$ord_excel_fd=array(
		'a' => '-- 공통코드 --',
		'idx' => '번호(역순)', 'ono' => '주문번호',
		'member_id' => '회원아이디', 'member_group' => '회원등급',
		'title' => '주문상품', 'option' => '상품옵션', 'code' => '상품코드', 'buy_ea' => '주문수량',
		'date1' => '주문일시', 'year' => '주문일시(년월)', 'day' => '주문일시(일)', 'date2' => '입금일시', 'date3' => '상품준비중일시', 'date4' => '배송일시', 'date5' => '배송완료일시', 'repay_date' => '취소일시',
		'bank_name' => '입금자명',
		'buyer_name' => '구매자명', 'buyer_email' => '구매자 이메일', 'buyer_phone' => '구매자 전화번호', 'buyer_cell' => '구매자 휴대폰번호',
		'addressee_name' => '수취인명', 'addressee_phone' => '수취인 전화번호', 'addressee_cell' => '수취인 휴대폰번호', 'addressee_zip' => '수취인 우편번호', 'addressee_addr' => '수취인 주소',
		'dlv_memo' => '배송메세지', 'etc' => '기타메시지',
		'total_prc' => '총결제금액', 'pay_prc' => '실결제금액', 'milage_prc' => '사용적립금', 'emoney_prc' => '사용예치금', 'coupon_name' => '사용쿠폰명', 'prd_prc' => '상품총액',
		'origin_prc' => '원가', 'dlv_prc' => '배송비', 'sale1' => '세트할인', 'sale2'=>'이벤트할인', 'sale3'=>'타임세일', 'sale4'=>'회원할인', 'sale5' => '전체상품쿠폰 할인금액', 'sale6' => '상품금액별할인', 'sale7' => '개별상품쿠폰 할인금액', 'sale8' => '정기배송 할인', 'repay_prc' => '부분취소금액', 'pay_type' => '결제방법',
		'stat'=>'주문상태', 'dlv_no' => '배송업체', 'dlv_code' => '송장번호', 'recom_member' => '추천인',
		'delivery_type' => '배송비형태','dlv_hold'=>'배송보류',
		'nations' => '해외배송 국가',
		'b' => '-- 상품엑셀 전용 --',
		'opno' => '주문상품번호', 'big' => '대분류명', 'seller' => '거래처명', 'origin_name' => '장기명', 'barcode' => '바코드', 'storage_name' => '창고명', 'storage_loc' => '창고위치', 'name_referer' => '참고 상품명',
		'c' => '-- 기타코드 --',
		'ymd' => '오늘날짜', 'idx_a' => '순서', 'dlv_prc' => '배송비', 'order_gift' => '사은품', 'blacklist' => '블랙리스트여부', 'black_reason' => '블랙리스트 사유', '1' => '1', 'null_fd' => '공란', 'sell_prc' => '상품단가', 'memo' => '주문메모'
	);

	if($cfg['order_add_field_use'] == 'Y'){
		$ord_excel_fd = array_merge($ord_excel_fd,array('addressee_id'=>'해외배송 추가 필드'));
	}

	if(fieldExist($tbl['order'],'tax')){
		$ord_excel_fd = array_merge($ord_excel_fd,array('tax'=>'관세'));
	}

	if($cfg['use_trigger_extra1'] == 'Y') {
		$ord_excel_fd['extra1'] = '배송 전 상품금액';
	}

    if ($scfg->comp('use_sbscr', 'Y') == true) {
		$ord_excel_fd['is_subscription'] = '정기배송주문';
    }

    if ($scfg->comp('use_set_product', 'Y') == false) {
        unset($ord_excel_fd['sale1']);
    }

	// 해외배송이 가능할때 국가/배송업체 추가
	if($cfg['delivery_fee_type'] == 'O' || $cfg['delivery_fee_type'] == 'A'){
		$ord_excel_fd = array_merge($ord_excel_fd,array(
            'nations'=>'배송국가',
            'delivery_com'=>'배송업체',
            'addressee_addr1'=>'해외배송 State/Province',
            'addressee_addr3'=>'해외배송 City',
            'addressee_addr4'=>'해외배송 Address1',
            'addressee_addr2'=>'해외배송 Address2',
        ));
	}

    // 불필요 시 엑셀다운로드를 느리게 하는 추천인 출력기능 제거
    if (isset($_use['recom_member']) == true || $_use['recom_member'] != 'Y') {
        unset($ord_excel_fd['recom_member']);
    }

    // 정기배송 미사용시 필드 제거
    if ($scfg->comp('use_sbscr', 'Y') == false) {
        unset($ord_excel_fd['sale8']);
    }

	if(@file_exists($root_dir.'/_config/order.php')){
		include_once $root_dir."/_config/order.php";
		if(@is_array($_ord_add_info)) {
			foreach($_ord_add_info as $key=>$val){
				$ord_excel_fd["order_add_info_".$key] = $_ord_add_info[$key]['name'];
			}
		}
	}

	// 구버전 데이터 마이그레이션
	if(!isTable($tbl['excel_preset'])) {
		include $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['excel_preset']);
	}

	if($pdo->row("select count(*) from $tbl[excel_preset] where type='order' and partner_no='$admin[partner_no]'") == 0) {
		$ord_excel_fd_default = 'idx,ono,title,date1,date2,buyer_name,bank_name,recom_member,total_prc,pay_prc,milage_prc,prd_prc,dlv_prc,pay_type,addressee_name,addressee_phone,addressee_cell,addressee_zip,addressee_addr,dlv_memo,stat';
		$pdo->query("insert into $tbl[excel_preset] (type, name, data, sort, reg_date, partner_no) values ('order', '기본', '$ord_excel_fd_default', '1', '$now', '$admin[partner_no]')");

		if(file_exists($root_dir.'/'.$dir['upload'].'/mng_excel_set/order_set.php')) {
			$file = file($root_dir.'/'.$dir['upload'].'/mng_excel_set/order_set.php');
			foreach($file as $key => $val) {
				$sort = $key+2;
				list($name, $set_data) = explode('@', addslashes(preg_replace('/^@|@#/' ,'', $val)));
				$pdo->query("insert into $tbl[excel_preset] (type, name, data, sort, reg_date) values ('order', '$name', '$set_data', '$sort', '$now')");
			}
		}
	}

	$xls_set = numberOnly($_REQUEST['xls_set']);
	if($_REQUEST['xls_set_temp']) $xls_set = $_REQUEST['xls_set_temp'];

	if($cfg['use_partner_shop'] == 'Y') {
		$excel_sql .= " and partner_no='$admin[partner_no]'";
	}
	$xls_res = $pdo->iterator("select * from $tbl[excel_preset] where type='order' $excel_sql order by sort asc");
    foreach ($xls_res as $set) {
		$ord_excel_set[$set['no']] = $set['data'];
		$ord_excel_set_name[$set['no']] = stripslashes($set['name']);
		if(!$xls_set) $xls_set = $set['no'];
	}
	$_ord_excel_fd_selected = explode(',', $ord_excel_set[$xls_set]);
	if(is_null($ord_excel_set[$xls_set])) $xls_set = 0;

	if(@strchr($body, 'order@order_excel.exe') || @strchr($body, 'order@order_list') || @strchr($body, 'order@order_trash')) {
		return;
	}

?>
<form name="frm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return saveSet(this);">
	<input type="hidden" name="body" value="config@excel_config.exe" />
	<input type="hidden" name="type" value="order" />
	<input type="hidden" name="exec" value="saveSet" />
	<input type="hidden" name="xls_set" value="<?=$xls_set?>" />
	<input type="hidden" name="set_data" value="" />

	<div class="box_title first">
		<h2 class="title">주문정보엑셀 설정</h2>
	</div>
	<div class="box_middle left">
		<ul class="list_msg">
			<li>원하시는 EXCEL 파일의 내용을 하단 오른쪽필드로 순서를 지정해주시기 바랍니다.</li>
			<li>저장된 형식은 주문리스트의 <u>"현재 검색결과를 엑셀 파일로 저장"</u> 버튼을 클릭하셔서 다운받으실 수 있습니다.</li>
			<li>불러오기 기능을 이용하신 경우 해당세트로 엑셀출력을 하시려면 우선 저장버튼을 클릭하여 설정을 저장하셔야 합니다.</li>
			<li><b><u>거래처명 장기명 대분류</u></b>를 출력하실경우 속도가 느리고 서버에 부담을 주게 되므로, 다량 주문을 엑셀출력 하실경우는 사용에 주의하시기 바랍니다(주문상품별 엑셀만 적용)</li>
			<li><b><u>주문메모</u></b> 필드의 경우 1회 최대 100개 이하의 주문서 다운로드 시에만 출력됩니다.</li>
		</ul>
	</div>
	<table class="tbl_row">
		<caption class="hidden">주문정보엑셀 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">세트 선택</th>
			<td>
				<select name="tmp_set">
					<option value="">======선택======</option>
					<?php foreach($ord_excel_set as $key=>$val){ ?>
					<option value="<?=$key?>" <?=checked($key, $xls_set, true)?>><?=$ord_excel_set_name[$key]?></option>
					<?php } ?>
				</select>
				<span class="box_btn_s"><input type="button" value="불러오기" onClick="loadSet(this.form);"></span>
				<span class="box_btn_s"><input type="button" value="새양식추가" onClick="makeSet(this.form);"></span>
			</td>
		</tr>
		<tr>
			<th scope="row">세트명</th>
			<td>
				<input type="text" name="set_name" value="<?=$ord_excel_set_name[$xls_set]?>" class="input">
				<?php if ($xls_set > 0) { ?>
				<span class="box_btn_s gray"><input type="button" value="삭제하기" onClick="delSet(<?=$xls_set?>);"></span>
				<?php } ?>
			</td>
		</tr>
	</table>
	<div class="box_middle2">
		<div class="add_fld">
			<div class="fld_list">
				<h3>추가할 필드 선택</h3>
				<select id="sel1" class="select_n" name="fd_list" size="25" multiple>
					<?php foreach($ord_excel_fd as $key=>$val) { ?>
						<option value='<?=$key?>'><?=$val?></option>
					<?php } ?>
				</select>
			</div>
			<div class="add">
				<span class="box_btn_s blue"><input type="button" value="추가하기" onclick="select2.addFromSelect(select1);"></span>
			</div>
			<div class="add_list">
				<h3>파일내용</h3>
				<select id="sel2" class="select_n" name="fd_list_selected" size="25" multiple>
					<?php foreach($_ord_excel_fd_selected as $key=>$val) { ?>
					<option value='<?=$val?>'><?=$ord_excel_fd[$val]?></option>
					<?php } ?>
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
        printLoading();
	}

	function delSet(xls_set) {
		if(confirm('선택한 세트를 삭제하시겠습니까?')) {
            printLoading();
			$.post('./index.php?body=config@excel_config.exe', {'exec':'remove', 'no':xls_set}, function(r) {
				location.reload();
			});
		}
	}

	function makeSet(f) {
        printLoading();
		$.post('./index.php?body=config@excel_config.exe', {'exec':'make', 'type':f.type.value}, function(r) {
			location.href = './index.php?body=config@order_excel_config&xls_set='+r;
		});
	}

	function saveSet(f) {
		var tmp = '';
		$(f.fd_list_selected).find('option').each(function() {
			if(tmp) tmp += ',';
			tmp += this.value;
		});
		f.set_data.value = tmp;

        printLoading();
	}
</script>