<?php defined( 'ABSPATH' ) or exit; ?>

<!-- ==================== -->
<!-- Icon field templates -->
<!-- ==================== -->

<!-- General template -->
<script type="text/html" id="sum-icon-tpl" class="su-maker-editor-js-template">
	<a href="javascript:;" class="sum-icon-picker wp-clearfix button"></a>
	<div class="sum-icon-dropdown wp-clearfix">
		<div class="sum-icon-media-link">
			<a href="javascript:;"></a>
		</div>
	</div>
</script>
<!-- /General template -->

<!-- FontAwesome icon -->
<script type="text/html" id="sum-icon-fa-tpl" class="su-maker-editor-js-template">
	<i class="fa fa-%ICON%" title="%ICON%"></i>
</script>
<!-- /FontAwesome icon -->

<!-- <img> icon -->
<script type="text/html" id="sum-icon-img-tpl" class="su-maker-editor-js-template">
	<img src="%ICON%" alt="">
</script>
<!-- /<img> icon -->


<!-- ========================== -->
<!-- Attributes field templates -->
<!-- ========================== -->

<!-- General template -->
<script type="text/html" id="sum-attributes-tpl" class="su-maker-editor-js-template">
	<div class="sum-attributes-head wp-clearfix">
		<div class="sum-attributes-head-label">%label%</div>
		<div class="sum-attributes-head-type">%type%</div>
		<div class="sum-attributes-head-default">%default%</div>
	</div>
	<div class="sum-attributes-items" data-placeholder="%none%"></div>
	<div class="sum-attributes-add">
		<a href="javascript:;" class="dashicons-before dashicons-plus button">%add%</a>
	</div>
</script>
<!-- /General template -->

<!-- Single attribute item -->
<script type="text/html" id="sum-attributes-item-tpl" class="su-maker-editor-js-template">
	<div class="sum-attributes-item">
		<div class="sum-attributes-item-restore">%deleted% <a href="javascript:;">%restore%</a></div>
		<div class="sum-attributes-item-head wp-clearfix">
			<div class="sum-attributes-item-head-label">
				<a href="javascript:;" data-no-label="(%noLabel%)"></a>
				<div class="sum-attributes-item-head-actions">
					<a href="javascript:;" class="sum-attributes-item-head-toggle" data-label-open="%edit%" data-label-closed="%close%"></a> |
					<a href="javascript:;" class="sum-attributes-item-head-delete">%delete%</a>
				</div>
			</div>
			<div class="sum-attributes-item-head-type"></div>
			<div class="sum-attributes-item-head-default" data-no-default="(%noDefault%)"></div>
		</div>
		<div class="sum-attributes-item-settings">
			<table class="form-table">

				<tr>
					<th colspan="row">
						<label for="sum-attributes-item-label-%index%">%label%</label>
					</th>
					<td>
						<input type="text" data-name="name" id="sum-attributes-item-label-%index%" class="sum-attributes-item-label regular-text">
						<p class="description">%labelDesc%</p>
					</td>
				</tr>

				<tr>
					<th colspan="row">
						<label for="sum-attributes-item-name-%index%">%name%</label>
					</th>
					<td>
						<input type="text" data-name="slug" id="sum-attributes-item-name-%index%" class="sum-attributes-item-name regular-text">
						<p class="description sum-validation-failed-message">%invalidName%</p>
						<p class="description sum-validation-required-message">%emptyName%</p>
						<p class="description">%nameDesc1%</p>
						<p class="description">%nameDesc2%</p>
						<p class="description">%nameDesc3%</p>
					</td>
				</tr>

				<tr>
					<th colspan="row">
						<label for="sum-attributes-item-type-%index%">%type%</label>
					</th>
					<td>
						<select data-name="type" id="sum-attributes-item-type-%index%" class="sum-attributes-item-type"></select>
						<p class="description">%typeDesc%</p>
					</td>
				</tr>

				<tr class="sum-attributes-item-options-container">
					<th colspan="row">
						<label for="sum-attributes-item-options-%index%">%options%</label>
					</th>
					<td>
						<textarea data-name="options" id="sum-attributes-item-options-%index%" class="sum-attributes-item-options large-text" rows="7"></textarea>
						<p class="description">%optionsDesc%</p>
					</td>
				</tr>

				<tr>
					<th colspan="row">
						<label for="sum-attributes-item-default-%index%">%default%</label>
					</th>
					<td>
						<textarea data-name="default" id="sum-attributes-item-default-%index%" rows="1" class="sum-attributes-item-default regular-text"></textarea>
						<p class="description">%defaultDesc%</p>
					</td>
				</tr>

				<tr>
					<th colspan="row">
						<label for="sum-attributes-item-desc-%index%">%desc%</label>
					</th>
					<td>
						<textarea data-name="desc" id="sum-attributes-item-desc-%index%" class="sum-attributes-item-desc large-text" rows="2"></textarea>
						<p class="description">%descDesc%</p>
					</td>
				</tr>

				<tr class="sum-attributes-item-min-container">
					<th colspan="row">
						<label for="sum-attributes-item-min-%index%">%min%</label>
					</th>
					<td>
						<input type="text" data-name="min" id="sum-attributes-item-min-%index%" class="sum-attributes-item-min small-text">
					</td>
				</tr>

				<tr class="sum-attributes-item-max-container">
					<th colspan="row">
						<label for="sum-attributes-item-max-%index%">%max%</label>
					</th>
					<td>
						<input type="text" data-name="max" id="sum-attributes-item-max-%index%" class="sum-attributes-item-max small-text">
					</td>
				</tr>

				<tr class="sum-attributes-item-step-container">
					<th colspan="row">
						<label for="sum-attributes-item-step-%index%">%step%</label>
					</th>
					<td>
						<input type="text" data-name="step" id="sum-attributes-item-step-%index%" value="" class="sum-attributes-item-step small-text">
					</td>
				</tr>

			</table>
		</div>
		<div class="sum-attributes-item-actions">
			<a href="javascript:;" class="sum-attributes-item-toggle button button-primary">%closeAttribute%</a>
		</div>
	</div>
</script>
<!-- /Single attribute item -->

<!-- Option -->
<script type="text/html" id="sum-attributes-option-tpl" class="su-maker-editor-js-template">
	<option value="%value%">%label%</option>
</script>
<!-- /Option -->

<!-- ==================== -->
<!-- Code field templates -->
<!-- ==================== -->

<!-- Variable -->
<script type="text/html" id="sum-code-variable-tpl" class="su-maker-editor-js-template">
	<li><a href="javascript:;"></a></li>
</script>
<!-- /Variable -->

<!-- Toggle fullscreen -->
<script type="text/html" id="sum-code-fullscreen-tpl" class="su-maker-editor-js-template">
	<a href="javascript:;" class="dashicons-before dashicons-editor-expand sum-code-editor-toggle-fullscreen"></a>
</script>
<!-- /Toggle fullscreen -->
