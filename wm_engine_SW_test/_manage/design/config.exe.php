<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  디자인 설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	$file_content="<?php\n// 디자인 설정파일 : ".date("Y-m-d H:i", $now)." 변경됨 - ".$admin['admin_id']."\n\n";
	foreach($design as $key=>$val){
		$file_content .= "\$design['$key']=\"".$val."\";\n";
	}
	$file_content .= "?>";

	$_filedir=$root_dir."/_skin/config.".$_skin_ext['g'];
	$_filebakdir=$root_dir."/_data/design_config_tmp.".$_skin_ext['g'];
	$of=fopen($_filebakdir, "w");
	$fw=fwrite($of, $file_content);
	if(!$fw) msg("계정디렉토리 권한이 잘못되어있습니다. 1:1고객센터 문의 글로 접수 바랍니다.");
	fclose($of);

	if($_GET['type'] == 'mobile') $head="m";
	$file['name']=$head."config.".$_skin_ext['g'];
	$file['tmp_name']=$_filebakdir;
	ftpUploadFile($root_dir."/_skin", $file, $_skin_ext['g']);
	unlink($file['tmp_name']);

?>