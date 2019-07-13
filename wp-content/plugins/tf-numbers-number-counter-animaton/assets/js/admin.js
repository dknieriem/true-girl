jQuery(document).ready(function($){
  // arrays
    var fontAwesome = {}
    var btn = '<span class="ic-prev"><i></i></span><a class="icon-add">Add/Change Icon</a>';
    fontAwesome['New In 4.7'] = ['fa-address-book', 'fa-address-book-o', 'fa-address-card', 'fa-address-card-o', 'fa-bandcamp', 'fa-bath', 'fa-drivers-license', 'fa-drivers-license-o', 'fa-eercast', 'fa-envelope-open', 'fa-envelope-open-o', 'fa-etsy', 'fa-free-code-camp', 'fa-grav', 'fa-handshake-o', 'fa-id-badge', 'fa-id-card', 'fa-id-card-o', 'fa-imdb', 'fa-linode', 'fa-meetup', 'fa-microchip', 'fa-podcast', 'fa-quora', 'fa-ravelry', 'fa-s15', 'fa-shower','fa-snowflake-o', 'fa-superpowers', 'fa-telegram', 'fa-thermometer', 'fa-thermometer-0', 'fa-thermometer-1', 'fa-thermometer-2', 'fa-thermometer-3', 'fa-thermometer-4', 'fa-thermometer-empty', 'fa-thermometer-full', 'fa-thermometer-half', 'fa-thermometer-quarter', 'fa-thermometer-three-quarters', 'fa-times-rectangle', 'fa-times-rectangle-o', 'fa-user-circle', 'fa-user-circle-o', 'fa-user-o', 'fa-vcard', 'fa-vcard-o', 'fa-window-close', 'fa-window-close-o', 'fa-window-maximize', 'fa-window-minimize', 'fa-window-restore', 'fa-wpexplorer'];
    fontAwesome['New In 4.6'] = ['fa-american-sign-language-interpreting','fa-assistive-listening-systems','fa-audio-description','fa-blind','fa-braille','fa-deaf','fa-envira','fa-first-order','fa-font-awesome','fa-gitlab','fa-glide','fa-glide-g','fa-google-plus-official','fa-instagram','fa-low-vision','fa-pied-piper','fa-question-circle-o','fa-sign-language','fa-snapchat','fa-snapchat-ghost','fa-snapchat-square','fa-themeisle','fa-universal-access','fa-viadeo','fa-viadeo-square','fa-volume-control-phone','fa-wheelchair-alt','fa-wpbeginner','fa-wpforms','fa-yoast'];
    fontAwesome['New In 4.5'] = [ 'fa-bluetooth', 'fa-bluetooth-b', 'fa-codiepie', 'fa-credit-card-alt', 'fa-edge', 'fa-fort-awesome', 'fa-hashtag', 'fa-mixcloud', 'fa-modx', 'fa-pause-circle', 'fa-pause-circle-o', 'fa-percent', 'fa-product-hunt', 'fa-reddit-alien', 'fa-scribd', 'fa-shopping-bag', 'fa-shopping-basket', 'fa-stop-circle', 'fa-stop-circle-o', 'fa-usb'];
    fontAwesome['New In 4.4'] = ['fa-500px', 'fa-amazon', 'fa-balance-scale', 'fa-battery-0', 'fa-battery-1', 'fa-battery-2', 'fa-battery-3', 'fa-battery-4', 'fa-battery-empty', 'fa-battery-full', 'fa-battery-half', 'fa-battery-quarter', 'fa-battery-three-quarters', 'fa-black-tie', 'fa-calendar-check-o', 'fa-calendar-minus-o', 'fa-calendar-plus-o', 'fa-calendar-times-o', 'fa-cc-diners-club', 'fa-cc-jcb', 'fa-chrome', 'fa-clone', 'fa-commenting', 'fa-commenting-o', 'fa-contao', 'fa-creative-commons', 'fa-expeditedssl', 'fa-firefox', 'fa-fonticons', 'fa-genderless', 'fa-get-pocket', 'fa-gg', 'fa-gg-circle', 'fa-hand-grab-o', 'fa-hand-lizard-o', 'fa-hand-paper-o', 'fa-hand-peace-o', 'fa-hand-pointer-o', 'fa-hand-rock-o', 'fa-hand-scissors-o', 'fa-hand-spock-o', 'fa-hand-stop-o', 'fa-hourglass', 'fa-hourglass-1', 'fa-hourglass-2', 'fa-hourglass-3', 'fa-hourglass-end', 'fa-hourglass-half', 'fa-hourglass-o', 'fa-hourglass-start', 'fa-houzz', 'fa-i-cursor', 'fa-industry', 'fa-internet-explorer', 'fa-map', 'fa-map-o', 'fa-map-pin', 'fa-map-signs', 'fa-mouse-pointer', 'fa-object-group', 'fa-object-ungroup', 'fa-odnoklassniki', 'fa-odnoklassniki-square', 'fa-opencart', 'fa-opera', 'fa-optin-monster', 'fa-registered', 'fa-safari', 'fa-sticky-note', 'fa-sticky-note-o', 'fa-television', 'fa-trademark', 'fa-tripadvisor', 'fa-tv', 'fa-vimeo', 'fa-wikipedia-w', 'fa-y-combinator', 'fa-yc'];
    fontAwesome['Web Application Icons'] = ['fa-500px', 'fa-amazon', 'fa-balance-scale', 'fa-battery-0', 'fa-adjust', 'fa-anchor', 'fa-archive', 'fa-area-chart', 'fa-arrows', 'fa-arrows-h', 'fa-arrows-v', 'fa-asterisk', 'fa-at', 'fa-automobile', 'fa-balance-scale', 'fa-ban', 'fa-bank', 'fa-bar-chart', 'fa-bar-chart-o', 'fa-barcode', 'fa-bars', 'fa-battery-0', 'fa-battery-1', 'fa-battery-2', 'fa-battery-3', 'fa-battery-4', 'fa-battery-empty', 'fa-battery-full', 'fa-battery-half', 'fa-battery-quarter', 'fa-battery-three-quarters', 'fa-bed', 'fa-beer', 'fa-bell', 'fa-bell-o', 'fa-bell-slash', 'fa-bell-slash-o', 'fa-bicycle', 'fa-binoculars', 'fa-birthday-cake', 'fa-bolt', 'fa-bomb', 'fa-book', 'fa-bookmark', 'fa-bookmark-o', 'fa-briefcase', 'fa-bug', 'fa-building', 'fa-building-o', 'fa-bullhorn', 'fa-bullseye', 'fa-bus', 'fa-cab', 'fa-calculator', 'fa-calendar', 'fa-calendar-check-o', 'fa-calendar-minus-o', 'fa-calendar-o', 'fa-calendar-plus-o', 'fa-calendar-times-o', 'fa-camera', 'fa-camera-retro', 'fa-car', 'fa-caret-square-o-down', 'fa-caret-square-o-left', 'fa-caret-square-o-right', 'fa-caret-square-o-up', 'fa-cart-arrow-down', 'fa-cart-plus', 'fa-cc', 'fa-certificate', 'fa-check', 'fa-check-circle', 'fa-check-circle-o', 'fa-check-square', 'fa-check-square-o', 'fa-child', 'fa-circle', 'fa-circle-o', 'fa-circle-o-notch', 'fa-circle-thin', 'fa-clock-o', 'fa-clone', 'fa-close', 'fa-cloud', 'fa-cloud-download', 'fa-cloud-upload', 'fa-code', 'fa-code-fork', 'fa-coffee', 'fa-cog', 'fa-cogs', 'fa-comment', 'fa-comment-o', 'fa-commenting', 'fa-commenting-o', 'fa-comments', 'fa-comments-o', 'fa-compass', 'fa-copyright', 'fa-creative-commons', 'fa-credit-card', 'fa-crop', 'fa-crosshairs', 'fa-cube', 'fa-cubes', 'fa-cutlery', 'fa-dashboard', 'fa-database', 'fa-desktop', 'fa-diamond', 'fa-dot-circle-o', 'fa-download', 'fa-edit', 'fa-ellipsis-h', 'fa-ellipsis-v', 'fa-envelope', 'fa-envelope-o', 'fa-envelope-square', 'fa-eraser', 'fa-exchange', 'fa-exclamation', 'fa-exclamation-circle', 'fa-exclamation-triangle', 'fa-external-link', 'fa-external-link-square', 'fa-eye', 'fa-eye-slash', 'fa-eyedropper', 'fa-fax', 'fa-feed', 'fa-female', 'fa-fighter-jet', 'fa-file-archive-o', 'fa-file-audio-o', 'fa-file-code-o', 'fa-file-excel-o', 'fa-file-image-o', 'fa-file-movie-o', 'fa-file-pdf-o', 'fa-file-photo-o', 'fa-file-picture-o', 'fa-file-powerpoint-o', 'fa-file-sound-o', 'fa-file-video-o', 'fa-file-word-o', 'fa-file-zip-o', 'fa-film', 'fa-filter', 'fa-fire', 'fa-fire-extinguisher', 'fa-flag', 'fa-flag-checkered', 'fa-flag-o', 'fa-flash', 'fa-flask', 'fa-folder', 'fa-folder-o', 'fa-folder-open', 'fa-folder-open-o', 'fa-frown-o', 'fa-futbol-o', 'fa-gamepad', 'fa-gavel', 'fa-gear', 'fa-gears', 'fa-gift', 'fa-glass', 'fa-globe', 'fa-graduation-cap', 'fa-group', 'fa-hand-grab-o', 'fa-hand-lizard-o', 'fa-hand-paper-o', 'fa-hand-peace-o', 'fa-hand-pointer-o', 'fa-hand-rock-o', 'fa-hand-scissors-o', 'fa-hand-spock-o', 'fa-hand-stop-o', 'fa-hdd-o', 'fa-headphones', 'fa-heart', 'fa-heart-o', 'fa-heartbeat', 'fa-history', 'fa-home', 'fa-hotel', 'fa-hourglass', 'fa-hourglass-1', 'fa-hourglass-2', 'fa-hourglass-3', 'fa-hourglass-end', 'fa-hourglass-half', 'fa-hourglass-o', 'fa-hourglass-start', 'fa-i-cursor', 'fa-image', 'fa-inbox', 'fa-industry', 'fa-info', 'fa-info-circle', 'fa-institution', 'fa-key', 'fa-keyboard-o', 'fa-language', 'fa-laptop', 'fa-leaf', 'fa-legal', 'fa-lemon-o', 'fa-level-down', 'fa-level-up', 'fa-life-bouy', 'fa-life-buoy', 'fa-life-ring', 'fa-life-saver', 'fa-lightbulb-o', 'fa-line-chart', 'fa-location-arrow', 'fa-lock', 'fa-magic', 'fa-magnet', 'fa-mail-forward', 'fa-mail-reply', 'fa-mail-reply-all', 'fa-male', 'fa-map', 'fa-map-marker', 'fa-map-o', 'fa-map-pin', 'fa-map-signs', 'fa-meh-o', 'fa-microphone', 'fa-microphone-slash', 'fa-minus', 'fa-minus-circle', 'fa-minus-square', 'fa-minus-square-o', 'fa-mobile', 'fa-mobile-phone', 'fa-money', 'fa-moon-o', 'fa-mortar-board', 'fa-motorcycle', 'fa-mouse-pointer', 'fa-music', 'fa-navicon', 'fa-newspaper-o', 'fa-object-group', 'fa-object-ungroup', 'fa-paint-brush', 'fa-paper-plane', 'fa-paper-plane-o', 'fa-paw', 'fa-pencil', 'fa-pencil-square', 'fa-pencil-square-o', 'fa-phone', 'fa-phone-square', 'fa-photo', 'fa-picture-o', 'fa-pie-chart', 'fa-plane', 'fa-plug', 'fa-plus', 'fa-plus-circle', 'fa-plus-square', 'fa-plus-square-o', 'fa-power-off', 'fa-print', 'fa-puzzle-piece', 'fa-qrcode', 'fa-question', 'fa-question-circle', 'fa-quote-left', 'fa-quote-right', 'fa-random', 'fa-recycle', 'fa-refresh', 'fa-registered', 'fa-remove', 'fa-reorder', 'fa-reply', 'fa-reply-all', 'fa-retweet', 'fa-road', 'fa-rocket', 'fa-rss', 'fa-rss-square', 'fa-search', 'fa-search-minus', 'fa-search-plus', 'fa-send', 'fa-send-o', 'fa-server', 'fa-share', 'fa-share-alt', 'fa-share-alt-square', 'fa-share-square', 'fa-share-square-o', 'fa-shield', 'fa-ship', 'fa-shopping-cart', 'fa-sign-in', 'fa-sign-out', 'fa-signal', 'fa-sitemap', 'fa-sliders', 'fa-smile-o', 'fa-soccer-ball-o', 'fa-sort', 'fa-sort-alpha-asc', 'fa-sort-alpha-desc', 'fa-sort-amount-asc', 'fa-sort-amount-desc', 'fa-sort-asc', 'fa-sort-desc', 'fa-sort-down', 'fa-sort-numeric-asc', 'fa-sort-numeric-desc', 'fa-sort-up', 'fa-space-shuttle', 'fa-spinner', 'fa-spoon', 'fa-square', 'fa-square-o', 'fa-star', 'fa-star-half', 'fa-star-half-empty', 'fa-star-half-full', 'fa-star-half-o', 'fa-star-o', 'fa-sticky-note', 'fa-sticky-note-o', 'fa-street-view', 'fa-suitcase', 'fa-sun-o', 'fa-support', 'fa-tablet', 'fa-tachometer', 'fa-tag', 'fa-tags', 'fa-tasks', 'fa-taxi', 'fa-television', 'fa-terminal', 'fa-thumb-tack', 'fa-thumbs-down', 'fa-thumbs-o-down', 'fa-thumbs-o-up', 'fa-thumbs-up', 'fa-ticket', 'fa-times', 'fa-times-circle', 'fa-times-circle-o', 'fa-tint', 'fa-toggle-down', 'fa-toggle-left', 'fa-toggle-off', 'fa-toggle-on', 'fa-toggle-right', 'fa-toggle-up', 'fa-trademark', 'fa-trash', 'fa-trash-o', 'fa-tree', 'fa-trophy', 'fa-truck', 'fa-tty', 'fa-tv', 'fa-umbrella', 'fa-university', 'fa-unlock', 'fa-unlock-alt', 'fa-unsorted', 'fa-upload', 'fa-user', 'fa-user-plus', 'fa-user-secret', 'fa-user-times', 'fa-users', 'fa-video-camera', 'fa-volume-down', 'fa-volume-off', 'fa-volume-up', 'fa-warning', 'fa-wheelchair', 'fa-wifi', 'fa-wrench', 'fa-battery-1', 'fa-battery-2', 'fa-battery-3', 'fa-battery-4', 'fa-battery-empty', 'fa-battery-full', 'fa-battery-half', 'fa-battery-quarter', 'fa-battery-three-quarters', 'fa-black-tie', 'fa-calendar-check-o', 'fa-calendar-minus-o', 'fa-calendar-plus-o', 'fa-calendar-times-o', 'fa-cc-diners-club', 'fa-cc-jcb', 'fa-chrome', 'fa-clone', 'fa-commenting', 'fa-commenting-o', 'fa-contao', 'fa-creative-commons', 'fa-expeditedssl', 'fa-firefox', 'fa-fonticons', 'fa-genderless', 'fa-get-pocket', 'fa-gg', 'fa-gg-circle', 'fa-hand-grab-o', 'fa-hand-lizard-o', 'fa-hand-paper-o', 'fa-hand-peace-o', 'fa-hand-pointer-o', 'fa-hand-rock-o', 'fa-hand-scissors-o', 'fa-hand-spock-o', 'fa-hand-stop-o', 'fa-hourglass', 'fa-hourglass-1', 'fa-hourglass-2', 'fa-hourglass-3', 'fa-hourglass-end', 'fa-hourglass-half', 'fa-hourglass-o', 'fa-hourglass-start', 'fa-houzz', 'fa-i-cursor', 'fa-industry', 'fa-internet-explorer', 'fa-map', 'fa-map-o', 'fa-map-pin', 'fa-map-signs', 'fa-mouse-pointer', 'fa-object-group', 'fa-object-ungroup', 'fa-odnoklassniki', 'fa-odnoklassniki-square', 'fa-opencart', 'fa-opera', 'fa-optin-monster', 'fa-registered', 'fa-safari', 'fa-sticky-note', 'fa-sticky-note-o', 'fa-television', 'fa-trademark', 'fa-tripadvisor', 'fa-tv', 'fa-vimeo', 'fa-wikipedia-w', 'fa-y-combinator', 'fa-yc'];
    fontAwesome['Hand Icons'] = ['fa-hand-grab-o', 'fa-hand-lizard-o', 'fa-hand-o-down', 'fa-hand-o-left', 'fa-hand-o-right', 'fa-hand-o-up', 'fa-hand-paper-o', 'fa-hand-peace-o', 'fa-hand-pointer-o', 'fa-hand-rock-o', 'fa-hand-scissors-o', 'fa-hand-spock-o', 'fa-hand-stop-o', 'fa-thumbs-down', 'fa-thumbs-o-down', 'fa-thumbs-o-up', 'fa-thumbs-up']
    fontAwesome['Transportation Icons'] = ['fa-ambulance', 'fa-automobile', 'fa-bicycle', 'fa-bus', 'fa-cab', 'fa-car', 'fa-fighter-jet', 'fa-motorcycle', 'fa-plane', 'fa-rocket', 'fa-ship', 'fa-space-shuttle', 'fa-subway', 'fa-taxi', 'fa-train', 'fa-truck', 'fa-wheelchair'];
    fontAwesome['Gender Icons'] = ['fa-genderless', 'fa-intersex', 'fa-mars', 'fa-mars-double', 'fa-mars-stroke', 'fa-mars-stroke-h', 'fa-mars-stroke-v', 'fa-mercury', 'fa-neuter', 'fa-transgender', 'fa-transgender-alt', 'fa-venus', 'fa-venus-double', 'fa-venus-mars'];
    fontAwesome['File Type Icons'] = ['fa-file', 'fa-file-archive-o', 'fa-file-audio-o', 'fa-file-code-o', 'fa-file-excel-o', 'fa-file-image-o', 'fa-file-movie-o', 'fa-file-o', 'fa-file-pdf-o', 'fa-file-photo-o', 'fa-file-picture-o', 'fa-file-powerpoint-o', 'fa-file-sound-o', 'fa-file-text', 'fa-file-text-o', 'fa-file-video-o', 'fa-file-word-o', 'fa-file-zip-o'];
    fontAwesome['Payment Icons'] = ['fa-cc-amex', 'fa-cc-diners-club', 'fa-cc-discover', 'fa-cc-jcb', 'fa-cc-mastercard', 'fa-cc-paypal', 'fa-cc-stripe', 'fa-cc-visa', 'fa-credit-card', 'fa-google-wallet', 'fa-paypal'];
    fontAwesome['fa-Chart Icons'] = ['fa-area-chart', 'fa-bar-chart', 'fa-bar-chart-o', 'fa-line-chart', 'fa-pie-chart'];
    fontAwesome['Currency Icons'] = ['fa-bitcoin', 'fa-btc', 'fa-cny', 'fa-dollar', 'fa-eur', 'fa-euro', 'fa-gbp', 'fa-gg', 'fa-gg-circle', 'fa-ils', 'fa-inr', 'fa-jpy', 'fa-krw', 'fa-money', 'fa-rmb', 'fa-rouble', 'fa-rub', 'fa-ruble', 'fa-rupee', 'fa-shekel', 'fa-sheqel', 'fa-try', 'fa-turkish-lira', 'fa-usd', 'fa-won', 'fa-yen'];
    fontAwesome['Text Editor Icons'] = ['fa-align-center', 'fa-align-justify', 'fa-align-left', 'fa-align-right', 'fa-bold', 'fa-chain', 'fa-chain-broken', 'fa-clipboard', 'fa-columns', 'fa-copy', 'fa-cut', 'fa-dedent', 'fa-eraser', 'fa-file', 'fa-file-o', 'fa-file-text', 'fa-file-text-o', 'fa-files-o', 'fa-floppy-o', 'fa-font', 'fa-header', 'fa-indent', 'fa-italic', 'fa-link', 'fa-list', 'fa-list-alt', 'fa-list-ol', 'fa-list-ul', 'fa-outdent', 'fa-paperclip', 'fa-paragraph', 'fa-paste', 'fa-repeat', 'fa-rotate-left', 'fa-rotate-right', 'fa-save', 'fa-scissors', 'fa-strikethrough', 'fa-subscript', 'fa-superscript', 'fa-table', 'fa-text-height', 'fa-text-width', 'fa-th', 'fa-th-large', 'fa-th-list', 'fa-underline', 'fa-undo', 'fa-unlink'];
    fontAwesome['Directional Icons'] = ['fa-angle-double-down', 'fa-angle-double-left', 'fa-angle-double-right', 'fa-angle-double-up', 'fa-angle-down', 'fa-angle-left', 'fa-angle-right', 'fa-angle-up', 'fa-arrow-circle-down', 'fa-arrow-circle-left', 'fa-arrow-circle-o-down', 'fa-arrow-circle-o-left', 'fa-arrow-circle-o-right', 'fa-arrow-circle-o-up', 'fa-arrow-circle-right', 'fa-arrow-circle-up', 'fa-arrow-down', 'fa-arrow-left', 'fa-arrow-right', 'fa-arrow-up', 'fa-arrows', 'fa-arrows-alt', 'fa-arrows-h', 'fa-arrows-v', 'fa-caret-down', 'fa-caret-left', 'fa-caret-right', 'fa-caret-square-o-down', 'fa-caret-square-o-left', 'fa-caret-square-o-right', 'fa-caret-square-o-up', 'fa-caret-up', 'fa-chevron-circle-down', 'fa-chevron-circle-left', 'fa-chevron-circle-right', 'fa-chevron-circle-up', 'fa-chevron-down', 'fa-chevron-left', 'fa-chevron-right', 'fa-chevron-up', 'fa-exchange', 'fa-hand-o-down', 'fa-hand-o-left', 'fa-hand-o-right', 'fa-hand-o-up', 'fa-long-arrow-down', 'fa-long-arrow-left', 'fa-long-arrow-right', 'fa-long-arrow-up', 'fa-toggle-down', 'fa-toggle-left', 'fa-toggle-right', 'fa-toggle-up'];
    fontAwesome['Video Player Icons'] = ['fa-arrows-alt', 'fa-backward', 'fa-compress', 'fa-eject', 'fa-expand', 'fa-fast-backward', 'fa-fast-forward', 'fa-forward', 'fa-pause', 'fa-play', 'fa-play-circle', 'fa-play-circle-o', 'fa-random', 'fa-step-backward', 'fa-step-forward', 'fa-stop', 'fa-youtube-play']
    fontAwesome['Brand Icons'] = ['fa-500px', 'fa-adn', 'fa-amazon', 'fa-android', 'fa-angellist', 'fa-apple', 'fa-behance', 'fa-behance-square', 'fa-bitbucket', 'fa-bitbucket-square', 'fa-bitcoin', 'fa-black-tie', 'fa-btc', 'fa-buysellads', 'fa-cc-amex', 'fa-cc-diners-club', 'fa-cc-discover', 'fa-cc-jcb', 'fa-cc-mastercard', 'fa-cc-paypal', 'fa-cc-stripe', 'fa-cc-visa', 'fa-chrome', 'fa-codepen', 'fa-connectdevelop', 'fa-contao', 'fa-css3', 'fa-dashcube', 'fa-delicious', 'fa-deviantart', 'fa-digg', 'fa-dribbble', 'fa-dropbox', 'fa-drupal', 'fa-empire', 'fa-expeditedssl', 'fa-facebook', 'fa-facebook-f', 'fa-facebook-official', 'fa-facebook-square', 'fa-firefox', 'fa-flickr', 'fa-fonticons', 'fa-forumbee', 'fa-foursquare', 'fa-ge', 'fa-get-pocket', 'fa-gg', 'fa-gg-circle', 'fa-git', 'fa-git-square', 'fa-github', 'fa-github-alt', 'fa-github-square', 'fa-gittip', 'fa-google', 'fa-google-plus', 'fa-google-plus-square', 'fa-google-wallet', 'fa-gratipay', 'fa-hacker-news', 'fa-houzz', 'fa-html5', 'fa-instagram', 'fa-internet-explorer', 'fa-ioxhost', 'fa-joomla', 'fa-jsfiddle', 'fa-lastfm', 'fa-lastfm-square', 'fa-leanpub', 'fa-linkedin', 'fa-linkedin-square', 'fa-linux', 'fa-maxcdn', 'fa-meanpath', 'fa-medium', 'fa-odnoklassniki', 'fa-odnoklassniki-square', 'fa-opencart', 'fa-openid', 'fa-opera', 'fa-optin-monster', 'fa-pagelines', 'fa-paypal', 'fa-pied-piper', 'fa-pied-piper-alt', 'fa-pinterest', 'fa-pinterest-p', 'fa-pinterest-square', 'fa-qq', 'fa-ra', 'fa-rebel', 'fa-reddit', 'fa-reddit-square', 'fa-renren', 'fa-safari', 'fa-sellsy', 'fa-share-alt', 'fa-share-alt-square', 'fa-shirtsinbulk', 'fa-simplybuilt', 'fa-skyatlas', 'fa-skype', 'fa-slack', 'fa-slideshare', 'fa-soundcloud', 'fa-spotify', 'fa-stack-exchange', 'fa-stack-overflow', 'fa-steam', 'fa-steam-square', 'fa-stumbleupon', 'fa-stumbleupon-circle', 'fa-tencent-weibo', 'fa-trello', 'fa-tripadvisor', 'fa-tumblr', 'fa-tumblr-square', 'fa-twitch', 'fa-twitter', 'fa-twitter-square', 'fa-viacoin', 'fa-vimeo', 'fa-vimeo-square', 'fa-vine', 'fa-vk', 'fa-wechat', 'fa-weibo', 'fa-weixin', 'fa-whatsapp', 'fa-wikipedia-w', 'fa-windows', 'fa-wordpress', 'fa-xing', 'fa-xing-square', 'fa-y-combinator', 'fa-y-combinator-square', 'fa-yahoo', 'fa-yc', 'fa-yc-square', 'fa-yelp', 'fa-youtube', 'fa-youtube-play', 'fa-youtube-square'];
    fontAwesome['Medical Icons'] = ['fa-ambulance', 'fa-h-square', 'fa-heart', 'fa-heart-o', 'fa-heartbeat', 'fa-hospital-o', 'fa-medkit', 'fa-plus-square', 'fa-stethoscope', 'fa-user-md', 'fa-wheelchair'];

    if( $('.post-type-tf_stats').length ) {
        $('.tf_icon').find('input').parent().append(btn);
        $('.cmb2-id--tf-layout').find('li').each(function(){ 
           var $this = $(this);
           var value = $this.find('input').val(); 
           $this.find('label').remove();
           $this.find('input').wrap('<label class="tf-select" />');
           $('<img src="'+url.path+value+'.png" />').appendTo($this.find('.tf-select') );
        }); 

        $('.cmb2-id--tf-layout').on('click', '.tf-select', opSelect);
        $('#icons #icn-srch input').on('change keyup', searchIcons);
        checked();

        $('.postbox.cmb-row').each(function(){
           var $this = $(this);
           var val = $this.find('.tf_icon input').val();
           if( val.indexOf('.') != -1 ) {
               $this.find('.ic-prev').append('<img/>');
               $this.find('.ic-prev img').attr('src', val);
           } else {
              $this.find('.ic-prev i').attr('class', val);
           }
        });


        function searchIcons() {
          var val = $('#icn-srch input').val();
          var icons = $('#icons');
          var current = icons.find('div.active');
          current.find('i, h5, img').removeClass('nt-match match');
          current.find('i, h5, img').addClass('nt-match');
          current.find('i[class*="'+val+'"], img[class*="'+val+'"]').addClass('match');
          if( ! val ) current.find('i, h5').removeClass('nt-match match');
        }

        function opSelect() {
          var $this = $(this);
          $this.addClass('active').parent().siblings().find('.tf-select').removeClass('active');
          var id = $this.find('input').data('id');
          if( id ) {
             $(id).css('display', 'block');
             $('label[data-id="'+id+'-not"').css('display', 'none');
          }
        }

        function checked() {
          $('.cmb2-id--tf-layout .tf-select').each(function(){
            var $this = $(this);
            var id = $this.find('input').data('id');
            if( id ) $(id).css('display', 'none');
            if( $this.find('input').is(':checked') ) {
              $this.addClass('active').siblings().removeClass('active');
              if( id ) $(id).css('display', 'inline-block');
            }
          });
        }

        $.each(fontAwesome, function( key,value ){ 
          var title = '<h5>' + key + '</h5>';
          $('#icons div').find('div').eq(0).append(title);
          $.each(value, function( key2,value2 ) {
            var icon = '<i class="fa '+ value2 +'" title="'+value2+'"></i>';
            $('#icons div').find('div').eq(0).append(icon);
          });
        });

        $('#icons li').click(function(){
          var index = $(this).index();
          $(this).addClass('active').siblings().removeClass('active');
          $('#icons div').find('div.active').removeClass('active').fadeOut('340', function(){$(this).css('display', 'none')});
          $('#icons div').find('div').eq(index).addClass('active').fadeIn('340', function(){$(this).css('display', 'block')});
        });

        $('#wpbody').off().on('click', '.cmb-add-group-row button', function() {
          $('.postbox.cmb-row').last().find('.bmt').removeClass('bmt');
        });

        $('.cmb-add-row').off().on('click', 'button', function(){
            setTimeout(function(){
                $('.postbox.cmb-row').last().find('.ic-prev > i').attr('class', '');
            },150) 
        });

        $('#wpbody').off().on('click', '.icon-add, .remove' ,function(){
          var z = 0;
          $('.bmt').removeClass('bmt');
          $(this).parent().addClass('bmt');
          var idD = $('.bmt');
          if( $('bmt').length > 0 ) {
            $('bmt').removeClass('bmt');
            idD.addClass('bmt');
          }
          if( $(this).hasClass('fa-times') ) {
            $(idD).find('i').attr('class', '');
            $(idD).find('i').val('');
          } else {
            $('#icons,#icons-wrap').addClass('active');
            $('#icons').on('click', 'i, div img', function(){
              if( z === 0 ) { 
                z++
                if( !$(this).hasClass('ic-remove') ){
                  var value = $(this).attr('class'); 
                  if( $(this).prop('tagName') == 'I' ) {
                    idD.find('.ic-prev img').remove();
                    idD.find('.ic-prev i').attr('class', value);
                    idD.find('input').val(value);
                  } else {
                    if( idD.find('.ic-prev img').length <= 0 ) idD.find('.ic-prev').append('<img/>');
                    var value = $(this).attr('src');
                    idD.find('.ic-prev i').attr('class', '');
                    idD.find('.ic-prev img').attr('src', value);
                    idD.find('input').val(value);
                  }
                  $('.bmt').removeClass('bmt');
                  $('#icons,#icons-wrap').removeClass('active');
                } else {
                  $('#icons,#icons-wrap').removeClass('active');
                  $('#icons div.active').find('i, h5').removeClass('nt-match match');
                }
              }
            });
          }
          
        });
    }
  });