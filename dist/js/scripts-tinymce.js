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
  					if( true === !!data.results.length ){
  						$.each(data.results, function( idx, item ){
  							r.push({
  								id: item.display_val + '***' + item.name,
  								text: item.display_val + ' (' + item.name.split(/(?=[A-Z])/).join(" ") + ')'
  							});
  						});
  					}
            if( true === !!data.overlays.length ){
              $.each(data.overlays, function( idx, item ){
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
		flexmls.locationSelector('.flexmls-locations-selector');
	});
  $(document).on('widget-added', function(event, widget){
    flexmls.locationSelector('.flexmls-locations-selector');
  });
  $(document).on('widget-updated', function(event, widget){
    flexmls.locationSelector('.flexmls-locations-selector');
  });

})(jQuery);

var flexmls = (typeof flexmls === 'undefined') ? {} : flexmls;

(function($){

  flexmls.ShortcodeGenerator = function(editor) {
    this.editor = editor;

    // override the default values in the children
    this.defaultValues = {};
  };


  // Gets the values from a previously created shortcode of the current type.
  // Returns and object.
  flexmls.ShortcodeGenerator.prototype.userValues = function() {
    var values = {};
    var selection = tinyMCE.activeEditor.selection.getContent({format : 'text'});
    
    if( true === !!selection ){
      var shortcode = wp.shortcode.next( this.shortCodeId, selection, 0 );

      if( true === !!shortcode ){
        try {
          values = shortcode.shortcode.attrs.named;
        } catch( e ){
          console.log( 'Error', e );
        }
      }
    }
    return values;
  };

  flexmls.ShortcodeGenerator.prototype.getInitialValues = function() {
    return $.extend( {}, this.defaultValues, this.userValues() );
  };

  flexmls.ShortcodeGenerator.prototype.onsubmit = function( e ) {
    var shortcode = wp.shortcode.string({
      tag: this.shortCodeId,
      attrs: e.data,
      type: 'single'
    });
    this.editor.insertContent( shortcode );
  };


  flexmls.ShortcodeGenerator.prototype.addPropertyTypeValues = function(){
    var self = this;
    
    $.post( ajaxurl, {action: 'tinymce_get_property_types'}, function( response ){
      if (response.length) {
        var newValues = [];
        
        for (var i = 0; i < response.length; i++) {
          var newValue = {
            text: response[i].text,
            value: response[i].value
          };
          newValues.push(newValue);
        }

        var newListBox = {
          name: 'property_type',
          type: 'listbox',
          values: newValues,
          value: self.getInitialValues().property_type,
        };

        self.propertyTypeInput.append(newListBox);
        self.propertyTypeInput.items()[0].remove();
        self.propertyTypeInput.reflow();
      }
    }, 'json' );
  };

})(jQuery);

(function($){

  flexmls.LocationSearch = function(editor) {

    flexmls.ShortcodeGenerator.call(this, editor);

  };


  flexmls.LocationSearch.prototype = Object.create($.extend({}, flexmls.ShortcodeGenerator.prototype, {
    constructor: flexmls.LocationSearch,


    editorOptions: function(){
      var title = '1-Click Searches';

      return {
        title: '1-Click Location Search',
        body: [
          {
            type: 'textbox',
            name: 'title',
            label: 'Title',
            size: 42,
            value: title
          },
          {
            items: [
              {
                disabled: true,
                name: 'idxlinksplaceholder',
                type: 'listbox',
                values: [{
                  text: 'Loading Saved Searches',
                  value: ''
                }],
              }
            ],
            label: 'Saved Search',
            minWidth: 42,
            name: 'idx_link',
            onPostRender: this.idxLinksOnPostRender,
            type: 'container'
          },
          {
            items: [
              {
                disabled: true,
                name: 'propertytypesplaceholder',
                type: 'listbox',
                values: [{
                  text: 'Loading Property Types',
                  value: ''
                }],
              }
            ],
            label: 'Property Type',
            minWidth: 42,
            name: 'property_type',
            onPostRender: this.addPropertyTypeValues,
            type: 'container'
          },
          {
            onKeyUp: function(ev) {
              var inputInstance = this;
              console.log(this)
              console.log(ev.target.value)
            },
            label: 'Select Location(s)',
            name: 'idx_area',
            size: 42,
            text: 'Search',
            type: 'textbox'
          },
          {
            hidden: false,
            label: ' ',
            name: 'idx_area_results',
            type: 'selectbox'
          },
          {
            items: [


            ],
            label: 'Select Location(s)',
            type: 'container'
          }
        ],
        onsubmit: this.onsubmit.bind(this)
      }
    },

    idxLinksOnPostRender: function(){
      var element = this.getEl(),
          input = element.firstChild,
          $input = $( input ),
          inputInstance = this;

      $.post( ajaxurl, {action: 'tinymce_get_idx_links'}, function( response ){
        if (response.length) {
          // for (var i = 0; i < inputInstance.items().length; i++) {
          //   inputInstance.items()[i].hide();
          // }
          var newValues = [];
          for (var i = 0; i < response.length; i++) {
            var newValue = {
              text: response[i].text,
              value: response[i].value
            };
            newValues.push(newValue);
          }
          var newListBox = {
            name: 'idxlinks',
            type: 'listbox',
            values: newValues
          }
          inputInstance.append(newListBox);
          inputInstance.items()[0].remove();
          inputInstance.reflow();
        }
      }, 'json' );
    },

  }));


})(jQuery);

(function($){

  flexmls.MarketStats = function(editor) {

    flexmls.ShortcodeGenerator.call(this, editor);

    this.shortCodeId = 'flexmls_market_stats';
    this.modalTitle = 'Market Statistics';

    this.defaultValues = {
      title: 'Market Statistics',
      stat_type: 'absorption',
      chart_type: 'line',
      property_type: 'A',
      time_period: 12,
    };

    this.statOptions;
  };

  flexmls.MarketStats.prototype = Object.create($.extend({}, flexmls.ShortcodeGenerator.prototype, {
    constructor: flexmls.MarketStats,

    editorOptions: function(){
      return {
        title: this.modalTitle,
        body: this.body(),
        onsubmit: this.onsubmit.bind(this),
      };

    },

    body: function() {

      var values = this.getInitialValues();
      var self = this;

      this.chartDataInput = tinymce.ui.Factory.create({
        type: 'container',
        name: 'chart_data',
        label: 'What data would you like to display?',
        onPostRender: this.getChartDataValues.bind(this),
        minHeight: 80,
        style: 'overflow-y: scroll; overflow-x: hidden;',
      });

      this.statTypeInput = tinymce.ui.Factory.create({
        type: 'listbox',
        name: 'stat_type',
        label: 'Type of Statistics',
        value: values.stat_type,
        values: [
          {value: 'absorption', text: "Absorption Rate"},
          {value: 'inventory', text: "Inventory"},
          {value: 'price', text: "Price"},
          {value: 'ratio', text: "Sale to List Price Ratios"},
          {value: 'dom', text: "Days On Market"},
          {value: 'volume', text: "Volume"},
        ],
        onselect: function(e) {
          self.updateChartDataValues();
        },
      })

      this.locationInput = tinymce.ui.Factory.create({
        type: 'selectbox',
        name: 'location_field',
        value: values.location_field,
        classes: 'flexmls-locations-selector',
        onPostRender: function() {
          flexmls.locationSelector('.mce-flexmls-locations-selector');

          // add pre-existing values to the dropdown
          if(typeof values.location_field !== 'undefined') {
            var locationParts = values.location_field.split('***');
            var id = values.location_field
            var text = locationParts[0] + ' (' + locationParts[1] + ')';

            var newOption = new Option(text, id, true, true)
            $('.mce-flexmls-locations-selector').append(newOption);
          }

        }
      });

      this.propertyTypeInput = tinymce.ui.Factory.create({
        items: [
        {
            disabled: true,
            type: 'listbox',
            values: [{
              text: 'Loading Property Types',
              value: ''
            }],
          }
        ],
        label: 'Property Type',
        minWidth: 42,
        name: 'property_type',
        onPostRender: this.addPropertyTypeValues.bind(this),
        type: 'container'
      });

      return [{
        type: 'textbox',
        name: 'title',
        label: 'Title',
        size: 42,
        value: values.title
      }, 
      this.statTypeInput, 
      this.chartDataInput, 
      {
        type: 'listbox',
        name: 'chart_type',
        label: 'Chart Type',
        value: values.chart_type,
        values: [
          {value: 'line', text: 'Line Chart'},
          {value: 'bar', text: 'Bar Chart'},
        ]
      }, 
      this.propertyTypeInput, 
      {
        type: 'listbox',
        name: 'time_period',
        label: 'Time Period',
        value: values.time_period,
        values: [
          {value: '1', text: "1 Month"},
          {value: '2', text: "2 Months"},
          {value: '3', text: "3 Months"},
          {value: '4', text: "4 Months"},
          {value: '5', text: "5 Months"},
          {value: '6', text: "6 Months"},
          {value: '7', text: "7 Months"},
          {value: '8', text: "8 Months"},
          {value: '9', text: "9 Months"},
          {value: '10', text: "10 Months"},
          {value: '11', text: "11 Months"},
          {value: '12', text: "12 Months"},
        ]
      },{
        type: 'container',
        label: 'Select Location',
        items: [this.locationInput]
      }];
    },

    getChartDataValues: function(e){
      var self = this;
      
      if(typeof this.statOptions === 'undefined') {
        $.post( ajaxurl, {action: 'tinymce_get_stat_options'}, function( response ){
          self.statOptions = response;
          self.updateChartDataValues();
        }, 'json' );
      } else {
        self.updateChartDataValues();
      }
    },

    updateChartDataValues: function() {
      var chartType = this.statTypeInput.value();
      var options = this.statOptions[chartType];
      var checkboxes = [];
      var selectedValues = [];

      if(typeof this.userValues().chart_data !== 'undefined') {
        selectedValues = this.userValues().chart_data.split(',');
      }

      Object.keys(options).forEach(function(value) {
        var label = options[value];
        var name = 'chart_data_' + value;
        var checked = selectedValues.indexOf(value) >= 0;

        checkboxes.push({ 
          type: 'checkbox', 
          name: name, 
          text: label, 
          checked: checked
        });
      });

      // clear previous checkboxes from the container
      $(this.chartDataInput.getEl()).find('.mce-container-body').html('');

      this.chartDataInput.append( checkboxes ).reflow();
    },

    onsubmit: function( e ) {
      var data = e.data;
      var chartDataValues = [];
      var validChartDataValues = Object.keys(this.statOptions[data.stat_type]);

      // All selected checkboxes for all chart data types will be include in the data
      // as {ChartData: true} or {ChartData: false}. They need to be added to the
      // shortcode as 'chart_data="ChartData,OtherChartData"'. This moves the stat
      // types and filters out those that don't apply to the selected stat_type.
      Object.keys(data).forEach(function(key) {
        if(key.indexOf('chart_data_') === 0){
          var id = key.replace('chart_data_', '');
          var value = data[key];
          
          if(value === true && validChartDataValues.indexOf(id) >= 0){
            chartDataValues.push(id);
          }
          delete data[key];
        }
      });

      if(chartDataValues.length > 0) {
        data.chart_data = chartDataValues.join(',');
      }

      // get the data from the select2 location box
      var locationValue = $(this.locationInput.getEl()).val();
      if (locationValue !== null) {
        data.location_field = locationValue;
      } else {
        delete data.location_field;
      }

      var shortcode = wp.shortcode.string({
        tag: this.shortCodeId,
        attrs: data,
        type: 'single'
      });
      this.editor.insertContent( shortcode );
    }

  }));

})(jQuery);

(function($){

  var flexmls_general_search = function( editor ){
    return {
      title: 'General Search',
      body: [{
          type: 'listbox',
          name: 'style',
          label: 'Style',
          'values': [
              {text: 'Clear', value: 'clear'},
              {text: 'White', value: 'white'},
              {text: 'Colour 1', value: 'colour1'},
              {text: 'Colour 2', value: 'colour2'},
              {text: 'Colour 3', value: 'colour3'},
          ]
      }],
      onsubmit: function( e ) {
        editor.insertContent( '[container style="' + e.data.style + '"]<br /><br />[/container]');
      }
    }
  };

  var flexmls_idxlinks = function( editor ){
    // Widget Defaults
    var title = 'Saved Searches';
    var idx_link = [];

    // If existing shortcode is selected and matches this type
    var selection = tinyMCE.activeEditor.selection.getContent({format : 'text'});
    if( true === !!selection ){
      var shortcode = wp.shortcode.next( 'flexmls_idxlinks', selection, 0 );
      if( true === !!shortcode ){
        try {
          var atts = shortcode.shortcode.attrs.named;
          if( atts.hasOwnProperty( 'title' ) ){
            title = atts.title;
          }
          if( atts.hasOwnProperty( 'idx_link' ) ){
            idx_link = atts.idx_link.split(',');
          }
        } catch( e ){
          console.log( 'Error' );
          console.log( e );
        }
      }
    }

    return {
      title: 'IDX Links',
      body: [
        {
          type: 'textbox',
          name: 'title',
          label: 'Title',
          size: 42,
          value: title
        },
        {
          type: 'container',
          name: 'idx_link',
          label: 'IDX Link(s)',
          onPostRender: function(){
            var element = this.getEl(),
                input = element.firstChild,
                $input = $( input ),
                inputInstance = this;
            $.post( ajaxurl, {action: 'tinymce_get_idx_links'}, function( response ){
              if (response.length) {
                for (var i = 0; i < inputInstance.items().length; i++) {
                  inputInstance.items()[i].hide();
                }
                for (var i = 0; i < response.length; i++) {
                  var lb = {
                    checked: -1 !== idx_link.indexOf(response[i].value),
                    type: 'checkbox',
                    text: response[i].text,
                    name: response[i].value
                  };
                  inputInstance.append(lb);
                }
                element.style.overflowY='scroll';
                inputInstance.reflow();
              }
            }, 'json' );
          },
          items: [
            {
              disabled: true,
              type: 'checkbox',
              text: 'Loading',
              value: 0
            },
            {
              disabled: true,
              type: 'checkbox',
              text: 'Loading',
              value: 0
            },
            {
              disabled: true,
              type: 'checkbox',
              text: 'Loading',
              value: 0
            },
            {
              disabled: true,
              type: 'checkbox',
              text: 'Loading',
              value: 0
            },
            {
              disabled: true,
              type: 'checkbox',
              text: 'Loading',
              value: 0
            },
            {
              disabled: true,
              type: 'checkbox',
              text: 'Loading',
              value: 0
            },
          ]
        }
      ],
      onsubmit: function( e ) {
        var attrs = {};
        var links = [];
        for (key in e.data) {
          switch (true) {
            case 'title' === key:
              attrs.title = e.data[key];
              break;
            default:
              if (true === e.data[key]) {
                links.push(key);
              }
          }
        }
        attrs.idx_link = links.join(',');
        var shortcode = wp.shortcode.string({
          tag: 'flexmls_idxlinks',
          attrs: attrs,
          type: 'single'
        });
        editor.insertContent( shortcode );
      }
    }
  };

  var flexmls_leadgen = function( editor ){
    // Widget Defaults
    var title = 'Contact Me';
    var blurb = '';
    var success = 'Thank you for your request';
    var buttontext = 'Submit';

    // If existing shortcode is selected and matches this type
    var selection = tinyMCE.activeEditor.selection.getContent({format : 'text'});
    if( true === !!selection ){
      var shortcode = wp.shortcode.next( 'flexmls_leadgen', selection, 0 );
      if( true === !!shortcode ){
        try {
          var atts = shortcode.shortcode.attrs.named;
          if( atts.hasOwnProperty( 'title' ) ){
            title = atts.title;
          }
          if( atts.hasOwnProperty( 'blurb' ) ){
            blurb = atts.blurb;
          }
          if( atts.hasOwnProperty( 'success' ) ){
            success = atts.success;
          }
          if( atts.hasOwnProperty( 'buttontext' ) ){
            buttontext = atts.buttontext;
          }
        } catch( e ){
          console.log( 'Error' );
          console.log( e );
        }
      }
    }
    return {
      title: 'Lead Generation',
      body: [
        {
          type: 'textbox',
          name: 'title',
          label: 'Title',
          size: 42,
          value: title
        },
        {
          type: 'textbox',
          name: 'blurb',
          label: 'Description',
          multiline: true,
          tooltip: 'Optional: this text appears below the title',
          value: blurb
        },
        {
          type: 'textbox',
          name: 'success',
          label: 'Success Message',
          multiline: true,
          tooltip: 'Appears after the message is sent successfully',
          value: success
        },
        {
          type: 'textbox',
          name: 'buttontext',
          label: 'Button Text',
          tooltip: 'Customize the text of the submit button',
          value: buttontext
        }
      ],
      onsubmit: function( e ) {
        var attrs = {};
        attrs.title = JSON.stringify(e.data.title).slice(1, -1);
        attrs.blurb = JSON.stringify(e.data.blurb).slice(1, -1);
        attrs.success = JSON.stringify(e.data.success).slice(1, -1);
        attrs.buttontext = JSON.stringify(e.data.buttontext).slice(1, -1);
        var shortcode = wp.shortcode.string({
          tag: 'flexmls_leadgen',
          attrs: attrs,
          type: 'single'
        });
        editor.insertContent( shortcode );
      }
    }
  };

  var flexmls_portal = function( editor ){
    var saved_searches, listing_carts;
    // If existing shortcode is selected and matches this type
    var selection = tinyMCE.activeEditor.selection.getContent({format : 'text'});
    if( true === !!selection ){
      var shortcode = wp.shortcode.next( 'flexmls_portal', selection, 0 );
      if( true === !!shortcode ){
        try {
          var atts = shortcode.shortcode.attrs.named;
          if( atts.hasOwnProperty( 'saved_searches' ) ){
            saved_searches = parseInt(atts.saved_searches);
          }
          if( atts.hasOwnProperty( 'listing_carts' ) ){
            listing_carts = parseInt(atts.listing_carts);
          }
        } catch( e ){
          console.log( 'Error' );
          console.log( e );
        }
      }
    }

    return {
      title: 'Portal Widget',
      body: [
        {
          border: '0 0 0 0',
          margin: '0 0 0 0',
          multiline: true,
          padding: '0 0 0 0',
          type: 'infobox',
          text: 'Do you want to display your visitor\'s Saved Searches on this widget?'
        },
        {
          checked: (1 === saved_searches ? true : false),
          name: 'saved_searches',
          type: 'checkbox',
          text: 'Yes, include Saved Searches'
        },
        {
          border: '0 0 0 0',
          margin: '0 0 0 0',
          multiline: true,
          padding: '0 0 0 0',
          type: 'infobox',
          text: 'Do you want to display your visitor\'s Listing Carts on this widget?'
        },
        {
          checked: (1 === listing_carts ? true : false),
          name: 'listing_carts',
          type: 'checkbox',
          text: 'Yes, include Listing Carts'
        }
      ],
      onsubmit: function( e ) {
        var attrs = {};
        if (true === e.data[ 'saved_searches' ]) {
          attrs.saved_searches = 1
        }
        if (true === e.data[ 'listing_carts' ]) {
          attrs.listing_carts = 1
        }
        var shortcode = wp.shortcode.string({
          tag: 'flexmls_portal',
          attrs: attrs,
          type: 'single'
        });
        editor.insertContent( shortcode );
      }
    }
  };

  var flexmls_slideshow = function( editor ){
    return {
      title: 'IDX Slideshow',
      body: [{
          type: 'listbox',
          name: 'style',
          label: 'Style',
          'values': [
              {text: 'Clear', value: 'clear'},
              {text: 'White', value: 'white'},
              {text: 'Colour 1', value: 'colour1'},
              {text: 'Colour 2', value: 'colour2'},
              {text: 'Colour 3', value: 'colour3'},
          ]
      }],
      onsubmit: function( e ) {
        editor.insertContent( '[container style="' + e.data.style + '"]<br /><br />[/container]');
      }
    }
  };

  tinymce.PluginManager.add( 'flexmlsidx', function( editor, url ){
    var self = this;

    editor.addButton( 'flexmlsidx_shortcodes', {
        type: 'menubutton',
        tooltip: 'Add a Flexmls widget',
        title: 'Add Flexmls Widget',
        image: flexmls.pluginurl + '/dist/assets/tinymce_flexmls_pin.png',
        menu: [
          {text: '1-Click Location Search', onclick: function(){
            var locationSearch = new flexmls.LocationSearch(editor);
            editor.windowManager.open( locationSearch.editorOptions() );
          } },
          {text: 'General Search', onclick: function(){
            editor.windowManager.open( flexmls_general_search( editor ) );
          } },
          // {text: 'IDX Links', onclick: flexmls_idxlinks },
          {text: 'IDX Links', onclick: function(){
            editor.windowManager.open( flexmls_idxlinks( editor ) );
          } },
          {text: 'IDX Slideshow', onclick: function(){
            editor.windowManager.open( flexmls_slideshow( editor ) );
          } },
          {text: 'Lead Generation', onclick: function(){
            editor.windowManager.open( flexmls_leadgen( editor ) );
          } },
          {text: 'Market Statistics', onclick: function(){
            var marketStats = new flexmls.MarketStats(editor);
            editor.windowManager.open( marketStats.editorOptions() );
          } },
          {text: 'Portal Widget', onclick: function(){
            editor.windowManager.open( flexmls_portal( editor ) );
          } }
        ]
      } );
  } );

})(jQuery);
