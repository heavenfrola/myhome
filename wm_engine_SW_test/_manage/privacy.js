class Privacy {
    /**
     * 생성자 함수
     * 사용할 제이쿼리 셀렉터들 정의
     * API통신 URL 정의
     * 사용할 JSON정보 정의
     * 기타 내부 변수값 정의
     */
    constructor() {
        this.writeForm = $('#privacy_writeF');
        this.listForm = $('#privacy_listF');
        this.statusTab = $('.box_tab', this.listForm);
        this.privacyView = $('#privacy_view');
        this.procUrl = manage_url+'/main/exec.php?exec_file=api/swapi.exe.php&urlfix=Y&route=config@';
        this.fieldInfo = {
            "collect_path": {
                "sampleText": "[수집경로 작성]"
            },
            "collection": {
                "sampleText": "[수집항목 작성]",
                "sublist": {
                    "basic_info": "일반 정보",
                    "job_info": "직장 정보",
                    "interests": "관심사, 기념일 정보",
                    "legal_agent": "법정대리인 정보",
                    "additional": "활동 정보",
                    "financial": "금융 정보",
                    "log_info": "자동 생성 정보",
                    "etc_info": "기타 정보"
                }
            },
            "purpose": {
                "sampleText": "[수집목적 작성]",
                "sublist": {
                    "service": "서비스",
                    "member_mng": "회원관리",
                    "marketing": "마케팅",
                    "etc2_info": "기타"
                }
            },
            "company_name": {
                "sampleText": "[회사이름]"
            },
            "effective_date": {
                "sampleText": "[시행일자]"
            },
            "customer_service_name": {
                "sampleText": "[고객서비스 담당 부서명]"
            },
            "customer_service_email": {
                "sampleText": "[고객서비스 담당 이메일]"
            },
            "customer_service_phone": {
                "sampleText": "[고객서비스 담당 연락처]"
            },
            "security_service_name": {
                "sampleText": "[개인 정보보호 책임자]"
            },
            "security_service_email": {
                "sampleText": "[개인 정보보호 책임자 이메일]"
            },
            "security_service_phone": {
                "sampleText": "[개인 정보보호 책임자 연락처]"
            },
            "remove": {
                "sampleText": "[개인정보 파기 절차 및 방법 기술]",
                "sublist": {
                    "remove_method": "파기 절차 및 방법"
                }
            },
            "auto_collect": {
                "sampleText": "[개인정보 자동수집 장치 운영여부 및 거부방법 기술]"
            }
        };

        this.parsingData = {};
        this.sampleTextAssets = `<link rel="stylesheet" type="text/css" href="<?=$_css_tmp_url?>">`;
        this.sampleText = `<style>
            #privacy table, #privacy th, #privacy td {border:1px solid #000; border-collapse:collapse; padding:5px;}
            </style>
            <div id="privacy">
                <p>
                    '{{company_name}}'은 (이하 '회사'는) 고객님의 개인정보를 중요시하며, "정보통신망 이용촉진 및 정보보호"에 관한 법률을 준수하고 있습니다.<br>
                    회사는 개인정보처리방침을 통하여 고객님께서 제공하시는 개인정보가 어떠한 용도와 방식으로 이용되고 있으며, 개인정보보호를 위해 어떠한 조치가 취해지고 있는지 알려드립니다.<br>
                    회사는 개인정보처리방침을 개정하는 경우 웹사이트 공지사항(또는 개별공지)을 통하여 공지할 것입니다.<br>
                    본 방침은 {{effective_date}} 부터 시행됩니다.
                </p>
                <ul>
                    <li>
                        <dl>
                            <dt>수집하는 개인정보 항목</dt>
                            <dd>회사는 {{collect_path}} 등등을 위해 아래와 같은 개인정보를 수집하고 있습니다.</dd>
                        </dl>
                    </li>
                    <li>
                        <dl>
                            <dt>수집항목</dt>
                            <dd>
                                {{collection}}
                            </dd>
                        </dl>
                    </li>
                    <li>
                        <dl>
                            <dt>개인정보의 수집 및 이용 목적</dt>
                            <dd>회사는 수집한 개인정보를 다음의 목적을 위해 활용합니다.</dd>
                            <dd>
                                {{purpose}}
                            </dd>
                        </dl>
                    </li>
                    <li>
                        <dl>
                            <dt>개인정보의 보유 및 이용기간</dt>
                            <dd>회사는 개인정보 수집 및 이용목적이 달성된 후에는 예외 없이 해당 정보를 지체 없이 파기합니다.</dd>
                        </dl>
                    </li>
                    <li>
                        <dl>
                            <dt>개인정보의 파기절차 및 방법</dt>
                            <dd>
                                회사는 원칙적으로 개인정보 수집 및 이용목적이 달성된 후에는 해당 정보를 지체없이 파기합니다. 파기절차 및 방법은 다음과 같습니다.
                                {{remove}}
                            </dd>
                        </dl>
                    </li>
                    <li>
                        <dl>
                            <dt>개인정보 제공</dt>
                            <dd>
                                회사는 이용자의 개인정보를 원칙적으로 외부에 제공하지 않습니다. 다만, 아래의 경우에는 예외로 합니다.
                                <ul>
                                    <li>이용자들이 사전에 동의한 경우</li>
                                    <li>법령의 규정에 의거하거나, 수사 목적으로 법령에 정해진 절차와 방법에 따라 수사기관의 요구가 있는 경우</li>
                                </ul>
                            </dd>
                        </dl>
                    </li>
                    <li>
                        <dl>
                            <dt>수집한 개인정보의 위탁</dt>
                            <dd>
                                회사는 고객님의 동의없이 고객님의 정보를 외부 업체에 위탁하지 않습니다.<br>
                                향후 그러한 필요가 생길 경우, 위탁 대상자와 위탁 업무 내용에 대해 고객님에게 통지하고 필요한 경우 사전 동의를 받도록 하겠습니다.
                            </dd>
                        </dl>
                    </li>
                    <li>
                        <dl>
                            <dt>이용자 및 법정대리인의 권리와 그 행사방법</dt>
                            <dd>
                                이용자 및 법정 대리인은 언제든지 등록되어 있는 자신 혹은 당해 만 14세 미만 아동의 개인정보를 조회하거나 수정할 수 있으며 가입해지를 요청할 수도 있습니다.
                                이용자 혹은 만 14세 미만 아동의 개인정보 조회수정을 위해서는 ‘개인정보변경’(또는 ‘회원정보수정’ 등)을 가입해지(동의철회)를 위해서는 “회원탈퇴”를 클릭하여 본인 확인 절차를 거치신 후 직접 열람, 정정 또는 탈퇴가 가능합니다.
                                혹은 개인정보보호책임자에게 서면, 전화 또는 이메일로 연락하시면 지체없이 조치하겠습니다.
                                귀하가 개인정보의 오류에 대한 정정을 요청하신 경우에는 정정을 완료하기 전까지 당해 개인정보를 이용 또는 제공하지 않습니다. 또한 잘못된 개인정보를 제3자에게 이미 제공한 경우에는 정정 처리결과를 제3자에게 지체없이 통지하여 정정이이루어지도록 하겠습니다.
                                회사는 이용자 혹은 법정 대리인의 요청에 의해 해지 또는 삭제된 개인정보는 “회사가 수집하는 개인정보의 보유 및 이용기간”에 명시된 바에 따라 처리하고 그 외의 용도로 열람 또는 이용할 수 없도록 처리하고 있습니다.
                            </dd>
                        </dl>
                    </li>
                    <li>
                        <dl>
                            <dt>개인정보 자동수집 장치의 설치, 운영 및 그 거부에 관한 사항</dt>
                            <dd>
                                {{auto_collect}}
                            </dd>
                        </dl>
                    </li>
                    <li>
                        <dl>
                            <dt>개인정보에 관한 민원서비스</dt>
                            <dd>
                                회사는 고객의 개인정보를 보호하고 개인정보와 관련한 불만을 처리하기 위하여 아래와 같이 관련 부서 및 개인정보보호책임자를 지정하고 있습니다.
                                <ul>
                                    <li>고객서비스담당 부서 : <strong>{{customer_service_name}}</strong></li>
                                    <li>전화번호 : <strong>{{customer_service_phone}}</strong></li>
                                    <li>이메일 : <strong>{{customer_service_email}}</strong></li>
                                </ul>
                                <ul>
                                    <li>개인정보보호책임자 성명 : <strong>{{security_service_name}}</strong></li>
                                    <li>전화번호 : <strong>{{security_service_phone}}</strong></li>
                                    <li>이메일 : <strong>{{security_service_email}}</strong></li>
                                </ul>
                                귀하께서는 회사의 서비스를 이용하시며 발생하는 모든 개인정보보호 관련 민원을 개인정보보호책임자 혹은 담당부서로 신고하실 수 있습니다.<br>
                                회사는 이용자들의 신고사항에 대해 신속하게 충분한 답변을 드릴 것입니다.<br>
                                기타 개인정보침해에 대한 신고나 상담이 필요하신 경우에는 아래 기관에 문의하시기 바랍니다.
                                <ol>
                                    <li>개인분쟁조정위원회 (<a href="http://www.1336.or.kr/" target="_blank">www.1336.or.kr/</a>1336)</li>
                                    <li>정보보호마크인증위원회 (<a href="http://www.eprivacy.or.kr/" target="_blank">www.eprivacy.or.kr/</a>02-580-0533~4)</li>
                                    <li>대검찰청 인터넷범죄수사센터 (<a href="http://icic.sppo.go.kr/" target="_blank">http://icic.sppo.go.kr/</a>02-3480-3600)</li>
                                    <li>경찰청 사이버테러대응센터 (<a href="http://www.ctrc.go.kr/" target="_blank">www.ctrc.go.kr/</a>02-392-0330)</li>
                                </ol>
                            </dd>
                        </dl>
                    </li>
                </ul>
            </div>`;
    }

    /**
     * 리스트출력
     */
    listGet() {
        this.listForm.submit();
    }

    /**
     * 미리보기 실행
     * @param flag
     */
    preview(flag) {
        this.formData = this.writeForm.serializeObject();
        if (flag === 'wizard') {
            this.docSet();
            this.replace('preview');
        } else {
            submitContents('contents', '');
            this.doc = $('textarea#contents').val();
        }

        // sendPreviewF 폼이 없으면 생성합니다.
        let formElement = document.querySelector('form[name="sendPreviewF"]');
        if (!formElement) {
            formElement = document.createElement('form');
            formElement.name = 'sendPreviewF';
            formElement.style.display = 'none'; // 폼을 화면에 표시하지 않도록 합니다.
            document.body.appendChild(formElement);
        }

        // FormData 객체를 생성하고 contents 변수를 추가합니다.
        const formData = new FormData(formElement);
        formData.append('contents', this.doc);

        // form 요소를 생성하고 FormData 객체의 데이터를 input 요소로 변환하여 추가합니다.
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/content/content.php?cont=privacy';
        form.target = '_blank';

        for (let [key, value] of formData.entries()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }

        // form 요소를 body에 추가하고 submit() 메서드를 호출하여 새 창을 엽니다.
        document.body.appendChild(form);
        form.submit();

    }

    /**
     * 본문 컨텐츠 아이프레임 크기 조절
     * @param iframe
     */
    autoHeightIframe(iframe) {
        iframe.style.height = (iframe.contentWindow.document.body.scrollHeight + 100) + 'px';
    }

    /**
     * 게시물 저장
     * @returns {Promise<boolean>}
     */
    async save() {
        this.formData = this.writeForm.serializeObject();
        if (!await this.duplicateChk(this.formData.effective_date, this.formData.no)) {
            return false;
        }
        let confirm_ment = '등록하시겠습니까?';
        if (this.formData.no > 0) {
            //수정인경우
            confirm_ment = '수정하시겠습니까?';
        }
        if (confirm(confirm_ment)) {
            submitContents('contents', '');
            this.doc = $('textarea#contents').val();
            let hidden = (this.formData?.hidden) ?? 'Y'; //존재하지 않는다면 Y(숨김)
            let param = {
                'contents': this.doc,
                'no': this.formData?.no,
                'hidden': hidden,
                'effective_date': this.formData.effective_date,
                'pageType': this.formData.pageType,
            };
            api(this.procUrl+'privacySet', 'POST', param)
                .then(ret => {
                    if (ret.status === 'success') {
                        location.href = './?body=config@privacy_view&no=' + ret.data.no;
                    }
                });
        }
    }

    /**
     * 마법사에서 입력한 데이터를 문자열로 치환
     * @param field
     * @param data
     * @returns {*}
     */
    parsingUserData(field, data) {
        let ret = '';
        if (typeof data === 'object') {
            ret += data.join(', ');
            if (data.includes('기타') && this.formData[field + '_text']) {
                ret += ' (' + this.formData[field + '_text'] + ')';
            }
        } else {
            ret = data;
            if (data === '기타' && this.formData[field + '_text']) {
                ret += ' (' + this.formData[field + '_text'] + ')';
            }
        }
        return ret;
    }

    /**
     * 본문 내용 생성
     */
    docSet() {
        if (this.formData['auto_collect']) {
            if (this.formData['auto_collect'] === 'Y') {
                this.formData['auto_collect'] = `회사는 귀하의 정보를 수시로 저장하고 찾아내는 "쿠키(cookie)" 등을 운용합니다.
                    쿠키란 웹사이트를 운영하는데 이용되는 서버가 귀하의 브라우저에 보내는 아주 작은 텍스트 파일로서 귀하의 컴퓨터 하드디스크에 저장됩니다.
                    회사은(는) 다음과 같은 목적을 위해 쿠키를 사용합니다.
                        <dl>
						<dt>[쿠키 설정 거부 방법]</dt>
						<dd>1. 쿠키 설정을 거부하는 방법으로는 회원님이 사용하시는 웹 브라우저의 옵션을 선택함으로써 모든 쿠키를 허용하거나 쿠키를 저장할 때마다 확인을 거치거나, 모든 쿠키의 저장을 거부할 수 있습니다.</dd>
						<dd>2. 설정방법 예(인터넷 익스플로어의 경우) : 웹 브라우저 상단의 도구 &gt; 인터넷 옵션 &gt; 개인정보</dd>
						<dd>3. 단, 귀하께서 쿠키 설치를 거부하였을 경우 서비스 제공에 어려움이 있을 수 있습니다.</dd>
					</dl>`;
            } else {
                this.formData['auto_collect'] = `회사는 귀하의 정보를 수시로 저장하고 찾아내는 '쿠키(cookie)' 등을 운용하고 있지 않습니다.`;
            }
        }
        for (let field in this.fieldInfo) {
            if (this.fieldInfo[field]['sublist']) {
                //테이블 형
                let sublist = this.fieldInfo[field]['sublist'];
                this.parsingData[field] = '<table>';
                for (let sub_field in sublist) {
                    let data = this.formData[sub_field];
                    if (data) {
                        let label = sublist[sub_field];
                        this.parsingData[field] += '<tr><td>' + label + '</td><td>' + this.parsingUserData(sub_field, data) + '</td></tr>';
                    }
                }
                this.parsingData[field] += '</table>';
            } else {
                //일반 텍스트형
                this.parsingData[field] = this.parsingUserData(field, this.formData[field]);
            }
        }
    }

    /**
     * 샘플텍스트내 치환코드 처리
     * @param {string} mode
     */
    replace(mode = '') {
        this.doc = '';
        if (mode === 'preview') {
            this.doc = this.sampleTextAssets + this.sampleText;
        } else {
            this.doc = this.sampleText;
        }
        for (let field in this.parsingData) {
            let text = this.parsingData[field];
            this.doc = this.doc.replace('{{' + field + '}}', text);
        }
    }

    /**
     * 마법사내 입력된 데이터를 파싱한 후 실제 본문 입력화면으로 이동
     * @returns {Promise<boolean>}
     */
    async wizardNext() {
        this.formData = this.writeForm.serializeObject();
        if (!await this.duplicateChk(this.formData.effective_date)) {
            return false;
        }
        if (confirm('등록할 내용을 모두 체크 하셨습니까?')) {
            this.docSet(); //마법사에 입력된 내용으로 파싱데이터 생성
            this.replace(); //파싱데이터로 문서내용 작성
            $('[name=contents]', this.writeForm).val(this.doc);
            this.writeForm.submit();
        }
    }

    /**
     * 컨텐츠 뷰 화면 생성
     * @param {number} no
     */
    viewSet(no) {
        let param = {
            'no' : no
        }
        api(this.procUrl+'privacyGet', 'POST', param)
            .then(ret => {
                if (ret.status === 'success') {
                    let obj = ret.data;
                    $('#admin', this.privacyView).text(obj.admin);
                    $('#effective_date', this.privacyView).text(obj.effective_date);
                    $('#reg_date', this.privacyView).text(obj.reg_date);
                    $('#hidden', this.privacyView).text(obj.show_text);
                    let skin_url = $('[name=skin_url]', this.privacyView).val();
                    //현재 스킨의 style.css 호출
                    obj.contents = `<style>@import url('${skin_url}/style.css');</style>`+obj.contents;
                    $('#contents iframe', this.privacyView).attr('srcdoc', obj.contents);
                }
            });
    }

    /**
     * 시행일이 겹치는 데이터가 있는지 확인한다
     * @param {string} effective_date
     * @returns {boolean}
     */
    async duplicateChk(effective_date, no = 0) {
        try {
            let param = {
                'effective_date' : effective_date,
                'no' : no
            }
            const response = await api(this.procUrl+'privacyDuplicateChk', 'POST', param)
                .then(ret => {
                    if (ret.status === 'success') {
                        return true;
                    } else {
                        return false;
                    }
                });
            return response;
        } catch (error) {
            return false;
        }
    }

    /**
     * 일반 텍스트모드로 신규등록시, 기본 샘플 텍스트 생성 후 배치
     */
    sampleDocSet() {
        let param = {
            'last' : true
        }
        api(this.procUrl+'privacyGet', 'POST', param)
            .then(ret => {
                let html = '';
                if (ret.status === 'success') {
                    html = ret.data.contents;
                } else {
                    html = this.sampleText;
                    for (let field in this.fieldInfo) {
                        if (this.fieldInfo[field]['sampleText']) {
                            let text = this.fieldInfo[field]['sampleText'];
                            html = html.replace('{{' + field + '}}', text);
                        }
                    }
                }
                $('#contents').val(html);
            });
    }

    /**
     * 컨텐츠 수정처리
     * @param no
     */
    modifySet(no) {
        let param = {
            'no':no
        }
        api(this.procUrl+'privacyGet', 'POST', param)
            .then(ret => {
                if (ret.status === 'success') {
                    let obj = ret.data;
                    $('#admin', this.writeForm).text(obj.admin);
                    $('[name=effective_date]', this.writeForm).val(obj.effective_date);
                    $('#reg_date', this.writeForm).text(obj.reg_date);
                    if (obj.hidden === 'N') {
                        $('[name=hidden]', this.writeForm).prop('checked', true);
                    } else {
                        $('[name=hidden]', this.writeForm).prop('checked', false);
                    }
                    $('[name=pageType]', this.writeForm).val(obj.pageType);
                    $('#contents', this.writeForm).val(obj.contents);
                }
            });
    }

    /**
     * 컨텐츠 삭제 처리
     * @param no
     */
    del(no = 0) {
        let checked_no = [];
        if (no) {
            checked_no.push(no);
        } else {
            $("[name='check_no[]']:checked", this.listForm).each(function () {
                checked_no.push($(this).val());
            });
        }
        if (checked_no[0]) {
            let confirm_text = '삭제하시겠습니까?';
            if (checked_no.length > 1) {
                confirm_text = checked_no.length + '건을 ' + confirm_text;
            }
            if (confirm(confirm_text)) {
                let param = {
                    'no':checked_no
                };
                api(this.procUrl+'privacyDel', 'POST', param )
                    .then(ret => {
                        if (ret.status === 'success') {
                            if (checked_no.length > 1) {
                                this.listGet();
                            } else {
                                location.href = './?body=config@privacy';
                            }
                        }
                    });
            }
        } else {
            alert('삭제할 처리방침을 선택해야합니다');
        }
    }

    /**
     * 컨텐츠 숨김처리
     * @param {object} obj 클릭한 html요소
     */
    hiddenSet(obj) {
        let $obj = $(obj);
        let hidden = ($obj.attr('data-hidden') === 'Y') ? 'N' : 'Y';
        let no = $obj.data('switch');
        let param = {
            'no' : no,
            'hidden' : hidden
        }
        api(this.procUrl+'privacyHiddenSet', 'POST', param)
            .then(ret => {
                if (ret.status === 'success') {
                    location.reload();
                }
            });
    }

    /**
     * 리스트내 전체, 사용, 미사용 탭 클릭
     * @param {string} flag
     */
    listStateSet(flag = '') {
        $('ul li a', this.statusTab).removeClass('active');
        if (flag) {
            $('ul li a.' + flag, this.statusTab).addClass('active');
        } else {
            $('ul li a', this.statusTab).eq(0).addClass('active');
        }

        $('[name=state]', this.listForm).val(flag);
        $('[name=page]', this.listForm).val(1);
        this.listGet();
    }

    /**
     * 키워드 검색조건에 따른 datepicker 사용처리
     * @param {string} searchType
     */
    searchStrSet(searchType) {
        let search_str = $('[name=search_str]', this.listForm);
        search_str.datepicker('destroy');
        if (searchType === 'effective_date') {
            search_str.addClass('datepicker').datepicker();
        } else {
            search_str.removeClass('datepicker');
        }
    }
}