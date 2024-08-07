<?php

/**
 * 게시판 스킨 탐색기
 **/

require_once 'template_name.php';

$folder = $_POST['folder'];
if (preg_match('/\.{2,}|\//', $folder) == true) {
    $folder = '';
}

$res = array(); // 결과 데이터
$_dir = $root_dir.'/board/_skin/'.$folder; // 탐색할 경로
$scan = opendir($_dir); // 경로 읽기

// 이름 순 정렬
$dirname = array();
while($name = readdir($scan)) {
    if ($name == '.' || $name == '..') continue;
    $dirname[] = $name;
}

// 출력
foreach ($dirname as $name) {
    $type = (is_dir($_dir.'/'.$name) == true) ? 'folder' : 'file';
    $icon = ($type == 'folder') ? 'ic_folder_c.gif' : 'file_txt.gif';
    $link = ($type == 'folder') ? '#'.$name : '?body=design@board&skinname='.$folder.'&filename='.$name;

    if ($folder && $type == 'folder') continue;

    // 현재 사용중 게시판
    $_btitle = '';
    $use_board = $pdo->iterator("select * from {$tbl['mari_config']} where skin='$name'");
    foreach ($use_board as $use) {
        $_btitle .= $_btitle ? ', '.$use['title'] : $use['title'];
    }
    if ($_btitle) $_btitle = '('.$_btitle.' 사용 중)';

    $res[] = array(
        'name' => $name,
        'type' => $type,
        'icon' => $icon,
        'link' => $link,
        'title' => $board_sub_arr[$name],
        'use' => $_btitle
    );
}

jsonReturn($res);