<?php

	if(!isTable($tbl['search_preset'])) {
		include $engine_dir.'/_config/tbl_schema.php';
		$pdo->query($tbl_schema['search_preset']);
	}

	$sp_count = $pdo->row("select count(*) from {$tbl['search_preset']} where menu='$preset_menu'");
	if($sp_count>0) {
        $spres = $pdo->iterator("select * from {$tbl['search_preset']} where menu='$preset_menu' order by sort");
        foreach ($spres as $spdata) {
            $active = ($spdata['no']==$spno) ? 'active':'';
            ?>
            <li class="btt" tooltip="<?=stripslashes($spdata['content'])?>">
                <a data-idx='<?=$spdata['no']?>' onclick="searchPreset('<?=$preset_menu?>', '<?=$spdata['no']?>');" class="link <?=$active?>">#<?=$spdata['title']?></a>
                <?php if ($admin['level'] < 4) { ?>
                <a onclick="presetDelete('<?=$preset_menu?>', '<?=$spdata['no']?>');" class="delete">삭제</a>
                <?php } ?>
            </li>
            <?php
        }
	}

?>