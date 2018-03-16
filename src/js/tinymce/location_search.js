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
