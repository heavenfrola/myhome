<?PHP

	$cfg['show_use']=$cfg['show_use'] ? $cfg['show_use'] : "N";
	$cfg['show_make_default']=$cfg['show_make_default'] ? $cfg['show_make_default'] : 2;
	$cfg['show_image_no']=$cfg['show_image_no'] ? $cfg['show_image_no'] : 2;

	$status = 1;

	$show_status = $wec->get(520, null, 1);
	$resp_stat = $show_status[0]->stat[0];

	$resp_stat=2;

	$dstest = $dstest ? false : true;

    function getCompareDataUrl($type=1, $sf="", $filetype=""){
        global $root_dir, $p_root_url, $dir, $s;
        $file=$_dir="";

        if($filetype == 1){ // 전체
            $filename="show_sum.txt";
        }elseif($filetype == 2){ // 요약
            $filename="show_new.txt";
        }
        if($type == 1) $_dir=$root_dir."/";
        elseif($type == 2) $_dir=$p_root_url."/";

        $file=$_dir.$dir[upload]."/".$dir[compare]."/daumDB/sh";
        if($sf) $file.="/".$filename;

        return $file;
    }

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading();">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="daum_show_linkage">
	<div class="box_title first">
		<h2 class="title">카카오 쇼핑하우</h2>
	</div>
	<div class="box_middle">
		<p class="explain left">카카오 쇼핑하우 입점업체로 등록 하고자 할 경우 여러 업체간의 정보 비교를 위해 카카오 쇼핑하우의 요구사항에 맞는 엔진파일을 생성하고 등록하여야 합니다.</p>
	</div>
	<table class="tbl_row">
		<caption class="hidden">카카오 쇼핑하우</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">엔진파일 생성</th>
			<td>
				<?if($resp_stat == 2){?>
				<input type="radio" name="show_use" id="s1" value="Y" <?=checked($cfg['show_use'],"Y")?>> <label for="s1" class="p_cursor">사용 </label>
				<p class="explain">(먼저 카카오 쇼핑하우에 입점 신청을 하여야 합니다)</p>
				<input type="radio" name="show_use" id="s2" value="N" <?=checked($cfg['show_use'],"N")?>> <label for="s2" class="p_cursor">사용 안함</label>
				<p class="explain">(카카오 쇼핑하우에 입점 하지 않을경우 사용하지 않는것이 좋습니다)</p>
				<?} else if($resp_stat == 3) {?>
				<p class="explain">입점신청이 <span class="p_color2">반려 되었습니다.</span> 위사 마케팅팀과 상의 하시어 반려 사유를 확인하신 후 재신청 해 주시기 바랍니다.</p>
				<?} else if($resp_stat == 1) {?>
				<p class="explain">입점신청이 <span class="p_color2">대기중입니다.</span> 카카오쇼핑하우에서 가입이 승인 된 이후 서비스를 이용하실 수 있습니다.</p>
				<?} else {?>
				<p class="explain">카카오 쇼핑하우 가입신청이 되어있지 않습니다.</p>
				<?}?>
			</td>
		</tr>
		<tr>
			<th scope="row">엔진파일 업데이트</th>
			<td>
				<input type="radio" name="show_make_default" id="t3" value="1" <?=checked($cfg['show_make_default'],"1")?>> <label for="t3" class="p_cursor">상품정보 변경시 엔진파일 업데이트 진행</label>
				<p class="explain">(상품정보 변경시 엔진파일도 함께 업데이트가 이루어 지므로 처리 시간이 길어 집니다)</p>
				<input type="radio" name="show_make_default" id="t4" value="2" <?=checked($cfg['show_make_default'],"2")?>> <label for="t4" class="p_cursor">상품정보 변경시 엔진파일 업데이트 하지 않음</label>
				<p class="explain">(상품정보 변경과 엔진파일 변경을 함께 처리 하지 않습니다)</p>
			</td>
		</tr>
		<tr>
			<th scope="row">상품이미지</th>
			<td>
				<input type="radio" name="show_image_no" id="img1" value="2" <?=checked($cfg['show_image_no'],"2")?>> <label for="img1" class="p_cursor">중간 이미지</label>
				<input type="radio" name="show_image_no" id="img2" value="3" <?=checked($cfg['show_image_no'],"3")?>> <label for="img2" class="p_cursor">작은 이미지</label>
				<input type="radio" name="show_image_no" id="img3" value="1" <?=checked($cfg['show_image_no'],"1")?>> <label for="img3" class="p_cursor">큰 이미지</label>
				<p class="explain">(gif, jpg 양식(애니 gif 사용 금지), 사이즈 최소: 150*150 픽셀, 권장 : 500*500 픽셀 이상)</p>
			</td>
		</tr>
		<tr>
			<th scope="row">제외 카테고리</th>
			<td>
                <a href="?body=product@catework&pgCode=2010" class="box_btn_s icon setup" style="margin-top:2px"></a>
				<span class="explain">(개인결제창 카테고리로 설정하시면 해당 상품은 EP에 수록되지 않습니다)</span>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<form name="engineFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="openmarket@show.exe">
	<input type="hidden" name="config_code" value="daum_show_engine">
	<input type="hidden" name="filetype">
	<div class="box_title">
		<h2 class="title">카카오 쇼핑하우 엔진파일 생성</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">카카오 쇼핑하우 엔진파일 생성</caption>
		<colgroup>
			<col style="width:15%">
			<col>
			<col style="width:15%">
		</colgroup>
		<?if($status) {?>
		<?
			$_fn=array(1=>"요약", 2=>"전체");
			for($ii=1; $ii<=2; $ii++){
		?>
		<tr>
			<th><?=$_fn[$ii]?> 정보</th>
			<td>
				<?
				$file_dir=getCompareDataUrl(1, 1, $ii);
				if(@is_file($file_dir)){
					$r=getCompareDataUrl(2, 1, $ii);
					$time=filemtime($file_dir);
					echo "<a href=\"".$r."\" target=\"_blank\" class=\"p_color\">".$r."</a> (".date("Y/m/d H:i",$time).")";
				}else{
					echo "파일이 생성되지 않았습니다";
				}
				?>
			</td>
			<td>
				<span class="box_btn_s"><input type="button" value="주소복사" onclick="tagCopy('<?=$r?>');"></span>
				<span class="box_btn_s"><input type="button" value="업데이트" onclick="makeFile(this.form, '<?=$ii?>');"></span>
			</td>
		</tr>
		<?}?>
		<?} else {?>
		<tr>
			<td colspan="3">
				서비스가 시작되지 않았습니다<br>1:1 게시판을 통해 eAD 사업부로 별도 문의 해 주시기 바랍니다.
			</td>
		</tr>
		<?}?>
	</table>
</form>

<script language="JavaScript">
	function makeFile(f, filetype){
		if(!confirm('상품수가 많을 경우 처리시간이 길어질 수 있습니다')) return;
        printLoading();
		f.filetype.value=filetype;
		f.submit();
	}
</script>