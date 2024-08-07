<div id="popupContent" class="popupContent layerPop" style="width:720px;">
    <div id="header" class="popup_hd_line">
        <h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
        <div id="mngTab_pop">상품 관리 설정</div>
    </div>
    <div id="popupContentArea">
        <p class="msg">상품 관리 설정을 통해 항목설정을 편리하게 설정할 수 있습니다.</p>
        <form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" onsubmit="this.target=hid_frame">
            <input type="hidden" name="body" value="config@config.exe">
            <input type="hidden" name="config_code" value="common">
            <table class="tbl_row">
                <caption class="hidden">상품 관리 설정</caption>
                <colgroup>
                    <col style="width:20%">
                    <col>
                </colgroup>
                <tr>
                    <th>상품조회 항목설정</th>
                    <td>
                        <label for="prd_prd_code" class="p_cursor"><input type="checkbox" name="prd_prd_code" id="prd_prd_code" value="Y" <?=checked($cfg['prd_prd_code'],"Y")?>> 상품코드</label>
                        <label for="prd_name_referer" class="p_cursor"><input type="checkbox" name="prd_name_referer" id="prd_name_referer" value="Y" <?=checked($cfg['prd_name_referer'],"Y")?>> 참고상품명</label>
                        <label for="prd_reg_date" class="p_cursor"><input type="checkbox" name="prd_reg_date" id="prd_reg_date" value="Y" <?=checked($cfg['prd_reg_date'],"Y")?>> 등록일</label>
                        <label for="prd_normal_prc" class="p_cursor"><input type="checkbox" name="prd_normal_prc" id="prd_normal_prc" value="Y" <?=checked($cfg['prd_normal_prc'],"Y")?>> <?=$cfg['product_normal_price_name']?></label>
                        <label for="prd_origin_name" class="p_cursor"><input type="checkbox" name="prd_origin_name" id="prd_origin_name" value="Y" <?=checked($cfg['prd_origin_name'],"Y")?>> 장기명</label>
                        <label for="prd_seller" class="p_cursor"><input type="checkbox" name="prd_seller" id="prd_seller" value="Y" <?=checked($cfg['prd_seller'],"Y")?>> 사입처</label>
                        <label for="prd_origin_prc" class="p_cursor"><input type="checkbox" name="prd_origin_prc" id="prd_origin_prc" value="Y" <?=checked($cfg['prd_origin_prc'],"Y")?>> 사입원가</label>
                    </td>
                </tr>
            </table>
            <div class="box_bottom noline">
                <span class="box_btn blue"><input type="submit" value="확인"></span>
                <span class="box_btn "><input type="button" value="닫기" onclick="oconfig.close()"></span>
            </div>
        </form>
    </div>
</div>