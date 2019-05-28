<ul class="tvd-right">
	<li>
		<a href="#reporting">
			<i class="tvd-icon-line-graph"></i>
			<?php echo __( "Reporting", THO_TRANSLATE_DOMAIN ); ?>
		</a>
	</li>
	<li>
		<a href="javascript:void(0);" class="tho-toggle-settings">
			<i class="tvd-icon-settings"></i>
			<?php echo __( "Settings", THO_TRANSLATE_DOMAIN ); ?>
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