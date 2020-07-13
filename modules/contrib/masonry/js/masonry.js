/**
 * @file
 * Masonry script.
 *
 * Sponsored by: www.freelance-drupal.com
 */

(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.masonry = {
    attach: function (context, settings) {

      // Apply Masonry on the page.
      applyMasonry();

      // Hack for tabs: when the tab is open, it takes to reload Masonry.
      $('a[data-toggle="tab"]', context).on('shown.bs.tab', function () {
        applyMasonry();
      });

      /**
       * Apply Masonry.
       */
      function applyMasonry() {

        // Iterate through all Masonry instances.
        $.each(drupalSettings.masonry, function (container, settings) {

          // Set container.
          var $container = $(container);

          // Set options.
          var $options = {};

          // Sets the item selector.
          if (settings.item_selector) {
            $options.itemSelector = settings.item_selector;
            // Add custom class to all items.
            $(settings.item_selector, $container).addClass('masonry-item');
          }

          // Apply column width units accordingly.
          if (settings.column_width) {
            if (settings.column_width_units == 'px') {
              $options.columnWidth = parseInt(settings.column_width);
            }
            else if (settings.column_width_units == '%') {
              $options.columnWidth = ($container.width() * (settings.column_width / 100)) - settings.gutter_width;
            }
            else {
              $options.columnWidth = settings.column_width;
            }
          }

          // Add stamped selector.
          if (settings.stamp) {
            $options.stamp = settings.stamp;
          }

          // Add the various options.
          $options.gutter = settings.gutter_width;
          $options.isResizeBound = settings.resizable;
          $options.isFitWidth = settings.fit_width;
          if (settings.rtl) {
            $options.isOriginLeft = false;
          }
          if (settings.animated) {
            $options.transitionDuration = settings.animation_duration + 'ms';
          }
          else {
            $options.transitionDuration = 0;
          }
          if (settings.percent_position) {
            $options.percentPosition = true;
          }

          // Include the extra options.
          $.each(settings.extra_options, function (option, value) {
            $options[option] = value;
          });

          /**
           * Apply Masonry to container.
           */

          // Load images first if necessary.
          if (settings.images_first) {
            $container.imagesLoaded(function () {
              if ($container.findOnce('masonry').length === 0) {
                $container.once('masonry').addClass('masonry').masonry($options);
                $(window).resize(function () {
                  $container.findOnce('masonry').masonry('bindResize')
                });
              }
              $container.masonry('reloadItems').masonry('layout');
            });
          }

          // Apply without loading images first otherwise.
          else {
            if ($container.findOnce('masonry').length === 0) {
              $container.once('masonry').addClass('masonry').masonry($options);
              $(window).resize(function () {
                $container.findOnce('masonry').masonry('bindResize')
              });
            }
            $container.masonry('reloadItems').masonry('layout');
          }
        });
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
