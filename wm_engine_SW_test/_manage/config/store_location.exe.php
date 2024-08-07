<?PHP
/**
 * [매장지도] 프로세스 처리
 */

$exec = addslashes($_POST['exec']);
$no = numberOnly($_POST['no']);

if(!$exec) msg('잘못된 경로 입니다.');

if($exec == 'register') {
	$title = addslashes($_POST['title']);
	$cell = addslashes($_POST['cell']);
	$phone = addslashes($_POST['phone']);
	$owner = addslashes($_POST['owner']);
	$content = addslashes($_POST['content']);
	$zipcode = addslashes(del_html($_POST['zipcode']));
	$email = addslashes($_POST['email']);
	$addr1 = addslashes($_POST['addr1']);
	$addr2 = addslashes($_POST['addr2']);
	$sido = addslashes($_POST['sido']);
	$hidden = addslashes($_POST['hidden']);
	$stat = (int)$_POST['stat'];
	$icons = $_POST['icons'];
	$facility = $_POST['facility'];
	$_otype = addslashes($_POST['otype']);
	$_sono = numberOnly($_POST['sono']);

	checkBlank($title,"상호명을 입력해주세요.");
	checkBlank($owner,"대표자명 입력해주세요.");
	if ($cell != '' && !ctype_digit($cell)) {
		msg('휴대전화의 경우 숫자만 입력 가능 합니다.');
	}

	checkBlank($phone,"전화번호를 입력해주세요.");
	if ($phone != '' && !ctype_digit($phone)) {
		msg('전화번호의 경우 숫자만 입력 가능 합니다.');
	}
	// 이메일 형식 체크
	if (empty($email) == false) {
		if (preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $email) == false)  {
			msg('이메일 형식을 확인해주세요.');
		}
	}

	//오프라인 매장 중복 체크
	if(!$no) {
		$_mche = $pdo->row("select no from {$tbl['store_location']} where title=:title and owner=:owner and email=:email and phone=:phone and cell=:cell",
			array(
				':title'=>$title,
				':owner'=>$owner,
				':email'=>$email,
				':phone'=>$phone,
				':cell'=>$cell
			)
		);
		$no = $_mche;
	}

	$_form_count = count($_POST['shour']); //총 영업시간 개수
	if($_form_count>0) {
		$_check_list_arr = array();
		$_oseq = 0;
		foreach ($_POST['shour'] as $i => $sh) {
			$_oseq++;
			$_ono = $_POST['ono'][$i];
			$_shour = $_POST['shour'][$i];
			$_ehour = $_POST['ehour'][$i];
			$_week = $_POST['week' . $i];
			$_all_time = ($_POST['all_time'][$i] == 'Y') ? 'Y' : 'N';

			$_st = strtotime($_shour);
			$_et = strtotime($_ehour);

			if ($_otype == 'C') {
				if (!$_week) {
					msg($_oseq . '번째 요일을 선택해 주세요.');
				}
				foreach ($_week as $k => $v) {
					if ($i > 1) {
						if (in_Array($v, $_check_list_arr)) {
							msg($_oseq . '번째 중복 요일이 존재 합니다.');
						}
					}
					$_check_list_arr[] = $v;
				}
			}

			if ($_all_time == 'N') {
				if (!$_shour) msg($_oseq . '번째 영업 시작시간을 선택해 주세요.');
				if (!$_ehour) msg($_oseq . '번째 영업 마감시간을 선택해 주세요.');
			}

			if ($_st > $_et) msg($_oseq . '번째 시작 시간의 경우 마감 시간을 초과 할 수 없습니다.');

			//최초 입력 시 중복 체크
			if (!$_ono) {
				$_otcheck = $pdo->row("select no from {$tbl['store_operate_time']} where week=:week and sono=:sono", array(':week' => $_week_list, ':sono' => $_ono));
				if ($_otcheck) {
					continue;
				}
			}

			/* 운영시간 브레이크 타임 유효성 체크 Start */
			$break_shour = $_POST['break_shour' . $i];
			$break_ehour = $_POST['break_ehour' . $i];
			$_bseq = 0;
			foreach ($break_shour as $k => $shour) {
				$_bseq++;

				$_bst = strtotime($shour);
				$_bet = strtotime($break_ehour[$k]);

				if (!$shour) msg($_bseq . '번째브레이크 타임 시작시간을 선택해 주세요.');
				if (!$break_ehour[$k]) msg($_bseq . '번째 브레이크 타임 마감시간을 선택해 주세요.');

				if ($_bst > $_bet) {
					msg($_bseq . '번째 브레이크 시작 시간의 경우 마감 시간을 초과 할 수 없습니다.');
				}
				/* 운영시간 브레이크 타임 유효성 체크 END */
			}
		}
	}
	/* 영업 시간 유효성 체크 END  */

	//카카오 주소 API 요청
	$local = $_kakao_store_handler->kakaoRestApi('address', ['query' => $addr1 . ' ' . $addr2], 'json');
	$local = $local['documents'][0]['road_address'];

	if ((!$local['y'] || !$local['x'])) msg('정확한 주소값을 입력해 주세요.');

	if(is_array($icons)) {
		$icons = implode('@', $icons);
		$icons = preg_replace('/^@|@$/', '', $icons);
		$icons = '@'.$icons.'@';
	}

	if(is_array($facility)) {
		$facility = implode('@', $facility);
		$facility = '@'.$facility.'@';
	}

	$_sql_arr = array(
		'partner_no'=>$admin['partner_no'], //입점사
		'title' => $title, // 제목
		'cell' => $cell, //휴대폰
		'phone' => $phone, //전화번호
		'owner' => $owner, //대표자명
		'zipcode' => $zipcode, //우편번호
		'addr1' => $addr1, //주소1
		'addr2' => $addr2, //주소2
		'email' => $email, //이메일
		'content' => $content,//기타내용
		'lat' => $local['y'], // 위도
		'lng' => $local['x'], //경도
		'sido' => $_kakao_store_handler->convSidoFromDaum($local['region_1depth_name']),//시도
		'hidden' => $hidden, //노출 여부
		'stat' => $stat, // 상태
		'icons' => $icons, // 아이콘
		'facility'=>$facility //시설안내
	);

	// 이미지 용량 제한
	include_once $GLOBALS['engine_dir'].'/_config/set.upload.php';
	$upfile_img_size_limit[1] = $up_cfg['storeThum']['filesize'];
	$upfile_img_size_limit[2] = $up_cfg['storeMain']['filesize'];

	for($i=1; $i<=2; $i++ ) {
		if ($_FILES['upfile'.$i]) {
			if ($no) {
				$data = $pdo->assoc("select updir, upfile1 from {$tbl['partner_shop']} where no=?",array($no));
				$updir = $data['updir'];
			}

			$file = $_FILES['upfile'.$i];

			if ($file['size'] > 0) {
				if ($data['upfile'.$i]) {
					deletePrdImage($data, 1, 1);
				}

				if (!$updir) {
					$updir = $dir['upload'] . '/store/'.date('Ym', $now).'/'.date('d',$now);
					makeFullDir($updir);
					$_sql_arr['updir'] = $updir;
				}

				$up_filename = md5($file['name'].$now.$file['size']);
				$up_info = uploadFile($file, $up_filename, $updir, "jpg|gif|png", $upfile_img_size_limit[$i]);
				${'upfile'.$i} = $up_info[0];
				$_sql_arr['upfile'.$i] = ${'upfile'.$i};
			}
		}
		// 파일업로드
		$_file = array(
			'tmp_name' => $_FILES['upfile'.$i]['tmp_name'],
			'name' => $_FILES['upfile'.$i]['name'],
			'size' => $_FILES['upfile'.$i]['size'],
		);
		if(($_file['size'] > 0 ) || $_POST['delfile'.$i] == 'Y') {
			$data = $pdo->assoc("select updir, upfile1 from {$tbl['partner_shop']} where no=?",array($no));
			$updir = $data['updir'];

			if($data['upfile'.$i]) deleteAttachFile($data['updir'], $data['upfile'.$i]);
			if(!$_file['size']) {
				$_sql_arr['upfile'.$i] = '';
			}
		}
	}

	$_where = array();

	if ($no) {
		$_sql_arr['edit_id'] = $admin['admin_id'];
		$_sql_arr['edit_date'] = $now;
		$_where['no'] = $no;
	} else {
		$_sql_arr['ip'] = $_SERVER['REMOTE_ADDR'];
		$_sql_arr['admin_id'] = $admin['admin_id'];
		$_sql_arr['reg_date'] = $now;
	}

	//쿼리 병합
	$_mqry = qryResult($_sql_arr, $_where);

	//수정 시
	if ($no) {
		$subject = "수정";
		$msql = "update {$tbl['store_location']} set " . $_mqry['u'] . " where " . $_mqry['w'];
	} else { // 추가
		$subject = "추가";
		$msql = "INSERT INTO {$tbl['store_location']} (" . $_mqry['i'] . ") VALUES (" . $_mqry['v'] . ")";
	}

	$r = $pdo->query($msql, $_mqry['a']);
	$_no = ($no) ? $no:$pdo->lastInsertId();

	/* 영업 시간 유효성 체크  */
	if (!$_otype) {
		//영업주기 없음 선택 시 모두 삭제
		$pdo->query("delete from {$tbl['store_operate']} where no=:no", array(':no' => $_sono));
		$pdo->query("delete from {$tbl['store_operate_time']} where sono=:sono", array(':sono' => $_sono));
		$pdo->query("delete from {$tbl['store_operate_break']} where sono=:sono", array(':sono' => $_sono));
	}

	if($_form_count>0) {
		$_check_list_arr = array();
		$_oseq = 0;
		$_operate_total_list = $_POST['shour'];

		foreach ($_operate_total_list as $i => $sh) {
			$_oseq++;
			$_ono = $_POST['ono'][$i];
			$_shour = $_POST['shour'][$i];
			$_ehour = $_POST['ehour'][$i];
			$_buse = $_POST['buse'][$i];
			$_week = $_POST['week' . $i];
			$_all_time = ($_POST['all_time'][$i] == 'Y') ? 'Y' : 'N';

			$_st = strtotime($_shour);
			$_et = strtotime($_ehour);

			if ($_otype == 'C') {
				if (!$_week) {
					msg($_oseq . '번째 요일을 선택해 주세요.');
				}
				foreach ($_week as $k => $v) {
					if ($i > 1) {
						if (in_Array($v, $_check_list_arr)) {
							msg($_oseq . '번째 중복 요일이 존재 합니다.');
						}
					}
					$_check_list_arr[] = $v;
				}
			}

			if ($_all_time == 'N') {
				if (!$_shour) msg($_oseq . '번째 영업 시작시간을 선택해 주세요.');
				if (!$_ehour) msg($_oseq . '번째 영업 마감시간을 선택해 주세요.');
			}

			if ($_st > $_et) msg($_oseq . '번째 시작 시간의 경우 마감 시간을 초과 할 수 없습니다.');

			//최초 입력 시 중복 체크
			if (!$_ono) {
				$_otcheck = $pdo->row("select no from {$tbl['store_operate_time']} where week=:week and sono=:sono", array(':week' => $_week_list, ':sono' => $_ono));
				if ($_otcheck) {
					continue;
				}
			}

			/* 운영시간 브레이크 타임 유효성 체크 Start */
			$break_shour = $_POST['break_shour'.$i];
			$break_ehour = $_POST['break_ehour'.$i];
			$_bseq = 0;
			foreach ($break_shour as $k => $shour) {
				$_bseq++;

				$_bst = strtotime($shour);
				$_bet = strtotime($break_ehour[$k]);

				if (!$shour) msg($_bseq . '번째브레이크 타임 시작시간을 선택해 주세요.');
				if (!$break_ehour[$k]) msg($_bseq . '번째 브레이크 타임 마감시간을 선택해 주세요.');

				if ($_bst > $_bet) {
					msg($_bseq . '번째 브레이크 시작 시간의 경우 마감 시간을 초과 할 수 없습니다.');
				}
				/* 운영시간 브레이크 타임 유효성 체크 END */
			}
		}
		/* 영업 시간 유효성 체크 END  */

		/* 영업 시간 설정 프로세스 */
		if ($_sono) {
			$pdo->query("update {$tbl['store_operate']} set  otype=:otype, edit_date=:edit_date, edit_id=:edit_id where no=:no",
				array(':otype' => $_otype, ':edit_date' => $now, ':edit_id' => $admin['admin_id'], ':no' => $_sono));
		} else {
			$pdo->query("insert into {$tbl['store_operate']} ( sno, otype, reg_date, admin_id, ip ) values (:sno, :otype, :reg_date, :admin_id, :ip)",
				array(':sno' => $_no, ':otype' => $_otype, ':reg_date' => $now, ':admin_id' => $admin['admin_id'], ':ip' => $_SERVER['REMOTE_ADDR'])
			);
			$_sono = $pdo->lastInsertId();
		}

		//영업주기 변경 시 삭제
		$_otche = $pdo->row("select no from {$tbl['store_operate_time']} where sono=:sno and otype=:otype",
			array(':sno' => $_sono, ':otype' => $_otype));

		if (!$_otche) {
			$ot = $pdo->query("delete from {$tbl['store_operate_time']} where sono=:sno ", array(':sno' => $_sono));
			$pdo->query("delete from {$tbl['store_operate_break']} where sono=:sno", array(':sno' => $_sono));
		}
		/* 영업 시간 설정 프로세스 END */

		/* 영업 시간 */
		$_check_list_arr = $_otime_delete = array();
		$_oseq = 0;
		$_operate_total_list = $_POST['shour'];

		foreach ($_operate_total_list as $i => $sh) {
			$_oseq++;
			$_ono = $_POST['ono'][$i];
			$_shour = $_POST['shour'][$i];
			$_ehour = $_POST['ehour'][$i];
			$_buse = $_POST['buse'][$i];
			$_week = $_POST['week' . $i];
			$_all_time = ($_POST['all_time'][$i] == 'Y') ? 'Y' : 'N';

			if ($_otype == 'C') {
				$_week_list = implode(',', $_week);
			} else {
				$_week_list = $_operate_otype_week_config[$_otype][$i];
			}

			//최초 입력 시 중복 체크
			if (!$_ono) {
				$_otcheck = $pdo->row("select no from {$tbl['store_operate_time']} where week=:week and sono=:sono", array(':week' => $_week_list, ':sono' => $_ono));
				if ($_otcheck) {
					continue;
				}
			}

			$_value_arr = array(
				'week' => $_week_list,
				'sono' => $_sono,
				'otype' => $_otype,
				'shour' => $_shour,
				'ehour' => $_ehour,
				'buse' => $_buse,
				'all_time' => $_all_time
			);
			$_where = array();

			if ($_ono) {
				//수정
				$_where['no'] = $_ono;
				$_value_arr = array_merge($_value_arr,
					array(
						'edit_date' => $now,
						'edit_id' => $admin['admin_id']
					)
				);
			} else {
				//작성
				$_value_arr = array_merge($_value_arr,
					array(
						'reg_date' => $now,
						'admin_id' => $admin['admin_id'],
						'ip' => $_SERVER['REMOTE_ADDR']
					)
				);
			}

			$_mqry = qryResult($_value_arr, $_where);

			if ($_ono) {
				$pdo->query("update {$tbl['store_operate_time']} set  {$_mqry['u']} where {$_mqry['w']}", $_mqry['a']);
			} else {
				$pdo->query("insert into {$tbl['store_operate_time']} ( {$_mqry['i']} ) values ( {$_mqry['v']} )", $_mqry['a']);
				$_ono = $pdo->lastInsertId();
			}
			$_otime_delete[] = $_ono;

			/* 운영시간 브레이크 타임 설정 프로세스 Start */
			$break_shour = $_POST['break_shour' . $i];
			$break_ehour = $_POST['break_ehour' . $i];
			$ob_no = $_POST['ob_no' . $i];

			$_break_delete = array();
			$_bseq = 0;
			foreach ($break_shour as $k => $shour) {
				$_bseq++;
				$_value_arr = array(
					'shour' => $shour,
					'ehour' => $break_ehour[$k],
				);

				$_where = array();
				$_ob_no = $ob_no[$k];
				if ($ob_no[$k]) {
					//수정
					$_where['no'] = $_ob_no;
					$_value_arr = array_merge($_value_arr,
						array(
							'edit_date' => $now,
							'edit_id' => $admin['admin_id']
						)
					);

				} else {
					//작성
					$_value_arr = array_merge($_value_arr,
						array(
							'sono' => $_sono,
							'stno' => $_ono,
							'reg_date' => $now,
							'admin_id' => $admin['admin_id'],
							'ip' => $_SERVER['REMOTE_ADDR']
						)
					);
				}

				$_mqry = qryResult($_value_arr, $_where);

				if ($_ob_no) {
					$pdo->query("update {$tbl['store_operate_break']} set  {$_mqry['u']} where {$_mqry['w']}", $_mqry['a']);
				} else {
					$pdo->query("insert into {$tbl['store_operate_break']} ( {$_mqry['i']} ) values ( {$_mqry['v']} )", $_mqry['a']);
					$_ob_no = $pdo->lastInsertId();
				}

				$_break_delete[] = $_ob_no;
			}

			$_ob_w = "";
			if ($_break_delete) $_ob_w = "and no not in(" . implode(',', $_break_delete) . ")";
			$pdo->query("delete from {$tbl['store_operate_break']} where stno=:stno $_ob_w", array(':stno' => $_ono));
			/* 운영시간 브레이크 타임 설정 프로세스 End */
		}
		if ($_otime_delete[0]) $pdo->query("delete from {$tbl['store_operate_time']} where sono=:sono and no not in(" . implode(',', $_otime_delete) . ")", array(':sono' => $_sono));
		/* 영업 시간 END */
	}

	msg($subject." 되었습니다.", '?body=config@store_location','parent');
} else if($exec == 'remove') {

	$_no = implode(',', $no);
	$pdo->query("delete from {$tbl['store_location']} where no in ($_no)");
	foreach($no as $k =>$_sno) {

		$_sono = $pdo->row("select no from {$tbl['store_operate']} where sno=:sno", array(':sno' => $_sno));

		$pdo->query("delete from {$tbl['store_operate']} where sno=:sno", array(':sno' => $_sno));
		$pdo->query("delete from {$tbl['store_operate_time']} where sono=:sono", array(':sono' => $_sono));
		$pdo->query("delete from {$tbl['store_operate_break']} where sono=:sono", array(':sono' => $_sono));
	}
}

?>

