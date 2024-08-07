<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  추가 페이지 편집 처리
	' +----------------------------------------------------------------------------------------------+*/

	checkBasic();

	$cont_no = $_POST['cont_no'];
	$content_add = $_POST['content_add'];
	$content1 = trim($_POST['content1']);
	$content2 = trim($_POST['content2']);

	if($cont_no=="") {
		msg("필수값(cont_no)이 없습니다");
	}
	if($content_add){
		$cont_edit_file=$root_dir."/_config/content_add.php";
		if(is_file($cont_edit_file)) include_once $cont_edit_file;
		$_cont_page[$cont_no]=$cont_no;
		$ext=getExt($_content_add_info[$cont_no]['pg_name']);
	}
	if(!$ext) $ext="php";

	$cont_page=$_cont_page[$cont_no];
	if(!$cont_page) {
		msg("잘못된 접속입니다");
	}
    for ($i = 1; $i <= 2; $i++) {
        if ($i == 2) $cont_page = $cont_page.'_m';
    	$cont_file = $root_dir.'/_template/content/'.$cont_page.'.'.$ext;

        $content = str_replace('[WMCODE]', '<?php', ${'content'.$i});
        $content = str_replace('[/WMCODE]', '?>', $content);
        $content = stripslashes($content);

        /*PHP 구문 삭제*/
        $content = preg_replace('/<\?php|<\?|\?>/i', '', $content);
        funcFilter($content);

        $fp = fopen($cont_file, 'w');
        fwrite($fp, $content);
        fclose($fp);
        chmod($cont_file, 0777);

        server_sync($cont_file);
    }

	msg("수정되었습니다","reload","parent");

?>