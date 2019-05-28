<?php if ( empty( $questions ) ) {
	return;
} ?>
<div class="tqb-question-wrapper">
	<?php foreach ( $questions as $question ) : ?>
		<div class="tqb-question-container">
			<div class="tqb-question-text">
				<?php echo $question['text']; ?>
			</div>

			<?php if ( $question['description'] ) : ?>
				<div class="tqb-question-description">
					<?php echo $question['description']; ?>
				</div>
			<?php endif; ?>

			<?php if ( $question['image'] ) : ?>
				<div class="tqb-question-image-container">
					<img src="<?php echo $question['image']; ?>" alt="question-image">
				</div>
			<?php endif; ?>

		</div>
		<div class="tqb-answers-container <?php if ( $question['answers'][0]['image'] ) : ?> tqb-answer-has-image <?php endif; ?>">
			<?php foreach ( $question['answers'] as $answer ) : ?>
				<div class="tqb-answer-inner-wrapper">
					<div class="tqb-answer-action">
						<?php if ( $answer['image'] ) : ?>
							<div class="tqb-answer-image-type">
								<div class="tqb-answer-image-container">
									<img src="<?php echo $answer['image']; ?>" alt="" class="tqb-answer-image">
								</div>
								<div class="tqb-answer-text-container">
									<div class="tqb-answer-text">
										<?php echo $answer['text']; ?>
									</div>
								</div>
							</div>
						<?php else : ?>
							<div class="tqb-answer-text-type">
								<div class="tqb-answer-text">
									<?php echo $answer['text']; ?>
								</div>
							</div>
						<?php endif; ?>

					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php break; ?>
	<?php endforeach; ?>
</div>
