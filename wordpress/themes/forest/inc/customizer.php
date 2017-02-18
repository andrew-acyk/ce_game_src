<?php
/**
 * forest Theme Customizer
 *
 * @package forest
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function forest_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	
	
	
	//Logo Settings
	$wp_customize->add_section( 'title_tagline' , array(
	    'title'      => __( 'Title, Tagline & Logo', 'forest' ),
	    'priority'   => 30,
	) );
	
	$wp_customize->add_setting( 'forest_logo_resize' , array(
	    'default'     => 100,
	    'sanitize_callback' => 'forest_sanitize_positive_number',
	) );
	$wp_customize->add_control(
	        'forest_logo_resize',
	        array(
	            'label' => __('Resize & Adjust Logo','forest'),
	            'section' => 'title_tagline',
	            'settings' => 'forest_logo_resize',
	            'priority' => 6,
	            'type' => 'range',
	            'active_callback' => 'forest_logo_enabled',
	            'input_attrs' => array(
			        'min'   => 30,
			        'max'   => 200,
			        'step'  => 5,
			    ),
	        )
	);
	
	function forest_logo_enabled($control) {
		$option = $control->manager->get_setting('custom_logo');
		return $option->value() == true;
	}
	
	
	
	//Replace Header Text Color with, separate colors for Title and Description
	//Override forest_site_titlecolor
	$wp_customize->remove_control('header_text');
	$wp_customize->remove_setting('header_textcolor');
	$wp_customize->add_setting('forest_site_titlecolor', array(
	    'default'     => '#333333',
	    'sanitize_callback' => 'sanitize_hex_color',
	));
	
	$wp_customize->add_control(new WP_Customize_Color_Control( 
		$wp_customize, 
		'forest_site_titlecolor', array(
			'label' => __('Site Title Color','forest'),
			'section' => 'colors',
			'settings' => 'forest_site_titlecolor',
			'type' => 'color'
		) ) 
	);
	
	$wp_customize->add_setting('forest_header_desccolor', array(
	    'default'     => '#777777',
	    'sanitize_callback' => 'sanitize_hex_color',
	));
	
	$wp_customize->add_control(new WP_Customize_Color_Control( 
		$wp_customize, 
		'forest_header_desccolor', array(
			'label' => __('Site Tagline Color','forest'),
			'section' => 'colors',
			'settings' => 'forest_header_desccolor',
			'type' => 'color'
		) ) 
	);
	
	//Settings for Nav Area
	/*
$wp_customize->add_section(
	    'forest_menu_basic',
	    array(
	        'title'     => __('Menu Settings','forest'),
	        'priority'  => 0,
	        'panel'     => 'nav_menus'
	    )
	);
	
	
	$wp_customize->add_setting( 'forest_disable_nav_desc' , array(
	    'default'     => true,
	    'sanitize_callback' => 'forest_sanitize_checkbox',
	) );
	
	$wp_customize->add_control(
	'forest_disable_nav_desc', array(
		'label' => __('Disable Description of Menu Items','forest'),
		'section' => 'forest_menu_basic',
		'settings' => 'forest_disable_nav_desc',
		'type' => 'checkbox'
	) );
*/
	
	
	//Settings For Logo Area
	
	$wp_customize->add_setting(
		'forest_hide_title_tagline',
		array( 'sanitize_callback' => 'forest_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'forest_hide_title_tagline', array(
		    'settings' => 'forest_hide_title_tagline',
		    'label'    => __( 'Hide Title and Tagline.', 'forest' ),
		    'section'  => 'title_tagline',
		    'type'     => 'checkbox',
		)
	);
		
	function forest_title_visible( $control ) {
		$option = $control->manager->get_setting('forest_hide_title_tagline');
	    return $option->value() == false ;
	}
	
	
	
	
	
	//SLIDER
	// SLIDER PANEL
	$wp_customize->add_panel( 'forest_slider_panel', array(
	    'priority'       => 35,
	    'capability'     => 'edit_theme_options',
	    'theme_supports' => '',
	    'title'          => __('Main Slider','forest'),
	) );
	
	$wp_customize->add_section(
	    'forest_sec_slider_options',
	    array(
	        'title'     => __('Enable/Disable','forest'),
	        'priority'  => 0,
	        'panel'     => 'forest_slider_panel'
	    )
	);
	
	
	$wp_customize->add_setting(
		'forest_main_slider_enable',
		array( 'sanitize_callback' => 'forest_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'forest_main_slider_enable', array(
		    'settings' => 'forest_main_slider_enable',
		    'label'    => __( 'Enable Slider on HomePage.', 'forest' ),
		    'section'  => 'forest_sec_slider_options',
		    'type'     => 'checkbox',
		)
	);
	
	
	$wp_customize->add_setting(
		'forest_main_slider_count',
			array(
				'default' => '0',
				'sanitize_callback' => 'forest_sanitize_positive_number'
			)
	);
	
	// Select How Many Slides the User wants, and Reload the Page.
	$wp_customize->add_control(
			'forest_main_slider_count', array(
		    'settings' => 'forest_main_slider_count',
		    'label'    => __( 'No. of Slides(Min:0, Max: 10)' ,'forest'),
		    'section'  => 'forest_sec_slider_options',
		    'type'     => 'number',
		    'description' => __('Save the Settings, and Reload this page to Configure the Slides.','forest'),
		    
		)
	);
	
	for ( $i = 1 ; $i <= 10 ; $i++ ) :
		
		//Create the settings Once, and Loop through it.
		static $x = 0;
		$wp_customize->add_section(
		    'forest_slide_sec'.$i,
		    array(
		        'title'     => __('Slide ','forest').$i,
		        'priority'  => $i,
		        'panel'     => 'forest_slider_panel',
		        'active_callback' => 'forest_show_slide_sec'
		        
		    )
		);	
		
		$wp_customize->add_setting(
			'forest_slide_img'.$i,
			array( 'sanitize_callback' => 'esc_url_raw' )
		);
		
		$wp_customize->add_control(
		    new WP_Customize_Image_Control(
		        $wp_customize,
		        'forest_slide_img'.$i,
		        array(
		            'label' => '',
		            'section' => 'forest_slide_sec'.$i,
		            'settings' => 'forest_slide_img'.$i,			       
		        )
			)
		);
		
		$wp_customize->add_setting(
			'forest_slide_title'.$i,
			array( 'sanitize_callback' => 'sanitize_text_field' )
		);
		
		$wp_customize->add_control(
				'forest_slide_title'.$i, array(
			    'settings' => 'forest_slide_title'.$i,
			    'label'    => __( 'Slide Title','forest' ),
			    'section'  => 'forest_slide_sec'.$i,
			    'type'     => 'text',
			)
		);
		
		$wp_customize->add_setting(
			'forest_slide_desc'.$i,
			array( 'sanitize_callback' => 'sanitize_text_field' )
		);
		
		$wp_customize->add_control(
				'forest_slide_desc'.$i, array(
			    'settings' => 'forest_slide_desc'.$i,
			    'label'    => __( 'Slide Description','forest' ),
			    'section'  => 'forest_slide_sec'.$i,
			    'type'     => 'text',
			)
		);
		
		
		
		$wp_customize->add_setting(
			'forest_slide_CTA_button'.$i,
			array( 'sanitize_callback' => 'sanitize_text_field' )
		);
		
		$wp_customize->add_control(
				'forest_slide_CTA_button'.$i, array(
			    'settings' => 'forest_slide_CTA_button'.$i,
			    'label'    => __( 'Custom Call to Action Button Text(Optional)','forest' ),
			    'section'  => 'forest_slide_sec'.$i,
			    'type'     => 'text',
			)
		);
		
		$wp_customize->add_setting(
			'forest_slide_url'.$i,
			array( 'sanitize_callback' => 'esc_url_raw' )
		);
		
		$wp_customize->add_control(
				'forest_slide_url'.$i, array(
			    'settings' => 'forest_slide_url'.$i,
			    'label'    => __( 'Target URL','forest' ),
			    'section'  => 'forest_slide_sec'.$i,
			    'type'     => 'url',
			)
		);
		
	endfor;
	
	//active callback to see if the slide section is to be displayed or not
	function forest_show_slide_sec( $control ) {
	        $option = $control->manager->get_setting('forest_main_slider_count');
	        global $x;
	        if ( $x < $option->value() ){
	        	$x++;
	        	return true;
	        }
		}
		
	
	//CUSTOM SHOWCASE
	$wp_customize->add_panel( 'forest_showcase_panel', array(
	    'priority'       => 35,
	    'capability'     => 'edit_theme_options',
	    'theme_supports' => '',
	    'title'          => __('Custom Showcase','forest'),
	) );
	
	$wp_customize->add_section(
	    'forest_sec_showcase_options',
	    array(
	        'title'     => __('Enable/Disable','forest'),
	        'priority'  => 0,
	        'panel'     => 'forest_showcase_panel'
	    )
	);
	
	
	$wp_customize->add_setting(
		'forest_showcase_enable',
		array( 'sanitize_callback' => 'forest_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'forest_showcase_enable', array(
		    'settings' => 'forest_showcase_enable',
		    'label'    => __( 'Enable Showcase on Front Page.', 'forest' ),
		    'section'  => 'forest_sec_showcase_options',
		    'type'     => 'checkbox',
		)
	);
	
	for ( $i = 1 ; $i <= 3 ; $i++ ) :
		
		//Create the settings Once, and Loop through it.
		$wp_customize->add_section(
		    'forest_showcase_sec'.$i,
		    array(
		        'title'     => __('ShowCase ','forest').$i,
		        'priority'  => $i,
		        'panel'     => 'forest_showcase_panel',
		        
		    )
		);	
		
		$wp_customize->add_setting(
			'forest_showcase_img'.$i,
			array( 'sanitize_callback' => 'esc_url_raw' )
		);
		
		$wp_customize->add_control(
		    new WP_Customize_Image_Control(
		        $wp_customize,
		        'forest_showcase_img'.$i,
		        array(
		            'label' => '',
		            'section' => 'forest_showcase_sec'.$i,
		            'settings' => 'forest_showcase_img'.$i,			       
		        )
			)
		);
		
		$wp_customize->add_setting(
			'forest_showcase_title'.$i,
			array( 'sanitize_callback' => 'sanitize_text_field' )
		);
		
		$wp_customize->add_control(
				'forest_showcase_title'.$i, array(
			    'settings' => 'forest_showcase_title'.$i,
			    'label'    => __( 'Showcase Title','forest' ),
			    'section'  => 'forest_showcase_sec'.$i,
			    'type'     => 'text',
			)
		);
		
		$wp_customize->add_setting(
			'forest_showcase_desc'.$i,
			array( 'sanitize_callback' => 'sanitize_text_field' )
		);
		
		$wp_customize->add_control(
				'forest_showcase_desc'.$i, array(
			    'settings' => 'forest_showcase_desc'.$i,
			    'label'    => __( 'Showcase Description','forest' ),
			    'section'  => 'forest_showcase_sec'.$i,
			    'type'     => 'text',
			)
		);
		
		
		$wp_customize->add_setting(
			'forest_showcase_url'.$i,
			array( 'sanitize_callback' => 'esc_url_raw' )
		);
		
		$wp_customize->add_control(
				'forest_showcase_url'.$i, array(
			    'settings' => 'forest_showcase_url'.$i,
			    'label'    => __( 'Target URL','forest' ),
			    'section'  => 'forest_showcase_sec'.$i,
			    'type'     => 'url',
			)
		);
		
	endfor;	
		
		
	//WOOCOMMERCE ENABLED STUFF
	if ( class_exists('woocommerce') ) :
	// CREATE THE fcp PANEL
	$wp_customize->add_panel( 'forest_fcp_panel', array(
	    'priority'       => 40,
	    'capability'     => 'edit_theme_options',
	    'theme_supports' => '',
	    'title'          => 'Featured Product Showcase',
	    'description'    => '',
	) );
	
	
	//SQUARE BOXES
	$wp_customize->add_section(
	    'forest_fc_boxes',
	    array(
	        'title'     => __('Square Boxes','forest'),
	        'priority'  => 10,
	        'panel'     => 'forest_fcp_panel'
	    )
	);
	
	$wp_customize->add_setting(
		'forest_box_enable',
		array( 'sanitize_callback' => 'forest_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'forest_box_enable', array(
		    'settings' => 'forest_box_enable',
		    'label'    => __( 'Enable Square Boxes & Posts Slider.', 'forest' ),
		    'section'  => 'forest_fc_boxes',
		    'type'     => 'checkbox',
		)
	);
	
 
	$wp_customize->add_setting(
		'forest_box_title',
		array( 'sanitize_callback' => 'sanitize_text_field' )
	);
	
	$wp_customize->add_control(
			'forest_box_title', array(
		    'settings' => 'forest_box_title',
		    'label'    => __( 'Title for the Boxes','forest' ),
		    'section'  => 'forest_fc_boxes',
		    'type'     => 'text',
		)
	);
 
 	$wp_customize->add_setting(
	    'forest_box_cat',
	    array( 'sanitize_callback' => 'forest_sanitize_product_category' )
	);
	
	$wp_customize->add_control(
	    new Forest_WP_Customize_Product_Category_Control(
	        $wp_customize,
	        'forest_box_cat',
	        array(
	            'label'    => __('Product Category.','forest'),
	            'settings' => 'forest_box_cat',
	            'section'  => 'forest_fc_boxes'
	        )
	    )
	);
	
		
	//SLIDER
	$wp_customize->add_section(
	    'forest_fc_slider',
	    array(
	        'title'     => __('3D Cube Products Slider','forest'),
	        'priority'  => 10,
	        'panel'     => 'forest_fcp_panel',
	        'description' => 'This is the Posts Slider, displayed left to the square boxes.',
	    )
	);
	
	
	$wp_customize->add_setting(
		'forest_slider_title',
		array( 'sanitize_callback' => 'sanitize_text_field' )
	);
	
	$wp_customize->add_control(
			'forest_slider_title', array(
		    'settings' => 'forest_slider_title',
		    'label'    => __( 'Title for the Slider', 'forest' ),
		    'section'  => 'forest_fc_slider',
		    'type'     => 'text',
		)
	);
	
	$wp_customize->add_setting(
		'forest_slider_count',
		array( 'sanitize_callback' => 'forest_sanitize_positive_number' )
	);
	
	$wp_customize->add_control(
			'forest_slider_count', array(
		    'settings' => 'forest_slider_count',
		    'label'    => __( 'No. of Posts(Min:3, Max: 10)', 'forest' ),
		    'section'  => 'forest_fc_slider',
		    'type'     => 'range',
		    'input_attrs' => array(
		        'min'   => 3,
		        'max'   => 10,
		        'step'  => 1,
		        'class' => 'test-class test',
		        'style' => 'color: #0a0',
		    ),
		)
	);
		
	$wp_customize->add_setting(
		    'forest_slider_cat',
		    array( 'sanitize_callback' => 'forest_sanitize_product_category' )
		);
		
	$wp_customize->add_control(
	    new Forest_WP_Customize_Product_Category_Control(
	        $wp_customize,
	        'forest_slider_cat',
	        array(
	            'label'    => __('Category For Slider.','forest'),
	            'settings' => 'forest_slider_cat',
	            'section'  => 'forest_fc_slider'
	        )
	    )
	);
	
	
	
	//COVERFLOW
	
	$wp_customize->add_section(
	    'forest_fc_coverflow',
	    array(
	        'title'     => __('Top CoverFlow Slider','forest'),
	        'priority'  => 5,
	        'panel'     => 'forest_fcp_panel'
	    )
	);
	
	$wp_customize->add_setting(
		'forest_coverflow_enable',
		array( 'sanitize_callback' => 'forest_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'forest_coverflow_enable', array(
		    'settings' => 'forest_coverflow_enable',
		    'label'    => __( 'Enable', 'forest' ),
		    'section'  => 'forest_fc_coverflow',
		    'type'     => 'checkbox',
		)
	);
	
	$wp_customize->add_setting(
		    'forest_coverflow_cat',
		    array( 'sanitize_callback' => 'forest_sanitize_product_category' )
		);
	
		
	$wp_customize->add_control(
	    new Forest_WP_Customize_Product_Category_Control(
	        $wp_customize,
	        'forest_coverflow_cat',
	        array(
	            'label'    => __('Category For Image Grid','forest'),
	            'settings' => 'forest_coverflow_cat',
	            'section'  => 'forest_fc_coverflow'
	        )
	    )
	);
	
	$wp_customize->add_setting(
		'forest_coverflow_pc',
		array( 'sanitize_callback' => 'forest_sanitize_positive_number' )
	);
	
	$wp_customize->add_control(
			'forest_coverflow_pc', array(
		    'settings' => 'forest_coverflow_pc',
		    'label'    => __( 'Max No. of Posts in the Grid. Min: 5.', 'forest' ),
		    'section'  => 'forest_fc_coverflow',
		    'type'     => 'number',
		    'default'  => '0'
		)
	);
	
	endif; //end class exists woocommerce
	
	
	//Extra Panel for Users, who dont have WooCommerce
	
	// CREATE THE fcp PANEL
	$wp_customize->add_panel( 'forest_a_fcp_panel', array(
	    'priority'       => 40,
	    'capability'     => 'edit_theme_options',
	    'theme_supports' => '',
	    'title'          => __('Featured Posts Showcase','forest'),
	    'description'    => '',
	) );
	
	
	//SQUARE BOXES
	$wp_customize->add_section(
	    'forest_a_fc_boxes',
	    array(
	        'title'     => __('Square Boxes','forest'),
	        'priority'  => 10,
	        'panel'     => 'forest_a_fcp_panel'
	    )
	);
	
	$wp_customize->add_setting(
		'forest_a_box_enable',
		array( 'sanitize_callback' => 'forest_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'forest_a_box_enable', array(
		    'settings' => 'forest_a_box_enable',
		    'label'    => __( 'Enable Square Boxes & Posts Slider.', 'forest' ),
		    'section'  => 'forest_a_fc_boxes',
		    'type'     => 'checkbox',
		)
	);
	
 
	$wp_customize->add_setting(
		'forest_a_box_title',
		array( 'sanitize_callback' => 'sanitize_text_field' )
	);
	
	$wp_customize->add_control(
			'forest_a_box_title', array(
		    'settings' => 'forest_a_box_title',
		    'label'    => __( 'Title for the Boxes','forest' ),
		    'section'  => 'forest_a_fc_boxes',
		    'type'     => 'text',
		)
	);
 
 	$wp_customize->add_setting(
	    'forest_a_box_cat',
	    array( 'sanitize_callback' => 'forest_sanitize_category' )
	);
	
	$wp_customize->add_control(
	    new Forest_WP_Customize_Category_Control(
	        $wp_customize,
	        'forest_a_box_cat',
	        array(
	            'label'    => __('Posts Category.','forest'),
	            'settings' => 'forest_a_box_cat',
	            'section'  => 'forest_a_fc_boxes'
	        )
	    )
	);
	
		
	//SLIDER
	$wp_customize->add_section(
	    'forest_a_fc_slider',
	    array(
	        'title'     => __('3D Cube Products Slider','forest'),
	        'priority'  => 10,
	        'panel'     => 'forest_a_fcp_panel',
	        'description' => 'This is the Posts Slider, displayed left to the square boxes.',
	    )
	);
	
	
	$wp_customize->add_setting(
		'forest_a_slider_title',
		array( 'sanitize_callback' => 'sanitize_text_field' )
	);
	
	$wp_customize->add_control(
			'forest_a_slider_title', array(
		    'settings' => 'forest_a_slider_title',
		    'label'    => __( 'Title for the Slider', 'forest' ),
		    'section'  => 'forest_a_fc_slider',
		    'type'     => 'text',
		)
	);
	
	$wp_customize->add_setting(
		'forest_a_slider_count',
		array( 'sanitize_callback' => 'forest_sanitize_positive_number' )
	);
	
	$wp_customize->add_control(
			'forest_a_slider_count', array(
		    'settings' => 'forest_a_slider_count',
		    'label'    => __( 'No. of Posts(Min:3, Max: 10)', 'forest' ),
		    'section'  => 'forest_a_fc_slider',
		    'type'     => 'range',
		    'input_attrs' => array(
		        'min'   => 3,
		        'max'   => 10,
		        'step'  => 1,
		        'class' => 'test-class test',
		        'style' => 'color: #0a0',
		    ),
		)
	);
		
	$wp_customize->add_setting(
		    'forest_a_slider_cat',
		    array( 'sanitize_callback' => 'forest_sanitize_category' )
		);
		
	$wp_customize->add_control(
	    new Forest_WP_Customize_Category_Control(
	        $wp_customize,
	        'forest_a_slider_cat',
	        array(
	            'label'    => __('Category For Slider.','forest'),
	            'settings' => 'forest_a_slider_cat',
	            'section'  => 'forest_a_fc_slider'
	        )
	    )
	);
	
	
	
	//COVERFLOW
	
	$wp_customize->add_section(
	    'forest_a_fc_coverflow',
	    array(
	        'title'     => __('Top CoverFlow Slider','forest'),
	        'priority'  => 5,
	        'panel'     => 'forest_a_fcp_panel'
	    )
	);
	
	$wp_customize->add_setting(
		'forest_a_coverflow_title',
		array( 'sanitize_callback' => 'sanitize_text_field' )
	);
	
	$wp_customize->add_control(
			'forest_a_coverflow_title', array(
		    'settings' => 'forest_a_coverflow_title',
		    'label'    => __( 'Title for the Coverflow', 'forest' ),
		    'section'  => 'forest_a_fc_coverflow',
		    'type'     => 'text',
		)
	);
	
	$wp_customize->add_setting(
		'forest_a_coverflow_enable',
		array( 'sanitize_callback' => 'forest_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'forest_a_coverflow_enable', array(
		    'settings' => 'forest_a_coverflow_enable',
		    'label'    => __( 'Enable', 'forest' ),
		    'section'  => 'forest_a_fc_coverflow',
		    'type'     => 'checkbox',
		)
	);
	
	$wp_customize->add_setting(
		    'forest_a_coverflow_cat',
		    array( 'sanitize_callback' => 'forest_sanitize_category' )
		);
	
		
	$wp_customize->add_control(
	    new Forest_WP_Customize_Category_Control(
	        $wp_customize,
	        'forest_a_coverflow_cat',
	        array(
	            'label'    => __('Category For Image Grid','forest'),
	            'settings' => 'forest_a_coverflow_cat',
	            'section'  => 'forest_a_fc_coverflow'
	        )
	    )
	);
	
	$wp_customize->add_setting(
		'forest_a_coverflow_pc',
		array( 'sanitize_callback' => 'forest_sanitize_positive_number' )
	);
	
	$wp_customize->add_control(
			'forest_a_coverflow_pc', array(
		    'settings' => 'forest_a_coverflow_pc',
		    'label'    => __( 'Max No. of Posts in the Grid. Min: 5.', 'forest' ),
		    'section'  => 'forest_a_fc_coverflow',
		    'type'     => 'number',
		    'default'  => '0'
		)
	);
	
	
	// Layout and Design
	$wp_customize->add_panel( 'forest_design_panel', array(
	    'priority'       => 40,
	    'capability'     => 'edit_theme_options',
	    'theme_supports' => '',
	    'title'          => __('Design & Layout','forest'),
	) );
	
	$wp_customize->add_section(
	    'forest_design_options',
	    array(
	        'title'     => __('Blog Layout','forest'),
	        'priority'  => 0,
	        'panel'     => 'forest_design_panel'
	    )
	);
	
	
	$wp_customize->add_setting(
		'forest_blog_layout',
		array( 'sanitize_callback' => 'forest_sanitize_blog_layout' )
	);
	
	function forest_sanitize_blog_layout( $input ) {
		if ( in_array($input, array('grid','forest','forest_3_column') ) )
			return $input;
		else 
			return '';	
	}
	
	$wp_customize->add_control(
		'forest_blog_layout',array(
				'label' => __('Select Layout','forest'),
				'settings' => 'forest_blog_layout',
				'section'  => 'forest_design_options',
				'type' => 'select',
				'choices' => array(
						'grid' => __('Standard Blog Layout','forest'),
						'forest' => __('Forest Theme Layout','forest'),
						'forest_3_column' => __('Forest Theme Layout (3 Columns)','forest'),
					)
			)
	);
	
	$wp_customize->add_section(
	    'forest_sidebar_options',
	    array(
	        'title'     => __('Sidebar Layout','forest'),
	        'priority'  => 0,
	        'panel'     => 'forest_design_panel'
	    )
	);
	
	$wp_customize->add_setting(
		'forest_disable_sidebar',
		array( 'sanitize_callback' => 'forest_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'forest_disable_sidebar', array(
		    'settings' => 'forest_disable_sidebar',
		    'label'    => __( 'Disable Sidebar Everywhere.','forest' ),
		    'section'  => 'forest_sidebar_options',
		    'type'     => 'checkbox',
		    'default'  => false
		)
	);
	
	$wp_customize->add_setting(
		'forest_disable_sidebar_home',
		array( 'sanitize_callback' => 'forest_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'forest_disable_sidebar_home', array(
		    'settings' => 'forest_disable_sidebar_home',
		    'label'    => __( 'Disable Sidebar on Home/Blog.','forest' ),
		    'section'  => 'forest_sidebar_options',
		    'type'     => 'checkbox',
		    'active_callback' => 'forest_show_sidebar_options',
		    'default'  => false
		)
	);
	
	$wp_customize->add_setting(
		'forest_disable_sidebar_front',
		array( 'sanitize_callback' => 'forest_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'forest_disable_sidebar_front', array(
		    'settings' => 'forest_disable_sidebar_front',
		    'label'    => __( 'Disable Sidebar on Front Page.','forest' ),
		    'section'  => 'forest_sidebar_options',
		    'type'     => 'checkbox',
		    'active_callback' => 'forest_show_sidebar_options',
		    'default'  => false
		)
	);
	
	
	$wp_customize->add_setting(
		'forest_sidebar_width',
		array(
			'default' => 4,
		    'sanitize_callback' => 'forest_sanitize_positive_number' )
	);
	
	$wp_customize->add_control(
			'forest_sidebar_width', array(
		    'settings' => 'forest_sidebar_width',
		    'label'    => __( 'Sidebar Width','forest' ),
		    'description' => __('Min: 25%, Default: 33%, Max: 40%','forest'),
		    'section'  => 'forest_sidebar_options',
		    'type'     => 'range',
		    'active_callback' => 'forest_show_sidebar_options',
		    'input_attrs' => array(
		        'min'   => 3,
		        'max'   => 5,
		        'step'  => 1,
		        'class' => 'sidebar-width-range',
		        'style' => 'color: #0a0',
		    ),
		)
	);
	
	/* Active Callback Function */
	function forest_show_sidebar_options($control) {
	   
	    $option = $control->manager->get_setting('forest_disable_sidebar');
	    return $option->value() == false ;
	    
	}
	
	class Forest_Custom_CSS_Control extends WP_Customize_Control {
	    public $type = 'textarea';
	 
	    public function render_content() {
	        ?>
	            <label>
	                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
	                <textarea rows="8" style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
	            </label>
	        <?php
	    }
	}
	
	$wp_customize-> add_section(
    'forest_custom_codes',
    array(
    	'title'			=> __('Custom CSS','forest'),
    	'description'	=> __('Enter your Custom CSS to Modify design.','forest'),
    	'priority'		=> 11,
    	'panel'			=> 'forest_design_panel'
    	)
    );
    
	$wp_customize->add_setting(
	'forest_custom_css',
	array(
		'default'		=> '',
		'capability'           => 'edit_theme_options',
		'sanitize_callback'    => 'wp_filter_nohtml_kses',
		'sanitize_js_callback' => 'wp_filter_nohtml_kses'
		)
	);
	
	$wp_customize->add_control(
	    new Forest_Custom_CSS_Control(
	        $wp_customize,
	        'forest_custom_css',
	        array(
	            'section' => 'forest_custom_codes',
	            'settings' => 'forest_custom_css'
	        )
	    )
	);
	
	function forest_sanitize_text( $input ) {
	    return wp_kses_post( force_balance_tags( $input ) );
	}
	
	$wp_customize-> add_section(
    'forest_custom_footer',
    array(
    	'title'			=> __('Custom Footer Text','forest'),
    	'description'	=> __('Enter your Own Copyright Text.','forest'),
    	'priority'		=> 11,
    	'panel'			=> 'forest_design_panel'
    	)
    );
    
	$wp_customize->add_setting(
	'forest_footer_text',
	array(
		'default'		=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
		)
	);
	
	$wp_customize->add_control(	 
	       'forest_footer_text',
	        array(
	            'section' => 'forest_custom_footer',
	            'settings' => 'forest_footer_text',
	            'type' => 'text'
	        )
	);	
	
	
	//Select the Default Theme Skin
	$wp_customize->add_section(
	    'forest_skin_options',
	    array(
	        'title'     => __('Choose Skin','forest'),
	        'priority'  => 39,
	    )
	);
	
	$wp_customize->add_setting(
		'forest_skin',
		array(
			'default'=> 'default',
			'sanitize_callback' => 'forest_sanitize_skin' 
			)
	);
	
	$skins = array( 'default' => __('Default(blue)','forest'),
					'orange' =>  __('Orange','forest'),
					'green' => __('Green','forest'),
					);
	
	$wp_customize->add_control(
		'forest_skin',array(
				'settings' => 'forest_skin',
				'section'  => 'forest_skin_options',
				'type' => 'select',
				'choices' => $skins,
			)
	);
	
	function forest_sanitize_skin( $input ) {
		if ( in_array($input, array('default','orange','brown','green','grayscale') ) )
			return $input;
		else
			return '';
	}
	
	
	//Fonts
	$wp_customize->add_section(
	    'forest_typo_options',
	    array(
	        'title'     => __('Google Web Fonts','forest'),
	        'priority'  => 41,
	        'description' => __('Defaults: Lato.','forest')
	    )
	);
	
	$font_array = array('Raleway','Khula','Open Sans','Droid Sans','Droid Serif','Roboto','Roboto Condensed','Lato','Bree Serif','Oswald','Slabo','Lora','Source Sans Pro','PT Sans','Ubuntu','Lobster','Arimo','Bitter','Noto Sans');
	$fonts = array_combine($font_array, $font_array);
	
	$wp_customize->add_setting(
		'forest_title_font',
		array(
			'default'=> 'Lato',
			'sanitize_callback' => 'forest_sanitize_gfont' 
			)
	);
	
	function forest_sanitize_gfont( $input ) {
		if ( in_array($input, array('Raleway','Khula','Open Sans','Droid Sans','Droid Serif','Roboto','Roboto Condensed','Lato','Bree Serif','Oswald','Slabo','Lora','Source Sans Pro','PT Sans','Ubuntu','Lobster','Arimo','Bitter','Noto Sans') ) )
			return $input;
		else
			return '';	
	}
	
	$wp_customize->add_control(
		'forest_title_font',array(
				'label' => __('Title','forest'),
				'settings' => 'forest_title_font',
				'section'  => 'forest_typo_options',
				'type' => 'select',
				'choices' => $fonts,
			)
	);
	
	$wp_customize->add_setting(
		'forest_body_font',
			array(	'default'=> 'Lato',
					'sanitize_callback' => 'forest_sanitize_gfont' )
	);
	
	$wp_customize->add_control(
		'forest_body_font',array(
				'label' => __('Body','forest'),
				'settings' => 'forest_body_font',
				'section'  => 'forest_typo_options',
				'type' => 'select',
				'choices' => $fonts
			)
	);
	
	// Social Icons
	$wp_customize->add_section('forest_social_section', array(
			'title' => __('Social Icons','forest'),
			'priority' => 44 ,
	));
	
	$social_networks = array( //Redefinied in Sanitization Function.
					'none' => __('-','forest'),
					'facebook' => __('Facebook','forest'),
					'twitter' => __('Twitter','forest'),
					'google-plus' => __('Google Plus','forest'),
					'instagram' => __('Instagram','forest'),
					'rss' => __('RSS Feeds','forest'),
					'vine' => __('Vine','forest'),
					'vimeo-square' => __('Vimeo','forest'),
					'youtube' => __('Youtube','forest'),
					'flickr' => __('Flickr','forest'),
				);
				
	$social_count = count($social_networks);
				
	for ($x = 1 ; $x <= ($social_count - 3) ; $x++) :
			
		$wp_customize->add_setting(
			'forest_social_'.$x, array(
				'sanitize_callback' => 'forest_sanitize_social',
				'default' => 'none'
			));

		$wp_customize->add_control( 'forest_social_'.$x, array(
					'settings' => 'forest_social_'.$x,
					'label' => __('Icon ','forest').$x,
					'section' => 'forest_social_section',
					'type' => 'select',
					'choices' => $social_networks,			
		));
		
		$wp_customize->add_setting(
			'forest_social_url'.$x, array(
				'sanitize_callback' => 'esc_url_raw'
			));

		$wp_customize->add_control( 'forest_social_url'.$x, array(
					'settings' => 'forest_social_url'.$x,
					'description' => __('Icon ','forest').$x.__(' Url','forest'),
					'section' => 'forest_social_section',
					'type' => 'url',
					'choices' => $social_networks,			
		));
		
	endfor;
	
	function forest_sanitize_social( $input ) {
		$social_networks = array(
					'none' ,
					'facebook',
					'twitter',
					'google-plus',
					'instagram',
					'rss',
					'vine',
					'vimeo-square',
					'youtube',
					'flickr'
				);
		if ( in_array($input, $social_networks) )
			return $input;
		else
			return '';	
	}
	
	$wp_customize->add_section(
	    'forest_sec_upgrade',
	    array(
	        'title'     => __('Forest Theme - Help & Support','forest'),
	        'priority'  => 45,
	    )
	);
	
	$wp_customize->add_setting(
			'forest_upgrade',
			array( 'sanitize_callback' => 'esc_textarea' )
		);
			
	$wp_customize->add_control(
	    new Forest_WP_Customize_Upgrade_Control(
	        $wp_customize,
	        'forest_upgrade',
	        array(
	            'label' => __('Thank You','forest'),
	            'description' => __('Thank You for Choosing Forest Theme by Rohitink.com. Forest is a Powerful Wordpress theme which also supports WooCommerce in the best possible way. It is "as we say" the last theme you would ever need. It has all the basic and advanced features needed to run a gorgeous looking site. For any Help related to this theme, please visit  <a href="https://rohitink.com/2016/01/05/forest-multipurpose-theme/" target="_blank">Forest Help & Support</a>.','forest'),
	            'section' => 'forest_sec_upgrade',
	            'settings' => 'forest_upgrade',			       
	        )
		)
	);
	
	
	/* Sanitization Functions Common to Multiple Settings go Here, Specific Sanitization Functions are defined along with add_setting() */
	function forest_sanitize_checkbox( $input ) {
	    if ( $input == 1 ) {
	        return 1;
	    } else {
	        return '';
	    }
	}
	
	function forest_sanitize_positive_number( $input ) {
		if ( ($input >= 0) && is_numeric($input) )
			return $input;
		else
			return '';	
	}
	
	function forest_sanitize_category( $input ) {
		if ( term_exists(get_cat_name( $input ), 'category') )
			return $input;
		else 
			return '';	
	}
	
	function forest_sanitize_product_category( $input ) {
		if ( get_term( $input, 'product_cat' ) )
			return $input;
		else 
			return '';	
	}
	
	
}
add_action( 'customize_register', 'forest_customize_register' );


/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function forest_customize_preview_js() {
	wp_enqueue_script( 'forest_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20130508', true );
}
add_action( 'customize_preview_init', 'forest_customize_preview_js' );
