(function($){
  var togglePropertySubtypes = function(){
    $( 'input[name^="PropertyType"]' ).on( 'change', function( ev ){
      var subtypes = $( this ).closest( 'li' ).find( '.flexmls-search-widget-propertysubtypes' );
      if( this.checked ){
        $( subtypes ).addClass( 'open' );
      } else {
        $( subtypes ).removeClass( 'open' );
      }
    });
  };

  $(document).ready(function(){
    togglePropertySubtypes();
  });
})(jQuery);