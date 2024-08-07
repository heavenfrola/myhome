<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  쇼핑몰관리권한 설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	adminCheck(2);

	checkBasic();

	$exec = $_POST['exec'];
	$no  = numberOnly($_POST['no']);

	$auth = $_POST['auth'];
	$mng_no = $_POST['mng_no'];
	$ck_menu = $_POST['ck_menu'];
	$code_name = addslashes($_POST['code_name']);

    if (isTable($tbl['mng_auth_log']) == false) {
        include __ENGINE_DIR__.'/_config/tbl_schema.php';
        $pdo->query($tbl_schema['mng_auth_log']);
    }

    if (fieldExist('target_no', $tbl['mng_auth_log']) == false) {
        addField($tbl['mng_auth_log'], 'target_no', 'int(10) not null');
        addField($tbl['mng_auth_log'], 'target_id', 'varchar(50) not null');
        addField($tbl['mng_auth_log'], 'remote_addr', 'varchar(15) not null');
    }

	// 메뷰별 세부 권한 설정
	if($exec == "auth_detail"){
		checkBlank($code_name, "메뉴를 입력해주세요.");
		checkBlank($no, "부관리자를 입력해주세요.");
		$data = $pdo->assoc("select * from `$tbl[mng]` where `no`='$no' limit 1");
		if(!$data[no]) msg("존재하지 않는 정보입니다", "close");
		if(count($auth) < 1) msg("모든 하부 메뉴에 접근을 차단하시려면 메뉴 차단을 사용하시기 바랍니다");

		addField($tbl['mng_auth'], 'customer', 'varchar(255)');
		addField($tbl['mng_auth'], 'wing', 'varchar(255)');

        // 변경 전 데이터
        $mng = $pdo->assoc("select no, admin_id, auth from {$tbl['mng']} where no=?", array($no));
        $_old_auth = explode('@', trim($mng['auth'], '@'));
        $_old_auth_det = $pdo->row("select `$code_name` from {$tbl['mng_auth']} where admin_no=?", array($no));
        $_old_auth_det = explode('@', trim($_old_auth_det, '@'));

		$_auth="@";
		for($ii=0; $ii<count($auth); $ii++){
			$_auth .= $auth[$ii]."@";
		}
		$_auth = addslashes($_auth);
		$auth_data=$pdo->row("select `no` from `$tbl[mng_auth]` where `admin_no`='$no' limit 1");
		if($auth_data){
			$sql="update `$tbl[mng_auth]` set `$code_name`='$_auth' where `admin_no`='$no' limit 1";
		}else{
			$sql="insert into `$tbl[mng_auth]` (`admin_no`, `$code_name`, `mod_date`) values('$no', '$_auth', '$now')";
		}
		if(!fieldExist($tbl['mng_auth'], $code_name)) {
			addField($tbl['mng_auth'], $code_name, 'varchar(255)');
		}
		$r=$pdo->query($sql);
		if(!$r) msg("권한설정이 실패하였습니다. 1:1고객센터 문의 글로 접수 바랍니다.");

		if(!@strchr($data[auth], "@auth_detail")){
			$data[auth] .= "@auth_detail";
		}

		if($code_name == "order"){
            // 카드 취소 권한
			$auth_cardcc = $_POST['auth_cardcc'];
			if($auth_cardcc == "Y"){
				if(!@strchr($data[auth], "@cardcc")) $data[auth] .= "@cardcc";
			}else{
				$data[auth]=str_replace("@cardcc", "", $data[auth]);
			}

            // 주문 엑셀 다운로드 권한
			$auth_orderexcel = $_POST['auth_orderexcel'];
            if ($auth_orderexcel) {
                $data['auth'] .= '@auth_orderexcel';
            } else {
				$data['auth'] = str_replace('@auth_orderexcel', '', $data['auth']);
            }
		}

		if ($code_name == 'member'){
            // 회원 엑셀 다운로드 권한
			$auth_memberexcel = $_POST['auth_memberexcel'];
            if ($auth_memberexcel) {
                $data['auth'] .= '@auth_memberexcel';
            } else {
				$data['auth'] = str_replace('@auth_memberexcel', '', $data['auth']);
            }
		}

		// 윙Disk 접속 권한
		if($code_name == "product"){
			if($auth_wftp == "Y"){
				if(!@strchr($data[auth], "@wftp")) $data[auth] .= "@wftp";
			}else{
				$data[auth]=str_replace("@wftp", "", $data[auth]);
			}
		}
		$pdo->query("update `$tbl[mng]` set `auth`='".$data[auth]."' where `no`='$no' limit 1");

        // 변동사항 체크
        $_auth_det = array_merge(explode('@', trim($_auth, '@')), explode('@', trim($data['auth'], '@')));
        $_old_auth_det = array_merge($_old_auth_det, $_old_auth);
        $auth_d1 = array_diff($_old_auth_det, $_auth_det); // 제거되는 권한
        $auth_d1 = implode('@', $auth_d1);
        $auth_d2 = array_diff($_auth_det, $_old_auth_det); // 추가되는 권한
        $auth_d2 = implode('@', $auth_d2);
        if (empty($auth_d1) == false || empty($auth_d2) == false) {
            $pdo->query("
                insert into {$tbl['mng_auth_log']}
                (admin_no, admin_id, target_no, target_id, category, auth1, auth2, auth_d1, auth_d2, remote_addr, reg_date)
                values
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now())
            ", array(
                $admin['no'], $admin['admin_id'], $mng['no'], $mng['admin_id'], $code_name, '', '', $auth_d1, $auth_d2, $_SERVER['REMOTE_ADDR']
            ));
        }
	}else{
		$ck_menu = array('main'=>0000, 'cardcc'=>0001);
		foreach($menudata->big as $key => $val) {
			$ck_menu[$val->attr('category')] = $val->attr('pgcode');
		}

		if(count($mng_no)){
			foreach($mng_no as $mkey=>$mval){
                // 변경 전 데이터
                $mng = $pdo->assoc("select no, admin_id, auth from {$tbl['mng']} where no=?", array($mval));
                $_old_auth = explode('@', trim($mng['auth'], '@'));

				$auth="";
				foreach($ck_menu as $key=>$val){
					if($val == 11000) continue; // 인트라넷제외
					if($_POST["auth_".$key][$mval] == "Y") $auth .= "@".$key;
				}
				$detail_chk=$pdo->row("select `auth` from `$tbl[mng]` where `no`='$mval' limit 1");
				if(strchr($detail_chk, "@auth_detail")) $auth .= "@auth_detail";
				if(strchr($detail_chk, "@cardcc")) $auth .= "@cardcc";
				if(strchr($detail_chk, "@auth_orderexcel")) $auth .= "@auth_orderexcel";
				if(strchr($detail_chk, "@auth_memberexcel")) $auth .= "@auth_memberexcel";
				if(strchr($detail_chk, "@wftp")) $auth .= "@wftp";
				$pdo->query("update `$tbl[mng]` set `auth`='$auth' where `no`='$mval' limit 1");

                // 변동사항 체크
                $_auth = explode('@', trim($auth, '@'));
                $auth1 = array_diff($_old_auth, $_auth); // 제거되는 권한
                $auth1 = implode('@', $auth1);
                $auth2 = array_diff($_auth, $_old_auth); // 추가되는 권한
                $auth2 = implode('@', $auth2);
                if (empty($auth1) == false || empty($auth2) == false) {
                    $pdo->query("
                        insert into {$tbl['mng_auth_log']}
                        (admin_no, admin_id, target_no, target_id, category, auth1, auth2, auth_d1, auth_d2, remote_addr, reg_date)
                        values
                        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now())
                    ", array(
                        $admin['no'], $admin['admin_id'], $mng['no'], $mng['admin_id'], '', $auth1, $auth2, '', '', $_SERVER['REMOTE_ADDR']
                    ));
                }
			}
		}
	}

	msg("설정되었습니다","reload","parent");

?>