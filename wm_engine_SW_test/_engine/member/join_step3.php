<?PHP

/* +----------------------------------------------------------------------------------------------+
' |  회원가입 완료페이지
' +----------------------------------------------------------------------------------------------+*/

include_once $engine_dir.'/_engine/include/common.lib.php';
if (!$_SESSION['just_join']) msg('', '/');

common_header();

if ($cfg['ace_counter_gcode']) {
?>
<script type="text/javascript">
var CL_jn = 'join';
var CL_jid = '<?=$member['member_id']?>';
</script>
<?php
}
if($nvcpa) { ?>
    <!-- 네이버 CPA 스크립트 -->
    <script type='text/javascript'>
        if (!wcs_add) var wcs_add={};
        wcs_add["wa"] = "<?=trim($cfg['ncc_AccountId'])?>";
        var _nasa={};
        if (window.wcs) {
            _nasa["cnv"] = wcs.cnv("2", "1");
            wcs_do(_nasa);
        }
    </script>
<?php }
include_once $engine_dir."/_engine/common/skin_index.php";
?>