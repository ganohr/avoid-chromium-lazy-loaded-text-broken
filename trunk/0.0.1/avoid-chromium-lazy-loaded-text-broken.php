<?php
/*
Plugin Name: Avoid the chromium lazy loading broken characters bug
Plugin URI: https://ganohr.net/blog/avoid-the-chromium-lazy-loading-broken-characters-bug/
Description: Avoid the chromium lazy loading broken characters bug.
Version: 0.0.1
Author: Ganohr
Author URI: https://ganohr.net/
License: GPL2
*/
?>
<?php
/*
  Copyright 2018 Ganohr (email : ganohr@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	 published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
// 直接呼び出しは禁止
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! defined( 'GANOHRS_AVOID_CLLTB_SIGNATURE' ) ) {
	define(
		'GANOHRS_AVOID_CLLTB_SIGNATURE',
		'avoid-chromium-lazy-loaded-text-broken'
	);
}

// 関数がなければ定義する
if ( ! function_exists( 'ganohrs_avoid_clltb_function' ) ) :

	function ganohrs_avoid_clltb_function( $the_content ) {
		if ( gnr_is_amp() ) {
			return $the_content;
		}
		$pattern = '#<strong class="gaclltb_dummy_area"></strong>#';
		if ( preg_match( $pattern, $the_content ) ) {
			return $the_content;
		}
		$result = preg_replace(
			'#(</h[1-6]>|</p>|</div>|</span>)#',
			'$1<strong class="gaclltb_dummy_area"></strong>',
			$the_content
		);
		if ( false === $result ) {
			return $the_content;
		}

		return $result;
	}
	add_action( 'the_content', 'ganohrs_avoid_clltb_function', 9999 );

	function ganohrs_avoid_clltb_add_defer( $tag, $handle ) {
		if ( $handle !== GANOHRS_AVOID_CLLTB_SIGNATURE ) {
			return $tag;
		}
		return str_replace( ' src=', ' defer src=', $tag );
	}

	add_filter( 'script_loader_tag', 'ganohrs_avoid_clltb_add_defer', 10, 2 );

	function ganohrs_avoid_clltb_add_scripts() {
		wp_register_script(
			GANOHRS_AVOID_CLLTB_SIGNATURE,
			plugins_url() . '/' . GANOHRS_AVOID_CLLTB_SIGNATURE . '/' . GANOHRS_AVOID_CLLTB_SIGNATURE . '.js',
			array( 'jquery-core' )
		);
		wp_enqueue_script(
			GANOHRS_AVOID_CLLTB_SIGNATURE,
			plugins_url() . '/' . GANOHRS_AVOID_CLLTB_SIGNATURE . '/' . GANOHRS_AVOID_CLLTB_SIGNATURE . '.js',
			array( 'jquery-core' )
		);
	}
	add_action( 'wp_enqueue_scripts', 'ganohrs_avoid_clltb_add_scripts' );

endif;

// AMPページか否か判定する
if ( ! function_exists( 'gnr_is_amp' ) ) :

	function gnr_is_amp() {
		if ( function_exists( 'is_amp' ) && is_amp() ) {
			return true;
		} elseif ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			return true;
		} elseif ( function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint() ) {
			return true;
		} elseif ( '1' === @$_GET['amp'] ) {
			return true;
		} elseif ( 'AMP' === @$_GET['type'] ) {
			return true;
		}
		$uri = gnr_get_uri_full();
		return gnr_is_amp_pattern( $uri );
	}
	function gnr_get_uri_full() {
		return ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http' )
			. '://'
			. $_SERVER['SERVER_NAME']
			. $_SERVER['REQUEST_URI'];
	}
	function gnr_tail_pattern_matched( $target, $pattern ) {
		if ( empty( $target ) && empty( $pattern ) ) {
			return true;
		} elseif ( empty( $target ) ) {
			return false;
		} elseif ( empty( $pattern ) ) {
			return false;
		}
		$s_end	= strlen( $target );
		$s_len	= strlen( $pattern );
		$offset = $s_end - $s_len;
		if ( $offset < 0 ) {
			return false;
		}
		$pos = strpos( $target, $pattern, $offset );
		return $pos === $offset;
	}
	function gnr_is_amp_pattern( $uri ) {
		if ( gnr_tail_pattern_matched( $uri, '/amp' ) ) {
			return true;
		}
		if ( gnr_tail_pattern_matched( $uri, '/amp/' ) ) {
			return true;
		}
		if ( gnr_tail_pattern_matched( $uri, '?amp=1' ) ) {
			return true;
		}
		if ( gnr_tail_pattern_matched( $uri, 'type=AMP' ) ) {
			return true;
		}
		return false;
	}
	function gnr_remove_amp_uri_part( $uri, $pattern ) {
		$s_end	= strlen( $uri );
		$s_len	= strlen( $pattern );
		$offset = $s_end - $s_len;
		if ( $offset < 0 ) {
			return $uri;
		}
		$pos = strpos( $uri, $pattern, $offset );
		if ( $pos === $offset ) {
			return substr( $uri, 0, $pos );
		}
		return $uri;
	}
	function gnr_remove_amp_pattern( $uri ) {
		$uri = gnr_remove_amp_uri_part( $uri, '/amp' );
		$uri = gnr_remove_amp_uri_part( $uri, '/amp/' );
		$uri = gnr_remove_amp_uri_part( $uri, '?amp=1' );
		$uri = gnr_remove_amp_uri_part( $uri, 'type=AMP' );
		return $uri;
	}
	function gnr_get_amp_pattern( $uri ) {
		$ret = '/amp';
		if ( gnr_tail_pattern_matched( $uri, $ret ) ) {
			return $ret;
		}
		$ret = '/amp/';
		if ( gnr_tail_pattern_matched( $uri, $ret ) ) {
			return $ret;
		}
		$ret = '/?amp=1';
		if ( gnr_tail_pattern_matched( $uri, $ret ) ) {
			return $ret;
		}
		$ret = 'type=AMP';
		if ( gnr_tail_pattern_matched( $uri, $ret ) ) {
			return $ret;
		}
	}
endif;


