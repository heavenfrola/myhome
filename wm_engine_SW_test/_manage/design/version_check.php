<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품이미지 줌이펙트 설정
	' +----------------------------------------------------------------------------------------------+*/

	$_skin_dir=$root_dir."/_skin";

	if($_REQUEST['type'] == 'mobile' || $_inc[0] == 'wmb') $head="m";

	if(file_exists($_skin_dir."/".$head."config.".$_skin_ext['g'])){
		include_once $_skin_dir."/".$head."config.".$_skin_ext['g'];
		$version=$design['version'];
	}
	$design_upgrade="N";

	if($cfg['design_version'] == ""){

		$cfg['design_version']=$version ? $version : "V2";

		$no_reload_config=1;
		$_POST['design_version']=$cfg['design_version'];
		include_once $engine_dir."/_manage/config/config.exe.php";

	}

	// 업그레이드 가능 여부 체크
	if($cfg['design_version'] == "V2" && $version == "V3"){

		$design_upgrade="Y";

	}

?>