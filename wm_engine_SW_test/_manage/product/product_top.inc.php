<?PHP

	include_once $engine_dir.'/_manage/product/product_hdd.exe.php';

?>
<div id="contentTop">
	<h2 class="subTitle"><?=$current_menu->name[0]?></h2>
	<dl class="total">
		<dt class="hidden">현황</dt>
		<dd class="first-child">총 <span class="num" style="color:#e05e17"><?=$stats['total']?></span></dd>
		<dd>정상 <span class="num" style="color:#77902d"><?=$stats[2]?></span></dd>
		<dd>숨김 <span class="num" style="color:#999"><?=$stats[4]?></span></dd>
		<dd>품절 <span class="num" style="color:#999"><?=$stats[3]?></span></dd>
	</dl>
	<?if(count($_rel_preload) > 0){?>
	<dl class="totalR">
		<dt><img src="<?=$engine_url?>/_manage/image/icon/ic_rel.png"> 관련기능</dt>
		<?
		$relmidx = 0;
		foreach($_rel_preload as $key => $val){
			$rel_firstChild = $relmidx == 0 ? 'first-child' : '';
			$relmidx++;
		?>
			<dd class="<?=$rel_firstChild?>"><a href="<?=$val?>"><?=$key?></a></dd>
		<?}?>
	</dl>
	<?}?>
</div>