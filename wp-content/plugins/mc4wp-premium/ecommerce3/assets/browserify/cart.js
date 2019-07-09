'use strict';

var checkoutForm = document.querySelector('form.woocommerce-checkout, form[name="checkout"]');
var previousEmailAddress;
var previousDataString;
var scheduledFunctionCall;
var formSubmitted = false;
var serialize = require('form-serialize');
var ajaxurl = typeof(mc4wp_ecommerce_cart) !== "undefined" ? mc4wp_ecommerce_cart.ajax_url : woocommerce_params.ajax_url;

function isEmailAddressValid(emailAddress) {
   var regex = /\S+@\S+\.\S+/;
   return regex.test(emailAddress);
}

function sendFormData() {
   var data = serialize(checkoutForm, { hash: true });
   data.previous_billing_email = previousEmailAddress;
   var allowedFields = [ 'billing_email', 'billing_first_name', 'billing_last_name', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_country' ];
   var dataString = JSON.stringify(data, allowedFields);

   // schedule cart update if email looks valid && data changed.
   if( isEmailAddressValid(data.billing_email) && dataString != previousDataString ) {
      var request = new XMLHttpRequest();
      request.open('POST', ajaxurl + "?action=mc4wp_ecommerce_schedule_cart", true);
      request.setRequestHeader('Content-Type', 'application/json');
      request.send(dataString);

      previousDataString = dataString;
      previousEmailAddress = data.billing_email;
   }
}

if( checkoutForm ) {
   // don't send more than once every 6 seconds
   checkoutForm.addEventListener('change', function() {
      scheduledFunctionCall && window.clearTimeout(scheduledFunctionCall);
      scheduledFunctionCall = window.setTimeout(sendFormData, 6000);
   });

   checkoutForm.addEventListener('submit', function() {
      formSubmitted = true;
   });

   // always send before unloading window, but not if form was submitted
   window.addEventListener('beforeunload', function() {
      if( ! formSubmitted ) {
         sendFormData();
      }
   });
}

