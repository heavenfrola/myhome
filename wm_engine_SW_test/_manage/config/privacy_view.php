<?php
    $no = $_GET['no'];
    $rURL = getListURL('privacyList');
    if(!$rURL) $rURL = './?body=config@privacy';
    $skin = getSkinCfg();
?>
<script type="text/javascript" src='<?=$engine_url?>/_manage/privacy.js?t=<?=time()?>'></script>
<div class="box_title first">
    <h2 class="title">개인정보처리방침</h2>
</div>
<div id="privacy_view">
    <input type="hidden" name="skin_url" value="<?=$skin['url']?>">
    <table class="tbl_row">
        <caption class="hidden">개인정보처리방침</caption>
        <colgroup>
            <col style="width:25%">
            <col style="width:25%">
            <col style="width:25%">
            <col style="width:25%">
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">작성자</th>
            <td id="admin"></td>
            <th scope="row">시행일시</th>
            <td id="effective_date"></td>
        </tr>
        <tr>
            <th scope="row">작성일시</th>
            <td id="reg_date"></td>
            <th scope="row">사용</th>
            <td id="hidden"></td>
        </tr>
        </tbody></table>
    <div class="box_middle2 board_view left" id="contents">
        <iframe style="width:100%; height:800px;" onload="privacy_cls.autoHeightIframe(this)"></iframe>
    </div>
</div>

<div id="reg_footer" class="box_bottom">
    <span class="box_btn blue"><input type="button" value="수정" onclick="location.href='./?body=config@privacy_write&no=<?=$no?>';"></span>
    <span class="box_btn gray"><input type="button" value="삭제" onclick="privacy_cls.del(<?=$no?>);"></span>
    <span class="box_btn gray"><input type="button" value="목록" onclick="location.href='<?=$rURL?>';"></span>
</div>

<input type="hidden" name="no" value="<?=$no?>">

<script>
    const privacy_cls = new Privacy();
    $(document).ready(function(){
        let no = $('[name=no]').val();
        privacy_cls.viewSet(no);
    });
</script>