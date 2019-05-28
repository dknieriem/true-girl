<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 1/16/2017
 * Time: 2:52 PM
 */
$post_type = get_post_type();

if ( empty( $post_type ) || ( $post_type != 'post' && $post_type != 'page' ) ) {
	return;
}

$quizzes = TQB_Quiz_Manager::get_valid_quizzes();
?>

<div class="tve_option_separator tve_clearfix" title="<?php echo __( 'Thrive Quiz Builder Shortcodes', Thrive_Quiz_Builder::T ) ?>">
	<div class="tve_icm tve-ic-my-library-books tve_left"></div>
	<span class="tve_expanded tve_left"><?php echo __( 'Quiz Shortcodes', Thrive_Quiz_Builder::T ) ?></span>
	<span class="tve_caret tve_icm tve_sub_btn tve_right tve_expanded"></span>

	<div class="tve_clear"></div>
	<div class="tve_sub_btn" title="<?php echo __( 'Thrive Quiz Builder', Thrive_Quiz_Builder::T ) ?>">
		<div class="tve_sub">
			<ul>
				<?php if ( count( $quizzes ) ) : ?>
					<li class="cp_draggable" data-elem="sc_thrive_quiz_builder_shortcode">
						<div class="tve_icm tve-ic-plus"></div>
						<?php echo __( 'Quizzes', Thrive_Quiz_Builder::T ); ?>
					</li>
				<?php else : ?>
					<li><?php echo __( 'No quizzes available', Thrive_Quiz_Builder::T ) ?></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</div>

