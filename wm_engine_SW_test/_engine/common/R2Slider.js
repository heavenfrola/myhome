R2Slider = function(id, slider, divPitch, marginTop, dElement) {
    if (isNaN(parseInt(marginTop))) marginTop = 0;    // 상단 마진 디폴트
    if (isNaN(parseInt(divPitch))) divPitch = 15;    // 이동 간격 디폴트
    if (!dElement) dElement = document.documentElement; // DTD strict 일때 ( Transitional 일때는 document.body )

    this.timer;    // 타이머 변수
    this.slider = slider;    // 객체 변수명
    this.obj = document.getElementById (id);    // 오브젝트
    this.marginTop = parseInt(marginTop);    // 상단 마진
    this.divPitch = parseInt(divPitch);    // 이동 간격
    this.dElement = dElement; // DTD 에 따른 도큐먼트 엘리먼트
    this.limitTop;     // 상단 한계점
    this.limitBottom;     // 하단 한계점
}


R2Slider.prototype.moveIt = function(){
    var pitch = (parseInt(this.dElement.scrollTop)+ parseInt(this.marginTop)) - parseInt(this.obj.style.top);

    if (pitch == 0) return;
    else nextPos = parseInt(this.obj.style.top) + pitch / this.divPitch
    nextPos = (pitch > 0) ? Math.ceil(nextPos) : Math.floor(nextPos);

    var limitBottom = this.dElement.scrollHeight - parseInt(this.limitBottom)- parseInt(this.obj.offsetHeight);
    if ( this.limitTop && nextPos  < this.limitTop ) nextPos = this.limitTop;
    if ( this.limitBottom && nextPos  > limitBottom ) nextPos = limitBottom;
    if (nextPos < this.marginTop) nextPos = this.marginTop;
    if (isNaN(nextPos)) nextPos = 0;

    this.obj.style.top = nextPos+"px";
}

R2Slider.prototype.slide = function() {
	if(this.obj) {
	    this.timer = setInterval(""+this.slider+".moveIt()", 10);
	}
}
