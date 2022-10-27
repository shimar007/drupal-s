/**
 * @file
 * Webform Autosave behaviors.
 */
(function ($, Drupal, drupalSettings) {
  'use strict';

  // Set our primary store.
  const store = Object.assign(
    {}, {
      csrfToken: null,
      activeClass: 'active',
      focusedElement: null,
      webform: null,
      submit: null
    },
    drupalSettings.webformautosave
  );

  /**
   * The handler that triggers after ajax is complete.
   */
  const ajaxCompleteHandler = function () {
    // Get outta here if we didn't trigger the ajax.
    if (!$(store.submit).hasClass(store.activeClass)) {
      return true;
    }
    // Remove the active class.
    $(store.submit).removeClass(store.activeClass);
    // Ensure our focus doesn't change.
    $(store.focusedElement).focus();
  };

  /**
   * The handler bound to inputs on the form.
   */
  const inputHandler = function () {
    let webformId = store.webform.data('webform-id');
    let formStore = store.forms[webformId];
    store.submit = $(store.webform).find('[data-autosave-trigger="submit"]');
    // Get out of here if the submit is already happening.
    if ($(store.submit).hasClass(store.activeClass)) {
      return true;
    }
    // Fire off the draft submission.
    if (formStore) {
      // Prevent propagation by adding the active class.
      $(store.submit).addClass(store.activeClass);
      setTimeout(function () {
        // Submit our draft after the timeout.
        $(store.submit).click();
        // Ensure our focus doesn't change.
        $(store.focusedElement).focus();
      }, formStore.autosaveTime);
    }
  };

  /**
   * Bind event handlers to input fields.
   */
  const bindAutosaveHandlers = function (form, context) {
    store.webform = $('form.webform-submission-form');
    store.submit = $(form).find('[data-autosave-trigger="submit"]');

    // Add input and focus event listeners to each input.
    $(once('webformAutosaveBehavior', 'input:not([data-autosave-trigger="submit"]), select:not([data-autosave-trigger="submit"]), textarea:not([data-autosave-trigger="submit"])', context))
      .on('input', inputHandler)
      .on('focus', function () {
        store.focusedElement = $(this);
      });

    // Remove the active class and perform other actions when ajax is complete.
    $(once('webformAutosaveBehaviorAjaxComplete', 'body')).ajaxComplete(ajaxCompleteHandler);
  };

  /**
   * Setup our default behaviors for the webformautosave module.
   */
  Drupal.behaviors.webformautosave = {
    attach: function (context, settings) {
      $(document, context).find('form.webform-submission-form');
      // This runs every time we attach (on backend ajax callback).
      store.forms = settings.webformautosave.forms
      let webformForm = $('form.webform-submission-form')
      // Let's bind an input event to our inputs once.
      if ($(webformForm).length) {
        $(webformForm).each(function (form) {
          bindAutosaveHandlers(form, context);
        });
      }
      // Ensure the wrapper for our draft submit is hidden.
      $(once('webformAutosaveHideWrapper', webformForm))
        .each(function () {
          // Ensure the wrapper is hidden.
          $(webformForm)
            .find('.webformautosave-trigger--wrapper')
            .hide();
        });
    },
  };
})(jQuery, Drupal, drupalSettings);
