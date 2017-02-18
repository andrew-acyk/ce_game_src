<?php
/* The Template to Render the showcase
*
*/

//Define all Variables.
if ( get_theme_mod('forest_showcase_enable' ) && is_front_page() ) : 


		
		?>
		<div id="showcase">
			<div class="container">
				<div class="showcase-container">
			            <div class="swiper-wrapper">
			            <?php
			            for ( $i = 1; $i <= 3; $i++ ) :
	
							$url = esc_url ( get_theme_mod('forest_showcase_url'.$i) );
							$img = esc_url ( get_theme_mod('forest_showcase_img'.$i) );
							$title = esc_html( get_theme_mod('forest_showcase_title'.$i) );
							$desc = esc_html( get_theme_mod('forest_showcase_desc'.$i) );
							 
							if ($img != '') :
							?>
							<div class="showcase-item col-md-4 col-sm-4 col-xs-12">
				            	<a href="<?php echo $url; ?>">
				            		<img src="<?php echo $img ?>" data-thumb="<?php echo $img ?>" title="<?php echo $title." - ".$desc ?>" />
				            	
				            	<div class="showcase-caption">
					                
					                <?php if ($title) : ?>
						                <div class="showcase-title"><?php echo $title ?></div>
						                <div class="showcase-desc"><span><?php echo $desc ?></span></div>
						            <?php endif; ?> 
						            
								</div>
								
								</a>

				            </div>
			             <?php
				             endif;
				        endfor; ?>
			               
			            </div>
			         
			        </div>
			</div> 
		</div>
	<?php	
	
endif;	?>	   