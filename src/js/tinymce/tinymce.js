import { LocationSearch } from './location_search';
import { GeneralSearch }  from './general_search';
import { IdxLinks }       from './idx_links';
import { Slideshow }      from './slideshow';
import { LeadGeneration } from './lead_generation';
import { MarketStats }    from './market_stats';
import { Portal }         from './portal';


(function($){

  tinymce.PluginManager.add( 'flexmlsidx', function( editor, url ){

    var getClickHandler = function(ClassName) {
      return function(){
        var instance = new ClassName(editor);
        instance.open();
      };
    };

    var menuItems = [
      {text: '1-Click Location Search', onclick: getClickHandler(LocationSearch) },
      {text: 'General Search',          onclick: getClickHandler(GeneralSearch) },
      {text: 'IDX Links',               onclick: getClickHandler(IdxLinks) },
      {text: 'IDX Slideshow',           onclick: getClickHandler(Slideshow) },
      {text: 'Lead Generation',         onclick: getClickHandler(LeadGeneration) },
      {text: 'Market Statistics',       onclick: getClickHandler(MarketStats) },
      {text: 'Portal Widget',           onclick: getClickHandler(Portal) },
    ];

    editor.addButton( 'flexmlsidx_shortcodes', {
      type: 'menubutton',
      tooltip: 'Add a Flexmls widget',
      title: 'Add Flexmls Widget',
      image: flexmls.pluginurl + '/dist/assets/tinymce_flexmls_pin.png',
      menu: menuItems
    });

  });

})(jQuery);
