<?php

/**
 * MS 클레어리티 연동
 */

if ($cfg['use_clarity'] != 'Y') $cfg['use_clarity'] = 'N';
?>
<form name="googleFrm" method="post" action="./index.php" target="hidden<?=$now?>">
    <input type="hidden" name="body" value="config@config.exe">
    <input type="hidden" name="config_code" value="clarity">
    <div class="box_title first">
        <h2 class="title">
            MS 클레어리티 연동&nbsp;
            <a href="https://clarity.microsoft.com/" target="_blank"><img src="<?=$engine_url?>/_manage/image/shortcut2.gif"></a>
        </h2>
    </div>
    <table class="tbl_row">
        <caption class="hidden">MS 클레어리티 연동</caption>
        <colgroup>
            <col style="width:15%">
        </colgroup>
        <tr>
            <th scope="row">사용 여부</th>
            <td>
                <label><input type="radio" name="use_clarity" value="Y" <?=checked($cfg['use_clarity'], 'Y')?>> 사용함</label>
                <label><input type="radio" name="use_clarity" value="N" <?=checked($cfg['use_clarity'], 'N')?>> 사용안함</label>
            </td>
        </tr>
        <tr>
            <th scope="row">프로젝트 ID</th>
            <td>
                <input type="text" name="clarity_code" class="input" size="30" value="<?=$cfg['clarity_code']?>">
                <ul class="list_msg">
                    <li>내 프로젝트 > 설정 > 개요 메뉴로 이동하시면 프로젝트 ID를 확인하실 수 있습니다.</li>
                </ul>
            </td>
        </tr>
    </table>
    <div class="box_middle2 left">
        <ul class="list_msg">
            <li>이미 스킨에 수동으로 입력하신 클레어리티 코드가 있을 경우 2중으로 수집되거나 충돌이 일어날수 있습니다.</li>
            <li>수동으로 입력하신 코드가 있을 경우에는 삭제 후 이용해주세요.</li>
            <li>연동 설정 후 2시간 이내에 프로젝트에서 데이터를 확인할 수 있습니다.</li>
        </ul>
    </div>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="확인"></span>
    </div>
</form>