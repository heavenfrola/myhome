<?php

/**
 * 구글 판매자센터 피드
 **/

$scfg->def('use_ge', 'N');
$scfg->def('ge_image_no', '3');
$scfg->def('add_prd_img', '3');
$scfg->def('ge_brand', '');

define('__FEED_URL__', $root_url.'/_data/compare/google/merchants_feed.txt');

function getImageNameByNo($no) {
    switch($no) {
        case '1' : return '대이미지';
        case '2' : return '중이미지';
        case '3' : return '소이미지';
    }
    return '추가이미지'.($no-3);
}

// 브랜드 연결
$brand = array();
if ($cfg['xbig_mng'] == 'Y') {
    $brand['xbig'] = $cfg['xbig_name_mng'].' 분류';
}
if ($cfg['ybig_mng'] == 'Y') {
    $brand['ybig'] = $cfg['ybig_name_mng'].' 분류';
}
$res = $pdo->iterator("select no, name from {$tbl['product_filed_set']} where category='0' order by name asc");
foreach ($res as $fd) {
    $brand['field@'.$fd['no']] .= '[추가항목] '.stripslashes($fd['name']);
}

?>
<?php if (preg_match('/\.mywisa\.(com|co\.kr)$/', $root_url) == true) { ?>
<div class="msg_topbar warning left">
    임시도메인(<?=$root_url?>) 사용 중입니다.
    정식 도메인 설정 후 <strong>확인</strong>버튼을 다시 클릭해주세요.
</div>
<br>
<?php } ?>

<form method="post" action="./index.php" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="ge">

	<div class="box_title first">
		<h2 class="title">구글 쇼핑 연동</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">구글 쇼핑 연동</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">사용 여부</th>
			<td>
				<label><input type="radio" name="use_ge" value="Y" <?=checked($cfg['use_ge'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_ge" value="N" <?=checked($cfg['use_ge'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">상품이미지</th>
			<td>
				<?php for ($i = 1; $i <= $cfg['add_prd_img']; $i++) { ?>
				<label>
                    <input type="radio" name="ge_image_no" value="<?=$i?>" <?=checked($cfg['ge_image_no'], $i)?>>
                    <?=getImageNameByNo($i)?>
                </label>
				<?php } ?>
				<ul class="list_msg">
					<li>사용하지 않거나 업로드 되지 않은 이미지를 선택하시면 상품의 이미지 정보가 정상적으로 제공되지 않습니다.</li>
					<li>설정한 종류의 이미지가 업로드 되지 않은 상품일 경우 대이미지-중이미지-소이미지 순서로 등록된 이미지를 설정합니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">브랜드 연결</th>
			<td>
				<?=selectArray($brand, 'ge_brand', false, '사용안함', $cfg['ge_brand'])?>
			</td>
		</tr>
    </table>
    <div class="box_middle2">
        <ul class="list_info left">
            <li>피드의 상품 설명은 '요약설명'이 입력되고 미입력 시 상품명으로 대체되며, 태그는 자동 제거됩니다.</li>
        </ul>
    </div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<?php if($cfg['use_ge'] == 'Y') { ?>
<div class="box_title">
	<h2 class="title">
        구글 쇼핑 피드 생성
        <div class="btns">
            <span class="box_btn_s icon copy2"><input type="button" value="즉시 갱신" onclick="epRefresh()"></span>
        </div>
    </h2>
</div>
<table class="tbl_row">
	<caption class="hidden">구글 쇼핑 피드 생성</caption>
	<colgroup>
		<col style="width:15%">
		<col>
		<col style="width:15%">
	</colgroup>
	<tr>
		<th>피드 주소</th>
		<td>
            <a href="<?=__FEED_URL__?>" class="p_color" target="_blank"><?=__FEED_URL__?></a>
        </td>
		<td class="right">
			<span class="box_btn_s"><input type="button" value="주소복사" class="clipboard" data-clipboard-text="<?=__FEED_URL__?>"></span>
		</td>
	</tr>
</table>
<div class="box_middle2">
    <ul class="list_info left">
        <li>구글 판매자센터에서 <a href="https://support.google.com/merchants/answer/1219255" target="_blank">예약된 가져오기</a> 설정 시 업데이트된 상품 정보가 자동 업로드 됩니다.</li>
        <li>피드 내용은 매일 오후 8시 ~ 9시 사이에 자동 갱신됩니다.</li>
    </ul>
</div>
<?php } ?>
<script>
new Clipboard('.clipboard').on('success', function() {
    window.alert('복사되었습니다.');
});

function epRefresh()
{
    printLoading();
    $.post(manage_url+'/main/exec.php?exec_file=cron/cron_ge.exe.php', {"urlfix": "Y", "site_key": "<?=$_we['wm_key_code']?>"}, function() {
        removeLoading();
    });
}
</script>