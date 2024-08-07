<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  단체메일 첨부파일
	' +----------------------------------------------------------------------------------------------+*/

	$temp = numberOnly($_GET['temp']);
	checkBlank($temp,"필수값(temp)을 입력해주세요.");

	$temp_dir="temp_".$temp;
	$updir1=$dir['upload']."/".$dir['mail']."/";
	$updir=$updir1.$temp_dir;

	if(is_dir($root_dir."/".$updir1)) { // 임시폴더 제거
		$open_dir=opendir($root_dir."/".$updir1);
		while($cfile=readdir($open_dir)){
			if($cfile!=$temp_dir && $cfile!="." && $cfile!=".." && preg_match("/temp/",$cfile)) {
				$rmdir=$root_dir."/".$updir1.$cfile;
				delAllFile($rmdir);
				rmdir($rmdir);
			}
		}
		closedir($open_dir);
	}

	// 임시폴더 생성
	$cdir=$updir;
	if($bizmail == 'Y') $cdir=str_replace("temp", "real", $cdir);
	makeFullDir($cdir);

?>
<style type="text/css" title="">
body {background:#fff;}
</style>
<ul class="icon_list" style="float:left; margin:5px 0; border:1px solid #ccc;">
	<?PHP
		$ci=0;
		$open_dir=opendir($root_dir."/".$cdir);
		$preview_script=array();
		while($cfile=readdir($open_dir)){
			if($cfile!="." && $cfile!="..") {
				$cimg=$cdir."/".$cfile;
				$cimg2=$root_url."/".$cdir."/".$cfile;

				list($width, $height)=getimagesize($root_dir."/".$cimg);
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
			<? if($bizmail == 'Y') { ?>
			| <a href="#" onclick="window.parent.postMessage('<?=$cimg2?>', '*'); return false;"><b>삽입</b></a>
			<? } else { ?>
			| <a href="javascript:parent.R2Na_Exec('content2', 'InsertImage', '<?=$cimg2?>');"><b>삽입</b></a>
			<? } ?>
			| <a href="javascript:tagCopy('<img src=<?=$cimg2?>>')">태그</a>
		<dd>
	</li>
	<?
				$ci++;
			}
		}
		closedir($open_dir);
		if($ci==0) echo "<li>업로드된 이미지가 없습니다</li>";
	?>
</ul>
<form name="mfup_frm" method="post" action="./" target="hidden<?=$now?>" enctype="multipart/form-data" style="clear:left;">
	<input type="hidden" name="img" value="">
	<input type="hidden" name="exec" value="upload">
	<input type="hidden" name="body" value="member@group_mail.exe">
	<input type="hidden" name="bizmail" value="<?=$bizmail?>">
	<input type="hidden" name="temp" value="<?=$temp?>">
	<input type="file" name="upfile" class="input" size="50">
	<span class="box_btn_s blue"><button type="submit">업로드</button></span>
</form>

<script language="JavaScript">
	window.onload=function() {
		selfResize();
		parent.selfResize();
	}
	function delCateImg(fname){
		if (!confirm('파일을 삭제하시겠습니까?'))
		{
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