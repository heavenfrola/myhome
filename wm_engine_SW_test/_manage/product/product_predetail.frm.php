<?PHP
include_once $engine_dir.'/_engine/include/design.lib.php';
$_skin = getSkinCfg();

$_css_tmp_url = $root_url.'/'.$dir['upload'].'/wing_'.$design['skin'].'_temp.css';

$pno = numberOnly($_GET['pno']);
$idx = numberOnly($_GET['idx']);
$mobile = $_GET['mobile'];
$data=get_info($tbl['product_content_log'],"no",$idx);

?>

<form name="preDetailFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
<input type="hidden" name="body" value="product@product_predetail.exe">
<input type="hidden" name="pno" value="<?=$pno?>">
<input type="hidden" name="idx" value="<?=$idx?>">
<input type="hidden" name="mobile" value="<?=$mobile?>">
<div id="popupContent" class="popupContent layerPop" style="width:100%; max-width:100%; box-sizing:border-box;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">변경/복구 전 상품상세설명 보기</div>
	</div>
	<div id="popupContentArea">
		<div id='contentField'><?=stripslashes($data['content2'])?></div>
		<div id="fastBtn">
			<span class="box_btn blue"><input type='button' onclick='edtDetail(this.form);' value='복구하기' /></span>
			<span class="box_btn"><input type="button" onclick='self.close()' value='닫기'></span>
		</div>
	</div>
</div>
</form>
<script type="text/javascript">

	$(window).load(function() {
		if(!opener.document) return;

		opener.blindContent(1);
	});

	function edtDetail(f) {
		if(confirm('복구하기 전 상품상세설명 이외 수정된 상품정보는 유실되므로, 저장하시기 바랍니다. 상품상세내용을 변경하시겠습니까?')) {
			f.submit();
			opener.location.reload();
		}
	}

</script>
<?close(1);?>