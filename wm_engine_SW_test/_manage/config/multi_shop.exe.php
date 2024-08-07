<?PHP

	include_once $engine_dir.'/_engine/include/file.lib.php';
	include_once $engine_dir."/_engine/include/img_ftp.lib.php";

	$weca = new weagleEyeClient($_we, 'account');

	if($_POST['flag'] == 'user') {
		$file = $_FILES['userflag'];
		$flag_path = $cfg['flag_url'];
		if($file['size'] > 0) {
			$flag_file = 'userflag';
			$flag_path = getFileDir($dir['upload'].'/icon').'/_data/icon/'.$flag_file.'.'.getExt($file['name']);
			uploadFile($file, $flag_file, '_data/icon');
		} else {
			if($cfg['flag'] != 'user') msg('업로드할 파일(30pxx30px)을 선택해주세요.');
		}
	} else {
		$flag_path = $engine_url.'/_manage/image/common/'.$_POST['flag'];
	}
	$_POST['flag_url'] = $flag_path;

	$weca->call('setAccountFlag', array('flag' => $flag_path, 'site_name' => $_POST['company_mall_name']));

	if($_POST['skin'] || $_POST['mskin']){
		$connection=ftpCon();
		if(!$connection) msg("FTP 접속이 실패하였습니다. 1:1고객센터 문의 글로 접수 바랍니다.");
	}

	checkBlank($_POST['skin'],"PC 스킨을 선택해주세요.");

	if($_POST['skin'] && $_POST['skin'] != $_POST['design_skin']){ //스킨 값이 존재하고 설정된 스킨과 틀릴때만
		$design['version']=$_POST['design_version'];
		$design['skin']=$_POST['skin'];
		$design['edit_skin']=$_POST['design_edit_skin']?$_POST['design_edit_skin']:$_POST['skin'];
		include $engine_dir."/_manage/design/config.exe.php";
	}

	if($cfg['mobile_use'] == 'Y') {
        if ($_POST['mskin']) {
            $_GET['type'] = 'mobile';
            if($_POST['mskin'] && $_POST['mskin'] != $_POST['mdesign_skin']){ //스킨 값이 존재하고 설정된 스킨과 틀릴때만
                $design['version']=$_POST['mdesign_version'];
                $design['skin']=$_POST['mskin'];
                $design['edit_skin']=$_POST['mdesign_edit_skin']?$_POST['mdesign_edit_skin']:$_POST['mskin'];
                include $engine_dir."/_manage/design/config.exe.php";
            }
        }
	}

	if($_POST['use_r_currency_custom'] == 'Y'){
		if(trim($_POST['r_currency_type_custom'])) $_POST['r_currency_type'] = $_POST['r_currency_type_custom'];
		else{
			$_POST['r_currency_type'] = "";
			$_POST['use_r_currency_custom'] = 'N';
			$_POST['r_currency_type_custom'] = '';
		}
	}else{
		$_POST['use_r_currency_custom'] = 'N';
		$_POST['r_currency_type_custom'] = '';
	}

	include 'config.exe.php';

?>