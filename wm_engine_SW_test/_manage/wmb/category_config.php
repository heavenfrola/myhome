<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  윙Mobile 매장분류 설정
	' +----------------------------------------------------------------------------------------------+*/
	$ctype = numberOnly($_GET['ctype']);
	$ctype=(empty($ctype)) ? 1 : $ctype;
?>
<script type="text/javascript">
	var ctype = '<?=$ctype?>';
</script>
<script type="text/javascript" src="<?=$engine_url?>/_manage/category.js"></script>
<div class="box_title first">
	<h2 class="title">매장분류 설정</h2>
</div>
<div class="box_middle left">
	<ul class="list_msg">
		<li><?=$cfg['mobile_name']?> 모바일기기에서의 매장분류의 표시,숨김을 조정하는 기능입니다.</li>
		<li>PC에서의 표시,숨김에는 영향이 없습니다.</li>
		<li>매장분류 수정,삭제 처리는 <a href="./?body=product@catework" class="sclink blank">상품관리 > 기본매장분류관리</a> 에서 하실 수 있습니다.</li>
	</ul>
</div>
<div class="box_middle">
	<div id="category_all" class="none_bg">
		<div class="m_btn">
			<span class="box_btn gray"><input type="button" value="모두 열기" onclick="categoryAll(1)"></span>
			<span class="box_btn gray"><input type="button" value="모두 닫기" onclick="categoryAll(2)"></span>
		</div>
		<div class="m_icon">
			<img src="<?=$engine_url?>/_manage/image/mobile_icon_on.gif" alt="표시"> : 표시
			<img src="<?=$engine_url?>/_manage/image/mobile_icon.gif" alt="숨김"> : 숨김
		</div>
	</div>
</div>
<form method="post" action="./" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="wmb@category_config.exe">
	<div class="box_middle left">
		<div id="category_tree" style="border:1px solid #c9c9c9;">
			<?=print_cat()?>
		</div>
	</div>
	<div id="categoryContent" style="display:none"></div>
	<div class="box_bottom">
		<span class="box_btn"><input type="button" value="원래대로" onclick="location.reload();"></span>
		<span class="box_btn gray"><input type="submit" value="적용하기"></span>
	</div>
</form>