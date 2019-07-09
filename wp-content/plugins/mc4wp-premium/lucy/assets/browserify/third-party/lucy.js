'use strict';

var m = require('mithril');
var algoliasearch = require('algoliasearch/lite');

function addEvent(element, event, handler) {
	if(element.addEventListener){
		element.addEventListener(event,handler,false);
	} else {
		element.attachEvent('on' + event, handler);
	}
}

function removeEvent(element, event, handler){
	if(element.removeEventListener){
		element.removeEventListener(event, handler);
	} else {
		element.detachEvent('on' + event, handler);
	}
}

function maybeClose(event) {
	// close when pressing ESCAPE
	if(event.type === 'keyup' && event.keyCode == 27 ) {
		this.close();
		return;
	}

	// close when clicking ANY element outside of Lucy
	var clickedElement = event.target || event.srcElement;
	if(event.type === 'click' && this.element.contains && ! this.element.contains(clickedElement) )  {
		this.close();
	}
}

function listenForInput(event) {
	var element = event.target || event.srcElement;
	var value = element.value;

	// revert back to list of links when empty
	if( value === '' && this.searchQuery !== '' ) {
		this.reset();
		return;
	}

	this.searchQuery = value;

	// perform search on [ENTER]
	if(event.keyCode == 13 ) {
		this.search(value);
	}
}

var Lucy = function( algoliaAppId, algoliaAppKey, algoliaIndexName, links, contactLink ) {

	this.algolia = algoliasearch( algoliaAppId, algoliaAppKey ).initIndex( algoliaIndexName );
	this.opened = false;
	this.loader = null;
	this.searchResults = null;
	this.searchQuery = '';
	this.element = document.createElement('div');
	this.element.setAttribute('class','lucy closed');
	this.hrefLinks = links;
	this.hrefContactLink = contactLink;
	
	document.body.appendChild(this.element);
	m.mount(this.element, { view: this.getDOM.bind(this) });
};

Lucy.prototype.getDOM = function() {

	var results = "";

	if( this.searchQuery.length > 0 ) {
		if( this.searchResults === null ) {
			results = m("em.search-pending", "Hit [ENTER] to search for \""+ this.searchQuery + "\"..")
		} else if( this.searchResults.length > 0 ) {
			results = this.searchResults.map(function(l) {
				return m('a', { href: l.href }, m.trust(l.text) );
			})
		} else {
			results = m('em.search-pending',  "Nothing found for \""+ this.searchQuery + "\"..");
		}
	}

	return [
		m('div.lucy--content', { style: { display: this.opened ? 'block' : 'none' } }, [
			m('span.close-icon', { onclick: this.close.bind(this) }, ""),
			m('div.header', [
				m('h4', 'Looking for help?'),
				m('div.search-form', {
					onsubmit: this.search.bind(this)
				}, [
					m('input', {
						type: 'text',
						value: this.searchQuery,
						onkeyup: listenForInput.bind(this),
						onupdate: (function(vnode) { console.log(this); if(this.opened) { vnode.dom.focus(); } }).bind(this),
						placeholder: 'What are you looking for?'
					}),
					m('span', {
						"class": 'loader',
						oncreate: (function(vnode) {
							this.loader = vnode.dom;
						}).bind(this)
					}),
					m('input', { type: 'submit' })
				])
			]),
			m('div.list', [

				m('div.links', { style: { display: this.searchQuery.length > 0 ? 'none' : 'block' } }, this.hrefLinks.map(function(l) {
					return m('a', { href: l.href }, m.trust(l.text) );
				})),

				m('div.search-results', results)

			]),
			m('div.footer', [
				m("span", "Can't find the answer you're looking for?"),
				m("a", { "class": 'button button-primary', href: this.hrefContactLink, target: "_blank" }, "Contact Support")
			])
		]),
		m('span.lucy-button', {
			onclick: this.open.bind(this),
			style: { display: this.opened ? 'none' : 'block' }
		}, [
			m('span.lucy-button-text',  "Need help?")
		])
	];
};

Lucy.prototype.open = function() {
	if( this.opened ) return;
	this.opened = true;

	this.element.setAttribute('class', 'lucy open' );

	m.redraw();

	addEvent(document, 'keyup', maybeClose.bind(this));
	addEvent(document, 'click', maybeClose.bind(this));
};

Lucy.prototype.close = function() {
	if( ! this.opened ) return;
	this.opened = false;

	this.reset();
	this.element.setAttribute('class', 'lucy closed' );

	removeEvent(document, 'keyup', maybeClose.bind(this));
	removeEvent(document, 'click', maybeClose.bind(this));
};

Lucy.prototype.reset = function() {
	this.searchQuery = '';
	this.searchResults = null;
	m.redraw();
};

Lucy.prototype.search = function(query) {
	var loader = this.loader;
	var tick = function() {
		loader.innerText += '.';

		if( loader.innerText.length > 3 ) {
			loader.innerText = '.';
		}
	};

	// start loader
	loader.innerText = '.';
	var loadingInterval = window.setInterval(tick, 333 );

	// search
	var handleAlgoliaResults = function( error, result ) {
		this.searchResults = [];

		/* clear loader */
		loader.innerText = '';
		window.clearInterval(loadingInterval);

		if( error ) {
			console.log(error);
			return;
		}

		this.searchResults = result.hits.map(function(r) {
			return { href: r.url, text: r._highlightResult.title.value};
		});

		m.redraw();
	};

	this.algolia.search( query, { hitsPerPage: 5 }, handleAlgoliaResults.bind(this));
};



module.exports = Lucy;
