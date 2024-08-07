<?php

/**
 * 작성자 표기 설정
 **/

// 전체 설정
if ($_POST['exec'] == 'global') {
    $_POST['writer_name_bbs'] = implode('@', $_POST['writer_name_bbs']);

    unset($_POST['exec']);

    require __ENGINE_DIR__.'/_manage/config/config.exe.php';
}

// 게시판별 설정
if ($_POST['exec'] == 'local') {
    foreach ($_POST['writer_name'] as $db => $val) {
        if (preg_match('/^bbs_(review|qna)$/', $db, $tmp) == true) {
            $db2 = $tmp[1];
            $scfg->import(array(
                'product_'.$db2.'_name' => $_POST['writer_name'][$db],
                'product_'.$db2.'_protect_name' => $_POST['protect_name'][$db],
                'product_'.$db2.'_protect_name_strlen' => $_POST['protect_name_strlen'][$db],
                'product_'.$db2.'_protect_name_suffix' => $_POST['protect_name_suffix'][$db],
                'product_'.$db2.'_protect_id' => $_POST['protect_id'][$db],
                'product_'.$db2.'_protect_id_strlen' => $_POST['protect_id_strlen'][$db],
                'product_'.$db2.'_protect_id_suffix' => $_POST['protect_id_suffix'][$db],
            ));
        } else {
            $pdo->query("
                update mari_config set
                    writer_name=?,
                    protect_name=?, protect_name_strlen=?, protect_name_suffix=?,
                    protect_id=?, protect_id_strlen=?, protect_id_suffix=?
                where db=?
            ", array(
                $_POST['writer_name'][$db],
                $_POST['protect_name'][$db],
                $_POST['protect_name_strlen'][$db],
                $_POST['protect_name_suffix'][$db],
                $_POST['protect_id'][$db],
                $_POST['protect_id_strlen'][$db],
                $_POST['protect_id_suffix'][$db],
                $db
            ));
        }

    }
    javac("parent.removeLoading();");
}

?>