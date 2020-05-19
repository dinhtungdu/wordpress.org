<?php
/**
 * The front-page template file.
 * 
 * @package wporg-themes
 */

$sections = [
	'block-styles' => (object) [
		'title' => 'Gutenberg Block Styled Themes',
		'url'   => home_url( '/tags/block-styles/' ),
		'args'  => [
			'tag'            => [
				'block-styles',
			],
			'posts_per_page' => 6,
			'orderby' => 'rand',
		],
	],
	'popular' => (object) [
		'title' => 'Popular Themes',
		'url'   => home_url( '/browse/popular/' ),
		'args'  => [
			'browse'         => 'popular',
			'posts_per_page' => 6,
		],
	],
	'special' => (object) [
		'title' => 'Latest',
		'url'   => home_url( '/browse/new/' ),
		'args'  => [
			'browse'         => 'new',
			'posts_per_page' => 6,
		],
	],
	'holiday' => (object) [
		'title' => 'Holiday Themes',
		'url'   => home_url( '/tags/holiday/' ),
		'args'  => [
			'tag'            => [
				'holiday'
			],
			'posts_per_page' => 6,
		]
	]

];

get_header();
?>
<style>
.themes-section { 
	position: relative;
    /* border-bottom: 2px solid #ddd; */
    margin: 0 auto 4em;
    padding-bottom: 4em;
}
.themes-section:last-of-type {
	border-bottom: none;
}
.themes-section .section-link {
    font-size: 16px;
    font-size: 1rem;
    position: absolute;
    right: 0;
    top: 11.2px;
    top: .7rem;
}
.themes-section > div > .theme:nth-child(3n) {
    margin-right: 0;
}
.themes-section .theme-actions {
	/* Disable download on the front page */
	display: none;
}
#themes-front .theme-navigation .navigation {
	/* Disable navigation on the front view */
	display: none;
}
</style>
<script>
	jQuery(document).ready( function($) {
		wp.themes.view.FrontPageDetails = wp.themes.view.Details.extend({
			collapse: function( event ) {
				if ( ! $( event.target ).is( '.close' ) && event.keyCode !== 27 ) {
					return;
				}

				this.remove();
				this.unbind();
				$('section.themes-section').show();
			}
		});

		$('section.themes-section .theme a').on( 'click', function(e) {
			e.preventDefault();

			var slug = this.href.split('/')[4]; // Meh. hacky

			var collection = new wp.themes.Collection;

			collection.on( 'query:success', function(e) {
				var view = new wp.themes.view.FrontPageDetails({
					model: this.models[0],
				});
				view.render();

				$('main div.theme-overlay').append(view.el);
				$('section.themes-section').hide();
			});

			collection.query({ theme: slug });

		});
	});
</script>

	<main id="themes-front" class="wrap theme-browser">

	<?php
	foreach ( $sections as $section_slug => $section ) {
		echo "<section class='themes themes-section section-{$section_slug}'>\n";
		printf(
			'<header class="section-header">
				<h2 class="section-title">%s</h2>
				<a class="section-link" href="%s">%s</a>
			</header>',
			$section->title,
			esc_attr( $section->url ),
			sprintf(
				'See all %s',
				'<span class="screen-reader-text">' . $section->title . '</span>'
			)
		);

		echo '<div>';

		$query = new WP_Query( $section->args );
		while ( $query->have_posts() ) {
			$query->the_post();
			$theme = wporg_themes_theme_information( $post->post_name );
			include __DIR__ . '/theme.php';
		}

		echo '</div>';

		echo '</section>';

	}
	?>
	<div class="theme-overlay"></div>
	</main>

	<?php get_template_part( 'sidebar', 'footer' ); ?>

<?php
get_footer();
