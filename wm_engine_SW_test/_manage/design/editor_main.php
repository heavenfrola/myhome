<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  페이지 편집 - 메인
	' +----------------------------------------------------------------------------------------------+*/

?>
<div class="box_title first">
	<h2 class="title">페이지 편집</h2>
</div>
<div class="box_middle">
	<p class="explain left">편집을 원하시는 페이지를 클릭하시면 편집창으로 이동됩니다.</p>
</div>
<table class="tbl_editor">
	<?php
		$edit_pg=$_GET['edit_pg'];
		if($edit_pg) list($pg1, $pg2)=explode("/", $edit_pg);
	?>
	<tr>
	<?php
		$edit_pg=$_GET['edit_pg'];
		if($edit_pg) list($pg1, $pg2)=explode("/", $edit_pg);

		$ii=1;
		$_pg_title="";
		$_divh=array(1=>130, 4=>350, 7=>250);
		foreach($_edit_list as $key=>$val){
			if($pg1 == $ii) $_pg_title=$key;
			$_divh2=$_divh[$ii] ? $_divh[$ii] : $_divh2;
	?>
		<td>
			<h3><?=$key?></h3>
			<ul>
				<?php
					$jj=1;
					foreach($_edit_list[$key] as $key2=>$val){
						if($pg1 == $ii && $pg2 == $jj){
							$_pg_title .= " > ".$val;
							$_edit_pg=$key2;
						}
						$_link="./?body=".$body."&type=".$_GET['type']."&edit_pg=".urlencode($ii."/".$jj);
						if($key == "게시판정보"){
							if(@preg_match("/^board_index/", $key2)) continue;
							if(!@preg_match("/\.$_skin_ext[p]/", $key2)){
								$_link=$key2;
								$val .= " <img src=\"$engine_url/_manage/image/shortcut2.gif\" alt=\"바로가기\" width=\"11\" height=\"11\" align=\"absmiddle\">";
							}
						}
				?>
				<li><a href="<?=$_link?>"><?=($pg1 == $ii && $pg2 == $jj) ? "<b>" : "";?><?=$val?><?=($pg1 == $ii && $pg2 == $jj) ? "</b>" : "";?></a></li>
				<?php
					$jj++;
					}
				?>
			</ul>
		</td>
		<?php
				if($ii % 4 == 0){
					echo "</tr><tr>";
				}
				$ii++;
			}
		?>
	</tr>
</table>
