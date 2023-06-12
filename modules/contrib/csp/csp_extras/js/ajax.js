/**
 * @file
 * Provides add_assets command for Ajax responses.
 */

(function (window, Drupal) {

  /**
   * Command to add css.
   *
   * Backported from Drupal 10.1 to handle attributes arrays.
   *
   * @param {Drupal.Ajax} [ajax]
   *   {@link Drupal.Ajax} object created by {@link Drupal.ajax}.
   * @param {object} response
   *   The response from the Ajax request.
   * @param {object[]|string} response.data
   *   An array of styles to be added.
   * @param {number} [status]
   *   The XMLHttpRequest status.
   */
  Drupal.AjaxCommands.prototype.add_css = function (ajax, response, status) {
    if (typeof response.data === 'string') {
      $('head').prepend(response.data);
      return;
    }

    const allUniqueBundleIds = response.data.map(function (style) {
      const uniqueBundleId = style.href + ajax.instanceIndex;
      loadjs(style.href, uniqueBundleId, {
        before(path, styleEl) {
          // This allows all attributes to be added, like media.
          Object.keys(style).forEach((attributeKey) => {
            styleEl.setAttribute(attributeKey, style[attributeKey]);
          });
        },
      });
      return uniqueBundleId;
    });
    // Returns the promise so that the next AJAX command waits on the
    // completion of this one to execute, ensuring the CSS is loaded before
    // executing.
    return new Promise((resolve, reject) => {
      loadjs.ready(allUniqueBundleIds, {
        success() {
          // All CSS files were loaded. Resolve the promise and let the
          // remaining commands execute.
          resolve();
        },
        error(depsNotFound) {
          const message = Drupal.t(
            `The following files could not be loaded: @dependencies`,
            { '@dependencies': depsNotFound.join(', ') },
          );
          reject(message);
        },
      });
    });
  };

})(window, Drupal);
