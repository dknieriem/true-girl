'use strict';

var config = mc4wp_ecommerce;
var i18n = mc4wp_ecommerce.i18n;
var m = require('mithril');
var Wizard = require('./_wizard.js');
var QueueProcessor = require('./_queue-processor.js');

// ask for confirmation for elements with [data-confirm] attribute
require('./_confirm-attr.js')();

var nextButtons = document.querySelectorAll('.wizard-step .button.next');

// product wizard
var productIds = mc4wp_ecommerce.product_ids;
var productCounts = mc4wp_ecommerce.product_count;
var productMount = document.getElementById('mc4wp-ecommerce-products-wizard');
var productBarMount = document.getElementById('mc4wp-ecommerce-products-progress-bar');
var productsWizard = new Wizard(productMount, productBarMount, productCounts.tracked, productCounts.all );
productsWizard.on('tick', productTicker);
productsWizard.on('done', enableNextButtons);
if( productsWizard.finished() ) {
    productsWizard.stop();
}

// order wizard
var orderCounts = mc4wp_ecommerce.order_count;
var orderIds = mc4wp_ecommerce.order_ids;
var orderMount = document.getElementById('mc4wp-ecommerce-orders-wizard');
var orderBarMount = document.getElementById('mc4wp-ecommerce-orders-progress-bar');
var ordersWizard = new Wizard(orderMount, orderBarMount, orderCounts.tracked, orderCounts.all );
ordersWizard.on('tick', orderTicker);
ordersWizard.on('done', enableNextButtons);
if(ordersWizard.finished()) {
    ordersWizard.stop();
}

function enableNextButtons(e) {
    [].forEach.call(nextButtons, function(b) {
        b.removeAttribute('disabled');
    });
}

function orderTicker(wizard) {
    let data = new FormData();
    data.append("order_id", orderIds[wizard.index]);

    m.request({
        method: "POST",
        url: ajaxurl + "?action=mc4wp_ecommerce_synchronize_orders",
        data: data,
    }).then(requestSuccessHandler(wizard))
      .catch(requestErrorHandler(wizard));
}


function productTicker(wizard) {
    let data = new FormData();
    data.append("product_id", productIds[wizard.index]);

    m.request({
        method: "POST",
        url: ajaxurl + "?action=mc4wp_ecommerce_synchronize_products",
        data: data,
    }).then(requestSuccessHandler(wizard))
      .catch(requestErrorHandler(wizard));
}

function requestErrorHandler(wizard) {
    return function(e) {
        console.log(e);
        wizard.logger.log(e);

        // proceed anyway
        wizard.tick();
    };
}

function requestSuccessHandler(wizard) {
    return function(response) {
        if( response.data && response.data.message ) {
            wizard.status(response.data.message, response.success);
        }

        wizard.tick();
    };
}

// queue processor
var element = document.getElementById('queue-processor');
if( element ) {
    m.mount( element, QueueProcessor );
}
