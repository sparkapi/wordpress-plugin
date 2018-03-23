import { ShortcodeGenerator } from './shortcode_generator';

var $ = window.jQuery;

class GeneralSearch extends ShortcodeGenerator {

  constructor(editor){
    super(editor);

    this.shortCodeId = 'flexmls_general_search';
    this.modalTitle = 'General Search';

    this.defaultValues = {
    };
  }



  editorOptions(){
    var self = this;
    var p = new Promise(function(resolve, reject) {  

      self.gatherData().then(() => { 
        resolve({
          title: self.modalTitle,
          body: self.body(),
          onsubmit: self.onsubmit.bind(self),
        });
      });

    });

    return p;
  }

  gatherData() {
    var self = this;
    var p = new Promise(function(resolve, reject) {  
      
      self.getPropertytypes(function(data) {
        self._propertyTypes = data;
        resolve();
      });

    });
    return p;
  }

  body() {
    var values = this.getInitialValues();

    return [
      {
        type: 'textbox',
        name: 'title',
        label: 'Title',
        size: 42,
        value: values.title
      },
      this.propertyTypeInput()
    ]
  }


  propertyTypeInput() {
    
    if(this._propertyTypeInput === undefined){

      this._propertyTypeInput = tinymce.ui.Factory.create({
        type: 'container',
        label: 'Property Types to Search',
        minWidth: 42,
        name: 'property_types_to_search',
        // onPostRender: this.addPropertyTypeValues.bind(this),
        items: this.propertyTypeCheckboxes()
      });
    }
    
    return this._propertyTypeInput;

  }

  propertyTypeCheckboxes() {
    var self = this;
    var checkboxes = [];
    var selectedValues = [];

    // if(typeof this.userValues().chart_data !== 'undefined') {
    //   selectedValues = this.userValues().chart_data.split(',');
    // }

    this._propertyTypes.forEach(function(pt) {
      var name = 'property_type_' + pt.value;
      var checked = selectedValues.indexOf(pt.value) >= 0;

      checkboxes.push({ 
        type: 'checkbox', 
        name: name, 
        text: pt.text, 
        checked: checked
      });
    });

    return checkboxes;
  }

}

export { GeneralSearch };
