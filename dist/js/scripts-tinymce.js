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

  var flexmls_idxlinkss = function( editor ){
    // Widget Defaults
    var title = 'Saved Searches';
    var idx_link = '';

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
            idx_link = atts.idx_link;
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
          name: 'idxlinks2',
          label: 'IDX Link(s)',
          onpostrender: function( el ){
            var container = this;
            var ed = editor;
            var str = {'type' : 'checkbox', 'name' : 'idxlinks', 'label' : 'IDX Link(s)', 'text' : 'Link Name', 'value': 1};
            container.append( str );
            container.reflow();
            console.log( 'Getting IDX Links' );
            console.log( ed.get_idx_links() );
            $.post( ajaxurl, {action: 'tinymce_get_idx_links'}, function( response ){

              response.forEach(function( item ) {
                var str = {'type' : 'checkbox', 'name' : 'idxlinks', 'label' : 'IDX Link(s)', 'text' : 'Link Name', 'value': 1};

                container.append( item );
              });
              container.reflow();
            }, 'json' );
          },
          style: 'max-height: 50vh; overflow-y: scroll',
          items: [
]
        }
      ],
      onsubmit: function( e ) {
        editor.insertContent( '[container style="' + e.data.style + '"]<br /><br />[/container]');
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

  var flexmls_location_search = function( editor ){
    return {
      title: '1-Click Location Search',
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

  var flexmls_market_stats = function( editor ){
    return {
      title: 'Market Statistics',
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

  var flexmls_portal = function( editor ){
    return {
      title: 'Portal Widget',
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

    function flexmls_idxlinks(){
      var title = 'Saved Searches';
      var idx_link = '';

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
              idx_link = atts.idx_link;
            }
          } catch( e ){
            console.log( 'Error' );
            console.log( e );
          }
        }
      }

      editor.windowManager.open( {
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
            name: 'idxlinks',
            label: 'IDX Link(s)',
            ondata: function(){
              var pas = this.parentsAndSelf();
              pas.reflow();
            },
            onpostrender: function( el ){
              var container = this;
              $.post( ajaxurl, {action: 'tinymce_get_idx_links'}, function( response ){
                var items = container.create( response );
                container.append( items ).reflow().repaint();
                container.fire( 'data' );
              }, 'json' );
            },
            //style: 'max-height: 50vh; overflow-y: scroll',
            items: []
          }
        ],
        onsubmit: function( e ) {
          editor.insertContent( '[container style="' + e.data.style + '"]<br /><br />[/container]');
        }
      } );
    }

    editor.addButton( 'flexmlsidx_shortcodes', {
        type: 'menubutton',
        tooltip: 'Add a Flexmls widget',
        title: 'Add Flexmls Widget',
        image: flexmls.pluginurl + '/dist/assets/tinymce_flexmls_pin.png',
        menu: [
          {text: '1-Click Location Search', onclick: function(){
            editor.windowManager.open( flexmls_location_search( editor ) );
          } },
          {text: 'General Search', onclick: function(){
            editor.windowManager.open( flexmls_general_search( editor ) );
          } },
          {text: 'IDX Links', onclick: flexmls_idxlinks },
          {text: 'IDX Slideshow', onclick: function(){
            editor.windowManager.open( flexmls_slideshow( editor ) );
          } },
          {text: 'Lead Generation', onclick: function(){
            editor.windowManager.open( flexmls_leadgen( editor ) );
          } },
          {text: 'Market Statistics', onclick: function(){
            editor.windowManager.open( flexmls_market_stats( editor ) );
          } },
          {text: 'Portal Widget', onclick: function(){
            editor.windowManager.open( flexmls_portal( editor ) );
          } }
        ]
      } );
  } );

})(jQuery);