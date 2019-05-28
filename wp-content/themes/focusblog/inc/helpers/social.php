<?php

add_action( 'wp_ajax_thrive_get_share_counts', 'thrive_social_ajax_share_counts' );
add_action( 'wp_ajax_nopriv_thrive_get_share_counts', 'thrive_social_ajax_share_counts' );

add_action( 'tve_dash_main_ajax_theme_shares', 'thrive_dash_social_ajax_share_counts', 10, 2 );

/**
 * return share counts to the dashboard main ajax call
 *
 * @param array $current
 * @param array $post_data
 */
function thrive_dash_social_ajax_share_counts( $current, $post_data ) {
	return thrive_social_ajax_share_counts( true, $post_data );
}

/**
 * AJAX - based count for social shares
 *
 * @param bool $return
 * @param array $post_data
 *
 * @return array|void
 */
function thrive_social_ajax_share_counts( $return = false, $post_data = array() ) {
	$post_data = null !== $post_data ? $post_data : $_POST;
	$url       = isset( $post_data['url'] ) ? $post_data['url'] : '';
	$networks  = isset( $post_data['networks'] ) ? $post_data['networks'] : array();

	$networks = array_intersect( $networks, array(
		'fb_share',
		't_share',
		'g_share',
		'pin_share',
		'in_share',
		'xing_share'
	) );

	if ( empty( $url ) || empty( $networks ) || ! is_array( $networks ) ) {
		if ( $return ) {
			return array( 'total' => 0 );
		}
		wp_send_json( array( 'total' => 0 ) );
	}

	$counts = array();

	$post_id = url_to_postid( $url );
	if ( empty( $post_id ) ) {
		foreach ( $networks as $network ) {
			$counts[ $network ] = call_user_func( 'thrive_social_fetch_count_' . $network, $url );
		}
	} else {
		$counts = thrive_social_get_share_count( $post_id, get_permalink( $post_id ), $networks );
	}
	$total = 0;

	foreach ( $networks as $n ) {
		$total += ( isset( $counts[ $n ] ) ? $counts[ $n ] : 0 );
	}

	if ( $return ) {
		return array( 'total' => $total );
	}
	wp_send_json( array( 'total' => $total ) );

}

/**
 * get the cached share count (or, if expired, fetch the count from the API for the network)
 *
 * @param mixed|null $post_id
 * @param string $post_permalink optional, if passed in will be used instead of get_permalink
 * @param array|string $networks the network to fetch the share count for (can also be an array)
 * @param bool $force_fetch if true, it will bypass the cache and make the API request
 *
 * Allowed values for the network / network keys:
 *  'fb_share',
 *  't_share',
 *  'g_share',
 *  'pin_share',
 *  'in_share',
 *  'xing_share'
 *
 * @return array
 * [fb_share] => $count
 * [t_share] => $count
 * ..
 */
function thrive_social_get_share_count( $post_id, $post_permalink = null, $networks = null, $force_fetch = false ) {
	/* half an hour */
	$cache_lifetime = apply_filters( 'thrive_cache_shares_lifetime', 300 );

	/* the share count is returned for a URL */
	if ( null === $post_permalink ) {
		$post_permalink = get_permalink( $post_id );
	}

	/**
	 * all possible networks
	 */
	$all_networks = array(
		'fb_share',
		't_share',
		'g_share',
		'pin_share',
		'in_share',
		'xing_share'
	);

	/* make sure the $networks will be an array */
	if ( $networks !== null ) {
		$networks = is_array( $networks ) ? $networks : array( $networks );
		$networks = array_intersect( $networks, $all_networks );
	} else {
		$networks = $all_networks;
	}

	$count = get_post_meta( $post_id, 'thrive_ss_count', true );

	/* if no cache or if the URL has changed => re-fetch the whole thing */
	if ( empty( $count ) || $count['url'] != $post_permalink ) {
		$count       = array();
		$force_fetch = true;
	}

	/* cache expired => re-fetch */
	if ( ! empty( $count['last_fetch'] ) && $count['last_fetch'] < time() - $cache_lifetime ) {
		$force_fetch = true;
	}

	/* check to see if we have all of the required networks added to cache */
	if ( ! $force_fetch && ! empty( $count ) ) {

		foreach ( $networks as $network ) {
			if ( ! isset( $count[ $network ] ) ) {
				$force_fetch = true;
				break;
			}
		}
	}

	if ( $force_fetch ) {
		$count['url']        = $post_permalink;
		$count['last_fetch'] = time();
		foreach ( $networks as $network ) {
			$shares = call_user_func( 'thrive_social_fetch_count_' . $network, $post_permalink );
			/* do not set the share count if it already exists and the value received from API is 0 */
			if ( isset( $count[ $network ] ) && empty( $shares ) ) {
				continue;
			}
			$count[ $network ] = $shares;
		}
		update_post_meta( $post_id, 'thrive_ss_count', $count );
	}

	unset( $count['url'] );
	unset( $count['last_fetch'] );

	return $count;
}


/**
 * fetch and decode a JSON response from a URL
 *
 * @param string $url
 * @param string $fn
 *
 * @return array
 */
function _thrive_social_helper_get_json( $url, $fn = 'wp_remote_get' ) {
	$response = $fn( $url, array( 'sslverify' => false ) );
	if ( $response instanceof WP_Error ) {
		return array();
	}

	$body = wp_remote_retrieve_body( $response );
	if ( empty( $body ) ) {
		return array();
	}

	$data = json_decode( $body, true );

	return empty( $data ) ? array() : $data;
}

/**
 * fetch the FB total number of shares for an url
 *
 * @param string $url
 *
 * @return int
 */
function thrive_social_fetch_count_fb_share( $url ) {
	return tve_dash_fetch_share_count_facebook( $url );
}

/**
 * fetch the total number of shares for an url from twitter
 *
 * @param string $url
 *
 * @return int
 */
function thrive_social_fetch_count_t_share( $url ) {
	return 0;
}

/**
 * fetch the total number of shares for an url from Pinterest
 *
 * @param string $url
 *
 * @return int
 */
function thrive_social_fetch_count_pin_share( $url ) {
	$response = wp_remote_get( 'http://api.pinterest.com/v1/urls/count.json?callback=_&url=' . rawurlencode( $url ), array(
		'sslverify' => false
	) );

	$body = wp_remote_retrieve_body( $response );
	if ( empty( $body ) ) {
		return 0;
	}
	$body = preg_replace( '#_\((.+?)\)$#', '$1', $body );
	$data = json_decode( $body, true );

	return empty( $data['count'] ) ? 0 : (int) $data['count'];
}

/**
 * fetch the total number of shares for an url from LinkedIn
 *
 * @param string $url
 *
 * @return int
 */
function thrive_social_fetch_count_in_share( $url ) {
	$data = _thrive_social_helper_get_json( 'http://www.linkedin.com/countserv/count/share?format=json&url=' . rawurlencode( $url ) );

	return empty( $data['count'] ) ? 0 : (int) $data['count'];
}

/**
 * fetch the total number of shares for an url from Google
 *
 * @param string $url
 *
 * @return int
 */
function thrive_social_fetch_count_g_share( $url ) {
	$response = wp_remote_post( 'https://clients6.google.com/rpc', array(
		'sslverify' => false,
		'headers'   => array(
			'Content-type' => 'application/json'
		),
		'body'      => json_encode( array(
			array(
				'method'     => 'pos.plusones.get',
				'id'         => 'p',
				'params'     => array(
					'nolog'   => true,
					'id'      => $url,
					'source'  => 'widget',
					'userId'  => '@viewer',
					'groupId' => '@self',
				),
				'jsonrpc'    => '2.0',
				'key'        => 'p',
				'apiVersion' => 'v1'
			)
		) )
	) );

	if ( $response instanceof WP_Error ) {
		return 0;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( empty( $data ) || ! isset( $data[0]['result']['metadata']['globalCounts'] ) ) {
		return 0;
	}

	return (int) $data[0]['result']['metadata']['globalCounts']['count'];
}


/**
 * fetch the total number of shares for an url from Xing
 *
 * @param string $url
 *
 * @return int
 */
function thrive_social_fetch_count_xing_share( $url ) {
	$response = wp_remote_get( 'https://www.xing-share.com/app/share?op=get_share_button;counter=top;url=' . rawurlencode( $url ), array(
		'sslverify' => false
	) );

	if ( $response instanceof WP_Error ) {
		return 0;
	}

	$html = wp_remote_retrieve_body( $response );

	if ( ! preg_match_all( '#xing-count(.+?)(\d+)(.*?)</span>#', $html, $matches, PREG_SET_ORDER ) ) {
		return 0;
	}

	return (int) $matches[0][2];
}