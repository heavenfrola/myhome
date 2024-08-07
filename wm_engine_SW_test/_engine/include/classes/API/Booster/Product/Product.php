<?php

/**
 * SmartWing API / Product
 */

namespace Wing\API\Booster\Product;

use Wing\API\Booster\Common\Common;
use Wing\API\Booster\Exceptions\CommonException;

require_once __ENGINE_DIR__.'/_engine/include/paging.php';
require_once __ENGINE_DIR__.'/_engine/include/file.lib.php';

Class Product {

    use Common;

    public function __construct()
    {
        $this->init();
    }

    /**
     * 컬러칩 리스트 출력 / GET
     **/
    public function colorchipGet()
    {
        $this->allowRequest(array('GET'));
        $this->permission('product', 'C0239');

        /* search */
        $w = '';
        $keyword = (isset($_GET['keyword'])) ? trim($_GET['keyword']) : null;
        if ($keyword) {
            $keyword = addslashes($keyword);
            $w .= " and name like '%$keyword%'";
        }

        /* paging */
        $page = (isset($_GET['page'])) ? (int) $_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $rows = (isset($_GET['rows'])) ? (int) $_GET['rows'] : 10;
        $block = 10;

        $NumTotalRec = $this->pdo->row("select count(*) from {$this->tbl['product_option_colorchip']} where 1 $w");
        $PagingInstance = new \Paging($NumTotalRec, $page, $rows, $block);
        $PagingResult=$PagingInstance->result('vue');

        /* list */
        $file_url = $file_dir = getFileDir('_data/product/colorchipGet');
        $res = $this->pdo->object("
            select * from {$this->tbl['product_option_colorchip']} where 1 $w order by no desc
            {$PagingResult['LimitQuery']}
        ");
        foreach ($res as $data) {
            $data->size = null;
            if ($data->upfile1) {
                $data->size = setImageSize($data->w1, $data->h1, 50, 50);
                $data->upfile1 = $file_url.'/'.$data->updir.'/'.$data->upfile1;
            }
        }

        $this->out(array(
            'result' => 'success',
            'data' => $res,
            'paging' => $PagingResult['PageLink']
        ));
    }

    /**
     * 컬러칩 수정 및 신규 등록 / POST
     **/
    public function colorchipSet()
    {
        global $dir;

        $this->allowRequest(array('POST'));
        $this->permission('product', 'C0239');

        if (!count($_POST['name'])) {
            throw new CommonException('컬러칩을 입력해주세요.');
        }

        foreach($_POST['name'] as $key => $val) {
            if(empty(trim($val)) == true) {
                throw new CommonException('컬러칩 이름을 입력해주세요.');
            }

            $_type = ($_POST['type'][$key] == 'file') ? 'file' : $_POST['type'][$key];
            if($_type == 'code' && empty($_POST['code'][$key]) == true) {
                throw new CommonException('컬러코드를 입력해주세요.');
            }
        }

        addField($this->tbl['product_option_colorchip'], 'type', 'enum("file", "code") not null default "file"');
        addField($this->tbl['product_option_colorchip'], 'code', 'varchar(7) not null default ""');

        foreach($_POST['name'] as $key => $val) {
            $_no = (int) $_POST['no'][$key];
            $_name = trim($val);
            $_type = ($_POST['type'][$key] == 'file') ? 'file' : $_POST['type'][$key];
            $_code = ($_POST['code'][$key]) ? $_POST['code'][$key] : '';

            // 이미지 첨부형
            if ($_FILES['upfile1']) {
                $_file = array(
                    'tmp_name' => $_FILES['upfile1']['tmp_name'][$key],
                    'name' => $_FILES['upfile1']['name'][$key],
                    'size' => $_FILES['upfile1']['size'][$key],
                );
            }

            $asql = $updir = $upfile1 = '';
            $w = $h = 0;
            $data = $this->pdo->assoc("select updir, upfile1 from {$this->tbl['product_option_colorchip']} where no=?", array($_no));
            if ($_type == 'file' && $_file['size'] > 0) {
                if($_no > 0) {
                    deleteAttachFile($data['updir'], $data['upfile1']);
                }
                $updir = $dir['upload'].'/product/colorchip';
                makeFullDir($updir);

                list($w, $h) = getImagesize($_file['tmp_name']);
                $up_info = uploadFile($_file, md5($_name.microtime()), $updir, 'jpg|jpeg|gif|png');
                $upfile1 = $up_info[0];
                $asql .= ", upfile1='$up_info[0]', w1='$w', h1='$h', updir='$updir'";
            } else if ($_type == 'code' && $data && $data['upfile1']) {
                deletePrdImage($data,1,1);
                $asql .= ", upfile1='', w1='0', h1='0', updir=''";
            }
            if ($_type == 'file' && (!$data && !$data['upfile1'] && !$_file)) {
                throw new CommonException('업로드할 파일을 선택해주세요.');
            }

            if($_no > 0) {
                $this->pdo->query("
                    update {$this->tbl['product_option_colorchip']}
                        set name='$_name', type='$_type', code='$_code' $asql
                        where no='$_no'
                ");
                if($this->pdo->lastRowCount() > 0) {
                    $this->pdo->query("update {$this->tbl['product_option_item']} set iname=? where chip_idx=?", array($_name, $_no));
                }
            } else {
                $this->pdo->query("
                    insert into {$this->tbl['product_option_colorchip']}
                    (name, type, updir, upfile1, w1, h1, code, reg_date) values
                    (?, ?, ?, ?, ?, ?, ?, unix_timestamp())
                ", array(
                    $_name, $_type, $updir, $upfile1, $w, $h, $_code
                ));
            }
            $result = ($this->pdo->geterror()) ? 'error' : 'success';
        }
        $this->out(array(
            'status' => $result,
            'message' => $this->pdo->geterror(),
            'file' => $upfile1
        ));
    }

    /**
     * 컬러칩 삭제 / GET
     */
    public function colorChipRemove()
    {
        $this->allowRequest(array('DELETE'));
        $this->permission('product', 'C0239');

        $no = (int) $_GET['no'];
        if (!$no) {
            throw new CommonException('삭제할 상품번호를 입력해주세요.');
        }

        $data = $this->pdo->assoc("select updir, upfile1 from {$this->tbl['product_option_colorchip']} where no=?", array($no));
        if ($data['upfile1']) {
            deleteAttachFile($data['updir'], $data['upfile1']);
        }

        $r = $this->pdo->query("delete from {$this->tbl['product_option_colorchip']} where no=?", array($no));
        if ($r) {
            $res = $this->pdo->iterator("select no from {$this->tbl['product_option_item']} where chip_idx=?", array($no));
            foreach ($res as $data) {
                $this->pdo->query("delete from {$this->tbl['product_option_item']} where no='$data[no]'");
                $this->pdo->query("update erp_complex_option set del_yn='Y' where opts like '%#_$data[no]#_%' ESCAPE '#'");
            }
            $this->out(array(
                'status' => 'success',
            ));
        }
        $this->out(array(
            'status' => 'error',
            'message' => $this->pdo->geterror()
        ));
    }
}