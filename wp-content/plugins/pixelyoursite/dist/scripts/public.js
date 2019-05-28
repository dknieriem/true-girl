/* global pysOptions */

// https://bitbucket.org/pixelyoursite/pys_pro_7/issues/7/possible-ie-11-error
// https://tc39.github.io/ecma262/#sec-array.prototype.includes
if (!Array.prototype.includes) {
    Object.defineProperty(Array.prototype, 'includes', {
        value: function (searchElement, fromIndex) {

            if (this == null) {
                throw new TypeError('"this" is null or not defined');
            }

            // 1. Let O be ? ToObject(this value).
            var o = Object(this);

            // 2. Let len be ? ToLength(? Get(O, "length")).
            var len = o.length >>> 0;

            // 3. If len is 0, return false.
            if (len === 0) {
                return false;
            }

            // 4. Let n be ? ToInteger(fromIndex).
            //    (If fromIndex is undefined, this step produces the value 0.)
            var n = fromIndex | 0;

            // 5. If n ≥ 0, then
            //  a. Let k be n.
            // 6. Else n < 0,
            //  a. Let k be len + n.
            //  b. If k < 0, let k be 0.
            var k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);

            function sameValueZero(x, y) {
                return x === y || (typeof x === 'number' && typeof y === 'number' && isNaN(x) && isNaN(y));
            }

            // 7. Repeat, while k < len
            while (k < len) {
                // a. Let elementK be the result of ? Get(O, ! ToString(k)).
                // b. If SameValueZero(searchElement, elementK) is true, return true.
                if (sameValueZero(o[k], searchElement)) {
                    return true;
                }
                // c. Increase k by 1.
                k++;
            }

            // 8. Return false
            return false;
        }
    });
}

!function ($, options) {

    if (options.debug) {
        console.log('PYS:', options);
    }

    var dummyPinterest = function () {

        /**
         * Public API
         */
        return {

            isEnabled: function () {
            },

            disable: function () {
            },

            loadPixel: function () {
            },

            fireEvent: function (name, data) {
                return false;
            },

            onCommentEvent: function () {
            },

            onDownloadEvent: function (params) {
            },

            onFormEvent: function (params) {
            },

            onWooAddToCartOnButtonEvent: function (product_id) {
            },

            onWooAddToCartOnSingleEvent: function (product_id, qty, is_variable, is_external, $form) {
            },

            onWooRemoveFromCartEvent: function (cart_item_hash) {
            },

            onEddAddToCartOnButtonEvent: function (download_id, price_index, qty) {
            },

            onEddRemoveFromCartEvent: function (item) {
            }

        }

    }();

    var Utils = function (options) {

        var Pinterest = dummyPinterest;

        var gtag_loaded = false;

        function loadPixels() {

            if (!options.gdpr.all_disabled_by_api) {

                if (!options.gdpr.facebook_disabled_by_api) {
                    Facebook.loadPixel();
                }

                if (!options.gdpr.analytics_disabled_by_api) {
                    Analytics.loadPixel();
                }

                if (!options.gdpr.pinterest_disabled_by_api) {
                    Pinterest.loadPixel();
                }

            }

        }

        /**
         * PUBLIC API
         */
        return {

            setupPinterestObject: function () {
                Pinterest = window.pys.Pinterest || Pinterest;
                return Pinterest;
            },

            // Clone all object members to another and return it
            copyProperties: function (from, to) {
                for (var key in from) {
                    to[key] = from[key];
                }
                return to;
            },

            // Returns array of elements with given tag name
            getTagsAsArray: function (tag) {
                return [].slice.call(document.getElementsByTagName(tag));
            },

            getRequestParams: function () {
                return [];
            },

            /**
             * Events
             */

            fireStaticEvents: function (pixel) {

                if (options.staticEvents.hasOwnProperty(pixel)) {

                    $.each(options.staticEvents[pixel], function (eventName, events) {
                        $.each(events, function (index, eventData) {

                            eventData.fired = eventData.fired || false;

                            if (!eventData.fired) {

                                var fired = false;

                                // fire event
                                if ('facebook' === pixel) {
                                    fired = Facebook.fireEvent(eventName, eventData);
                                } else if ('ga' === pixel) {
                                    fired = Analytics.fireEvent(eventName, eventData);
                                } else if ('pinterest' === pixel) {
                                    fired = Pinterest.fireEvent(eventName, eventData);
                                }

                                // prevent event double event firing
                                eventData.fired = fired;

                            }

                        });
                    });

                }

            },

            /**
             * Load tag's JS
             *
             * @link: https://developers.google.com/analytics/devguides/collection/gtagjs/
             * @link: https://developers.google.com/analytics/devguides/collection/gtagjs/custom-dims-mets
             */
            loadGoogleTag: function (id) {

                if (!gtag_loaded) {

                    (function (window, document, src) {
                        var a = document.createElement('script'),
                            m = document.getElementsByTagName('script')[0];
                        a.async = 1;
                        a.src = src;
                        m.parentNode.insertBefore(a, m);
                    })(window, document, '//www.googletagmanager.com/gtag/js?id=' + id);

                    window.dataLayer = window.dataLayer || [];
                    window.gtag = window.gtag || function gtag() {
                        dataLayer.push(arguments);
                    };

                    gtag('js', new Date());

                    gtag_loaded = true;

                }

            },

            /**
             * GDPR
             */

            loadPixels: function () {

                if (options.gdpr.ajax_enabled) {

                    // retrieves actual PYS GDPR filters values which allow to avoid cache issues
                    $.get({
                        url: options.ajaxUrl,
                        dataType: 'json',
                        data: {
                            action: 'pys_get_gdpr_filters_values'
                        },
                        success: function (res) {

                            if (res.success) {

                                options.gdpr.all_disabled_by_api = res.data.all_disabled_by_api;
                                options.gdpr.facebook_disabled_by_api = res.data.facebook_disabled_by_api;
                                options.gdpr.analytics_disabled_by_api = res.data.analytics_disabled_by_api;
                                options.gdpr.google_ads_disabled_by_api = res.data.google_ads_disabled_by_api;
                                options.gdpr.pinterest_disabled_by_api = res.data.pinterest_disabled_by_api;

                            }

                            loadPixels();

                        }
                    });

                } else {
                    loadPixels();
                }

            },

            consentGiven: function (pixel) {

                /**
                 * Cookiebot
                 */
                if (options.gdpr.cookiebot_integration_enabled && typeof Cookiebot !== 'undefined') {

                    var cookiebot_consent_category = options.gdpr['cookiebot_' + pixel + '_consent_category'];

                    if (options.gdpr[pixel + '_prior_consent_enabled']) {
                        if (Cookiebot.consented === false || Cookiebot.consent[cookiebot_consent_category]) {
                            return true;
                        }
                    } else {
                        if (Cookiebot.consent[cookiebot_consent_category]) {
                            return true;
                        }
                    }

                    return false;

                }

                /**
                 * Ginger – EU Cookie Law
                 */
                if (options.gdpr.ginger_integration_enabled) {

                    var ginger_cookie = Cookies.get('ginger-cookie');

                    if (options.gdpr[pixel + '_prior_consent_enabled']) {
                        if (typeof ginger_cookie === 'undefined' || ginger_cookie === 'Y') {
                            return true;
                        }
                    } else {
                        if (ginger_cookie === 'Y') {
                            return true;
                        }
                    }

                    return false;

                }

                /**
                 * Cookie Notice
                 */
                if (options.gdpr.cookie_notice_integration_enabled && typeof cnArgs !== 'undefined') {

                    var cn_cookie = Cookies.get(cnArgs.cookieName);

                    if (options.gdpr[pixel + '_prior_consent_enabled']) {
                        if (typeof cn_cookie === 'undefined' || cn_cookie === 'true') {
                            return true;
                        }
                    } else {
                        if (cn_cookie === 'true') {
                            return true;
                        }
                    }

                    return false;

                }

                /**
                 * Cookie Law Info
                 */
                if (options.gdpr.cookie_law_info_integration_enabled) {

                    var cli_cookie = Cookies.get('viewed_cookie_policy');

                    if (options.gdpr[pixel + '_prior_consent_enabled']) {
                        if (typeof cli_cookie === 'undefined' || cli_cookie === 'yes') {
                            return true;
                        }
                    } else {
                        if (cli_cookie === 'yes') {
                            return true;
                        }
                    }

                    return false;

                }

                return true;

            },

            setupGdprCallbacks: function () {

                /**
                 * Cookiebot
                 */
                if (options.gdpr.cookiebot_integration_enabled && typeof Cookiebot !== 'undefined') {

                    Cookiebot.onaccept = function () {

                        if (Cookiebot.consent[options.gdpr.cookiebot_facebook_consent_category]) {
                            Facebook.loadPixel();
                        }

                        if (Cookiebot.consent[options.gdpr.cookiebot_analytics_consent_category]) {
                            Analytics.loadPixel();
                        }

                        if (Cookiebot.consent[options.gdpr.cookiebot_pinterest_consent_category]) {
                            Pinterest.loadPixel();
                        }

                    };

                    Cookiebot.ondecline = function () {
                        Facebook.disable();
                        Analytics.disable();
                        Pinterest.disable();
                    };

                }

                /**
                 * Cookie Notice
                 */
                if (options.gdpr.cookie_notice_integration_enabled) {

                    $(document).onFirst('click', '.cn-set-cookie', function () {

                        if ($(this).data('cookie-set') === 'accept') {
                            Facebook.loadPixel();
                            Analytics.loadPixel();
                            Pinterest.loadPixel();
                        } else {
                            Facebook.disable();
                            Analytics.disable();
                            Pinterest.disable();
                        }

                    });

                    $(document).onFirst('click', '.cn-revoke-cookie', function () {
                        Facebook.disable();
                        Analytics.disable();
                        Pinterest.disable();
                    });

                }

                /**
                 * Cookie Law Info
                 */
                if (options.gdpr.cookie_law_info_integration_enabled) {

                    $(document).onFirst('click', '#cookie_action_close_header', function () {
                        Facebook.loadPixel();
                        Analytics.loadPixel();
                        Pinterest.loadPixel();
                    });

                    $(document).onFirst('click', '#cookie_action_close_header_reject', function () {
                        Facebook.disable();
                        Analytics.disable();
                        Pinterest.disable();
                    });

                }

            },

            /**
             * DOWNLOAD DOCS
             */

            getLinkExtension: function (link) {

                // Remove anchor, query string and everything before last slash
                link = link.substring(0, (link.indexOf("#") === -1) ? link.length : link.indexOf("#"));
                link = link.substring(0, (link.indexOf("?") === -1) ? link.length : link.indexOf("?"));
                link = link.substring(link.lastIndexOf("/") + 1, link.length);

                // If there's a period left in the URL, then there's a extension
                if (link.length > 0 && link.indexOf('.') !== -1) {
                    link = link.substring(link.indexOf(".") + 1); // Remove everything but what's after the first period
                    return link;
                } else {
                    return "";
                }
            },

            getLinkFilename: function (link) {

                // Remove anchor, query string and everything before last slash
                link = link.substring(0, (link.indexOf("#") === -1) ? link.length : link.indexOf("#"));
                link = link.substring(0, (link.indexOf("?") === -1) ? link.length : link.indexOf("?"));
                link = link.substring(link.lastIndexOf("/") + 1, link.length);

                // If there's a period left in the URL, then there's a extension
                if (link.length > 0 && link.indexOf('.') !== -1) {
                    return link;
                } else {
                    return "";
                }
            }

        };

    }(options);

    var Facebook = function (options) {

        var defaultEventTypes = [
            'PageView',
            'ViewContent',
            'Search',
            'AddToCart',
            'AddToWishlist',
            'InitiateCheckout',
            'AddPaymentInfo',
            'Purchase',
            'Lead',

            'Subscribe',
            'CustomizeProduct',
            'FindLocation',
            'StartTrial',
            'SubmitApplication',
            'Schedule',
            'Contact',
            'Donate'
        ];

        var initialized = false;

        function fireEvent(name, data) {

            var actionType = defaultEventTypes.includes(name) ? 'track' : 'trackCustom';

            var params = {};
            Utils.copyProperties(data, params);
            Utils.copyProperties(options.commonEventParams, params);

            if (options.debug) {
                console.log('[Facebook] ' + name, params);
            }

            fbq(actionType, name, params);

        }

        /**
         * Public API
         */
        return {

            isEnabled: function () {
                return options.hasOwnProperty('facebook');
            },

            disable: function () {
                initialized = false;
            },

            /**
             * Load pixel's JS
             */
            loadPixel: function () {

                if (initialized || !this.isEnabled() || !Utils.consentGiven('facebook')) {
                    return;
                }

                !function (f, b, e, v, n, t, s) {
                    if (f.fbq) return;
                    n = f.fbq = function () {
                        n.callMethod ?
                            n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                    };
                    if (!f._fbq) f._fbq = n;
                    n.push = n;
                    n.loaded = !0;
                    n.version = '2.0';
                    n.agent = 'dvpixelyoursite';
                    n.queue = [];
                    t = b.createElement(e);
                    t.async = !0;
                    t.src = v;
                    s = b.getElementsByTagName(e)[0];
                    s.parentNode.insertBefore(t, s)
                }(window,
                    document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

                // initialize pixel
                options.facebook.pixelIds.forEach(function (pixelId) {

                    if (options.facebook.removeMetadata) {
                        fbq('set', 'autoConfig', false, pixelId);
                    }

                    fbq('init', pixelId, options.facebook.advancedMatching);

                });

                initialized = true;

                Utils.fireStaticEvents('facebook');

            },

            fireEvent: function (name, data) {

                if (!initialized || !this.isEnabled()) {
                    return false;
                }

                data.delay = data.delay || 0;
                data.params = data.params || {};

                if (data.delay === 0) {

                    fireEvent(name, data.params);

                } else {

                    setTimeout(function (name, params) {
                        fireEvent(name, params);
                    }, data.delay * 1000, name, data.params);

                }

                return true;

            },

            onCommentEvent: function () {

                if (initialized && this.isEnabled() && options.facebook.commentEventEnabled) {

                    this.fireEvent('Comment', {
                        params: Utils.copyProperties(options.facebook.contentParams, {})
                    });

                }

            },

            onDownloadEvent: function (params) {

                if (initialized && this.isEnabled() && options.facebook.downloadEnabled) {

                    this.fireEvent('Download', {
                        params: Utils.copyProperties(options.facebook.contentParams, params)
                    });

                }

            },

            onFormEvent: function (params) {

                if (initialized && this.isEnabled() && options.facebook.formEventEnabled) {

                    this.fireEvent('Form', {
                        params: Utils.copyProperties(options.facebook.contentParams, params)
                    });

                }

            },

            onWooAddToCartOnButtonEvent: function (product_id) {

                if (window.pysWooProductData.hasOwnProperty(product_id)) {
                    if (window.pysWooProductData[product_id].hasOwnProperty('facebook')) {

                        this.fireEvent('AddToCart', {
                            params: Utils.copyProperties(window.pysWooProductData[product_id]['facebook'], {})
                        });

                    }
                }

            },

            onWooAddToCartOnSingleEvent: function (product_id, qty, is_variable, $form) {

                window.pysWooProductData = window.pysWooProductData || [];

                if (window.pysWooProductData.hasOwnProperty(product_id)) {
                    if (window.pysWooProductData[product_id].hasOwnProperty('facebook')) {

                        if (is_variable && !options.facebook.wooVariableAsSimple) {
                            product_id = parseInt($form.find('input[name="variation_id"]').val());
                        }

                        var params = Utils.copyProperties(window.pysWooProductData[product_id]['facebook'], {});

                        // maybe customize value option
                        if (options.woo.addToCartOnButtonValueEnabled && options.woo.addToCartOnButtonValueOption !== 'global') {
                            params.value = params.value * qty;
                        }

                        // only when non Facebook for WooCommerce logic used
                        if (params.hasOwnProperty('contents')) {
                            params.contents[0].quantity = qty;
                        }

                        this.fireEvent('AddToCart', {
                            params: params
                        });

                    }
                }

            },

            onWooRemoveFromCartEvent: function (cart_item_hash) {

                window.pysWooRemoveFromCartData = window.pysWooRemoveFromCartData || [];

                if (window.pysWooRemoveFromCartData[cart_item_hash].hasOwnProperty('facebook')) {

                    this.fireEvent('RemoveFromCart', {
                        params: Utils.copyProperties(window.pysWooRemoveFromCartData[cart_item_hash]['facebook'], {})
                    });

                }

            },

            onEddAddToCartOnButtonEvent: function (download_id, price_index, qty) {

                if (window.pysEddProductData.hasOwnProperty(download_id)) {

                    var index;

                    if (price_index) {
                        index = download_id + '_' + price_index;
                    } else {
                        index = download_id;
                    }

                    if (window.pysEddProductData[download_id].hasOwnProperty(index)) {
                        if (window.pysEddProductData[download_id][index].hasOwnProperty('facebook')) {

                            var params = Utils.copyProperties(window.pysEddProductData[download_id][index]['facebook'], {});

                            // maybe customize value option
                            if (options.edd.addToCartOnButtonValueEnabled && options.edd.addToCartOnButtonValueOption !== 'global') {
                                params.value = params.value * qty;
                            }

                            // update contents qty param
                            var contents = JSON.parse(params.contents);
                            contents[0].quantity = qty;
                            params.contents = JSON.stringify(contents);

                            this.fireEvent('AddToCart', {
                                params: params
                            });

                        }
                    }

                }

            },

            onEddRemoveFromCartEvent: function (item) {

                if (item.hasOwnProperty('facebook')) {

                    this.fireEvent('RemoveFromCart', {
                        params: Utils.copyProperties(item['facebook'], {})
                    });

                }

            }

        };

    }(options);

    var Analytics = function (options) {

        var initialized = false;

        /**
         * Fires event
         *
         * @link: https://developers.google.com/analytics/devguides/collection/gtagjs/sending-data
         * @link: https://developers.google.com/analytics/devguides/collection/gtagjs/events
         * @link: https://developers.google.com/gtagjs/reference/event
         * @link: https://developers.google.com/gtagjs/reference/parameter
         *
         * @link: https://developers.google.com/analytics/devguides/collection/gtagjs/custom-dims-mets
         *
         * @param name
         * @param data
         */
        function fireEvent(name, data) {

            var eventParams = Utils.copyProperties(data, {});

            var _fireEvent = function (tracking_id) {

                var params = Utils.copyProperties(eventParams, {send_to: tracking_id});

                if (options.debug) {
                    console.log('[Google Analytics #' + tracking_id + '] ' + name, params);
                }

                gtag('event', name, params);

            };

            options.ga.trackingIds.forEach(function (tracking_id) {
                _fireEvent(tracking_id);
            });

        }

        /**
         * Public API
         */
        return {

            isEnabled: function () {
                return options.hasOwnProperty('ga');
            },

            disable: function () {
                initialized = false;
            },

            loadPixel: function () {

                if (initialized || !this.isEnabled() || !Utils.consentGiven('analytics')) {
                    return;
                }

                Utils.loadGoogleTag(options.ga.trackingIds[0]);

                var config = {
                    'link_attribution': options.ga.enhanceLinkAttr,
                    'anonymize_ip': options.ga.anonimizeIP
                };

                // Cross-Domain tracking
                if (options.ga.crossDomainEnabled) {
                    config.linker = {
                        accept_incoming: options.ga.crossDomainAcceptIncoming,
                        domains: options.ga.crossDomainDomains
                    };
                }

                // configure tracking ids
                options.ga.trackingIds.forEach(function (trackingId) {
                    gtag('config', trackingId, config);
                });

                initialized = true;

                Utils.fireStaticEvents('ga');

            },

            fireEvent: function (name, data) {

                if (!initialized || !this.isEnabled()) {
                    return false;
                }

                data.delay = data.delay || 0;
                data.params = data.params || {};

                if (data.delay === 0) {

                    fireEvent(name, data.params);

                } else {

                    setTimeout(function (name, params) {
                        fireEvent(name, params);
                    }, data.delay * 1000, name, data.params);

                }

                return true;

            },

            onCommentEvent: function () {

                if (initialized && this.isEnabled() && options.ga.commentEventEnabled) {

                    this.fireEvent(window.location.href, {
                        params: {
                            event_category: 'Comment',
                            event_label: $(document).find('title').text(),
                            non_interaction: options.ga.commentEventNonInteractive
                        }
                    });

                }

            },

            onDownloadEvent: function (params) {

                if (initialized && this.isEnabled() && options.ga.downloadEnabled) {

                    this.fireEvent(params.download_url, {
                        params: {
                            event_category: 'Download',
                            event_label: params.download_name,
                            non_interaction: options.ga.downloadEventNonInteractive
                        }
                    });

                }

            },

            onFormEvent: function (params) {

                if (initialized && this.isEnabled() && options.ga.formEventEnabled) {

                    this.fireEvent(window.location.href, {
                        params: {
                            event_category: 'Form',
                            event_label: params.form_class,
                            non_interaction: options.ga.formEventNonInteractive
                        }
                    });

                }

            },

            onWooAddToCartOnButtonEvent: function (product_id) {

                if (window.pysWooProductData.hasOwnProperty(product_id)) {
                    if (window.pysWooProductData[product_id].hasOwnProperty('ga')) {

                        this.fireEvent('add_to_cart', {
                            params: window.pysWooProductData[product_id]['ga']
                        });

                    }
                }

            },

            onWooAddToCartOnSingleEvent: function (product_id, qty, is_variable, $form) {

                window.pysWooProductData = window.pysWooProductData || [];

                if (is_variable) {
                    product_id = parseInt($form.find('input[name="variation_id"]').val());
                }

                if (window.pysWooProductData.hasOwnProperty(product_id)) {
                    if (window.pysWooProductData[product_id].hasOwnProperty('ga')) {

                        var params = Utils.copyProperties(window.pysWooProductData[product_id]['ga'], {});

                        // maybe customize value option
                        if (options.woo.addToCartOnButtonValueEnabled && options.woo.addToCartOnButtonValueOption !== 'global') {
                            params.items[0].price = params.items[0].price * qty;
                        }

                        // update items qty param
                        params.items[0].quantity = qty;

                        this.fireEvent('add_to_cart', {
                            params: params
                        });

                    }
                }

            },

            onWooRemoveFromCartEvent: function (cart_item_hash) {

                window.pysWooRemoveFromCartData = window.pysWooRemoveFromCartData || [];

                if (window.pysWooRemoveFromCartData[cart_item_hash].hasOwnProperty('ga')) {

                    this.fireEvent('remove_from_cart', {
                        params: Utils.copyProperties(window.pysWooRemoveFromCartData[cart_item_hash]['ga'], {})
                    });

                }

            },

            onEddAddToCartOnButtonEvent: function (download_id, price_index, qty) {

                if (window.pysEddProductData.hasOwnProperty(download_id)) {

                    var index;

                    if (price_index) {
                        index = download_id + '_' + price_index;
                    } else {
                        index = download_id;
                    }

                    if (window.pysEddProductData[download_id].hasOwnProperty(index)) {
                        if (window.pysEddProductData[download_id][index].hasOwnProperty('ga')) {

                            var params = Utils.copyProperties(window.pysEddProductData[download_id][index]['ga'], {});

                            // update items qty param
                            params.items[0].quantity = qty;

                            this.fireEvent('add_to_cart', {
                                params: params
                            });

                        }
                    }

                }

            },

            onEddRemoveFromCartEvent: function (item) {

                if (item.hasOwnProperty('ga')) {

                    this.fireEvent('remove_from_cart', {
                        params: Utils.copyProperties(item['ga'], {})
                    });

                }

            }

        };

    }(options);

    window.pys = window.pys || {};
    window.pys.Facebook = Facebook;
    window.pys.Analytics = Analytics;
    window.pys.Utils = Utils;

    $(document).ready(function () {

        var Pinterest = Utils.setupPinterestObject();

        Utils.setupGdprCallbacks();

        // setup WooCommerce events
        if (options.woo.enabled) {

            // WooCommerce AddToCart
            if (options.woo.addToCartOnButtonEnabled) {

                // Loop, any kind of "simple" product, except external
                $('.add_to_cart_button:not(.product_type_variable)').click(function (e) {

                    var product_id = $(this).data('product_id');

                    if (typeof product_id !== 'undefined') {
                        Facebook.onWooAddToCartOnButtonEvent(product_id);
                        Analytics.onWooAddToCartOnButtonEvent(product_id);
                        Pinterest.onWooAddToCartOnButtonEvent(product_id);
                    }

                });

                // Single Product
                $('.single_add_to_cart_button').click(function (e) {

                    var $button = $(this);

                    if ($button.hasClass('disabled')) {
                        return;
                    }

                    var $form = $button.closest('form');

                    if ($form.length === 0) {
                        return;
                    }

                    var is_variable = $form.hasClass('variations_form');

                    var product_id;
                    var qty;

                    if (is_variable) {
                        product_id = parseInt($form.find('*[name="add-to-cart"]').val());
                        qty = parseInt($form.find('input[name="quantity"]').val());
                    } else {
                        product_id = parseInt($form.find('*[name="add-to-cart"]').val());
                        qty = parseInt($form.find('input[name="quantity"]').val());
                    }

                    Facebook.onWooAddToCartOnSingleEvent(product_id, qty, is_variable, $form);
                    Analytics.onWooAddToCartOnSingleEvent(product_id, qty, is_variable, $form);
                    Pinterest.onWooAddToCartOnSingleEvent(product_id, qty, is_variable, false, $form);

                });

            }

            // WooCommerce RemoveFromCart
            if (options.woo.removeFromCartEnabled) {

                $('body').on('click', options.woo.removeFromCartSelector, function (e) {

                    var $a = $(e.currentTarget),
                        href = $a.attr('href');

                    // extract cart item hash from remove button URL
                    var regex = new RegExp("[\\?&]remove_item=([^&#]*)"),
                        results = regex.exec(href);

                    if (results !== null) {

                        var item_hash = results[1];
                        window.pysWooRemoveFromCartData = window.pysWooRemoveFromCartData || [];

                        if (window.pysWooRemoveFromCartData.hasOwnProperty(item_hash)) {
                            Facebook.onWooRemoveFromCartEvent(item_hash);
                            Analytics.onWooRemoveFromCartEvent(item_hash);
                            Pinterest.onWooRemoveFromCartEvent(item_hash);
                        }

                    }

                });

            }

        }

        // setup EDD events
        if (options.edd.enabled) {

            // EDD AddToCart
            if (options.edd.addToCartOnButtonEnabled) {

                $('form.edd_download_purchase_form .edd-add-to-cart').click(function (e) {

                    var $button = $(this);
                    var $form = $button.closest('form');
                    var variable_price = $button.data('variablePrice'); // yes/no
                    var price_mode = $button.data('priceMode'); // single/multi
                    var ids = [];
                    var quantities = [];
                    var qty;
                    var id;

                    if (variable_price === 'yes' && price_mode === 'multi') {

                        id = $form.find('input[name="download_id"]').val();

                        // get selected variants
                        $.each($form.find('input[name="edd_options[price_id][]"]:checked'), function (i, el) {
                            ids.push(id + '_' + $(el).val());
                        });

                        // get qty for selected variants
                        $.each(ids, function (i, variant_id) {

                            var variant_index = variant_id.split('_', 2);
                            qty = $form.find('input[name="edd_download_quantity_' + variant_index[1] + '"]').val();

                            if (typeof qty !== 'undefined') {
                                quantities.push(qty);
                            } else {
                                quantities.push(1);
                            }

                        });

                    } else if (variable_price === 'yes' && price_mode === 'single') {

                        id = $form.find('input[name="download_id"]').val();
                        ids.push(id + '_' + $form.find('input[name="edd_options[price_id][]"]:checked').val());

                        qty = $form.find('input[name="edd_download_quantity"]').val();

                        if (typeof qty !== 'undefined') {
                            quantities.push(qty);
                        } else {
                            quantities.push(1);
                        }

                    } else {

                        ids.push($button.data('downloadId'));

                        qty = $form.find('input[name="edd_download_quantity"]').val();

                        if (typeof qty !== 'undefined') {
                            quantities.push(qty);
                        } else {
                            quantities.push(1);
                        }


                    }

                    // fire event for each download/variant
                    $.each(ids, function (i, download_id) {

                        var q = parseInt(quantities[i]);
                        var variant_index = download_id.toString().split('_', 2);
                        var price_index;

                        if (variant_index.length === 2) {
                            download_id = variant_index[0];
                            price_index = variant_index[1];
                        }

                        Facebook.onEddAddToCartOnButtonEvent(download_id, price_index, q);
                        Analytics.onEddAddToCartOnButtonEvent(download_id, price_index, q);
                        Pinterest.onEddAddToCartOnButtonEvent(download_id, price_index, q);

                    });

                });

            }

            // EDD RemoveFromCart
            if (options.edd.removeFromCartEnabled) {

                $('form#edd_checkout_cart_form .edd_cart_remove_item_btn').click(function (e) {

                    var href = $(this).attr('href');
                    var key = href.substring(href.indexOf('=') + 1).charAt(0);

                    window.pysEddRemoveFromCartData = window.pysEddRemoveFromCartData || [];

                    if (window.pysEddRemoveFromCartData[key]) {

                        var item = window.pysEddRemoveFromCartData[key];

                        Facebook.onEddRemoveFromCartEvent(item);
                        Analytics.onEddRemoveFromCartEvent(item);
                        Pinterest.onEddRemoveFromCartEvent(item);

                    }

                });

            }

        }

        // setup Comment Event
        if (options.commentEventEnabled) {

            $('form.comment-form').submit(function () {

                Facebook.onCommentEvent();
                Analytics.onCommentEvent();
                Pinterest.onCommentEvent();

            });

        }

        // setup DownloadDocs event
        if (options.downloadEventEnabled && options.downloadExtensions.length > 0) {

            $('body').click(function (event) {

                var el = event.srcElement || event.target;

                /* Loop up the DOM tree through parent elements if clicked element is not a link (eg: an image inside a link) */
                while (el && (typeof el.tagName === 'undefined' || el.tagName.toLowerCase() !== 'a' || !el.href)) {
                    el = el.parentNode;
                }

                if (el && el.href) {

                    var extension = Utils.getLinkExtension(el.href);
                    var track_download = false;

                    if (extension.length > 0) {

                        for (i = 0, len = options.downloadExtensions.length; i < len; ++i) {
                            if (options.downloadExtensions[i] === extension) {
                                track_download = true;
                                break;
                            }
                        }

                    }

                    if (track_download) {

                        var params = {
                            download_url: el.href,
                            download_type: extension,
                            download_name: Utils.getLinkFilename(el.href)
                        };

                        Facebook.onDownloadEvent(params);
                        Analytics.onDownloadEvent(params);
                        Pinterest.onDownloadEvent(params);

                    }

                }

            });

        }

        // setup Form Event
        if (options.formEventEnabled) {

            $(document).onFirst('submit', 'form', function () {

                var $form = $(this);

                // exclude WP forms
                if ($form.hasClass('comment-form') || $form.hasClass('search-form') || $form.attr('id') === 'adminbarsearch') {
                    return;
                }

                // exclude Woo forms
                if ($form.hasClass('woocommerce-product-search') || $form.hasClass('cart') || $form.hasClass('woocommerce-cart-form')
                    || $form.hasClass('woocommerce-shipping-calculator') || $form.hasClass('checkout') || $form.hasClass('checkout_coupon')) {
                    return;
                }

                // exclude EDD forms
                if ($form.hasClass('edd_form') || $form.hasClass('edd_download_purchase_form')) {
                    return;
                }

                var params = {
                    form_id: $form.attr('id'),
                    form_class: $form.attr('class')
                };

                Facebook.onFormEvent(params);
                Analytics.onFormEvent(params);
                Pinterest.onFormEvent(params);

            });

            // Ninja Forms
            $(document).onFirst('nfFormSubmitResponse', function (e, data) {

                var params = {
                    form_id: data.response.data.form_id,
                    form_title: data.response.data.settings.title
                };

                Facebook.onFormEvent(params);
                Analytics.onFormEvent(params);
                Pinterest.onFormEvent(params);

            });

        }

        // load pixel APIs
        Utils.loadPixels();

    });

}(jQuery, pysOptions);