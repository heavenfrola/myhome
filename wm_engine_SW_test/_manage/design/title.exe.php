<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  네비게이터 편집 처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/img_ftp.lib.php";
	$connection=ftpCon();
	if(!$connection) msg("FTP 접속이 실패하였습니다. 1:1고객센터 문의 글로 접수 바랍니다.");
	$exec = $_POST['exec'];

	if($exec == "reset"){
		ftpDeleteFile($root_dir."/_config", "title_name.php");
		msg("", "reload", "parent");
	}

	$content="<?php
// 파일정보 : ".date("Y-m-d H:i", $now)." - ".$admin['admin_id']."

\$_page_title['home']=\"".trim(inputText($_POST['home']))."\";
\$_page_title['member']=\"".trim(inputText($_POST['member']))."\";
\$_page_title['mypage']=\"".trim(inputText($_POST['mypage']))."\";
\$_page_title['joint']=\"".trim(inputText($_POST['joint']))."\";

";
	foreach($_POST as $key=>$val){
		if(strchr($key, "/")){
			$val=trim($val);
			$content .= "\$_page_sub_title['".$key."']=\"".inputText($val)."\";\n";
		}
	}
	$content .= "
?>";

	$_filedir=$root_dir."/_config/title_name.php";
	$_filebakdir=$root_dir."/_data/title_name_tmp.php";
	if($content){
		$of=fopen($_filebakdir, "w");
		$fw=fwrite($of, $content);
		if(!$fw) msg("계정디렉토리 권한이 잘못되어있습니다. 1:1고객센터 문의 글로 접수 바랍니다.");
		fclose($of);

		$file['name']="title_name.php";
		$file['tmp_name']=$_filebakdir;
		ftpUploadFile($root_dir."/_config", $file, "php");
		unlink($file['tmp_name']);
	}

	msg("", "reload", "parent");

?>