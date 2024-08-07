<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 상단 디자인 이미지 업로드
	' +----------------------------------------------------------------------------------------------+*/

?>
<style type="text/css" title="">
body {background:#fff;}
</style>
<table style="width:100%;">
	<tr>
		<?

			$cdir=$dir['upload']."/".$dir['board_common'];
			makeFullDir($cdir);
			$ci=0;
			$open_dir=opendir($root_dir."/".$cdir);
			$preview_script=array();
			while($cfile=readdir($open_dir)){
				if($cfile!="." && $cfile!="..") {
					$cimg=$cdir."/".$cfile;
					$cimg2=$root_url."/".$cdir."/".$cfile;
					if(!is_file($root_dir."/".$cimg)) continue;

					list($width, $height)=getimagesize($root_dir."/".$cimg);
					$is=setImageSize($width,$height,300,100);
					$imgstr=$is[2];

					$is2=setImageSize($width,$height,500,500);
					$imgstr2=$is2[2];
					$preview_script[$ci]="<img src=\"$cimg2\" $imgstr2>";
		?>
		<td class="center">
			<div><a href="<?=$cimg2?>" target="_blank"><img src="<?=$cimg2?>" alt="" <?=$imgstr?>></a></div>
			<p>
				<a href="javascript:delCateImg('<?=$cfile?>')">삭제</a>
				| <a href="javascript:parent.R2Na_Exec('content2','InsertImage','<?=$cimg2?>');"><b>삽입</b></a>
				| <a href="javascript:tagCopy('<img src=<?=$cimg2?>>')">태그</a>
			</p>
		</td>
		<?
					$ci++;
					if($ci%4==0) {
						echo "</tr><tr>";
					}
				}
			}
			while($ci%4!=0 && $ci>0) {
				$ci++;
				echo "<td style=\"width:25%\">&nbsp;</td>";
			}
			closedir($open_dir);

			if($ci==0) {
		?>
		<td class="center"><u>업로드된 이미지가 없습니다</u></td>
		<?
			}
		?>
	</tr>

</table>
<form name="mfup_frm" method="post" action="./" target="hidden<?=$now?>" enctype="multipart/form-data" class="center">
	<input type="hidden" name="img" value="">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="body" value="board@board_file.exe">
	<input type="file" name="upfile" class="input" size="70">
	<span class="box_btn_s"><input type="submit" value="업로드"></span>
</form>

<script language="JavaScript">
	window.onload=function() {
		selfResize();
		parent.selfResize();
	}

	function delCateImg(fname) {
		if (!confirm('파일을 삭제하시겠습니까?')) {
			return;
		}
		f=document.mfup_frm;
		f.img.value=fname;
		f.exec.value='delete';
		f.submit();
	}

	<?foreach($preview_script as $key=>$val) {?>
		helptext[<?=$key?>]='<?=$val?>';
	<?}?>
</script>