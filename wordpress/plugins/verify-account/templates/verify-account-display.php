<?php
/**
 * Template Name: Verify Account ID
 * Description: Verify user account ID
 */

get_header(); ?>

	<div id="primary-mono" class="content-area <?php do_action('forest_primary-width') ?> page">
        <main id="main" class="site-main" role="main">

            <?php while ( have_posts() ) : the_post(); ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header">
                        <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                    </header><!-- .entry-header -->

                    <!-- account ID form -->
                    <div class="entry-content">
                        <form action="" method="post" id="account-id-form">
                            <div id="feedback"></div>
                            <p>Enter your account ID:</p>
                            <input type="text" >
                            <input type="submit" value="Submit">
                        </form>
                    </div><!-- .entry-content -->

                </article><!-- #post-## --> 
            <?php endwhile; // end of the loop. ?>

        </main><!-- #main -->
    </div><!-- #primary -->

<?php get_footer(); ?>
