'use strict';

var m = require('mithril');
var qp = {};
var i18n = mc4wp_ecommerce.i18n;
var state = {
   working: false,
   done: false,
   action: "process",
};

function chooseProcess(e) {
  state.action = e.target.value; 
}

function chooseReset(e) {
  if(confirm(i18n.confirmation)) { 
    state.action = e.target.value; 
  } else { 
    e.preventDefault(); 
  }
}

function process(e) {
    e && e.preventDefault();

    state.working = true;
    state.done = false;

    m.request({
      method: "POST",
      url: ajaxurl + "?action=" + "mc4wp_ecommerce_"+ state.action +"_queue",
    }).then(function(result) {
       state.done = true;
       state.working = false;
    }).catch(function(e) {
       console.log(e);
    })
}

qp.view = function() {
    return m('form', {
        method: "POST",
        onsubmit: process,
    }, [
       m('p', [
          m( 'button', {
               type: "submit",
               className: "button button-primary",
               value: 'process',
               disabled: state.working || state.done,
               onclick: chooseProcess,
           }, state.done && state.action === 'process' ? i18n.done : i18n.process),
          m.trust('&nbsp; or &nbsp;'),
          m( 'button', {
               type: "submit",
               className: "button button-link-delete",
               value: 'reset',
               disabled: state.working || state.done,
               onclick: chooseReset,
           }, state.done && state.action === 'reset' ? i18n.done : i18n.reset),
       ]),
       state.working ? m('p', [ ' ', m('span.mc4wp-loader'), ' ', m('span.help', i18n.processing )]) : ''
    ]);
};

module.exports = qp;
