<?php
/**
 * template for displaying a user's shelf
 * adapted from TwentyEleven
 *
 * @package media-libraries
 * @subpackage template
 */
get_header(); ?>

		<div id="primary">
			<div id="content" role="main">
				<!-- Library Header  -->
				<?php the_library_header(); ?>

				<?php while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<h1 class="entry-title"><?php the_title(); ?></h1>
						<h2 class="entry-user"><?php the_author(); ?></h2>
					</header>

					<div class="entry-content">
						<?php ml_shelf_page(get_the_ID()); ?>
					</div><!-- .entry-content -->

					<footer class="entry-meta">
						<?php printf(
								__('Bookmark the <a href="%s" title="Permalink to %s" rel="bookmark">permalink</a>.', 'media-libraries'),
								esc_url(get_permalink()),
								get_the_title()
							); ?>
						<?php edit_post_link( __('Edit', 'twentyeleven'), '<span class="edit-link">', '</span>'); ?>
					</footer><!-- .entry-meta -->
				</article><!-- #post-<?php the_ID(); ?> -->
				<?php endwhile; ?>
			</div>
		</div>
<?php get_footer(); ?>
