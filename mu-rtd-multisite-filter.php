<?php
/*
 * Plugin Name: RTD Multisite Filter
 * Plugin URI: https://redtaildesign.com
 * Description: Allows you to search/filter a large number of sites in a multisite network.
 * Version: 1.0
 * Author: Will Presley
 * Author URI: https://redtaildesign.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * TextDomain: rtdbase
 * DomainPath:
 * Network: true
 */

// Previously, these non-admin scripts were being enqueued even for non-logged-in users.
function enqueue_stuff()
{
    if(is_user_logged_in())
    {
	add_action( 'admin_bar_menu',        'wc_mf_admin_bar_menu' );
	add_action( 'admin_enqueue_scripts', 'wc_mf_enqueue_styles' );
	add_action( 'admin_enqueue_scripts', 'wc_mf_enqueue_scripts' );
	add_action( 'wp_enqueue_scripts',    'wc_mf_enqueue_styles' );
	add_action( 'wp_enqueue_scripts',    'wc_mf_enqueue_scripts' );
    }
}
add_action('init', 'enqueue_stuff');

/**
 * Add search field menu item
 *
 * @param WP_Admin_Bar $wp_admin_bar
 * @return void
 */
function wc_mf_admin_bar_menu( $wp_admin_bar ) {

	$total_users_sites = count( $wp_admin_bar->user->blogs );
	$show_if_gt        = apply_filters( 'mms_show_search_minimum_sites', 10 );

	if ( ! is_user_logged_in() || ( $total_users_sites < $show_if_gt ) ) {
		return;
	}

	$wp_admin_bar->add_menu( array(
		'parent' => 'my-sites-list',
		'id'     => 'my-sites-search',
		'title'  => sprintf(
			'<label for="my-sites-search-text">%s</label><input type="text" id="my-sites-search-text" placeholder="%s" />',
			esc_html__( 'Filter My Sites', 'mss' ),
			esc_attr__( 'Search Sites', 'mss' )
		),
		'meta'   => array(
			'class' => 'hide-if-no-js'
		)
	) );
}

/**
 * Enqueue styles
 * Inline styles with admin-bar dependency
 *
 * @return void
 */
function wc_mf_enqueue_styles() {
	ob_start();
	?>
#wp-admin-bar-my-sites-search.hide-if-no-js {
	display: none;
}
#wp-admin-bar-my-sites-search label[for="my-sites-search-text"] {
	clip: rect(1px, 1px, 1px, 1px);
	position: absolute !important;
	height: 1px;
	width: 1px;
	overflow: hidden;
}
#wp-admin-bar-my-sites-list {
    direction: rtl;
    padding-top: 38px;
    overflow-y: auto;
    overflow-x: visible;
    max-height: 75%;
    max-height: 75vh;
}
#wp-admin-bar-my-sites-list li {
    direction: ltr;
}
#wp-admin-bar-my-sites-search {
	height: 38px;
    position: absolute;
    top: 0;
    width: 100%;
}
#wp-admin-bar-my-sites-search .ab-item {
	height: 34px;
}
#wpadminbar .menupop li.hover {
    position: relative;
}
#wpadminbar .menupop li.hover > .ab-sub-wrapper {
    margin-top: 0;
    margin-left: 336px;
    position: fixed;
}
#wpadminbar .menupop li > .ab-sub-wrapper.fixed {
    position: fixed;
}
#wp-admin-bar-my-sites-search input {
	padding: 0 2px;
	width: 95%;
	width: calc( 100% - 4px );
}
#wp-admin-bar-my-sites-list .blavatar {
    vertical-align: text-bottom !important;
}
#wp-admin-bar-my-sites-list .blavatar:before {
    margin-bottom: 0 !important;
}
#wpadminbar .menupop li#wp-admin-bar-network-admin.hover > .ab-sub-wrapper {
    margin-top: -32px;
    margin-left: 353px;
}
	<?php
	$style = ob_get_clean();
	wp_enqueue_style( 'admin-bar' );
	wp_add_inline_style( 'admin-bar', $style );
}

/**
 * Enqueue JavaScript
 * Inline script with jQuery dependency
 *
 * @return void
 */
function wc_mf_enqueue_scripts() {
	ob_start();
	?>
var waitForFinalEvent = (function () {
    var timers = {};
    return function (callback, ms, uniqueId) {
        if (!uniqueId) { uniqueId = "Don't call this twice without a uniqueId"; }
        if (timers[uniqueId]) { clearTimeout (timers[uniqueId]); }
        timers[uniqueId] = setTimeout(callback, ms);
    };
})();
// how long to wait before deciding the resize has stopped, in ms. Around 50-100 should work ok.
var timeToWaitForLast = 200;

jQuery(document).ready( function($) {
	$('#wp-admin-bar-my-sites-search.hide-if-no-js').show();
	$('#wp-admin-bar-my-sites-search input').keyup( function( ) {
		var searchValRegex = new RegExp( $(this).val(), 'i');
		$('#wp-admin-bar-my-sites-list > li.menupop').hide().filter(function() {
			return searchValRegex.test( $(this).find('> a').text() );
		}).show();
	});

    $( "#wp-admin-bar-my-sites-list > li.menupop" ).hover(
        function() {
            var windowTop   = $(window).scrollTop();
            var fromTop     = $( this ).offset().top;
            var childTop    = fromTop - windowTop;
            $( this ).find('.ab-sub-wrapper').css('top', childTop);
        }, function() {
            /*$( this ).find('.ab-sub-wrapper').css('top', 'unset');*/
        }
    );
});
	<?php
	$script = ob_get_clean();
	wp_enqueue_script( 'jquery-core' );
	wp_add_inline_script( 'jquery-core', $script );
}
