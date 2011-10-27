<?php
// adapted from TwentyEleven
get_header(); ?>

		<div id="primary">
			<div id="content" role="main">

				<?php while ( have_posts() ) : the_post(); ?>

					<nav id="nav-single">
						<h3 class="assistive-text"><?php _e('Post navigation', 'twentyeleven'); ?></h3>
						<span class="nav-previous"><?php previous_post_link('%link', __('<span class="meta-nav">&larr;</span> Previous', 'twentyeleven')); ?></span>
						<span class="nav-next"><?php next_post_link('%link', __('Next <span class="meta-nav">&rarr;</span>', 'twentyeleven')); ?></span>
					</nav><!-- #nav-single -->

					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<header class="entry-header">
							<h1 class="entry-title"><?php the_title(); ?></h1>
							<h2 class="entry-people"><?php the_people(); ?></h2>
						</header><!-- .entry-header -->

						<div class="entry-content">
							<a href="<?php the_product_link(); ?>"><img src="<?php the_product_image(); ?>" alt="<?php the_title() ?>" /></a>
						</div><!-- .entry-content -->

						<footer class="entry-meta">
							<?php printf(
									__('This product is classified in %s. Bookmark the <a href="%s" title="Permalink to %s" rel="bookmark">permalink</a>.', 'media-libraries'),
									get_the_product_type(),
									esc_url(get_permalink()),
									get_the_title()
								); ?>
							<?php edit_post_link( __('Edit', 'twentyeleven'), '<span class="edit-link">', '</span>'); ?>
						</footer><!-- .entry-meta -->

						<!-- reviews -->
						<?php if ( have_reviews() ) : ?>
							<h3 id="reviews"><?php	printf( _n( 'One Response to %2$s', '%1$s Responses to %2$s', get_reviews_number() ),
								number_format_i18n( get_reviews_number() ), '&#8220;' . get_the_title() . '&#8221;' ); ?></h3>

							<div class="navigation">
								<div class="alignleft"><?php previous_reviews_link() ?></div>
								<div class="alignright"><?php next_reviews_link() ?></div>
							</div>

							<ol class="reviewlist">
							<?php wp_list_reviews();?>
							</ol>

							<div class="navigation">
								<div class="alignleft"><?php previous_reviews_link() ?></div>
								<div class="alignright"><?php next_reviews_link() ?></div>
							</div>
						<?php endif;?>

						<!-- reading stats (restrict to $_REQUEST['user_id'] if it exists) -->
					</article><!-- #post-<?php the_ID(); ?> -->
				<?php endwhile; // end of the loop. ?>

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_footer(); ?>
