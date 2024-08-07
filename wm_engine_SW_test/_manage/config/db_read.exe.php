<?php

$data = array();
foreach ($_POST['db_read_ip'] as $key => $ip) {
    if ($ip) {
        $data[$key] = array(
            $ip,
            floor($_POST['db_read_rt'][$key])
        );
    }
}
$config_file = $root_dir.'/_data/config/db_read.json';
$fp = fopen($config_file, 'w');
fwrite($fp, json_encode($data));
fclose($fp);
chmod($config_file, 0777);

exit('OK');