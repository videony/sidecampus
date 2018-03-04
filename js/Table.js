$(document).ready(function(){
    
    $('.sortIt').before('<button class="button right reset"><img src="media/icons/zoom_out.png" /> Reinitialiser les filtres</button>');
    
   // Tablesorter
    $(".sortIt").tablesorter({
        theme: 'blue',

        // hidden filter input/selects will resize the columns, so try to minimize the change
        widthFixed : true,

        // initialize zebra striping and filter widgets
        widgets: ["zebra", "filter"],

        ignoreCase: false,

        widgetOptions : {
            // filters are shown
          filter_columnFilters : true,
            // class added to filter fields
          filter_cellFilter : 'filter_field',
            // class added to filtered rown
          filter_filteredRow : 'filtered',
            // filters hidden when empty table
          filter_hideEmpty : true,
            // Not case sensitive
          filter_ignoreCase : true,
            // search while the user is typing
          filter_liveSearch : true,
            // filters reset button
          filter_reset : 'button.reset',
            // delay before starting searching while user is typing
          filter_searchDelay : 300,
            // do not search in hidden rows
          filter_searchFiltered: true,
            // Filter using parsed content for ALL columns
            // be careful on using this on date columns as the date is parsed and stored as time in seconds
          filter_useParsedData : false,
            // data attribute in the header cell that contains the default filter value
          filter_defaultAttrib : 'data-value'

        }

  });
  
  var alphabetic = "";
  /*$.each(['A', 'B', 'C'], function(letter) {
      console.log(letter);
      alphabetic += '<button class="button alpha_elem">'+letter.toString()+'</button>'
    });*/
    for (var i = 65; i <= 90; i++) 
    {
      alphabetic += '<button class="button alpha_elem">'+String.fromCharCode(i)+'</button>';
    }
  $('.alphabetic').before(alphabetic);
    
});

