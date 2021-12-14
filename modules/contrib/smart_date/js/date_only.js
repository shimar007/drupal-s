(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.smartDate = {
    attach: function(context, settings) {
      // Update the end values when the start is changed.
      $('.smartdate--widget .time-start input').on('change', function(){
        startChanged(this);
      });

      $('.smartdate--widget .time-end').once('smartDate').on('change', function(){
        // Update the duration when end changed.
        endChanged(this);
      }).each(function () {
        // Set initial duration.
        endChanged(this);
      });

      function startChanged(element) {
        var start = $(element);
        if (!start.val()) {
          return;
        }
        var wrapper = $(element).closest('fieldset');
        var duration = start.prop('data-duration');
        var start_date = start.val();
        var end = new Date(Date.parse(start_date));
        // ISO 8601 string get encoded as UTC so add the timezone offset.
        var is_iso_8061 = start_date.match(/\d{4}-\d{2}-\d{2}/);
        if (is_iso_8061 && end.getTimezoneOffset() != 0) {
          end.setMinutes(end.getMinutes() + end.getTimezoneOffset());
        }
        // Update end date.
        if (duration) {
          end.setDate(end.getDate() + duration);
        }
        var new_end = end.getFullYear() + '-' + pad(end.getMonth() + 1, 2) + '-' + pad(end.getDate(), 2);
        wrapper.find('.time-end.form-date').val(new_end);
      }

      function endChanged(element) {
        var wrapper = $(element).closest('fieldset');
        var start = wrapper.find('.time-start.form-date');
        var end = $(element);
        var start_date = new Date(Date.parse(start.val()));
        var end_date = new Date(Date.parse(end.val()));
        // Update duration if a number can be determined.
        var duration = (end_date - start_date) / (1000 * 60 * 60 * 24);
        if (duration === 0 || duration > 0) {
          start.prop('data-duration', duration);
        }
      }

      function pad(str, max) {
        str = str.toString();
        return str.length < max ? pad("0" + str, max) : str;
      }
    }
  };
})(jQuery, Drupal);
