<?php
if ($_POST['type']) {
    include $engine_dir."/_engine/include/common.lib.php";

    if ($_POST['type'] === 'set') {
        $ret = loginInfoSet($_POST['loginInfo']);
    } elseif ($_POST['type'] === 'get') {
        $ret = loginInfoGet($_POST['authToken'], $_POST['authTime']);
    }

    echo json_encode(array(
        'status' => 'success',
        'loginInfo' => $ret
    ));
}
exit;