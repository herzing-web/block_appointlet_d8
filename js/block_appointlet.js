/**
 * Created by mwessel on 2/6/2020.
 */

(function( $ ) {
  Drupal.behaviors.block_appointlet = {
    attach: function( context, settings ) {
      // Code that should run on every page load, including after AJAX events
    }
  };

  $( document ).ready( function() {
    // Code that should run only on page load

    console.log( 'your wish is my command');

    $.getJSON( '/webform/lookup_cookie', function( data) {
      console.log(data);
    });

  } );

  // Add helper functions here

})( jQuery );
