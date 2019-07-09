'use strict';

var m = require('mithril');

function scrollToBottom(element, initialized, context) {
    element.scrollTop = element.scrollHeight;
}

function Logger(element) {
    this.items = [];
    this.collapsed = true;

    m.mount(element, this);
}

Logger.prototype.log = function(message) {
    var line = {
        time: new Date(),
        text: message
    };

    this.items.push(line);
    m.redraw();
};

Logger.prototype.toggle = function() {
    this.collapsed = !this.collapsed;
    m.redraw();
};

Logger.prototype.view = function() {
    if( this.items.length <= 0 ) {
        return '';
    }

    var lines = this.items.map( function( item ) {
        var timeString =
            ("0" + item.time.getHours()).slice(-2)   + ":" +
            ("0" + item.time.getMinutes()).slice(-2) + ":" +
            ("0" + item.time.getSeconds()).slice(-2);

        return m("div", [
            m('span.time', timeString),
            m.trust(item.text )
        ] )
    });

    return [
        m('h4.toggle', { onclick: this.toggle.bind(this) }, m.trust( ( this.collapsed ? "&darr;" : "&uarr;" ) + " Event log (" + lines.length + ")" )),
        m("div.log", {
            config: scrollToBottom,
            style: { display: this.collapsed ? 'none' : 'block' }
        }, lines)
    ];
};

module.exports = Logger;
