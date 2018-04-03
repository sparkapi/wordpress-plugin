import { LocationSearch } from './location_search';
import { GeneralSearch } from './general_search';
import { MarketStats } from './market_stats';
import { Slideshow } from './slideshow';

(function($){

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
                for (let i = 0; i < inputInstance.items().length; i++) {
                  inputInstance.items()[i].hide();
                }
                for (let i = 0; i < response.length; i++) {
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
    };
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
    };
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
        if (true === e.data.saved_searches) {
          attrs.saved_searches = 1;
        }
        if (true === e.data.listing_carts) {
          attrs.listing_carts = 1;
        }
        var shortcode = wp.shortcode.string({
          tag: 'flexmls_portal',
          attrs: attrs,
          type: 'single'
        });
        editor.insertContent( shortcode );
      }
    };
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
    };
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
            var locationSearch = new LocationSearch(editor);
            locationSearch.open();
          } },
          {text: 'General Search', onclick: function(){
            var generalSearch = new GeneralSearch(editor);
            generalSearch.open();
          } },
          {text: 'IDX Links', onclick: function(){
            editor.windowManager.open( flexmls_idxlinks( editor ) );
          } },
          {text: 'IDX Slideshow', onclick: function(){
            var slideshow = new Slideshow(editor);
            slideshow.open();
          } },
          {text: 'Lead Generation', onclick: function(){
            editor.windowManager.open( flexmls_leadgen( editor ) );
          } },
          {text: 'Market Statistics', onclick: function(){
            var marketStats = new MarketStats(editor);
            editor.windowManager.open( marketStats.editorOptions() );
          } },
          {text: 'Portal Widget', onclick: function(){
            editor.windowManager.open( flexmls_portal( editor ) );
          } }
        ]
      } );
  } );

})(jQuery);
