<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  적립금, 포인트 상호교환 처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/milage.lib.php";
	memberOnly();
	checkBasic();

	switch($_POST['exec']) {
		case 'to_milage' :
			$pamount = numberOnly($_POST['pamount']);
			checkBlank($pamount, __lang_mypage_input_transPts__);
			milageChanging($pamount, $member, 1);
		break;
		case 'to_point' :
			$mamount = numberOnly($_POST['mamount']);
			checkBlank($mamount, __lang_mypage_input_transMileage__);
			milageChanging($mamount, $member, 2);
		break;
	}

?>