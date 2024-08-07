<div id="popupContent" class="popupContent layerPop" style="width:450px;">
    <div id="header" class="popup_hd_line">
        <h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
        <div id="mngTab_pop">개인정보처리방침 마법사</div>
    </div>
    <div id="popupContentArea">
        <div>
            개인정보처리방침 마법사를 진행 하시겠습니까?<br><br>
            '예' 클릭 시 마법사 진행<br>
            '아니요' 클릭 시 텍스트 입력 진행<br><br>
            <p class="msg">마법사를 진행 하시면 개인정보처리방침 등록시 편리하게 등록 할 수 있습니다</p>
        </div>
            <div class="pop_bottom">
                <span class="box_btn blue"><button onClick="goM('config@privacy_write_wizard');">예</button></span>
                <span class="box_btn gray"><button onclick="goM('config@privacy_write');">아니오</button></span>
                <span class="box_btn"><button onClick="privacyModeLayer.close();">닫기</button></span>
            </div>
    </div>
</div>