<?php

/**
 * 배송 불가 지역 설정
 **/

if (!isset($admin['partner_no'])) $admin['partner_no'] = 0;
$partner_no = (int) $admin['partner_no'];

// 하위 주소 읽기
if ($_POST['exec'] == 'getAddr') {
    printAjaxHeader();

    $next_child = $_POST['next_child'];
    $sido = $_POST['sido'];
    $gugun = $_POST['gugun'];
    $dong = $_POST['dong'];

    exit(getAddr($next_child, $sido, $gugun, $dong));
}

// 정책 저장
if ($_POST['exec'] == 'setRange') {
    printAjaxHeader();

    $no = (int) $_POST['no'];

    if ($no > 0) {
        $pdo->query("
            update {$tbl['delivery_range']}
                set type=?, name=?, sido=?, gugun=?, dong=?, ri=?, reason=?
            where no=? and partner_no=?
        ", array(
            trim($_POST['type']), $_POST['range_name'], $_POST['sido'], $_POST['gugun'], $_POST['dong'], $_POST['ri'], trim($_POST['reason']), $no, $partner_no
        ));
    } else {
        $pdo->query("
            insert into {$tbl['delivery_range']}
                (type, name, sido, gugun, dong, ri, reason, reg_date, partner_no)
                values (?, ?, ?, ?, ?, ?, ?, now(), ?)
        ", array(
            trim($_POST['type']), $_POST['range_name'], $_POST['sido'], $_POST['gugun'], $_POST['dong'], $_POST['ri'], trim($_POST['reason']), $partner_no
        ));
    }

    require 'delivery_range.inc.php';
    exit;
}

// 정책 삭제
if ($_POST['exec'] == 'remove') {
    printAjaxHeader();

    if (is_array($_POST['no']) == false) {
        $_POST['no'] = array($_POST['no']);
    }

    $no = implode(',', numberOnly($_POST['no']));

    $pdo->query("delete from {$tbl['delivery_range']} where no in ($no) and partner_no='$partner_no'");

    require 'delivery_range.inc.php';
    exit;
}

// 정책 수정 폼 호출
if ($_POST['exec'] == 'modify') {
    header('Content-type:application/json; charset=utf-8;');

    $data = $pdo->assoc("select * from {$tbl['delivery_range']} where no=? and partner_no=?", array(
        (int) $_POST['no'], $partner_no
    ));
    $data = array_map('stripslashes', $data);

    $_dong = ($data['dong']) ? explode(',', $data['dong']) : array();
    $_ri = ($data['ri']) ? explode(',', $data['ri']) : array();
    $data['area'] = trim($data['sido'].' '.$data['gugun'].' '.$_dong[0]);
    if (!$data['gugun']) $data['area'] .= ' 전체';
    elseif (!$data['dong']) $data['area'] .= ' 전체';

    $data['sido_list'] = getAddr('sido', $data['sido']);
    $data['gugun_list'] = getAddr('gugun', $data['sido'], $data['gugun']);
    if($data['gugun']) $data['dong_list'] = getAddr('dong', $data['sido'], $data['gugun'], $_dong);
    if($data['dong']) $data['ri_list'] = getAddr('ri', $data['sido'], $data['gugun'], $_dong[0], $_ri);

    exit(json_encode($data));
}

// 엑셀 업로드
if ($_POST['exec'] == 'excelUpload') {
    header('Content-type: application/json;');

    $file = $_FILES['excel'];
    $success = 0;
    $type = $_POST['type'];

    if (getExt($file['name']) != 'csv') {
        msg('csv 형식의 파일만 업로드 가능합니다.');
    }

    $fp = fopen($file['tmp_name'], 'r');
    while ($data = fgetcsv($fp, 2048)) {
        foreach ($data as $key => $val) {
            $data[$key] = trim($val);
            $data[$key] = mb_convert_encoding($data[$key], _BASE_CHARSET_, 'euckr');
        }

        if ($data[0] == '번호') continue;
        if (!$data[1]) continue;
        if (!$data[6]) continue;

        $no = (int) $data[0];
        $name = trim($data[1]);
        $sido = $data[2];
        $gugun = $data[3];
        $dong = $data[4];
        $ri = $data[5];
        $reason = ($type == 'A') ? '' : trim($data[6]);

        if ($no > 0) {
            if ($pdo->row("select count(*) from {$tbl['delivery_range']} where no=? and type=? and partner_no=?", array($no, $type, $partner_no)) == 0) {
                $pdo->query("
                    insert into {$tbl['delivery_range']}
                        (type, name, sido, gugun, dong, ri, reason, reg_date, partner_no)
                        values (?, ?, ?, ?, ?, ?, ?, now(), ?)
                ", array(
                    $type, $name, $sido, $gugun, $dong, $ri, $reason, $partner_no
                ));
            } else {
                $pdo->query("
                    update {$tbl['delivery_range']}
                        set type=?, name=?, sido=?, gugun=?, dong=?, ri=?, reason=?
                    where no=? and partner_no=?
                ", array(
                    $type, $name, $sido, $gugun, $dong, $ri, $reason, $no, $partner_no
                ));
            }
        } else {
            $pdo->query("
                insert into {$tbl['delivery_range']}
                    (type, name, sido, gugun, dong, ri, reason, reg_date, partner_no)
                    values (?, ?, ?, ?, ?, ?, ?, now(), ?)
            ", array(
                $type, $name, $sido, $gugun, $dong, $ri, $reason, $partner_no
            ));
        }

        if ($pdo->lastRowCount() == 1) $success++;
    }

    if ($success == 0) msg('처리된 내역이 없습니다.');

    exit('{"status": "success"}');
}