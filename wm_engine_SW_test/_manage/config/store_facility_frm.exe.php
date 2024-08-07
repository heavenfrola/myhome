<?PHP
/**
 * [매장지도] 시설 안내 등록 폼
 */
printAjaxHeader();

$fno = numberOnly($_GET['fno']);
if($fno) {
	$title = "시설안내 수정";

	$data = get_info($tbl['store_facility_set'], "no", $fno);
	if(!$data['no']) {
		alert('존재하지 않는 항목입니다.');
		javac('fdFrm.close();');
	}
}
else {
	$title = "시설안내 추가";
}

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
    <div id="header" class="popup_hd_line">
        <h1 id="logo"><img src="<?php echo $engine_url; ?>/_manage/image/wisa.gif" alt="WISA."></h1>
        <div id="mngTab_pop">시설안내 관리</div>
    </div>
    <div id="popupContentArea">

        <form id="pfieldFrm" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" onSubmit="return checkStoreField(this)" enctype="multipart/form-data">
            <input type="hidden" name="body" value="config@store_facility.exe">
            <input type="hidden" name="fno" value="<?php echo $fno; ?>">
            <input type="hidden" name="exec" value="register">
            <div class="box_title first">
                <h2 class="title"><?php echo $title;?></h2>
            </div>
            <table class="tbl_row">
                <caption class="hidden"><?php echo $title;?></caption>
                <colgroup>
                    <col style="width:20%">
                </colgroup>
                <tr>
                    <th scope="row"><strong>시설명</strong></th>
                    <td><input type="text" name="name" value="<?php echo inputText($data['name']); ?>" class="input" size="30" placeholder="시설명을 입력하세요. (ex. 주차가능)"></td>
                </tr>
                <tr>
                    <th scope="row">시설 아이콘</th>
                    <td>
                        <input type="file" name="upfile1" class="input">
						<?php if($data['updir'] && $data['upfile1']) {
							$img = $root_url ."/".$data['updir'] ."/". $data['upfile1'];
							$del = delImgStr($data, 1);
							if($del) echo $del;
							?>
						<?php }?>
                        <ul class="list_info tp">
                            <li>해당 시설의 의미를 나타내는 적절한 아이콘 <strong>(50x50 권장)</strong> 을 사용하세요.</li>
                            <li><strong>jpg, png, gif</strong> 파일만 가능합니다.</li>
                        </ul>
                    </td>
                </tr>
                <tr class="ftypeSelect">
                    <th scope="row">설명</td>
                    <td>
                        <textarea name="content" class="txta" placeholder="내용을 입력하세요."><?php echo stripslashes($data['content']); ?></textarea>
                    </td>
                </tr>
            </table>
            <div class="box_bottom">
                <span class="box_btn blue"><input type="submit" value="확인"></span>
                <span class="box_btn"><input type="button" value="닫기" onclick="fdFrm.close();"></span>
            </div>
        </form>
    </div>
</div>