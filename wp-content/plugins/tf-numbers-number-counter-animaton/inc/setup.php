<?php

if( !class_exists( 'TF_Numbers' ) )
{
  class TF_Numbers
  {
    static $version = TF_NUMBERS_VERSION;
    static $tf = TF_NUMBERS_STRING;

    protected static function hooks()
      {
         //enqueue front-end scripts and styles
         add_action( 'wp_enqueue_scripts', array( 'TF_Numbers', 'enqueue_scripts' ) );
         //enqueue back-end scripts and styles
         add_action( 'admin_enqueue_scripts', array( 'TF_Numbers', 'admin_enqueue_scripts' ) );
         add_action( 'init', array( 'TF_Numbers', 'tf_stats_init' ) );
         add_action( 'cmb2_init', array( 'TF_Numbers', 'numbers_metabox_init' ) );
         add_filter( "manage_edit-tf_stats_columns", array( 'TF_Numbers', "tf_number_post_columns" ) );
         add_action( "manage_tf_stats_posts_custom_column", array( 'TF_Numbers', "tf_number_custom_columns" ), 10, 2 );
         add_action( 'admin_head', array( 'TF_Numbers', 'collect_numbers' ) );
         //add_filter( 'post_row_actions', array( 'TF_Numbers', 'remove_view_link' ) ); Depricated
         add_action( 'add_meta_boxes', array( 'TF_Numbers', 'sections' ) );
         add_action( 'admin_menu', array( 'TF_Numbers', 'replace_submit_meta_box' ) );
         add_filter( 'mce_buttons', array( 'TF_Numbers', 'register_button' ) );
         add_filter( 'mce_external_plugins', array( 'TF_Numbers', 'add_button_js' ) );
         add_action( 'plugins_loaded', array( 'TF_Numbers', 'vc_support' ) );
         add_action( 'admin_footer', array( 'TF_Numbers', 'tf_icons_panel' ) );
      }

      /**
      * Enqueue scripts and styles
      *
      */
      public static function enqueue_scripts()
      {
         wp_enqueue_style( 'awesome-admin', TF_NUMBERS_DIR . 'assets/css/font-awesome.min.css', self::$version );
         wp_enqueue_style( 'tf_numbers-style', TF_NUMBERS_DIR . 'assets/css/style.css', self::$version );
         wp_enqueue_script( 'tf_numbers', TF_NUMBERS_DIR . 'assets/js/tf_numbers.js', array('jquery'), self::$version, true );
      }

      public static function admin_enqueue_scripts()
      {
          $screen = get_current_screen();
          if( is_admin() && 'tf_stats' === $screen->post_type ) {
             wp_enqueue_style( 'tf-admin', TF_NUMBERS_DIR . 'assets/css/admin.css', self::$version );
             wp_enqueue_script( 'tf-admin-js', TF_NUMBERS_DIR . 'assets/js/admin.js', array('jquery'), self::$version, true );
             wp_localize_script( 'tf-admin-js', 'url', array(
                    'path' => TF_NUMBERS_DIR.'assets/images/',
                    'admin' => admin_url( 'admin-ajax.php' )
              ) );
          }
         ?>
          <style>
               .cmb2_select{font-family: 'FontAwesome'; font-size: 1.2em;}
              .tf_numbers.vc_element-icon,
              .mce-ico.tf_numbers {background: none;}
              .mce-ico.tf_numbers:before,
              .tf_numbers.vc_element-icon:before{font-size: 31px}
          </style>
         <?php
      }

     /**
      * Register tf numbers button
      * to tinyMCE buttons
      *
      * @since   1.4.5
      */
      public static function register_button($buttons)
      {
        global $current_screen;
        $type = $current_screen->post_type;

        if( is_admin() )
          array_push( $buttons, 'tf_numbers' );

        return $buttons;
      }


     /**
      * Add script callback to tf numbers
      * shortcode button in tinyMCE editor
      *
      * @since   1.4.5
      */
      public static function add_button_js($plugins)
      {
        if( is_admin() )
          $plugins['tf_numbers'] = TF_NUMBERS_DIR . 'assets/js/shortcode.js';

        return $plugins;
      }


     /**
      * Collect Stats for inclusion
      * into shortcode selection.
      *
      * @since   1.4.5
      */
      public static function collect_numbers(){
        $args = array(
          'post_type'      => 'tf_stats',
          'posts_per_page' => -1
        );
         $stats = get_posts($args);
         ?>
         <script type="text/javascript">
           var names = {};
           <?php foreach( $stats as $stat ): ?>
           names['<?php echo $stat->post_name ?>'] = ['<?php echo $stat->post_name; ?>'];
           <?php endforeach; ?>
         </script>
         <?php
       }

     /**
      * Initialize Stats custom
      * post_type
      *
      * @since   1.0.0
      */
      public static function tf_stats_init()
      {
        $labels = array(
            'name'          => esc_html__( 'Random Numbers', 'tf_numbers' ),
            'singlular_name'=> esc_html__( 'Random Number', 'tf_numbers' ),
            'plural_name'   => esc_html__( 'Random Numbers', 'tf_numbers' ),
            'add_new'       => esc_html__('Add Numbers', 'tf_numbers'),
            'add_new_item'  => esc_html__('Add Numbers', 'tf_numbers'),
            'new_item'      => esc_html__('New Numbers', 'tf_numbers'),
            'edit_item'     => esc_html__('Edit Numbers', 'tf_numbers'),
            'all_items'     => esc_html__('All Numbers', 'tf_numbers'),
            'not_found'     => esc_html__('No Numbers found', 'tf_numbers'),
            'not_found_in_trash'  => esc_html__('No Numbers found in trash', 'tf_numbers'),
        );

        register_post_type(
          'tf_stats', array(
            'labels' => $labels,
            'public'  => false,
            'supports' => array('title'),
            'rewrite' => false,
            'publicly_queriable' => false,
            'show_ui' => true,
            'exclude_from_search' => true,
            'show_in_nav_menus' => false,
            'has_archive' => false,
            'menu_icon' => 'dashicons-slides',
            'menu_position'  => 65
          )
        );
      }

      /**
       * Create metaboxes for options
       *
       */
       public static function numbers_metabox_init()
       {
          $prefix = '_tf_';

          new TFNumbersOptions($prefix);
     }

     static function sections() {
       new TFNumbersSections;
     }

   /**
    * Include icons menu
    *
    * @since    1.0.0
    */
    public static function tf_icons_panel() {
      $screen = get_current_screen();
      if( is_admin() && 'post' === $screen->base && 'tf_stats' === $screen->post_type ) {
          $srch = '';
          $srch = apply_filters( 'tf_numbers_icon_search', $srch );
          //icons tabs
          $li = array( '<li class="active">Font-Awesome</li>' );
          $li = apply_filters( 'tf_icons_tabs', $li );
          $total = count($li);
          //tabs markup
          $ul = '<ul>';
          foreach( $li as $el ) {
            $ul .= $el;
          }
          $ul .= '</ul>';
          //tab content
          $tab  = '<div>';
          for($n = 0; $n < $total; $n++){
            if( $n === 0 ) $tab  .= '<div class="active"></div>';
            else if( $n === 1 ) $tab .= '<div>'.apply_filters( 'tf_custom_icons', '' ).'</div>';
            else $tab .= '<div></div>';
          }
          $tab .= '</div>';
          $html = '<div id="icons-wrap"><div id="icons"><i class="ic-remove">✖</i>';
          $html .= $srch;
          $html .= $ul;
          $html .= $tab;
          $html .= '</div>';
          $html .= '<div id="size_prev">';
          $html .= '<span></span>';
          $html .= '<span></span>';
          $html .= '<span></span>';
          $html .= '</div></div>';

          echo $html;
      }
    }

     /**
      * Add Custom Columns
      * post edit screen
      *
      */
      public static function tf_number_post_columns($cols)
      {
        $cols = array(
          'cb' => '<input type="checkbox" />',
          'title' => esc_html__('Title', 'tf_numbers'),
          'shortcode' => esc_html__('Shortcode', 'tf_numbers')
        );
        return $cols;
      }

     /**
     * custom columns callback
     *
     * @since    1.0.0
     */
      public static function tf_number_custom_columns( $column, $post_id )
      {
        switch( $column )
        {
          case 'shortcode':
            global $post;
            $name = $post->post_name;
            $shortcode = '<span style="border: solid 2px cornflowerblue; background:#fafafa; padding:2px 7px 5px; font-size:17px; line-height:40px;">[tf_numbers name="'.esc_attr($name).'"]</strong>';
          echo $shortcode;
          break;
        }
      }

      public static function replace_submit_meta_box() {
          $item = 'tf_stats';
          remove_meta_box('submitdiv', $item, 'core');
          add_meta_box('submitdiv', __('Save/Update Numbers', 'tf_numbers'), array( 'TF_Numbers', 'submit_meta_box' ), $item, 'side', 'low');
     }

     /**
      * Custom edit of default wordpress publish box callback
      * loop through each custom post type and remove default
      * submit box, replacing it with custom one that has
      * only submit button with custom text on it (add/update)
      *
      * @global $action, $post
      * @see wordpress/includes/metaboxes.php
      * @since  1.0
      *
      */
      public static  function submit_meta_box() {
          global $action, $post;

          $post_type = $post->post_type;
          $post_type_object = get_post_type_object($post_type);
          $can_publish = current_user_can($post_type_object->cap->publish_posts);
          $item = 'tf_stats';
          ?>
          <div class="submitbox" id="submitpost">
           <div id="major-publishing-actions">
           <?php
           do_action( 'post_submitbox_start' );
           ?>
           <div id="delete-action">
           <?php
           if ( current_user_can( "delete_post", $post->ID ) ) {
             if ( !EMPTY_TRASH_DAYS )
                  $delete_text = esc_html__('Delete Permanently');
             else
                  $delete_text = esc_html__('Move to Trash');
           ?>
           <a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a><?php
           } //if ?>
          </div>
           <div id="publishing-action">
           <span class="spinner"></span>
           <?php
           if ( !in_array( $post->post_status, array('publish', 'future', 'private') ) || 0 == $post->ID ) {
                if ( $can_publish ) : ?>
                  <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Save Numbers') ?>" />
                  <?php submit_button( sprintf( esc_html__( 'Save %' ), $item ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
           <?php
                endif;
           } else { ?>
                  <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update ') . $item; ?>" />
                  <input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e('Update ') . $item; ?>" />
           <?php
           } //if ?>
           </div>
           <div class="clear"></div>
           </div>
           </div>
        <?php
      } //som_submit_meta_box()


    /**
    * Remove view post link from
    * post edit screen
    *
    * @param $action
    * @return $action
    * @since 1.1
    */
    public static function remove_view_link( $action )
    {
       /* DEPRICATED since 1.4.1
        *
        unset ($action['view']);
        return $action;
        */
    }

    /**
    * Visual Composer Shortcode
    *
    * @since    1.4.8
    */
    public static function vc_support(){
      if( class_exists('WPBakeryVisualComposerAbstract') )
        require_once 'vc-shortcode.php';
    }

    /**
    * Initialize TF Numebrs
    *
    * @since    1.0.0
    */
    public static function init()
     {
       self::hooks();
     }
  }
}
