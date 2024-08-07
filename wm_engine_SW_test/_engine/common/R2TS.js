function R2TS(objectID, instance, speed, freeze) {
	this.obj = document.getElementById(objectID);

	if(!this.obj) return;
	if(this.obj.offsetHeight >= this.obj.scrollHeight) return;

	this.instance	= instance;
	this.height		= this.obj.firstChild.offsetHeight;
	this.speed		= speed;
	this.freeze		= freeze;
	this.pointer	= this.height;
	this.count		= 0;
	this.sleep		= 0;
	this.mode		= true;

	this.timer = setInterval(this.instance+".moveIt()", this.speed);
}

R2TS.prototype.moveIt = function() {
	if(this.mode == false) return;

	if(this.pointer == this.height) {
		this.sleep++;
		if(this.sleep == this.freeze) {
			this.sleep = 0;
			this.pointer = 0;
			this.height = this.obj.children[this.count].offsetHeight;
		} else {
			if(this.sleep == 1) {
				this.obj.appendChild(this.obj.children[this.count].cloneNode(true));
				this.count++;
			}
		}
		return;
	}

	this.obj.scrollTop++;
	this.pointer++;
}