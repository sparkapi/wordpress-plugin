(function($){

  var doColorPicker = function(){
    $( '.iris-color-picker' ).iris({
      change: function(event, ui) {
        $( event.target ).val(ui.color.toString()).trigger('change');
      },
      hide: false,
      palettes: ['#4b6ed0', '#666370', '#84B03D', '#ff9933', '#59CFEB', '#ffffff']
    });
  };

  var doThemeOptions = function(){
    if( $( '.flexmls-search-widget-theme-select' ).length ){
      var select = $( '.flexmls-search-widget-theme-select' );
      $( select ).each(function(){
        var widget = $( this ).closest( '.widget' );
        var optionsSection = $( widget ).find( '.flexmls-search-widget-theme-options' );
        if ( $( this ).val().length > 0) {
          $( optionsSection ).fadeIn();
        } else {
          $( optionsSection ).fadeOut();
        }

        $( this ).on( 'change', function( ev ){
          console.log(this.value);
          if (this.value.length > 0) {
            $( optionsSection ).fadeIn();
          } else {
            $( optionsSection ).fadeOut();
          }
        });
      });
    }
  };

  $(document).ready(function(){
    doColorPicker();
    doThemeOptions();
  });
  $(document).on('widget-added', function(event, widget){
    doColorPicker();
    doThemeOptions();
  });
  $(document).on('widget-updated', function(event, widget){
    doColorPicker();
    doThemeOptions();
  });
})(jQuery);