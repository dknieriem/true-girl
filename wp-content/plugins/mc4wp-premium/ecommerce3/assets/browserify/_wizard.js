'use strict';

var Bar = require('./_progress-bar.js');
var Logger = require( './_logger');
var EventEmitter = require('wolfy87-eventemitter');
var heir = require('heir');
var i18n = mc4wp_ecommerce.i18n;

function Wizard(element, barElement, start, end ) {
    this.element = element;
    this.form = element.tagName === 'FORM' ? element : element.querySelector('form');
    this.button = element.querySelector('input[type="submit"], input[type="button"], button');
    this.form.addEventListener('submit', this.toggle.bind(this));
    this.running = false;
    this.progress = new Bar(barElement, start, end);

    this.loader = document.createElement('span');
    this.loader.className = 'mc4wp-loader';
    this.loader.style.visibility = 'hidden';
    this.button.parentNode.insertBefore(this.loader, this.button.nextSibling );

    this.statusElement = document.createElement('span');
    this.statusElement.className = 'wizard-status';
    this.loader.parentNode.insertBefore(this.statusElement, this.loader.nextSibling);

    // init logger
    var logElement = document.createElement('div');
    element.parentNode.appendChild(logElement);
    this.logger = new Logger(logElement);

    this.alpha = start;
    this.omega = end;
    this.index = 0;
}

heir.inherit(Wizard, EventEmitter);

Wizard.prototype.status = function(text, positive) {
    var el = this.statusElement.cloneNode(true);
    var parentNode = this.statusElement.parentNode;

    el.innerText = text.split("\n")[0]; // log a single line only
    el.style.color = !!positive ? 'limegreen' : 'orangered';

    parentNode.removeChild(this.statusElement);
    parentNode.insertBefore(el, this.statusElement.nextSibling);
    this.statusElement = el;

    this.logger.log(text); // write full text to logger
};

Wizard.prototype.toggle = function(e) {
    e && e.preventDefault();
    this.running ? this.pause() : this.start();
};

Wizard.prototype.start = function(e) {
    e && e.preventDefault();
    this.button.value = i18n.pause;
    this.running = true;
    this.loader.style.visibility = 'visible';

    this.emitEvent('tick', [this]);
};

Wizard.prototype.pause = function(e) {
    e && e.preventDefault();
    this.button.value = i18n.resume;
    this.running = false;
    this.loader.style.visibility = 'hidden';
};

Wizard.prototype.stop = function(e) {
    e && e.preventDefault();
    this.button.value = i18n.done;
    this.button.disabled = true;
    this.running = false;
    this.loader.style.visibility = 'hidden';
    this.emitEvent('done', [this]);
};

Wizard.prototype.finished = function() {
    return this.alpha + this.index >= this.omega;
};

Wizard.prototype.tick = function() {
    this.progress.tick();
    this.index++;

    if(this.finished()) {
        this.stop();
    }

    if( this.running ) {
        this.emitEvent('tick', [this]);
    }
};


module.exports = Wizard;
