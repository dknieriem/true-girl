<?php

/**
 * The class responsible for the custom post type.
 *
 * @since        1.5.5
 * @package      Shortcodes_Ultimate_Maker
 * @subpackage   Shortcodes_Ultimate_Maker/includes
 */
final class Shortcodes_Ultimate_Maker_Post_Type {

	/**
	 * The slug of the custom post type.
	 *
	 * @since    1.5.5
	 * @access   private
	 * @var      string    $slug    The slug of the custom post type.
	 */
	private $slug;

	/**
	 * The class constructor.
	 *
	 * Only defines class properties.
	 *
	 * @since   1.5.5
	 */
	public function __construct() {
		$this->slug = 'shortcodesultimate';
	}

	/**
	 * Register the custom post type.
	 *
	 * @since  1.5.5
	 */
	public function register_post_type() {

		$capability = apply_filters( 'su/maker/capability', 'manage_options' );

		$labels = array(
			'name'               => __( 'Custom shortcodes',        'shortcodes-ultimate-maker' ),
			'singular_name'      => __( 'Shortcode',                'shortcodes-ultimate-maker' ),
			'add_new'            => __( 'Create shortcode',         'shortcodes-ultimate-maker' ),
			'add_new_item'       => __( 'Add new Shortcode',        'shortcodes-ultimate-maker' ),
			'edit_item'          => __( 'Edit shortcode',           'shortcodes-ultimate-maker' ),
			'new_item'           => __( 'New shortcode',            'shortcodes-ultimate-maker' ),
			'all_items'          => __( 'Custom shortcodes',        'shortcodes-ultimate-maker' ),
			'view_item'          => __( 'View shortcode',           'shortcodes-ultimate-maker' ),
			'search_items'       => __( 'Search shortcodes',        'shortcodes-ultimate-maker' ),
			'not_found'          => __( 'No shortcodes found',      'shortcodes-ultimate-maker' ),
			'not_found_in_trash' => __( 'No shortcodes in Trash',   'shortcodes-ultimate-maker' ),
			'menu_name'          => __( 'Custom shortcodes',        'shortcodes-ultimate-maker' ),
			'filter_items_list'  => __( 'Filter custom shortcodes', 'shortcodes-ultimate-maker' ),
			'parent_item_colon'  => '',
		);

		$args = array(
			'labels'               => $labels,
			'public'               => false,
			'exclude_from_search'  => true,
			'publicly_queryable'   => false,
			'show_ui'              => true,
			'show_in_nav_menus'    => false,
			'show_in_menu'         => 'shortcodes-ultimate',
			'show_in_admin_bar'    => false,
			'supports'             => false,
			'query_var'            => false,
			'capabilities'         => array(
				'edit_post'              => $capability,
				'read_post'              => $capability,
				'delete_post'            => $capability,
				'edit_posts'             => $capability,
				'edit_others_posts'      => $capability,
				'publish_posts'          => $capability,
				'read_private_posts'     => $capability,
				'read'                   => $capability,
				'delete_posts'           => $capability,
				'delete_private_posts'   => $capability,
				'delete_published_posts' => $capability,
				'delete_others_posts'    => $capability,
				'edit_private_posts'     => $capability,
				'edit_published_posts'   => $capability,
				'create_posts'           => $capability,
			),
		);

		register_post_type( $this->slug, $args );

	}

	/**
	 * Change the 'post updated' messages.
	 *
	 * @since  1.5.5
	 * @param mixed   $messages Original 'post updated' messages.
	 * @return mixed            Modified 'post updated' messages.
	 */
	public function custom_updated_messages( $messages ) {

		if ( get_post_type() !== $this->slug ) {
			return $messages;
		}

		$preview = Su_Generator::button( array(
				'target'    => '',
				'text'      => __( 'View shortcode', 'shortcodes-ultimate-maker' ),
				'class'     => '',
				'icon'      => false,
				'echo'      => false,
				'shortcode' => get_post_meta( get_the_ID(), 'sumk_slug', true ),
			) );

		$messages[ $this->slug ] = array(
			0 => '',
			1  => sprintf( __( 'Shortcode updated. %s.', 'shortcodes-ultimate-maker' ),   $preview ),
			2 => '',
			3 => '',
			4  => sprintf( __( 'Shortcode updated. %s.', 'shortcodes-ultimate-maker' ),   $preview ),
			5 => false,
			6  => sprintf( __( 'Shortcode added. %s.', 'shortcodes-ultimate-maker' ),     $preview ),
			7  => sprintf( __( 'Shortcode saved. %s.', 'shortcodes-ultimate-maker' ),     $preview ),
			8  => sprintf( __( 'Shortcode submitted. %s.', 'shortcodes-ultimate-maker' ), $preview ),
			9  => '',
			10 => __( 'Shortcode draft updated', 'shortcodes-ultimate-maker' ),
		);

		return $messages;

	}

	/**
	 * Add custom admin columns.
	 *
	 * @since  1.5.5
	 * @param mixed   $columns   Original admin columns.
	 * @param string  $post_type Post type slug.
	 * @return mixed             Modified admin columns.
	 */
	public function add_posts_columns( $columns, $post_type = 'page' ) {

		$new_columns = array(
			'shortcode' => __( 'Shortcode', 'shortcodes-ultimate-maker' ),
			'icon'      => __( 'Icon', 'shortcodes-ultimate-maker' ),
		);

		$columns = $columns + $new_columns;

		unset( $columns['date'] );

		return $columns;

	}

	/**
	 * Display the content in custom admin columns.
	 *
	 * @since  1.5.5
	 * @param string  $column  Admin column ID.
	 * @param int     $post_id Post ID.
	 */
	public function posts_custom_column( $column, $post_id ) {

		if ( 'icon' === $column ) {

			$icon = get_post_meta( $post_id, 'sumk_icon', true );

			// <img> icon
			if ( strpos( $icon, '/' ) !== false ) {
				$icon_template = '<img src="%1$s" alt="" width="%2$s" height="%2$s">';
			}

			// FontAwesome icon
			else {
				$icon_template = '<i class="fa fa-%1$s" style="display:block;width:%2$spx;line-height:%2$spx;text-align:center;font-size:%3$spx;color:#888"></i>';
			}

			printf(
				$icon_template,
				$icon,
				40, // image size
				34 // FA icon font-size
			);

		}

		elseif ( 'shortcode' === $column ) {

			printf(
				'<code>[%s%s]</code>',
				get_option( 'su_option_prefix', '' ),
				get_post_meta( $post_id, 'sumk_slug', true )
			);

			if ( get_post_meta( $post_id, 'sumk_desc', true ) ) {

				printf(
					'<p class="description">%s</p>',
					get_post_meta( $post_id, 'sumk_desc', true )
				);

			}

		}

	}

	/**
	 * Add custom row actions for posts.
	 *
	 * @since  1.5.5
	 * @param mixed   $actions Default actions.
	 * @param WP_Post $post    The post object.
	 * @return mixed           Modified actions.
	 */
	public function post_row_actions( $actions, $post ) {

		if ( $post->post_status !== 'publish' || $post->post_type !== $this->slug ) {
			return $actions;
		}

		$shortcode = get_post_meta( $post->ID, 'sumk_slug', true );

		$actions['su_generator'] = Su_Generator::button( array(
				'target'    => '',
				'text'      => __( 'View shortcode', 'shortcodes-ultimate-maker' ),
				'class'     => '',
				'icon'      => false,
				'echo'      => false,
				'shortcode' => $shortcode
			) );

		unset( $actions['inline hide-if-no-js'] );

		return $actions;

	}

}
