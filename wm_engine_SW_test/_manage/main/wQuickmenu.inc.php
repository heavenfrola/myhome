<div id="contentTop">
	<h2 class="subTitle"><a href='javascript:;' onclick="document.getElementById('qmFrom').focus()"><img src="<?=$engine_url?>/_manage/image/main/quickmenu.gif" alt="QUICK MENU"></a></h2>

	<ul id='quickMenuSearch'>
		<li>
			<input type='text' id='qmFrom' size='18' class='input' value='메뉴 자동검색' onfocus='qmFocus(this,1)' onblur='qmFocus(this,2)' onkeyup='qmSearch(this,event)'>
		</li>
		<li>
			<ul id='quickSearchList'>
			</ul>
		</li>
	</ul>

	<ul class="quickMenu">
		<?=$qm_list?>
	</ul>
</div>