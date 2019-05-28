<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function adminGetPromoNoticesContent() {
    return [
        'woo' => [
          [
              'disabled' => false,
              'from'     => 0,
              'to'       => 2,
              'content'  => 'PixelYourSite has been updated and it does a lot of new stuff for you: <a href="https://www.pixelyoursite.com/pixelyoursite-free-plugin">find out more here!</a>'
          ],
        ],
        'edd' => [
          [
              'disabled' => false,
              'from'     => 0,
              'to'       => 2,
              'content'  => 'PixelYourSite has been updated and it does a lot of new stuff for you: <a href="https://www.pixelyoursite.com/pixelyoursite-free-plugin">find out more here!</a>'
          ],
        ],
        'no_woo_no_edd' => [
            [
                'disabled' => false,
                'from'     => 0,
                'to'       => 2,
                'content'  => 'PixelYourSite has been updated and it does a lot of new stuff for you: <a href="https://www.pixelyoursite.com/pixelyoursite-free-plugin">find out more here!</a>'
            ],
        ],
    ];
}
