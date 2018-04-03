var $ = window.jQuery;

export function locationSelector(elementSelector){

  var queryObject = {
    c: 'Y',
    command_line_mode: 'true',
    cmd: 'srv+api/search/getLocations.json',
    i: 'City,StateOrProvince,StreetAddress,PostalCode,CountyOrParish,SubdivisionName,MLSAreaMajor,MLSAreaMinor,MapOverlay,ListingId,SchoolDistrict',
    l: 18,
    ma: 'x\'' + flexmls.ma_tech_id + '\'',
    p: 'A',
    std: 'Y',
    tech_id: 'x\'' + flexmls.tech_id + '\''
  };

  $( elementSelector ).each(function(){

    if( ! $( this ).data( 'select2' ) ){
      $( this ).select2({
        width: '100%',
        ajax: {
          cache: false,
          crossDomain: true,
          dataType: 'jsonp',
          delay: 250,
          data: function( params ){
            queryObject.q = params.term;
            return queryObject;
          },
          error: function( x, err, p ){
            console.log( x, err, p );
          },
          processResults: function( data, params ){
            var r = [];
            var results = data.results.concat(data.overlays);

            $.each(results, function( idx, item ){
              r.push({
                id: item.value + '***' + item.name + '***'+ item.display_val,
                text: item.display_val + ' (' + item.name.split(/(?=[A-Z])/).join(" ") + ')'
              });
            });

            return { results: r };
          },
          url: 'https://www.flexmls.com/cgi-bin/mainmenu.cgi'
        },
        minimumInputLength: 3
      });
    }
  });
}


export function dependentSelect(){
  $( 'select.widget-toggle-dependent' ).on( 'change', function(){
    var v = $( this ).val();
    var target = $( this ).data( 'child' );
    var triggeron = $( this ).data( 'triggeron' );
    if( -1 === $.inArray( v, triggeron ) ){
      $( target ).hide();
    } else {
      $( target ).show();
    }
  } );
}


export function populateMarketStatOptions(){
  $('.marketStatFields').each(function() {
    var $container = $(this);

    $container.find('.flexmls-widget-market-stat-selector').change(function( ev ){
      
      var availableOptions = $( this ).data( 'options' );
      var selected = $( this ).val();
      var select = $container.find( '.flexmls-widget-market-stat-options' );
      var options = '';

      if( availableOptions.hasOwnProperty( selected ) ){
        $.each( availableOptions[selected], function( key, val ){
          options += '<option value="' + key + '">' + val + '</option>';
        } );
      }
      $( select ).html( options );
    } );
  });

}


export function doColorPicker(){
  $( '.iris-color-picker' ).iris({
    change: function(event, ui) {
      $( event.target ).val(ui.color.toString()).trigger('change');
    },
    hide: false,
    palettes: ['#4b6ed0', '#666370', '#84B03D', '#ff9933', '#59CFEB', '#ffffff']
  });
}


export function doThemeOptions(scope){
  var $scope = (scope === undefined) ? $('body') : $(scope);
  
  $scope.find('.flexmls-search-widget-theme-select').each(function(){
    var $optionsSection = $(this).siblings( '.flexmls-search-widget-theme-options' );
    
    $(this).find('select').change(function() {
      $(this).val() ? $($optionsSection).fadeIn() : $($optionsSection).fadeOut();
    });
  });
}

