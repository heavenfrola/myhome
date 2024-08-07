<?PHP

/* +----------------------------------------------------------------------------------------------+
' |  무료배송 이벤트 수정
' +----------------------------------------------------------------------------------------------+*/

$freedeli_event_begin = date('Y-m-d H:i:s', strtotime($_POST['begin1'].' '.$_POST['begin2'].':'.$_POST['begin3'].':00'));
$freedeli_event_finish = date('Y-m-d H:i:s', strtotime($_POST['finish1'].' '.$_POST['finish2'].':'.$_POST['finish3'].':59'));

if ($event_begin > $event_finish) {
    msg('시작일은 종료일 이전이어야합니다.');
}

$scfg->import(array(
    'freedeli_event_use' => ($_POST['freedeli_event_use'] == 'Y') ? 'Y' : 'N',
    'freedeli_event_begin' => $freedeli_event_begin,
    'freedeli_event_finish' => $freedeli_event_finish,
    'freedeli_event_min_pay' => (int) $_POST['freedeli_event_min_pay'],
    'freedeli_event_obj' => ($_POST['freedeli_event_obj'] == '1') ? '1' : '2'
));

msg('', 'reload', 'parent');