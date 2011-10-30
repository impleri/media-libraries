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
				<div id="post-<?php the_ID(); ?>" style="clear:both">
						<div style="float:left;">
							<a href="<?php echo esc_url(get_permalink()); ?>"><img src="<?php the_product_image(); ?>" alt="<?php the_title() ?>" /></a>
						</div>
						<div style="float:right; width:80%">
							<h3 class="entry-title"><a href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a></h3>
							<h4 class="entry-people"><?php the_people(); ?></h4>
							<p><?php printf(_n('One Review', '%1$s Reviews', get_reviews_number(), 'media-libraries'),
								number_format_i18n(get_reviews_number())); ?></p>
						</div>
				</div><!-- #post-<?php the_ID(); ?> -->
				<?php endwhile; ?>
			</div>
		</div>
<?php get_footer(); ?>
