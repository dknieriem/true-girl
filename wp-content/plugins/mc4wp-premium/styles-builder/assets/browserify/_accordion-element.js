'use strict';

function AccordionElement(element) {
    this.element = element;
    this.heading = element.querySelector('h2, h3, h4');
    this.content = element.querySelector('div');

    element.setAttribute('class','accordion');
    this.heading.setAttribute('class','accordion-heading');
    this.content.setAttribute('class','accordion-content');
    this.content.style.display = 'none';
    this.heading.addEventListener('click', this.toggle.bind(this));
}

/**
 * Open this accordion
 */
AccordionElement.prototype.open = function() {
    this.toggle(true);
};

/**
 * Close this accordion
 */
AccordionElement.prototype.close = function() {
    this.toggle(false);
};

/**
 * Toggle this accordion
 *
 * @param show
 */
AccordionElement.prototype.toggle = function(show) {
    if( typeof(show) !== "boolean" ) {
        show = ( this.content.offsetParent === null );
    }

    this.content.style.display = show ? 'block' : 'none';
    this.element.className = 'accordion ' + ( ( show ) ? 'expanded' : 'collapsed' );
};

module.exports = AccordionElement;