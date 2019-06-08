<?php

/**
 * Toolset_Condition_Plugin_Elementor_Pro_Active
 *
 * @since 3.0.7
 */
class Toolset_Condition_Plugin_Elementor_Pro_Active implements Toolset_Condition_Interface {

	public function is_met() {
		return did_action( 'elementor_pro/init' );
	}
}
