<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  기본 텍스트 편집 처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/img_ftp.lib.php";
	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	$_skin_name=editSkinName();
	include_once $root_dir."/_skin/".$_skin_name."/skin_config.".$_skin_ext['g'];

	$text_type = $_POST['text_type'];
	$family = $_POST['family'];
	$size = $_POST['size'];
	$weight = $_POST['weight'];
	$style = $_POST['style'];
	$color = $_POST['color'];
	$deco = $_POST['deco'];

	checkBlank($text_type, "편집 타입을 입력해주세요.");

	// 필드 정리
	$_edt_style=array("family", "size", "weight", "style", "color", "deco");
	$_fd_key=array();
	foreach(${$_edt_style[0]} as $key=>$val){
		$_fd_key[]=$key;
	}

	$ii=1;
	foreach($_fd_key as $key=>$val){
		$_cfg_key="text_edit_".$text_type."_".$val;
		unset($_skin[$_cfg_key]);
		$_tmp="";
		foreach($_edt_style as $ekey=>$eval){
			$_style=$_POST[$eval][$val];
			$_style=stripslashes($_style);
			$_style=str_replace('"', "", $_style);
			$_style=str_replace("'", "", $_style);
			$_style=trim($_style);
			if($_style){
				if($eval == "color") $_tmp .= " ".$eval.":".$_style.";";
				elseif($eval == "deco") $_tmp .= " text-decoration:".$_style.";";
				else $_tmp .= " font-".$eval.":".$_style.";";
			}
		}
		if($_tmp != "") $_skin[$_cfg_key]=$_tmp;
		$ii++;
	}

	include $engine_dir."/_manage/design/skin_config.exe.php";

	designValUnset();

	msg("", "reload", "parent");

?>