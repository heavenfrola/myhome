<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  배너광고코드 등록 처리
	' +----------------------------------------------------------------------------------------------+*/

	$name = addslashes(strip_tags($_POST['name']));
	$exec = addslashes($_POST['exec']);
	$icon = addslashes($_POST['icon']);
	$no = numberOnly($_POST['no']);

	// 서브 베너 출력
	if($exec == 'banners') {
		$group = $pdo->assoc("select `code` from `$tbl[pbanner_group]` where `no`='$no'");
		$res = $pdo->iterator("select * from `$tbl[pbanner]` where `ref` = '$no' order by `no` asc");

		$list = '';
        foreach ($res as $data) {
			$data['link'] .= preg_match('/\?/', $data['link']) ? '&wsmk='.$group['code'] : '?wsmk='.$group['code'];
			$list .= "
			<tr>
				<td>$data[name]</td>
				<td><a href='$data[link]' target='_blank'>$data[link]</a></td>
				<td><a href='#copy' class='box_btn_s clipboard btnp' data-clipboard-text='$data[link]'><span>복사하기</span></a></td>
			</tr>
			";
		}

		printAjaxHeader();
		?>
		<div style="padding:10px;">
			<table class="tbl_mini full">
				<colgroup>
					<col span="2">
					<col style="width:170px">
				</colgroup>
				<thead>
					<tr>
						<th scope="col">배너명</th>
						<th scope="col">링크주소</th>
						<th scope="col">링크복사</th>
					</tr>
				</thead>
				<tbody>
					<?=$list?>
				</tbody>
			</table>
		</div>
		<?
		exit;
	}

	// 배너코드 삭제
	if($exec == 'delete') {
		$check_pno = $_POST['check_pno'];
		foreach($check_pno as $val) {
			$pdo->query("delete from `$tbl[pbanner]` where `ref` = '$val'");
			$pdo->query("delete from `$tbl[pbanner_group]` where `no` = '$val'");
		}

		msg('삭제되었습니다.\t', 'reload', 'parent');
	}

	// 단위 배너 삭제
	if($exec == 'bdelete') {
		$pdo->query("delete from `$tbl[pbanner]` where `no` = '$no'");
		exit("[$no]");
	}

	// 배너그룹 저장
	if(!$icon) msg('아이콘을 선택해주십시오');

	$code = ($_POST['codetype'] == 1) ? addZero($pdo->row("select max(`no`) from `$tbl[pbanner_group]`")+1,5) : addslashes($_POST['code']);
	$code_exists = $pdo->row("select count(*) from `$tbl[pbanner_group]` where `no` != '$no' and `code` = '$code'");
	if($code_exists > 0) msg('이미 동일한 코드의 배너광고가 등록되어있습니다.\\t\\n다른 코드를 입력해 주십시오');


	if($no) {
		$pdo->query("update `$tbl[pbanner_group]` set `name`='$name', `icon`='$icon' where `no`='$no'");
	} else {
		$pdo->query("insert into `$tbl[pbanner_group]` (`name`, `code`, `icon`, `reg_date`) values ('$name', '$code', '$icon', '$now')");
		$no = $pdo->lastInsertId();
	}

	// 배너 저장
	$bno = numberOnly($_POST['bno']);
	$link = $_POST['link'];
	foreach($_POST['banner'] as $key => $val) {
		$val = addslashes(strip_tags($val));
		$link[$key] = addslashes($link[$key]);
		if(!$val) continue;
		if($bno[$key]) {
			$pdo->query("update `$tbl[pbanner]` set `name`='$val', `link`='$link[$key]' where `no`='$bno[$key]'");
		} else {
			$pdo->query("insert into `$tbl[pbanner]` (`ref`, `name`, `link`, `reg_date`) values ('$no', '$val', '$link[$key]', '$now')");
		}
	}

	$listURL = urldecode($listURL);
	if(!$listURL) $listURL = '?body=openmarket@ban_list';
	msg('광고배너가 저장되었습니다', $listURL, 'parent');

?>