<?php

if( !class_exists( 'TF_Numbers_Shortcode' ) )
{
  class TF_Numbers_Shortcode
  {
    // global style that will be
    // colleted in this variable
    private $css = array();
    // global index that will be
    //used in styles array
    private $n;

    function __construct()
    {
      add_shortcode( 'tf_numbers', array( $this, 'tf_numbers_shortcode' ) );
      add_action('wp_footer', array($this,'tf_print_style'));
    }

    public function tf_numbers_shortcode( $atts )
    {
      /**
        * Call post by name extracting the $name
        * from the shortcode previously created
        * in custom post column
        */
        extract( shortcode_atts( array(
             'tf_numbers'  => '',
             'name' => ''
          ), $atts )
        );

        $args = array('post_type' => 'tf_stats', 'name' => $name);
        $numbers = get_posts( $args );
        $html = '';
        if( $numbers )
        {
          foreach( $numbers as $number )
          {
            setup_postdata($number);
            $ID = 'tf-stat-' . $number->ID;
            $vals = get_post_meta( $number->ID, '_tf_stat', true );
            $image = get_post_meta( $number->ID, '_tf_bg', true );
            $bgc = get_post_meta( $number->ID, '_tf_bgc', true );
            $bgct = get_post_meta( $number->ID, '_tf_bgct', true );
            if( !$image ) {
                $image = esc_url($bgc);
            } else {
                $image = 'url('.esc_url($image).')';
            }
            $user_style = '';
            $addon_style = '';
            $tc = get_post_meta( $number->ID, '_tf_tc', true );
            $ic = get_post_meta( $number->ID, '_tf_ic', true );
            $ctc = get_post_meta( $number->ID, '_tf_ctc', true );
            $nc = get_post_meta( $number->ID, '_tf_nc', true );
            $ics = get_post_meta( $number->ID, '_tf_ics', true );
            $border = get_post_meta( $number->ID, '_tf_border', true );
            $tts = get_post_meta( $number->ID, '_tf_tts', true );
            $nbs = get_post_meta( $number->ID, '_tf_nbs', true );
            $layout = get_post_meta( $number->ID, '_tf_layout', true );
            $nmh = get_post_meta( $number->ID, '_tf_nmh', true ) ? get_post_meta( $number->ID, '_tf_nmh', true ) : '';
            $sp = get_post_meta( $number->ID, '_tf_sp', true ) ? get_post_meta( $number->ID, '_tf_sp', true ) : 80;
            $nmt = get_post_meta( $number->ID, '_tf_nmt', true ) ? 'data-nmt="'.get_post_meta( $number->ID, '_tf_nmt', true ).'"' : '';
            $nmtd = get_post_meta( $number->ID, '_tf_nmtd', true ) ? ' data-nmtd="'.get_post_meta( $number->ID, '_tf_nmtd', true ).'"' : '';
            $nma = get_post_meta( $number->ID, '_tf_nma', true ) ? 'data-nma="'.get_post_meta( $number->ID, '_tf_nma', true ).'"' : '';
            $nmad = get_post_meta( $number->ID, '_tf_nmad', true ) ? ' data-nmad="'.get_post_meta( $number->ID, '_tf_nmad', true ).'"' : '';
            $cm = get_post_meta( $number->ID, '_tf_cm', true ) ? 'data-cm="'.get_post_meta( $number->ID, '_tf_cm', true ).'"' : '';
            $cmo = get_post_meta( $number->ID, '_tf_cmo', true ) ? 'data-cmo="'.get_post_meta( $number->ID, '_tf_cmo', true ).'"' : '';
            $tvm = get_post_meta( $number->ID, '_tf_tvm', true ) ? get_post_meta( $number->ID, '_tf_tvm', true ) : '';
            $stats = $this->apply_layout($number->ID);
            $atts = apply_filters('tf_numbers_atts', '', $number->ID);

            //css
            $css = '#'.esc_attr($ID).'{background: '.esc_attr($image).'; background-size: cover} @media only screen and (max-width: 860px){ #'.esc_attr($ID).'{background-size: cover} }';
            if( strpos($image, 'url' ) !== false ) $css .= '#'.esc_attr($ID).':after{content: " ";display: block;background: rgba(0,0,0,0.57);width: 100%;height: 100%;position: absolute;top:0;left:0}';
            if( 'on' == $nmh ) {
                $css .= '#'.esc_attr($ID).' .stat, #'.esc_attr($ID).'{opacity: 0}';
                $css .= '#'.esc_attr($ID).' .stat[data-nm="none"], #'.esc_attr($ID).'[data-nma="none"]{opacity: 1}';
            }
            if( 'on' == $bgct ) {
                $css .= '#'.esc_attr($ID).'{background: transparent} #'.esc_attr($ID).':after{display: none}';
            }
            if ( ! empty( $border ) ) {
                $css .= '#'.esc_attr($ID).'{border: ' . esc_attr($border) . '}';
            }
            $css .= '#'.esc_attr($ID).' .stat .fa{color: '.esc_attr($ic).'; font-size: '.esc_attr($ics).'em} ';
            $css .= '#'.esc_attr($ID).' .stat .number{color: '.esc_attr($nc).'; font-size: '.esc_attr($nbs).'em} ';
            $css .= '#'.esc_attr($ID).' .stat .count-title{color: '.esc_attr($ctc).'; font-size: '.esc_attr($tts).'em; margin-bottom: 0} .stat .count-subtitle{display: block;}';
            $css .= '#'.esc_attr($ID).' h3{color: '.esc_attr($tc).'; margin: '.esc_attr($tvm).' 0;}';

            $user_style = apply_filters( 'tf_custom_styles', $user_style );
            if( $user_style ) {

              foreach( $user_style as $style ) {
                $selector = $style['selector'];
                $values = $style['values'];
                $css .= '#'.esc_attr($ID).' '.esc_attr($selector).'{';

                foreach( $values as $value ) {
                  $val = get_post_meta( $number->ID, '_tf_'.esc_attr($value['id']), true );
                  $prop = $value['property'];
                  $css .= esc_attr($prop).':'.esc_attr($val).';';
                }

                $css .= '}';
              }
            }

            $css .= apply_filters('tf_numbers_after_style', '', $ID, $number->ID);

            $addon_style = apply_filters( 'tf_addon_styles', $addon_style );
            if( $addon_style ) {
              foreach( $addon_style as $style ) {
                $selector = $style['selector'];
                $values = $style['values'];
                $css .= '#'.esc_attr($ID).' '.esc_attr($selector).'{';

                foreach( $values as $value ) {
                  $val = get_post_meta( $number->ID, '_tf_'.esc_attr($value['id']), true );
                  $prop = $value['property'];
                  $css .= esc_attr($prop).':'.esc_attr($val).';';
                }

                $css .= '}';
              }
            }

            $this->css[$ID] = $css;

            $html .= '<section id="'.esc_attr($ID).'" class="statistics '.esc_attr($layout).'" '.$nma.$nmad.$cmo.' data-sp="'.esc_attr($sp).'" '.$cm.$atts.'>';

            if( isset( $number->post_title ) && $number->post_title ) {
              $html .='<h3 '.$nmt.$nmtd.'>'. apply_filters('the_title', $number->post_title) .'</h3>';
            }

            $html .= '<div class="statistics-inner">';

            foreach( $vals as $key => $value ) {
              $nm = isset($value['_tf_nm']) ? 'data-nm="'.esc_attr($value['_tf_nm']).'"' : '';
              $nd = isset($value['_tf_nd']) ? ' data-nd="'.esc_attr($value['_tf_nd']).'"' : '';
              $nl = isset($value['_tf_nl']) ? ' data-nl="'.esc_attr($value['_tf_nl']).'"' : '';
              $number = isset($value['_tf_number']) ? $value['_tf_number'] : 0;
              $dynamic = isset($value['_tf_dynamic_nmb']) ? $value['_tf_dynamic_nmb'] : 0;
              $number = $this->get_number($number, $dynamic);
              $attts = apply_filters( 'tf_numbers_number_data', '', $value );
              $html .= sprintf( '<div class="stat" %s data-count="%s">', $nm.$nd.$nl.$attts, esc_attr($number) );
              $html .= $this->list_stats($stats, $value);
              $html .= '</div>';
            }

            $html .= '</div></section>';
          }
        }

        return $html;
    }

    public function list_stats($stats, $value) {
      //elements
      $cs = isset($value['cr']) ? ' '.$value['cr'] : '';
      $cs .= isset($value['crp']) ? ' '.$value['crp'] : '';
      $icon = '<span class="'. (isset($value['_tf_icon']) ? esc_attr($value['_tf_icon']) : '') .'"></span>';
      if ( isset($value['_tf_icon']) && strpos($value['_tf_icon'], '.') !== false ) {
        $icon = '<span class="custom-icon"><img src="' . esc_attr($value['_tf_icon']) .'" alt="icon" /></span>';
      }
      $number = '<span class="number'.esc_attr($cs).'"></span>';
      $title = '<span class="count-title">'. esc_html($value['_tf_title']) .'</span>';
      $sub = '';

      if( isset($value['_tf_subt']) ) {
          $sub = '<span class="count-subtitle">'. esc_html($value['_tf_subt'])  .'</span>';
      }

      for( $g = 0; $g < count($stats); $g++ )  {

         if( strpos( $stats[$g], "[val]" ) !== false ) {
            $split = explode('[val]', $stats[$g]);
            $val = $split[1];

            if( isset($value[$val]) ) {
              $stats[$g] = $split[0].$value[$val].$split[2];
            } else {
               $stats[$g] = '';
            }
         }

         if( $stats[$g] === 'icon' ) $stats[$g] = $icon;
         if( $stats[$g] === 'number' ) $stats[$g] = $number;
         if( $stats[$g] === 'title' ) $stats[$g] = $title;
         if( $stats[$g] === 'sub' ) $stats[$g] = $sub;
       }

      $html = '';
      foreach( $stats as $stat ) {
        $html .= $stat;
      }

      return $html;
    }

    function get_number($number, $dynamic) {
      if ( $dynamic ) {
        if(method_exists ($this, "get_{$dynamic}_count")) {
          $method = "get_{$dynamic}_count";
          return $this->$method();

        } else {
          return apply_filters('tf_numbers_dynamic_number', $dynamic);
        }

      } else {
        return $number;
      }
    }

    public function apply_layout($id) {
       $layout = get_post_meta( $id, '_tf_layout', true );
       if( 'n2' === $layout || 'n4' === $layout ) {
         $order = array(
               0 => 'icon',
               1 => 'title',
               2 => 'number'
          );
       } elseif( 'n6' === $layout ){
         $order =  array(
             0 => 'number',
             1 => 'icon',
             2 => 'title'
          );
       } elseif( 'n7' === $layout ) {
         $order =  array(
             1 => 'icon',
             2 => 'number',
             0 => 'title',
             3 => 'sub'
          );
       } elseif( 'n8' === $layout ) {
         $order =  array(
             0 => 'number',
             1 => 'title',
             2 => 'sub'
          );
       } elseif( 'n9' === $layout ) {
         $order =  array(
             1 => 'title',
             0 => 'number',
             2 => 'sub'
          );
       } else {
         $order =  array(
             0 => 'icon',
             1 => 'number',
             2 => 'title'
          );
       }

       return apply_filters( 'tf_layouts_order', $order );
    }

    public function tf_print_style(){
        $styles = $this->css;
        $css = '<style type="text/css">';
        foreach( $styles as $style ) {
          $css .= $style;
        }
        $css .= '</style>';

        echo $css;
    }

    function get_articles_count() {
      $args = array(
        'posts_per_page'   => -1,
      	'post_type'        => 'post',
      	'post_status'      => 'publish',
      	'suppress_filters' => true,
      );
      $posts = get_posts($args);

      return count($posts);
    }

    function get_authors_count() {
      $user_query = new \WP_User_Query( array( 'who' => 'authors' ) );

      return count($user_query->results);
    }

    function get_categories_count() {
      $args = array('hide_empty' => false);
      $categories = get_categories($args);

      return count($categories);
    }

    function get_comments_count(){
      $comments_count = wp_count_comments();

      return $comments_count->total_comments;
      // echo "Comments for site <br />";
      // echo "Comments in moderation: " . $comments_count->moderated . "<br />";
      // echo "Comments approved: " . $comments_count->approved . "<br />";
      // echo "Comments in Spam: " . $comments_count->spam . "<br />";
      // echo "Comments in Trash: " . $comments_count->trash . "<br />";
      // echo "Total Comments: " . $comments_count->total_comments . "<br />";
    }
  }//class ends
}//if !class_exists
