<div id="floating_menu" <?php echo $float_menu_attr; ?>>
	<div class="menu-header">
		<?php
		$thrive_logo = false;
		if ( $options['logo_type'] == "text" ):
			if ( get_theme_mod( 'thrivetheme_header_logo' ) != 'hide' ):
				?>
			<div id="text_logo"
			     class="<?php if ( $options['logo_color'] == "default" ): ?><?php echo $options['color_scheme'] ?><?php else: ?><?php echo $options['logo_color'] ?><?php endif; ?> ">
				<a href="<?php echo home_url( '/' ); ?>"><?php echo $options['logo_text']; ?></a>
			</div>

			<?php endif;
		
			elseif ( $options['logo'] && $options['logo'] != "" ):
			$thrive_logo = true;
			if ( get_theme_mod( 'thrivetheme_header_logo' ) != 'hide' ):
			?>
				<div id="logo" class="lg left">
					<a href="<?php echo home_url( '/' ); ?>">
						<img src="<?php echo $options['logo']; ?>"
						     alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"/>
					</a>
				</div>
				<?php
			endif;
		endif;
		?>
		<?php if ( has_nav_menu( "primary" ) ): ?>
			<?php wp_nav_menu( array(
				'container'       => 'nav',
				'depth'           => 0,
				'theme_location'  => 'primary',
				'container_class' => "main-menu",
				'menu_class'      => 'menu',
				'menu_id'		  => 'blog-menu',
				'link_before' => '<span class="menu-text">',
				'link_after'  => '</span>',
				'walker'          => new skg_custom_menu_walker()
			) ); ?>
			<?php require_once get_template_directory() . '/inc/templates/woocommerce-navbar-mini-cart.php'; ?>
		<?php else: ?>
			<span class="dfm">
				<?php _e( "Assign a 'primary' menu", 'thrive' ); ?>
			</span>
		<?php endif; ?>
		<div class="clear"></div>
		
		<?php tha_header_bottom(); ?>
	</div>
	<?php tha_header_after(); ?>
</div>
