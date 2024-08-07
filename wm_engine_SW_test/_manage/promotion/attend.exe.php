<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  출석체크 이벤트 생성/수정
	' +----------------------------------------------------------------------------------------------+*/

	if($_POST['exec'] == 'remove') {
		$no = numberOnly($_POST['no']);

		$data = $pdo->assoc("select * from $tbl[attend_new] where no='$no'");
		if(!$data['no']) exit;
		$serialize = serialize($data);

		$pdo->query("alter table $tbl[delete_log] change type type char(1) not null default 'P' COMMENT '타입 (P:상품, O:주문)'");
		$pdo->query("insert into $tbl[delete_log] (type, deleted, title, admin, deldate) values ('A', '$no', '$serialize', '$admin[admin_id]', '$now')");
		$pdo->query("delete from $tbl[attend_new] where no='$no'");

		exit('삭제되었습니다.');
	}

	$no = numberOnly($_POST['no']);
	$check_use = ($_POST['check_use'] == 'Y') ? 'Y' : 'N';
	$name = trim(addslashes($_POST['name']));
	$start_date = strtotime($_POST['start_date']);
	$finish_date = ($_POST['finish_date']) ? strtotime($_POST['finish_date'])+86399 : 2147483647;
	$event_type = numberOnly($_POST['event_type']);
	$complete_day = numberOnly($_POST['complete_day']);
	$prize_cno = numberOnly($_POST['prize_cno']);
	if($prize_cno == '') $prize_cno=0;
	$prize_milage = numberOnly($_POST['prize_milage']);
	$prize_point = numberOnly($_POST['prize_point']);
	$repeat_type = numberOnly($_POST['repeat_type']);
	$check_type = numberOnly($_POST['check_type']);
	$reg_date = $now;

	checkBlank($name, '출석체크 명을 입력해주세요.');
	checkBlank($_POST['start_date'], '시작일을 입력해주세요.');
	if($_POST['unlimited'] != 'Y') checkBlank($_POST['finish_date'], '종료일을 입력해주세요.');
	if($complete_day < 1) msg('달성 조건을 1일 이상 입력해 주세요.');
	if(!$prize_cno && $prize_milage < 1 && $prize_point < 1) msg('달성 혜택을 입력해 주세요.');

	if($no > 0) {
		$data  = $pdo->assoc("select * from $tbl[attend_new] where no='$no'");
		if(!$data['no']) msg('존재하지 않는 이벤트번호입니다.');
		if($data['check_use'] == 'N' && $check_use != 'Y') msg('중단된 이벤트는 수정하실수 없습니다.');
		if($data['check_cnt'] > 0) {
			if($data['start_date'] != $start_date || $data['finish_date'] != $finish_date) {
				msg('이미 시작된 이벤트의 기간은 변경할수 없습니다.');
			}
			if($data['complete_day'] != $complete_day) {
				msg('이미 시작된 이벤트의 달성 조건은 변경할수 없습니다.');
			}
			if($data['check_type'] != $check_type) {
				msg('이미 시작된 이벤트의 참여 방법은 변경할수 없습니다.');
			}
		}
		if($data['prize_cnt'] > 0) {
			if($data['prize_cno'] != $prize_cno || $data['prize_milage'] != $prize_milage || $data['prize_point'] != $prize_point) {
				msg('진행중인 이벤트의 혜택내용을 변경할수 없습니다.');
			}
			if($data['event_type'] != $event_type || $data['repeat_type'] != $repeat_type) {
				msg('진행중인 이벤트의 참여 방식 및 달성 조건은 변경할수 없습니다.');
			}
		}

		$pdo->query("
			update $tbl[attend_new] set
				name='$name', start_date='$start_date', finish_date='$finish_date', event_type='$event_type', complete_day='$complete_day',
				prize_cno='$prize_cno', prize_milage='$prize_milage', prize_point='$prize_point', repeat_type='$repeat_type', check_type='$check_type',
				check_use='$check_use'
			where no='$no'
		");

		msg('', 'popup');
	} else {
		$pdo->query("
			insert into $tbl[attend_new]
			(name, start_date, finish_date, event_type, complete_day, prize_cno, prize_milage, prize_point, repeat_type, check_type, check_use, reg_date)
			values
			('$name', '$start_date', '$finish_date', '$event_type', '$complete_day', '$prize_cno', '$prize_milage', '$prize_point', '$repeat_type', '$check_type', '$check_use', '$reg_date')
		");

		msg('', '?body=promotion@attend_list', 'parent');
	}

?>