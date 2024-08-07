<?php

/**
 *  구글 애드워즈 연동 설정
 **/

$scfg->def('use_google_ads', 'N');

?>
<form method="post" action="./index.php" target="hidden<?=$now?>" onsubmit="printLoading();">
    <input type="hidden" name="body" value="config@config.exe">

	<div class="box_title first">
		<h2 class="title">
			구글 애드워즈 연동&nbsp;
			<a href="https://ads.google.com/" target="_blank"><img src="<?=$engine_url?>/_manage/image/shortcut2.gif"></a>
		</h2>
	</div>
    <table class="tbl_row">
        <caption class="hidden">구글 애널리틱스 연동</caption>
        <colgroup>
            <col style="width:7%">
            <col style="width:13%">
        </colgroup>
        <tr>
            <th scope="row" colspan="2">사용 여부</th>
            <td>
                <label><input type="radio" name="use_google_ads" value="Y" <?=checked($scfg->get('use_google_ads'), 'Y')?>> 사용함</label>
                <label><input type="radio" name="use_google_ads" value="N" <?=checked($scfg->get('use_google_ads'), 'N')?>> 사용안함</label>
            </td>
        </tr>
        <tr>
            <th scope="row" colspan="2">Conversion ID</th>
            <td>
                <input type="text" name="google_ads_id" value="<?=inputText($scfg->get('google_ads_id'))?>" class="input" size="20">
            </td>
        </tr>
        <tr>
            <th scope="row" rowspan="3" style="border-right: solid 1px #d6d6d6">이벤트<br>스니펫</th>
            <th scope="row">장바구니 이벤트 ID</th>
            <td>
                <input type="text" name="google_ads_cart_id" value="<?=inputText($scfg->get('google_ads_cart_id'))?>" class="input" size="40">
            </td>
        </tr>
        <tr>
            <th scope="row">구매전환 이벤트 ID</th>
            <td>
                <input type="text" name="google_ads_conv_id" value="<?=inputText($scfg->get('google_ads_conv_id'))?>" class="input" size="40">
            </td>
        </tr>
        <tr>
            <th scope="row">회원가입 이벤트 ID</th>
            <td>
                <input type="text" name="google_ads_join_id" value="<?=inputText($scfg->get('google_ads_join_id'))?>" class="input" size="40">
            </td>
        </tr>
    </table>
    <div class="box_middle2 left">
        <ul class="list_info">
            <li>사용할 이벤트를 Google ads의 도구 및 설정 > 측정 > 전환 페이지에서 등록한 후 생성된 스니펫 이벤트 ID를 입력하세요.</li>
        </ul>
    </div>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
    </div>
</form>