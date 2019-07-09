'use strict';

const Cookies = require('js-cookie');
const triggers = [ 'mc_cid', 'mc_eid', 'mc_tc' ];

function getUrlValue(key) {
	var regex = new RegExp(key+'=([^&]+)');
	var matches = regex.exec(window.location.search)
	if(matches) {
		return matches[1];
	}

	return '';
}

// set mc_cid, mc_eid & mc_tc cookies if url params are set
for(var i=0; i<triggers.length; i++ ) {
	let paramName = triggers[i];
	let paramValue = getUrlValue(paramName);
	if(paramValue !== '') {
		Cookies.set(paramName, paramValue, { expires: 14 });
	}
}

// store landing site in mc_landing_site cookie, if not set
if( ! Cookies.get('mc_landing_site') ) {
	Cookies.set('mc_landing_site', window.location.href, { expires: 7 });
}

