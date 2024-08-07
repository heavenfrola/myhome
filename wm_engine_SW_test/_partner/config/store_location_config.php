<?PHP
/*
 * [매장지도] API 입니다
 */
    //오프라인 매장 안내
    if (!isTable($tbl['store_location'])) {
		$_store_field_arr = array(
			'use_kakao_location'=>'N',
			'store_marker_yn'=>'N',
			'store_marker_clusterer'=>'N',
			'store_marker_clusterer_color'=>'#26ace2',
			'store_location_gps'=>'N',
			'location_layout_type'=>'1'
		);
		foreach($_store_field_arr as $name => $v) {
			$pdo->query("insert into {$tbl['config']} (name, value, reg_date) values (:name, :value, :reg_date)",
				array(':name'=>$name, ':value'=>$v, ':reg_date'=>$now)
			);
		}

        require __ENGINE_DIR__.'/_config/tbl_schema.php';
        $pdo->query($tbl_schema['store_location']);
    }

    //매장 정보 설정
    if (!isTable($tbl['store_operate'])) {
        require __ENGINE_DIR__.'/_config/tbl_schema.php';
        $pdo->query($tbl_schema['store_operate']);
    }

    //영업 시간 설정
    if (!isTable($tbl['store_operate_time'])) {
        require __ENGINE_DIR__.'/_config/tbl_schema.php';
        $pdo->query($tbl_schema['store_operate_time']);
    }

    //브레이크 시간 설정
    if (!isTable($tbl['store_operate_break'])) {
        require __ENGINE_DIR__.'/_config/tbl_schema.php';
        $pdo->query($tbl_schema['store_operate_break']);
    }

    //시설 관리 안내
    if (!isTable($tbl['store_facility_set'])) {
        require __ENGINE_DIR__.'/_config/tbl_schema.php';
        $pdo->query($tbl_schema['store_facility_set']);
    }

    //오프라인 매장 찜 목록
    if (!isTable($tbl['store_wish'])) {
        require __ENGINE_DIR__.'/_config/tbl_schema.php';
        $pdo->query($tbl_schema['store_wish']);
    }
    ?>
    <form method="post" action="./index.php" target="hidden<?php echo $now; ?>" onsubmit="printLoading()">
        <input type="hidden" name="body" value="config@config.exe">
        <input type="hidden" name="config_code" value="store_location">
        <table class="tbl_row">
            <caption>카카오 지도 API</caption>
            <colgroup>
                <col width="150px">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th scope="row">카카오 지도API<br>사용여부</th>
                    <td>
                        <label><input type="radio" name="use_kakao_location" value="N" <?php echo checked($cfg['use_kakao_location'], 'N'); ?>> 사용안함</label>
                        <label><input type="radio" name="use_kakao_location" value="Y" <?php echo checked($cfg['use_kakao_location'], 'Y'); ?>> 사용함</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">API key</th>
                    <td>
                        <input type="input" name="use_kakao_location_key" class="input" size="50" value="<?php echo $cfg['use_kakao_location_key'];?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row">REST API</th>
                    <td>
                        <input type="input" name="use_kakao_location_rest_key" class="input" size="50" value="<?php echo $cfg['use_kakao_location_rest_key'];?>">
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="box_bottom">
            <span class="box_btn blue"><input type="submit" value="확인"></span>
        </div>
    </form>
    <br>
<?php if (isTable($tbl['store_location'])) {?>
    <form name="kakaoConfigFrm" id="kakaoConfigFrm" method="post" action="./index.php" target="hidden<?php echo $now; ?>" enctype="multipart/form-data" onsubmit="printLoading()">
        <input type="hidden" name="body" value="config@config.exe">
        <input type="hidden" name="config_code" value="store_location_config">
        <table class="tbl_row">
            <caption>카카오 지도 설정</caption>
            <colgroup>
                <col width="150px">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th scope="row">마커 이미지<br>사용 여부</th>
                <td>
                    <label><input type="radio" name="store_marker_yn" value="N" <?php echo checked($cfg['store_marker_yn'], 'N'); ?>> 사용안함</label>
                    <label><input type="radio" name="store_marker_yn" value="Y" <?php echo checked($cfg['store_marker_yn'], 'Y'); ?>> 사용함</label>
                </td>
            </tr>
            <tr>
                <th scope="row">마커 이미지
                    <a href="#" class="tooltip_trigger" data-child="tooltip_marker_setting">설명</a>
                    <div class="info_tooltip tooltip_marker_setting w700">
                        <h3>마커 이미지 설정</h3>
                        <p>마커 이미지는 50x50 사이즈로 등록하셔야 합니다</p>
                        <a href="#" class="tooltip_closer">닫기</a>
                    </div>
                </th>
                <td>
                    <input type="file" name="store_marker_upfile1" class="input">
					<?php
                        $data['upfile1'] = $cfg['store_marker_upfile1'];
				    	$data['updir'] = $cfg['store_marker_updir'];
                        if($cfg['store_marker_updir'] && $cfg['store_marker_upfile1']) {
					    	$img = $root_url ."/".$cfg['store_marker_updir'] . $cfg['store_marker_upfile1'];
					    	$del = delImgStr($data, 1, 'Y');
					    	if($del) echo $del;
						?>
					<?php }?>
                    <br>
                    W: <input type="input" name="store_marker_w" class="input" size="3" value="<?php echo $cfg['store_marker_w']; ?>">
                    H: <input type="input" name="store_marker_h" class="input" size="3" value="<?php echo $cfg['store_marker_h']; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">클러스터 사용 여부</th>
                <td>
                    <label><input type="radio" name="store_marker_clusterer" value="N" <?php echo checked($cfg['store_marker_clusterer'], 'N'); ?>> 사용안함</label>
                    <label><input type="radio" name="store_marker_clusterer" value="Y" <?php echo checked($cfg['store_marker_clusterer'], 'Y'); ?>> 사용함</label>
                    <br>
                    <br>
                    Color : <input type="input" name="store_marker_clusterer_color" class="input colorpicker_input" size="5" value="<?php echo $cfg['store_marker_clusterer_color']; ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">GPS 사용 여부</th>
                <td>
                    <label><input type="radio" name="store_location_gps" value="N" <?php echo checked($cfg['store_location_gps'], 'N'); ?>> 사용안함</label>
                    <label><input type="radio" name="store_location_gps" value="Y" <?php echo checked($cfg['store_location_gps'], 'Y'); ?>> 사용함</label>
                    <br>
                    <br>
                    GPS 중심 좌표<br>
                    <span class="box_btn_s"><input type="button" value="주소 검색" class="btn2" onClick="locationZipSearchM('kakaoConfigFrm','gps_center_zip','gps_center_addr1','gps_center_addr2')"></span>
                    <br>
                    <input type="text" name="gps_center_zip" value="<?php echo $cfg['gps_center_zip'];?>" class="input" size="6" maxlength="50" style="margin:5px 0;" readonly>
                    <input type="text" name="gps_center_addr1" value="<?php echo $cfg['gps_center_addr1']; ?>" class="input" size="40" maxlength="100" readonly>
                    <input type="hidden" name="gps_center_addr2" value="<?php echo $cfg['gps_center_addr2']; ?>" class="input" size="10" maxlength="100">
                 </td>
            </tr>
            </tbody>
        </table>
        <div class="box_bottom">
            <span class="box_btn blue"><input type="submit" value="확인"></span>
        </div>
    </form>
    <br>
    <form method="post" action="./index.php" target="hidden<?php echo $now; ?>" onsubmit="printLoading()">
        <input type="hidden" name="body" value="config@config.exe">
        <table class="tbl_row map">
            <caption>공통 설정</caption>
            <colgroup>
                <col width="150px">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th scope="row">레이아웃 구조 변경</th>
                <td>
                    <label><p><input type="radio" name="location_layout_type" value="1" <?php echo checked($cfg['location_layout_type'], '1'); ?>> A Type</p> <div class="img"></div></label>
                    <label><p><input type="radio" name="location_layout_type" value="2" <?php echo checked($cfg['location_layout_type'], '2'); ?>> B Type </p><div class="img"></div></label>
                    <label><p><input type="radio" name="location_layout_type" value="3" <?php echo checked($cfg['location_layout_type'], '3'); ?>> C Type </p><div class="img"></div></label>
                    <label><p><input type="radio" name="location_layout_type" value="4" <?php echo checked($cfg['location_layout_type'], '4'); ?>> D Type </p><div class="img"></div></label>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="box_bottom">
            <span class="box_btn blue"><input type="submit" value="확인"></span>
        </div>
    </form>
<?php } ?>
    <br>
    <!--
    <table class="tbl_row">
        <caption>네이버 지도 API</caption>
        <colgroup>
            <col width="150px">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">네이버 지도API 사용여부</th>
            <td>
                <label><input type="radio" name="use_naver_location" value="N" <?php echo checked($cfg['use_naver_location'], 'N'); ?>> 사용안함</label>
                <label><input type="radio" name="use_naver_location" value="Y" <?php echo checked($cfg['use_naver_location'], 'Y'); ?>> 사용함</label>
            </td>
        </tr>
        <tr>
            <th scope="row">네이버 API Client ID</th>
            <td>
                <input type="input" name="use_naver_location_key" class="input" size="100" value="<?php echo $cfg['use_naver_location_key'];?>">
            </td>
        </tr>
        <tr>
            <th scope="row">네이버 API Client Secret</th>
            <td>
                <input type="input" name="use_naver_location_rest_key" class="input" size="100" value="<?php echo $cfg['use_naver_location_rest_key'];?>">
            </td>
        </tr>
        </tbody>
    </table>
	-->
<script type="text/javascript" src="<?php echo $engine_url; ?>/_engine/common/colorpicker/colorpicker.js"></script>
<link rel="stylesheet" href="<?php echo $engine_url;?>/_engine/common/colorpicker/colorpicker.css.php?engine_url=<?php echo $engine_url;?>" type="text/css">
<script type="text/javascript">
    function locationZipSearchM(form_nm,zip_nm,addr1_nm,addr2_nm){
        var srurl = manage_url+'/common/zip_search.php?urlfix=Y&form_nm='+form_nm+'&zip_nm='+zip_nm+'&addr1_nm='+addr1_nm+'&addr2_nm='+addr2_nm;
        window.open(srurl,'zip', ('scrollbars=yes,resizable=no,width=374, height=170'));
    }

    $('.colorpicker_input').bind({
        'focus' : function() {
            $('body').append('<div class="colorpicker"></div>');
            if(this.value == '') this.value = '#000000';
            $('.colorpicker').show();
            $('.colorpicker').farbtastic(this);
            $('.colorpicker').css({
                'position': 'absolute',
                'top': $(this).offset().top,
                'left': $(this).offset().left
            });
        },
        'blur' : function() {
            this.value = this.value.toUpperCase();
            $('.colorpicker').remove();
        }
    });

</script>
