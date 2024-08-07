<?PHP

	include_once 'content_list.php';

	$_cate = array();
	$res = $pdo->iterator("select no, name from mari_cate");
    foreach ($res as $data) {
		$_cate[$data['no']] = stripslashes($data['name']);
	}

    $ExcelWriter = setExcelWriter();

    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'widths' => array()
    );
    $widths = array(
        'addr1' => 40,
        'addr2' => 40,
        'title' => 40,
        'content' => 50,
    );

	if($_GET['mng'] == 2) {
        $col_list = array(
            'no' => '글번호',
            'subject' => '글제목',
            'member_id' => '아이디',
            'name' => '작성자명',
            'group_name' => '회원등급',
            'phone' => '전화번호',
            'cell' => '휴대폰번호',
            'zip' => '우편번호',
            'addr1' => '주소',
            'addr2' => '상세주소',
            'content' => '본문',
            'ip' => '작성아이피',
            'reg_date' => '작성일시'
        );
	} else {
        $col_list = array(
			'no' => '글번호',
            'db' => '게시판명',
            'cate' => '분류',
            'member_id' => '아이디',
            'name' => '작성자명',
            'group_name' => '회원등급',
            'phone' => '전화번호',
            'cell' => '휴대폰번호',
            'zip' => '우편번호',
            'addr1' => '주소',
            'addr2' => '상세주소',
            'title' => '제목',
            'content' => '본문',
            'hit' => '조회수',
            'ip' => '작성아이피',
            'reg_date' => '작성일시',
            'secret' => '비밀글',
            'notice' => '공지',
            'upfile1' => '첨부파일1',
            'upfile2' => '첨부파일2',
            'link1' => '링크1',
            'link2' => '링크2'
		);
	}
    foreach ($col_list as $key => $val) {
        $headerType[$val] = 'string';
        $headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 20;
    }
    $file_name = '게시판목록';
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

	$res = $pdo->iterator($sql);
    foreach ($res as $data) {
		$data = array_map('stripslashes', $data);
		if($data['upfile1']) $data['upfile1'] .= $data['up_dir'].'/'.$data['upfile1'];
		if($data['upfile2']) $data['upfile2'] .= $data['up_dir'].'/'.$data['upfile2'];

		$data['member'] = $data['group_name'] = array();
		if($data['member_no'] > 0) {
			$data['member'] = $pdo->assoc("select phone, cell, zip, addr1, addr2, total_ord, total_prc, level from $tbl[member] where no='$data[member_no]'");
			$data['group_name'] = getGroupName($data['member']['level']);
		} else {
            $data['group_name'] = '비회원';
        }
        $data['phone'] = $data['member']['phone'];
        $data['cell'] = $data['member']['cell'];
        $data['zip'] = $data['member']['zip'];
        $data['addr1'] = $data['member']['addr1'];
        $data['addr2'] = $data['member']['addr2'];
        $data['reg_date'] = date('Y-m-d H:i:s', $data['reg_date']);
		if($_GET['mng'] == 2) {
			$data['subject'] = stripslashes($pdo->row("select title from mari_board where no='$data[ref]'"));
		} else {
            $data['db'] = $board[$data['db']];
            $data['cate'] = $_cate[$data['cate']];
		}
        $row = array();
        foreach ($col_list as $key => $val) {
            $row[] = $data[$key];
        }
        $ExcelWriter->writeSheetRow($row);
        unset($row);
	}
    $ExcelWriter->writeFile();
