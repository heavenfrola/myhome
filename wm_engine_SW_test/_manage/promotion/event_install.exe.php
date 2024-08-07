<?php
	if(!defined("evnet_install")) return;
	include_once $engine_dir.'/_config/tbl_schema.php';


	if(isTable($tbl['event'])) {
		//이벤트 이름 컬럼 추가
		addField($tbl['event'], 'event_name', 'varchar(100) NOT NULL DEFAULT "" COMMENT "이벤트명"');
	} else {
		//다중 등록 가능한 이벤트 사용전 신규 이벤트 테이블 생성
		$pdo->query($tbl_schema['event']);
		//신규 이벤트 테이블에 기존 데이터 삽입 및 기존 데이터 삭제
		if($cfg['event_use'] == 'Y' || $cfg['event_use'] == 'N') {
			$_event_begin   = $cfg['event_begin'  ] ? strtotime($cfg['event_begin'  ]) : $now;
			$_event_finish  = $cfg['event_finish' ] ? strtotime($cfg['event_finish' ])+86399 : $now;
			$_event_use     = $cfg['event_use'    ];
			$_event_min_pay = $cfg['event_min_pay'];
			$_event_obj     = $cfg['event_obj'    ];
			$_event_type    = $cfg['event_type'   ];
			$_event_milage_addable  = $cfg['event_milage_addable' ];
			$_event_milage_addable2 = $cfg['event_milage_addable2'];
			$_event_ptype   = $cfg['event_ptype'  ];
			$_event_per     = $cfg['event_per'    ];
			$_event_round   = $cfg['event_round'  ];
			$pdo->query("
				insert into $tbl[event]
				(event_begin ,event_finish ,event_use ,event_min_pay ,event_obj ,event_type ,event_milage_addable ,event_milage_addable2 ,event_ptype ,event_per ,event_round ,reg_date)
				values
				('$_event_begin', '$_event_finish', '$_event_use', '$_event_min_pay', '$_event_obj', '$_event_type', '$_event_milage_addable', '$_event_milage_addable2', '$_event_ptype', '$_event_per', '$_event_round', '$now')
			");


			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='event_begin'");
			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='event_finish'");
			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='event_use'");
			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='event_min_pay'");
			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='event_obj'");
			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='event_type'");
			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='event_milage_addable'");
			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='event_milage_addable2'");
			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='event_ptype'");
			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='event_per'");
			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='event_round'");
			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='finish1'");
			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='finish2'");
			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='finish3'");
			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='begin1'");
			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='begin2'");
			$pdo->query("DELETE FROM $tbl[config] WHERE `name`='begin3'");
		}

	}
?>