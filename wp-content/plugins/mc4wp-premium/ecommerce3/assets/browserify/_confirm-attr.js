'use strict';

module.exports = function(i18n) {
    var confirmationElements = document.querySelectorAll('[data-confirm]');
    for( var i=0; i<confirmationElements.length; i++ ) {
        var element = confirmationElements[i];
        element.addEventListener(element.tagName === 'FORM' ? 'submit' : 'click', function(e) {
            var sure = confirm(e.target.getAttribute('data-confirm') || i18n.confirmation);

            if( ! sure ) {
                e.preventDefault();
                return false;
            }

            return true;
        });
    }
};