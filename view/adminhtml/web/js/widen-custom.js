require([
    'jquery',
    'select2'
], function ($) {
    jQuery(document).ready(function () {
        jQuery('#acquiadam_property').select2();
        jQuery('#acquiadam_property_image_role').select2();
        jQuery('#acquiadam_property_alt_tax').select2();
		jQuery('#acquiadam_property_color').select2();
		jQuery('#acquiadam_property_order').select2();
        jQuery("#import_button").appendTo(".page-actions-buttons");
    });

    
});