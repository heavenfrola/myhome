    <?PHP
    /* +----------------------------------------------------------------------------------------------+
    ' |  개인정보처리방침 PHP 7이상 지원
    ' +----------------------------------------------------------------------------------------------+*/
    use Wing\API\Booster\Config;

    if (PHP_MAJOR_VERSION < 7) {
        //PHP 7미만인 경우
        ?>
        <div class="box_title first">
            <h2 class="title">개인정보처리방침</h2>
        </div>
        <div class="box_middle2">
            <ul class="list_info left">
                <li>개인정보처리방침 관리 기능을 사용하기 위해서는 PHP 7 이상이 설치되어있어야 합니다.</li>
                <li>기능 이용을 위한 시스템 세팅 또는 서버이전 필요합니다. 위사 호스팅 이용 시 1:1 고객센터로 문의해 주세요.</li>
                <li>관리 기능을 사용하지 않으시는 경우 처리방침내용의 직접 작성은 디자인 > HTML편집 > 페이지 편집 > 회사 정보 > <a href="./?body=design@editor&type=&edit_pg=2%2F5">개인 정보 처리 방침</a>에서 가능합니다.</li>
            </ul>
        </div>
    <?php } else {
        include_once $engine_dir."/_config/tbl_schema.php";
        include_once $engine_dir.'/_engine/include/design.lib.php';

        getSkinCfg();

        $privacy_api = new Config();
        $privacy_api->privacyTableChk();

        $totalTab = (!$_GET['state']) ? 'active' : '';
        $showTab = ($_GET['state'] === 'show') ? 'active' : '';
        $hiddenTab = ($_GET['state'] === 'hidden') ? 'active' : '';

        include $engine_dir."/_engine/include/paging.php";

        $page = numberOnly($_GET['page']);
        $row = numberOnly($_GET['row']);

        $listParam = $_GET;
        $listParam['retJson'] = false;
        $listParam['orderby'] = ($_GET['orderby']) ? $_GET['orderby'] : 'reg_date';
        $listParam['search_type'] = ($_GET['search_type']) ? $_GET['search_type'] : 'effective_date';
        $listParam['search_str'] = ($_GET['search_str']) ? $_GET['search_str'] : '';
        $listParam['state'] = ($_GET['state']) ? $_GET['state'] : '';

        if($page <= 1) $page = 1;
        if(!$row) $row = 10;
        if($row > 100) $row = 100;

        $block=10;
        $state_cnt = $privacy_api->privacyStateGet($listParam);
        $NumTotalRec = $privacy_api->privacyTotalListGet($listParam);
        $PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
        $PagingInstance->addQueryString(makeQueryString('page'));
        $PagingResult = $PagingInstance->result($pg_dsn);

        $pageRes = $PagingResult['PageLink'];
        $idx = $NumTotalRec-($row*($page-1));
        $listParam['LimitQuery'] = $PagingResult['LimitQuery'];
        $listData = $privacy_api->privacyListGet($listParam);

        setListURL('privacyList');
        ?>
        <script type="text/javascript" src='<?=$engine_url?>/_manage/privacy.js?t=<?=time()?>'></script>
        <form name="privacy_listF" id="privacy_listF" action="/_manage/" method="GET">
            <input type="hidden" name="body" value="config@privacy">
            <input type="hidden" name="exec" value="list">
            <input type="hidden" name="page" value="<?=$page?>">
            <input type="hidden" name="state" value="<?=$listParam['state']?>">
            <div class="box_title first">
                <h2 class="title">개인정보처리방침</h2>
            </div>

            <div id="search">
                <div class="box_search">
                    <div class="box_input">
                        <div class="select_input shadow">
                            <div class="select">
                                <select name="search_type" onchange="privacy_cls.searchStrSet(this.value);">
                                    <option value="effective_date" <?=checked($listParam['search_type'], 'effective_date', true)?>>시행일</option>
                                    <option value="admin" <?=checked($listParam['search_type'], 'admin', true)?>>작성자</option>
                                </select>
                            </div>
                            <div class="area_input">
                                <input type="text" name="search_str" value="<?=$listParam['search_str']?>" class="datepicker input" placeholder="검색어를 입력해주세요.">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box_bottom top_line">
                    <span class="box_btn blue"><button type="button" onClick="privacy_cls.listGet();">검색</button></span>
                    <span class="box_btn"><button type="button" onClick="location.href='/_manage/?body=config@privacy';">초기화</button></span>
                </div>
            </div>

            <style>
                @media all and (max-width:1700px) {
                    .box_tab .btns {top:55px; z-index:5;}
                }
            </style>

            <div class="box_tab">
                <ul>
                    <li><a href="javascript:;" class="<?=$totalTab?>" onClick="privacy_cls.listStateSet('');">전체<span><?=number_format($state_cnt['total'])?></span></a></li>
                    <li><a href="javascript:;" class="<?=$showTab?> show" onClick="privacy_cls.listStateSet('show');">사용<span><?=number_format($state_cnt['show'])?></span></a></li>
                    <li><a href="javascript:;" class="<?=$hiddenTab?> hide" onClick="privacy_cls.listStateSet('hidden');">미사용<span><?=number_format($state_cnt['hidden'])?></span></a></li>
                </ul>
            </div>

            <div class="box_sort">
                <dl class="list">
                    <dt class="hidden">정렬</dt>
                    <dd>
                        <select name="row" onchange="privacy_cls.listGet();">
                            <option value="10" <?=checked($row, 10, true)?>>10개</option>
                            <option value="20" <?=checked($row, 20, true)?>>20개</option>
                            <option value="30" <?=checked($row, 30, true)?>>30개</option>
                            <option value="50" <?=checked($row, 50, true)?>>50개</option>
                        </select>&nbsp;&nbsp;
                        정렬
                        <select name="orderby" onchange="privacy_cls.listGet();">
                            <option value="reg_date" <?=checked($param['orderby'], 'reg_date', true)?>>작성일순</option>
                            <option value="effective_date" <?=checked($param['orderby'], 'effective_date', true)?>>시행일순</option>
                        </select>
                    </dd>
                </dl>
            </div>

            <table class="tbl_col">
                <caption class="hidden">개인정보처리방침 리스트</caption>
                <colgroup>
                    <col style="width:40px;">
                    <col style="width:50px;">
                    <col>
                    <col style="width:80px;">
                    <col style="width:200px;">
                    <col style="width:200px;">
                </colgroup>
                <thead>
                <tr>
                    <th><input type="checkbox" onclick="checkAll(document.privacy_listF.check_no,this.checked)"></th>
                    <th>번호</th>
                    <th>시행일</th>
                    <th>사용</th>
                    <th>작성자</th>
                    <th>작성일시</th>
                </tr>
                </thead>
                <tbody id="privacy_list">
                <?php
                while ($ldata = $privacy_api->privacyListHtmlSet($listData['data'])) { ?>
                    <tr>
                        <td><input type="checkbox" name="check_no[]" id="check_no" value="<?=$ldata['no']?>"></td>
                        <td><?=$idx?></td>
                        <td class="left" style="white-space: nowrap;">
                            <a href="/_manage/?body=config@privacy_write&no=<?=$ldata['no']?>"><strong><?=$ldata['effective_date']?></strong></a>
                        </td>
                        <td>
                            <div data-switch="<?=$ldata['no']?>" data-hidden="<?=$ldata['hidden']?>" class="switch <?=$ldata['hidden_on']?>" onClick="privacy_cls.hiddenSet(this);"></div>
                        </td>
                        <td class="center order_title">
                            <?=$ldata['admin']?>
                        </td>
                        <td class="center"><?=$ldata['reg_date']?></td>
                    </tr>
                <?php
                $idx--;
                } ?>
                </tbody>
            </table>
            <!-- 페이징 & 버튼 -->

            <div class="box_middle2">
                <ul class="list_info left">
                    <li>개인정보처리방침 내용이 변경되는 경우 공지사항을 통해 변경 사실을 게시해야합니다.</li>
                </ul>
            </div>

            <div class="box_bottom">
                <?=$pageRes?>
                <div class="left_area">
                    <span class="box_btn_s icon delete"><input type="button" value="선택 삭제" onclick="privacy_cls.del();"></span>
                </div>
                <div class="right_area">
                    <span class="box_btn blue"><input type="button" id="privacy_write_btn" value="등록" onclick="privacyModeLayer.open();"></span>
                </div>
            </div>
            <!-- //페이징 & 버튼 -->

        </form>

        <script>
            const privacyModeLayer = new layerWindow('config@privacy_mode.frm');
            const privacy_cls = new Privacy();
        </script>
    <?php } ?>