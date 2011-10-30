<?php
/**
 * template for displaying a single product with all details
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
						<div style="float:left;">
							<a href="<?php the_product_link(); ?>"><img src="<?php the_product_image(); ?>" alt="<?php the_title() ?>" /></a>
						</div>
						<div style="float:right; width:80%">
							<h1 class="entry-title"><?php the_product_title(); ?></h1>
							<h2 class="entry-people"><?php the_people(); ?></h2>
						</div>
					</header>

					<div class="entry-content">
						<h2><?php the_title(); ?></h2>
						<?php the_content(); ?>
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
