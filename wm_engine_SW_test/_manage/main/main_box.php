<?PHP

	function BoxWrite($type="top", $boxn=1, $tbw=215, $h=14, $tbbgc="", $imgn="", $tdh=""){
		global $engine_url;
		if($h){
			list($h, $w)=explode("/",$h);
			if(!$w) $w=14;
		}
		if($type == "top"){
?>
		<table width="<?=$tbw?>" border="0" cellspacing="0" cellpadding="0" bgcolor="<?=$tbbgc?>">
			<tr height="<?=$h?>">
				<td width="<?=$w?>"><img src="<?=$engine_url?>/_manage/image/main/box<?=$boxn?>_lt.gif" width="<?=$w?>" height="<?=$h?>"></td>
				<td background="<?=$engine_url?>/_manage/image/main/box<?=$boxn?>_t_bg.gif"><img src="<?=$engine_url?>/_manage/image/main/box<?=$boxn?>_t_bg.gif" width="<?=$w?>" height="<?=$h?>"></td>
				<td width="<?=$w?>"><img src="<?=$engine_url?>/_manage/image/main/box<?=$boxn?>_rt.gif" width="<?=$w?>" height="<?=$h?>"></td>
			</tr>
			<tr>
				<td width="<?=$w?>" background="<?=$engine_url?>/_manage/image/main/box<?=$boxn?>_l_bg.gif"><img src="<?=$engine_url?>/_manage/image/main/box<?=$boxn?>_l_bg.gif" width="<?=$w?>"></td>
				<td height="<?=$tdh?>">
<?php
		} else {
?>
				</td>
				<td width="<?=$w?>" background="<?=$engine_url?>/_manage/image/main/box<?=$boxn?>_r_bg.gif"><img src="<?=$engine_url?>/_manage/image/main/box<?=$boxn?>_r_bg.gif" width="<?=$w?>"></td>
			</tr>
			<tr height="<?=$h?>">
				<td width="<?=$w?>"><img src="<?=$engine_url?>/_manage/image/main/box<?=$boxn?>_lb<?=$imgn?>.gif" width="<?=$w?>" height="<?=$h?>"></td>
				<td background="<?=$engine_url?>/_manage/image/main/box<?=$boxn?>_b_bg<?=$imgn?>.gif"><img src="<?=$engine_url?>/_manage/image/main/box<?=$boxn?>_b_bg<?=$imgn?>.gif" width="<?=$w?>" height="<?=$h?>"></td>
				<td width="<?=$w?>"><img src="<?=$engine_url?>/_manage/image/main/box<?=$boxn?>_rb<?=$imgn?>.gif" width="<?=$w?>" height="<?=$h?>"></td>
			</tr>
		</table>
<?php
		}
	}
	function colorGraph($color="bl", $per=""){
		global $engine_url;

        echo "
		<table height=\"100px\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
			<tr>
				<td height=\"".floor(100-$per)."%\"></td>
			</tr>
			<tr>
				<td height=\"{$per}%\">
				<table height=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
					<tr>
						<td height=\"19px\"><img src=\"$engine_url/_manage/image/main/graph_{$color}.gif\"></td>
					</tr>
					<tr>
						<td background=\"$engine_url/_manage/image/main/graph_{$color}_bg.gif\">
                            <img src=\"$engine_url/_manage/image/main/graph_{$color}_bg.gif\"></td>
					</tr>
					<tr>
						<td height=\"11px\"><img src=\"$engine_url/_manage/image/main/graph_{$color}_b.gif\"></td>
					</tr>
				</table>
				</td>
			</tr>
		</table>
        ";
    }