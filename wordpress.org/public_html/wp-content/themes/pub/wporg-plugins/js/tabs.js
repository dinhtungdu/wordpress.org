/* eslint-disable no-var */

( function( $ ) {
	var openTab = function( tabButton, tabs, widgets ) {
		$( '#main' ).find( '.tabcontent' ).css( 'display', 'none' );
		$( '#main' ).find( '.entry-meta .widget' ).css( 'display', 'none' );

		$( '#main' ).find( '.tablinks' ).removeClass( 'active' ).attr( 'aria-selected', 'false' );
		$( '#' + tabButton ).addClass( 'active' ).attr( 'aria-selected', 'true' );

		for ( var i = 0; i < tabs.length; i++ ) {
			$( '#' + tabs[ i ] ).css( 'display', 'block' );
		}

		for ( var j = 0; j < widgets.length; j++ ) {
			$( '#main' ).find( '.entry-meta .widget.' + widgets[ j ] ).css( 'display', 'block' );
		}

		if ( widgets.length === 0 ) {
			$( '#main' ).find( '.entry-meta' ).css( 'display', 'none' );
		} else {
			$( '#main' ).find( '.entry-meta' ).css( 'display', 'block' );
		}
	};

	var openTargetTab = function( targetTab ) {
		if ( targetTab === 'reviews' ) {
			openTab(
				'tab-button-reviews',
				[ 'tab-reviews' ],
				[ 'plugin-ratings' ] );
		} else if ( targetTab === 'installation' ) {
			openTab(
				'tab-button-installation',
				[ 'tab-installation' ],
				[ 'plugin-meta', 'plugin-ratings', 'plugin-support' ] );
		} else if ( targetTab === 'developers' ) {
			openTab(
				'tab-button-developers',
				[ 'tab-developers', 'tab-changelog' ],
				[ 'plugin-meta', 'plugin-ratings', 'plugin-support' ] );
		} else if ( targetTab === 'block' ) {
			openTab(
				'tab-button-block',
				[ 'preview', 'blocks' ],
				[ ] );
		} else {
			openTab(
				'tab-button-description',
				[ 'tab-description', 'screenshots', 'faq' ],
				[ 'plugin-meta', 'plugin-ratings', 'plugin-support' ] );
		}
	};

	$( '#tab-button-description' ).bind( 'click', function() {
		openTargetTab( 'description' );
	} );

	$( '#tab-button-reviews' ).bind( 'click', function() {
		openTargetTab( 'reviews' );
	} );

	$( '#tab-button-installation' ).bind( 'click', function() {
		openTargetTab( 'installation' );
	} );

	$( '#tab-button-developers' ).bind( 'click', function() {
		openTargetTab( 'developers' );
	} );

	$( '#tab-button-block' ).bind( 'click', function() {
		openTargetTab( 'block' );
	} );

	window.showUrlHashTargetTab = function() {
		var targetTab = window.location.hash.substr( 1 );
		openTargetTab( targetTab );
	};

	window.showUrlHashTargetTab();
} )( jQuery );
