<?PHP

/* +----------------------------------------------------------------------------------------------+
' |  파비콘 설정 처리
' +----------------------------------------------------------------------------------------------+*/

include_once __ENGINE_DIR__.'/_engine/include/file.lib.php';

checkBasic();

// 파일 체크
if (!$_FILES['icon']['size']) {
    msg('업로드할 파일을 선택해주세요.');
}

$ext = getExt($_FILES['icon']['name']);
if ($ext != 'ico' && $ext != 'png') {
    msg('ico 또는 png파일만 업로드할 수 있습니다.');
}

// 기존 파일 삭제
$favicon = $scfg->get('favicon');
if ($favicon && $favicon != 'Y') {
    deletePrdImage(array(
        'updir' => '_data/favicon',
        'upfile1' => $favicon
    ), 1, 1);
}

// 신규 파일 업로드
$name = 'favicon_'.time();
makeFullDir('_data/favicon');
$ret = uploadFile($_FILES['icon'], $name, '_data/favicon');
$_POST['favicon'] = $ret[0];

// 설정 저장
$no_reload_config=1;
include_once __ENGINE_DIR__.'/_manage/config/config.exe.php';

msg('', 'reload', 'parent');