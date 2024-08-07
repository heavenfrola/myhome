<?PHP

/**
 * 사용자 모듈 편집 / 그룹배너
 **/

use Wing\Design\BannerGroup;

include_once $engine_dir.'/_engine/include/common.lib.php';
include_once $engine_dir."/_engine/include/img_ftp.lib.php";
include_once $engine_dir."/_engine/include/file.lib.php";

$code = ($_POST['new_code']) ? $_POST['new_code'] : $_POST['code'];
$exec = $_POST['exec'];
$type = $_POST['type'];
$cursor = $_POST['cursor'];

include_once $root_dir.'/_skin/'.($type == 'mobile' ? 'm' : '').'config.cfg';

$bn = new BannerGroup($type, $code, $cursor);

switch($exec) {
    case 'upload' : // 추가
        $bn->add($_FILES);
        $bn->reload();
    break;
    case 'sort' : // 정렬
        $bn->sorting($_POST['sort']);
        $bn->reload();
    break;
    case 'remove' : // 삭제
        $bn->removeItem($_POST['id']);
        $bn->reload();
    break;
    case 'removeRollover' : // 오버이미지 삭제
        $bn->removeRollover($_POST['id']);
        $bn->reload();
    break;
    case 'toggle' : // 개별 코드 on/of
        $bn->toggle();

        header('Content-type:text/html; charset='._BASE_CHARSET_);
        $_GET = array(
            'body' => 'design@group_banner',
            'use_yn' => $_POST['use_yn']
        );
        require 'group_banner.php';
    break;
    case 'removeCode' :
        $mode = 'selected';
        if(is_array($code) == false) {
            $code = array($code);
        }
        foreach($code as $_code) {
            $bn = new BannerGroup($type, $_code);
            $bn->removeCode();
        }

        header('Content-type:text/html; charset='._BASE_CHARSET_);
        $_GET = array(
            'body' => 'design@group_banner',
            'use_yn' => $_POST['use_yn']
        );
        require 'group_banner.php';
    break;
    default : // 수정
        $bn->modify();
    break;
}