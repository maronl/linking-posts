jQuery(function() {
    jQuery( "#add-linking-setting").click( function() {
        old_settings = jQuery( "#linking-settings").val();
        new_setting = jQuery( "#linking-post-type option:selected" ).val() + ',' + jQuery( "#related-post-type option:selected" ).val();
        old_settings = old_settings.split(';');
        check_setting = old_settings.indexOf(new_setting);
        if(check_setting < 0){
            old_settings.push(new_setting);
        }
        new_settings = old_settings.join(';');
        jQuery( "#linking-settings").val(new_settings);
    });
});