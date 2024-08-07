function Resize() {
	selfResize();
}

window.onload=function (){
	var tbody=document.getElementById('tbody');
	var obody=document.getElementById('obody');
	if (tbody && obody)
	{
		layTgl(tbody);
		layTgl(obody);
	}
	selfResize();
}

var popup=1;