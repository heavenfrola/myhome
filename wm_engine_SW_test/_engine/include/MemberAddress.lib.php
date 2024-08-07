<?php

/**
 * 최초 회원 기본 주소록 생성
 */
function memberAddressInit()
{
    global $tbl, $pdo, $member, $con_info;

    if (!isTable($tbl['member_address'])) {
        require __ENGINE_DIR__.'/_config/tbl_schema.php';
        $pdo->query($tbl_schema['member_address']);
    }

    //우편번호 필드 사이즈 확인
    $data_type = $pdo->row("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME='zip' and TABLE_SCHEMA='".$con_info[4]."' and TABLE_NAME='{$tbl['member_address']}'");
    if ($data_type != 'varchar(7)') {
        modifyField($tbl['member_address'], 'zip', 'VARCHAR(7) NOT NULL COMMENT "우편번호" COLLATE "utf8mb4_general_ci"');
    }

    if (!fieldExist($tbl['member_address'], 'nations')) {
		addField($tbl['member_address'], 'nations', 'varchar(30) ');
	}

    if (!isset($member['addr3'])) $member['addr3'] = '';
    if (!isset($member['addr4'])) $member['addr4'] = '';

    $addrs = $pdo->query("select * from {$tbl['member_address']} where member_no=? and member_id=?", array(
        $member['no'], $member['member_id']
    ));
    $total = $addrs->rowCount();
    if ($total == 0) {
        // 회원 정보를 주소록에 추가
        if ($member['zip']) {
            memberAddressSet(
                $member['name'], 'member', $member['name'],
                $member['phone'], $member['cell'],
                $member['zip'], $member['addr1'], $member['addr2'], $member['addr3'], $member['addr4']
            );
        }

        $res = $pdo->object("select * from {$tbl['order']} where member_no=? and member_id=? and stat < 10 and addressee_zip!='' group by addressee_addr2 order by no asc", array(
            $member['no'], $member['member_id']
        ));
        foreach ($res as $ord) {
            if (!isset($ord->addressee_addr3)) $ord->addressee_addr3 = '';
            if (!isset($ord->addressee_addr4)) $ord->addressee_addr4 = '';
            if (!isset($ord->addresses_phone)) $ord->addresses_phone = '';

            memberAddressSet(
                $ord->addressee_name, 'order', $ord->addressee_name, $ord->addressee_phone, $ord->addressee_cell,
                $ord->addressee_zip, $ord->addressee_addr1, $ord->addressee_addr2, $ord->addressee_addr3, $ord->addressee_addr4
            );
        }

        $pdo->query("update {$tbl['member_address']} set is_default='Y' where member_no=? and member_id=? order by sort asc limit 1", array(
            $member['no'], $member['member_id']
        ));
    } else {
        // 회원 정보 업데이트 시 주소록의 회원 정보에서 가져온 데이터도 업데이트
        $pdo->query("
            update {$tbl['member_address']} set
                name=?, phone=?, cell=?, zip=?, addr1=?, addr2=?, addr3=?, addr4=?
            where
                member_no=? and member_id=? and source='member'
            order by sort asc limit 1",
            array(
                $member['name'], $member['phone'], $member['cell'],
                $member['zip'], $member['addr1'], $member['addr2'], $member['addr3'], $member['addr4'],
                $member['no'], $member['member_id']
            )
        );
    }
}

/**
 * 회원 주소록 목록을 출력
 * @param $member
 * @param string $where 검색조건
 * @param string $order 정렬조건
 * @return object|false
 */
function memberAddressGet($member, $where='', $order='')
{
    global $tbl, $pdo;
    if (!$order) $order = 'order by sort asc';

    // 반드시 is_default가 마지막 필드여야 함
    return $pdo->object("
                select name, phone, cell, zip, addr1, addr2, addr3, addr4, nations, title, idx as no, is_default
                from {$tbl['member_address']} where member_no=? and member_id=? {$where} {$order}
            ", array(
        $member['no'], $member['member_id']
    ));
}

/**
 * 회원 주소록에 데이터 추가
 * @param string $title
 * @param string $source
 * @param string $name
 * @param string $phone
 * @param string $cell
 * @param string $zip
 * @param string $addr1
 * @param string $addr2
 * @param string $addr3
 * @param string $addr4
 * @param string $is_default
 * @param int $addr_no
 * @param string $nations
 * @return int
 */
function memberAddressSet(
    $title, $source, $name, $phone, $cell,
    $zip, $addr1, $addr2, $addr3='', $addr4='',
    $is_default = 'N', $addr_no = '0', $nations = ''
) {
    global $tbl, $pdo, $member;

    // 중복 체크
    $exists = $pdo->row("
            select idx from {$tbl['member_address']} where
               member_no=? and member_id=? and phone=? and cell=? and zip=? and addr1=? and addr2=? and addr3=? and addr4=? and title=? and name=? and nations=?
        ", array(
        $member['no'], $member['member_id'], $phone, $cell, $zip, $addr1, $addr2, $addr3, $addr4, $title, $name, $nations
    ));
    if ($exists) return $exists;

    if( $addr_no > 0 ) {
        $pdo->query("
            update {$tbl['member_address']} set
                title=?, name=?, phone=?, cell=?, zip=?, addr1=?, addr2=?, addr3=?, addr4=?, nations=?
            where
                member_no=? and member_id=? and idx=? ",
            array(
                $title, $name, $phone, $cell, $zip, $addr1, $addr2, $addr3, $addr4, $nations, $member['no'], $member['member_id'], $addr_no
            )
        );
    }else {
        // 정렬 순서
        $sort = $pdo->row("select max(sort)+1 from {$tbl['member_address']} where member_no=? and member_id=?", array(
            $member['no'], $member['member_id']
        ));
        if (!$sort) $sort = 1;

        // 저장
        if ($sort == 1 && $is_default != 'Y') {
            $is_default = 'Y';
        }

        if ($is_default == 'Y') {
            memberAddressDefault('',$nations);
        }
        $pdo->query("
                insert into {$tbl['member_address']}
                (member_no, member_id, title, name, phone, cell, zip, addr1, addr2, addr3, addr4, source, is_default, sort, nations)
                values
                (?, ?, ?, ?, ? ,?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", array(
            $member['no'], $member['member_id'], $title, $name, $phone, $cell, $zip, $addr1, $addr2, $addr3, $addr4, $source, $is_default, $sort, $nations
        ));
        $addr_no = $pdo->lastInsertId();
    }

    return $addr_no;
}

/**
 * 지정한 주소를 기본 주소로 지정
 * @param int|null $idx
 * @return bool
 */
function memberAddressDefault($idx = null, $nations = '')
{
    global $tbl, $pdo, $member;
    $where = ($nations) ? " and ifnull(nations, '') != '' " : " and ifnull(nations, '') = '' ";
    // 기존 기본 주소 제거
    $pdo->query("update {$tbl['member_address']} set is_default='N' where member_no=? and member_id=? ".$where, array(
        $member['no'], $member['member_id']
    ));
    if (!$idx) return true;

    return $pdo->query("update {$tbl['member_address']} set is_default='Y' where idx=? and member_no=? and member_id=? order by sort desc limit 1", array(
        $idx, $member['no'], $member['member_id']
    ));;
}