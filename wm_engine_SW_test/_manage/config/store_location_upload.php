<?php
/**
 * [매장지도] 엑셀 업로드
 */

?>
<form method='post' enctype='multipart/form-data' action='./index.php' target='hidden<?=$now?>' onsubmit="return upXls();">
    <input type="hidden" name='exec' value='upload'>

    <table class="tbl_row">
        <caption>오프라인 매장 엑셀 일괄 업로드</caption>
        <colgroup>
            <col style="width: 150px;">
            <col>
        </colgroup>
        <tr>
            <th>업로드 파일 종류</th>
            <td>
                <label><input type="radio" name="body" value="config@store_location_upload.exe" checked> 오프라인 매장 업로드</label>
            </td>
        </tr>
        <tr>
            <th>xls 파일</th>
            <td><input type="file" name="csv" class="input" size="20"></td>
        </tr>
        <tr>
            <td colspan="2">
                <ul class="list_msg" style="margin-bottom:10px;">
                    <li><a href="?body=config@store_location">[설정 > 오프라인 매장 관리 > 오프라인 매장]</a> 메뉴에서 파일을 다운로드 받아 수정하신 후 <a>‘Excel 97 - 2003 통합 문서’</a>로 다른 이름으로 저장한 후 업로드 해주세요.
					</li>
                    <li>고유번호가 있을 경우 수정, 없을 경우 신규 등록으로 처리 됩니다.</li>
                    <li>신규 등록 혹은 기존 정보 수정 시, 상호명은 반드시 존재해야 합니다.</li>
                </ul>

                <h3 class="p_color3">필드별 입력 주의사항</h3>
                <ul class="list_msg">
                    <li>상호명 / 대표자명 / 전화번호 / 휴대전화번호 / 이메일 : 각 필드에 대응되는 매장 정보를 입력해 주세요. <br>ㄴ 전화번호 및 휴대전화는 ‘-’ 를 제외하고 입력해 주세요. <br>ㄴ 이메일은 형식에 맞춰 입력해 주세요.</li>
                    <li>우편번호 / 주소 / 상세주소 : 매장이 위치한 주소지의 우편번호 및 도로명 주소, 상세주소를 입력해 주세요.</li>
                    <li>상태 : 정상 / 휴업 / 폐업 중 하나를 입력해 주세요.</li>
                    <li>숨김여부 : Y 또는 N 으로 입력해 주세요. 빈값일 경우 N 으로 인정됩니다.</li>
                    <li>내용 : 매장에 대한 기타 상세 정보를 입력해 주세요.</li>
                    <li>이미지 경로 : 썸네일 이미지 및 커버 이미지 업로드 시, 반드시 <strong>_data/store/YYYYMM/DD</strong> 를 입력해 주세요. <br>ㄴ 경로 내 /YYYYMM/DD 는 해당 매장의 신규 등록 날짜를 나타냅니다. (예. _data/store/202309/12) <br>ㄴ 없을 경우, 모든 이미지 관련 작업은 처리되지 않습니다.</li>
                    <li>썸네일 이미지 / 커버 이미지 : 웹사이트 서버의 이미지 경로에 이미지를 업로드 해두신 후 해당 파일명만 입력해주세요. <br>ㄴ 반드시 FTP로 해당 이미지가 상기 이미지 경로에 미리 업로드 되어 있어야 합니다.</li>
                    <li>시설안내 : 매장에서 제공하는 시설의 고유번호를 @로 구분하여 입력해 주세요. (예. 1@2@3) <br>ㄴ 각 시설별 고유번호는 <a>[설정 > 오프라인 매장 관리 > 오프라인 매장]</a> 메뉴에서 확인해 주세요.</li>
                </ul>
            </td>
        </tr>
    </table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" id="stpBtn" value="확인"></span>
    </div>
</form>
<script type="text/javascript">
    function upXls() {
        if(confirm('선택한 엑셀을 반영하시겠습니까?')) {
            $('#stpBtn').hide();
            return true;
        }
        return false;
    }
</script>