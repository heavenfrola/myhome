<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  지역별 추가배송비 세부설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	$exec = trim($_POST['exec']);
	if(!fieldExist($tbl['delivery_area'], 'partner_no')) {
		addField($tbl['delivery_area'], 'partner_no', 'varchar(20) not null default ""');
		addField($tbl['delivery_area_detail'], 'partner_no', 'varchar(20) not null default ""');
	}

	function getDeliveryArea() {
		global $tbl, $admin, $pdo;

		ob_start();
		$cnt = 0;
		if($admin['partner_no'] == 0 || $admin['partner_no'] == '') {
			$where = "partner_no in ('0', '')";
		} else {
			$where = "partner_no='{$admin['partner_no']}'";
		}
		$res = $pdo->iterator("select * from $tbl[delivery_area_detail] where $where order by sort asc");
        foreach ($res as $data) {
			$dong = explode(',', $data['dong']);
			$ri = explode(',', $data['ri']);
			$data['area'] = ($data['sido'].' '.$data['gugun'].' '.$dong[0].' '.$ri[0]);
			$data['addprc'] = number_format($data['addprc']);
			$data['reg_date'] = date('Y-m-d H:i:s', $data['reg_date']);
			$data['areas'] = str_replace(',', '<br />', $data['dong']);
			$data['areas_ri'] = str_replace(',', '<br />', $data['ri']);
			$cnt++;

			if(!$data['gugun']) $data['area'] .= '전체';
			elseif(!$data['dong']) $data['area'] .= '전체';

			$count = count($dong)-1;
			if($count > 0) {
				if($count > 0) $data['area'] .= " 외 {$count}";
				$data['area'] = "<a id='da_{$data['no']}' href='#' onclick='return false;' onmouseover=\"new R2Tip(this, '{$data['areas']}', null, event)\">{$data['area']}</a>";
			}

			$count = count($ri)-1;
			if($count > 0) {
				if($count > 0) $data['area'] .= " 외 {$count}";
				$data['area'] = "<a id='da_{$data['no']}' href='#' onclick='return false;' onmouseover=\"new R2Tip(this, '{$data['areas_ri']}', null, event)\">{$data['area']}</a>";
			}
		?>
		<tr>
			<td><?=$data['name']?></td>
			<td class="left"><?=$data['area']?></td>
			<td><?=$data['addprc']?> 원</td>
			<td><span class="box_btn_s blue"><input type="button" value="수정" onclick="modifyDeliveryArea(<?=$data['no']?>)"></span></td>
			<td><span class="box_btn_s gray"><input type="button" value="삭제" onclick="removeDeliveryArea(<?=$data['no']?>)"></span></td>
			<td>
				<span class="box_btn_s blue"><input type="button" value="▲" onclick="sortDeliveryArea(<?=$data['no']?>, -1, this)"></span>
				<span class="box_btn_s blue"><input type="button" value="▼" onclick="sortDeliveryArea(<?=$data['no']?>, 1, this)"></span>
			</td>
			<td><?=$data['reg_date']?></td>
		</tr>
		<?}

		if($cnt == 0) {?>
		<tr>
			<td colspan="7" class="center">등록된 설정이 없습니다.</td>
		</tr>
		<?}
		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}

	if(!$exec) return;

	// 하위 주소 읽기
	if($exec == 'getAddr') {
		printAjaxHeader();

		$next_child = $_POST['next_child'];
		$sido = $_POST['sido'];
		$gugun = $_POST['gugun'];
		$dong = $_POST['dong'];

		exit(getAddr($next_child, $sido, $gugun, $dong));
	}

	// 추가배송비 리스트 리로드
	if($exec == 'reload') {
		printAjaxHeader();
		exit(getDeliveryArea());
	}

	// 추가배송비 삭제
	if($exec == 'remove') {
		$no = numberOnly($_POST['no']);
		$pdo->query("delete from $tbl[delivery_area_detail] where no='$no'");
		exit('OK');
	}

	// 우선순위 변경
	if($exec == 'sort') {
		$no = numberOnly($_POST['no']);
		$dir = $_POST['dir'];
		$sort = $pdo->row("select sort from $tbl[delivery_area_detail] where no='$no'");
		if($dir < 0) {
			$target = $pdo->row("select no from $tbl[delivery_area_detail] where sort < $sort order by sort desc limit 1");
		} else {
			$target = $pdo->row("select no from $tbl[delivery_area_detail] where sort > $sort order by sort asc limit 1");
		}
		$pdo->query("update $tbl[delivery_area_detail] set sort=sort-$dir where no='$target'");
		$pdo->query("update $tbl[delivery_area_detail] set sort=sort+$dir where no='$no'");

		exit;
	}

	// 추가배송비 불러오기
	if($exec == 'modify') {
		header('Content-type:application/json; charset=utf-8;');

		$no = numberOnly($_POST['no']);
		$data = $pdo->assoc("select no, name, sido, gugun, dong, ri, addprc from $tbl[delivery_area_detail] where no='$no'");
		$data = array_map('stripslashes', $data);

		$_dong = ($data['dong']) ? explode(',', $data['dong']) : array();
		$_ri = ($data['ri']) ? explode(',', $data['ri']) : array();
		$data['area'] = trim($data['sido'].' '.$data['gugun'].' '.$_dong[0]);
		if(!$data['gugun']) $data['area'] .= ' 전체';
		elseif(!$data['dong']) $data['area'] .= ' 전체';
		$count = count($_dong)-1;
		if($count > 0) {
			if($count > 0) $data['area'] .= " 외 {$count}";
		}

		$data['sido_list'] = getAddr('sido', $data['sido']);
		$data['gugun_list'] = getAddr('gugun', $data['sido'], $data['gugun']);
		if($data['gugun']) $data['dong_list'] = getAddr('dong', $data['sido'], $data['gugun'], $_dong);
		if($data['dong']) $data['ri_list'] = getAddr('ri', $data['sido'], $data['gugun'], $_dong[0], $_ri);

		exit(json_encode($data));
	}

	// 추가배송비 저장
	if($exec == 'update') {
		$no = numberOnly($_POST['no']);
		$name = trim(addslashes($_POST['ad2_name']));
		$sido = trim(addslashes($_POST['sido']));
		$gugun = trim(addslashes($_POST['gugun']));
		$dong = trim(addslashes($_POST['dong']));
		$ri = trim(addslashes($_POST['ri']));
		$addprc = numberOnly($_POST['ad2_prc']);

		checkBlank($sido, '시/도 이름을 입력해주세요.');
		if($addprc < 1) msg('추가배송비를 입력해 주세요.');

		if(!$name) {
			$dong1 = explode(',', $dong);
			$name = trim($sido.' '.$gugun.' '.$dong1[0]);
		}

		if($cfg['use_partner_shop'] == 'Y') {
			$asql = ", partner_no='$admin[partner_no]'";
		}

		if($pdo->row("select count(*) from $tbl[delivery_area_detail] where name='$name' and no != '$no' $asql") > 0) {
			msg('이미 동일한 이름의 설정이 존재합니다.');
		}

		if(!$no) {
			$sort = $pdo->row("select max(sort) from $tbl[delivery_area_detail] where 1 $asql");
			$sort++;

			$pdo->query("insert into $tbl[delivery_area_detail] (name, sido, gugun, dong, ri, addprc, reg_date, sort, partner_no) values ('$name', '$sido', '$gugun', '$dong', '$ri', '$addprc', '$now', '$sort', '$admin[partner_no]')");
		} else {
			$data = $pdo->assoc("select no from $tbl[delivery_area_detail] where no='$no'");
			if(!$data['no']) msg('잘못된 배송정보 입니다.');

			$pdo->query("update $tbl[delivery_area_detail] set `name`='$name', `sido`='$sido', `gugun`='$gugun', `dong`='$dong', `ri`='$ri', `addprc`='$addprc' where `no`='$no'");
		}

		javac("parent.reloadDeliveryArea();");
		exit;
	}

?>