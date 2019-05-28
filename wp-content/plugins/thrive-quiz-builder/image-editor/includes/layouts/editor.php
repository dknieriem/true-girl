<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * This file has to be included at the beginning of all editor layouts
 *
 * @package thrive-quiz-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

if ( tie()->is_request( 'ajax' ) ) {
	return;
}
$image = new TIE_Image( get_post() );
nocache_headers();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="tie-html">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow"/>
	<?php wp_head(); ?>

	<?php echo $image->print_fonts() ?>
</head>
<body>

<div id="wpbody" class="clearfix">

	<div id="tie-canvas-wrapper">
		<div id="tie-canvas" style="<?php echo $image->get_canvas_style() ?>">
			<div id="tie-canvas-overlay" style="<?php echo $image->get_overlay_style() ?>"></div>
			<?php echo apply_filters( 'tie_canvas_content', $image->get_content() ) ?>
		</div>
	</div>

	<?php include( dirname( dirname( __FILE__ ) ) . '/templates/control-panel.php' ) ?>
</div>

<?php wp_footer() ?>

<script type="text/template" id="tie-element-text">
	<div class="tie-element">
		<div class="tie-editable">
			<p style="font-family: Roboto">
				<#= item.label #>
			</p>
		</div>
	</div>
</script>

<script type="text/template" id="tie-element-actions">
	<div class="tie-element-actions">
		<span class="tie-duplicate tie-action-icons"><i class="tie-icons tie-window-restore"></i></span>
		<span class="tie-drag tie-action-icons"><i class="tie-icons tie-move"></i></span>
		<span class="tie-delete tie-action-icons"><i class="tie-icons tie-cross"></i></span>
	</div>
</script>

<script type="text/template" id="tie-page-loader">
	<div class="tie-preloader-overlay tie-page-preloader" id="tie-hide-onload" style="display: block">
		<div class="tie-card-preloader">
			<div class="tie-preloader-wrapper tie-bigger tie-active">
				<div class="tie-spinner-layer tie-spinner-blue-only">
					<div class="tie-circle-clipper tie-left">
						<div class="tie-circle"></div>
					</div>
					<div class="tie-gap-patch">
						<div class="tie-circle"></div>
					</div>
					<div class="tie-circle-clipper tie-right">
						<div class="tie-circle"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>

<script type="text/template" id="tie-modal-templates">
	<div class="tie-modal-content">
		<div class="tie-modal-title">
			<h3>
				<?php echo __( 'Social Share Badge Template you would like to use', Thrive_Image_Editor::T ) ?>
			</h3>
		</div>
		<p><?php echo __( 'Choose a Social Share Badge template or start creating one from scratch' ) ?>:</p>
		<div id="tie-templates-list" class="tie-row tie-collapse"></div>
	</div>
	<div class="tie-modal-footer">
		<div class="tie-row">
			<div class="tie-col tie-s6">&nbsp;
				<a href="javascript:void(0)" class="tie-waves-effect tie-waves-light tie-btn tie-btn-gray tie-modal-close">
					<?php echo __( 'Cancel', Thrive_Image_Editor::T ) ?>
				</a>
			</div>
			<div class="tie-col tie-s6">
				<a href="javascript:void(0)"
				   class="tie-waves-effect tie-waves-light tie-btn tie-btn-green tie-right tie-modal-submit">
					<?php echo __( 'Choose Template', Thrive_Image_Editor::T ) ?>
				</a>
			</div>
		</div>
	</div>
</script>

<script type="text/template" id="tie-modal-delete-bg">
	<div class="tie-modal-content">
		<h4><?php echo __( 'Are you sure you want to delete this background image' ) ?>?</h4>
	</div>
	<div class="tie-modal-footer">
		<div class="tie-row">
			<div class="tie-col tie-s12 tie-m6">
				<a href="javascript:void(0)"
				   class="tie-btn-flat tie-btn-flat-primary tie-btn-flat-light tie-modal-close tie-waves-effect"><?php echo __( 'Cancel', Thrive_Quiz_Builder::T ) ?></a>
			</div>
			<div class="tie-col tie-s12 tie-m6">
				<a href="javascript:void(0)"
				   class="tie-waves-effect tie-waves-light tie-btn-flat tie-btn-flat-primary tie-btn-flat-light tie-right tve-confirm-delete-action"><?php echo __( 'Delete', Thrive_Quiz_Builder::T ) ?></a>
			</div>
		</div>
	</div>
</script>

<script type="text/template" id="template-item">
	<div class="tie-card tie-white tie-pointer tie-center-align <#= item.get('selected') ? 'tie-selected-card' : '' #>" data-key="<#= item.get('key') #>">
		<div class="tie-card-content">
			<div class="tie-template-item-image" style="<#= item.get_thumb_style() #>"></div>
			<p>
				<strong>
					<#= item.get('name') #>
				</strong>
			</p>
		</div>
	</div>
</script>

<script type="text/template" id="tie-modal-preview-canvas">
	<div class="tie-modal-content tie-preview-modal">
		<div class="tie-row">
			<a href="javascript:void(0)" class="tie-waves-effect tie-waves-light tie-btn tie-btn-green tie-generate-result">
				<?php echo __( 'Generate Random Result', Thrive_Image_Editor::T ) ?>
			</a>
		</div>
		<div class="tie-row">
			<div id="tie-canvas-preview-container"></div>
		</div>
	</div>
</script>

<script type="text/template" id="tie-modal-canvas-size">
	<div class="tie-modal-content">
		<h3 class="tie-modal-title">
			<?php echo __( 'Resize', Thrive_Image_Editor::T ) ?>
		</h3>
		<div class="tie-v-spacer"></div>
		<div class="tie-row">
			<div class="tie-col tie-s5">
				<div class="tie-input-field">
					<input name="width" type="number" min="0" value="<#= size.get('width') #>">
					<label for="tie-ar-install-url" class="tie-active"><?php echo __( 'Width', Thrive_Image_Editor::T ) ?></label>
				</div>
			</div>
			<div class="tie-col tie-s2 tie-center">X</div>
			<div class="tie-col tie-s5">
				<div class="tie-input-field">
					<input name="height" type="number" min="0" value="<#= size.get('height') #>">
					<label for="tie-ar-install-url" class="tie-active"><?php echo __( 'Height', Thrive_Image_Editor::T ) ?></label>
				</div>
			</div>
		</div>
	</div>

	<div class="tie-modal-footer">
		<div class="tie-row">
			<div class="tie-col tie-s6">
				<a href="javascript:void(0)"
				   class="tie-waves-effect tie-waves-light tie-btn tie-btn-gray tie-modal-close">
					<?php echo __( 'Close', Thrive_Image_Editor::T ) ?>
				</a>
			</div>
			<div class="tie-col tie-s6">
				<a href="javascript:void(0)"
				   class="tie-waves-effect tie-waves-light tie-btn tie-btn-green tie-right tie-modal-submit" id="tie-set-new-dimensions">
					<?php echo __( 'Save', Thrive_Image_Editor::T ) ?>
				</a>
			</div>
		</div>
	</div>
</script>

<script type="text/template" id="tie-modal-image-position">
	<img id="tie-img-loader" style="display: none"/>
	<div class="tie-modal-content">
		<h3 class="tie-modal-title"><?php echo __( 'Background image crop position', Thrive_Image_Editor::T ) ?></h3>
		<hr>
		<div class="tie-row tie-collapse tie-modal-subtitle">
			<div class="tie-col tie-s10">
				<?php echo __( 'Drag the image to place it on your canvas. Use the corners handlers to resize your image.' ) ?>
			</div>
			<div class="tie-col tie-s2 tie-text-right">
				<a class="tie-bg-toggle-help" href="javascript:void(0)"><span><?php echo __( 'Help', Thrive_Image_Editor::T ) ?></span><i
							class="tie-icons tie-question"></i></a>
			</div>
		</div>
		<div id="tie-image-option-bar" class="tie-collapse tie-no-margin tie-gray tie-lighten-3">
			<div class="tie-floating-containers">
				<p class="tie-background-action-details"><?php echo __( 'Align Image to Canvas', Thrive_Image_Editor::T ) ?></p>
				<div class="tie-position-canvas">
					<div id="top">
						<a class="tie-btn tie-click" data-fn="position" data-params="nw" href="javascript:void(0)"></a>
						<a class="tie-btn tie-click" data-fn="position" data-params="n" href="javascript:void(0)"></a>
						<a class="tie-btn tie-click" data-fn="position" data-params="ne" href="javascript:void(0)"></a>
					</div>
					<div id="mid">
						<a class="tie-btn tie-click" data-fn="position" data-params="w" href="javascript:void(0)"></a>
						<a class="tie-btn tie-click" data-fn="position" data-params="c" href="javascript:void(0)"></a>
						<a class="tie-btn tie-click" data-fn="position" data-params="e" href="javascript:void(0)"></a>
					</div>
					<div id="bot">
						<a class="tie-btn tie-click" data-fn="position" data-params="sw" href="javascript:void(0)"></a>
						<a class="tie-btn tie-click" data-fn="position" data-params="s" href="javascript:void(0)"></a>
						<a class="tie-btn tie-click" data-fn="position" data-params="se" href="javascript:void(0)"></a>

					</div>
				</div>
			</div>
			<div class="tie-floating-containers">
				<p class="tie-background-action-details tie-image-size"><?php echo __( 'Image Size', Thrive_Image_Editor::T ) ?></p>
				<div class="tie-size-slider tie-row">
					<div class="tie-col tie-s7" id="tie-image-size" data-connect-to="#tie-image-zoom"></div>
					<input class="tie-col tie-s3" id="tie-image-zoom" type="number">
				</div>
			</div>
		</div>

		<div id="tie-workspace" class="tie-row tie-collapse tie-no-margin">
			<div id="tie-bg-helper-overlay"></div>
			<div id="tie-canvas-frame">
				<div id="tie-bg-helper">
					<div class="tie-centered-helper">
						<div class="tie-col tie-s6 tie-bordered-box">
							<div class="tie-help-image" id="tie-help-drag"></div>
							<p class="tie-gray-text tie-text-lighten-4"><?php echo __( 'Start Dragging to move your image on the canvas', Thrive_Image_Editor::T ) ?></p>
						</div>
						<div class="tie-col tie-s6">
							<div class="tie-help-image" id="tie-help-resize"></div>
							<p class="tie-gray-text tie-text-lighten-4"><?php echo __( 'Resize the image by dragging the corners', Thrive_Image_Editor::T ) ?></p>
						</div>
						<div class="tie-col tie-s12 tie-align-center">
							<a class="tie-bg-toggle-help tie-btn tie-btn-blue"
							   href="javascript:void(0)"><?php echo __( "Got it, let's go", Thrive_Image_Editor::T ) ?></a>
						</div>
					</div>
				</div>
				<div id="tie-canvas-dimensions">
					<?php echo sprintf( __( 'Canvas size: %s X %s', Thrive_Image_Editor::T ), $image->get_settings()->get_data( 'size/width' ), $image->get_settings()->get_data( 'size/height' ) ) ?>
				</div>

				<div id="top" class="tie-frame-item"></div>
				<div id="right" class="tie-frame-item"></div>
				<div id="bottom" class="tie-frame-item"></div>
				<div id="left" class="tie-frame-item"></div>

				<div id="tie-workspace-image">
					<div class="tie-resize-handle tie-resize-nw ui-resizable-handle ui-resizable-nw"></div>
					<div class="tie-resize-handle tie-resize-ne ui-resizable-handle ui-resizable-ne"></div>
					<div class="tie-resize-handle tie-resize-se ui-resizable-handle ui-resizable-se"></div>
					<div class="tie-resize-handle tie-resize-sw ui-resizable-handle ui-resizable-sw"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="tie-modal-footer">
		<div class="tie-row">
			<div class="tie-col tie-s6">
				<a href="javascript:void(0)" class="tie-modal-close">
					<?php echo __( 'Cancel', Thrive_Image_Editor::T ) ?>
				</a>
			</div>
			<div class="tie-col tie-s6">
				<a href="javascript:void(0)"
				   class="tie-waves-effect tie-waves-light tie-btn tie-btn-green tie-right tie-modal-submit">
					<?php echo __( 'Save', Thrive_Image_Editor::T ) ?>
				</a>
			</div>
		</div>
	</div>
</script>

<script type="text/template" id="modal-loader">
	<div class="tie-modal-preloader">
		<div class="tie-preloader-wrapper tie-big tie-active">
			<div class="tie-spinner-layer tie-spinner-blue-only">
				<div class="tie-circle-clipper tie-left">
					<div class="tie-circle"></div>
				</div>
				<div class="tie-gap-patch">
					<div class="tie-circle"></div>
				</div>
				<div class="tie-circle-clipper tie-right">
					<div class="tie-circle"></div>
				</div>
			</div>
		</div>
	</div>
</script>

<script type="text/template" id="text-element-panel">
	<h4 class="tie-bold tie-panel-subtitle"><?php echo __( 'Text formatting', Thrive_Image_Editor::T ) ?></h4>
	<div class="tie-card tie-text-formatting-holder">
		<div class="tie-card-content">
			<div id="tie-text-styling">
				<p class="tie-text-style-title"><?php echo __( 'Text style', Thrive_Image_Editor::T ) ?></p>
				<ul class="tie-buttons-list">
					<li>
						<a data-click-callback="Bold" id="tie-bold" class="tie-click tie-waves-effect"><b>B</b></a>
					</li>
					<li>
						<a data-click-callback="Italic" class="tie-click tie-waves-effect"><i>I</i></a>
					</li>
					<li>
						<a data-click-callback="Underline" class="tie-click tie-waves-effect"><u>U</u></a>
					</li>
					<li>
						<a data-click-callback="Strikethrough" class="tie-click tie-waves-effect"><s>S</s></a>
					</li>

				</ul>
				<ul class="tie-buttons-list">
					<li>
						<a data-click-callback="InsertUnorderedList" class="tie-click tie-waves-effect"><i class="tie-icons tie-list"></i></a>
					</li>
					<li>
						<a data-click-callback="InsertOrderedList" class="tie-click tie-waves-effect"><i class="tie-icons tie-list-numbered"></i></a>
					</li>
					<li>
						<a data-click-callback="clear_format" class="tie-click tie-waves-effect">C</a>
					</li>
				</ul>

				<p class="tie-text-style-title"><?php echo __( 'Text align', Thrive_Image_Editor::T ) ?></p>
				<ul class="tie-buttons-list">
					<li>
						<a data-click-callback="JustifyLeft" class="tie-click tie-waves-effect"><i class="tie-icons tie-align-left2"></i></a>
					</li>
					<li>
						<a data-click-callback="JustifyCenter" class="tie-click tie-waves-effect"><i class="tie-icons tie-align-center"></i></a>
					</li>
					<li>
						<a data-click-callback="JustifyRight" class="tie-click tie-waves-effect"><i class="tie-icons tie-align-right"></i></a>
					</li>
					<li>
						<a data-click-callback="JustifyFull" class="tie-click tie-waves-effect"><i class="tie-icons tie-align-justify"></i></a>
					</li>
				</ul>
			</div>
			<hr>
			<div id="text-color" class="clearfix">
				<div class="left">
					<p class="tie-text-style-title"><?php echo __( 'Text color', Thrive_Image_Editor::T ) ?></p>
					<input type="text" class="tie-color-picker-forecolor" value="">
					<input type="text" class="tie-color-code" id="tie-text-element-forecolor">
				</div>
				<div class="right">
					<p class="tie-text-style-title"><?php echo __( 'Background color', Thrive_Image_Editor::T ) ?></p>
					<input type="text" class="tie-color-picker-hilitecolor" value="">
					<input type="text" class="tie-color-code" id="tie-text-element-hilitecolor">
				</div>
			</div>
			<hr>
			<div id="font-family">
				<p class="tie-text-style-title"><?php echo __( 'Font face', Thrive_Image_Editor::T ) ?></p>
				<select id="tie-fonts">
					<option value=""><?php echo __( '- Select Font -', Thrive_Image_Editor::T ) ?></option>
				</select>
			</div>
			<hr>
			<div id="font-size">
				<p class="tie-text-style-title"><?php echo __( 'Font size', Thrive_Image_Editor::T ) ?></p>
				<div class="slider-holder">
					<div id="tie-font-size-slider"
						 data-min="1"
						 data-max="150"
						 data-value="0"
						 data-connect-to="#tie-font-size">
					</div>
					<div class="tie-font-size-input">
						<div class="tie-labels">
							<a href="javascript:void(0)" class="tie-font-size-unit tie-click" data-click-callback="change_font_size_unit" data-params="px">px</a>
							<span>-</span>
							<a href="javascript:void(0)" class="tie-font-size-unit tie-click" data-click-callback="change_font_size_unit" data-params="rem">em</a>
						</div>
						<input id="tie-font-size" class="tie-left" type="number" maxlength="4">
					</div>
				</div>
			</div>
			<div id="font-height">
				<p class="tie-text-style-title"><?php echo __( 'Line height', Thrive_Image_Editor::T ) ?></p>
				<div class="slider-holder">
					<div id="tie-line-height-slider"
						 data-min="1"
						 data-max="200"
						 data-value="0"
						 data-connect-to="#tie-line-height">
					</div>
					<div class="tie-line-height-input">
						<div class="tie-labels">
							<!-- Add .active class on toggle -->
							<a href="javascript:void(0)" class="tie-line-height-unit tie-click" data-click-callback="change_line_height_unit" data-params="px">px</a>
							<span>-</span>
							<a href="javascript:void(0)" class="tie-line-height-unit tie-click" data-click-callback="change_line_height_unit" data-params="rem">em</a>
						</div>
						<input id="tie-line-height" class="tie-left" type="number" maxlength="4">
					</div>
				</div>
			</div>
		</div>
	</div>
</script>
</body>
</html>
