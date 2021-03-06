<?php
/**
 * @package Forest
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('col-md-4 col-sm-4 grid grid_3_column forest-grid'); ?>>

		<div class="featured-thumb col-md-12">
			<?php if (has_post_thumbnail()) : ?>	
				<a href="<?php the_permalink() ?>" title="<?php the_title_attribute() ?>"><?php the_post_thumbnail('forest-pop-thumb'); ?></a>
			<?php else: ?>
				<a href="<?php the_permalink() ?>" title="<?php the_title_attribute() ?>"><img src="<?php echo get_template_directory_uri()."/assets/images/placeholder2.jpg"; ?>"></a>
			<?php endif; ?>
			
			<div class="postedon"><?php forest_posted_on_icon('date'); ?></div>
			
		</div><!--.featured-thumb-->
			
		<div class="out-thumb col-md-12">
			<header class="entry-header">
				<h1 class="entry-title title-font"><a class="hvr-underline-reveal" href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
				<span class="entry-excerpt"><?php the_excerpt(); ?></span>
				<span class="readmore"><a href="<?php the_permalink() ?>"><?php _e('Read More','forest'); ?></a></span>
			</header><!-- .entry-header -->
		</div><!--.out-thumb-->
					
</article><!-- #post-## -->