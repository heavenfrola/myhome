<?php

/**
 * 상품 상세 미리보기
 **/

$pno = (int) $_GET['pno'];
$content_id = $_GET['content_id'];
$browser_type = ($_GET['content_no'] == 'm') ? 'mobile' : 'pc';

?>
<style>
#d_preview_header {
    padding: 10px;
    border-bottom: solid 1px #ccc;
}
body {
    overflow:hidden;
}
</style>

<!-- 헤더 -->
<div id="d_preview_header">
    상세 설명에 입력한 내용이 노출되며, 새로고침하여 변경 사항을 확인할 수 있습니다.
    <span class="box_btn_s"><input type="button" value="새로고침" onclick="previewReload();"></span>
</div>

<!-- 본문 프레임 -->
<iframe id="d_preview_iframe"
    src="<?=$manage_url?>/shop/detail.php?ano=<?=$pno?>&d_preview=Y&browser_type=<?=$browser_type?>&urlfix=Y"
    style="width: 100%; height: 100%"
></iframe>

<!-- 프레임 위치 조정 -->
<script>
var content_id = '<?=$content_id?>';
var iframe = document.getElementById('d_preview_iframe');
browser_type = '<?=$browser_type?>';

function previewReload()
{
    printLoading();
    iframe.contentWindow.d_previewReload();
}

printLoading();
$('#d_preview_iframe').height(
    $(window).height()-$('#d_preview_header').innerHeight()
);
</script>