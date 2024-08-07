<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  추가 페이지 편집
	' +----------------------------------------------------------------------------------------------+*/

	$exec = $_REQUEST['exec'];
	$name = $_REQUEST['name'];
	$cate = $_REQUEST['cate'];
	$pg_name = $_REQUEST['pg_name'];
	$mgroup = $_REQUEST['mgroup'];
	$no = $_REQUEST['no'];
	$gck = $_REQUEST['gck'];

	if(!$exec){
		checkBasic();
		checkBlank($name, '페이지명을 입력해주세요.');
	}

    $_banned = array(
        'company.php', 'guide.php', 'uselaw.php', 'privacy.php', 'join_rull.php'
    );
    if (in_array($pg_name, $_banned) == true) {
        msg('사용할 수 없는 페이지명입니다.');
    }

	$cont_edit_dir = $root_dir.'/_config/';
	$cont_edit_file = $cont_edit_dir.'content_add.php';
	$cont_add_dir = $root_dir.'/_template/content/';

	$_content_add_info = array();
	if(file_exists($cont_edit_file) == true) {
		include_once $cont_edit_file;

        if(is_writable($cont_edit_file) == false) {
            msg('추가페이지 설정파일에 쓰기 권한이 없습니다.');
        }
	} else {
        if(is_writable($cont_edit_dir) == false) {
            msg('추가페이지 설정파일 생성권한이 없습니다.');
        }
    }

    // 추가 및 수정
	if($exec != 'delete'){
		if (!$pg_name) {
			$pg_name = 'content_'.(count($_content_add_info)+1).'.php';
		}
		$ext = getExt($pg_name);
		$key = str_replace('.'.$ext, '', $pg_name);
		if (!$no) $no = $key;
		else $modify = 1;
		$pg_name = $key.'.'.$ext;

        // 접근 권한
    	$_mgroup = '';
		if (is_array($mgroup) == true && count($mgroup) > 0) {
			foreach($mgroup as $mkey=>$mval){
				$_mgroup .= $mval.'@';
			}
		}
		if($gck) {
			$_mgroup = $_mgroup."1@";
		}

		$_content_add_info[$key]['cate'] = $cate;
		$_content_add_info[$key]['name'] = $name;
		$_content_add_info[$key]['pg_name'] = $pg_name;
		$_content_add_info[$key]['mgroup'] = $_mgroup;
		$_content_add_info[$key]['use_m_content'] = ($_POST['use_m_content'] == 'Y') ? 'Y' : 'N';
	}

	$_file_contents  = "<?php\n\n";
    $_file_contents .= "/*\n";
    $_file_contents .= " *  추가 페이지 설정\n";
    $_file_contents .= " *  Modifited : ".date("Y-m-d H:i")."\n";
    $_file_contents .= " *  Author : ".$admin['admin_id']."\n";
    $_file_contents .= " */";
	foreach ($_content_add_info as $key => $val) {
		$_pg_name = $_content_add_info[$key]['pg_name'];
		if ($exec == 'delete' && $no == $key) {
			@unlink($cont_add_dir.$_pg_name);
			if (file_exists($cont_add_dir.$_pg_name) == true) {
                msg('파일삭제에 실패하였습니다. 1:1고객센터로 문의해주세요.');
            }

            // 모바일 파일 삭제
            $ext = getExt($_pg_name);
            if (file_exists($cont_add_dir.'/'.$key.'_m.'.$ext) == true) {
                unlink($cont_add_dir.'/'.$key.'_m.'.$ext);
            }
			continue;
		}

        // 빈 스킨파일 생성
		if(file_exists($cont_add_dir.$_pg_name) == false) {
            if (fwriteTo(str_replace($root_dir.'/', '', $cont_add_dir.$_pg_name), '', 'w') == false) {
                msg('디렉토리 권한설정문제로 파일생성에 실패하였습니다. 1:1고객센터로 문의해주세요.');
            }
			chmod($cont_add_dir.$_pg_name, 0777);
			server_sync($cont_add_dir.$_pg_name);
		}

        $_file_contents .= "\n";
		$_file_contents .= "\n\$_content_add_info['".$key."']['cate']=\"".$_content_add_info[$key]['cate']."\";";
		$_file_contents .= "\n\$_content_add_info['".$key."']['name']=\"".$_content_add_info[$key]['name']."\";";
		$_file_contents .= "\n\$_content_add_info['".$key."']['pg_name']=\"".$_pg_name."\";";
		$_file_contents .= "\n\$_content_add_info['".$key."']['mgroup']=\"".$_content_add_info[$key]['mgroup']."\";";
		$_file_contents .= "\n\$_content_add_info['".$key."']['use_m_content']=\"".$_content_add_info[$key]['use_m_content']."\";";
	}
	$_file_contents .= "\n\n?>";

    if (fwriteTo(str_replace($root_dir.'/', '', $cont_edit_file), $_file_contents, 'w') == false) {
        msg('파일생성에 실패하였습니다.');
    }
	chmod($cont_edit_file, 0777);
	server_sync($cont_edit_file);

	msg('', 'reload', 'parent');

?>