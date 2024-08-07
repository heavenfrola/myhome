<?PHP

    include_once $engine_dir.'/_engine/include/common.lib.php';

	$sResponseData = $_REQUEST['enc_data'];

    if(preg_match('~[^0-9a-zA-Z+/=]~', $sResponseData, $match)) exit('입력 값 확인이 필요합니다.');
	if(base64_encode(base64_decode($sResponseData))!= $sResponseData) exit('입력 값 확인이 필요합니다.');
    if(preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReservedParam1, $match)) exit('문자열 점검 : '.$match[0]);
    if(preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReservedParam2, $match)) exit('문자열 점검 : '.$match[0]);
    if(preg_match("/[#\&\\+\-%@=\/\\\:;,\.\'\"\^`~\_|\!\/\?\*$#<>()\[\]\{\}]/i", $sReservedParam3, $match)) exit('문자열 점검 : '.$match[0]);

?>
<form name="vnoform" method="post" target="_self" action="<?=$root_url?>/main/exec.php?exec_file=member/ipin/result.exe.php">
    <input type="hidden" name="enc_data" value="<?=$sResponseData?>">
    <input type="hidden" name="param_r1" value="<?=$sReservedParam1?>">
    <input type="hidden" name="param_r2" value="<?=$sReservedParam2?>">
    <input type="hidden" name="param_r3" value="<?=$sReservedParam3?>">
</form>
<script>
    document.vnoform.submit();
</script>