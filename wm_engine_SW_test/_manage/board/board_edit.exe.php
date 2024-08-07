<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 관리 처리
	' +----------------------------------------------------------------------------------------------+*/

	include $engine_dir."/board/include/lib.php";

	$delete_db = addslashes($_GET['delete_db']);
	$new_edit = $_POST['new_edit'];
	$auth_member = ($_POST['auth_member'] == "2") ? "2" : "1";

	if($delete_db) {
		$sql="select * from `$mari_set[mari_board]` where `db`='$delete_db'";
		$res = $pdo->iterator($sql);
        foreach ($res as $data) {
			if($data[up_dir]) {
				$up_dir=$mari_path.$data[up_dir];
				if(is_dir($up_dir)) {
					@dellAllFile($up_dir);
					@rmdir($up_dir);
				}
			}
		}

		$sql="delete from `$mari_set[mari_board]` where `db`='$delete_db'";
		$pdo->query($sql);
		$sql="delete from `$mari_set[mari_comment]` where `db`='$delete_db'";
		$pdo->query($sql);
		$sql="delete from `$mari_set[mari_cate]` where `db`='$delete_db'";
		$pdo->query($sql);
		$sql="delete from `$mari_set[mari_config]` where `db`='$delete_db'";
		$pdo->query($sql);

		msg("삭제되었습니다","reload","parent");

	}
	elseif($new_edit){
		$no = numberOnly($_POST['no']);
		$data=get_info("mari_config", "no", $no);

		function dbNameChk($type){
			global $pdo;
			$tmp=1;
			while($tmp){
				$db=$type."_".$tmp;
				if($pdo->row("select `no` from `mari_config` where `db`='$db'")){
					$tmp++;
				}else{
					return $db;
				}
			}
		}

		if(!fieldExist('mari_config', 'date_type_list')) {
			addField('mari_config', 'date_type_list', 'varchar(50)');
			addField('mari_config', 'date_type_view', 'varchar(50)');
			addField('mari_config', 'date_type_user', 'varchar(50)');
		}

		if(!fieldExist('mari_config', 'mskin')) {
			addField('mari_config', 'mskin', 'varchar(50)');
		}

		if(!fieldExist('mari_config', 'fsubject')) {
			addField('mari_config', 'fsubject', 'text');
			addField('mari_config', 'use_fsubject', 'enum("N","Y") not null default "N"');
		}

		if(!fieldExist('mari_config', 'protect_name')) {
			addField('mari_config', 'protect_name', 'enum("N","Y") not null default "N"');
			addField('mari_config', 'protect_name_strlen', 'int(2)');
			addField('mari_config', 'protect_name_suffix', 'varchar(20)');
		}

		if(!fieldExist('mari_config', 'use_scallback')) {
			addField('mari_config', 'use_scallback', 'enum("N","Y") not null default "N"');
			addField('mari_config', 'use_mcallback', 'enum("N","Y") not null default "N"');
		}
		if(!fieldExist('mari_config', 'start_mode')) {
			addField('mari_config', 'start_mode', 'enum("1","2", "3") not null default "1"');
		}
		if(!fieldExist('mari_config', 'load_url')) {
			addField('mari_config', 'load_url', 'enum("1","2", "3") not null default "1"');
			addField('mari_config', 'loading_url', 'text');
		}

		addField('mari_config', 'board_comment_sort', 'enum("1", "2") not null default "1"');
		addField('mari_config', 'auth_member', 'enum("1", "2") not null default "1"');
		addField('mari_config', 'use_sort', 'enum("N", "Y") not null default "N"');

		foreach($_POST as $key => $val) {
			${$key} = $_POST[$key] = addslashes(trim($val));
		}
		$use_fsubject = ($_POST['use_fsubject'] == 'Y') ? 'Y' : 'N';

		if($_POST['board_type'] == 'blog'){
			$list_mode = 2;
			$auth_view = 10;
			$use_view = "N";
		}

		checkBlank($title,"게시판명을 입력해주세요.");
		$db = ($data['db']) ? $data['db'] : dbNameChk($board_type);
		$board_sql="`title`='$title', `db`='$db'";
		$board_sql .= ($skin) ? ", `skin`='$skin'" : "";
		$board_sql .= ($mskin) ? ", `mskin`='$mskin'" : "";
		$board_sql .= ", `gallery_cols`='$gallery_cols'";
		$board_sql .= ", `cut_title`='$cut_title', `page_row`='$page_row', `page_block`='$page_block', `list_mode`='$list_mode' , `start_mode`='$start_mode', board_comment_sort='$board_comment_sort', `load_url`='$load_url', `loading_url`='$loading_url'";
		$board_sql .= ", `auth_list`='$auth_list', `auth_write`='$auth_write', `auth_view`='$auth_view', `auth_reply`='$auth_reply', `auth_comment`='$auth_comment', `auth_upload`='$auth_upload'";
		$board_sql .= ", `use_editor`='$use_editor', `use_sort`='$use_sort', `use_scallback`='$use_scallback', `use_mcallback`='$use_mcallback', `use_del`='$use_del',`use_edit`='$use_edit',`use_view`='$use_view', `use_reply`='$use_reply', `use_cate`='$use_cate', `use_comment`='$use_comment'";
		$board_sql .= ", `tag`='$tag', `hit_type`='$hit_type', `del_type`='$del_type',`writer_name`='$writer_name',`day_write`='$day_write',`day_comment`='$day_comment', `upfile_ext`='$upfile_ext', `upfile_size`='$upfile_size', `auto_secret`='$auto_secret'";
		$board_sql .= ", `date_type_list`='$date_type_list', `date_type_view`='$date_type_view', `date_type_user`='$date_type_user',`use_fsubject`='$use_fsubject', `fsubject`='$fsubject'";
		$board_sql .= ", `protect_name`='$protect_name', `protect_name_strlen`='$protect_name_strlen', `protect_name_suffix`='$protect_name_suffix'";
		$board_sql .= ", `auth_member`='$auth_member'";
		if($data[no]){
			$pdo->query("update `mari_config` set $board_sql where `no`='$data[no]'");
			$msg="설정이 완료되었습니다";
			$rURL = 'reload';
		}else{
			$pdo->query("insert into `mari_config` set $board_sql");
			$msg="게시판이 생성되었습니다";
			$rURL = '?body=board@board_new_list';

            // 작성자 표기 설정 통합 설정에 포함
            $scfg->import(array(
                'writer_name_bbs' => $scfg->get('writer_name_bbs').'@'.$db
            ));
		}
		msg('적용되었습니다.', $rURL, 'parent');
	}

?>