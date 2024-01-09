/*! Admin Page Framework - Import Field Type 0.0.1 */
/** global: PowerPlugin_AdminPageFrameworkScriptFormMain */
var apfMain  = PowerPlugin_AdminPageFrameworkScriptFormMain;
/** global: PowerPlugin_AdminPageFrameworkImportFieldType */
var apfImport = PowerPlugin_AdminPageFrameworkImportFieldType;
(function ( $ ) {
  
  debugLog( '0.0.1', apfImport );

  $( document ).ready( function () {
    $( '.power-plugin-field-import input[type=submit]' ).on( 'click', function ( event ) {
      var _iFiles = $( this ).closest( '.power-plugin-field-import' ).find( 'input[type=file]' ).get( 0 ).files.length;
      if ( 0 === _iFiles ) {
        alert( apfImport.label.noFile );
        return false;
      }
      return true;
    } );
  } ); // document ready  
  
  function debugLog( ...msg ) {
    if ( ! parseInt( apfMain.debugMode ) ) {
      return;
    }
    console.log( 'APF Import Field Type', ...msg );
  }

}( jQuery ));