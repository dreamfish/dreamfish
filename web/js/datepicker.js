// unobtrusive datepicker for symfony - Massimiliano Arione 2009 - LGPL

jQuery.fn.addDatePicker = function(name, minYear, maxYear) {
  // min/max check
  var minY = minYear > maxYear ? maxYear : minYear;
  var maxY = minYear < maxYear ? maxYear : minYear;

  // initialise the "Select date" link
  $('#datepick_' + name)
    .datePicker(
      // associate the link with a date picker
      {
        createButton:false,
        startDate:'01/01/' + minY,
        endDate:'31/12/' + maxY
      }
    ).bind(
      // when the link is clicked display the date picker
      'click',
      function()
      {
        updateSelects($(this).dpGetSelected()[0]);
        $(this).dpDisplay();
        return false;
      }
    ).bind(
      // when a date is selected update the SELECTs
      'dateSelected',
      function(e, selectedDate, $td, state)
      {
        updateSelects(selectedDate);
      }
    ).bind(
      'dpClosed',
      function(e, selected)
      {
        updateSelects(selected[0]);
      }
    );

  var updateSelects = function (selectedDate)
  {
    var sDate = new Date(selectedDate);
    $('#' + name + 'day option[value=' + sDate.getDate() + ']').attr('selected', 'selected');
    $('#' + name + 'month option[value=' + (sDate.getMonth()+1) + ']').attr('selected', 'selected');
    $('#' + name + 'year option[value=' + (sDate.getFullYear()) + ']').attr('selected', 'selected');
  }
  // listen for when the selects are changed and update the picker
  $('#' + name + 'day, #' + name + 'month, #' + name + 'year')
    .bind(
      'change',
      function()
      {
        var d = new Date(
              $('#' + name + 'year').val(),
              $('#' + name + 'month').val()-1,
              $('#' + name + 'day').val()
            );
        $('#datepick_' + name).dpSetSelected(d.asString());
      }
    );

  // default the position of the selects to today
  if (!$('#' + name + 'year option:selected').length || $('#' + name + 'year option:selected').val() == '')
  {
    var today = new Date();
    updateSelects(today.getTime());
  }

  // and update the datePicker to reflect it...
  $('#' + name + 'day').trigger('change');
};

jQuery(document).ready(function() {
  $('select[id$=year]').each(function(i) {
    if (this.className == 'nocal')
    {
      return;
    }
    $('<a/>').attr('id', 'datepick_' + this.id.slice(0, -4)).attr('href', '#').append($('<img/>').attr('src', '/images/calendar.png')).insertAfter(this).addDatePicker(this.id.slice(0, -4), $(this).children(':eq(1)').val(), $(this).children(':last').val());
  });
});
