<?php
/**
 * The template for displaying search forms in Underscores.me
 *
 * @package understrap
 */

?>
<form method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search" class="search-form">
	<div class="search-form__input-wrapper">
		<input class="search-form__search-input" id="s" name="s" type="text"
			placeholder="<?php esc_attr_e( 'How to...', 'understrap' ); ?>" value="<?php the_search_query(); ?>">
		<!-- <input class="search-form__search-button" id="searchsubmit" name="submit" type="submit"
			value="Go"> -->
			<button class="search-form__search-button" id="searchsubmit" name="submit">
				search
			</button>
	</div>
</form>
