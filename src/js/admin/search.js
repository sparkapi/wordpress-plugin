(function($){

  var doColorPicker = function(){
    $( '.iris-color-picker' ).iris({
      change: function(event, ui) {
        console.log(ui)
        console.log(event)
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
  $(document).ajaxSuccess(function( e, xhr, settings ){
    doColorPicker();
    doThemeOptions();
  });
})(jQuery);