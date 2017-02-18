jQuery(document).ready(function($) {
    $( "#account-id-form" ).on("submit", function(event) {

        // remove previous error messages from template page
        $('#account-id-form').removeClass('has-error');
        $('.error-msg').remove();

        // get account ID user input
        var input = $( 'input:text' ).val();

        // get page ID
        var post_id = $( 'article' ).attr('id');

        // make AJAX call
        $.ajax({
            url: myajax.ajaxurl,
            type: 'post',
            data: {
                action: 'my_ajax_function', // links to ajax handler function
                account_id: input,
                security: myajax.nonce, // send nonce for security
                post_id: post_id // send page ID
            },
            dataType: 'json', // expect to get response data in json
            encode: true
        })
            // define AJAX callback function
            .done(function(data) {
                console.log(data); // record any errors

                // has error
                if ( ! data.success) {
                    // unauthorized error
                    if (data.errors.unauthorized) {
                        $('#account-id-form').addClass('has-error');
                        $('#account-id-form').append('<div class="error-msg">' + data.errors.unauthorized + '</div>');
                    }

                    // user is not logged in
                    if (data.errors.login) {
                        $('#account-id-form').addClass('has-error');
                        $('#account-id-form').append('<div class="error-msg">' + data.errors.login + '</div>');
                    }

                    // input was empty
                    if (data.errors.empty) {
                        $('#account-id-form').addClass('has-error');
                        $('#account-id-form').append('<div class="error-msg">' + data.errors.empty + '</div>');
                    }
                    // incorrect account ID
                    if (data.errors.incorrect) {
                        $('#account-id-form').addClass('has-error');
                        $('#account-id-form').append('<div class="error-msg">' + data.errors.incorrect + '</div>');
                    }

                // no error
                } else {
                    // display profile data
                    $('.entry-content').html(data.html);
                }
            })
            // AJAX request failed
            .fail(function(data) {
                // record any errors
                console.log(data);
            });

        event.preventDefault();
    });
});
