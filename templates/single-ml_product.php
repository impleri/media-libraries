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

				<!-- The product(s) -->
				<?php while ( have_posts() ) : the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<header class="entry-header">
							<div style="float:left;">
								<a href="<?php the_product_link(); ?>"><img src="<?php the_product_image(); ?>" alt="<?php the_title() ?>" /></a>
							</div>
							<div style="float:right; width:80%">
								<h1 class="entry-title"><?php the_title(); ?></h1>
								<h2 class="entry-people"><?php the_people(); ?></h2>
							</div>
						</header>

						<div class="entry-content">
							<!-- usage summary for everyone and logged in user -->
							<?php the_product_usage();  ?>

							<!-- reviews -->
							<?php  if (has_reviews(0, 'official')) { ?>
								<h3 id="official-reviews"><?php echo _n('Official Review', 'Official Reviews', get_reviews_number(0, 'official'), 'media-libraries'); ?></h3>
								<ol class="reviewlist">
								<?php ml_list_reviews(array('type' => 'official'));?>
								</ol>
							<?php } ?>

							<?php  if (has_reviews(0, 'review')) { ?>
								<h3 id="reviews"><?php	printf(_n('One User Review of %2$s', '%1$s User Reviews of %2$s', get_reviews_number(0, 'review'), 'media-libraries'),
									number_format_i18n(get_reviews_number(0, 'review')), '&#8220;' . get_the_title() . '&#8221;'); ?></h3>
								<div class="navigation">
									<div class="alignleft"><?php previous_reviews_link() ?></div>
									<div class="alignright"><?php next_reviews_link() ?></div>
								</div>

								<ol class="reviewlist">
								<?php ml_list_reviews(array('type' => 'review')); ?>
								</ol>

								<div class="navigation">
									<div class="alignleft"><?php previous_reviews_link() ?></div>
									<div class="alignright"><?php next_reviews_link() ?></div>
								</div>
							<?php } ?>

							<!-- link to add a review if one hasn't been written -->
							<?php add_review_link(); ?>
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
					</article><!-- #post-<?php the_ID(); ?> -->
				<?php endwhile; ?>
			</div>
		</div>
<?php get_footer(); ?>
