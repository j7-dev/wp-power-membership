/*! Admin Page Framework - Radio Field Type 0.0.2 */
( function( $ ){

  var apfMain  = PowerPlugin_AdminPageFrameworkScriptFormMain;
  var apfRadio = PowerPlugin_AdminPageFrameworkFieldTypeRadio;

  $( document ).ready( function(){
    if ( 'undefined' === apfRadio ) {
      return;
    }
    debugLog( '0.0.2', apfRadio );


    $( 'input[type=radio].power-plugin-input-radio' ).on( 'change', function ( e ) {

      var _sInputID = $( this ).data( 'id' );
      // Uncheck the other radio buttons
      $( this ).closest( '.power-plugin-field' ).find( 'input[type=radio][data-id="' + _sInputID + '"]' )
        .prop( 'checked', false )
        .attr( 'checked', false );

      // Make sure the clicked item is checked
      $( this )
        .prop( 'checked', true )
        .attr( 'checked', 'checked' );

    } );

    $().registerPowerPlugin_AdminPageFrameworkCallbacks( {
        /**
         * Called when a field of this field type gets repeated.
         */
        repeated_field: function( oCloned, aModel ) {
          oCloned.find( 'input[type=radio].power-plugin-input-radio' )
            .off( 'change' )
            .on( 'change', function( e ) {

            var _sInputID = $( this ).data( 'id' );

            // Uncheck the other radio buttons
            // prop( 'checked', ... ) does not seem to take effect so use .attr( 'checked' ) also.
            // removeAttr( 'checked' ) causes JQMIGRATE warnings for its deprecation.
            $( this ).closest( '.power-plugin-field' ).find( 'input[type=radio][data-id="' + _sInputID + '"]' )
              .prop( 'checked', false )
              .attr( 'checked', false );

            // Make sure the clicked item is checked
            $( this )
              .prop( 'checked', true )
              .attr( 'checked', 'checked' );
          });                      
        },
      },
      apfRadio.fieldTypeSlugs
    );

  });

  function debugLog( ...msg ) {
    if ( ! parseInt( apfMain.debugMode ) ) {
      return;
    }
    console.log( 'APF Radio Field Type', ...msg );
  }

}( jQuery ) );