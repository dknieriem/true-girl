<?php

class Toolset_Compatibility_Theme_avada extends Toolset_Compatibility_Theme_Handler{

    protected function run_hooks() {
	    $this->set_inline_css_mode();
    }

	/**
	 * Force Avada css mode to inline instead of file, this solves the problem with caching
	 */
    public function set_inline_css_mode(){
    	if( !defined('FUSION_DISABLE_COMPILERS') ){
		    define('FUSION_DISABLE_COMPILERS', true);
	    }
    }
}