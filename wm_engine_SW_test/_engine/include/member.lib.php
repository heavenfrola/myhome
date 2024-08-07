<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원가입/정보수정 라이브러리
	' +----------------------------------------------------------------------------------------------+*/

	function checkID($member_id) {
		global $tbl, $pdo;
		$blockID=array("admin","administrator","master","webmaster","wisa","test","tester"); // 등록 불가 아이디

		if(preg_match('/[A-Z]/', $member_id)) return 8;

		$member_id=strtolower($member_id);

		if(checkKorean($member_id)) return 1;

		if(strpos($member_id," ")) return 6;

        if(!preg_match('/[a-z]/i', $member_id)) return 7;

		if(strlen($member_id)<4 || strlen($member_id)>50) return 5;

        $id_check = $pdo->assoc("select no from {$tbl['member']} where member_id=?", array($member_id));
		if($id_check['no'] && !$_SESSION["sns_login"]["cid"]) return 2;

		if(in_array($member_id,$blockID)) return 4;
		if(checkNameFilter($member_id) == false) return 4;

		return 0;
	}

	function checkPwd($pwd) {
		global $cfg;

		checkBlank($pwd[0], __lang_member_input_pwd__);
		checkBlank($pwd[1], __lang_member_input_cpwd__);

		if($pwd[0]!=$pwd[1]) msg(__lang_member_error_cpwd__);

		if($cfg['password_engnum'] == 'Y') {
			if(!preg_match('/[0-9]/', $pwd[0]) || !preg_match('/[a-z]/i', $pwd[0])) {
				msg(__lang_member_error_pwd1__);
			}
		}

		if($cfg['password_special'] == 'Y') {
			if(!preg_match('/[^0-9a-z ]/i', $pwd[0])) {
				msg(__lang_member_error_pwd2__);
			}
		}

		if(!$cfg['password_min']) $cfg['password_min'] = 4;
		if($cfg['password_min'] >= 4 && strlen($pwd[0]) < $cfg['password_min']) {
			msg(sprintf(__lang_member_error_pwd3__, $cfg['password_min']));
		}

		if($cfg['password_max'] > 4 && strlen($pwd[0]) > $cfg['password_max']) {
			msg(sprintf(__lang_member_error_pwd4__, $cfg['password_max']));
		}

		$str=", `pwd`=password('".$pwd[0]."')";

		$pass=sql_password($pwd[0]);
		$str=", `pwd`='$pass'";
		return array($pass,$str);
	}

	// 주민 번호
	function checkRegNum($reginum) {
		$weight = '234567892345';
		$len = strlen($reginum);
		$sum = 0;

		if ($len <> 13) { return false; }

		for ($i = 0; $i < 12; $i++) {
			$sum = $sum + (substr($reginum,$i,1)*substr($weight,$i,1));
		}

		$rst = $sum%11;
		$result = 11 - $rst;

		if ($result == 10) {$result = 0;}
		else if ($result == 11) {$result = 1;}

		$jumin = substr($reginum,12,1);

		if ($result <> $jumin) {return false;}
		return true;
	}

	// 사업자 번호
	function checkBizNum($reginum) {
		$weight='137137135';
		$len=strlen($reginum);
		$sum=0;

		if ($len <> 10) return false;

		for ($i = 0; $i < 9; $i++) {
			$sum=$sum+(substr($reginum,$i,1)*substr($weight,$i,1));
		}
		$sum=$sum+((substr($reginum,8,1)*5)/10);
		$rst=$sum%10;

		if ($rst == 0) $result = 0;
		else $result = 10 - $rst;

		$saub = substr($reginum,9,1);

		if ($result <> $saub) return false;
		return true;
	}

	// 이름규칙
	function checkNameFilter($nm) {
		global $tbl, $pdo;

		$filter = array();
		$res = $pdo->iterator("select code, value from {$tbl['default']} where code like 'name_filter_%'");
        foreach ($res as $data) {
			if($data['value']) {
				$filter[$data['code']] = preg_replace('/\s*,\s*/', '|', preg_quote($data['value']));
			}
		}
		if($filter['name_filter_1']) { // 포함
			if(preg_match('/'.$filter['name_filter_1'].'/', $nm) == true) return false;
		}
		if($filter['name_filter_2']) { // 시작
			if(preg_match('/^'.$filter['name_filter_2'].'/', $nm) == true) return false;
		}
		if($filter['name_filter_3']) { // 끝
			if(preg_match('/'.$filter['name_filter_3'].'$/', $nm) == true) return false;
		}
		return true;
	}

	// 추가 정보
	function memberAddFrm($n) {
		global $_mbr_add_info,$member,$amember;

		$now_member=($amember['no']) ? $amember : $member;
		$data=$_mbr_add_info[$n];
		if(!$data) return;
		$r="<input type=\"hidden\"  name=\"add_info_style$n\" value=\"".$data['ncs']."::".$data['type']."::".$data['name']."\">";
		if($data['type']=="radio" || $data['type']=="checkbox") { // 2007-06-07 : 체크박스 추가 - Han
			foreach($data['text'] as $key=>$val) {
				$ck="";
				if(($now_member["add_info".$n]!="") && ((!strchr($now_member["add_info".$n],"@") && $now_member["add_info".$n]==$key) || strchr($now_member["add_info".$n],"@".$key."@"))) {
					$ck="checked";
				}
				$_info_name=($data['type'] == "checkbox") ? "add_info".$n."[]" : "add_info".$n; // 2007-06-07
				$r.="<input id='add_fd_{$n}_{$key}' type=\"$data[type]\" name=\"$_info_name\" id=\"add_info$n\" value=\"$key\" $ck> <label for='add_fd_{$n}_{$key}'>$val</label> ";
			}
		}
		elseif($data['type']=="select") {
			$r="<select name=\"add_info".$n."\">";
			$r.="<option value=\"\" $ck>:: 선택 ::</option>";
			foreach($data['text'] as $key=>$val) {
				$ck="";
				if($now_member["add_info".$n]!="" && $now_member["add_info".$n]==$key) {
					$ck="selected";
				}
				$r.="<option value=\"$key\" $ck>$val</option>";
			}
			$r.="</select>";
		}
		elseif($data['type']=="text") {
			$r.="<input type=\"text\" name=\"add_info".$n."\" value=\"".inputText($now_member["add_info".$n])."\" size=\"$data[size]\" class=\"input input_form form_input $data[class]\">";
		}
		elseif($data['type']=="textarea") {
			$r.="<textarea name=\"add_info".$n."\" class=\"$data[class]\">".stripslashes($now_member["add_info".$n])."</textarea>";
		}
		elseif($data['type']=="selectarray") {
			if($now_member["add_info".$n]) {
				$selected = explode('@' ,$now_member["add_info".$n]);
			}
			for($i = 1; $i <=3; $i++){
				if($i == 1)	{
					$day = "년";
					$select = "----";
				} else if($i == 2){
				    $day = "월";
					$select = "--";
				} else {
				    $day = "일";
					$select = "--";
				}
				$r.="<select style='width:109px' name=\"add_info".$n."[]\">";
				$r.="<option value=\"\" $ck>$select</option>";
				for($ii=date('Y'); $ii>=1900; $ii--) {
					$ck = '';
					if($selected[1] && $ii == $selected[1]) $ck = "selected";
					if($i == 1) $r.="<option value=\"$ii\" $ck>$ii</option>";
				}
				for($ii=1; $ii<=12; $ii++) {
					$ck = '';
					if($ii<10) $ii="0".$ii;
					if($selected[2] && $ii == $selected[2]) $ck = "selected";
					if($i == 2) $r.="<option value=\"$ii\" $ck>$ii</option>";
				}
				for($ii=1; $ii<=31; $ii++) {
					$ck = '';
					if($ii<10) $ii="0".$ii;
					if($selected[3] && $ii == $selected[3]) $ck = "selected";
					if($i == 3) $r.="<option value=\"$ii\" $ck>$ii</option>";
				}
				$r.="</select> $day ";
			}
		}
        else if ($data['type'] == 'file') {
            $accept = '';
            if (is_array($data['ext']) == true) {
                $accept = implode(',', array_map(function($str) {
                    return '.'.$str;
                }, $data['ext']));
            }
            $r .= "<input type=\"file\" name=\"add_info{$n}\" class=\"input\" accept=\"$accept\">";
            if (defined('_wisa_manage_edit_') == true) {
                $_updir = '_data/member/add_info'.$n;
                $_url = getListImgURL($_updir, $now_member['add_info'.$n]);
                $r .= "<a href='$_url' target='_blank'>".$now_member['add_info'.$n]."</a>";
            }
        }
		return $r;
	}

	function restoreDeleted($member_no) {
		global $tbl, $engine_dir, $erpListener, $pdo;

		$data = $pdo->assoc("select * from $tbl[member_deleted] where no='$member_no'");
		if(!$data['no']) return false;

		$r = $pdo->query("
			update $tbl[member] set
				name='$data[name]', email='$data[email]', phone='$data[phone]', cell='$data[cell]',
				zip='$data[zip]', addr1='$data[addr1]', addr2='$data[addr2]', birth='$data[birth]',
				sex='$data[gender]', milage='$data[milage]', emoney='$data[emoney]', withdraw='N'
			where no='$data[no]'
		");

		if($r) {
			$pdo->query("delete from $tbl[member_deleted] where no='$data[no]'");
			if($data['milage'] > 0) {
				include_once $engine_dir.'/_engine/include/milage.lib.php';
				expireMilage($data['member_id']);
			}
		}

		if(is_object($erpListener)) {
			$erpListener->setChangedMember($data['member_id'], $data['no']);
		}

		return $r;
	}

	// 아이디를 이메일로 사용 세팅시 테이블 전체의 member_id field 치환
	function updateMemberIdField($new_id, $old_id){
		global $con_info, $pdo;

		$res = $pdo->iterator("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME='member_id' and TABLE_SCHEMA='".$con_info[4]."'");
        foreach ($res as $data) {
			$sql = "update ".$data['TABLE_NAME']." set member_id='${new_id}' where member_id='${old_id}'";
			$r = $pdo->query($sql);
		}
		return $r;
	}

	// 회원  sms, email 수신여부 변경시 기록
	function setAdvInfoDate($member_no, $org_mailing, $org_sms, $is_admin = false) {
		global $tbl, $admin, $member, $now, $pdo;

		$member_no = numberOnly($member_no);
		$editor = ($is_admin == true) ? 'adm/'.$admin['admin_id'] : $member['member_id'];
		if(!$editor) $editor = addslashes($_POST['member_id']); // 신규가입시
		if(!$_POST['sms']) $_POST['sms'] = 'N';
		if(!$_POST['mailing']) $_POST['mailing'] = 'N';
		$asql = '';

		// db 마이그레이션
		if(!fieldExist($tbl['member'], 'mailing_chg_date')) {
			addField($tbl['member'], 'mailing_chg_date', 'int(10) not null default "0"');
			addField($tbl['member'], 'mailing_chg_id', 'varchar(50) not null default ""');
			addField($tbl['member'], 'sms_chg_date', 'int(10) not null default "0"');
			addField($tbl['member'], 'sms_chg_id', 'varchar(50) not null default ""');

			$pdo->query("update $tbl[member] set mailing_chg_date=reg_date, sms_chg_date=reg_date");
		}

		if($member_no < 1) return false;

		$mail_yn = "";
		$sms_yn = "";
		if($_POST['mailing'] != $org_mailing) {
			$mail_yn = "Y";
			$asql .= ", mailing_chg_date='$now', mailing_chg_id='$editor'";
		}
		if($_POST['sms'] != $org_sms) {
			$sms_yn = "Y";
			$asql .= ", sms_chg_date='$now', sms_chg_id='$editor'";
		}
		if($asql) {
			$asql = substr($asql, 1);
			$pdo->query("update $tbl[member] set $asql where no='$member_no'");
			return array($mail_yn,$sms_yn);
		}

		return false;
	}

	function putLoginCoupon($member, $option = '') {
		global $tbl, $pdo;

		if(!$member['no']) return false;

		$down_types = (isSmartApp() == false) ? "'L'" : "'L','L2'";
		$w = '';
		if($option && fieldExist($tbl['coupon'], 'cpn_option') == true) $w = " and cpn_option like '%@$option@%'"; // 관련 필드 생성 전에는 쿼리 오류 발생하나 무시

		$date = date('Y-m-d');
		$res = $pdo->iterator("select * from {$tbl['coupon']} where down_type in ($down_types) and (rdate_type=1 or (rdate_type=2 and rstart_date<='$date' and rfinish_date >= '$date')) $w");
        foreach ($res as $coupon) {
			$ret = putCoupon($coupon, $member);
			if($ret > 0  && $coupon['down_msg']) {
				$pdo->query("update {$tbl['coupon']} set down_hit=down_hit+1 where no='{$coupon['no']}'");

				if(is_array($_SESSION['cpn_message']) == false) $_SESSION['cpn_message'] = array();
				$_SESSION['cpn_message'][] = php2java($coupon['down_msg']);
			}
		}
	}

?>