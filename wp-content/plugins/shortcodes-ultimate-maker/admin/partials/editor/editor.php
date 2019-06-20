<?php defined( 'ABSPATH' ) or exit; ?>

<div class="sum-editor">

	<table class="sum-form form-table">

		<?php foreach ( $this->get_fields() as $field ) : ?>

			<tr>
				<th scope="row">
					<label for="sum-<?php echo esc_attr( $field['id'] ); ?>"><?php echo $field['title']; ?></label>
				</th>
				<td>
					<?php $this->the_template( 'fields/' . $field['id'] ); ?>
				</td>
			</tr>

		<?php endforeach; ?>

	</table>

	<?php if ( get_post_status( get_the_ID() ) === 'publish' ) : ?>

		<div class="sum-actions">
			<?php submit_button( __( 'Update shortcode', 'shortcodes-ultimate-maker' ), 'primary', 'save', false ); ?>
		</div>

	<?php else : ?>

		<div class="sum-actions">
			<?php submit_button( __( 'Create shortcode', 'shortcodes-ultimate-maker' ), 'primary', 'publish', false ); ?>
			<?php submit_button( __( 'Save draft', 'shortcodes-ultimate-maker' ), '', 'save', false ); ?>
		</div>

	<?php endif; ?>

	<?php $this->the_template( 'js-templates' ); ?>

	<?php wp_nonce_field( 'save', 'sumk_nonce' ); ?>

</div>
