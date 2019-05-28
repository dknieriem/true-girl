<?php
/**
 * The search-form template.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

$is_live_search = Avada()->settings->get( 'live_search' );
?>
<form role="search" class="searchform fusion-search-form fusion-live-search" method="get" action="<?php echo esc_url_raw( home_url( '/' ) ); ?>">
	<div class="fusion-search-form-content">
		<div class="fusion-search-field search-field">
			<label><span class="screen-reader-text"><?php esc_attr_e( 'Search for:', 'Avada' ); ?></span>
				<?php if ( $is_live_search ) : ?>
					<input type="text" class="s fusion-live-search-input" name="s" id="fusion-live-search-input" autocomplete="off" placeholder="<?php esc_html_e( 'Search ...', 'Avada' ); ?>" required aria-required="true" aria-label="<?php esc_html_e( 'Search ...', 'Avada' ); ?>"/>
				<?php else : ?>
					<input type="text" value="" name="s" class="s" placeholder="<?php esc_html_e( 'Search ...', 'Avada' ); ?>" required aria-required="true" aria-label="<?php esc_html_e( 'Search ...', 'Avada' ); ?>"/>
				<?php endif; ?>
			</label>
		</div>
		<div class="fusion-search-button search-button">
			<input type="submit" class="fusion-search-submit searchsubmit" value="&#xf002;" />
			<?php if ( $is_live_search ) : ?>
			<div class="fusion-slider-loading"></div>
			<?php endif; ?>
		</div>
	</div>
	<?php if ( $is_live_search ) : ?>
		<div class="fusion-search-results-wrapper"><div class="fusion-search-results"></div></div>
	<?php endif; ?>
</form>
