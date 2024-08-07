<?php

include_once $engine_dir.'/_engine/include/common.lib.php';

$prd = $pdo->assoc("select name, content2 from {$tbl['product']} where hash=?", array(
    $_GET['hash']
));

// css
include_once $GLOBALS['root_dir']."/_skin/mconfig.cfg";
$_css_tmp_url = $root_url.'/'.$dir['upload'].'/wing_'.$design['skin'].'_temp.css';

?>
<html>
<head>
    <title><?=stripslashes(strip_tags($prd['name']))?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?=_BASE_CHARSET_?>">
    <?php if ($_SESSION['browser_type'] == 'mobile') { ?>
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,viewport-fit=cover">
    <?php } ?>
    <style type="text/css">
    body {
        margin: 5px !important;
    }
    img {
        max-width: 100%;
    }
    </style>
    <link rel="stylesheet" href="<?=$_css_tmp_url?>">
</head>
<body>
    <?=stripslashes($prd['content2'])?>
</body>
</html>