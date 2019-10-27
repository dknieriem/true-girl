<?php

class TFNumbersSections {

  function __construct(){
    $this->init();
  }
    /**
    * add thanks and addons metaboxes
    *
    * @since    1.0.0
    */
    function init() {
      add_meta_box('tf_tnk', sprintf( '<b style="color:indianred">%s</b>', esc_html__( 'Thank You For Using TF Numbers Plugin', 'tf_numbers' ) ), array( $this, 'tf_ops_thanks_meta' ), 'tf_stats', 'normal', 'low');
      add_meta_box('tf_ad', '<b style="color:green">Info</b>', array( $this, 'tf_advanced' ), 'tf_stats', 'side', 'core');
    }

   /**
   * Addons box in sidebar
   *
   * @since    1.2.0
   */
   function tf_advanced() {
        $ul  = sprintf('<i style="display:block;color: brown">%s</i>', esc_html__( 'Make your numbers more beautiful', 'tf_numbers' ) );
        $ul .= '<a target="_blank" href="http://themeflection.com/plugins/wordpress/tf-numbers/demo2.html">' . esc_html__( 'Demo With Addons', 'tf_numbers' ) . '</a>';
        $ul .= '<ul id="tf-links">';
        $ul .= '<li><a href="http://themeflection.com/contact/">' .'<i class="dashicons dashicons-sos"></i>'. esc_html__('Support (email me)', 'tf_numbers') . '</a></li>';
        $ul .= '<li><a href="http://themeflection.com/docs/tf-numbers/">' .'<i class="dashicons dashicons-book"></i>'. esc_html__('Documentation', 'tf_numbers') . '</a></li>';
        $ul .= '<li><a href="http://themeflection.com/docs/tf-numbers/index.html#video">' .'<i class="dashicons dashicons-video-alt3"></i>'. __('Video Tutorials') . '</a></li>';
        $ul .= '<li><a href="admin.php?edit.php?post_type=tf_stats&page=tf-addons">' .'<i class="dashicons dashicons-archive"></i>'. __('Addons', 'tf-numbers') . '</a></li>';
        $ul .= '</ul>';

        $body = '<div style="position: relative;padding: 15px; border: 1px solid #333;vertical-align: top;padding: 15px;background: #eee; height:45px">';
        $body .= '<h4 style="font-size:1.1em;color:#A14400;margin-top: 0">Rate/Review TF Numbers <i style="color: #777; display: inline-block; vertical-align: middle;" class="fa fa-wordpress fa-2x"></i></h4>';
        $body .= '<a style="color: #777; position: absolute; top: 0; left: 0; width: 100%; height: 100%" href="https://wordpress.org/support/view/plugin-reviews/tf-numbers-number-counter-animaton" target="_BLANK"></a>';
        $body .= '<div style="position:absolute;bottom: 5px"><i class="fa fa-star" style="color:gold"></i><i class="fa fa-star" style="color:gold"></i><i class="fa fa-star" style="color:gold"></i><i class="fa fa-star" style="color:gold"></i><i class="fa fa-star" style="color:gold"></i></div>';
        $body .= '</div>';

        $html = $ul . $body;

        echo $html;
   }

   /**
   * custom metaboxes callback
   *
   * @since    1.1.0
   */
  function tf_ops_thanks_meta()
  {
      $body = '<h3 style="font-style: italic">Check some of the addons to supercahrge your numbers!</h3>';
      $body .= '<div class="addons">';

      $body .= '<div class="one-fourth">';
      $body .= '<img src="'.TF_NUMBERS_DIR . 'assets/images/controller.jpg'.'"/>';
      $body .= '<p class="desc"><strong class="h4">Controller Addon</strong> gives you more controlls over TF Numbers plugin. It will alow you to change numbers counting speed, unlock 4 more layouts and let you include "," comma separator for better looking numbers (like 10,000, 7,800 etc.). It also gives you an option to start counting immediatelly after page is loaded. Also get Section Padding And Margin Options and choose to show, or hide Section Title. <a target="_blank" href="http://themeflection.com/plug/controller-addon/" class="learn-more">Get It</a></p>';
      $body .= '</div>';

      $body .= '<div class="one-fourth">';
      $body .= '<img src="'.TF_NUMBERS_DIR . 'assets/images/iconizer.jpg'.'"/>';
      $body .= '<p class="desc"><strong class="h4">Iconizer Addon</strong> - It makes icons more powerfull. <strong>Iconizer</strong> will let you add your own image icons to the TF Numebrs plugin icons panel. You will be able to add your icons directly from icons panel. It also includes 2 more options your custom image icons width and height, and it unlocks search field in the icons panel so you can search through your icons and built-in font-awesome icons.<a target="_blank" href="http://themeflection.com/plug/iconizer-addon/" class="learn-more">Get It</a></p>';
      $body .= '</div>';

      $body .= '<div class="one-fourth">';
      $body .= '<img src="'.TF_NUMBERS_DIR . 'assets/images/woo-stats.jpg'.'"/>';
      $body .= '<p class="desc"><strong class="h4">Woo Statistics Addon</strong> allows showing of different WooCommerce statistics. You can showcase total number of producst, sales, customers, number of categories, and number of producst per single category. All data is automatically pulled from WooCommerce. <a target="_blank" href="http://themeflection.com/plug/tf-numbers-woo-statistics-addon/" class="learn-more">Get It</a></p>';
      $body .= '</div>';

      $body .= '<div class="one-fourth">';
      $body .= '<img src="'.TF_NUMBERS_DIR . 'assets/images/incrementer.jpg'.'"/>';
      $body .= '<p class="desc"><strong class="h4">Auto Incrementer Addon</strong> gives you the option to choose the starting position of the counter. So your number can start from 100, or 1200, etc. instead from 0. You can also have the auto incrementing option so your numbers can be increment daily by some value (eq 15).<a target="_blank" href="http://themeflection.com/plug/tf-numbers-auto-increment-addon/" class="learn-more">Get It</a></p>';
      $body .= '</div>';

      $body .= '<div class="one-fourth">';
      $body .= '<img src="'.TF_NUMBERS_DIR . 'assets/images/currencies.jpg'.'"/>';
      $body .= '<p class="desc"><strong class="h4">Currencies Addon</strong> allows showing of currencies in 4 ways: before number, after number, in form of superscipt, or subscript. You can choose between 16 currencies. Full list of available currencies can be found on the addon\'s page.<a target="_blank" href="http://themeflection.com/plug/currencies-addon/" class="learn-more">Get It</a></p>';
      $body .= '</div>';

      $body .= '<div class="one-fourth">';
      $body .= '<img src="'.TF_NUMBERS_DIR . 'assets/images/parallax.jpg'.'"/>';
      $body .= '<p class="desc"><strong class="h4">Parallax Addon</strong> - Add Parallax Effect To Backgound Image. <strong>Parallax</strong> will let you add the parallax effect to the background image of your numbers section. You will have new option to enable or disable parallax image effect, and a new option to change the image\'s transparent overlay background color.<a target="_blank" href="http://themeflection.com/plug/parallax-addon/" class="learn-more">Get It</a></p>';
      $body .= '</div>';

      $body .= '<div class="one-fourth">';
      $body .= '<img src="'.TF_NUMBERS_DIR . 'assets/images/animator.jpg'.'"/>';
      $body .= '<p class="desc"><strong class="h4">Animator Addon</strong> will extend the TF Numbers by allowing you to add powerful animations to the static numbers section. Add more dynamic to your website by choosing from 14 available animations. You can change animation duration and delay for each number. You can also apply animation to the numbers section title and entire section container itself.<a target="_blank" href="http://themeflection.com/plug/animator-addon/" class="learn-more">Get It</a></p>';
      $body .= '</div>';

      $body .= '<div class="one-fourth">';
      $body .= '<img src="'.TF_NUMBERS_DIR . 'assets/images/bundle.jpg'.'"/>';
      $body .= '<p class="desc"><strong style="font-size: 1.18em" class="h4">Addons Bundle</strong> <span style="font-size: 1.3em; margin: 0"><span style="color: crimson;">Save 60%</span> by getting all 7 addons for small price.<a target="_blank" href="http://themeflection.com/plug/tf-numbers-addons-bundle/" class="learn-more">Get It</a></span></p>';
      $body .= '</div>';

      $body .= '</div>';

      print $body;
  }
}
