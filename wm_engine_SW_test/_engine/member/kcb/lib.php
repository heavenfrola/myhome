<?php

/**
 * kcb okname 라이브러리
 */

/**
 * 서비스 거래번호 생성
 * @return false|string
 */
function generateSvcTxSeqno()
{
    $numbers  = "0123456789";
    $svcTxSeqno = date("YmdHis");
    $nmr_loops = 6;
    while ($nmr_loops--) {
        $svcTxSeqno .= $numbers[mt_rand(0, strlen($numbers)-1)];
    }
    return $svcTxSeqno;
}

/**
 * 본인 인증으로부터 받은 생일로 성인 여부 판별
 * @return bool|null
 */
function is_adult()
{
    global $pdo, $tbl, $member;

    if (isset($member['is_adult'])) {
        return $member['is_adult'];
    } else {
        $birth = $pdo->row("select birth from {$tbl['member_cert']} where no=? and member_id=?", array(
            $member['no'],
            $member['member_id']
        ));
        if ($birth) {
            $birthdate = DateTime::createFromFormat('Y-m-d', $birth);
            if (is_object($birthdate)) {
                $age = $birthdate->diff(new DateTime())->y;
                if ($age >= 19) {
                    $member['is_adult'] = '1';
                    return true;
                }
            }
        } else {
            return null; // 미인증
        }
    }

    // 성인 아님
    $member['is_adult'] = false;
    return false;
}

/**
 * 인증 정보 저장
 * @return false|void
 */
function setMemberCert()
{
    global $pdo, $tbl, $member;

    if (!$member['no']) return false;
    if (!isset($_SESSION['ipin_res'])) return false;

    $exists = $pdo->assoc("select no from {$tbl['member_cert']} where no=? and member_id=?", array(
        $member['no'], $member['member_id']
    ));
    if ($exists) { // 기존 인증 데이터 있을 경우 삭제
        $pdo->query("delete from {$tbl['member_cert']} where where no=? and member_id=?", array(
            $member['no'], $member['member_id']
        ));
    }

    $data = $_SESSION['ipin_res'];
    if ($data['gender'] == '남') $data['gender'] = 'M';
    else if ($data['gender'] == '여') $data['gender'] = 'F';
    
    $pdo->query("
                insert into {$tbl['member_cert']} (no, member_id, birth, gender, is_foreign, DI, CI, reg_date)
                values (?, ?, ?, ?, ?, ?, ?, now())
            ", array(
        $member['no'], $member['member_id'], $data['birth'], $data['gender'], $data['is_foreign'], $data['DI'], $data['CI']
    ));
}