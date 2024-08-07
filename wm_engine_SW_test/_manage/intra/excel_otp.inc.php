<?php

/**
 * 엑셀 다운로드 인증
 **/

// 압축파일 생성 후 다운로드
function downloadArchive($file_path, $password = null)
{
    global $root_dir;

    $name = pathinfo($file_path, PATHINFO_FILENAME);

    $zip_path = $root_dir.'/_data/'.$name.'.zip';
    if (file_exists($zip_path) == true && is_writeable($zip_path) == true) {
        unlink($zip_path);
    }

    $zip = new ZipArchive;
    $zip->open($zip_path, ZipArchive::CREATE);
    $zip->addFile($file_path, basename($file_path));
    if ($password) {
        $zip->setPassword($password);
        $zip->setEncryptionName(basename($file_path), ZipArchive::EM_AES_256);
    }
    $zip->close();
    $zip_size = filesize($zip_path);

    // 다운로드 실행
    Header("Content-Type: file/unknown");
    Header("Content-Disposition: attachment; filename=".$name.'.zip');
    Header("Content-Length: ".$zip_size);
    header("Content-Transfer-Encoding: binary ");
    Header("Pragma: no-cache");
    Header("Expires: 0");
    flush();

    if($fp = fopen($zip_path, "r")) {
        echo fread($fp, $zip_size);
    }
    fclose($fp);

    // 파일 삭제
    unlink($file_path);
    unlink($zip_path);
}

// 엑셀 다운로드 보안 설정
switch($body) {
    case 'order@order_excel.exe';
        $page_type = 'oexcel';
        break;
    case 'member@member_excel.exe';
    case 'member@milage_excel.exe';
    case 'member@emoney_excel.exe';
        $page_type = 'mexcel';
        break;
    case 'order@order_cash_receipt_excel.exe';
        $page_type = 'cexcel';
        break;
}
$excel_auth_type = 'password';
if ($scfg->comp('use_'.$page_type.'_protect', 'Y') == true) {
    if ($scfg->comp($page_type.'_otp_method', 'sms') == true) $excel_auth_type = 'sms';
    else if ($scfg->comp($page_type.'_otp_method', 'mail') == true) $excel_auth_type = 'email';
} else {
    if ($page_type =='oexcel' || $page_type == 'cexcel') return; // 2차 인증 미 사용시 주문 엑셀은 그냥 통과
}

// 패스워드 확인 및 생성
$xls_down = $_REQUEST['xls_down'];
$rand = sprintf('%05d', rand(0,99999));

if ($excel_auth_type == 'password') { // 관리자 비밀번호 확인
    if (empty($xls_down) == true) {
        msg('비밀번호를 입력하세요.');
    }

	$openapi = comm($manage_url.'/main/exec.php?'.http_build_query(array(
		'exec_file' => 'api/openapi.exe.php',
		'action' => 'login',
		'mng_id' => $admin['admin_id'],
		'mng_pw' => $xls_down,
		'urlfix' => 'Y'
	)));
	$ret = json_decode($openapi);
	if (is_object($ret) == false || $ret->status != 'Y') {
		msg('비밀번호 인증이 실패하였습니다');
	}
} else if ($excel_auth_type == 'sms') { // 관리자 휴대폰 번호를 확인
    if (empty($xls_down) == true) {
        msg('휴대폰번호를 입력하세요.');
    }
	if (empty($admin['cell']) == true) {
		msg('관리자 휴대폰번호가 설정되어있지 않습니다.\\n최고 관리자를 통해 휴대폰번호를 등록해주시기 바랍니다.');
	}
	if (str_replace('-', '', $admin['cell']) != str_replace('-', '', $xls_down)) {
		msg('휴대폰번호 인증이 실패하였습니다.');
	}

	// 문자 발송
	include_once __ENGINE_DIR__.'/_engine/sms/sms_module.php';
	$sms_def_msg['manual'] = "[{$cfg['company_mall_name']}] 다운로드한 파일의 압축 해제 비밀번호는 [$rand] 입니다.";
	SMS_send_case('manual', $admin['cell']);
} else if ($excel_auth_type == 'email') { // 관리자 이메일 확인
    if (empty($xls_down) == true) {
        msg('이메일주소를 입력하세요.');
    }
	if (empty($admin['email']) == true) {
		msg('관리자 이메일주소가 설정되어있지 않습니다.\\n최고 관리자를 통해 이메일주소를 등록해주시기 바랍니다.');
	}
	if ($admin['email'] != $xls_down) {
		msg('이메일주소 인증이 실패하였습니다.');
	}

	// 이메일 발송
	$mail_case = 6;
	include __ENGINE_DIR__.'/_engine/include/mail.lib.php';

	$mail_title[6] = '엑셀 다운로드 비밀번호';

	$_mstr['메일내용'] = <<<CONTENT
    <h2 style="margin:0; padding:30px 0 0 0; color:#000000; font-family:'NanumBarunGothic', dotum, gulim, sans-serif; font-size:24px; font-weight:bold; line-height:160%;">인증번호 발송</h2>
    <p style="margin:0; padding:30px 0 0 0; color:#666666; font-family:'NanumBarunGothic', dotum, gulim, sans-serif; font-size:16px; font-weight:normal; line-height:160%;">안녕하세요. <strong style="color:#666666; font-family:'NanumBarunGothic', dotum, gulim, sans-serif; font-size:16px; line-height:160%;">{$cfg['company_mall_name']}</strong>입니다.</p><br>

    <p style="margin:0; padding:15px 0 0 0; color:#666666; font-family:'NanumBarunGothic', dotum, gulim, sans-serif; font-size:16px; font-weight:normal; line-height:160%;">다운로드한 파일의 압축 해제 비밀번호는 <strong style="color:#666666; font-family:'NanumBarunGothic', dotum, gulim, sans-serif; font-size:16px; line-height:160%;">$rand</strong> 입니다.<br>

    <p style="margin:0; padding:15px 0 0 0; color:#666666; font-family:'NanumBarunGothic', dotum, gulim, sans-serif; font-size:16px; font-weight:normal; line-height:160%;">감사합니다.</p>
CONTENT;
	sendMailContent($mail_case, $admin['name'], $admin['email']);
} else {
	msg('엑셀 다운로드 인증방법이 설정되어있지 않습니다.');
}

?>