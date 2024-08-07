var tid = null;
$(document).ready(function() {
    let find_type = $("input[name=find_type]").eq(0);
    find_type.prop("checked",true);
    typechk(find_type.val());
});
function typechk(type) {
    if(type==1) {
        $('#text_cell').show();
        $('#text_email').hide();
    }else {
        $('#text_cell').hide();
        $('#text_email').show();
    }
}
function unlockchk(f) {
    if(!f.reg_code.value) {
        alert("인증번호를 입력하세요.");
        return false;
    }

    if(parseInt($('#min').text())==0 && parseInt($('#sec').text())==0) {
        alert("인증번호 유효시간이 초과되었습니다.");
        return false;
    }
}
function confirmSend(f) {
    if(f.find_type.value==1) {
        if(!f.cell.value) {
            alert("휴대폰 번호를 입력하세요.");
            return false;
        }
    }else {
        if(!f.email.value) {
            alert("이메일을 입력하세요.");
            return false;
        }
    }

    if(parseInt($('#min').text())<3) {
        $('#counter').html('');
        if(tid) {
            clearInterval(tid);
        }
    }

    if($('#counter').html()=='') {
        $.post('./index.php?body=intra@access_limit.exe', {'exec':'confirm', 'find_type':f.find_type.value,  'cell':f.cell.value, 'email':f.email.value}, function(r) {
            if(r=='OK') {
                alert("인증번호가 발송되었습니다. 발송된 인증번호의 유효시간은 5분입니다.");
                f.btn_confirm.value = "인증번호 재요청";
                setCertTimer();
            } else {
                alert(r);
            }
        });
    }else {
        if(parseInt($('#min').text())==4) {
            alert("인증번호 재요청은 1분 "+padZero(parseInt(cnt%60))+"초 뒤에 가능합니다.");
        }else if(parseInt($('#min').text())==3) {
            alert("인증번호 재요청은 "+padZero(parseInt(cnt%60))+"초 뒤에 가능합니다.");
        }
        return false;
    }
}
function setCertTimer() {
    cnt = 5*60;
    tid = setInterval("showCertTimer()",1000);
}

function showCertTimer() {
    $("#counter").html("<strong id='min'>"+padZero(parseInt(cnt/60))+"</strong>분 <strong id='sec'>"+padZero(parseInt(cnt%60))+"</strong>초");
    cnt--;

    if (cnt<0) {
        clearInterval(tid);
    }
}

function padZero(n) {
    return n>9?n:"0"+n;
}