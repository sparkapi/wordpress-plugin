import { LocationSearch } from './location_search';
import { GeneralSearch }  from './general_search';
import { IdxLinks }       from './idx_links';
import { LeadGeneration } from './lead_generation';
import { MarketStats }    from './market_stats';
import { Slideshow }      from './slideshow';

(function($){

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
            var idxLinks = new IdxLinks(editor);
            idxLinks.open();
          } },
          {text: 'IDX Slideshow', onclick: function(){
            var slideshow = new Slideshow(editor);
            slideshow.open();
          } },
          {text: 'Lead Generation', onclick: function(){
            var leadGeneration = new LeadGeneration(editor);
            leadGeneration.open();
          } },
          {text: 'Market Statistics', onclick: function(){
            var marketStats = new MarketStats(editor);
            marketStats.open();
          } },
          {text: 'Portal Widget', onclick: function(){
            editor.windowManager.open( flexmls_portal( editor ) );
          } }
        ]
      } );
  } );

})(jQuery);
