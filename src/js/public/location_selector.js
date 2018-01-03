(function($){

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

	var locationSelector = function(){
		if( $( '.flexmls-locations-selector' ).length ){
			$( '.flexmls-locations-selector' ).select2({
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
						if( true === !!data.results.length ){
							$.each(data.results, function( idx, item ){
								r.push({
									id: item.display_val + '***' + item.name,
									text: item.display_val + ' (' + item.name.split(/(?=[A-Z])/).join(" ") + ')'
								});
							});
						}
						return {
							results: r
						};
					},
					url: 'https://www.flexmls.com/cgi-bin/mainmenu.cgi'
				},
				minimumInputLength: 3
			});
		}
	};

	$(document).ready(function(){
		locationSelector();
	});

})(jQuery);