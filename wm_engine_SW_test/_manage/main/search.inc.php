<?PHP

	$_relmenu = array();
	if(count($_rel_preload) > 0) {
		foreach($_rel_preload as $key => $val) {
			list($rtarget, $val) = explode('___', $val);
			$_relmenu[] = array(
				'rTarget' => $rtarget,
				'rLink' => $val,
				'rName' => $key
			);
		}
	}

?>
<h1><img src="<?=$engine_url?>/_manage/image/common/title_total_search.png" alt="W. 통합검색"></h1>
<form method="post" onSubmit="return false" class="search_form">
	<div>
		<input type="text" name="tsearch_str" value="" placeholder="메뉴, 매뉴얼, FAQ, 회원">
		<input type="submit" value="검색" class="btn">
	</div>
</form>
<p class="nodata" style="display:none;">검색어를 입력해주세요.</p>
<div class="tsearch_before">
	<?php if (count($_rel_preload) > 0) { ?>
	<div class="box relative">
		<h2>관련기능</h2>
		<ul>
			<?php foreach($_relmenu as $val) { ?>
			<li><a href="<?=$val['rLink']?>" target="<?=$val['rTarget']?>"><?=$val['rName']?></a></li>
			<?php } ?>
		</ul>
	</div>
	<?php } ?>
	<div class="box search_cnt faq_big" style="display:none;">
		<h2>FAQ</h2>
		<a href="https://redirect.wisa.co.kr/smartwing_faq" target="_blank" class="quick">바로가기 &gt</a>
		<ul class="search_faq_list">

		</ul>
	</div>
</div>
<div class="tsearch_after">
	<span class="searchkeytest"></span>
	<ul class="tab">
		<li><a onclick="tsearchOpen(null, '');" class="all active">전체</a></li>
		<?php if ($admin['level'] < 4) { ?>
		<li><a onclick="tsearchOpen(null, 'member');" class="member">회원</a></li>
		<?php } ?>
		<li><a onclick="tsearchOpen(null, 'menu');" class="menu">메뉴</a></li>
		<?php if ($admin['level'] < 4) { ?>
		<li><a onclick="tsearchOpen(null, 'manual');" class="manual">매뉴얼</a></li>
		<?php } ?>
		<li><a onclick="tsearchOpen(null, 'faq');" class="faq">FAQ</a></li>
	</ul>
	<?php if ($admin['level'] < 4) { ?>
	<div class="box search_cnt member">
		<h2>회원(<span class="search_member_cnt">0</span>)</h2>
		<ul class="search_member_list">

		</ul>
		<a class="search_member_more more" onclick="tsearchOpen(null, 'member');" class="more"><span class="search_member_more_cnt"></span>개 검색결과 더보기 &gt</a>
		<p class="search_member_nodata nodata">검색결과가 없습니다.</p>
	</div>
	<?php } ?>
	<div class="box search_cnt menu">
		<h2>메뉴(<span class="search_menu_cnt">0</span>)</h2>
		<ul class="search_menu_list">

		</ul>
		<a class="search_menu_more more" onclick="tsearchOpen(null, 'menu');" class="more"><span class="search_menu_more_cnt"></span>개 검색결과 더보기 &gt</a>
		<p class="search_menu_nodata nodata">검색결과가 없습니다.</p>
	</div>
	<?php if ($admin['level'] < 4) { ?>
	<div class="box search_cnt manual">
		<h2>매뉴얼(<span class="search_manual_cnt">0</span>)</h2>
		<a href="https://redirect.wisa.co.kr/smartwing_manual" target="_blank" class="quick">바로가기 &gt</a>
		<ul class="search_manual_list">

		</ul>
		<a class="search_manual_more more" onclick="tsearchOpen(null, 'manual');" class="more"><span class="search_manual_more_cnt"></span>개 검색결과 더보기 &gt</a>
		<p class="search_manual_nodata nodata">검색결과가 없습니다.</p>
	</div>
	<?php } ?>
	<div class="box search_cnt faq">
		<h2>FAQ(<span class="search_faq_cnt">0</span>)</h2>
		<a href="https://redirect.wisa.co.kr/smartwing_faq" target="_blank" class="quick">바로가기 &gt</a>
		<ul class="search_faq_list">

		</ul>
		<a class="search_faq_more more" onclick="tsearchOpen(null, 'faq');" class="more"><span class="search_faq_more_cnt"></span>개 검색결과 더보기 &gt</a>
		<p class="search_faq_nodata nodata">검색결과가 없습니다.</p>
	</div>
</div>
<!-- //검색 후 -->
<a onclick="closeTsearch();" class="close">닫기</a>