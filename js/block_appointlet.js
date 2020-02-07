/**
 * Created by mwessel on 2/6/2020.
 */

(function( $, Drupal, drupalSettings ) {
  Drupal.behaviors.block_appointlet = {
    attach: function( context, settings ) {
      // Code that should run on every page load, including after AJAX events
    }
  };

  $( document ).ready( function() {
    // Code that should run only on page load

    // get field data and load appointlet javascript
    $.getJSON( '/webform/lookup_cookie' )
      .done(function( data ) {

        // bail if no results
        if( $.isEmptyObject (data ) ) { return; }

        // calculate bookable
        var  bookable = drupalSettings.block_appointlet.campus_bookable[data.campus];

        // set attributes
        $('.appointlet-button').each( function() {

          $(this).attr( {
            'data-appointlet-bookable'          : bookable,
            'data-appointlet-email'             : data.email,
            'data-appointlet-field-homephone'   : data.phone,
            'data-appointlet-field-firstname'   : data.first_name,
            'data-appointlet-field-lastname'    : data.last_name,
            'data-appointlet-field-postalcode'  : data.zip,
            'data-appointlet-field-program'     : data.program,
            'data-appointlet-field-campuscode'  : data.campus,
          });

        });

      })
      .fail(function( jqxhr, textStatus, error ) {
        // handle any errors in request
        // var err = textStatus + ", " + error;
        // console.log( err );
      })
      .always( function() {
        // run when done or failed

        // load appointlet script when button has been updated
        $.getScript( 'https://www.appointletcdn.com/loader/loader.min.js' );

      } );


  });

  // Add helper functions here

})( jQuery, Drupal, drupalSettings );


