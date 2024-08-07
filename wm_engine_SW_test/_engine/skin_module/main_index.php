<?php

/*
 * index page
 **/

if($_SESSION['browser_type']=='mobile') {
    $mRes = $pdo->iterator("select no, name from {$tbl['category']} where ctype = 6 order by no asc");
    foreach ($mRes as $i => $mData) {
        $_replace_code['common_module']['m_cate_name'.($i+1)] = $mData['name'];
    }
}

?>