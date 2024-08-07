<?php
    $contents = ($_POST['contents']) ? $_POST['contents'] : '';
    $effective_date = ($_POST['effective_date']) ? $_POST['effective_date'] : date('Y-m-d');
    $hiddenChecked = (isset($_POST['hidden']) && $_POST['hidden'] === 'Y') ? '' : 'checked';
    $pageType = ($_POST['pageType']) ? $_POST['pageType'] : 'simple';
    $no = ($_GET['no']) ? $_GET['no'] : 0;
    if ($pageType === 'wizard') {
        $cancelUrl = 'history.back();';
    } else {
        $rURL = getListURL('privacyList');
        if(!$rURL) $rURL = './?body=config@privacy';
        $cancelUrl = 'location.href=\''.$rURL.'\';';
    }
?>
<script type="text/javascript" src='<?=$engine_url?>/_engine/common/jquery.serializeObject.js'></script>
<script type="text/javascript" src='<?=$engine_url?>/_manage/privacy.js?t=<?=time()?>'></script>
<form name="privacy_writeF" id="privacy_writeF">
    <input type="hidden" name="no" value=<?=$no?>>
    <input type="hidden" name="pageType" value="<?=$pageType?>">
    <div class="box_title first">
        <h2 class="title">개인정보처리방침</h2>
    </div>
    <div id="privacy_view">
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
                <th scope="row">시행일시</th>
                <td><input type="text" name="effective_date" class="datepicker input" value="<?=$effective_date?>"></td>
                <th scope="row">사용</th>
                <td><input type="checkbox" name="hidden" value="N" <?=$hiddenChecked?>></td>
            </tr>
            </tbody></table>
        <div class="box_middle2 board_view">
            <textarea id="contents" style="visibility:hidden;">
                <?=$contents?>
            </textarea>
        </div>
    </div>

    <div id="reg_footer" class="box_bottom">
        <span class="box_btn blue"><button type="button" onclick="privacy_cls.save()">확인</button></span>
        <span class="box_btn gray"><button type="button" onclick="<?=$cancelUrl?>">취소</button></span>
        <span class="box_btn gray"><button type="button" onClick="privacy_cls.preview('simple');">미리보기</button></span>
    </div>
</form>

<style>
    #contents {width:100%; height:600px;}
</style>
<script>
    const privacy_cls = new Privacy();
    $(document).ready(function(){
        let no = $('[name=no]', privacy_cls.writeForm).val();
        let $contents = $('#contents', this.writeForm);
        if (no>0) {
            privacy_cls.modifySet(no);
        } else if (!$contents.val().trim()) {
            $contents.val(privacy_cls.sampleDocSet());
        }
        seCall('contents', 'SE2M_CONTENTS', 'privacy');
    });
</script>