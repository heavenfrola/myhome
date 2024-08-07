<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  매장분류 관리
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	$wmode = $_POST['wmode'];
    $parent = $_POST['parent'];

	// 카테고리 리로드
	if($wmode == "reload") {
		$ctype = numberOnly($_POST['ctype']);
		$no = numberOnly($_POST['no']);
		$execmode = $_POST['execmode'];

		ob_start();
		print_cat();
		$tree = ob_get_contents(); // 버퍼 내용을 저장
		ob_end_clean();

		echo($tree);
	}


	// 카테고리 정렬
	if($wmode == "sort") {
		$cat_list = Preg_replace('/^@/', '', $_POST['cat_list']);
		$cat_list = numberOnly(explode('@', $cat_list));
		$cnt = count($cat_list);

		for($i = 0; $i < $cnt; $i++) {
			$pdo->query("update `$tbl[category]` set `sort` = '$i' where `no` = '$cat_list[$i]'");
		}

		javac("
			parent.moveCat($parent);
			parent.reloadTree($parent);
		");
		exit;
	}

	// 카테고리 저장
	include_once $engine_dir.'/board/include/lib.php';

	addField($tbl['category'], 'no_buy_msg', 'varchar(255) not null default ""');
	addField($tbl['category'], 'updir', 'varchar(50) not null default ""');
	addField($tbl['category'], 'upfile1', 'varchar(100) not null default ""');

	$parent = numberOnly($_POST['parent']);
	$cno = $_POST['cno'];
	$no_buy_msg = $_POST['no_buy_msg'];
	$ctype = numberOnly($_POST['ctype']);
	$parent = numberOnly($_POST['parent']);

	neko_lock($_POST['neko_id']);
	if(is_array($cno) == false || count($cno) == 0) {
		$cno = array(0);
	}

	$cnt = 0;
	foreach($cno as $val) {
		$asql1 = $asql2 = $asql3 = '';

		$_name = addslashes(trim($_POST['name'][$cnt]));
		$_hidden = ($_POST['hidden'][$cnt] == 'Y') ? 'Y' : 'N';
		$_use_top = ($_POST['use_top'][$cnt] == 'Y') ? 'Y' : 'N';
		$_prd_type = numberOnly($_POST['prd_type'][$cnt]);
		$_cols = numberOnly($_POST['cols'][$cnt]);
		$_rows = numberOnly($_POST['rows'][$cnt]);
		$_template = addslashes(trim($_POST['template'][$cnt]));
		$_content2 = addslashes(trim($_POST['content2'][$cnt]));
		$_cut_title = numberOnly($_POST['cut_title'][$cnt]);
		$_code = addslashes(trim($_POST['code'][$cnt]));
		$_access_limit = $_POST['access_limit'][$cnt];
		$_no_access_page = addslashes(del_html($_POST['no_access_page'][$cnt]));
		$_no_access_msg = addslashes(del_html($_POST['no_access_msg'][$cnt]));
		$_no_buy_msg = addslashes(del_html($_POST['no_buy_msg'][$cnt]));
		$_private = ($_POST['private'][$cnt] == 'Y') ? 'Y' : 'N';
		if(!$_prd_type) $_prd_type = 1;
		if(!$_ctype) $_cype = 1;
		if(!$_cols) $_cols = 4;
		if(!$_rows) $_rows = 4;

		checkBlank($_name, '분류명을 입력해주세요.');

		$_access_member = '';
		if($_access_limit == 2 || $_access_limit == 3) {
			if(count($_POST['access_member'][$cnt]) == 0) {
				$_access_member .= '@1';
			} else {
				foreach($_POST['access_member'][$cnt] as $key => $mem) {
					$_access_member .= '@'.$mem;
				}
			}
			$_access_member .= '@';
			if($_access_limit == 3) $_access_member = 'buy'.$_access_member;
		}

		// 파일업로드
		$_file = array(
			'tmp_name' => $_FILES['upfile1']['tmp_name'][$cnt],
			'name' => $_FILES['upfile1']['name'][$cnt],
			'size' => $_FILES['upfile1']['size'][$cnt],
		);
		if(($_file['size'] > 0 && $val > 0) || $_POST['delfile1'] == 'Y') {
			$data = $pdo->assoc("select updir, upfile1 from $tbl[category] where no='$val'");
			$updir = $data['updir'];

			if($data['upfile1']) deleteAttachFile($data['updir'], $data['upfile1']);
			if(!$_file['size']) {
				$asql3 .= ", upfile1=''";
			}
		}

		if($_file['size'] > 0) {
			if(!$updir) {
				$updir = $dir['upload'].'/'.$dir['product'].'/category';
				makeFullDir($updir);
				$asql3 .= ", updir='$updir'";
			}

			$up_info = uploadFile($_file, md5($val+time()+1), $updir, 'jpg|jpeg|gif|png');
			$asql3 .= ", upfile1='$up_info[0]'";
		}

		if($val) {
			$r = $pdo->query("
				update $tbl[category] set
					name='$_name', hidden='$_hidden', access_member='$_access_member',
					cols='$_cols', `rows`='$_rows', template='$_template', use_top='$_use_top', top_prd='$_top_prd', cut_title='$_cut_title',
					prd_type='$_prd_type', code='$_code', no_access_page='$_no_access_page', no_access_msg='$_no_access_msg', no_buy_msg='$_no_buy_msg',
					private='$_private', add_cont1='$_content2' $asql3
				where no='$val'
			");
			if($r) {
				if($cont_copy) {
					$pr_name = $_cate_colname[1][$level];
					$pdo->query("update $tbl[category] set `add_cont1='$_content2' where $pr_name='$val'");
				}
				$modno++;
			}

			javac("parent.document.getElementById('name_$val').innerText = '$_name'");
		} else {
			if($parent > 0) {
				$prdata = $pdo->assoc("select * from $tbl[category] where no='$parent'");
				$level = $prdata['level']+1;
				for($i = 1; $i <= $prdata['level']; $i++) {
					$fdname = $_cate_colname[1][$i];
					$fval = ($prdata['level'] == $i) ? $prdata['no'] : $prdata[$_cate_colname[1][($i)]];
					$asql1 .= ", $fdname";
					$asql2 .= ", '$fval'";
				}
    			$pcode = $_cate_colname[1][($level-1)];
			} else {
				$prdata = 1;
				$level = 1;
                $pcode = 'big';
			}

			$sort = $pdo->row("select max(sort)+1 from $tbl[category] where $pcode='$parent'");
			$no = $pdo->row("select max(no) from $tbl[category]");
			if(!$no) $no = 1000;
			$no++;

			$r = $pdo->query("insert into $tbl[category] (no, name, ctype, level, sort $asql1) values ('$no', '$_name', '$ctype', '$level', '$sort' $asql2)");
			if($r) {
				if(!$_code) $pdo->query("update $tbl[category] set code='$no' where no='$no'"); // code 미입력시 자동생성
				$temp = "
					<img id='ic_$no' src='$engine_url/_manage/image/icon/ic_plus.gif' onClick='open_cat($no)' class='pointer'>
					<img id='folder_$no' src='$engine_url/_manage/image/icon/ic_folder_c.gif'>
					<a href='javascript:moveCat($no)' id='name_$no' title='CODE:$no'>$_name</a>
				";
				$temp = php2java($temp);
				javac("parent.insertcatIcon($no, $parent, \"$temp\");");
			}
		}
		$cnt++;
	}

	if($modno > 0) {
		javac("
			window.alert('$modno 건의 분류가 수정되었습니다.');
			parent.moveCat($parent);
		");
	}

?>