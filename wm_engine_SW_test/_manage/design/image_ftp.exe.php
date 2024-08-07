<?PHP

	include_once $engine_dir."/_engine/include/img_ftp.lib.php";

	extract($_GET);
	extract($_POST);

	$_dir=$root_dir."/_image";

	// 멀티 파일서버
	$use_multi_server = fsConFolder($_dir);
	if ($use_multi_server) {
		$file_server_num = $matched_server[0];
		$dirname = $file_server[$file_server_num]['file_dirname'];
	}

	if($skin){
		$skin=trim($skin);
		$_dir=$root_dir."/board/_skin/".$skin."/img";
	}

	if($exec){
		if($exec == "modify" || $exec == "delete"){

			$_w=explode("/",$w);
			$_fname=$_w[count($_w)-1];
			//$_fname=stripslashes($_fname);
			$_dir=str_replace("/".$_fname,"",$w);

			if($exec == "modify") ftpRename($_dir,$_fname,$wto);
			elseif($exec == "delete") ftpDeleteFile($_dir,$_fname);

		}elseif($exec == "newdir"){

			$_dir=($rdir) ? $rdir : $_dir;

			ftpMakeDir($_dir);

		}elseif($exec == "upload"){

			$_dir=($rdir) ? $rdir : $_dir;

			if(!$_FILES['upfile']['size']) msg("잘못된 형식의 파일입니다");
			ftpUploadFile($_dir, $_FILES['upfile'], "jpg|jpeg|gif|bmp|png|flv|swf");
			@unlink($_FILES['upfile']['tmp_name']);
?>
<script type="text/javascript">
	parent.getFileList('<?=$rdir?>');
</script>
<?
	exit();
	}

	exit("=".$_dir);
	}

	$folder_step=$_f="";
	if($rdir) {
		$folder=str_replace($_dir,"",$rdir);
		if($folder){
			$folder=explode("/",$folder);
			for($ii=0; $ii<=count($folder)-1; $ii++){
				$_f .= "/".$folder[$ii];
			}
			$_dir .= $_f;
		}
	}

	if ($use_multi_server) {
		if (eregi("^$root_dir/_image/+$dirname", $_dir)) $_dir = eregi_replace($root_dir."/_image", "/", $_dir);
		$_dir = eregi_replace($root_dir, $dirname, $_dir);

		fileServerCon($file_server_num);
		ftp_chdir($fs_ftp_con, $_dir);
		$_dir = ftp_pwd($fs_ftp_con);
		$_f = eregi_replace("^/?$dirname/_image", "", $_dir);
	}

	header('Content-type:text/html; charset='._BASE_CHARSET_);

	$odir = opendir($_dir);
	if(!$odir && !$use_multi_server) {
		echo "<center><font color=#FF0000>※ 이미지 디렉토리가 존재하지 않습니다. <a href=\"./?body=support@1to1\" target=\"_blank\">[1:1 고객센터]</a>문의 글로 접수 바랍니다.</font>";
	} else {
		$f=array();
		if ($use_multi_server) {
			$file_dir = $file_server[$file_server_num]['url'];
			$arr = ftp_nlist($fs_ftp_con, ".");
			$f1[]= "..";
			foreach ( $arr as $fname) {
				$fn = preg_replace("/.*\/([^\/]+)$/", "$1", $fname);
				if (ftp_is_dir($fname)) $f1[]= $fn;
				else $f2[] = $fn;
			}
		} else {
			$file_dir = $root_url;
			while($arr=@readdir($odir)){
				if(@is_dir($_dir."/".$arr)) $f1[]=$arr;
				else $f2[]=$arr;
			}
		}

		@sort($f1);
		@sort($f2);
		$f=array_merge($f1,$f2);

		foreach($f as $key=>$val){
			if($val == "." || ($val == ".." && !$_f)) continue;
			$w=$_dir."/".$val;
			$_w=(!$use_multi_server) ? realpath($w) : ftp_pwd($fs_ftp_con)."/$val";

			if ($use_multi_server) $root_dir = $file_server[$file_server_num]['file_dirname'];

			$_w=str_replace("\\","/",$_w);
			$_url=preg_replace("@/?".$root_dir.'@',$file_dir,$_w);
			$_suburl=preg_replace("@/?".$root_dir.'@',$file_dir,$_w);
			$_suburl = "<img src='$_suburl'>";

			$button="<span class=\"box_btn_s\"><input type=\"button\" value=\"파일명수정\" onclick=\"fileEdit('modify','".urlencode($_w)."','".$key."');\"></span>
			<span class=\"box_btn_s\"><input type=\"button\" value=\"파일삭제\" onclick=\"fileEdit('delete','".urlencode($_w)."','".$key."');\"></span>";

			$hid_fd="<input type=\"text\" id=\"h".$key."\" value=\"".$val."\" size=\"".(strlen($val)+5)."\" style=\"border:1px solid #333300; height:15px; line-height:13px; visibility:hidden;\" onblur=\"getFileList('', '&exec=modify&w=".urlencode($_w)."&wto='+this.value);\">";
			$style=" style=\"width:300px;\"";

			if(@is_dir($w) || ($use_multi_server && @in_array($val,$f1)) || $val == ".."){
				if($val == ".."){
					$button=$hid_fd="";
				}
				$img_src=(dirFileExist($w) && $val != "..") ? "ic_folder_img.gif\" width=\"16\" height=\"16" : "ic_folder_c.gif\" width=\"16\" height=\"14";
				echo "<a href=\"javascript:;\" onclick=\"getFileList('".$_w."');\"$style><img src=\"".$engine_url."/_manage/image/icon/$img_src\" align=\"absmiddle\" vspace=2> <span id=\"s".$key."\">".$val."</span>".$hid_fd."</a> ".$button."<br>";
			}elseif(@is_file($w) || $use_multi_server){
				$ext=strtolower(getExt($val));
				if($ext == "jpeg") $ext="jpg";
				if(@strchr("jpg|gif|bmp|png|flv|swf",$ext)){
					if($ext == "flv" || $ext == "swf") $ext="etc";
?>
		<div onmouseover="this.style.backgroundColor='#EBEBEB';" onmouseout="this.style.backgroundColor='';" style="overflow:hidden; padding:5px 0; border-bottom:1px solid #eee;">
			<div style="float:left; width:500px;"><?="<a href=\"".$_url."\" target=\"_blank\"$style><img src=\"".$engine_url."/_manage/image/icon/img_".$ext.".gif\" style=\"width:16px; height:16px; vertical-align:middle;\"> <span id=\"s".$key."\">".$val."</span>".$hid_fd."</a>";?></div>
			<div style="float:right;">
				<?=$button?>
				<span class="box_btn_s"><input type="button" value="HTML 복사" class="clipboard" data-clipboard-text="<?=$_suburl?>"></span>
			</div>
		</div>
<?
				}
			}
		}
	}

?>