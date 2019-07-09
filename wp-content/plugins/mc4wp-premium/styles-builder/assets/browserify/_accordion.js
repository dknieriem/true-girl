'use strict';

var AccordionElement = require('./_accordion-element.js');

function Accordion(element) {

	var accordions = [],
		accordionElements;

	// add class to container
	element.className+= " accordion-container";

	// find accordion blocks
	accordionElements = element.children;

	// hide all content blocks
	for( var i=0; i < accordionElements.length; i++) {

		// only act on direct <div> children
		if( accordionElements[i].tagName.toUpperCase() !== 'DIV' ) {
			continue;
		}

		// create new accordion
		var acEl = new AccordionElement(accordionElements[i]);

		// add to list of accordions
		accordions.push(acEl);
	}

	// open first accordion
	accordions[0].open();
}

module.exports = Accordion;