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
			'location_layout_type'=>'1',
            'use_store_partner_yn'=>'N'
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
                    <th scope="row">카카오 지도 사용</th>
                    <td>
                        <label><input type="radio" name="use_kakao_location" value="N" <?php echo checked($cfg['use_kakao_location'], 'N'); ?>> 사용안함</label>
                        <label><input type="radio" name="use_kakao_location" value="Y" <?php echo checked($cfg['use_kakao_location'], 'Y'); ?>> 사용함</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">JavaScript 키</th>
                    <td>
                        <input type="input" name="use_kakao_location_key" class="input" size="50" value="<?php echo $cfg['use_kakao_location_key'];?>" placeholder="발급받은 JavaScript 키를 넣으시면 됩니다.">
                    </td>
                </tr>
                <tr>
                    <th scope="row">REST API 키</th>
                    <td>
                        <input type="input" name="use_kakao_location_rest_key" class="input" size="50" value="<?php echo $cfg['use_kakao_location_rest_key'];?>" placeholder="발급받은 REST API 키를 넣으시면 됩니다.">
                    </td>
                </tr>
            </tbody>
        </table>
		<div class="map_setting">
			<h3>카카오 지도 JavaScript 키 및 REST API 키 얻는 방법</h3>
			<ul class="">
				<li>1) 카카오 개발자사이트 <a href="https://developers.kakao.com/">(https://developers.kakao.com/)</a> 접속</li>
				<li>2) 개발자 등록 및 앱 생성</li>
				<li>3) 웹 플랫폼 추가: 앱 선택 – [플랫폼] – [Web 플랫폼 등록] – 사이트 도메인 등록</li>
				<li>4) 사이트 도메인 등록: [웹] 플랫폼을 선택하고, [사이트 도메인] 을 등록합니다. (예: http://localhost:8080/)</li>
				<li>5) 페이지 상단의 [JavaScript 키] 복사 후 위사 관리자 [설정 > 오프라인 매장 > 오프라인 매장 설정] 내 ‘JavaScript 키’에 입력</li>
				<li>6) 마찬가지 방법으로 [REST API 키 복사 후 'REST API 키'에 입력 및 확인</li>
			</ul>
		</div>
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
                <col width="150px">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th class="milageCha line_r" scope="row" rowspan="3">마커</th>
                <th scope="row">이미지 사용</th>
                <td>
                    <label><input type="radio" name="store_marker_yn" value="N" <?php echo checked($cfg['store_marker_yn'], 'N'); ?>> 사용안함</label>
                    <label><input type="radio" name="store_marker_yn" value="Y" <?php echo checked($cfg['store_marker_yn'], 'Y'); ?>> 사용함</label>
                </td>
            </tr>
            <tr>
                <th scope="row">이미지 등록
                    <div class="info_tooltip tooltip_marker_setting w700">
                        <h3>마커 이미지 설정</h3>
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
					<ul class="list_info tp">
						<li>최대 <strong>1MB</strong> 크기 이하의 <strong>gif, jpg, png</strong> 파일만 가능합니다.</li>
					</ul>
                </td>
            </tr>
			<tr>
				<th scope="row">이미지 사이즈</th>
				<td>
					가로 <input type="input" name="store_marker_w" class="input" size="3" value="<?php echo $cfg['store_marker_w']; ?>" placeholder="50"> px X
                    세로 <input type="input" name="store_marker_h" class="input" size="3" value="<?php echo $cfg['store_marker_h']; ?>" placeholder="60"> px
					<ul class="list_info tp">
						<li>권장 사이즈는 50 x 60 입니다.</li>
					</ul>
				</td>
			</tr>
            <tr>
                <th class="milageCha line_r" scope="row" rowspan="2">클러스터</th>
                <th scope="row">가능 사용</th>
                <td>
                    <label><input type="radio" name="store_marker_clusterer" value="N" <?php echo checked($cfg['store_marker_clusterer'], 'N'); ?>> 사용안함</label>
                    <label><input type="radio" name="store_marker_clusterer" value="Y" <?php echo checked($cfg['store_marker_clusterer'], 'Y'); ?>> 사용함</label>
					<ul class="list_info tp">
						<li>사용 시, 밀집된 지역 내 다수의 마커는 하나의 대표 클러스터(Cluster)로 표시됩니다.</li>
					</ul>
                </td>
            </tr>
			<tr>
                <th scope="row">색상</th>
                <td>
                   <input type="input" name="store_marker_clusterer_color" class="input colorpicker_input" size="5" value="<?php echo $cfg['store_marker_clusterer_color']; ?>" placeholder="#">
                </td>
            </tr>
            <tr>
				<th class="milageCha line_r" scope="row" rowspan="2">위치정보<br>접근허용</th>
                <th scope="row">설정 사용</th>
                <td>
                    <label><input type="radio" name="store_location_gps" value="N" <?php echo checked($cfg['store_location_gps'], 'N'); ?>> 사용안함</label>
                    <label><input type="radio" name="store_location_gps" value="Y" <?php echo checked($cfg['store_location_gps'], 'Y'); ?>> 사용함</label>
					<ul class="list_info tp">
						<li>해당 설정 사용 시, 회원에게 위치정보 접근 권한 확인을 요청합니다.</li>
					</ul>
                 </td>
            </tr>
			<tr>
				<th scope="row">대체 중심 위치</th>
				<td>
					<input type="text" name="gps_center_zip" value="<?php echo $cfg['gps_center_zip'];?>" class="input" size="6" maxlength="50" style="margin:5px 0;" readonly>
					<span class="box_btn_s"><input type="button" value="주소 검색" class="btn2" onClick="locationZipSearchM('kakaoConfigFrm','gps_center_zip','gps_center_addr1','gps_center_addr2')"></span>
                    <br>
                    <input type="text" name="gps_center_addr1" value="<?php echo $cfg['gps_center_addr1']; ?>" class="input" size="40" maxlength="100" readonly>
                    <input type="hidden" name="gps_center_addr2" value="<?php echo $cfg['gps_center_addr2']; ?>" class="input" size="10" maxlength="100">
					<ul class="list_info tp">
						<li>관리자가 위치정보 접근 허용 설정을 <strong>사용하지 않거나</strong>, 혹은 회원이 위치정보 접근 허용을 <strong>거부</strong> 하였을 시 해당 주소를 중심으로 매장안내 서비스를 제공합니다.</li>
					</ul>
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
                <th scope="row">레이아웃 타입</th>
                <td>
                    <label><p><input type="radio" name="location_layout_type" value="1" <?php echo checked($cfg['location_layout_type'], '1'); ?>> A 타입</p> <div class="img"></div></label>
                    <label><p><input type="radio" name="location_layout_type" value="2" <?php echo checked($cfg['location_layout_type'], '2'); ?>> B 타입 </p><div class="img"></div></label>
                    <label><p><input type="radio" name="location_layout_type" value="3" <?php echo checked($cfg['location_layout_type'], '3'); ?>> C 타입 </p><div class="img"></div></label>
                    <label><p><input type="radio" name="location_layout_type" value="4" <?php echo checked($cfg['location_layout_type'], '4'); ?>> D 타입 </p><div class="img"></div></label>
                </td>
            </tr>
            <?php
/* 추후 개발 ( 연동 작업만 완료 )
            <tr>
                <th scope="row">지도 노출 기준 설정</th>
                <td>
                    <label><input type="radio" name="use_store_partner_yn" value="N" <?php echo checked($cfg['use_store_partner_yn'], 'N'); ?>> 본사 기준</label>
                    <label><input type="radio" name="use_store_partner_yn" value="Y" <?php echo checked($cfg['use_store_partner_yn'], 'Y'); ?>> 입점몰 기준</label>
                </td>
            </tr>
*/?>
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
