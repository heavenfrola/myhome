<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  온라인 쿠폰 직접 다운로드
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";

	$no = numberOnly($_GET['no']);

	function checkAvailCpn($data) {
		global $now;
		$nowYmd=date("Ymd",$now);
		$r=0;
		if($data[rdate_type]==1 || ($data[rdate_type]==2 && numberOnly($data[rstart_date])<=$nowYmd && numberOnly($data[rfinish_date])>=$nowYmd)) {
			$r=1;
		}
		if($data['down_type'] == 'L' || $data['down_type'] == 'L2') $r = 0; // 로그인 시 다운로드 전용 제외
		return $r;
	}

	// 자동쿠폰 정리
	function trncAutoCpn() {
		global $tbl, $member, $pdo;

		if (!$member['no']) return false;
		$pdo->query("delete from {$tbl['coupon_download']} where ono='' and auto_cpn='Y' and member_no=:member_no", array(
            ':member_no' => $member['no']
        ));
	}

	if(!$member['no']) {
		exit(json_encode(array(
			'result' => 'error',
			'resultmsg' => 'login'
		)));
	}

	if(!$no) {
		exit(json_encode(array(
			'result' => 'error',
			'resultmsg' => __lang_common_error_required__
		)));
	}

	$data=get_info($tbl[coupon],"no",$no);

	trncAutoCpn();

	if(!$data[no] || checkAvailCpn($data)==0) {
		exit(json_encode(array(
			'result' => 'error',
			'resultmsg' => __lang_cpn_error_notIssue__
		)));
	} else {

		// 자동 쿠폰 여부
		if($data[auto_cpn]=="Y") {
			exit(json_encode(array(
				'result' => 'error',
				'resultmsg' => __lang_cpn_error_ordercpn__
			)));
		}

		// 발급한정
		if($data[release_limit]=="2" && $data[down_hit]>=$data[release_limit_ea]) {
			exit(json_encode(array(
				'result' => 'error',
				'resultmsg' => __lang_cpn_error_number__
			)));
		}

		// 1인당 다운로드가능여부
		if($data[download_limit]>1) {
			// 사용하지 않은 동일 쿠폰
			if($data[download_limit]==2) {
				$dl1=$pdo->row("select count(*) from `$tbl[coupon_download]` where `cno`='$data[no]' and `member_no`='$member[no]' and `member_id`='$member[member_id]' and `ono`=''");
				if($dl1>0) {
					exit(json_encode(array(
						'result' => 'error',
						'resultmsg' => __lang_cpn_error_aleradyIssued__
					)));
				}
			}
			elseif($data[download_limit]==3) {
				$dl2=$pdo->row("select count(*) from `$tbl[coupon_download]` where `cno`='$data[no]' and `member_no`='$member[no]' and `member_id`='$member[member_id]'");
				if($dl2>=$data[download_limit_ea]) {
					exit(json_encode(array(
						'result' => 'error',
						'resultmsg' => __lang_cpn_error_limitDN__
					)));
				}
			}
		}

		if($data[down_type]){
			if($data[down_type] == "B"){ //
				if($data[down_gradeonly] == "Y") {
					if($member[level] != $data[down_grade]) {
						exit(json_encode(array(
							'result' => 'error',
							'resultmsg' => sprintf(__lang_cpn_error_groupOnly__, getGroupName($data['down_grade']))
						)));
					}
				} else {
					if($member[level] > $data[down_grade]) {
						exit(json_encode(array(
							'result' => 'error',
							'resultmsg' => sprintf(__lang_cpn_error_groupOver__, getGroupName($data['down_grade']))
						)));
					}
				}
			}
		}
		if($data['down_type'] != 'A' && $data['down_type'] != 'B') {
			exit(json_encode(array(
				'result' => 'error',
				'resultmsg' => sprintf(__lang_cpn_error_notIssue__)
			)));
		}

		if($data['udate_type'] == 3 && $data['udate_limit']) {
			$data['ufinish_date']=date('Y-m-d', $now+($data['udate_limit']*86400));
		}


		// 발급 이력 체크
		putCoupon($data, $member);
		$pdo->query("update `$tbl[coupon]` set `down_hit`=`down_hit`+1 where `no`='$data[no]'");

		exit(json_encode(array(
			'result' => 'OK',
			'resultmsg' => __lang_cpn_info_downloaded__
		)));
	}

?>