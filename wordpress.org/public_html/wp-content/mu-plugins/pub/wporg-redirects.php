<?php

/*
 * WordPress.org/-specific redirects
 */
if ( 1 === get_current_blog_id() ) {
	add_action( 'template_redirect', function() {
		// WordPress.org/feed/* should redirect to WordPress.org/news/feed/*
		if ( is_feed() ) {
			wp_safe_redirect( '/news/feed/' . ( 'feed' !== get_query_var('feed') ? get_query_var('feed') : '' ), 301 );
			exit;

		// WordPress.org does not have a specific site search, only the global WordPress.org search
		} elseif ( is_search() ) {
			wp_safe_redirect( '/search/' . urlencode( get_query_var('s') ), 301 );
			exit;

		} elseif ( is_404() ) {
			$path_redirects = [
				// The news blog is often thought to be at /blog
				'/blog' => '/news/',

				// new Downloads pages https://meta.trac.wordpress.org/ticket/3673
				'/download/beta'            => '/download/beta-nightly/',
				'/download/nightly'         => '/download/beta-nightly/',
				'/download/release-archive' => '/download/releases/',
				'/download/legacy'          => '/download/',

				// Five for the Future site aliases
				'/5'                => '/five-for-the-future/',
				'/five'             => '/five-for-the-future/',
				'/5-for-the-future' => '/five-for-the-future/',
				'/5forthefuture'    => '/five-for-the-future/',
				'/fiveforthefuture' => '/five-for-the-future/',
			];

			foreach ( $path_redirects as $test => $redirect ) {
				if ( 0 === strpos( $_SERVER['REQUEST_URI'], $test ) ) {

					// override nocache_headers();
					header_remove( 'expires' );
					header_remove( 'cache-control' );

					wp_safe_redirect( $redirect, 301 );
					exit;
				}
			}
		}
	}, 9 ); // Before redirect_canonical();
}

/**
 * Redirect some common urls to the proper location.
 */
add_action( 'template_redirect', function() {
	$host = $_SERVER['HTTP_HOST'];
	$path = $_SERVER['REQUEST_URI'];

	if ( ! is_404() ) {
		return;
	}

	$path_redirects = [
		// Singular => Plural for plugin/theme directories
		'/plugin/' => '/plugins/',
		'/theme/'  => '/themes/',

		// The plugin directory was available at /plugins-wp/ during a beta-test, and is still linked to.
		'/plugins-wp/' => '/plugins/',

		// Rosetta txt-download urls were changed to /download/.
		'/txt-download/' => '/downloads/',
	];

	if ( 'make.wordpress.org' === $host ) {
		// Slack invite url is /chat not /slack.
		$path_redirects['/slack'] = '/chat/';
	}

	foreach ( $path_redirects as $test => $redirect ) {
		if ( 0 === strpos( $path, $test ) || 0 === strpos( $path . '/', $test ) ) {

			// Include any extra path components. (eg. /plugin/hello-dolly/)
			$path = substr( $path, strlen( $test ) );
			if ( $path ) {
				$redirect .= $path;
			}

			// override nocache_headers();
			header_remove( 'expires' );
			header_remove( 'cache-control' );

			wp_safe_redirect( $redirect, 301 );
			exit;
		}
	}

}, 9 );

/**
 * Handle the domain-based redirects
 * 
 * Called from sunrise.php on ms_site_not_found and ms_network_not_found actions.
 */
function wporg_redirect_site_not_found() {
	// Default location for a not-found site or network is the main WordPress.org homepage.
	$location = 'https://wordpress.org/';
	$host     = $_SERVER['HTTP_HOST'];

	switch ( $host ) {
		// :earth_asia::earth_africa::earth_americas:.wordpress.org
		case 'xn--tg8hcb.wordpress.org':
			$location = 'https://emoji.wordpress.org/';
			break;

		// Singular => Plural
		case 'profile.wordpress.org':
			$location = 'https://profiles.wordpress.org' . $_SERVER['REQUEST_URI'];
			break;

		// WordPress.org => WordPress.net
		case 'wp15.wordpress.org':
		case '2017.wordpress.org':
		case '2019.wordpress.org':
		case '2020.wordpress.org':
		case '2021.wordpress.org':
			$location = 'https://' . explode( '.', $host )[0] . '.wordpress.net/';
			break;

		case 'slack.wordpress.org':
		case 'chat.wordpress.org':
			$location = 'https://make.wordpress.org/chat/';
			break;

		// Plural => Singular
		case 'developers.wordpress.org':
			$location = 'https://developer.wordpress.org/';
			break;
	}

	if ( ! headers_sent() ) {
		header( 'Location: ' . $location, true, 301 );
	} else {
		// Headers should not have been sent at this point in time.
		// On some pages, such as wp-cron.php the request has been terminated prior to WordPress loading, and so headers were "sent".
		echo "<a href='$location'>$location</a>";
	}
	exit;
}