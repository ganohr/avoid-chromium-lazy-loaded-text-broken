<?php
/*
Plugin Name: Avoid the chromium lazy loading broken characters bug
Plugin URI: https://ganohr.net/blog/avoid-the-chromium-lazy-loading-broken-characters-bug/
Description: Avoid the chromium lazy loading broken characters bug.
Version: 0.0.4
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
		'avoid-the-chromium-lazy-loading-broken-characters-bug'
	);
}

// 関数がなければ定義する
if ( ! function_exists( 'gnraclltb_append_dumy_elems' ) ) :

	function gnraclltb_append_dumy_elems( $the_content ) {
		if ( gnraclltb_is_amp() ) {
			return $the_content;
		}
		$dummy_elem_tag = '<b class="gaclltb_dummy"></b>';
		$pattern = "#$dummy_elem_tag#";
		if ( preg_match( $pattern, $the_content ) ) {
			return $the_content;
		}
		$result = preg_replace(
			'#(</h[1-6]>|</p>|</div>|</span>|</table>)#',
			"$1$dummy_elem_tag",
			$the_content
		);
		if ( false === $result ) {
			return $the_content;
		}

		return $result;
	}
	add_action( 'the_content', 'gnraclltb_append_dumy_elems', 9999 );

	function gnraclltb_loader_tag( $tag, $handle ) {
		if ( $handle !== GANOHRS_AVOID_CLLTB_SIGNATURE ) {
			return $tag;
		}
		return str_replace( ' src=', ' defer src=', $tag );
	}
	add_filter( 'script_loader_tag', 'gnraclltb_loader_tag', 10, 2 );

	function gnraclltb_enqueue_scripts() {
		$depend = array( 'jquery-core' );
		$js = plugins_url()
			. '/' . GANOHRS_AVOID_CLLTB_SIGNATURE
			. '/' . GANOHRS_AVOID_CLLTB_SIGNATURE . '.js';
		wp_register_script( GANOHRS_AVOID_CLLTB_SIGNATURE, $js, $depend );
		wp_enqueue_script ( GANOHRS_AVOID_CLLTB_SIGNATURE, $js, $depend );
	}
	add_action( 'wp_enqueue_scripts', 'gnraclltb_enqueue_scripts' );

endif;

// AMPページか否か判定する
if ( ! function_exists( 'gnraclltb_is_amp' ) ) :

	function gnraclltb_is_amp() {
		if ( function_exists( 'is_amp' ) && is_amp() ) {
			return true;
		} elseif ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			return true;
		} elseif ( function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint() ) {
			return true;
		} elseif ( array_key_exists( 'amp', $_GET ) && '1' === $_GET['amp'] ) {
			return true;
		} elseif ( array_key_exists( 'type', $_GET ) && 'AMP' === $_GET['type'] ) {
			return true;
		}
		return false;
	}
endif;
