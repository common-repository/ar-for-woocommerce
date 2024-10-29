class ARModel{


    constructor(model_id, variation_id){
        this.modelobj = jQuery('#model_' + model_id);

        this.suffix = '';
        this.variation_id = variation_id;
        if(variation_id)
            this.suffix = '_var_' + variation_id;

        //this.modelScale();
    }

    modelScale(){
        jQuery(document).on('change','#_ar_x_' + this.suffix + ', ._ar_y_' + this.suffix + ', _ar_z_' + this.suffix, function() {
            console.log(this.suffix);
            /*var x = document.getElementById('_ar_x<?php echo $suffix;?>').value;
            var y = document.getElementById('_ar_y<?php echo $suffix;?>').value;
            var z = document.getElementById('_ar_z<?php echo $suffix;?>').value;
            
            const updateScale = () => {
              this.modelobj.scale = x +' '+ y +' '+ z;
            };

            updateScale();*/
            
        });
        
    }
}

let modelFields = [];

jQuery(document).ready(function(){

    jQuery('#woocommerce-product-data').on('click','.ar_accordian', function(event){
                    
        jQuery(this).toggleClass("ar_active");

        var panel = jQuery(this).next();
        //console.log(panel[0].scrollHeight);
        /*if (panel.style.display === "block") {
          panel.style.display = "none";
        } else {
          panel.style.display = "block";
        }*/
        if (panel[0].style.maxHeight) {
          panel[0].style.maxHeight = null;
        } else {
          panel[0].style.maxHeight = panel[0].scrollHeight + "px";
        }
    }); 


    jQuery('#woocommerce-product-data').on('change','._ar_x_field,._ar_y_field,._ar_z_field', function(e) {

        var model_id = getModelID(jQuery(this));
        var suffix = '_var_' + model_id;

        if(modelFields[model_id].variation_id == '')
            suffix = '';

        var x = jQuery('#_ar_x' + suffix).val();
        var y = jQuery('#_ar_y' + suffix).val();
        var z = jQuery('#_ar_z' + suffix).val();
        
        //console.log(x +' '+ y +' '+ z + ' suffix:' + suffix);

        const updateScale = () => {
          modelFields[model_id].modelobj[0].scale = x +' '+ y +' '+ z;
        };

        updateScale();
        e.stopPropagation();
    });


    jQuery('#woocommerce-product-data').on('change','._skybox_file_field', function(e) { 

        var model_id = getModelID(jQuery(this));
        console.log('skybox - ' + jQuery(this).val());

        jQuery('#model_' + model_id).attr("skybox-image", jQuery(this).val());
        e.stopPropagation();
    });

    jQuery('#woocommerce-product-data').on('change','._environment_file_field', function(e) { 
    
        var model_id = getModelID(jQuery(this));
        console.log('environment - ' + jQuery(this).val());

        jQuery('#model_' + model_id).attr("environment-image", jQuery(this).val());
        e.stopPropagation();
    });

    jQuery('#woocommerce-product-data').on('change','._ar_placement_field', function(e) {  
        
        var model_id = getModelID(jQuery(this));
        var placement = jQuery(this).find(":selected").val();

        var element = jQuery('#model_' + model_id);
        if ( placement == 'floor'){
            element.attr("ar-placement", '');
        }else{
            element.attr("ar-placement", placement);
        }

        //console.log(element.attr("ar-placement"));
    });

    jQuery('#woocommerce-product-data').on('change','._ar_zoom_in', function(e) { 
        var model_id = getModelID(jQuery(this));
        var value = jQuery(this).val();
        //console.log(model_id + ' - ' + value);

        var element = document.getElementById("model_" + model_id);
            if (value == 'default'){
                element.setAttribute("min-camera-orbit", 'auto auto 20%');
            }else{
                var min_orbit_arr = element.getAttribute("min-camera-orbit").split(" ");
                element.setAttribute("min-camera-orbit", min_orbit_arr[0]+' '+min_orbit_arr[1]+' '+(100 - value) +'%');
            }
        });

    jQuery('#woocommerce-product-data').on('change','._ar_zoom_out', function(e) { 
        var model_id = getModelID(jQuery(this));
        var value = jQuery(this).val();

        var element = document.getElementById("model_" + model_id);
        if (value == 'default'){
            element.setAttribute("max-camera-orbit", 'Infinity auto 300%');
        }else{
            var max_orbit_arr = element.getAttribute("max-camera-orbit").split(" ");
            element.setAttribute("max-camera-orbit", max_orbit_arr[0]+' '+max_orbit_arr[1]+' '+(((value/100)*400)+100) +'%');
        }
    });

    jQuery('#woocommerce-product-data').on('change','._ar_field_of_view', function(e) {    
        var model_id = getModelID(jQuery(this));
        var value = jQuery(this).val();

        var element = document.getElementById("model_" + model_id);
        if (value == 'default'){
            element.setAttribute("field-of-view", '');
        }else{
            element.setAttribute("field-of-view", value +'deg');
        }
    });

    jQuery('#woocommerce-product-data').on('change','._ar_environment_image', function(e) {
        var model_id = getModelID(jQuery(this));
        var value = jQuery(this).val();

        var element = document.getElementById("model_" + model_id);

        if (jQuery(this).is(':checked')){
            element.setAttribute("environment-image", 'legacy');
        }else{
            element.setAttribute("environment-image", '');
        }
        //console.log(document.getElementById("_ar_environment_image<?php echo $suffix;?>").checked);
    });

    jQuery('#woocommerce-product-data').on('change','._ar_exposure', function(e) {
        var model_id = getModelID(jQuery(this));
        var element = document.getElementById("model_" + model_id);
        element.setAttribute("exposure", jQuery(this).val());
    });

    jQuery('#woocommerce-product-data').on('change','._ar_shadow_intensity', function(e) {
        var model_id = getModelID(jQuery(this));
        var element = document.getElementById("model_" + model_id);
        element.setAttribute("shadow-intensity", jQuery(this).val());
    });

    jQuery('#woocommerce-product-data').on('change','._ar_shadow_softness', function(e) {
        var model_id = getModelID(jQuery(this));
        var element = document.getElementById("model_" + model_id);
        element.setAttribute("shadow-softness", jQuery(this).val());
    });


    jQuery('#woocommerce-product-data').on('change','._ar_view_hide', function(e) {
        var model_id = getModelID(jQuery(this));
        var element = document.getElementById("ar-button_" + model_id);

        if (jQuery(this).is(':checked')){
            element.style.display = "none";
        }else{
            element.style.display = "block";
        }
    });
    
    jQuery('#woocommerce-product-data').on('change','._ar_qr_hide', function(e) {
        var model_id = getModelID(jQuery(this));
        var element = document.getElementById("ar-qrcode_" + model_id);

        if (jQuery(this).is(':checked')){
            element.style.display = "none";
        }else{
            element.style.display = "block";
        }
    });

    jQuery('#woocommerce-product-data').on('change','._ar_hide_reset', function(e) {
        var model_id = getModelID(jQuery(this));
        var element = document.getElementById("ar-reset_" + model_id);

        if (jQuery(this).is(':checked')){
            element.style.display = "none";
        }else{
            element.style.display = "block";
        }
    });
    
    jQuery('#woocommerce-product-data').on('change','._ar_hide_dimensions', function(e) {
        var model_id = getModelID(jQuery(this));
        
        var element = document.getElementById("controls");
        var element_checkbox = document.getElementById("show-dimensions_" + model_id);

        if (jQuery(this).is(':checked')){
            element.style.display = "none";
            element_checkbox.checked = false;
            var modelViewer = document.querySelector('#model_' + model_id);
            modelViewer.querySelectorAll('button').forEach((hotspot) => {
              if ((hotspot.classList.contains('dimension'))||(hotspot.classList.contains('dot'))){
                    hotspot.classList.add('nodisplay');
              }
            });
        }else{
            element.style.display = "block";
        }
    });

    jQuery('#woocommerce-product-data').on('change','._ar_disable_zoom', function(e) {
        var model_id = getModelID(jQuery(this));
        var element = document.getElementById("model_" + model_id);

        if (jQuery(this).is(':checked')){
            element.setAttribute("disable-zoom",true);
        }else{
            element.removeAttribute("disable-zoom");
        }
    });
    jQuery('#woocommerce-product-data').on('change','._ar_rotate', function(e) {
        var model_id = getModelID(jQuery(this));
        var element = document.getElementById("model_" + model_id);

        if (jQuery(this).is(':checked')){
            element.removeAttribute("auto-rotate");
        }else{
            element.setAttribute("auto-rotate",true);
        }
    });
    
    jQuery('#woocommerce-product-data').on('change','.ar_rotate_limit', function(e) {
        var suffix = '';
        var model_id = getModelID(jQuery(this));
        var container = jQuery(this).closest('.armodel_fields_panel');
                
        var min_orbit_arr = modelFields[model_id].modelobj.attr("min-camera-orbit").split(" ");
        var max_orbit_arr = modelFields[model_id].modelobj.attr("max-camera-orbit").split(" ");
        var element = container.find(".ar_rotation_limits_containter").first();


        if (e.target.checked == true){
            element.css('display','block');
        }else{
            element.css('display','none');
            modelFields[model_id].modelobj.attr("min-camera-orbit", 'auto auto '+min_orbit_arr[2]);
            modelFields[model_id].modelobj.attr("max-camera-orbit", 'Infinity auto '+max_orbit_arr[2]);
            container.find("._ar_compass_top_value").first().val('');
            container.find("._ar_compass_bottom_value").first().val('');
            container.find("._ar_compass_left_value").first().val('');
            container.find("._ar_compass_right_value").first().val('');
            container.find(".ar-compass-top").first().css('background-color','#e2e2e2');
            container.find(".ar-compass-bottom").first().css('background-color','#e2e2e2');
            container.find(".ar-compass-left").first().css('background-color','#e2e2e2');
            container.find(".ar-compass-right").first().css('background-color','#e2e2e2');
        }
    });


    // Add a click event listener to each button
        
    jQuery('#woocommerce-product-data').on('mouseenter','.ar-compass-button', function(e) {
        var model_id = getModelID(jQuery(this));
        var container = jQuery(this).closest('.armodel_fields_panel');
        var ar_compass_image = container.find('.ar-compass-image').first();
        var suffix = '_var_' + model_id;
        var id = jQuery(this).attr('id');

        //console.log("mouseenter - " + id);

        if(modelFields[model_id].variation_id == '')
            suffix = '';

        if (jQuery(this).hasClass('ar-compass-top')){
            ar_compass_image.css('transform','rotate(0deg)');
        }else if (jQuery(this).hasClass('ar-compass-bottom')){
            ar_compass_image.css('transform','rotate(180deg)');
        }else if (jQuery(this).hasClass('ar-compass-right')){
            ar_compass_image.css('transform','rotate(90deg)');
        }else if (jQuery(this).hasClass('ar-compass-left')){
            ar_compass_image.css('transform','rotate(270deg)');
        }
    });

    jQuery('#woocommerce-product-data').on('click', '.ar-compass-button', function(e) {    
        var model_id = getModelID(jQuery(this));
        var container = jQuery(this).closest('.armodel_fields_panel');
        var ar_compass_image = container.find('.ar-compass-image').first();
        var suffix = '_var_' + model_id;
        var id = jQuery(this).attr('id');

        var ar_compass_top_value = container.find('._ar_compass_top_value').first();
        var ar_compass_bottom_value = container.find('._ar_compass_bottom_value').first();
        var ar_compass_left_value = container.find('._ar_compass_left_value').first();
        var ar_compass_right_value = container.find('._ar_compass_right_value').first();

        if(modelFields[model_id].variation_id == '')
            suffix = '';

        var min_orbit_arr = modelFields[model_id].modelobj.attr("min-camera-orbit").split(" ");
        var max_orbit_arr = modelFields[model_id].modelobj.attr("max-camera-orbit").split(" ");
        //Set the input field to the axis rotate value and update the model viewer
        if (jQuery(this).hasClass('ar-compass-top')){
            var orbit = modelFields[model_id].modelobj[0].getCameraOrbit();
            if (ar_compass_top_value.val() == ''){
                var orbitString = `${orbit.phi}rad`;
                ar_compass_top_value.val(orbitString);

                document.getElementById(id).style.backgroundColor = '#f37a23';

            }else{
                var orbitString = `auto`;
                document.getElementById(id).style.backgroundColor = '#e2e2e2';
                ar_compass_top_value.val('');
            }
            modelFields[model_id].modelobj[0].setAttribute("min-camera-orbit", min_orbit_arr[0]+' '+orbitString+' '+min_orbit_arr[2]);
        

        } else if (jQuery(this).hasClass('ar-compass-bottom')){
            var orbit = modelFields[model_id].modelobj[0].getCameraOrbit();
            if (ar_compass_bottom_value.val() == ''){
                var orbitString = `${orbit.phi}rad`;
                ar_compass_bottom_value.val(orbitString);
                document.getElementById(id).style.backgroundColor = '#f37a23';
            }else{
                var orbitString = `auto`;
                document.getElementById(id).style.backgroundColor = '#e2e2e2';
                ar_compass_bottom_value.val('');
            }
            modelFields[model_id].modelobj[0].setAttribute("max-camera-orbit", max_orbit_arr[0]+' '+orbitString+' '+max_orbit_arr[2]);
        

        } else if (jQuery(this).hasClass('ar-compass-right')){
            var orbit = modelFields[model_id].modelobj[0].getCameraOrbit();
            if (ar_compass_right_value.val() == ''){
                var orbitString = `${orbit.theta}rad`;
                ar_compass_right_value.val(orbitString);
                document.getElementById(id).style.backgroundColor = '#f37a23';
            }else{
                var orbitString = `Infinity`;
                document.getElementById(id).style.backgroundColor = '#e2e2e2';
                ar_compass_right_value.val('');
            }
            modelFields[model_id].modelobj[0].setAttribute("max-camera-orbit", orbitString+' '+max_orbit_arr[1]+' '+max_orbit_arr[2]);
        

        } else if (jQuery(this).hasClass('ar-compass-left')){
            var orbit = modelFields[model_id].modelobj[0].getCameraOrbit();
            if (ar_compass_left_value.val() == ''){
                var orbitString = `${orbit.theta}rad`;
                ar_compass_left_value.val(orbitString);
                document.getElementById(id).style.backgroundColor = '#f37a23';
            }else{
                var orbitString = `auto`;
                document.getElementById(id).style.backgroundColor = '#e2e2e2';
                ar_compass_left_value.val('');
            }
            modelFields[model_id].modelobj[0].setAttribute("min-camera-orbit", orbitString+' '+min_orbit_arr[1]+' '+min_orbit_arr[2]);
        }

        modelFields[model_id].modelobj[0].removeAttribute("auto-rotate");
        document.getElementById("_ar_rotate" + suffix).checked = true;
    });

    jQuery('#woocommerce-product-data').on('click','.wctoggle-model-fields', function(event){
        event.preventDefault();
        var glb_elem = jQuery(this).closest('.ar_model_files_fields').find('.glb-file-container').first();
        var usdz_elem = jQuery(this).closest('.ar_model_files_fields').find('.usdz-file-container').first();

        if(jQuery(this).data('status') == 'hidden'){
            usdz_elem.removeClass('nodisplay');
            glb_elem.removeClass('nodisplay');
            jQuery(this).data('status','visible');
            jQuery(this).text('Hide Model Fields');
        } else {
            usdz_elem.addClass('nodisplay');
            glb_elem.addClass('nodisplay');
            jQuery(this).data('status','hidden');
            jQuery(this).text('Show Model Fields');
        }

    });

    
        

});

function getModelID(element){
    var element_id = element.closest('.armodel_fields_panel').find('.ar_model_id').first();
    var model_id = element_id.val();

    return model_id;
}


jQuery(document).ready(function(){

    jQuery(document).on('click','#toggle-model-fields', function(event){
        event.preventDefault();
        console.log(jQuery(this).data('status'));
        if(jQuery(this).data('status') == 'hidden'){
            jQuery('#_usdz_file').attr('type','text');
            jQuery('#_glb_file').attr('type','text');
            jQuery(this).data('status','visible');
            jQuery(this).text('Hide Model Fields');
        } else {
            jQuery('#_usdz_file').attr('type','hidden');
            jQuery('#_glb_file').attr('type','hidden');
            jQuery(this).data('status','hidden');
            jQuery(this).text('Show Model Fields');
        }

    });
}); 

