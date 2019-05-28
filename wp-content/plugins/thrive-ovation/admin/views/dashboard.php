<?php $is_thrive_leads_active = is_plugin_active( 'thrive-leads/thrive-leads.php' ); ?>
<?php $is_thrive_visual_editor_active = is_plugin_active( 'thrive-visual-editor/thrive-visual-editor.php' ); ?>
<div class="tvo-header">
	<nav id="tvo-nav">
		<div class="nav-wrapper">
			<div class="tvo-logo tvd-left">
				<a href="<?php menu_page_url( 'tvo_admin_dashboard' ); ?>"
				   title="<?php echo __( 'Thrive Ovation', TVO_TRANSLATE_DOMAIN ); ?>">
					<img src="<?php echo TVO_ADMIN_URL; ?>/img/tvo-logo-white.png">
				</a>
			</div>
			<?php include TVO_ADMIN_PATH . '/views/menu.php'; ?>
		</div>
	</nav>
</div>
<div class="tvo-breadcrumbs-wrapper">
	<ul class="tvo-breadcrumbs"></ul>
</div>

<div id="tvo-dashboard-wrapper"></div>

<div style="display: none;">
	<?php /* make sure we have the javascript instantiation code for tinymce */
	wp_editor( '', 'tvo-tinymce-tpl', array( 'quicktags' => false, 'media_buttons' => false ) );
	if ( ! empty( $code ) ) {
		echo '<a href="' . admin_url( 'admin.php?page=tvo_admin_dashboard#socialimport' ) . '" id="tvo_go_to_social_media"></a><script>document.getElementById("tvo_go_to_social_media").click();</script>';
	}
	?>
</div>
