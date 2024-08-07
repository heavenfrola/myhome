<?php

	include_once $engine_dir.'/_config/set.country.php'; // 국가정보
	asort($_nations_kr);

	if(!$_GET['delivery_com']) $_GET['delivery_com'] = $pdo->row("select no from `".$tbl['delivery_url']."` where overseas_delivery='O' order by `sort`, `no` desc limit 1");

	## 배송사 목록
	$sql="select * from `".$tbl['delivery_url']."` where overseas_delivery='O' order by `sort`, `no` desc";
	$res = $pdo->iterator($sql);
	$delivery_list="";
	$chk=0;
    foreach ($res as $data) {
		$str="$data[name]";
		$chk = ($_GET['delivery_com']==$data['no'])?'selected':'';
		$delivery_list.="<option value=\"$data[no]\" $chk>$str</option>\n";
		$chk++;
	}

	if(!$chk) msg("설정된 배송업체가 없습니다. 배송업체를 먼저 추가하세요.","?body=config@delivery_prv");

	$cols = 0;
	## 배송 지역 목록
	if($_GET['delivery_com']){
		$sql = "select * from ${tbl['os_delivery_area']} where delivery_com='${_GET['delivery_com']}' order by `order` asc";
		$area_row = $pdo->iterator($sql);
		if($area_row) $cols = $area_row->rowCount();
	}

    $nations = array();
    foreach ($area_row as $key => $data) {
        $tmp = $pdo->iterator("select * from ${tbl['os_delivery_country']} where delivery_com=:delivery_com and area_no=:area_no order by country_code", array(
            ':delivery_com' => $_GET['delivery_com'],
            ':area_no' => $data['no'],
        ));
        $nations[$key] = array();
        foreach ($tmp as $nation) {
            $name_kr = $_nations_kr[$nation['country_code']];

            $nations[$key][$name_kr] = "$name_kr({$nation['country_code']})";
        }
        // 한글 순서로 정렬
        ksort($nations[$key]);
    }

?>
<style>
.file_input_hidden {position:absolute; left:0; top:10px; height:30px; width:105px; opacity:0; cursor:pointer;}
</style>
<form name="deliveryFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return deliver_chk(this);"  enctype="multipart/form-data">
	<input type="hidden" name="body" value="config@oversea_delivery.exe">
	<input type="hidden" name="exec" value="remove">
	<div class="box_title first">
		<h2 class="title">
            해외 배송 설정
            <div class="total">
                <span class="box_btn_s icon copy2"><input type="button" value="엑셀업로드"></span>
                <input type="file" name="excel_file" class="file_input_hidden" onchange="upExcelFile('<?=$_GET['delivery_com']?>')">
                <span class="box_btn_s icon excel"><input type="button" value="엑셀저장" onclick="makeExcelSample('<?=$_GET['delivery_com']?>')"></span>
            </div>
        </h2>
	</div>
    <div class="box_middle left">
        <div class="list_info">
            <p>엑셀업로드 시 국가코드를 참고하세요. <a href="#" onclick="downCountryCode(); return false;" class="p_color">다운로드</a></p>
        </div>
    </div>
	<table class="tbl_row">
		<caption class="hidden">배송 지역 관리</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">배송 업체</th>
			<td>
				<select id="select" name="delivery_com">
					<?=$delivery_list?>
				</select>
				<a href="?body=config@delivery_prv" class="sclink3 none_opacity">설정</a>
			</td>
		</tr>
    </table>
    <table class="tbl_col nonbd_top">
        <colgroup>
            <col style="width:50px">
            <col style="width:150px">
            <col>
            <col style="width:80px">
            <col style="width:80px">
        </colgroup>
        <thead>
            <tr>
                <th scope="col"><input type="checkbox" class="all_check"></th>
                <th scope="col">배송지 별칭</th>
                <th scope="col">국가명</th>
                <th scope="col">수정</th>
                <th scope="col">삭제</th>
            </tr>
        </thead>
        <tbody>
			<?php if ($area_row->rowCount() > 0) { ?>
            <?php foreach ($area_row as $key => $data) {?>
            <tr>
                <td><input type="checkbox" name="no[]" class="sub_check" value="<?=$data['no']?>"></td>
                <td><?=$data['name']?></td>
                <td class="left">
                    <ul class="list_common5 col3">
                    <?php foreach ($nations[$key] as $val) {?>
                        <li><?=$val?></li>
                    <?php }?>
                    </ul>
                </td>
                <td><span class="box_btn_s"><input type="button" value="수정" class="editArea" data-no="<?=$data['no']?>"></span></td>
                <td><span class="box_btn_s"><input type="button" value="삭제" class="removeArea" data-no="<?=$data['no']?>"></span></td>
            </tr>
            <?php }?>
			<?php } else { ?>
			<tr class="none">
				<td colspan="5"><p class="nodata">등록된 국가가 없습니다.</p></td>
			</tr>
			<?}?>
        </tbody>
    </table>
	<div class="box_bottom">
        <div class="left_area">
            <span class="box_btn_s icon delete"><input type="button" value="선택 삭제" onclick="removeArea()"></span>
        </div>
        <div class="right_area">
            <span class="box_btn_s icon regist"><input type="button" value="등록" onclick="overseaRegister.open()"></span>
        </div>
	</div>
</form>
<script>
    var overseaRegister = new layerWindow('config@oversea_delivery.pop&delivery_com=<?=$_GET['delivery_com']?>');

	$(function() {
		$('#select').change(function(){
			location.href="<?=$PHP_SELF?>?body=<?=$body?>&delivery_com="+$(this).val();
		});

        $('.editArea').click(function() {
            overseaRegister.open('&no='+$(this).data('no'));
        });

        $('.removeArea').click(function() {
            removeArea($(this).data('no'));
        });

        chainCheckbox($('.all_check'), $('.sub_check'));
	});

	function makeExcelSample(delivery_com){
		var f = document.deliveryFrm;

		f.body.value = "config@oversea_delivery_excel.exe";
		f.submit();
		f.body.value = "config@oversea_delivery.exe";
	}

	// 엑셀 업로드
	function upExcelFile(){
		var f = document.deliveryFrm;

		if(confirm($('#select option:selected').text()+" 배송사의 지역 및 국가를 첨부하실 파일의 내용으로 교체하시겠습니까?")){
            printLoading();
			f.exec.value = "excel";
			f.submit();
		}else{
			f.excel_file.value = "";
		}
	}

	function downCountryCode(){
		var f = document.deliveryFrm;

		f.exec.value = "county_sample";
		f.submit();
		f.body.value = "config@oversea_delivery.exe";
	}

    function removeArea(no) {
        if (no) {
            var param = {
                'body': 'config@oversea_delivery.exe',
                'exec':'remove',
                'no[]': no
            }
        } else {
            if($('.sub_check:checked').length < 1) {
                window.alert('삭제할 해외 배송지를 선택해주세요.');
                return false;
            }
            var param = $('form[name=deliveryFrm]').serialize();
        }

        if (confirm('해외 배송지를 삭제하시겠습니까?') == true) {
            printLoading();
            $.post('./index.php', param, function(r) {
                location.reload();
            });
        }
    }
</script>