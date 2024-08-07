<?php

/**
 * Smartwing Editor File
 **/

namespace Wing\common;

include_once __ENGINE_DIR__.'/_engine/include/file.lib.php';

class EditorFile {

    private $group;
    private $id;

    public function __construct()
    {
        $this->table = $GLOBALS['tbl']['neko'];
        $this->db = &$GLOBALS['pdo'];

        if (rand(0, 100) == 0) $this->gc();
    }

    /**
     * 업로더 아이디 생성
     **/
    public function setId($group, $id = null)
    {
        if (empty($id) == true) {
            $id = 'temp_'.time();
        }
        $this->group = $group;
        $this->id = $this->group.'_'.$id;
    }

    /**
     * 업로더 아이디 가져오기
     **/
    public function getid()
    {
        return $this->id;
    }

    /**
     * gc 되지 않도록 lock 처리
     **/
    public function lock($group, $id, $tmp_id)
    {
        $this->setId($group, $id);
        $this->db->query("
            update $this->table set neko_id=:neko_id, `lock`='Y' where neko_id=:tmp_id
        ", array(
            ':neko_id' => $this->id,
            ':tmp_id' => $tmp_id,
        ));
    }

    /**
     * Garbage Collect
     **/
    public function gc($hours = 12)
    {
        $st = strtotime('2022-01-19'); // lock 정보 관리 안되는 과거 이미지 제외
        $ed = strtotime('-'.$hours.' hours');
        $res = $this->db->iterator("select no from $this->table where `lock`='N' and regdate between $st and $ed");
        foreach ($res as $data) {
            $this->__remove($data);
        }
    }

    /**
     * 아이디별 이미지 전체 삭제
     **/
    public function removeId($group, $id)
    {
        $this->setId($group, $id);
        $res = $this->db->iterator("select no, updir, filename from $this->table where neko_id=?", array($this->id));
        foreach ($res as $data) {
            $this->__remove($data);
        }
    }

    /**
     * 개별 이미지 삭제
     **/
    public function remove($no)
    {
        $data = $this->db->assoc("select no, updir, filename from $this->table where no=?", array($no));
        return $this->__remove($data);
    }

    private function __remove($data)
    {
        if (!$data) {
            return false;
        }
        deleteAttachFile($data['updir'], $data['filename']);
        return $this->db->query("delete from $this->table where no='{$data['no']}'");
    }

}