jQuery(function() {

    jQuery( "#sortable" ).sortable();

    jQuery( "#sortable" ).disableSelection();

    jQuery( "#save-issue-menu").click( function() {

        var data = 'action=update_related_posts_orders&post_ID=' + jQuery('#post_ID').val() + '&' + jQuery( "#sortable" ).sortable( 'serialize' );

        jQuery.post(ajaxurl, data, function(response) {
            console.log( 'Got this from the server: ' + response );
        });
    });

    jQuery( "#add-article-issue").click( function() {

        var data = 'action=add_related_post'
            + '&post_ID=' + jQuery('#post_ID').val()
            + '&new_related_post_order=' + ( jQuery( "#sortable li").length + 1 )
            + '&new_related_post_ID=' + jQuery( "#possible-related-posts option:selected" ).val()
            + '&new_related_post_title=' + jQuery( "#possible-related-posts option:selected" ).text();

        jQuery.post(ajaxurl, data, function(response) {

            if(response.status == 1){

                new_article = '<li id="linked_posts_orders_' + response.data.id + '" class="ui-state-default"><div class="dashicons dashicons-sort"></div><a href="/wp-admin/post.php?post=1&amp;action=edit">' + response.data.title + '</a> - <a href="#1" class="remove-post-link">remove</a></li>';

                jQuery("#sortable").append(new_article);
                jQuery("#sortable").sortable('refresh');
            }
        },'json');
    });

    jQuery( ".remove-post-link").click( function() {

        var data = 'action=remove_related_post'
            + '&post_ID=' + jQuery('#post_ID').val()
            + '&related_post_ID=' + jQuery( this ).attr('href').slice(1)

        jQuery.post(ajaxurl, data, function(response) {
            if(response.status == 1){
                jQuery( '#linked_posts_orders_' + response.data.related_post_ID).slideUp( 'normal', function() { jQuery(this).remove(); if(jQuery( "#sortable li").length > 0) jQuery( "#save-issue-menu").trigger('click'); } );
            }
        },'json');
    });

});
