<?php
/**
 * aml connective functions
 * @package media-libraries
 * @author Christopher Roussel <christopher@impleri.net>
 */

/**
 * @todo
 * product: one-to-many review (one per user), one-to-many reading
 * reading: many-to-one product, many-to-one review, many-to-many shelf
 * review: many-to-one product (one per user), one-to-many reading
 * shelf: many-to-many reading
 */

/**
 * Create review
 *
 * connect a review to a product
 * @param int review ID
 * @param int product ID
 * @return bool True on success
 */
function linkReview($review, $product) {}

/**
 * Create reading
 *
 * connect a reading to a product and shelf
 * @param int reading ID
 * @param int shelf ID
 * @param int product ID
 * @return bool True on success
 */
function linkReading($reading, $shelf, $product) {}

// Pure PHP files should not have a closing PHP tag!!
