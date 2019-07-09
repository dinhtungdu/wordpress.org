<?php
/**
 * Plugin Name: Theme Switcher for wp-themes.com
 * Description: The Theme switcher for wp-themes.com. Requires Rewrites to be enabled.
 * Author: Dion Hulse
 * Version: 0.3
 */

class WP_Themes_Theme_Switcher {
	public $theme = false;

	function __construct() {
		if ( is_admin() || preg_match( '!^/wp-json/!i', $_SERVER['REQUEST_URI'] ) || defined( 'XMLRPC_REQUEST' ) || 'cli' == php_sapi_name() ) {
			return;
		}

		$this->theme = $this->determine_theme();
		add_filter( 'plugins_loaded', array( $this, 'redirect_unknown_themes' ) );

		add_filter( 'template',    array( $this, 'template' ) );
		add_filter( 'stylesheet',  array( $this, 'stylesheet' ) );

		add_action( 'pre_option_stylesheet', array( $this, 'stylesheet' ) );
		add_action( 'pre_option_template',   array( $this, 'template' ) );
		add_filter( 'pre_option_blogname',   array( $this, 'blogname' ) );

		add_filter( 'home_url', array( $this, 'home_url' ) );
	}

	/**
	 * Determine the current theme based on the REQUEST_URI, the first component of the URI is the theme slug.
	 */
	protected function determine_theme() {
		// If the first component in the URL is a stylesheet, use it and chop it off the REQUEST_URI
		$stylesheet = array_shift( array_filter( explode( '/', $_SERVER['REQUEST_URI'] ) ) );

		if ( ! $stylesheet ) {
			return false;
		}

		$theme = wp_get_theme( $stylesheet );
		if ( ! $theme || ! $theme->exists() ) {
			return false;
		}

		// Strip out the theme from the REQUEST_URI so that WordPress rewrite rules still match.
		$_SERVER['REQUEST_URI'] = substr(
			$_SERVER['REQUEST_URI'],
			strpos( $_SERVER['REQUEST_URI'], $stylesheet ) + strlen( $stylesheet )
		);

		return $theme;
	}

	/**
	 * Redirect unknown theme paths to the default theme.
	 */
	function redirect_unknown_themes() {
		if ( ! $this->theme ) {
			$default_theme = WP_Theme::get_core_default_theme();
			wp_safe_redirect( home_url( $default_theme->stylesheet . '/' ) );
			die();
		}
	}

	/**
	 * Returns the current stylesheet.
	 */
	function stylesheet( $stylesheet = '' ) {
		return $this->theme ? $this->theme->get_stylesheet() : $stylesheet;
	}

	/**
	 * Returns the current template.
	 */
	function template( $template = '' ) {
		return $this->theme ? $this->theme->get_template() : $template;
	}

	/**
	 * Replace the site title with the name of the Theme.
	 */
	function blogname( $name ) {
		return $this->theme ? $this->theme->get( 'Name' ) : $name;
	}

	/**
	 * Prefixes the path with the currently active theme.
	 */
	function home_url( $url ) {
		if ( ! $this->theme ) {
			return $url;
		}

		$hostname = parse_url( $url, PHP_URL_HOST );

		$url = str_replace(	
			$hostname . '/',
			$hostname . '/' . $this->stylesheet() . '/',
			$url
		);

		return $url;
	}

}
new WP_Themes_Theme_Switcher;

