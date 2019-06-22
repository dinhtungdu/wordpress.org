<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPressdotorg\Plugin_Directory\Theme
 */

namespace WordPressdotorg\Plugin_Directory\Theme;

use WordPressdotorg\Plugin_Directory\Plugin_Directory;
use WordPressdotorg\Plugin_Directory\Template;

global $section, $section_slug, $section_content, $post;

$content   = Plugin_Directory::instance()->split_post_content_into_pages( get_the_content() );
$is_closed = in_array( get_post_status(), [ 'closed', 'disabled' ], true );

$plugin_title = $is_closed ? $post->post_name : get_the_title();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php the_plugin_banner(); ?>

	<header class="plugin-header">
		<?php the_active_plugin_notice(); ?>

		<div class="entry-thumbnail">
			<?php
			// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
			echo Template::get_plugin_icon( $post, 'html' );
			?>
		</div>

		<div class="plugin-actions">
			<?php the_plugin_favorite_button(); ?>

			<?php if ( 'publish' === get_post_status() || current_user_can( 'plugin_admin_view', $post ) ) : ?>
				<a class="plugin-download button download-button button-large" href="<?php echo esc_url( Template::download_link() ); ?>"><?php esc_html_e( 'Download', 'wporg-plugins' ); ?></a>
			<?php endif; ?>
		</div>

		<?php if ( get_query_var( 'plugin_advanced' ) ) : ?>
		<h1 class="plugin-title"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo wp_kses_post( $plugin_title ); ?></a></h1>
		<?php else : ?>
		<h1 class="plugin-title"><?php echo wp_kses_post( $plugin_title ); ?></h1>
		<?php endif; ?>

		<span class="byline"><?php the_author_byline(); ?></span>
	</header><!-- .entry-header -->

	<?php if ( ! get_query_var( 'plugin_advanced' ) ) : ?>
		<div class="tab" role="tablist">
			<a 
				id="tab-button-description"
				class="tablinks"
				role="tab"
				aria-controls="tab-description"
				href="#description"
			>
				<?php esc_html_e( 'Details', 'wporg-plugins' ); ?>
			</a>
			<a 
				id="tab-button-block"
				class="tablinks"
				role="tab"
				aria-controls="tab-block"
				href="#block"
			>
				<?php esc_html_e( 'Block', 'wporg-plugins' ); ?>
			</a>
			<a 
				id="tab-button-reviews"
				class="tablinks"
				role="tab"
				aria-controls="tab-reviews"
				href="#reviews"
			>
				<?php esc_html_e( 'Reviews', 'wporg-plugins' ); ?>
			</a>
			<?php if ( isset( $content['installation'] ) && ! $is_closed ) : ?>
				<a 
					id="tab-button-installation"
					class="tablinks"
					role="tab"
					aria-controls="tab-installation"
					href="#installation"
				>
					<?php esc_html_e( 'Installation', 'wporg-plugins' ); ?>
				</a>
			<?php endif; ?>
			<a href="<?php echo esc_url( Template::get_support_url() ); ?>"><?php esc_html_e( 'Support', 'wporg-plugins' ); ?></a>
			<a 
				id="tab-button-developers"
				class="tablinks"
				role="tab"
				aria-controls="tab-developers"
				href="#developers"
			>
				<?php esc_html_e( 'Development', 'wporg-plugins' ); ?>
			</a>
		</div>
		<script type="text/javascript">if ( '#changelog' == window.location.hash ) { window.location.hash = '#developers'; }</script>
	<?php endif; ?>
	<div class="tab-content-wrapper">
		<div class="entry-content">
			<?php
			if ( get_query_var( 'plugin_advanced' ) ) :
				get_template_part( 'template-parts/section-advanced' );
			else :
				$plugin_sections = Template::get_plugin_sections();

				foreach ( array( 'description', 'screenshots', 'blocks', 'preview', 'installation', 'faq', 'reviews', 'developers', 'changelog' ) as $section_slug ) :
					$section_content = '';

					if ( 'description' === $section_slug && $is_closed ) {
						// Don't show the description for closed plugins, show a notice instead.
						$section_content = get_closed_plugin_notice();

					} elseif ( 'blocks' === $section_slug ) {
						$section_content = get_post_meta( get_the_ID(), 'all_blocks', true );
					} elseif ( 'preview' === $section_slug ) {
						$block_files = get_post_meta( get_the_ID(), 'block_files', true );
						foreach ( $block_files as $i => $block_file ) {
							$block_files[ $i ] = esc_url( 'https://ps.w.org/' . $post->post_name . $block_file ); //FIXME: better validation and sanitizing
						}
						echo '<script type="text/javascript" src="/plugins/wp-includes/js/dist/components.js"></script>';
						echo '<script type="text/javascript" src="/plugins/wp-includes/js/dist/block-library.js?ver=2.0.1"></script>';
						echo '<div id="preview" data-script-urls="' . implode( ',', $block_files ) . '">preview</div>';
					} elseif ( ! in_array( $section_slug, [ 'screenshots', 'installation', 'faq', 'changelog' ], true ) || ! $is_closed ) {
						if ( isset( $content[ $section_slug ] ) ) {
							$section_content = trim( apply_filters( 'the_content', $content[ $section_slug ], $section_slug ) );
						}
					}

					if ( empty( $section_content ) ) {
						continue;
					}

					$section = wp_list_filter( $plugin_sections, array( 'slug' => $section_slug ) );
					$section = array_pop( $section );

					if ( 'blocks' === $section_slug ) {
						get_template_part( 'template-parts/section-blocks' );
					}  elseif ( 'preview' === $section_slug ) {
					} else {
						get_template_part( 'template-parts/section' );
					}
				endforeach;
			endif; // plugin_advanced.
			?>
		</div><!-- .entry-content -->

		<div class="entry-meta">
			<?php
			if ( get_query_var( 'plugin_advanced' ) && ( ! $is_closed || current_user_can( 'plugin_admin_view', $post ) ) ) {
				get_template_part( 'template-parts/plugin-sidebar', 'advanced' );
			} elseif ( $is_closed ) {
				get_template_part( 'template-parts/plugin-sidebar', 'closed' );
			} else {
				get_template_part( 'template-parts/plugin-sidebar' );
			}
			?>
		</div><!-- .entry-meta -->
	</div>
</article><!-- #post-## -->
