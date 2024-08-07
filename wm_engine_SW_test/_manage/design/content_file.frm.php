<ul class="icon_list" style="float:left; border:1px solid #ccc; margin:5px 0;">
<?PHp

	$cdir=$dir['upload']."/".$dir['content'];
	makeFullDir($cdir);
	$ci=0;
	$open_dir=opendir($root_dir."/".$cdir);
	$preview_script=array();
	while($cfile=readdir($open_dir)){
		if($cfile!="." && $cfile!="..") {
			$cimg=$cdir."/".$cfile;
			$cimg2=$root_url."/".$cdir."/".$cfile;
			if(!is_file($root_dir."/".$cimg)) continue;

			list($width, $height)=@getimagesize($root_dir."/".$cimg);
			$is=setImageSize($width,$height,100,100);
			$imgstr=$is[2];

			$is2=setImageSize($width,$height,500,500);
			$imgstr2=$is2[2];
			$preview_script[$ci]="<img src=\"$cimg2\" $imgstr2>";
			?>
			<li>
				<dt style="height:100px; border:1px solid #eee; background:#f7f7f7;"><a href="<?=$cimg2?>" target="_blank"><img src="<?=$cimg2?>" alt="" <?=$imgstr?>></a></dt>
				<dd>
					<a href="javascript:delCateImg('<?=$cfile?>')">삭제</a>
					 | <a href="#" onclick="parent.R2Na_Exec('content2','InsertImage','<?=$cimg2?>')"><b>삽입</b></a>
					 | <a href="javascript:tagCopy('<img src=<?=$cimg2?>>')">태그</a>
				<dd>
			</li>
			<?
		}
	}
	closedir($open_dir);
	$ci++;

	if($ci==0) echo "<li>업로드된 이미지가 없습니다</li>";
	?>
</ul>

<form name="mfup_frm" method="post" action="./" target="hidden<?=$now?>" enctype="multipart/form-data" style="clear:left;">
	<input type="hidden" name="img" value="">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="body" value="design@content_file.exe">
	<input type="file" name="upfile" class="input" size="50">
	<span class="btn blue small"><input type="submit" value="업로드"></span>
</form>

<script language="JavaScript">
	window.onload=function() {
		selfResize();
	}

	function delCateImg(fname){
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