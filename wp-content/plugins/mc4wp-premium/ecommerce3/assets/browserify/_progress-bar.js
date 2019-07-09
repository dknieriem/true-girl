'use strict';

function getColor(value){
	if( value > 1 ) {
		value = value / 100;
	}

	//value from 0 to 1
	var hue=(value*120).toString(10);
	return ["hsl(",hue,",100%,50%)"].join("");
}

function Bar( element, current, ticks ) {
	this.tickSize = 100 / ticks;
	this.progress = 0;
	this.current = 0;
	this.total = ticks;
	this.color = getColor(this.progress);
	this.done = this.current >= this.total;

	// start at current ticks
	if( current > 0 ) {
		this.tick(current);
	}

	m.mount(element, this);
}

Bar.prototype.view = function() {
	return m('div.progress-bar', [
		m('div.label', this.current + "/" + this.total),
		m('div.line', '' ),
		m('div.bar', { style: { width: this.progress + "%", background: this.color } }, '' )
	]);
};

Bar.prototype.tick = function(ticks) {
	if( this.done ) {
		return;
	}

	ticks = ticks ? ticks : 1;
	this.current += ticks;
	this.progress += ( this.tickSize * ticks );
	this.color = getColor(this.progress);
	this.done = this.current >= this.total;

	m.redraw();
};



module.exports = Bar;