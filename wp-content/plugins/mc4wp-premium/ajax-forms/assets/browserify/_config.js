'use strict';

function Config(objectName) {
    this.objectName = objectName;
}

Config.prototype.get = function(k, d) {
    return ( window[this.objectName] !== undefined ) ? window[this.objectName][k] : d;
};

Config.prototype.set = function(k, v) {
    if( ! window[this.objectName] ) {
        window[this.objectName] = {};
    }

    window[this.objectName][k] = v;
};

module.exports = Config;