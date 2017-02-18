<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package forest
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site">
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'forest' ); ?></a>
	<div id="jumbosearch">
		<span class="fa fa-remove closeicon"></span>
		<div class="form">
			<?php get_search_form(); ?>
		</div>
	</div>	
	
	<div id="top-bar">
		<div class="container">
			<div class="social-icons">
				<?php get_template_part('social', 'fa'); ?>	 
			</div>
			<div id="top-menu">
				<?php wp_nav_menu( array( 'theme_location' => 'top' ) ); ?>
			</div>
		</div>
	</div>
	
	<header id="masthead" class="site-header" role="banner">
		<div class="container masthead-container">
			<div class="site-branding">
				<?php if ( forest_has_logo() ) : ?>
				<div id="site-logo">
					<?php forest_logo(); ?>
				</div>
				<?php endif; ?>
				<div id="text-title-desc">
				<h1 class="site-title title-font"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
				<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
				</div>
			</div>	
			
			<div id="slickmenu"></div>
			<nav id="site-navigation" class="main-navigation" role="navigation">
					<?php $walker = new Forest_menu_with_Icon;
						if (!has_nav_menu('primary')) :
							$walker = '';
						endif;
					wp_nav_menu( array( 'theme_location' => 'primary', 'walker' => $walker ) ); ?>
			</nav><!-- #site-navigation -->
			

			<?php if (class_exists('woocommerce')) : ?>
			<div id="top-cart">
				<div class="top-cart-icon">

	 
					<a class="cart-contents" href="<?php echo WC()->cart->get_cart_url(); ?>" title="<?php _e('View your shopping cart', 'forest'); ?>">
						<div class="count"><?php echo sprintf(_n('%d item', '%d items', WC()->cart->cart_contents_count, 'forest'), WC()->cart->cart_contents_count);?></div>
						<div class="total"> <?php echo WC()->cart->get_cart_total(); ?>
						</div>
					</a>
					
					<i class="fa fa-shopping-cart"></i>
					</div>
			</div>
			
			<div id="userlinks">
				<?php if ( is_user_logged_in() ) { ?>
				 	<a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="<?php _e('My Account','forest'); ?>"><?php _e('My Account','forest'); ?></a>
				 <?php } 
				 else { ?>
				 	<a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" title="<?php _e('Login / Register','forest'); ?>"><?php _e('Login / Register','forest'); ?></a>
				<?php } ?>
			</div>
				
			<?php endif; ?>
			
			<div id="searchicon">
				<i class="fa fa-search"></i>
			</div>
			
		</div>	
		
	</header><!-- #masthead -->
	
	<?php get_template_part('slider', 'swiper'); ?>
	<?php get_template_part('featured', 'showcase'); ?>
	
	<div class="mega-container">
		
		<?php if (class_exists('woocommerce')) : ?>	
		<?php get_template_part('coverflow', 'product'); ?>
		<?php get_template_part('featured', 'products'); ?>
		<?php endif; ?>
		<?php get_template_part('coverflow', 'posts'); ?>
		<?php get_template_part('featured', 'posts'); ?>
	
		<div id="content" class="site-content container">