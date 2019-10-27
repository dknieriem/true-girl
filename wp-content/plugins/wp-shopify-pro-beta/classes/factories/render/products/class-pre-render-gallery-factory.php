<?php

namespace WPS\Factories\Render\Products;

defined('ABSPATH') ?: die;

use WPS\Render\Products\Pre_Render_Gallery;
use WPS\Factories;

class Pre_Render_Gallery_Factory {

	protected static $instantiated = null;

	public static function build() {

		if (is_null(self::$instantiated)) {

			self::$instantiated = new Pre_Render_Gallery();

		}

		return self::$instantiated;

	}

}
