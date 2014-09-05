jQuery(function() {
    jQuery( "#add-linking-setting").click( function() {
        linking_post_type = jQuery( "#linking-post-type option:selected" ).val();
        linked_post_type = jQuery( "#related-post-type option:selected" ).val();
        linking_post_title = jQuery( "#linking-post-type option:selected" ).text();
        linked_post_title = jQuery( "#related-post-type option:selected" ).text();
        old_settings = jQuery( "#linking-settings").val();
        new_setting = linking_post_type + ',' + linked_post_type;
        if(old_settings == ''){
            old_settings = new Array();
        }else{
            old_settings = old_settings.split(';');
        }
        check_setting = old_settings.indexOf(new_setting);
        if(check_setting < 0){
            old_settings.push(new_setting);
            new_setting_html = '<li data-linking="'
                + linking_post_type + '" data-related="' + linked_post_type + '">'
                + linking_post_title + ' => ' + linked_post_title
                + ' - <a href="#'+ linking_post_type +','+ linked_post_type
                + '" class="remove-linking-setting">remove</a></li>'
            jQuery( '#linking-settings-list').append( new_setting_html );
            if( jQuery( '#linking-settings-list li').length > 1 ){
                jQuery( '#no-link-settings').addClass( 'hidden' );
            }
        }
        new_settings = old_settings.join(';');
        jQuery( "#linking-settings").val(new_settings);
    });

    jQuery( '.remove-linking-setting').live( 'click', function() {
        remove_data = jQuery( this).attr('href').slice(1);
        old_settings = jQuery( "#linking-settings").val();
        old_settings = old_settings.split(';');
        check_setting = jQuery.inArray(remove_data, old_settings);
        if( check_setting >= 0 ){
            old_settings.splice(check_setting, 1);
        }
        remove_data = remove_data.split(',');
        new_settings = old_settings.join(';');
        jQuery( 'li[data-linking='+remove_data[0]+'][data-related='+remove_data[1]+']').remove();
        jQuery( '#linking-settings').val(new_settings);
        if( jQuery( '#linking-settings-list li').length == 1 ){
            jQuery( '#no-link-settings').removeClass( 'hidden' );
        }
    } );
});