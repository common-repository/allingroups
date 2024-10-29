<?php
/**
 * Plugin Name: Allingroups
 * Plugin URI: http://allingroups.com/
 * Description: Un plugin pour utiliser le service allingroups, service permettant la publication automatisée de vos posts et articles dans vos groupes Facebook
 * Version: 1.0
 * Author: linterweb
 * Author URI: http://linterweb.fr
 * Text Domain: allingroups
 * Domain Path: /lang/
 * License: GPLv2 or later
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Allingroups
{

	public function __construct()
	{
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'allingroups_settings_link') );
		add_action( 'admin_menu', array( $this, 'allingroups_custom_admin_menu') );
		add_action( 'admin_init', array( $this, 'allingroups_settings_api_init') );
		add_action( 'admin_init', array( $this, 'allingroups_register_settings') );
		add_action( 'post_submitbox_misc_actions', array( $this, 'allingroups_article_in') );
		add_action( 'save_post', array( $this, 'allingroups_save_article_in') );
		add_action( 'plugins_loaded', array( $this, 'allingroups_load_textdomain') );
		
		/* AJAX hooks */
		add_action( 'wp_ajax_allingroups_api_call', array( $this, 'allingroups_api_call' ) );
		add_action( 'wp_ajax_nopriv_allingroups_api_call', array( $this, 'allingroups_api_call' ) );
		
	}

	public function allingroups_load_textdomain() {
		load_plugin_textdomain( 'allingroups', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	public function allingroups_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=allingroups-plugin">Settings</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	public function allingroups_custom_admin_menu() {
		add_options_page( 'Allingroups', 'Allingroups settings', 'read', 'allingroups-plugin', array( $this, 'allingroups_options_page') );
	}

	public function allingroups_settings_api_init() {
		add_settings_section( 'allingroups_options', 'Allingroups options', array( $this, 'allingroups_options_callback'), 'allingroups-plugin');
		register_setting('allingroups-plugin', array( $this, 'allingroups_options') );
	}

	public function allingroups_options_callback() {
		echo '';
	}

	public function allingroups_register_settings() {
		register_setting( 'allingroups_options', 'allingroups_email', 'sanitize_email' );
		register_setting( 'allingroups_options', 'allingroups_wp_identifier', 'sanitize_key' );
		register_setting( 'allingroups_options', 'allingroups_playlist', 'intval' );
		register_setting( 'allingroups_options', 'allingroups_teaser', 'intval' );
		register_setting( 'allingroups_options', 'allingroups_auto', 'intval' );
	}

	public function allingroups_options_page() {
		
		if( !current_user_can( 'read' ) )	{
			wp_die( __('You do not have sufficient permissions to access this page.', 'allingroups') );
		}
		
		require_once( 'views/admin_page.php' );
		
	}
	
	public function allingroups_api_call(){
		
		$body = array();
		$valid_actions = array( 'wpconnect' , 'wpgetplaylist' , 'wpgetgroup' , 'wpcreateplaylist' , 'wpremove' );
		
		// Build body
		
		if (
			isset( $_POST['activity'] ) 
			&& in_array( $_POST['activity'], $valid_actions )
		) {
			$body['action'] = sanitize_text_field( $_POST['activity'] );
		}
		
		if (
			isset( $_POST['email'] ) 
			&& filter_var( $_POST['email'], FILTER_VALIDATE_EMAIL )
		) {
			$body['email'] = sanitize_email( $_POST['email'] );
		}
		
		if (
			isset( $_POST['password'] ) 
		) {
			$body['password'] = $_POST['password'];
		}
		
		if (
			isset( $_POST['wp_uid'] ) 
		) {
			$body['wp_uid'] = sanitize_text_field( $_POST['wp_uid'] );
		}
		
		if (
			isset( $_POST['wp'] ) 
		) {
			$body['wp'] = sanitize_text_field( $_POST['wp'] );
		}
		
		if (
			isset( $_POST['wp_playlist'] ) 
		) {
			$body['wp_playlist'] = sanitize_text_field( $_POST['wp_playlist'] );
		}
		
		if (
			isset( $_POST['wp_teaser'] ) 
		) {
			$body['wp_teaser'] = sanitize_text_field( $_POST['wp_teaser'] );
		}
		
		if (
			isset( $_POST['wp_auto'] ) 
		) {
			$body['wp_auto'] = sanitize_text_field( $_POST['wp_auto'] );
		}
		
		if (
			isset( $_POST['playlist_name'] ) 
		) {
			$body['playlist_name'] = sanitize_text_field( $_POST['playlist_name'] );
		}
		
		if (
			isset( $_POST['groups'] ) 
		) {
			$body['groups'] = sanitize_text_field( $_POST['groups'] );
		}
		
		if (
			isset( $_POST['data'] ) 
		) {
			$body['data'] = sanitize_text_field( $_POST['data'] );
		}
                global $wp_version;
$args = array(
    'timeout'     => 10,
    'redirection' => 1,
    'httpversion' => '1.0',
    'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
    'blocking'    => true,
    'headers'     => array(),
    'cookies'     => array(),
    'body'        => $body,
    'compress'    => false,
    'decompress'  => true,
    'sslverify'   => false,
    'stream'      => false,
    'filename'    => null
);
		$response = wp_remote_get( 'https://connect2.allingroups.com/internal_api.php?' . http_build_query($body), $args );

		if ( ! is_wp_error( $response ) && 200 == $response['response']['code']	) {
			wp_die( $response['body'] );
		}
		
		wp_die( $response );
		
	}

	public function allingroups_article_in() {
		global $post;
		$options = get_option( 'allingroups_auto' );
		if( $options && '1' == $options ) {
			return;
		}
		if ( 'post' == get_post_type( $post ) ) {
			echo '<div class="misc-pub-section misc-pub-section-last" style="border-top: 1px solid #eee;">';
			wp_nonce_field( plugin_basename( __FILE__ ), 'article_in_aig_nonce' );
			$val = get_post_meta( $post->ID, '_article_in_aig', true ) ? get_post_meta( $post->ID, '_article_in_aig', true ) : 'no';
			echo '<label>' . __( 'Publish in allingroups?', 'allingroups' ) . '</label><br /><input type="radio" name="article_in_aig" id="article_in_aig-yes" value="yes" ' . checked( $val, 'yes', false ) . ' /> <label for="article_in_aig-yes" class="select-it">' . __( 'yes', 'allingroups' ) . '</label><br />';
			echo '<input type="radio" name="article_in_aig" id="article_in_aig-no" value="no" ' . checked( $val, 'no', false ) . '/> <label for="article_in_aig-no" class="select-it">' . __( 'no', 'allingroups' ) . '</label>';
			echo '</div>';
		}
	}

	public function allingroups_save_article_in( $post_id ) {
		if ( ! isset( $_POST['post_type'] ) ) {
			return $post_id;
		}
		if ( ! wp_verify_nonce( $_POST['article_in_aig_nonce'], plugin_basename( __FILE__ ) ) ) {
			return $post_id;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		if ( 'post' == $_POST['post_type'] && ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return $post_id;
		}
		if ( ! isset( $_POST['article_in_aig'] ) ) {
			return $post_id;
		} else {
			$mydata = $_POST['article_in_aig'];
			update_post_meta( $post_id, '_article_in_aig', $mydata, get_post_meta( $post_id, '_article_in_aig', true ) );
			$options = get_option( 'allingroups_auto' );
			if ( '0' !== $options ) {
				return $post_id;
			}
			if ( $mydata ) {
				$options = (int) get_option( 'allingroups_wp_identifier' );
				$wp_uid = $options ? $options : 0;
				$body = array(
					'action' => 'wppushpost',
					'wp_uid' => $wp_uid,
					'wp'     => get_bloginfo( 'rss2_url' ),
					'guid'   => get_the_guid( $post_id ),
				);
				                global $wp_version;
$args = array(
    'timeout'     => 10,
    'redirection' => 1,
    'httpversion' => '1.0',
    'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
    'blocking'    => true,
    'headers'     => array(),
    'cookies'     => array(),
    'body'        => null,
    'compress'    => false,
    'decompress'  => true,
    'sslverify'   => false,
    'stream'      => false,
    'filename'    => null
);
                		$response = wp_remote_get( 'https://connect2.allingroups.com/internal_api.php?' . http_build_query($body), $args );
			}
			return $post_id;
		}
		
	}

}

new Allingroups();
