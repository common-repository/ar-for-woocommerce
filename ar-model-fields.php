<?php
/**
 * AR Display
 * AR for Woocommerce
 * https://augmentedrealityplugins.com
**/
if (!defined('ABSPATH'))
    exit;

add_action('admin_enqueue_scripts', 'ar_advance_register_script');

/* Adding a custom AR Display Product Data tab*/

function ar_woo_tab( $tabs ) {
  $tabs['ardisplay'] = array(
    'label'  => __( 'AR Models', 'ar-for-woocommerce' ),
    'target' => 'ardisplay_panel',
    'class'  => array(),
  );
  return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'ar_woo_tab' );

function ar_woo_tab_panel($prod_id, $variation_id='') {
    global $post, $wpdb, $shortcode_examples, $ar_whitelabel, $wp, $ar_wcfm, $ar_css_styles, $ar_css_names, $js_displayed;
    if ((isset($prod_id)) AND($prod_id!='')){
        //$post = wc_get_product( $variation->$prod_id );
        //echo 'its set';
    }elseif(isset($wp->query_vars['wcfm-products-manage'])){
        $post = get_post($wp->query_vars['wcfm-products-manage']);
        $prod_id = $post->ID;
    }else{
        $prod_id = $post->ID;
    }
    
    $suffix = $variation_id ? '_var_'.$variation_id : '';
    $class = $variation_id ? '' : 'panel woocommerce_options_panel';
    
    //Model Count
    $model_count = ar_model_count();
    $model_array=array();
    $model_array['id'] = $prod_id;

  echo '
  <div id="ardisplay_panel'.$suffix.'" class="'.$class.' armodel_fields_panel" style="padding:10px !important">
    <div class="options_group">';

        ar_model_fields($prod_id, $model_array, $variation_id);

          
          echo '
    </div>
    </div>';

    ?>
    <script language="javascript">
        jQuery(document).ready(function($){
            modelFields[<?php echo $prod_id;?>] = new ARModel('<?php echo $prod_id;?>','<?php echo $variation_id;?>');
        });
    </script>
    <?php


    if(!$variation_id){
        echo ar_upload_button_js($prod_id);
    }

    if (!isset($js_displayed)){
        ar_model_js($model_array, $variation_id);
        $js_displayed=1;
    }
}

add_action( 'woocommerce_product_data_panels', 'ar_woo_tab_panel' );
/**
 * Add a bit of style.
 */
function ar_woo_custom_style() {
	$output='
	<style>
		#woocommerce-product-data .ardisplay_options.active:hover > a:before,
		#woocommerce-product-data .ardisplay_options > a:before,
		.ardisplay_options.active:hover > a:before,
		.ardisplay_options > a:before {
			background: url( \''.esc_url( plugins_url( "assets/images/chair.png", __FILE__ ) ).'\' ) center center no-repeat;
			content: " " !important;
			background-size: 100%;
			width: 13px;
			height: 13px;
			display: inline-block;
			line-height: 1;
		}
		@media only screen and (max-width: 900px) {
			#woocommerce-product-data .ardisplay_options.active:hover > a:before,
			#woocommerce-product-data .ardisplay_options > a:before,
			#woocommerce-product-data .ardisplay_options:hover a:before {
				background-size: 35%;
			}
		}
		.ardisplay_options:hover a:before {
			background: url( \''.esc_url( plugins_url( "assets/images/chair.png", __FILE__ ) ).'\' ) center center no-repeat;
		}

	</style>';
	echo $output;

}
add_action( 'admin_head', 'ar_woo_custom_style' );

//Save Woocommerce product custom fields
//add_action( 'woocommerce_process_product_meta_simple', 'save_ar_option_fields'  );
//add_action( 'woocommerce_process_product_meta_variable', 'save_ar_option_fields'  );
add_action( 'woocommerce_new_product', 'save_ar_option_fields', 10, 1  );
add_action( 'woocommerce_update_product', 'save_ar_option_fields', 10, 1  );
add_action( 'woocommerce_save_product_variation', 'save_ar_variation', 10, 2 );



add_action('plugins_loaded', function(){
  if($GLOBALS['pagenow']=='post.php'){
    add_action('admin_print_scripts', 'ar_woo_admin_scripts');
  }
});

function ar_model_fields($prod_id, $model_array, $variation_id=''){
    global $post, $wpdb, $shortcode_examples, $ar_whitelabel, $wp, $ar_wcfm, $ar_css_styles, $ar_css_names, $ar_css_import_global, $hotspot_count, $jsArray;
    $plan_check = get_option('ar_licence_plan');
                            



    $suffix = $variation_id ? "_var_".$variation_id : '';
    $button_atts = $variation_id ? 'data-variation="'.$variation_id.'"' : '';
    ?>
       
                        <div id="ar_shortcode_instructions">
                    <div style="width:100%;height:80px;background-color:#12383d">
                        <div class="ar_admin_view_title">
                         <img src="<?php echo esc_url( plugins_url( "assets/images/ar-for-woocommerce-box.jpg", __FILE__ ) );?>" style="padding: 10px 30px 10px 10px; height:60px" align="left">
                                <h1 style="color:#ffffff; padding-top:20px;font-size:20px"><?php _e('AR for Woocommerce','ar-for-woocommerce'); ?></h1>
                            </div>
                            <?php
                        if ((substr(get_option('ar_licence_valid'),0,5)!='Valid')AND($model_count>=2)){?>
                        
                        </div>
                            <p><b><a href="edit.php?post_type=armodels&page"><?php _e( 'Please check your subscription & license key.</a> If you are using the free version of the plugin then you have exceeded the limit of allowed models.', 'ar-for-woocommerce' );?></a></b></p>
                    </div><?php
                    }else{
                        $model_array=array();
                        $model_array['id'] = $prod_id;
                ?>
                		
                		<div  class="ar_admin_view_shortcode">
                    	    <center><b>Shortcode</b> <span id="copied" class="ar_label_tip"></span><br> 
                    	        <a heref="#" onclick="copyToClipboard('ar_shortcode');document.getElementById('copied').innerHTML='-&nbsp;Copied!';">
                    	        <input id="ar_shortcode" type="text" class="button ar_admin_button" value="[ardisplay id=<?=$model_array['id'];?>]" readonly style="width:164px;background: none !important; border: none !important;color:#f37a23 !important;font-size: 16px;"><span class="dashicons dashicons-admin-page" style="color:#fff"></span>
                    	        </a>
                    	        </center>
                    	   </div>
                		<div  class="ar_admin_view_post">
                    		<?php if (get_post_meta( $model_array['id'], '_glb_file', true )!=''){
                               // echo '<div class="ar_admin_view_post">'.sprintf( __('<a href="%s" target="_blank"><button type="button" class="button ar_admin_button" style="margin-right:20px">'.__('View Model Post','ar-for-woocommerce').'</button></a>'), esc_url( get_permalink($model_array['id']) ) ).'</div>';
                                echo ''.sprintf( __('<a href="%s" target="_blank"><button type="button" class="button ar_admin_button" style="margin-right:20px">'.__('View Model Post','ar-for-woocommerce').'</button></a>'), esc_url( get_permalink($model_array['id']) ) );
                            }
                            ?>
                    	</div>
                    </div>
                        	
            	</div>
                <div style="clear:both"></div>
                <!-- Tab links -->
                <div class="ar_tab">
                  <button class="ar_tablinks ar_tablinks<?=$suffix?>" onclick="ar_open_tab(event, 'model_files_content<?=$suffix?>', 'model_files_tab<?=$suffix?>')" id="model_files_tab<?=$suffix?>" type="button"><?php _e( 'Model Files', 'ar-for-woocommerce' );?><span style=" vertical-align: super;font-size: smaller;"> </span></button>
                  <?php if (!$variation_id){?><button class="ar_tablinks ar_tablinks<?=$suffix?>" onclick="ar_open_tab(event, 'asset_builder_content<?=$suffix?>', 'asset_builder_tab<?=$suffix?>')" id="asset_builder_tab<?=$suffix?>" type="button"><?php _e( '3D Gallery Builder', 'ar-for-woocommerce' );?><span style=" vertical-align: super;font-size: smaller;"> </span></button><?php } ?>
                  <button class="ar_tablinks ar_tablinks<?=$suffix?>" onclick="ar_open_tab(event, 'instructions_content<?=$suffix?>','instructions_tab<?=$suffix?>')" id="instructions_tab<?=$suffix?>" type="button"><?php _e( 'Shortcodes', 'ar-for-woocommerce' );?><span style=" vertical-align: super;font-size: smaller;"> </span></button>
                  <a href="https://augmentedrealityplugins.com/support/" target="_blank"><button class="ar_tablinks ar_tablinks<?=$suffix?>" id="support_tab<?=$suffix?>" type="button"><?php _e( 'Support', 'ar-for-woocommerce' );?><span style=" vertical-align: super;font-size: smaller;">&#8599;</span></button></a>
                </div>
                
            <div id="model_files_content<?=$suffix?>" class="ar_tabcontent ar_tabcontent<?=$suffix?>">
            <a href="#" class="wctoggle-model-fields" data-status='hidden'>Show Model Fields</a><br><br>
                	<?php if (!$variation_id){?>
                	<div class="ar_model_files_advert hide_on_devices">
                	    <center>
                	        <img src="<?php echo esc_url( plugins_url( "assets/images/ar_asset_ad_icon.jpg", __FILE__ ) ); ?>" style="height:60px">
                    	    <h3><?=_e('Hang your artwork in Augmented Reality with just a photo!', 'ar-for-woocommerce' );?></h3>
                    	    <button type="button" id="asset_builder_button" onclick="ar_open_tab(event, 'asset_builder_content<?=$suffix?>', 'asset_builder_tab<?=$suffix?>');/*ar_activeclass('asset_builder_tab');*/" class="button ar_admin_button" style="margin-right:20px"><?=_e('3D Gallery Builder', 'ar-for-woocommerce' );?></button>
                	        <p><a href="https://wordpress.org/support/plugin/ar-for-wordpress/reviews/#new-post" target="_blank"><?=_e('Rate this plugin!', 'ar-for-woocommerce' );?></a> <a href="https://wordpress.org/support/plugin/ar-for-wordpress/reviews/#new-post" target="_blank"><img src="<?=esc_url( plugins_url( "assets/images/5-stars.png", __FILE__ ) );?>" style="width: 45px;vertical-align: middle;"></a></p>
                	    </center>
                	</div>
                	<?php } ?>
                <div class="ar_model_files_fields">
                    <?php if (get_post_meta( $model_array['id'], '_glb_file'.$suffix, true )!=''){
                        $glb_upload_image = esc_url( plugins_url( "assets/images/ar_model_icon_tick.jpg", __FILE__ ) ); 
                        $path_parts = pathinfo(sanitize_text_field( get_post_meta( $model_array['id'], '_glb_file'.$suffix, true ) ));
                        $glb_filename = $path_parts['basename'];
                        $ar_glb_pulse = '';
                    }else{
                        $glb_upload_image = esc_url( plugins_url( "assets/images/ar_model_icon.jpg", __FILE__ ) ); 
                        $glb_filename = '';
                        $ar_glb_pulse = 'ar_file_icons_pulse';
                    }
                    if (get_post_meta( $model_array['id'], '_usdz_file'.$suffix, true )!=''){
                        $usdz_upload_image = esc_url( plugins_url( "assets/images/ar_model_icon_tick.jpg", __FILE__ ) );
                        $path_parts = pathinfo(sanitize_text_field( get_post_meta( $model_array['id'].$suffix, '_usdz_file', true ) ));
                        $usdz_filename = $path_parts['basename'];
                        $ar_usdz_pulse = '';
                    }else{
                        $usdz_upload_image = esc_url( plugins_url( "assets/images/ar_model_icon.jpg", __FILE__ ) ); 
                        $usdz_filename = '';
                        $ar_usdz_pulse = 'ar_file_icons_pulse';
                    }
                   
                    ?>
                    <div style="width:48%; float:left;padding-right:10px;position: relative;">
                        
                        <center><strong><?php _e( 'GLTF/GLB 3D Model', 'ar-for-woocommerce' );?></strong> 
                        <img src="<?=$glb_upload_image;?>" id="glb_thumb_img<?php echo $suffix;?>" class="ar_file_icons <?=$ar_glb_pulse?>" onclick="document.getElementById('upload_glb_button<?php echo $suffix;?>').click();document.getElementById('glb_thumb_img<?php echo $suffix;?>').src = '<?php echo esc_url( plugins_url( "assets/images/ar_model_icon_tick.jpg", __FILE__ ) ); ?>';document.getElementById('glb_thumb_img<?php echo $suffix;?>').classList.remove('ar_file_icons_pulse');">
                        <img src="<?=esc_url( plugins_url( "assets/images/delete.png", __FILE__ ) );?>" style="width: 15px;vertical-align: middle;"  onclick="document.getElementById('_glb_file<?php echo $suffix;?>').value = '';document.getElementById('glb_filename<?php echo $suffix;?>').innerHTML = '';document.getElementById('glb_thumb_img<?php echo $suffix;?>').src = '<?php echo esc_url( plugins_url( "assets/images/ar_model_icon.jpg", __FILE__ ) ); ?>';document.getElementById('glb_thumb_img<?php echo $suffix;?>').classList.add('ar_file_icons_pulse');">
                        <br clear="all"><br><span id="glb_filename<?php echo $suffix;?>" class="ar_filenames"><?=$glb_filename;?></span>
                        <div class="nodisplay glb-file-container" align="center"><?php
                        if(!$variation_id){?>
                        <input type="hidden" id="uploader_modelid" value="">
                        <?php
                        }

                        /*woocommerce_wp_text_input( array(
                			'id'				=> '_glb_file'.$suffix,
                			'label'				=> __('GLB/GLTF 3D Model', 'ar-for-woocommerce' ),
                			'desc_tip'			=> 'true',
                			'class'             => 'ar_input_field _glb_file_field',
                			'wrapper_class' => 'form-row-first',
                			'description'		=> __( 'Upload a GLB or GLTF 3D model file. You can also upload a DAE, DXF, 3DS, OBJ, PDF, PLY, STL, or Zipped version of these files and they will be converted automatically.', 'ar-for-woocommerce' ),
                            'custom_attributes' => ['data-model'=>($variation_id ? $variation_id : $prod_id)],
                            'value' => get_post_meta( $model_array['id'], '_glb_file'.$suffix, true ),
                		) ); */?>
                        <input type="text" pattern="https?://.+" title="<?php _e('Secure URLs only','ar-for-woocommerce'); ?> https://" placeholder="https://" name="_glb_file<?php echo $suffix;?>" id="_glb_file<?php echo $suffix;?>" class="regular-text _glb_file_field" value="<?php echo get_post_meta( $model_array['id'], '_glb_file'.$suffix, true );?>"> 
                        
    		            <input id="upload_glb_button<?php echo $suffix;?>" data-suffix="<?php echo $suffix;?>" class="button nodisplay upload_glb_button" type="button" <?php echo $button_atts; ?> value="<?php _e( 'Upload', 'ar-for-woocommerce' );?>" /> 
                        </div>
                        
                        </center>
                    </div>
                    <div style="width:48%; float:left;">
                        <center>
                    	<strong><?php echo __( 'USDZ/REALITY 3D Model', 'ar-for-woocommerce' ).' - '.__('<span class="ar_label_tip">Optional</span>', 'ar-for-woocommerce' );?></strong><br><br>
                    	<img src="<?=$usdz_upload_image;?>" id="usdz_thumb_img<?php echo $suffix;?>"  class="ar_file_icons <?= $ar_usdz_pulse;?>" onclick="document.getElementById('upload_usdz_button<?php echo $suffix;?>').click();document.getElementById('usdz_thumb_img<?php echo $suffix;?>').src = '<?php echo esc_url( plugins_url( "assets/images/ar_model_icon_tick.jpg", __FILE__ ) ); ?>';document.getElementById('usdz_thumb_img<?php echo $suffix;?>').classList.remove('ar_file_icons_pulse');">
                        <img src="<?=esc_url( plugins_url( "assets/images/delete.png", __FILE__ ) );?>" style="width: 15px;vertical-align: middle;" <?php echo $button_atts; ?> onclick="document.getElementById('_usdz_file<?php echo $suffix;?>').value = '';document.getElementById('usdz_filename<?php echo $suffix;?>').innerHTML = '';document.getElementById('usdz_thumb_img<?php echo $suffix;?>').src = '<?php echo esc_url( plugins_url( "assets/images/ar_model_icon.jpg", __FILE__ ) ); ?>';document.getElementById('usdz_thumb_img<?php echo $suffix;?>').classList.add('ar_file_icons_pulse');">
                        
                        <br clear="all"><br><span id="usdz_filename<?php echo $suffix;?>" class="ar_filenames"><?=$usdz_filename;?></span>

                        <div class="nodisplay usdz-file-container"><?php
                            
                        /*woocommerce_wp_text_input( array(
                			'id'				=> '_usdz_file'.$suffix,
                			'label'				=> __('USDZ/REALITY 3D Model', 'ar-for-woocommerce' ),
                			'desc_tip'			=> 'true',
                			'class'             => 'ar_input_field _usdz_file_field',
                			'description'		=> __( 'Upload a USDZ or REALITY 3D model file for iOS devices', 'ar-for-woocommerce' ),
                            'value' => get_post_meta( $model_array['id'], '_usdz_file'.$suffix, true ),
                		) );*/?>
                        <input type="text" pattern="https?://.+" title="<?php _e('Secure URLs only','ar-for-woocommerce'); ?> https://" placeholder="https://" name="_usdz_file<?php echo $suffix;?>" id="_usdz_file<?php echo $suffix;?>" class="regular-text _usdz_file_field" value="<?php echo get_post_meta( $model_array['id'], '_usdz_file'.$suffix, true );?>">

                        <input id="upload_usdz_button<?php echo $suffix;?>" class="button upload_usdz_button nodisplay" type="button" value="<?php _e( 'Upload', 'ar-for-woocommerce' );?>" />
                		</div>
                		 
                        
                        </center>
                    </div>
                    <div style="clear:both"></div> 
                    <?php 
                    
                    if($plan_check!='Premium') { 
                		    $premium_only = '<b> - '.__('Premium Plans Only', 'ar-for-woocommerce').'</b>'; 
                		    $disabled = ' disabled';
                		    $readonly = ['readonly' => 'readonly'];
                		    $custom_attributes = $readonly;
                		    echo '<div style="pointer-events: none;">'; //disable mouse clicking 
                		}else{
                		    $disabled = '';
                		    $readonly = '';
                		    $premium_only = '';
                		    //Used for Scale inputs
                		    $custom_attributes = array(
                                'step' => '0.1',
                                'min' => '0.1');
                		}
                		?>
                	</div>
            	</div>
            	<?php /* Asset Builder */ ?>
            	<div id="asset_builder_content<?=$suffix?>" class="ar_tabcontent ar_tabcontent<?=$suffix?>" style="padding:0px;">
                    <div id="asset_builder">
                        <div id="asset_builder_top_content" style="padding:6px 10px;min-height:520px">
                            <img src="<?php echo plugins_url('assets/images/wall_art_guide.jpg', __FILE__); ?>" style="float:right;max-width:50%">
                            <!---<h3><?php _e( '3D Gallery Builder', 'ar-for-woocommerce' );?></h3>-->
                            <div style="width:45%;float:left">
                            <?php 
                            $nodisplay = ' class=""';
                            for($i = 0; $i<1; $i++) { //Previously 10 - Cube will require 6
                            if ($i>0){$nodisplay = ' class="nodisplay"';}
                            ?>
                               <div  id="texture_container_<?=$i?>" <?=$nodisplay;?> style="padding:10px 0px">
                                 <p><strong><?php _e( 'Image', 'ar-for-woocommerce' );?></strong> <span id="ar_asset_builder_texture_done"></span><br>
                                <img src="<?php echo esc_url( plugins_url( "assets/images/ar_asset_icon.jpg", __FILE__ ) ); ?>" id="asset_thumb_img"  class="ar_file_icons ar_file_icons_pulse" onclick="document.getElementById('upload_asset_texture_button_<?php echo $i; ?>').click();document.getElementById('glb_thumb_img').classList.remove('ar_file_icons_pulse');"> <img src="<?=esc_url( plugins_url( "assets/images/delete.png", __FILE__ ) );?>" style="width: 15px;vertical-align: middle;cursor:pointer" onclick="document.getElementById('_asset_texture_file_<?php echo $i; ?>').value = '';document.getElementById('ar_asset_builder_texture_done').innerHTML = '';document.getElementById('asset_thumb_img').src = '<?php echo esc_url( plugins_url( "assets/images/ar_asset_icon.jpg", __FILE__ ) ); ?>';">
                                <span id="texture_<?=$i?>">
                    	        <input type="hidden" name="_asset_texture_file_<?php echo $i; ?>" id="_asset_texture_file_<?php echo $i; ?>" class="regular-text"> <input id="upload_asset_texture_button_<?php echo $i; ?>" class="upload_asset_texture_button_<?php echo $i; ?> button nodisplay" type="button" value="<?php _e( 'Upload', 'ar-for-woocommerce' );?>" />
                    	        <input type="text" name="_asset_texture_id_<?php echo $i; ?>" id="_asset_texture_id_<?php echo $i; ?>" class="nodisplay"></span></p>
                    	        
                    	        </div>
                         
                            <?php }
                            ?><input type="text" name="_asset_texture_flip" id="_asset_texture_flip" class="nodisplay">
                            <br>
                            
                                <strong><?php _e( 'Frame', 'ar-for-woocommerce' );?> <span id="ar_asset_builder_model_done"></span></strong>
                                <div id="ar_asset_iframe_panel">
                                    <div id="asset_builder_iframe" style="min-height:200px"></div>
                                </div>
                            
                           
                            
                            
                    	<input type="hidden" name="_ar_asset_file" id="_ar_asset_file" class="regular-text" value="">
                        <input type="hidden" name="ar_asset_orientation" id="ar_asset_orientation" class="regular-text" value="portrait">
                        <input type="hidden" name="ar_asset_ratio" id="ar_asset_ratio" value="">
                        <input type="hidden" class="ar_model_id" name="ar_model_id[]" value="<?php echo $model_array['id']; ?>">
                        
      
                        <div style="min-height:100px">
                         <div id="ar_asset_size_container" style="display:none;">
                             <div style="float:left;">
                                 <strong><?php _e( 'Image Ratio', 'ar-for-woocommerce' );?></strong> <span id="ar_asset_builder_ratio_done">&#10003;</span><br>
                                                    <select id="ar_asset_ratio_select" class="regular-text" style="max-width:80%">
                                        <option  id="ar_asset_ratio_options" value="1.0">1:1</option>
                                        <option  id="ar_asset_ratio_options" value="1.4142">A4-A1</option>
                                        <option  id="ar_asset_ratio_options" value="1.25">2:3</option>
                                        <option  id="ar_asset_ratio_options" value="1.5">4:5</option>
                                        <option  id="ar_asset_ratio_options" value="1.33">3:4</option>
                                  </select>
                                  
                              </div>
                              <div style="float:left;">
                                  <strong><?php _e( 'Print Size', 'ar-for-woocommerce' );?></strong> <span id="ar_asset_builder_ratio_done">&#10003;</span><br>
                                  <select id="ar_asset_size" class="regular-text" style="max-width:80%">
                                        <option  id="ar_asset_size_options" value="-1" selected="selected">Select your Asset Below First</option>
                                  </select></p>
                              </div>
                          </div>
                            
                            <span id="ar_asset_builder_submit_container" style="display:none;">
                                <br clear="all"><!--<br>
                                <button id = "ar_asset_builder_submit" class="button ar_admin_button" >Build Asset</button>-->
                                <br>
                                <strong><?php _e( 'Please Publish/Update your post to build the Gallery Asset. You may need to refresh your browser once updated to ensure the latest files are displayed.', 'ar-for-woocommerce' );?></strong>
                                <br>
                                
                            </span>
                            </div>
                            </div>
                        </div>
                    </div>
                </div> 
                
                <?php /* Instructions */ ?>
            	<div id="instructions_content<?=$suffix?>" class="ar_tabcontent ar_tabcontent<?=$suffix?>">
                        <br>                		    
        		        <?php echo $shortcode_examples;
        		        //echo '<p>'.__( 'Models can be uploaded as a GLB or GLTF file for viewing in AR and within the broswer display. You can also upload a USDZ or REALITY file for iOS, otherwise a USDZ file is generated on the fly. The following formats can be uploaded and will be automatically converted to GLB format - DAE, DXF, 3DS, OBJ, PDF, PLY, STL, or Zipped versions of these files. Model conversion accuracy cannot be guaranteed, please check your model carefully.', 'ar-for-woocommerce' );
                        if (!$ar_whitelabel){
                		    echo '<p><a href="https://augmentedrealityplugins.com/support/" target="_blank">'.__('Documentation', 'ar-for-woocommerce').'</a> | <a href="https://augmentedrealityplugins.com/support/3d-model-resources/" target="_blank">'.__('Sample 3D Models', 'ar-for-woocommerce').'</a> | <a href="https://augmentedrealityplugins.com/support/3d-model-resources/#hdr" target="_blank">'.__('Sample HDR Images', 'ar-for-woocommerce').'</a> ';
                		}
                		?>
                </div>
                <?php
                $ar_open_tabs=get_option('ar_open_tabs'); 
                $ar_open_tabs_array = explode(',',$ar_open_tabs);
                $jsArray = json_encode($ar_open_tabs_array);
                ?>   
                <div style="clear:both"></div>
                <div class="ar_admin_viewer">
                    <input type="hidden" name="ar_open_tabs" id="ar_open_tabs" value="<?=$ar_open_tabs;?>">
                	<button class="ar_accordian" id="ar_display_options_acc" type="button"><?php _e('Display Options', 'ar-for-woocommerce' ); echo $premium_only;?></button>
                    <div id="ar_display_options_panel" class="ar_accordian_panel">
                        <br>
                		
                		
                		
                		
                		
                		
                		<?php
            
    		//Skybox File Input
    		echo '<div style="float:left">';
    		woocommerce_wp_text_input( array(
    			'id'				=> '_skybox_file'.$suffix,
    			'label'				=> __( 'Skybox/Background', 'ar-for-woocommerce' ),
    			'desc_tip'			=> 'true',
    			'class'             => 'ar_input_field _skybox_file_field',
    			'description'		=> __( 'Upload a HDR, JPG or PNG file to use as the Skybox or background image - Optional', 'ar-for-woocommerce' ),
    			'custom_attributes' => $readonly,
                'value' => get_post_meta( $model_array['id'], '_skybox_file'.$suffix, true ),
    		) );
    		echo '</div><div style="float:left;padding-top:10px"><input id="upload_skybox_button" class="button upload_skybox_button" type="button" value="'.__('Upload','ar-for-woocommerce').'" '.$disabled.'  '.$button_atts.' /></div><br clear="all">';
    		//Environment Image
    		echo '<div style="float:left">';
    		woocommerce_wp_text_input( array(
    			'id'				=> '_ar_environment'.$suffix,
    			'label'				=> __( 'Environment Image', 'ar-for-woocommerce' ),
    			'desc_tip'			=> 'true',
    			'class'             => 'ar_input_field _environment_file_field',
    			'description'		=> __( 'Upload a HDR, JPG or PNG file to use as the environment image - Optional', 'ar-for-woocommerce' ),
    			'custom_attributes' => $readonly,
                'value' => get_post_meta( $model_array['id'], '_environment_file'.$suffix, true ),
    		) );
    		echo '</div><div style="float:left;padding-top:10px"><input id="upload_environment_button" class="button upload_environment_button" type="button" value="'.__('Upload','ar-for-woocommerce').'" '.$disabled.' '.$button_atts.' /></div><br clear="all" >';
    		
    		?>
    		<?php
    		//Placement
            $ar_placement = get_post_meta( $prod_id, '_ar_placement'.$suffix, true );
    		woocommerce_wp_select( array(
    			'id' 		=> '_ar_placement'.$suffix,
    			'label' 	=> __( 'Model placement', 'ar-for-woocommerce' ),
                    'options' => array(
                        'floor' => __('Floor - Horizontal', 'ar-for-woocommerce'),
                        'wall' => __('Wall - Vertical', 'ar-for-woocommerce')
                    ),
                'desc_tip'			=> 'true',
    			'class'             => 'ar_input_field',
    			'description'		=> __( 'Place your model on a horizontal or vertical surface', 'ar-for-woocommerce' ),
    			'custom_attributes' => $disabled,
                'value' => $ar_placement,
    		) );
            
			//Scale Inputs
			$ar_x = get_post_meta($prod_id, '_ar_x'.$suffix, true );
            if ( ! $ar_x ) {
                $ar_x = 1;
            }
    		woocommerce_wp_text_input( array(
    			'id'				=> '_ar_x'.$suffix,
    			'label'				=> __( 'Scale X', 'ar-for-woocommerce' ),
    			'desc_tip'			=> 'true',
    			'description'		=> __( '1 = 100%, only affects desktop view, not available in AR', 'ar-for-woocommerce' ),
    			'wrapper_class' => 'scale_input',
    			'class'             => 'ar-input _ar_x_field',
    			'type' => 'number',
    			'value' => $ar_x,
                'custom_attributes' => $custom_attributes
    		    ) 
    		);
    		$ar_y = get_post_meta($prod_id, '_ar_y'.$suffix, true );
            if ( ! $ar_y ) {
                $ar_y = 1;
            }
    		woocommerce_wp_text_input( array(
    			'id'				=> '_ar_y'.$suffix,
    			'label'				=> __( 'Scale Y', 'ar-for-woocommerce' ),
    			'desc_tip'			=> 'true',
    			'description'		=> __( '1 = 100%, only affects desktop view, not available in AR', 'ar-for-woocommerce' ),
    			'wrapper_class' => 'scale_input',
    			'class'             => 'ar-input _ar_y_field',
    			'type' => 'number',
    			'value' => $ar_y,
                'custom_attributes' => $custom_attributes
    		    ) 
    		);
    		$ar_z = get_post_meta($prod_id, '_ar_z'.$suffix, true );
            if ( ! $ar_z ) {
                $ar_z = 1;
            }
    		woocommerce_wp_text_input( array(
    			'id'				=> '_ar_z'.$suffix,
    			'label'				=> __( 'Scale Z', 'ar-for-woocommerce' ),
    			'desc_tip'			=> 'true',
    			'description'		=> __( '1 = 100%, only affects desktop view, not available in AR', 'ar-for-woocommerce' ),
    			'wrapper_class' => 'scale_input',
    			'class'             => 'ar-input _ar_z_field',
    			'type' => 'number',
    			'value' => $ar_z,
                'custom_attributes' => $custom_attributes
    		    ) 
    		);
    		echo '
          <br clear="all">';
            //Zoom and Field of View Inputs
            $fov_in_array=array();
            $fov_in_array['default']=__('Default', 'ar-for-woocommerce' );
            for ($x = 10; $x <= 180; $x+=10) {
                $fov_in_array [$x] = $x.' '.__('Degrees', 'ar-for-woocommerce' );
            }
            $arfieldview = get_post_meta($prod_id, '_ar_field_of_view'.$suffix, true );
            $arzoomin = get_post_meta($prod_id, '_ar_zoom_in'.$suffix, true );
            $arzoomout = get_post_meta($prod_id, '_ar_zoom_out'.$suffix, true );

    		woocommerce_wp_select( array(
    			'id'				=> '_ar_field_of_view'.$suffix,
    			'label'				=> __( 'Field of View', 'ar-for-woocommerce' ),
    			'wrapper_class' => 'scale_input',
    			'class'             => 'ar-input _ar_field_of_view',
    			'options' =>  $fov_in_array,
                'value'=> $arfieldview,
                )
    		);
    		$zoom_in_array=array();
            $zoom_in_array['default']=__('Default', 'ar-for-woocommerce' );
            for ($x = 100; $x >= 0; $x-=10) {
                $zoom_in_array [$x] = $x.'%';
            }
    		woocommerce_wp_select( array(
    			'id'				=> '_ar_zoom_in'.$suffix,
    			'label'				=> __( 'Zoom In', 'ar-for-woocommerce' ),
    			'wrapper_class' => 'scale_input',
    			'class'             => 'ar-input _ar_zoom_in',
    			'options' =>  $zoom_in_array,
                'value'=> $arzoomin,
    			
                )
    		);
            $zoom_out_array=array();
            $zoom_out_array['default']=__('Default', 'ar-for-woocommerce' );
            for ($x = 0; $x <= 100; $x+=10) {
                $zoom_out_array [$x] = $x.'%';
            }
    		woocommerce_wp_select( array(
    			'id'				=> '_ar_zoom_out'.$suffix,
    			'label'				=> __( 'Zoom Out', 'ar-for-woocommerce' ),
    			'wrapper_class' => 'scale_input',
    			'class'             => 'ar-input _ar_zoom_out',
    			'options' =>  $zoom_out_array,
                'value'=> $arzoomout,
                )
    		);
    		echo '
          <br clear="all">';
          
          woocommerce_wp_text_input( array(
    			'id'				=> '_ar_light_color'.$suffix,
    			'label'				=> __( 'Light Color', 'ar-for-woocommerce' ),
    			//'desc_tip'			=> 'true',
    			'class'             => 'ar_input_field _light_color',
    			//'description'		=> __( 'Upload a HDR, JPG or PNG file to use as the environment image - Optional', 'ar-for-woocommerce' ),
    			'custom_attributes' => $readonly,
                'value' => get_post_meta( $model_array['id'], '_ar_light_color'.$suffix, true ),
    		) );
          //Exposure and Shadow Inputs
			$ar_exposure = get_post_meta($prod_id, '_ar_exposure'.$suffix, true );
            if ((!$ar_exposure)AND($ar_exposure!='0')){ $ar_exposure = 1; }
            $custom_attributes = array(
                    'step' => '0.1',
                    'min' => '0',
                    'max' => '2');
    		woocommerce_wp_text_input( array(
    			'id'				=> '_ar_exposure'.$suffix,
    			'label'				=> __( 'Exposure', 'ar-for-woocommerce' ),
    			'desc_tip'			=> 'true',
    			'wrapper_class' => 'scale_input',
    			'class'             => 'ar-slider _ar_exposure',
    			'type' => 'range',
    			'value' => $ar_exposure,
                'custom_attributes' => $custom_attributes
    		    ) 
    		);
    		echo '
          <br clear="all">';
    		$ar_shadow_intensity = get_post_meta($prod_id, '_ar_shadow_intensity'.$suffix, true );
    		if ((!$ar_shadow_intensity)AND($ar_shadow_intensity!='0')){ $ar_shadow_intensity = 1; }
            $custom_attributes = array(
                    'step' => '0.1',
                    'min' => '0',
                    'max' => '2');
    		woocommerce_wp_text_input( array(
    			'id'				=> '_ar_shadow_intensity'.$suffix,
    			'label'				=> __( 'Shadow Intensity', 'ar-for-woocommerce' ),
    			'desc_tip'			=> 'true',
    			'wrapper_class' => 'scale_input',
    			'class'             => 'ar-slider _ar_shadow_intensity',
    			'type' => 'range',
    			'value' => $ar_shadow_intensity,
                'custom_attributes' => $custom_attributes
    		    ) 
    		);echo '
          <br clear="all">';
    		$ar_shadow_softness = get_post_meta($prod_id, '_ar_shadow_softness'.$suffix, true );
    		if ((!$ar_shadow_softness)AND($ar_shadow_softness!='0')){ $ar_shadow_softness = 1; }
            $custom_attributes = array(
                    'step' => '0.1',
                    'min' => '0',
                    'max' => '1');
    		woocommerce_wp_text_input( array(
    			'id'				=> '_ar_shadow_softness'.$suffix,
    			'label'				=> __( 'Shadow Softness', 'ar-for-woocommerce' ),
    			'desc_tip'			=> 'true',
    			'wrapper_class' => 'scale_input',
    			'class'             => 'ar-slider _ar_shadow_softness',
    			'type' => 'range',
    			'value' => $ar_shadow_softness,
                'custom_attributes' => $custom_attributes
    		    ) 
    		);
    		echo '
          <br clear="all">
          <div>';
            // Variants
          $arvariants = get_post_meta($prod_id, '_ar_variants'.$suffix, true );
          $arlighting = get_post_meta($prod_id, '_ar_environment_image'.$suffix, true );
          $aranimation = get_post_meta($prod_id, '_ar_animation'.$suffix, true );
          $arautoplay = get_post_meta($prod_id, '_ar_autoplay'.$suffix, true );
          $ar_emissive = get_post_meta($prod_id, '_ar_emissive'.$suffix, true );

			woocommerce_wp_checkbox( array( 
				'id'            => '_ar_variants'.$suffix, 
				'label'         => __('Model includes variants', 'ar-for-woocommerce' ), 
				'desc_tip'			=> 'true',
				'description'   => __( 'Does your model include texture variants? - Optional', 'ar-for-woocommerce' ),
    			'class'             => 'ar-ui-toggle _ar_variants',
    			'custom_attributes' => $readonly,
                'value' => $arvariants,
				)
			);
    		woocommerce_wp_checkbox( array( 
				'id'            => '_ar_environment_image'.$suffix, 
				'label'         => __('Legacy lighting', 'ar-for-woocommerce' ), 
				'desc_tip'			=> 'true',
				'description'   => __( 'The default lighting is designed as a neutral lighting environment that is evenly lit on all sides, but there is also a baked-in legacy lighting primarily for frontward viewing available', 'ar-for-woocommerce' ),
    			'class'             => 'ar-ui-toggle _ar_environment_image',
				'custom_attributes' => $readonly,
                'value' => $arlighting,
				)
			);
			woocommerce_wp_checkbox( array( 
				'id'            => '_ar_emissive'.$suffix, 
				'label'         => __('Emissive lighting', 'ar-for-woocommerce' ), 
				'desc_tip'			=> 'true',
				'description'   => __( 'Emissive lighting to simulate objects that emit light, such as glowing objects or light sources', 'ar-for-woocommerce' ),
    			'class'             => 'ar-ui-toggle _ar_emissive',
				'custom_attributes' => $readonly,
                'value' => $ar_emissive,
				)
			);
			woocommerce_wp_checkbox( array( 
				'id'            => '_ar_animation'.$suffix, 
				'label'         => __('Animation - Play/Pause', 'ar-for-woocommerce' ), 
				'desc_tip'			=> 'true',
				'description'   => __( 'Show a play/pause button if your GLB/GLTF contains animation. Only displays on desktop view - Optional', 'ar-for-woocommerce' ),
    			'class'             => 'ar-ui-toggle _ar_animation',
    			'custom_attributes' => $readonly,
                'value' => $aranimation,
				)
			);
			woocommerce_wp_checkbox( array( 
				'id'            => '_ar_autoplay'.$suffix, 
				'label'         => __('Animation - Auto Play', 'ar-for-woocommerce' ), 
				'desc_tip'			=> 'true',
				'description'   => __( 'Auto Play your animation if your GLB/GLTF contains animation. Only animates on desktop view - Optional', 'ar-for-woocommerce' ),
    			'class'             => 'ar-ui-toggle _ar_autoplay',
    			'custom_attributes' => $readonly,
                'value' => $arautoplay,
				)
			);
			//check if animations in the file and list
			$variation_att = '';
            if($readonly && $variation_id){
                $variation_att = array_merge($readonly, ['data-variation'=>$variation_id, 'data-model'=>$prod_id]);

            } else if(!$readonly && $variation_id){
                $variation_att = ['data-variation'=>$variation_id, 'data-model'=>$prod_id];
            } else if($readonly && !$variation_id){
                $variation_att = array_merge($readonly, ['data-variation'=>$variation_id, 'data-model'=>$prod_id]);
            } else {
                $variation_att = ['data-variation'=>$variation_id, 'data-model'=>$prod_id];
            }?>
            <p class="form-field " id="animationDiv<?php echo $model_array['id']; ?>" style="display:none"><br clear="all"><label for="_ar_animation_selection"><?php _e( 'Animation Selection', 'ar-for-woocommerce' );?></label> <select name="_ar_animation_selection<?php echo $suffix;?>" id="_ar_animation_selection<?php echo $suffix;?>" class="ar-input" <?php echo $disabled;?>></select></p>
            </div>
            </div> <!-- end of Accordian Panel -->
            <?php
            if($variation_id == ''){
            ?>
                    <button class="ar_accordian" id="ar_rotation_acc" type="button"><?php _e('Rotation Limits', 'ar-for-woocommerce' ); echo $premium_only;?></button>
                    <div id="ar_rotation_panel" class="ar_accordian_panel"><br>
            <?php
            
    			woocommerce_wp_checkbox( array( 
    				'id'            => '_ar_rotate_limit'.$suffix, 
    				'label'         => __('Rotation - Set Limits', 'ar-for-woocommerce' ), 
    				'desc_tip'			=> 'true',
    				'description'   => __( 'Restrict the rotation of your model- Optional', 'ar-for-woocommerce' ),
        			'class'             => 'ar-ui-toggle ar_rotate_limit',
        			'custom_attributes' => $variation_att,
    				)
    			);

    			//$hide_rotate_limit = 'display:none';
    			if (get_post_meta( $prod_id, '_ar_rotate_limit'.$suffix, true )){
                    $hide_rotate_limit = '';
                }
                //if ar_rotate_limit is true show limit options
                $ar_compass_top_value = '';
                $ar_compass_top_selected = '';
                if (get_post_meta( $prod_id, '_ar_compass_top_value'.$suffix, true )){
            	    $ar_compass_top_value = get_post_meta( $prod_id, '_ar_compass_top_value'.$suffix, true );
            	    $ar_compass_top_selected = 'style="background-color:#f37a23 !important"';
            	}
            	$ar_compass_bottom_value = '';
                $ar_compass_bottom_selected = '';
                if (get_post_meta( $prod_id, '_ar_compass_bottom_value'.$suffix, true )){
            	    $ar_compass_bottom_value = get_post_meta( $prod_id, '_ar_compass_bottom_value'.$suffix, true );
            	    $ar_compass_bottom_selected = 'style="background-color:#f37a23 !important"';
            	}
            	$ar_compass_left_value = '';
                $ar_compass_left_selected = '';
                if (get_post_meta( $prod_id, '_ar_compass_left_value'.$suffix, true )){
            	    $ar_compass_left_value = get_post_meta( $prod_id, '_ar_compass_left_value'.$suffix, true );
            	    $ar_compass_left_selected = 'style="background-color:#f37a23 !important"';
            	}
            	$ar_compass_right_value = '';
                $ar_compass_right_selected = '';
                if (get_post_meta( $prod_id, '_ar_compass_right_value'.$suffix, true )){
            	    $ar_compass_right_value = get_post_meta( $prod_id, '_ar_compass_right_value'.$suffix, true );
            	    $ar_compass_right_selected = 'style="background-color:#f37a23 !important"';
            	}
            	
                ?>
            	<div style="clear:both"></div>
            	
                <div id="ar_rotation_limits<?php echo $suffix;?>" class="ar_rotation_limits_containter" style="<?php echo $hide_rotate_limit;?>">
                    <center>
                        <h3><?php _e( 'Rotation Limits', 'ar-for-woocommerce' ); ?></h3>
                        <p><?php _e( 'Set your initial camera view first.<br>Then rotate your model to each of your desired limits and click the arrows to apply.', 'ar-for-woocommerce' ); ?></p>
                        <div class="ar-compass-container">
                            <img src="<?php echo esc_url( plugins_url( "assets/images/rotate_up_arrow.png", __FILE__ ) );?>" alt="Compass" id="ar-compass-image<?php echo $suffix;?>" class="ar-compass-image">
                            <button id = "ar-compass-top<?php echo $suffix;?>" class="ar-compass-button ar-compass-top ar-compass-btn-<?php echo $suffix;?>" data-variation="<?php echo $variation_id; ?>" data-model="<?php echo $prod_id; ?>" <?php echo $ar_compass_top_selected; ?> data-rotate="0" type="button">&UpArrowBar;</button>
                            <button id = "ar-compass-bottom<?php echo $suffix;?>" class="ar-compass-button ar-compass-bottom ar-compass-btn-<?php echo $suffix;?>" <?php echo $ar_compass_bottom_selected; ?> data-rotate="180" type="button"  data-variation="<?php echo $variation_id; ?>" data-model="<?php echo $prod_id; ?>">&DownArrowBar;</button>
                            <button id = "ar-compass-left<?php echo $suffix;?>" class="ar-compass-button ar-compass-left ar-compass-btn-<?php echo $suffix;?>" <?php echo $ar_compass_left_selected; ?> data-rotate="270" type="button"  data-variation="<?php echo $variation_id; ?>" data-model="<?php echo $prod_id; ?>">&LeftArrowBar;</button>
                            <button id = "ar-compass-right<?php echo $suffix;?>" class="ar-compass-button ar-compass-right ar-compass-btn-<?php echo $suffix;?>" <?php echo $ar_compass_right_selected; ?> data-rotate="90" type="button"  data-variation="<?php echo $variation_id; ?>" data-model="<?php echo $prod_id; ?>">&RightArrowBar;</button>
                        </div>
                    </center>
                    <input id="_ar_compass_top_value<?php echo $suffix;?>" name="_ar_compass_top_value<?php echo $suffix;?>" class="_ar_compass_top_value" type="hidden" value="<?php echo $ar_compass_top_value;?>" <?php echo $disabled;?>> 
                    <input id="_ar_compass_bottom_value<?php echo $suffix;?>" name="_ar_compass_bottom_value<?php echo $suffix;?>" class="_ar_compass_bottom_value" type="hidden" value="<?php echo $ar_compass_bottom_value;?>" <?php echo $disabled;?>> 
                    <input id="_ar_compass_left_value<?php echo $suffix;?>" name="_ar_compass_left_value<?php echo $suffix;?>" class="_ar_compass_left_value" type="hidden" value="<?php echo $ar_compass_left_value;?>" <?php echo $disabled;?>> 
                    <input id="_ar_compass_right_value<?php echo $suffix;?>" name="_ar_compass_right_value<?php echo $suffix;?>" class="_ar_compass_right_value" type="hidden" value="<?php echo $ar_compass_right_value;?>" <?php echo $disabled;?>> 
                </div>
            
			
			</div> <!-- end of Accordian Panel -->
			<?php } ?>
                    <button class="ar_accordian" id="ar_disable_elements_acc" type="button"><?php _e('Disable/Hide Elements', 'ar-for-woocommerce' ); if ($disabled!=''){echo ' - '.__('Premium Plans Only', 'ar-for-woocommerce');}?></button>
                    <div id="ar_disable_elements_panel" class="ar_accordian_panel">
                        <br>
			<?php
			$arviewhide = get_post_meta( $prod_id, '_ar_view_hide'.$suffix, true );
            $autorotate = get_post_meta( $prod_id, '_ar_rotate'.$suffix, true );
            $hidedimensions = get_post_meta( $prod_id, '_ar_hide_dimensions'.$suffix, true );
            $arprompt = get_post_meta( $prod_id, '_ar_prompt'.$suffix, true );
            $arresizing = get_post_meta( $prod_id, '_ar_resizing'.$suffix, true );
            $qrhide = get_post_meta( $prod_id, '_ar_qr_hide'.$suffix, true );
            $hidereset = get_post_meta( $prod_id, '_ar_hide_reset'.$suffix, true );
            $disablezoom = get_post_meta( $prod_id, '_ar_disable_zoom'.$suffix, true );

			woocommerce_wp_checkbox( array( 
				'id'            => '_ar_view_hide'.$suffix, 
				'label'         => __('AR View Button', 'ar-for-woocommerce' ), 
				'desc_tip'			=> 'true',
				'description'   => __( 'Disable the ability for the user to view the model in the AR view? - Optional', 'ar-for-woocommerce' ),
    			'class'             => 'ar-ui-toggle _ar_view_hide',
                'value' => $arviewhide,
    			'custom_attributes' => $readonly
				)
			);
            woocommerce_wp_checkbox( array( 
				'id'            => '_ar_rotate'.$suffix, 
				'label'         => __('Auto Rotate', 'ar-for-woocommerce' ), 
				'desc_tip'			=> 'true',
				'description'   => __( 'Turn off the auto rotation on your model? - Optional', 'ar-for-woocommerce' ),
    			'class'             => 'ar-ui-toggle _ar_rotate',
                'value' => $autorotate,
    			'custom_attributes' => $readonly,
				)
			);
			woocommerce_wp_checkbox( array( 
				'id'            => '_ar_hide_dimensions'.$suffix, 
				'label'         => __('Dimensions', 'ar-for-woocommerce' ), 
				'desc_tip'			=> 'true',
				'description'   => __( 'Disable the ability for the user to view the dimensions of a model? - Optional', 'ar-for-woocommerce' ),
    			'class'             => 'ar-ui-toggle _ar_hide_dimensions',
                'value' => $hidedimensions,
    			'custom_attributes' => $readonly
				)
			);
			//Prompt
            woocommerce_wp_checkbox( array( 
				'id'            => '_ar_prompt'.$suffix, 
				'label'         => __('Interaction Prompt', 'ar-for-woocommerce' ), 
				'desc_tip'			=> 'true',
				'description'   => __( 'Turn off the rotation and cursor prompt on your model? - Optional', 'ar-for-woocommerce' ),
    			'class'             => 'ar-ui-toggle',
                'value' => $arprompt,
    			'custom_attributes' => $readonly,
				)
			);
            
    		woocommerce_wp_checkbox( array( 
				'id'            => '_ar_resizing'.$suffix, 
				'label'         => __('Resizing in AR', 'ar-for-woocommerce' ), 
				'desc_tip'			=> 'true',
				'description'   => __( 'Disable the ability for the user to rezise the model in the AR view on Android devices only? - Optional', 'ar-for-woocommerce' ),
    			'class'             => 'ar-ui-toggle',
                'value' => $arresizing,
    			'custom_attributes' => $readonly
				)
			);
			
			
			
			woocommerce_wp_checkbox( array( 
				'id'            => '_ar_qr_hide'.$suffix, 
				'label'         => __('QR Code', 'ar-for-woocommerce' ), 
				'desc_tip'			=> 'true',
				'description'   => __( 'Hide the QR code on the desktop view? - Optional', 'ar-for-woocommerce' ),
    			'class'             => 'ar-ui-toggle _ar_qr_hide',
                'value' => $qrhide,
    			'custom_attributes' => $readonly
				)
			);
			woocommerce_wp_checkbox( array( 
				'id'            => '_ar_hide_reset'.$suffix, 
				'label'         => __('Reset Button', 'ar-for-woocommerce' ), 
				'desc_tip'			=> 'true',
				'description'   => __( 'Disable the ability for the user to reset the initial view of a model? - Optional', 'ar-for-woocommerce' ),
    			'class'             => 'ar-ui-toggle _ar_hide_reset',
                'value' => $hidereset,
    			'custom_attributes' => $readonly
				)
			);
			?>
			<br clear="all">
			<?php
			woocommerce_wp_checkbox( array( 
				'id'            => '_ar_disable_zoom'.$suffix, 
				'label'         => __('Zoom', 'ar-for-woocommerce' ), 
				'desc_tip'			=> 'true',
				'description'   => __( 'Disable the ability for the user to zoom in and out? - Optional', 'ar-for-woocommerce' ),
    			'class'             => 'ar-ui-toggle',
                'value' => $disablezoom,
    			'custom_attributes' => $readonly
				)
			);
			
		
			if($variation_id == ''){ 
			?>
			<br clear="all">
			 </div> <!-- end of Accordian Panel -->
			 <button class="ar_accordian" id="ar_qr_code_acc" type="button"><?php _e('QR Code Options', 'ar-for-woocommerce' ); echo $premium_only;?></button>
                        <div id="ar_qr_code_panel" class="ar_accordian_panel">
                        <br>
			<?php $ar_qr_destination = get_post_meta( $prod_id, '_ar_qr_destination_mv'.$suffix, true );?>
			<p class="form-field _ar_qr_dest ">
                 <label for="_ar_qr_image"><?php _e('QR Code Destination', 'ar-for-woocommerce' );?></label>
                        <select id="_ar_qr_destination_mv<?php echo $suffix;?>" name="_ar_qr_destination_mv<?php echo $suffix;?>" class="ar-input" <?= $disabled;?>>
                          <option value=""><?php _e('Use Global Setting', 'ar-for-woocommerce' );?></option>
                          <option value="parent-page" <?php
                            if ($ar_qr_destination=='parent-page'){
                                echo 'selected';
                            }
                          ?>><?php _e('Parent Page', 'ar-for-woocommerce' );?></option>
                          <option value="model-viewer" <?php
                            if ($ar_qr_destination=='model-viewer'){
                                echo 'selected';
                            }
                          ?>
                          ><?php _e('AR View', 'ar-for-woocommerce' );?></option>
                          </select></p>
                       
			<?php //Custom QR Image
    		echo '<div style="float:left">';
            $qrimage = get_post_meta( $prod_id, '_ar_qr_image'.$suffix, true );
    		woocommerce_wp_text_input( array(
    			'id'				=> '_ar_qr_image'.$suffix,
    			'label'				=> __( 'Custom QR Image', 'ar-for-woocommerce' ),
    			'desc_tip'			=> 'true',
    			'class'             => 'ar_input_field',
    			'description'		=> __( 'Upload a JPG or PNG file to use as a custom QR Code Image - Optional. Requires Imagick PHP Extension', 'ar-for-woocommerce' ),
                'value'             => $qrimage,
    			'custom_attributes' => $readonly
    		) );

    		echo '</div><div style="float:left;padding-top:10px"><input id="upload_qr_image_button" class="button upload_qr_image_button" type="button" value="'.__('Upload','ar-for-woocommerce').'" '.$disabled.' /></div><br clear="all">';
    		
    		//Custom QR Code Destination ?>
    		<p class="form-field _ar_qr_dest ">
            <label for="_ar_qr_dest<?php echo $suffix;?>"><?php _e( 'Custom QR Code URL', 'ar-for-woocommerce' ); ?></label>
            <input type="url" pattern="https?://.+" name="_ar_qr_dest<?php echo $suffix;?>" id="_ar_qr_dest<?php echo $suffix;?>" class="regular-text ar_input_field" style="width:300px" value="<?php echo get_post_meta( $prod_id, '_ar_qr_dest'.$suffix, true );?>" <?php echo $disabled;?> > </p>
            </div> <!-- end of Accordian Panel -->
                        <button class="ar_accordian" id="ar_additional_interactions_acc" type="button"><?php _e('Additional Interactions', 'ar-for-woocommerce' ); echo $premium_only;?></button>
                        <div id="ar_additional_interactions_panel" class="ar_accordian_panel">
                        <br>
			<p class="form-field _ar_cta_field ">
            <label for="_ar_cta<?php echo $suffix;?>"><?php _e( 'Call To Action Button', 'ar-for-woocommerce' ); ?></label><span class="woocommerce-help-tip" data-tip="<?php _e( 'Button Displays in 3D Model view and in AR view on Android only', 'ar-for-woocommerce' );?>"></span>
            <input type="text" name="_ar_cta<?php echo $suffix;?>" id="_ar_cta<?php echo $suffix;?>" class="regular-text" value="<?php echo get_post_meta( $prod_id, '_ar_cta'.$suffix, true );?>" <?php echo $disabled;?> style="width:140px;" > </p>
            <p class="form-field _ar_cta_field ">
            <label for="_ar_cta_url<?php echo $suffix;?>"><?php _e( 'Call To Action URL', 'ar-for-woocommerce' ); ?></label>
            <input type="url" pattern="https?://.+" name="_ar_cta_url<?php echo $suffix;?>" id="_ar_cta_url<?php echo $suffix;?>" class="regular-text" value="<?php echo get_post_meta( $prod_id, '_ar_cta_url'.$suffix, true );?>" <?php echo $disabled;?> > </p>
            
			<p class="form-field _ar_hotspot_field ">
		    <label for="_ar_hotspot_text<?php echo $suffix;?>"><?php _e( 'Hotspots', 'ar-for-woocommerce' );?></label><span class="woocommerce-help-tip" data-tip="<?php _e( 'Add your text which can include html and an optional link, click the Add Hotspot button, then click on your model where you would like it placed', 'ar-for-woocommerce' );?>"></span>
		    <input type="text" name="_ar_hotspot_text<?php echo $suffix;?>" id="_ar_hotspot_text<?php echo $suffix;?>" class="regular-text hotspot_annotation" placeholder="<?php _e( 'Hotspot Text', 'ar-for-woocommerce' );?>" <?php echo $disabled;?> value="<?php echo get_post_meta( $prod_id, '_ar_hotspot_text'.$suffix, true );?>"> <input type="text" name="_ar_hotspot_link<?php echo $suffix;?>" id="_ar_hotspot_link<?php echo $suffix;?>" class="regular-text hotspot_annotation" placeholder="<?php _e( 'Hotspot Link', 'ar-for-woocommerce' );?>" <?php echo $disabled;?> value="<?php echo get_post_meta( $prod_id, '_ar_hotspot_link'.$suffix, true );?>">
            <input type="checkbox" name="_ar_hotspot_check<?php echo $suffix;?>" id="_ar_hotspot_check<?php echo $suffix;?>" class="regular-text" value="y" style="display:none;" <?php ;?>>
            <input type="button" class="button" data-variation="<?php echo $variation_id;?>" onclick="enableHotspot()" value="<?php _e( 'Add Hotspot', 'ar-for-woocommerce' );?>" <?php echo $disabled;?>> </p>
		    
        	
        	
        	<?php 
        	if (get_post_meta( $prod_id, '_ar_hotspots'.$suffix, true )){
        	    $_ar_hotspots = get_post_meta( $prod_id, '_ar_hotspots'.$suffix, true );
        	    $hotspot_count = count($_ar_hotspots['annotation']);
        	    $hide_remove_btn = '';
        	    foreach ($_ar_hotspots['annotation'] as $k => $v){
        	        if (isset($_ar_hotspots["link"][$k])){
        	            $link = $_ar_hotspots["link"][$k];
        	        }else{
        	            $link ='';
        	        }
        	        echo '<div id="_ar_hotspot_container_'.$k.'"><p class="form-field _ar_autoplay_field "><label for="_ar_hotspot">Hotspot '.$k.'</label><span id="_ar_hotspot_field_'.$k.'">
        	        <input hidden="true" id="_ar_hotspots[data-normal]['.$k.']" name="_ar_hotspots[data-normal]['.$k.']" value="'.$_ar_hotspots['data-normal'][$k].'">
        	        <input hidden="true" id="_ar_hotspots[data-position]['.$k.']" name="_ar_hotspots[data-position]['.$k.']" value="'.$_ar_hotspots['data-position'][$k].'">
        	        <input type="text" class="regular-text hotspot_annotation" id="_ar_hotspots[annotation]['.$k.']" name="_ar_hotspots[annotation]['.$k.']" hotspot_name="hotspot-'.$k.'" value="'.$v.'">
                    <input type="text" class="regular-text hotspot_annotation" id="_ar_hotspots[link]['.$k.']" name="_ar_hotspots[link]['.$k.']" hotspot_link="hotspot-'.$k.'" value="'.$link.'" placeholder="Link">
        	        </span></div></p>';
        	    
        	    }
        	}else{
        	    $hotspot_count = 0;
        	    $hide_remove_btn = 'style="display:none;"';
        	    echo '<div id="_ar_hotspot_container_0"></div>';
        	}
        	?>
        	<p class="form-field _ar_hotspot_field "><label for="_ar_remove_hotspot"></label> <input id="_ar_remove_hotspot" type="button" class="button" <?php echo $hide_remove_btn;?> onclick="removeHotspot()" data-variation="<?php echo $variation_id;?>" value="Remove last hotspot" <?php echo $disabled;?>></p>
        	
                <?php } ?>
			</div> <!-- end of Accordian Panel --> 	
                        <button class="ar_accordian" id="ar_alternative_acc" type="button"><?php _e('Alternative Model For Mobile', 'ar-for-woocommerce' ); echo $premium_only; ?></button>
                        <div id="ar_additional_interactions_panel" class="ar_accordian_panel">
                        <br>
            <div style="clear:both"></div>
                <h3> <?php
                	    _e('Alternative Model For Mobile', 'ar-for-woocommerce' );
                        if ($disabled!=''){echo ' - '.__('Premium Plans Only', 'ar-for-woocommerce');}
                        ?></h3>
                <div style="clear:both"></div>
                <div class="ar_admin_label"><?php _e( 'Display a different AR model when viewing on mobile devices', 'ar-for-woocommerce' );?></div>
                <?php 
                $temp_post = $post;
                //Get list of AR Models
                $args = array(
                    'post_type'=> 'armodels',
                    'orderby'        => 'title',
                    'posts_per_page' => -1,
                    'order'    => 'ASC'
                );              
                $the_query = new WP_Query( $args );
                if($the_query->have_posts() ) { 
                    while ( $the_query->have_posts() ) { 
                        $the_query->the_post();
                        $mob_title = get_the_title();
                        $mob_id = get_the_ID();
                        if (($mob_title)){
                            $ar_id_array[$mob_id] = $mob_title;
                        }
                    } 
                    wp_reset_postdata(); 
                }
                $post = $temp_post;
                ?>
                
            	<div class="ar_admin_field"><select name="_ar_mobile_id<?php echo $suffix;?>" id="_ar_mobile_id<?php echo $suffix;?>" class="ar-input" <?php echo $disabled;?>>
            	    <option value=''></option>
            	    <?php
            	    foreach ($ar_id_array as $mob_id => $mob_title){
            	        if ($mob_id != $post->ID){
            	            echo '<option value="'.$mob_id.'" '.selected( get_post_meta( $post->ID, '_ar_mobile_id', true ), $mob_id ).'>'.$mob_title.' (#'.$mob_id.')</option>';
            	        }
            	    }
            	    ?>
            	</select></div>

                <div style="clear:both"></div>

                            <div class="ar_admin_label"><?php _e( 'Display a different AR model when viewing on AR mode', 'ar-for-woocommerce' );?></div>
                            <div class="ar_admin_field"><select name="_ar_alternative_id<?php echo $suffix;?>" id="_ar_alternative_id<?php echo $suffix;?>" class="ar-input /*ar-input-wide*/" <?php echo $disabled;?>>
                                <option value=''></option>
                                <?php
                                foreach ($ar_id_array as $mob_id => $mob_title){
                                    if ($mob_id != $model_array['id']){
                                        echo '<option value="'.$mob_id.'" '.selected( get_post_meta( $model_array['id'], '_ar_alternative_id'.$suffix, true ), $mob_id ).'>'.$mob_title.' (#'.$mob_id.')</option>';
                                    }
                                }
                                ?>
                            </select></div>
                            <div style="clear:both"></div>
                
            	</div> <!-- end of Accordian Panel -->
            	<?php 
            	if($variation_id == ''){ ?>
                        <button class="ar_accordian" id="ar_element_positions_acc" type="button"><?php _e('Element Positions and CSS Styles', 'ar-for-woocommerce' );echo $premium_only;?></button>
                        <div id="ar_additional_interactions_panel" class="ar_accordian_panel">
                        <br>
                        <div style="clear:both"></div>
                        <p class="form-field _ar_css_field"><label for="_ar_css"><?php _e( 'Override Global Settings', 'ar-for-woocommerce' );?></label><input type="checkbox" name="_ar_css_override<?php echo $suffix;?>" id="_ar_css_override<?php echo $suffix;?>" class="regular-text" value="1" <?php if (get_post_meta( $prod_id, '_ar_css_override'.$suffix, true )=='1'){echo 'checked';$hide_custom_css='';}else{$hide_custom_css='/*style="display:none;"*/';} echo $disabled;?>> </p>
                        <div style="clear:both"></div>
                        <div id="ar_custom_css_div" <?php echo $hide_custom_css;?>>
                            <input type="button" class="button" data-variation="<?php echo $variation_id;?>" onclick="importCSS()" value="<?php _e( 'Import Global Settings', 'ar-for-woocommerce' );?>" <?php echo $disabled;?>><br  clear="all"><br>
                            
                            <?php //CSS Positions
                            $ar_css_positions = get_post_meta( $prod_id, '_ar_css_positions'.$suffix, true );
                            foreach ($ar_css_names as $k => $v){
                                ?>
                                <div>
                                  <div style="width:160px;float:left;"><strong>
                                      <?php _e($k, 'ar-for-woocommerce' );?> </strong></div>
                                  <div style="float:left;"><select id="_ar_css_positions[<?=$k;?>]" name="_ar_css_positions[<?=$k;?>]" <?= $disabled;?>>
                                      <option value="">Default</option>
                                      <?php 
                                      foreach ($ar_css_styles as $pos => $css){
                                        echo '<option value = "'.$pos.'"';
                                        if (is_array($ar_css_positions)){
                                            if ($ar_css_positions[$k]==$pos){echo ' selected';}
                                        }
                                        echo '>'.$pos.'</option>';
                                      }?>
                                      
                                      </select></div>
                                </div>
                                <br  clear="all">
                                <br>
                            <?php
                            }
                            ?>
                            <div>
                            <div style="width:160px;float:left;"><strong>
                              <?php
                                $ar_css = get_post_meta( $prod_id, '_ar_css'.$suffix, true );
                                $ar_css_import_global='';
                                if (get_option('ar_css')!=''){
                                    $ar_css_import_global = get_option('ar_css');
                                }
                                $ar_css_import=ar_curl(esc_url( plugins_url( "assets/css/ar-display-custom.css", __FILE__ ) ));
                        	    _e('CSS Styling', 'ar-for-woocommerce' );
                                ?>
                                </strong>
                            </div>
                      <div style="float:left;"><textarea id="_ar_css<?php echo $suffix;?>" name="_ar_css<?php echo $suffix;?>" style="width: 400px; height: 200px;" <?= $disabled;?>><?php echo $ar_css; ?></textarea></div>
                    </div>
                </div>
            </div> <!-- end of Accordian Panel --> 
            <?php } ?>
            </div>
            <?php
          /* Display the 3D model if it exists */
            $hide_ar_view = '';
            if (get_post_meta($model_array['id'], '_glb_file'.$suffix, true )==''){ $hide_ar_view = 'display:none;';}
            echo '<div class="ar_admin_viewer" id="ar_admin_model_'.$model_array['id'].'" style="'.$hide_ar_view.'">';
            echo '<div style="width: 100%; border: 1px solid #f8f8f8;">'.ar_display_shortcode($model_array, $variation_id).'</div>'; 
            $ar_camera_orbit = get_post_meta( $prod_id, '_ar_camera_orbit'.$suffix, true );?>
            

            <?php if($variation_id == ''){ ?>
            <button id="downloadPosterToBlob" onclick="downloadPosterToDataURL()" class="button" type="button" style="margin-top:10px">Set Featured Image</button>
            <input type="hidden" id="_ar_poster_image_field" name="_ar_poster_image_field">
            <input id="camera_view_button" class="button" type="button" style="float:right;margin-top: 10px" value="<?php _e( 'Set Current Camera View as Initial', 'ar-for-woocommerce' );?>" <?php echo $disabled;?> />
            <div id="_ar_camera_orbit_set" style="float:right;margin: 10px;display:none"><span style="color:green;margin-left: 7px; font-size: 19px;">&#10004;</span></div><input id="_ar_camera_orbit" name="_ar_camera_orbit" type="text" value="<?php echo $ar_camera_orbit;?>" style="display:none;"><br clear="all" style="float:right;">
            
           
            
        	
            <?php 
            }
            echo '</div>';
          
        
           
           } 
           if($plan_check!='Premium') { 
        	    echo '</div>'; 
        	//close the div that disables mouse clicking 
        	} 
}

function ar_model_js($model_array, $variation_id=''){
    global $ar_css_import_global, $hotspot_count,$jsArray;
    $ar_css_import = '';

    $suffix = ($variation_id != '') ? "_var_".$variation_id : '';

    ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Check if jQuery is defined
            if (typeof jQuery !== 'undefined') {
                // Your jQuery code here
                jQuery(document).ready(function($) {
                    // Click the element with ID "model_files_tab"
                    $("#model_files_tab").click();
                });
            } else {
                // If not using jQuery, use pure JavaScript
                var modelFilesTab = document.getElementById("model_files_tab");
                if (modelFilesTab) {
                    // Click the element
                    modelFilesTab.click();
                }
            }
        });
        function ar_open_tab(evt, tabName, target) {
          // Declare all variables
          var i, ar_tabcontent, ar_tablinks;
          // Get all elements with class="tabcontent" and hide them
          ar_tabcontent = document.getElementsByClassName("ar_tabcontent");
          for (i = 0; i < ar_tabcontent.length; i++) {
            ar_tabcontent[i].style.display = "none";
          }
        
          // Get all elements with class="ar_tablinks" and remove the class "active"
          ar_tablinks = document.getElementsByClassName("ar_tablinks");
          for (i = 0; i < ar_tablinks.length; i++) {
            ar_tablinks[i].className = ar_tablinks[i].className.replace(" active", "");
          }
        
          // Show the current tab, and add an "active" class to the button that opened the tab
          document.getElementById(tabName).style.display = "block";
          //evt.currentTarget.className += " active";
          document.getElementById(target).classList.add("active");
        }
        
        function ar_activeclass(divId) {
          var element = document.getElementById(divId);
          if (element) {
            element.className += " active";
          }
        }
        

        
    //Rotation Limits Compass
        //jQuery.noConflict();
        jQuery(document).ready(function($){
            //rotate limit form
            //window.modelviewer.admin.init('<?php echo $model_array['id']; ?>');
            //window.modelviewer.admin.functions();
            //console.log($('#model_<?php echo $model_array['id']; ?>'));
            
        });


            
        const modelViewer<?php echo $model_array['id']; ?> = document.querySelector('#model_<?php echo $model_array['id']; ?>');
        const ar_compass_buttons<?php echo $model_array['id']; ?> = document.getElementsByClassName('ar-compass-button');
        const ar_compass_image<?php echo $model_array['id']; ?> = document.getElementById('ar-compass-image<?php echo $suffix;?>');

        /*document.querySelector('.ar_rotate_limit').addEventListener('change', function(e) {
            var suffix = '';

            if(e.target.hasAttribute('data-variation')){
                if(e.target.getAttribute('data-variation') != '')
                    suffix = '_var_' + e.target.getAttribute('data-variation');
            }
            
            const min_orbit_arr<?php echo $model_array['id']; ?> = modelViewer<?php echo $model_array['id']; ?>.getAttribute("min-camera-orbit").split(" ");
            const max_orbit_arr<?php echo $model_array['id']; ?> = modelViewer<?php echo $model_array['id']; ?>.getAttribute("max-camera-orbit").split(" ");
            var element<?php echo $model_array['id']; ?> = document.getElementById("ar_rotation_limits" + suffix);

            console.log("ar_rotation_limits" + suffix);

            if (e.target.checked == true){
                element<?php echo $model_array['id']; ?>.style.display = "block";
            }else{
                element<?php echo $model_array['id']; ?>.style.display = "none";
                modelViewer<?php echo $model_array['id']; ?>.setAttribute("min-camera-orbit", 'auto auto '+min_orbit_arr<?php echo $model_array['id']; ?>[2]);
                modelViewer<?php echo $model_array['id']; ?>.setAttribute("max-camera-orbit", 'Infinity auto '+max_orbit_arr<?php echo $model_array['id']; ?>[2]);
                document.getElementById("_ar_compass_top_value" + suffix).value = '';
                document.getElementById("_ar_compass_bottom_value" + suffix).value = '';
                document.getElementById("_ar_compass_left_value" + suffix).value = '';
                document.getElementById("_ar_compass_right_value" + suffix).value = '';
                document.getElementById("ar-compass-top" + suffix).style.backgroundColor = '#e2e2e2';
                document.getElementById("ar-compass-bottom" + suffix).style.backgroundColor = '#e2e2e2';
                document.getElementById("ar-compass-left" + suffix).style.backgroundColor = '#e2e2e2';
                document.getElementById("ar-compass-right" + suffix).style.backgroundColor = '#e2e2e2';
            }
        });
        
        
        // Add a click event listener to each button
        for (let i = 0; i < ar_compass_buttons<?php echo $model_array['id']; ?>.length; i++) {
            ar_compass_buttons<?php echo $model_array['id']; ?>[i].addEventListener('mouseenter', function(e) {
                const id = this.id;
                var suffix = '';
                if(e.target.hasAttribute('data-variation')){
                    if(e.target.getAttribute('data-variation') != '')
                        suffix = '_var_' + e.target.getAttribute('data-variation');
                }

                if (id == 'ar-compass-top' + suffix){
                    ar_compass_image<?php echo $model_array['id']; ?>.style.transform = 'rotate(0deg)';
                }else if (id == 'ar-compass-bottom' + suffix){
                    ar_compass_image<?php echo $model_array['id']; ?>.style.transform = 'rotate(180deg)';
                }else if (id == 'ar-compass-right<?php echo $suffix;?>'){
                    ar_compass_image<?php echo $model_array['id']; ?>.style.transform = 'rotate(90deg)';
                }else if (id == 'ar-compass-left<?php echo $suffix;?>'){
                    ar_compass_image<?php echo $model_array['id']; ?>.style.transform = 'rotate(270deg)';
                }
            });
            ar_compass_buttons<?php echo $model_array['id']; ?>[i].addEventListener('click', function() {
                const id = this.id;
                const min_orbit_arr<?php echo $model_array['id']; ?> = modelViewer<?php echo $model_array['id']; ?>.getAttribute("min-camera-orbit").split(" ");
                const max_orbit_arr<?php echo $model_array['id']; ?> = modelViewer<?php echo $model_array['id']; ?>.getAttribute("max-camera-orbit").split(" ");
                //Set the input field to the axis rotate value and update the model viewer
                if (id == 'ar-compass-top<?php echo $suffix;?>'){
                    var orbit = modelViewer<?php echo $model_array['id']; ?>.getCameraOrbit();
                    if (document.getElementById("_ar_compass_top_value<?php echo $suffix;?>").value == ''){
                        var orbitString = `${orbit.phi}rad`;
                        document.getElementById("_ar_compass_top_value<?php echo $suffix;?>").value = orbitString;
                        document.getElementById(id).style.backgroundColor = '#f37a23';
                    }else{
                        var orbitString = `auto`;
                        document.getElementById(id).style.backgroundColor = '#e2e2e2';
                        document.getElementById("_ar_compass_top_value<?php echo $suffix;?>").value = '';
                    }
                    modelViewer<?php echo $model_array['id']; ?>.setAttribute("min-camera-orbit", min_orbit_arr<?php echo $model_array['id']; ?>[0]+' '+orbitString+' '+min_orbit_arr<?php echo $model_array['id']; ?>[2]);
                }else if (id == 'ar-compass-bottom<?php echo $suffix;?>'){
                    var orbit = modelViewer<?php echo $model_array['id']; ?>.getCameraOrbit();
                    if (document.getElementById("_ar_compass_bottom_value<?php echo $suffix;?>").value == ''){
                        var orbitString = `${orbit.phi}rad`;
                        document.getElementById("_ar_compass_bottom_value<?php echo $suffix;?>").value = orbitString;
                        document.getElementById(id).style.backgroundColor = '#f37a23';
                    }else{
                        var orbitString = `auto`;
                        document.getElementById(id).style.backgroundColor = '#e2e2e2';
                        document.getElementById("_ar_compass_bottom_value<?php echo $suffix;?>").value = '';
                    }
                    modelViewer<?php echo $model_array['id']; ?>.setAttribute("max-camera-orbit", max_orbit_arr<?php echo $model_array['id']; ?>[0]+' '+orbitString+' '+max_orbit_arr<?php echo $model_array['id']; ?>[2]);
                }else if (id == 'ar-compass-right<?php echo $suffix;?>'){
                    var orbit = modelViewer<?php echo $model_array['id']; ?>.getCameraOrbit();
                    if (document.getElementById("_ar_compass_right_value<?php echo $suffix;?>").value == ''){
                        var orbitString = `${orbit.theta}rad`;
                        document.getElementById("_ar_compass_right_value<?php echo $suffix;?>").value = orbitString;
                        document.getElementById(id).style.backgroundColor = '#f37a23';
                    }else{
                        var orbitString = `Infinity`;
                        document.getElementById(id).style.backgroundColor = '#e2e2e2';
                        document.getElementById("_ar_compass_right_value<?php echo $suffix;?>").value = '';
                    }
                    modelViewer<?php echo $model_array['id']; ?>.setAttribute("max-camera-orbit", orbitString+' '+max_orbit_arr<?php echo $model_array['id']; ?>[1]+' '+max_orbit_arr<?php echo $model_array['id']; ?>[2]);
                }else if (id == 'ar-compass-left<?php echo $suffix;?>'){
                    var orbit = modelViewer<?php echo $model_array['id']; ?>.getCameraOrbit();
                    if (document.getElementById("_ar_compass_left_value<?php echo $suffix;?>").value == ''){
                        var orbitString = `${orbit.theta}rad`;
                        document.getElementById("_ar_compass_left_value<?php echo $suffix;?>").value = orbitString;
                        document.getElementById(id).style.backgroundColor = '#f37a23';
                    }else{
                        var orbitString = `auto`;
                        document.getElementById(id).style.backgroundColor = '#e2e2e2';
                        document.getElementById("_ar_compass_left_value<?php echo $suffix;?>").value = '';
                    }
                    modelViewer<?php echo $model_array['id']; ?>.setAttribute("min-camera-orbit", orbitString+' '+min_orbit_arr<?php echo $model_array['id']; ?>[1]+' '+min_orbit_arr<?php echo $model_array['id']; ?>[2]);
                }
                modelViewer<?php echo $model_array['id']; ?>.removeAttribute("auto-rotate");
                document.getElementById("_ar_rotate").checked = true;
            });
        }*/
        //Animation Selector
            const animationSelector<?php echo $model_array['id']; ?> = document.getElementById('_ar_animation_selection<?php echo $suffix;?>');
            const animationDiv<?php echo $model_array['id']; ?> = document.getElementById('animationDiv<?php echo $model_array['id']; ?>');
            // Load the model and retrieve animation names
            modelViewer<?php echo $model_array['id']; ?>.addEventListener('load', () => {
                const names = modelViewer<?php echo $model_array['id']; ?>.availableAnimations;
    
                if (names && names.length > 0) {
                    names.forEach((animationName, index) => {
                        const option = document.createElement('option');
                        option.value = animationName;
                        option.text = animationName || `Animation ${index + 1}`;
                        animationSelector<?php echo $model_array['id']; ?>.appendChild(option);
                        // Preselect an option if it matches the PHP variable value
                        if (animationName === "<?php echo get_post_meta( $model_array['id'], '_ar_animation_selection', true );?>") {
                            option.selected = true;
                            modelViewer<?php echo $model_array['id']; ?>.animationName = animationName;
                        }
                    });
                    // Set the display style to "block" if animations exist
                    animationDiv<?php echo $model_array['id']; ?>.style.display = 'block';
                    // Add event listener to change animations
                    animationSelector<?php echo $model_array['id']; ?>.addEventListener('change', () => {
                        const selectedAnimationName = animationSelector<?php echo $model_array['id']; ?>.value;
                        modelViewer<?php echo $model_array['id']; ?>.animationName = selectedAnimationName;
                    });
                }
            });
        modelViewer<?php echo $model_array['id']; ?>.addEventListener('camera-change', () => {
            const orbit = modelViewer<?php echo $model_array['id']; ?>.getCameraOrbit();
            const orbitString = `${orbit.theta}rad ${orbit.phi}rad ${orbit.radius}m`;
            jQuery(document).ready(function($){
                $( "#camera_view_button" ).click(function() {
                    document.getElementById("_ar_camera_orbit_set").style.display='block';
                    document.getElementById("_ar_camera_orbit").value=orbitString;
                });
            });
        });
        
        /*
        document.getElementById('_ar_disable_zoom<?php echo $suffix;?>').addEventListener('change', function() {
            if (document.getElementById("_ar_disable_zoom<?php echo $suffix;?>").checked == true){
                modelViewer<?php echo $model_array['id']; ?>.setAttribute("disable-zoom",true);
            }else{
                modelViewer<?php echo $model_array['id']; ?>.removeAttribute("disable-zoom");
            }
        });
        document.getElementById('_ar_rotate<?php echo $suffix;?>').addEventListener('change', function() {
            if (document.getElementById("_ar_rotate<?php echo $suffix;?>").checked == true){
                modelViewer<?php echo $model_array['id']; ?>.removeAttribute("auto-rotate");
            }else{
                modelViewer<?php echo $model_array['id']; ?>.setAttribute("auto-rotate",true);
            }
        });*/

        document.querySelector('._glb_file_field').addEventListener('change', function(e) {
            var model_id = this.getAttribute('data-model');
            var element = document.getElementById("model_" + model_id);
            element.setAttribute("src", this.value);
            var element2 = document.getElementById("ar_admin_model_" + model_id);
            element2.style.display = "block";
        });
       
        /*document.getElementById('_ar_zoom_in<?php echo $suffix;?>').addEventListener('change', function() {
            var element = document.getElementById("model_<?php echo $model_array['id']; ?>");
            if (this.value == 'default'){
                element.setAttribute("min-camera-orbit", 'auto auto 20%');
            }else{
                const min_orbit_arr<?php echo $model_array['id']; ?> = element.getAttribute("min-camera-orbit").split(" ");
                element.setAttribute("min-camera-orbit", min_orbit_arr<?php echo $model_array['id']; ?>[0]+' '+min_orbit_arr<?php echo $model_array['id']; ?>[1]+' '+(100 - this.value) +'%');
            }
        });
        document.getElementById('_ar_zoom_out<?php echo $suffix;?>').addEventListener('change', function() {
            var element = document.getElementById("model_<?php echo $model_array['id']; ?>");
            if (this.value == 'default'){
                element.setAttribute("max-camera-orbit", 'Infinity auto 300%');
            }else{
                const max_orbit_arr<?php echo $model_array['id']; ?> = element.getAttribute("max-camera-orbit").split(" ");
                element.setAttribute("max-camera-orbit", max_orbit_arr<?php echo $model_array['id']; ?>[0]+' '+max_orbit_arr<?php echo $model_array['id']; ?>[1]+' '+(((this.value/100)*400)+100) +'%');
            }
        });
        document.getElementById('_ar_field_of_view<?php echo $suffix;?>').addEventListener('change', function() {
            var element = document.getElementById("model_<?php echo $model_array['id']; ?>");
            if (this.value == 'default'){
                element.setAttribute("field-of-view", '');
            }else{
                element.setAttribute("field-of-view", this.value +'deg');
            }
        });
        document.getElementById('_ar_environment_image<?php echo $suffix;?>').addEventListener('change', function() {
            var element = document.getElementById("model_<?php echo $model_array['id']; ?>");
            if (document.getElementById("_ar_environment_image<?php echo $suffix;?>").checked == true){
                element.setAttribute("environment-image", 'legacy');
            }else{
                element.setAttribute("environment-image", '');
            }
            console.log(document.getElementById("_ar_environment_image<?php echo $suffix;?>").checked);
        });
        document.getElementById('_ar_emissive').addEventListener('change', function() {
            var element = document.getElementById("model_<?php echo $model_array['id']; ?>");
            var isChecked = document.getElementById("_ar_emissive").checked;
        
            if (isChecked) {
                element.setAttribute("emissive", "True");
            } else {
                element.removeAttribute("emissive");
            }
        });
        document.getElementById('_ar_exposure<?=$suffix;?>').addEventListener('change', function() {
            var element = document.getElementById("model_<?php echo $model_array['id']; ?>");
            element.setAttribute("exposure", this.value);
        });
        document.getElementById('_ar_shadow_intensity<?php echo $suffix;?>').addEventListener('change', function() {
            var element = document.getElementById("model_<?php echo $model_array['id']; ?>");
            element.setAttribute("shadow-intensity", this.value);
        });
        document.getElementById('_ar_light_color').addEventListener('change', function() {
                var element = document.getElementById("model_<?php echo $model_array['id']; ?>");
                element.setAttribute("light-color", this.value);
            });
        document.getElementById('_ar_shadow_softness<?php echo $suffix;?>').addEventListener('change', function() {
            var element = document.getElementById("model_<?php echo $model_array['id']; ?>");
            element.setAttribute("shadow-softness", this.value);
        });*/
        
        //const modelViewer = document.querySelector('#model_<?php echo $model_array['id']; ?>');
        
        
        document.getElementById('_ar_view_hide<?php echo $suffix;?>').addEventListener('change', function() {
            var element = document.getElementById("ar-button_<?php echo $model_array['id']; ?>");
            if (document.getElementById("_ar_view_hide<?php echo $suffix;?>").checked == true){
                element.style.display = "none";
            }else{
                element.style.display = "block";
            }
        });
        
        document.getElementById('_ar_qr_hide<?php echo $suffix;?>').addEventListener('change', function() {
            var element = document.getElementById("ar-qrcode_<?php echo $model_array['id']; ?>");
            if (document.getElementById("_ar_qr_hide<?php echo $suffix;?>").checked == true){
                element.style.display = "none";
            }else{
                element.style.display = "block";
            }
        });
        
        document.getElementById('_ar_hide_dimensions<?php echo $suffix;?>').addEventListener('change', function() {
                var element = document.getElementById("controls");
                var element_checkbox = document.getElementById("show-dimensions_<?php echo $model_array['id']; ?>");
                if (document.getElementById("_ar_hide_dimensions<?php echo $suffix;?>").checked == true){
                    element.style.display = "none";
                    element_checkbox.checked = false;
                    const modelViewer<?php echo $model_array['id']; ?> = document.querySelector('#model_<?php echo $model_array['id']; ?>');
                    modelViewer<?php echo $model_array['id']; ?>.querySelectorAll('button').forEach((hotspot) => {
                      if ((hotspot.classList.contains('dimension'))||(hotspot.classList.contains('dot'))){
                            hotspot.classList.add('nodisplay');
                      }
                    });
                }else{
                    element.style.display = "block";
                }
            });
        
        document.getElementById('_ar_hide_reset<?php echo $suffix;?>').addEventListener('change', function() {
                var element = document.getElementById("ar-reset_<?php echo $model_array['id']; ?>");
                if (document.getElementById("_ar_hide_reset<?php echo $suffix;?>").checked == true){
                    element.style.display = "none";
                }else{
                    element.style.display = "block";
                }
            });
            
        /*[ _ar_x<?php echo $suffix;?>, _ar_y<?php echo $suffix;?>, _ar_z<?php echo $suffix;?> ].forEach(function(element) {
            element.addEventListener('change', function() {
                var x = document.getElementById('_ar_x<?php echo $suffix;?>').value;
                var y = document.getElementById('_ar_y<?php echo $suffix;?>').value;
                var z = document.getElementById('_ar_z<?php echo $suffix;?>').value;
                const updateScale = () => {
                  modelViewerTransform<?php echo $model_array['id']; ?>.scale = x +' '+ y +' '+ z;
                };
                updateScale();
            });
        });


         document.querySelector('._skybox_file_field').addEventListener('change', function(e) {
            var model_id = this.getAttribute('data-model');
            var element = document.getElementById("model_" + model_id);
            element.setAttribute("skybox-image", this.value);
        });

        document.getElementById('_ar_environment').addEventListener('change', function() {
            var element = document.getElementById("model_<?php echo $model_array['id']; ?>");
            element.setAttribute("environment-image", this.value);
        });
        document.getElementById('_ar_placement<?php echo $suffix;?>').addEventListener('change', function() {
            var element = document.getElementById("model_<?php echo $model_array['id']; ?>");
            if (this.value == 'floor'){
                element.setAttribute("ar-placement", '');
            }else{
                element.setAttribute("ar-placement", this.value);
            }
        });

        */
        document.getElementById('_ar_animation<?php echo $suffix;?>').addEventListener('change', function() {
            var element = document.getElementById("ar-button-animation_");
            if (document.getElementById("_ar_animation<?php echo $suffix;?>").checked == true){
                element.style.display = "block";
            }else{
                element.style.display = "none";
            }
        });
        
        document.body.addEventListener( 'keyup', function ( event ) {
            //Hotspots update on change 
            if( event.target.id.startsWith('_ar_hotspots' )) {
                var hotspot_name = event.target.getAttribute("hotspot_name");
                var hotspot_link = event.target.getAttribute("hotspot_link");
                var match = event.target.id.match(/\[([0-9]+)\]/);
                var index = match ? match[1] : null;
                if (hotspot_name){
                    var hotspot_content = document.getElementById(event.target.getAttribute("hotspot_name")).innerHTML;
                    // Extract the index from the currentId
                    if (index !== null) {
                        // Replace "annotation" with "link" and construct the new id
                        var newId = event.target.id.replace('annotation', 'link');
                        var inputlink = document.getElementById(newId).value;
                    }
                    var inputtext = event.target.value;
                }
                if (hotspot_link){
                    var inputlink = event.target.value;
                    // Replace "link" with "annotation" and construct the new id
                    var newId = event.target.id.replace('link', 'annotation');
                    var inputtext = document.getElementById(newId).value;
                    var hotspot_name = hotspot_link;
                }
                    
                if (inputlink){
                    inputtext = '<a href="'+inputlink+'" target="_blank">'+inputtext+'</a>';
                }
                document.getElementById(hotspot_name).innerHTML='<div class="annotation">'+inputtext+'</div>';
            
            };
            //CTA update on change 
            if( event.target.id=='_ar_cta') {
                document.getElementById("ar-cta-button-container").style="display:block";
                document.getElementById("ar-cta-button").innerHTML=event.target.value;
            };
        });
        
        //Custom CSS Importing
        function importCSS(){
            var css_content = '<?php if ($ar_css_import_global!=''){ echo ar_encodeURIComponent($ar_css_import_global);}else{echo ar_encodeURIComponent($ar_css_import);}?>';
            document.getElementById('_ar_css').value = decodeURI(css_content);
            <?php 
            $ar_css_positions = get_option('ar_css_positions');
            if (is_array($ar_css_positions)){
                foreach ($ar_css_positions as $k => $v){
                      echo "document.getElementById('_ar_css_positions[".$k."]').value = '".$v."';
                      ";
                }
            }
            ?>
        }
        
        document.getElementById('_ar_css_override').addEventListener('change', function() {
            var element = document.getElementById("ar_custom_css_div");
            if (document.getElementById("_ar_css_override").checked == true){
                element.style.display = "block";
            }else{
                element.style.display = "none";
            }
        });
        
    
      
        document.addEventListener('DOMContentLoaded', function () {
        // Convert the JSON-encoded PHP array to a JavaScript array
        var arOpenTabsArray = <?php echo $jsArray; ?>;

        // Loop through each button ID and trigger a click
        arOpenTabsArray.forEach(function (buttonId) {
            var ar_tab_button = document.getElementById(buttonId);

            // Check if the button element exists
            if (ar_tab_button) {
                ar_tab_button.click();
                console.log("Button with ID '" + buttonId + "' opened.");
            } else {
               // console.log("Button with ID '" + buttonId + "' not found.");
            }
        });
    });
    
    
      jQuery(document).ready(function($){
          
        var asset_JsonList = {"asset_Table" : 
            [
                    {"modelMakeID" : "1","modelMake" : "1.0"},
                    {"modelMakeID" : "2","modelMake" : "1.4142"},
                    {"modelMakeID" : "3","modelMake" : "1.25"},
            		{"modelMakeID" : "4","modelMake" : "1.5"},
            		{"modelMakeID" : "5","modelMake" : "1.33"}
            ]};
        var modelTypeJsonList = {"1.0" : 
            [
                    {"modelTypeID" : "1","modelType" : "100%"},
                    {"modelTypeID" : "1.5","modelType" : "150%"},
                    {"modelTypeID" : "2","modelType" : "200%"},
                    {"modelTypeID" : "2.5","modelType" : "250%"},
                    {"modelTypeID" : "3","modelType" : "300%"},
                    {"modelTypeID" : "4","modelType" : "400%"},
                    {"modelTypeID" : "5","modelType" : "500%"}
            ],
            "1.4142" : 
            [
                    {"modelTypeID" : "1","modelType" : "A4 21.0 x 29.7cm / 8.3 x 11.7in"},
                    {"modelTypeID" : "1.41","modelType" : "A3 29.7 x 42cm / 11.7 x 16.5in"},
                    {"modelTypeID" : "2","modelType" : "A2 42 x 59.4cm / 16.5 x 23.4in"},
                    {"modelTypeID" : "2.83","modelType" : "A1 59.4 x 84.1cm / 23.4 x 33.1in"}
            ],
            "1.25" : 
            [
                    {"modelTypeID" : "1","modelType" : "20 x 25cm / 8 x 10in"},
                    {"modelTypeID" : "1.5","modelType" : "30.5 x 38.0cm / 12 x 15in"},
                    {"modelTypeID" : "2","modelType" : "41 x 51cm /16 x 20in"},
                    {"modelTypeID" : "3","modelType" : "61 x 76cm / 24 x 30in"}
            ],
            "1.5" : 
            [
                    {"modelTypeID" : "1","modelType" : "20 x 30cm"},
                    {"modelTypeID" : "1","modelType" : "20 x 30cm / 8 x 12in"},
                    {"modelTypeID" : "1.5","modelType" : "30 x 46cm / 12 x 18in"},
                    {"modelTypeID" : "2","modelType" : "41 x 51cm / 16 x 24in"},
                    {"modelTypeID" : "2.5","modelType" : "51 x 76cm / 20 x 30in"}
            ],
            "1.33" : 
            [
                    {"modelTypeID" : "1","modelType" : "23 x 30cm / 9 x 12in"},
                    {"modelTypeID" : "1.3","modelType" : "30 x 41cm/ 12 x 16in"},
                    {"modelTypeID" : "1.6","modelType" : "38 x 51cm/ 15 x 20in"},
                    {"modelTypeID" : "2","modelType" : "46 x 61cm / 18 x 24in"}
            ]
        };
        var ModelListItems= "";
        for (var i = 0; i < asset_JsonList.asset_Table.length; i++){
            ModelListItems+= "<option value='" + asset_JsonList.asset_Table[i].modelMakeID + "'>" + asset_JsonList.asset_Table[i].modelMake + "</option>";
        }
        $("#makeSelectionBox").html(ModelListItems);
    
    var updatear_asset_size_options = function(ratio) {
        console.log('updating with ', ratio);
        var listItems = "";
        if (ratio in modelTypeJsonList) {
    
        } else {
    
            ratio = '1.0';
        }
        if (ratio in modelTypeJsonList) {
            for (var i = 0; i < modelTypeJsonList[ratio].length; i++) {
                listItems += "<option value='" + modelTypeJsonList[ratio][i].modelTypeID + "'>" + modelTypeJsonList[ratio][i].modelType + "</option>";
            }
            $("select#ar_asset_size").html(listItems);
            $('#ar_asset_size_container').css('display', 'block');
            if ($('#_ar_asset_file').val()) {
                $('#ar_asset_builder_model_done').html('&#10003;');
                $('#ar_asset_builder_submit_container').css('display', 'block');
            }
        }
    }
    
    // Light Color - initialize the WordPress color picker
    $('#_ar_light_color').wpColorPicker({
        palettes: ['#ff0000', '#00ff00', '#0000ff', '#ffffff', '#000000', '#cccccc'],
        change: function(event, ui) {
            // Handle color change event (optional)
            console.log('Selected color:', ui.color.toString());
        }
    });
    function ar_update_size_fn(){ 
        var ratio = $('#ar_asset_ratio').val();
        if (ratio === '1') {
            ratio = '1.0';
        }
        $('#ar_asset_ratio_select').val(ratio);
        // Remove " Matches your Image" from all options
        $('#ar_asset_ratio_select option:not([value="' + ratio + '"])').each(function() {
            var currentText = $(this).text();
            $(this).text(currentText.replace(' - Suggested for your Image', ''));
        });
        // Get the original text of the selected option
        var originalText = $('#ar_asset_ratio_select option[value="' + ratio + '"]').text();
        
        // Update the text content of the selected option
        $('#ar_asset_ratio_select option[value="' + ratio + '"]').text(originalText + ' - Suggested for your Image');
        
        
        updatear_asset_size_options(ratio); 
        $('#ar_asset_builder_texture_done').html('&#10003;');
    }  
    ar_update_size_function = ar_update_size_fn;
    $("select#ar_asset_ratio_select").on('change',function(){
        var selectedRatio = $('#ar_asset_ratio_select option:selected').val();
        $('#ar_asset_ratio').val(selectedRatio);
        updatear_asset_size_options(selectedRatio);
    });  
    //Update the scale of the model
    $("select#ar_asset_size").on('change',function(){
        var selectedSize = $('#ar_asset_size option:selected').val();
        $('#_ar_x<?php echo $suffix;?>').val(selectedSize);
        $('#_ar_y<?php echo $suffix;?>').val(selectedSize);
        $('#ar_asset_builder_size_done').html('&#10003;');
        
    });  
});
function calculateImageRatio() {
      var imageUrl = jQuery('#_asset_texture_file_0').val();
      // Create an image element dynamically
      var img = new Image();

      // Set the source URL for the image
      img.src = imageUrl;

      // Wait for the image to load
      img.onload = function() {
        // Determine if the image is landscape or portrait
        var orientation;
        if (img.width > img.height) {
          orientation = 'landscape';
        } else if (img.width < img.height) {
          orientation = 'portrait';
        } else {
          orientation = 'square';
        }

        // Set the longer dimension as width
        var width = (orientation === 'landscape') ? img.width : img.height;
        var height = (orientation === 'landscape') ? img.height : img.width;

        // Update the select field with the orientation
        //jQuery('#ar_asset_orientation').find('option[value="' + orientation + '"]').prop('selected', true);
        jQuery('#ar_asset_orientation').val(orientation);
        // Calculate the width-to-height ratio
        var ratio = width / height;

        // Define the target ratios
        //var targetRatios = [2 / 3, 4 / 5, 3 / 4, 11 / 14, 1.4142]; // A4:A3 paper ratio is approximately 1.4142
        var targetRatios = [1.0, 1.5, 1.25, 1.33, 1.27, 1.4142]; // A4:A3 paper ratio is approximately 1.4142

        // Find the closest ratio
        var closestRatio = findClosestRatio(ratio, targetRatios);

        // Output the result
        jQuery('#ar_asset_ratio').val(closestRatio);
        //alert('Closest Ratio: ' + closestRatio);
         // Execute the ar_update_size_fn function
        ar_update_size_function(closestRatio);
      };
    }

    // Function to find the closest ratio
    function findClosestRatio(actualRatio, targetRatios) {
      var closestRatio = targetRatios[0];
      var minDifference = Math.abs(actualRatio - targetRatios[0]);

      for (var i = 1; i < targetRatios.length; i++) {
        var difference = Math.abs(actualRatio - targetRatios[i]);
        if (difference < minDifference) {
          minDifference = difference;
          closestRatio = targetRatios[i];
        }
      }

      return closestRatio;
    }
    function asset_display_thumb() {
        
        var imageUrl = jQuery('#_asset_texture_file_0').val();
        jQuery('#asset_thumb_img').attr('src', imageUrl);
    }
    
    // Trigger the function when the value of _asset_texture_file_0 changes
    jQuery('#_asset_texture_file_0').on('input', calculateImageRatio);
    jQuery('#_asset_texture_file_0').on('input', asset_display_thumb);
    
    
    
        //Save screenshot of model
        function downloadPosterToDataURL() {
                var btn = document.getElementById("downloadPosterToBlob");
                btn.innerHTML = 'Creating Image';
                btn.disabled = true;
                const url = modelViewer<?php echo $model_array['id']; ?>.toDataURL("image/png").replace("image/png", "image/octet-stream");
                const a = document.createElement("a");
                document.getElementById("_ar_poster_image_field").value=url;
                var xhr = new XMLHttpRequest();
                //document.getElementById("nonce").value="<?php wp_create_nonce('set_ar_featured_image'); ?>"
                var data = new FormData();
                data.append('post_ID', document.getElementById("post_ID").value);
                
                if(document.getElementById("original_post_title")){
                    data.append('post_title', document.getElementById("original_post_title").value);
                } else if(document.getElementsByClassName("wp-block-post-title")) {
                    data.append('post_title', document.getElementsByClassName("wp-block-post-title")[0].value);
                } else {
                    data.append('post_title','armodel-' + document.getElementById("post_ID").value);
                }
                data.append('_ar_poster_image_field',document.getElementById("_ar_poster_image_field").value);
                data.append('action',"set_ar_featured_image");
                data.append('nonce',"<?php echo wp_create_nonce('set_ar_featured_image'); ?>");
                //data.nonce = "<?php wp_create_nonce('set_ar_featured_image'); ?>";
               // console.log(data);
                xhr.open("POST", "<?php echo site_url('wp-json/arforwp/v2/set_ar_featured_image/');?>", true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                /*xhr.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        var attachmentID = xhr.responseText; 
                    wp.media.featuredImage.set( attachmentID );
                   }
                };*/

                //convert to json
                var object = {};
                data.forEach(function(value, key){
                    object[key] = value;
                });
                var json = JSON.stringify(object);


                xhr.onload = function () { 
                    var attachmentID = xhr.responseText; 
                    wp.media.featuredImage.set( attachmentID );
                    btn.innerHTML = 'Set Featured Image';
                    btn.disabled = false;
                }
                
                xhr.send(json);
                return false;
            }
    </script>
    <!-- HOTSPOTS -->
    <!-- The following libraries and polyfills are recommended to maximize browser support -->
    <!-- Web Components polyfill to support Edge and Firefox < 63 -->
    <script src="https://unpkg.com/@webcomponents/webcomponentsjs@2.1.3/webcomponents-loader.js"></script>
    <!-- Intersection Observer polyfill for better performance in Safari and IE11 -->
    <script src="https://unpkg.com/intersection-observer@0.5.1/intersection-observer.js"></script>
    <!-- Resize Observer polyfill improves resize behavior in non-Chrome browsers -->
    <script src="https://unpkg.com/resize-observer-polyfill@1.5.1/dist/ResizeObserver.js"></script>
    <script>
        var hotspotCounter = <?php echo $hotspot_count ? $hotspot_count : '0'; ?>;
        function addHotspot(MouseEvent) {
            //var _ar_hotspot_check = document.getElementById('_ar_hotspot_check').value;
            if (document.getElementById("_ar_hotspot_check").checked != true){
            return;
                
            }
            var inputtext = document.getElementById('_ar_hotspot_text').value;
        
            // if input = nothing then alert error if it isnt then add the hotspot
            if (inputtext == ""){
                alert("<?php _e( 'Enter hotspot text first, then click the Add Hotspot button.', 'ar-for-woocommerce' );?>");
                return;
            }else{
                var inputlink = document.getElementById('_ar_hotspot_link').value;
                if (inputlink){
                    inputtext = '<a href="'+inputlink+'" target="_blank">'+inputtext+'</a>';
                }
                const viewer = document.querySelector('#model_<?php echo $model_array['id']; ?>');
            
                const x = event.clientX;
                const y = event.clientY;
                const positionAndNormal = viewer.positionAndNormalFromPoint(x, y);
                
                // if the model is not clicked return the position in the console
                if (positionAndNormal == null) {
                    console.log('no hit result: mouse = ', x, ', ', y);
                    return;
                }
                const {position, normal} = positionAndNormal;
                
                // create the hotspot
                const hotspot = document.createElement('button');
                hotspot.slot = `hotspot-${hotspotCounter ++}`;
                hotspot.classList.add('hotspot');
                hotspot.id = `hotspot-${hotspotCounter}`;
                hotspot.dataset.position = position.toString();
                if (normal != null) {
                    hotspot.dataset.normal = normal.toString();
                }
                viewer.appendChild(hotspot);
                // adds the text to last hotspot
                var element = document.createElement("div");
                element.classList.add('annotation');
                element.innerHTML = inputtext;
                document.getElementById(`hotspot-${hotspotCounter}`).appendChild(element);
                
                //Add Hotspot Input fields
                var hotspot_container = document.getElementById(`_ar_hotspot_container_${hotspotCounter -1}`);
                
		    
                hotspot_container.insertAdjacentHTML('afterend', `<div id="_ar_hotspot_container_${hotspotCounter}"><p class="form-field _ar_autoplay_field "><label for="_ar_animation">Hotspot ${hotspotCounter}</label><span class="ar_admin_field" id="_ar_hotspot_field_${hotspotCounter}">`);
                
                var hotspot_fields = document.getElementById(`_ar_hotspot_field_${hotspotCounter}`);
                var inputList = document.createElement("input");
                inputList.setAttribute('type','text');
                inputList.setAttribute('class','regular-text hotspot_annotation');
                inputList.setAttribute('id',`_ar_hotspots[link][${hotspotCounter}]`);
                inputList.setAttribute('name',`_ar_hotspots[link][${hotspotCounter}]`);
                inputList.setAttribute('hotspot_name',`hotspot-${hotspotCounter}`);
                inputList.setAttribute('value',document.getElementById('_ar_hotspot_link').value);
                inputList.setAttribute('placeholder','Link');
                hotspot_fields.insertAdjacentElement('afterend', inputList);   
                
                
                var inputList = document.createElement("input");
                inputList.setAttribute('type','text');
                inputList.setAttribute('class','regular-text hotspot_annotation');
                inputList.setAttribute('id',`_ar_hotspots[annotation][${hotspotCounter}]`);
                inputList.setAttribute('name',`_ar_hotspots[annotation][${hotspotCounter}]`);
                inputList.setAttribute('hotspot_name',`hotspot-${hotspotCounter}`);
                inputList.setAttribute('value',document.getElementById('_ar_hotspot_text').value);
                hotspot_fields.insertAdjacentElement('afterend', inputList);
                
                var inputList = document.createElement("input");
                inputList.setAttribute('hidden','true');
                inputList.setAttribute('id',`_ar_hotspots[data-position][${hotspotCounter}]`);
                inputList.setAttribute('name',`_ar_hotspots[data-position][${hotspotCounter}]`);
                inputList.setAttribute('value',hotspot.dataset.position);
                hotspot_fields.insertAdjacentElement('afterend', inputList);
                
                var inputList = document.createElement("input");
                inputList.setAttribute('hidden','true');
                inputList.setAttribute('id',`_ar_hotspots[data-normal][${hotspotCounter}]`);
                inputList.setAttribute('name',`_ar_hotspots[data-normal][${hotspotCounter}]`);
                inputList.setAttribute('value',hotspot.dataset.normal);
                hotspot_fields.insertAdjacentElement('afterend', inputList);
                
                hotspot_fields.insertAdjacentHTML('afterend', '</span></p></div>');
                var additionalPanel = document.getElementById("ar_additional_interactions_panel");

                // Check if the element exists
                if (additionalPanel) {
                    // Get the current height and add 100px to it
                    var newHeight = additionalPanel.offsetHeight + 100;
                
                    // Set the new height to the element
                    //additionalPanel.style.height = newHeight + "px";
                    additionalPanel.style.maxHeight = newHeight + "px";
                }
                //Reset hotspot text box and checkbox
                document.getElementById('_ar_hotspot_text').value = "";
                document.getElementById('_ar_hotspot_link').value = "";
                document.getElementById("_ar_hotspot_check").checked = false;
                
                //Show Remove Hotspot button
                document.getElementById('_ar_remove_hotspot').style = "display:block;";
            }
        }
        function enableHotspot(event){
            var suffix = '';
            //if(event.target.hasAttribute('data-variation')){
            //    suffix = '_var_' + event.target.getAttribute('data-variation');
            //}
            var inputtext = document.getElementById('_ar_hotspot_text' + suffix).value;
            if (inputtext == ""){
                alert("<?php _e( 'Enter hotspot text first, then click Add Hotspot button.', 'ar-for-woocommerce' );?>");
                return;
            }else{
                document.getElementById("_ar_hotspot_check").checked = true;
            }
        }
        function removeHotspot(){
            var el = document.getElementById(`_ar_hotspot_container_${hotspotCounter}`);
            var el2 = document.getElementById(`hotspot-${hotspotCounter}`);
            if (el == null){
                alert("No hotspots to delete");
            }else{
                hotspotCounter --;
                el.remove(); // Removes the last added hotspot fields
                el2.remove(); // Removes the last added hotspot from model
            }
        }
    </script>
    <?php
    
    //Output Upload Choose AR Model Files Javascript
    //echo ar_upload_button_js($model_array['id'], $variation_id);
}
add_action( 'woocommerce_variation_header', function( \WP_Post $variation ) {

            $variation = wc_get_product( $variation->ID );
            $variation_id = $variation->get_id();
            echo '<span class="ardisplay_options" style="padding-left: 20px"> <a style="text-decoration:none;" id="ar_variation_button_'.$variation_id.'" > AR Model</a></span>';
            echo '<div id="ar_variation_'.$variation_id.'" style="display:none;">
                    <div class="ar_variation_view">
                        ';
                            ar_woo_tab_panel($variation_id, $variation_id);
                            echo '
                        <div class="ar-popup-btn-container hide_on_devices"><button type="button" id="arqr_close_'.$variation_id.'_pop" class="ar_popup-btn hide_on_devices" style="cursor: pointer"  onclick="document.getElementById(\'ar_variation_'.$variation_id.'\').style.display = \'none\';"><img src="'.esc_url( plugins_url( "assets/images/close.png", __FILE__ ) ).'" class="ar-fullscreen_btn-img"></button></div>
                    </div>
                </div>';
                ?>
                <script>
                //let modelFields = [];
                    
                    //console.log(modelFields);

                ( function($) {
                 
                    $("#ar_variation_button_<?php echo $variation_id;?>").click(function(){
                      $("#ar_variation_<?php echo $variation_id;?>").toggle();
                    });
                    
                    //console.log(modelFields);
                    
                  
                } ) ( jQuery );	
                </script>
    <?php
            

        } );



function ar_woo_admin_scripts(){
  wp_enqueue_script('jquery');
  wp_enqueue_script('media-upload');
}
?>