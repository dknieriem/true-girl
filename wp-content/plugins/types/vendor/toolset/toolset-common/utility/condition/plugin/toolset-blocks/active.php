<?php

/**
 * Toolset_Condition_Plugin_Toolset_Blocks_Active
 *
 * @since 3.3.8
 */
class Toolset_Condition_Plugin_Toolset_Blocks_Active implements Toolset_Condition_Interface {

	public function is_met() {
		if ( defined( 'TB_VERSION' ) ) {
			return true;
		}

		return false;
	}

}
