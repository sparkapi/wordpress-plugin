var flexmls = (typeof flexmls === 'undefined') ? {} : flexmls;

(function($){

  flexmls.locationSelector = function(elementSelector){

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

    if( $( elementSelector ).length ){
      $( elementSelector ).each(function(){
        if( true === !!$( this ).data( 'select2' ) ){
          $( this ).select2( 'destroy' );
        }
      });
      $( elementSelector ).select2({
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
  };


  $(document).ready(function(){
    flexmls.locationSelector('.flexmls-locations-selector');
  });
  $(document).on('widget-added', function(event, widget){
    flexmls.locationSelector('.flexmls-locations-selector');
  });
  $(document).on('widget-updated', function(event, widget){
    flexmls.locationSelector('.flexmls-locations-selector');
  });

})(jQuery);
