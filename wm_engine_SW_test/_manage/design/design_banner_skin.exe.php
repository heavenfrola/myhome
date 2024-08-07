<?php

/**
 * 디자인 배너 저장
 **/

include_once $engine_dir.'/_engine/include/img_ftp.lib.php';

getSkinBanner($_POST['source']);

$skin_folder = $root_dir.'/_skin/'.$_POST['source'];
$internal_dir = $skin_folder.'/img/internal_banner';
$external_dir = '_data/internal_banner/'.basename($skin_folder);
if (file_exists(__ENGINE_DIR__.'/_engine/include/account/setHosting.inc.php') == true) {
    $external_dir = $root_dir.'/'.$external_dir;
}

if ($_POST['exec'] == 'toggle') {
    $no = (int) $_POST['no'];
    $use_banner = $skinbanner_cfg[$no]['use_banner'];
    $use_banner = ($use_banner == 'Y') ? 'N' : 'Y';
    $skinbanner_cfg[$no]['use_banner'] = $use_banner;
} else if ($_POST['exec'] == 'delete') {
    foreach ($_POST['no'] as $no) {
        foreach(array('upfile1', 'upfile2') as $fn) {
            if ($skinbanner_cfg[$no][$fn]) { // 수정 시 기존파일 삭제
                deleteAttachFile($internal_dir, $skinbanner_cfg[$no][$fn]);
                deleteAttachFile($external_dir, $skinbanner_cfg[$no][$fn]);
            }
        }
        unset($skinbanner_cfg[$no]);
    }
} else if ($_POST['exec'] == 'copy') {
    $_GET = $_POST;
    require 'design_banner.php';

    // 스킨을 복사할 타겟 스킨 설정
    unset($skinbanner_cfg);
    getSkinBanner($_POST['target']);
    $skin_folder = '_skin/'.$_POST['target'];
    $internal_dir = $skin_folder.'/img/internal_banner';
    $external_dir = '_data/internal_banner/'.basename($skin_folder);

    $no = count($skinbanner_cfg);
    $selected = explode(',', $_GET['selected']);
    foreach ($sql as $idx => $data) {
        if ($_GET['cpmode'] == '1') {
            if (in_array($idx, $selected) == false) continue;
        }

        if ($_POST['target'] == '') { // 공통배너로 복사
            // 배너 이미지 복사
            foreach(array('upfile1', 'upfile2') as $fn) {
                if (!$data[$fn]) continue;

                $filepath = $root_dir.'/'.$data['updir'].'/'.$data[$fn];

                $up_filename = md5($fn.microtime());
                $imginfo = getimagesize($filepath);
                $up_filename = uploadFile(
                    array(
                        'name' => $data[$fn],
                        'tmp_name' => $filepath,
                        'size' => filesize($filepath),
                        'type' => $imginfo['mime']
                    ), $up_filename, '_data/banner', 'jpg|jpeg|gif|png|webp'
                );
                $data[$fn] = $up_filename[0];
            }

            // insert
            $no = (int) $pdo->row("select max(no) from {$tbl['banner']}")+1;
            if (!$data['link_type']) $data['link_type'] = '1';
            if (!$data['obj_type']) $data['obj_type'] = '1';
            if (!$data['upfile1']) $data['upfile1'] = '';
            if (!$data['upfile2']) $data['upfile2'] = '';
            if (!$data['big']) $data['big'] = '';
            if (!$data['mid']) $data['mid'] = '';
            if (!$data['small']) $data['small'] = '';
            if (!$data['depth4']) $data['depth4'] = '';

            $pdo->query("
                insert into {$tbl['banner']} (no, name, link, link_type, obj_type, updir, upfile1, upfile2, target, use_banner, start_date, finish_date, big, mid, small, depth4)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", array(
                $no, $data['name'], $data['link'], $data['link_type'], $data['obj_type'], '_data/banner', $data['upfile1'], $data['upfile2'],
                $data['target'], $data['use_banner'], $data['start_date'], $data['finish_date'], $data['big'], $data['mid'], $data['small'], $data['depth4']
            ));
        } else { // 스킨배너로 복사
            // 배너 이미지 복사
            foreach(array('upfile1', 'upfile2') as $fn) {
                if (!$data[$fn]) continue;

                $filepath = $root_dir.'/'.$data['updir'].'/'.$data[$fn];
                $up_filename = preg_replace('/\..*$/', '', $data[$fn]);
                $imginfo = getimagesize($filepath);
                $file = array(
                    'name' => $data[$fn],
                    'tmp_name' => $filepath,
                    'size' => filesize($filepath),
                    'type' => $imginfo['mime']
                );
                ftpUploadFile($internal_dir, $file, 'jpg|jpeg|gif|png|webp'); // 로컬
                uploadFile($file, $up_filename, $external_dir, 'jpg|jpeg|gif|png|webp'); // 원격

                $data['src'] = getListImgURL('_data/internal_banner/'.$_POST['target'], $up_filename);
            }
            $data['updir'] = '/img/internal_banner';
            unset($data['src_local']);

            $no++;
            $skinbanner_cfg[$no] = $data;
        }
    }
    if ($_POST['target'] == '') exit;
} else {
    // 첨부 이미지 저장
    if (is_dir($internal_dir) == false) {
        ftpMakeDir($internal_dir, 'internal_banner');
    }
    makeFullDir(str_replace($root_dir.'/', '', $external_dir));

    foreach ($_FILES as $fn => $file) {
        if ($file['size'] == 0) continue;

        $up_filename = md5($file['name'].$now);
        $file['name'] = $up_filename.'.'.getExt($file['name']);

        if ($skinbanner_cfg[$no][$fn]) { // 수정 시 기존파일 삭제
            deleteAttachFile($internal_dir, $skinbanner_cfg[$no][$fn]);
            deleteAttachFile($external_dir, $skinbanner_cfg[$no][$fn]);
        }

        // 로컬 폴더
        ftpUploadFile($internal_dir, $file, 'jpg|jpeg|gif|png');

        // 리모트 폴더
        $up_filename = uploadFile($file, $up_filename, $external_dir, 'jpg|jpeg|gif|png');
        $skinbanner_cfg[$no][$fn] = $up_filename[0];
    }

    // 링크 생성
    switch($_POST['link_type']) {
        case '1' :
            $url = $_POST['link'][0];
        break;
        case '2' :
            $hash = $pdo->row("select hash from {$tbl['product']} where no=?", array($_POST['link'][0]));
            $url = $root_url.'/shop/detail.php?pno='.$hash;
        break;
        case '3' :
            $url = $root_dir.'/shop/big_section.php?cno1='.$_POST['link'][0];
        break;
    }

    // 배너 설정 생성
    $no = (int) $_POST['no'];
    if (isset($skinbanner_cfg[$no]) == false) {
        $skinbanner_cfg[$no] = array();
    }
    $skinbanner_cfg[$no]['name'] = $_POST['name'];

    // 이미지
    $skinbanner_cfg[$no]['updir'] = '/img/internal_banner';
    if (isset($up_filename[0]) == true) {
        $skinbanner_cfg[$no]['src'] = getListImgURL('_data/internal_banner/'.$_POST['source'], $up_filename[0]);
    }

    $skinbanner_cfg[$no]['link'] = $_POST['link'][0];
    $skinbanner_cfg[$no]['link_type'] = $_POST['link_type'];
    $skinbanner_cfg[$no]['url'] = $url;
    $skinbanner_cfg[$no]['target'] = $_POST['target'];
    $skinbanner_cfg[$no]['use_banner'] = $_POST['use_banner'];
    // 시작일
    if ($_POST['start_date']) {
        $_s = explode('-', $_POST['start_date']);
        $_POST['start_date'] = sprintf('%s-%s-%s %s:%s:00', $_s[0], $_s[1], $_s[2], $_s[3], $_s[4], $_s[5]);
    }
    // 종료일
    $skinbanner_cfg[$no]['start_date'] = $_POST['start_date'];
    if ($_POST['finish_date']) {
        $_e = explode('-', $_POST['finish_date']);
        $_POST['finish_date'] = sprintf('%s-%s-%s %s:%s:59', $_e[0], $_e[1], $_e[2], $_e[3], $_e[4], $_e[5]);
    }
    $skinbanner_cfg[$no]['finish_date'] = $_POST['finish_date'];
    $skinbanner_cfg[$no]['maptext'] = $_POST['content'];
    // 기타
    foreach (array('big', 'mid', 'small', 'depth4', 'obj_type') as $field) {
        if (empty($_POST[$field]) == false) {
            $skinbanner_cfg[$no][$field] = $_POST[$field];
        } else {
            unset($skinbanner_cfg[$no][$field]);
        }
    }
}

$json = json_encode_pretty($skinbanner_cfg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// 파일 저장
fwriteTo('_data/banner.json', $json, 'w');

ftpUploadFile($skin_folder, array(
    'tmp_name' => $root_dir.'/_data/banner.json',
    'name' => 'banner.json',
    'type' => 'application/json',
    'error' => 0,
    'size' => filesize($root_dir.'/_data/banner.json')
), 'json');
unlink($root_dir.'/_data/banner.json');

if ($exec == 'copy') return;

if ($_POST['exec'] == 'toggle') {
    header('Content-type:application/json;');
    exit(json_encode(array(
        'changed' => $use_banner,
    )));
}

$listurl = getListURL('banner');
$listurl = preg_replace('/&?source=([^&]+)/', '', $listurl);
$listurl .= '&source='.$_POST['source'];

msg('배너가 저장되었습니다.', $listurl, 'parent');