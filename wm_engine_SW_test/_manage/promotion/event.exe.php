<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  이벤트 생성/수정
	' +----------------------------------------------------------------------------------------------+*/
	$no = numberOnly($_POST['no']);
	if($_POST['exec'] == 'remove') {
		$data = $pdo->assoc("select * from $tbl[event] where no='$no'");
		if(!$data['no']) exit;
		$pdo->query("delete from $tbl[event] where no='$no'");
		exit('삭제되었습니다.');
	}

	$event_name = trim(addslashes($_POST['event_name']));
	$event_use = trim(addslashes($_POST['event_use']));
	$event_min_pay = numberOnly($_POST['event_min_pay']);
	$event_obj = trim(addslashes($_POST['event_obj']));
	$event_type = trim(addslashes($_POST['event_type']));
	$event_milage_addable = trim(addslashes($_POST['event_milage_addable']));
	$event_milage_addable2 = trim(addslashes($_POST['event_milage_addable2']));
	$event_ptype = trim(addslashes($_POST['event_ptype']));
	$event_per = numberOnly($_POST['event_per']);
	$event_round = trim(addslashes($_POST['event_round']));
	$reg_date = $now;
    $event_begin = strtotime($_POST['begin1'].' '.$_POST['begin2'].':'.$_POST['begin3'].':00');
    $event_finish = strtotime($_POST['finish1'].' '.$_POST['finish2'].':'.$_POST['finish3'].':59');

	if($event_begin > $event_finish)msg("시작일은 종료일 이전이어야합니다.");
	if($event_type == "1" && $event_obj == "1") msg('이벤트 방식이 적립일 경우에는\n전체고객을 대상으로 할 수 없습니다.');
	if($event_min_pay < 0) msg('최소 결제 금액을 입력해주세요.');
	if($event_per < 0) msg('할인(적립)률을 입력해주세요.');


	//시작일종료일체크 및 중복기간체크
	if($event_use == 'Y') {
		$check = 0;
		$res = $pdo->iterator("select event_begin, event_finish from $tbl[event] where event_use='Y' and no!='$no'");
        foreach ($res as $event) {
			$_res_event_begin  = $event['event_begin'];
			$_res_event_finish = $event['event_finish'];
			if($event_begin <= $_res_event_begin  &&  $event_finish >= $_res_event_begin)  $check++;
			if($event_begin >= $_res_event_begin  &&  $event_begin  <= $_res_event_finish) $check++;
			if($event_begin <= $_res_event_finish &&  $event_finish >= $_res_event_finish) $check++;
			if($event_begin >= $_res_event_begin  &&  $event_finish <= $_res_event_finish) $check++;
			if($check > 0) {
				msg('해당 기간중 진행되는 다른 이벤트가 있습니다.');
			}
		}
	}

	$_POST = array();
	$_POST['event_minute'] = "Y";
	$no_reload_config = "Y";
	include $engine_dir.'/_manage/config/config.exe.php';

	if($no > 0) {
		$pdo->query("
			update $tbl[event] set
				event_name='$event_name', event_begin='$event_begin', event_finish='$event_finish', event_use='$event_use', event_min_pay='$event_min_pay',
				event_obj='$event_obj', event_type='$event_type', event_milage_addable='$event_milage_addable',
				event_milage_addable2='$event_milage_addable2', event_ptype='$event_ptype', event_per='$event_per', event_round='$event_round'
			where no='$no'
		");

		msg('', 'popup');
	} else {
		$pdo->query("
			insert into $tbl[event]
			(event_name ,event_begin ,event_finish ,event_use ,event_min_pay ,event_obj ,event_type ,event_milage_addable ,event_milage_addable2 ,event_ptype ,event_per ,event_round ,reg_date)
			values
			('$event_name', '$event_begin', '$event_finish', '$event_use', '$event_min_pay', '$event_obj', '$event_type', '$event_milage_addable', '$event_milage_addable2', '$event_ptype', '$event_per', '$event_round', '$reg_date')
		");

		msg('', '?body=promotion@event_list', 'parent');
	}
?>