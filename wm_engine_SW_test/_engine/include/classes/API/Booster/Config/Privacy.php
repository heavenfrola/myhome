<?php

namespace Wing\API\Booster\Config;

use Wing\API\Booster\Exceptions\CommonException;

Trait Privacy
{
    /**
     * 개인정보처리방침 테이블의 유무 확인
     * 테이블 및 현재 스킨내용을 토대로 신규 데이터 자동 생성
     * @throws CommonException
     */
    public function privacyTableChk()
    {
        if (!isTable($this->tbl['privacy_policy'])) {
            if ($this->pdo->query($this->tbl_schema['privacy_policy'])) {
                $sample = $this->privacySampleGet();
                $data = [
                    'contents' => $sample,
                    'hidden' => 'N',
                    'default_yn' => 'Y',
                    'effective_date' => date('Y-m-d'),
                    'admin_name' => '시스템',
                    'init' => true,
                    'retJson' => false
                ];
                $this->privacySet($data);
            }
        }
    }

    /**
     * 샘플 html문서를 읽어서 리턴한다.
     * @return string
     */
    private function privacySampleGet()
    {
        $sampleFileSrc = $this->engine_dir.'/_manage/config/privacy_sample.html';
        $fp = fopen($sampleFileSrc, 'r');
        $sample = fread($fp, filesize($sampleFileSrc));
        fclose($fp);
        $sample = str_replace('[시행일]', date('Y.m.d'), $sample);
        return $sample;
    }

    /**
     * 정책을 삭제한다.
     * @throws CommonException
     */
    public function privacyDel()
    {
        $this->allowRequest(['POST']);
        $param = $_POST;
        if (!is_array($param['no']) || !$param['no'][0] ) {
            throw new CommonException('삭제할 정책을 선택해주세요.');
        }
        //기존 데이터 수 확인
        $totalCnt = $this->privacyTotalListGet(['state' => 'show']);
        $delCnt = count($param['no']);
        if ($totalCnt['cnt']<=$delCnt) {
            throw new CommonException('최소한 하나의 정책이 필요합니다.');
        }
        $del_no = implode(',', array_map('addslashes', $param['no']));
        if (!$this->pdo->query("UPDATE {$this->tbl['privacy_policy']} SET deleted = ? WHERE `no` IN (".$del_no.")", ['Y'])) {
            throw new CommonException('DB 관련 에러가 발생하였습니다'.$this->pdo->geterror());
        }
        $this->privacyDefaultSet();

        $this->out([
            'status'=>'success',
            '총 '.count($param['no']).'건이 삭제되었습니다'
        ]);
    }

    /**
     * 정책 작성 및 수정
     * init이 true인 경우, 기본게시물  설정을 하지 않는다.
     * @param array $param
     * @return array|void
     * @throws CommonException
     */
    public function privacySet($param = [])
    {
        if (!$param) {
            $this->allowRequest(['POST']);
            $param = $_POST;
        }
        if (!isset($param['retJson'])) $param['retJson'] = true;
        $no = ($param['no']) ? $param['no'] : 0;
        $contents = $param['contents'];
        $effective_date = strtotime($param['effective_date']);
        $hidden = ($param['hidden']) ? $param['hidden'] : 'Y';
        $admin_name = ($param['admin_name']) ? $param['admin_name'] : $this->cfg->get('admin_name');
        $data = [
            'no' => $no,
            'contents' => $contents,
            'hidden' => $hidden,
            'effective_date' => $effective_date
        ];
        $data['contents'] = $this->escape($data['contents']);
        if ($data['no']) {
            $sql = "UPDATE {$this->tbl['privacy_policy']} SET 
            contents = ?, 
            hidden = ?, 
            effective_date = ?, 
            admin = ?
            WHERE `no` = ?";
            $bindArray = [
                $data['contents'],
                $data['hidden'],
                $data['effective_date'],
                $admin_name,
                $data['no']
            ];
        } else {
            $sql = "INSERT INTO {$this->tbl['privacy_policy']} SET 
            contents = ?, 
            hidden = ?, 
            effective_date = ?, 
            admin = ?,            
            reg_date = ?";
            $bindArray = [
                $data['contents'],
                $data['hidden'],
                $data['effective_date'],
                $admin_name,
                time()
            ];
        }
        if ($this->pdo->query($sql, $bindArray)) {
            if (!$data['no']) $data['no'] = $this->pdo->lastInsertId();
            if (!$param['init']) {
                $this->privacyDefaultSet();
            }
        } else {
            throw new CommonException('DB 관련 에러가 발생하였습니다'.$this->pdo->geterror());
        }

        $ret = ['status'=> 'success', 'data'=>$data];
        if ($param['retJson']) {
            $this->out($ret);
        } else {
            return $ret;
        }
    }

    /**
     * 특정 정책을 숨김처리한다.
     * @throws CommonException
     */
    public function privacyHiddenSet()
    {
        $this->allowRequest(['POST']);
        $param = $_POST;
        if (!$param['no'] || !$param['hidden']) {
            throw new CommonException('변경할 정책을 선택해주세요.');
        }
        $res = $this->privacyStateGet();
        if ($res['show'] <= 1 &&  $param['hidden'] === 'Y') {
            throw new CommonException('최소 하나의 정책은 사용해야 합니다.');
        }
        if ($this->pdo->query("UPDATE {$this->tbl['privacy_policy']} 
            SET hidden = ? 
            WHERE `no` = ?",
            [$param['hidden'], $param['no']])) {
            $this->privacyDefaultSet();
        } else {
            throw new CommonException('DB 관련 에러가 발생하였습니다'.$this->pdo->geterror());
        }

        $this->out([
            'status'=> 'success'
        ]);
    }

    /**
     * 리스트나 카운트쿼리에 사용될 조건절을 생성한다.
     * @param array $param
     * @return array
     */
    private function PrivacyWhereSet($param)
    {
        $where = " deleted = ?";
        $bindData = ['N'];
        if ($param['search_str']) {
            if ($param['search_type'] === 'effective_date') {
                $where .= " AND effective_date = ?";
                $bindData[] = strtotime($param['search_str']);
            }
            if ($param['search_type'] === 'admin') {
                $where .= " AND admin LIKE ?";
                $bindData[] = "%".$param['search_str']."%";
            }
        }
        switch ($param['state'])
        {
            case 'show':
                $where .= " AND hidden = ?";
                $bindData[] = 'N';
                break;
            case 'hidden':
                $where .= " AND hidden = ?";
                $bindData[] = 'Y';
                break;
        }
        return [
            'where'=> $where,
            'bindData'=> $bindData
        ];
    }

    /**
     * 상태별 정책 카운트
     * @param array $param
     * @return int[]
     */
    public function privacyStateGet($param = [])
    {
        //전체 상태값을 카운트 하므로 해당 조건은 삭제한다.
        unset($param['state']);
        $whereSet = $this->PrivacyWhereSet($param);
        $res = $this->pdo->iterator("SELECT COUNT(`no`) AS cnt, hidden
                FROM ".$this->tbl['privacy_policy']." 
                WHERE ".$whereSet['where']." 
                GROUP BY hidden", $whereSet['bindData']);
        $countArr = [
            'total' => 0,
            'hidden' => 0,
            'show' => 0
        ];
        foreach ($res as  $ldata) {
            if ($ldata['hidden'] === 'Y') {
                $countArr['hidden'] = $ldata['cnt'];
            } else {
                $countArr['show'] = $ldata['cnt'];
            }
            $countArr['total'] += $ldata['cnt'];
        }
        return $countArr;
    }

    /**
     * 현재 검색조건에 맞는 전체 리스트 카운트
     * @param array $param
     * @return mixed
     * @throws CommonException
     */
    public function privacyTotalListGet(array $param = [])
    {
        if (!$param) {
            $this->allowRequest(['POST']);
            $param = $_POST;
        }
        $whereSet = $this->PrivacyWhereSet($param);
        $res = $this->pdo->assoc("SELECT COUNT(`no`) AS cnt 
            FROM {$this->tbl['privacy_policy']}  
            WHERE ".$whereSet['where'], $whereSet['bindData']);
        if (!$res) {
            throw new CommonException('DB 관련 에러가 발생하였습니다'.$this->pdo->geterror());
        }

        return $res['cnt'];
    }

    /**
     * 현재 검색조건에 맞는 페이징 리스트
     * @param array $param
     * @return array|void
     */
    public function privacyListGet(array $param = [])
    {
        if (!$param) {
            $this->allowRequest(['POST']);
            $param = $_POST;
        }
        if (!isset($param['retJson'])) $param['retJson'] = true;
        //페이징된 게시글 목록
        if (!$param['orderby']) $param['orderby'] = 'effective_date';
        $whereSet = $this->PrivacyWhereSet($param);
        $res = $this->pdo->iterator("SELECT `no`, contents, hidden, `default_yn`, effective_date, admin, reg_date 
            FROM {$this->tbl['privacy_policy']} 
            WHERE " . $whereSet['where'] . " 
            ORDER BY " . $param['orderby'] . " DESC " . $param['LimitQuery'], $whereSet['bindData']);
        if (!$res) {
            throw new CommonException('DB 관련 에러가 발생하였습니다' . $this->pdo->geterror());
        }
        $data = [];
        foreach ($res as $ldata) {
            $ldata['contents'] = $this->unescape(($ldata['contents']));
            $ldata['reg_date'] = date('Y/m/d H:i:s', $ldata['reg_date']);
            $ldata['effective_date'] = date('Y-m-d', $ldata['effective_date']);
            $data[] = $ldata;
        }
        $ret = ['status' => 'success', 'data' => $data];
        if ($param['retJson']) {
            $this->out($ret);
        } else {
            return $ret;
        }
    }

    /**
     * 중복 시행일 데이터 검사 (신규 등록 및 수정시 사용)
     * @throws CommonException
     */
    public function privacyDuplicateChk()
    {
        $this->allowRequest(['POST']);
        $param = $_POST;
        //동일한 시행일이면서 스킨에 반영된적이 있는 데이터 조회 (최초 임시로 생성된 데이터는 반영되지 않은 데이터이다)
        $where = " deleted = ? AND effective_date = ?";
        $bindData = [
            'N',
            strtotime($param['effective_date'])
        ];
        if ($param['no']) {
            $where .= " AND `no` != ?";
            $bindData[] = $param['no'];
        }
        $res = $this->pdo->assoc("SELECT COUNT(no) AS cnt 
            FROM {$this->tbl['privacy_policy']} 
            WHERE ".$where,
            $bindData);
        if (!$res) {
            throw new CommonException('DB 관련 에러가 발생하였습니다'.$this->pdo->geterror());
        }
        if ($res['cnt'] > 0) {
            throw new CommonException('동일한 시행일의 정책이 이미 존재합니다.');
        }

        $this->out([
            'status'=> 'success'
        ]);
    }

    /**
     * 특정 게시글 호출
     * last가 true인경우 마지막 게시글 호출
     * @throws CommonException
     */
    public function privacyGet()
    {
        $this->allowRequest(['POST']);
        $param = $_POST;
        $where = " deleted = ?";
        $bindData = ['N'];
        if (!$param['last']) {
            if (!$param['no']) {
                //신규생성이 아닌데, 글 번호가 없다면 에러
                throw new CommonException('불러올 정책이 없습니다.');
            }
            $where .= " AND `no` = ?";
            $bindData[] = $param['no'];
        }
        $data = $this->pdo->assoc("SELECT * 
            FROM {$this->tbl['privacy_policy']} 
            WHERE ".$where." 
            ORDER BY reg_date DESC LIMIT 0, 1",
            $bindData);
        if (!$data['no']) {
            throw new CommonException('DB 관련 에러가 발생하였습니다'.$this->pdo->geterror());
        }
        $data['contents'] = $this->unescape($data['contents']);
        $data['reg_date'] = date('m/d H:i:s', $data['reg_date']);
        $data['effective_date'] = date('Y-m-d', $data['effective_date']);
        if ($data['hidden'] === 'Y') {
            $data['show_text'] = '아니오';
        } else {
            $data['show_text'] = '예';
        }
        $this->out([
            'status'=> 'success',
            'data'=> $data
        ]);
    }

    /**
     * 사용할 정책 설정 (개인정보처리방침 페이지 접근시 기본 노출)
     * 1. 숨김처리하지 않은 가장 최신 시행일을 가진 정책의 default_yn을 'Y'으로 변경한다.
     * 2. 다른 정책은 모두 N으로 업데이트
     * 신규등록 및 수정시 동작
     * @throws CommonException
     */
    private function privacyDefaultSet()
    {
        $data = $this->pdo->assoc("SELECT `no` 
            FROM {$this->tbl['privacy_policy']} 
            WHERE hidden = ? AND deleted = ? 
            ORDER BY effective_date DESC LIMIT 0, 1",
            ['N', 'N']);
        if (!$data['no']) {
            throw new CommonException('DB 관련 에러가 발생하였습니다'.$this->pdo->geterror());
        }
        $this->pdo->query("UPDATE {$this->tbl['privacy_policy']} 
            SET `default_yn` = ? ",
            ['N']);
        if ($this->pdo->geterror()) {
            throw new CommonException('DB 관련 에러가 발생하였습니다'.$this->pdo->geterror());
        }
        $this->pdo->query("UPDATE {$this->tbl['privacy_policy']} 
            SET `default_yn` = ? 
            WHERE `no` = ?",
            ['Y', $data['no']]);
        if ($this->pdo->geterror()) {
            throw new CommonException('DB 관련 에러가 발생하였습니다'.$this->pdo->geterror());
        }
    }

    /**
     * 관리자 목록내 리스트 HTML을 생성한다.
     * @param $res
     * @return false|mixed
     */
    public function privacyListHtmlSet(&$res)
    {
        $data = current($res);
        next($res);
        if (!$data) return false;
        $data['hidden_on'] = '';
        $data['hidden_flag'] = 'N';
        if ($data['hidden'] === 'N') {
            $data['hidden_on'] = 'on';
            $data['hidden_flag'] = 'Y';
        }

        return $data;
    }
}
