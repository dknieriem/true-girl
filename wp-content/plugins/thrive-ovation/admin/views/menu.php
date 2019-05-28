<ul class="tvd-right">
	<li class="tvo-main-menu-button">
		<a href="javascript:void(0)" data-dropdown="display-testimonials" class="tvd-dropdown-button" data-activates="tvo-display-testimonials-dropdown"
		   data-beloworigin="true" data-hover="false" data-constrainwidth="false">
			<i class="tvo-icon-television"></i>
			<?php echo __( 'Display Testimonials', TVO_TRANSLATE_DOMAIN ); ?>
			<i class="tvd-icon-expanded tvd-no-margin-right"></i>
		</a>
		<ul id="tvo-display-testimonials-dropdown" class="tvd-dropdown-content">
			<li>
				<a href="<?php echo $is_thrive_visual_editor_active ? TVO_DISPLAY_USING_CONTENT_BUILDER_ACTIVE : TVO_DISPLAY_USING_CONTENT_BUILDER_INACTIVE ?>"
				   class="wistia-popover[height=450,playerColor=2bb914,width=800]"><?php echo __( 'Display using Thrive Content Builder', TVO_TRANSLATE_DOMAIN ); ?>
				</a>
			</li>
			<li>
				<a href="#shortcodes/display"
				   class="tvo-main-menu-link"><?php echo __( 'Display using Wordpress Shortcodes', TVO_TRANSLATE_DOMAIN ); ?></a>
			</li>
			<!-- DISABLE THIS FOR NOW
			<li>
				<a href="<?php echo $is_thrive_leads_active ? TVO_DISPLAY_USING_LEADS_ACTIVE : TVO_DISPLAY_USING_LEADS_INACTIVE ?>"
				   class="wistia-popover[height=450,playerColor=2bb914,width=800]"><?php echo __( 'Display in Email Capture Form', TVO_TRANSLATE_DOMAIN ); ?></a>
			</li>
			-->
		</ul>
	</li>
	<li class="tvo-main-menu-button">
		<a href="javascript:void(0)" data-dropdown="capture-testimonials" class="tvd-dropdown-button" data-activates="tvo-capture-testimonials-dropdown"
		   data-beloworigin="true" data-hover="false" data-constrainwidth="false">
			<!--#shortcodes/capture-->
			<i class="tvo-icon-crosshairs"></i>
			<?php echo __( 'Capture Testimonials', TVO_TRANSLATE_DOMAIN ); ?>
			<i class="tvd-icon-expanded tvd-no-margin-right"></i>
		</a>
		<ul id="tvo-capture-testimonials-dropdown" class="tvd-dropdown-content">
			<!-- DISABLE THIS FOR NOW
			<li>
				<a href="<?php echo $is_thrive_visual_editor_active ? TVO_CAPTURE_USING_LANDING_PAGE_ACTIVE : TVO_CAPTURE_USING_LANDING_PAGE_INACTIVE ?>"
				   class="wistia-popover[height=450,playerColor=2bb914,width=800]"><?php echo __( 'Create a Testimonial Capture Landing Page', TVO_TRANSLATE_DOMAIN ); ?></a>
			</li>
			-->
			<li>
				<a href="<?php echo $is_thrive_visual_editor_active ? TVO_CAPTURE_USING_CONTENT_BUILDER_ACTIVE : TVO_CAPTURE_USING_CONTENT_BUILDER_ACTIVE ?>"
				   class="wistia-popover[height=450,playerColor=2bb914,width=800]"><?php echo __( 'Capture using Thrive Content Builder', TVO_TRANSLATE_DOMAIN ); ?></a>
			</li>
			<li>
				<a href="#shortcodes/capture"
				   class="tvo-main-menu-link"><?php echo __( 'Capture using Wordpress Shortcodes', TVO_TRANSLATE_DOMAIN ); ?></a>
			</li>
			<li>
				<a href="<?php echo $is_thrive_leads_active ? TVO_CAPTURE_USING_LEADS_ACTIVE : TVO_CAPTURE_USING_LEADS_ACTIVE ?>"
				   class="wistia-popover[height=450,playerColor=2bb914,width=800]"><?php echo __( 'Capture using Thrive Leads', TVO_TRANSLATE_DOMAIN ); ?></a>
			</li>
			<li>
				<a href="#socialimport" class="tvo-main-menu-link"><?php echo __( 'Import from Social Media', TVO_TRANSLATE_DOMAIN ); ?></a>
			</li>
		</ul>
	</li>
	<li>
		<a href="#settings">
			<i class="tvd-icon-settings"></i>
			<?php echo __( 'Settings', TVO_TRANSLATE_DOMAIN ); ?>
		</a>
	</li>
	<li>
		<a id="tvd-share-modal" class="tvd-modal-trigger" href="#tvd-modal1"
		   data-overlay_class="tvd-white-bg" data-opacity=".95">
			<span class="tvd-icon-heart"></span>
		</a>
	</li>
</ul>
<?php include TVE_DASH_PATH . '/templates/share.phtml'; ?>
