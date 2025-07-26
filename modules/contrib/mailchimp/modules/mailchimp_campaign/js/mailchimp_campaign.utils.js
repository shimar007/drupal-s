/**
 * @file
 * Mailchimp Campaign javascript extras.
 */

(function ($) {
  'use strict';

  /**
   * Utility methods for Mailchimp campaign management.
   * Some text field manipulation code is adapted from
   * the Tokens module.
   */
  Drupal.behaviors.mailchimpCampaignUtils = {
    attach: function (context, settings) {
      // Keep track of which text field was last selected/focused.
      $('textarea', context).on('focus', function () {
        drupalSettings.mailchimpCampaignFocusedField = this;
      });

      /**
       * Add entity token click handler.
       */
      $('.add-entity-token-link', context).off('click').on('click', function (e) {
        e.preventDefault();
        const elementId = $(this).attr('id');
        const section = elementId.replace('-add-entity-token-link', '');

        // Start with import tag field hidden.
        $(`#${section}-entity-import-tag-field`).hide();

        // Get the last selected text field.
        const targetElement = drupalSettings.mailchimpCampaignFocusedField;

        // Get the selected entity ID.
        let entityId = '';
        const entityValue = $(`#${section}-entity-import-entity-id`).val();
        if (entityValue && entityValue.length > 0) {
          const entityParts = entityValue.split(' ');
          const entityIdString = entityParts[entityParts.length - 1];

          entityId = entityIdString.replace('[', '').replace(']', '').replace('"', '');
        }

        if (entityId.length === 0) {
          alert(Drupal.t('Select an entity to import before adding the token.'));
          return;
        }

        // Generate token based on user input.
        const entityType = $(`.${section}-entity-import-entity-type`).val();
        const viewMode = $(`#${section}-entity-import-entity-view-mode`).val();

        const token = `[mailchimp_campaign`
          + `|entity_type=${entityType}`
          + `|entity_id=${entityId}`
          + `|view_mode=${viewMode}`
          + `]`;

        // Insert token into last selected text field.
        if (targetElement) {
          Drupal.behaviors.mailchimpCampaignUtils.addTokenToElement(targetElement, token);
        } else {
          // Missing a selected text field. Insert token into token field,
          // where it can be manually copied by the user.
          $(`#${section}-entity-import-tag-field`).html(token);
          $(`#${section}-entity-import-tag-field`).show();
        }

        // Unset last focused field.
        drupalSettings.mailchimpCampaignFocusedField = null;
      });

      /**
       * Add merge var click handler.
       */
      $('.add-merge-var', context).off('click').on('click', function (e) {
        e.preventDefault();
        // Get the last selected text field.
        const targetElement = drupalSettings.mailchimpCampaignFocusedField;

        // Get the merge var.
        const elementId = $(this).attr('id');
        const mergeVar = elementId.replace('merge-var-', '');
        const token = `*|${mergeVar}|*`;

        // Insert token into last selected text field.
        if (targetElement) {
          Drupal.behaviors.mailchimpCampaignUtils.addTokenToElement(targetElement, token);
        }

        // Unset last focused field.
        drupalSettings.mailchimpCampaignFocusedField = null;
      });
    },

    /**
     * Inserts a token at the last selected point in a text field element.
     *
     * @param object targetElement
     *   The text field element to insert the token into.
     * @param string token
     *   The token to insert.
     */
    addTokenToElement: function (targetElement, token) {
      // IE support.
      if (document.selection) {
        targetElement.focus();
        const sel = document.selection.createRange();
        sel.text = token;
      }
      // MOZILLA/NETSCAPE support.
      else if (targetElement.selectionStart || targetElement.selectionStart === '0') {
        const startPos = targetElement.selectionStart;
        const endPos = targetElement.selectionEnd;
        targetElement.value = targetElement.value.substring(0, startPos)
          + token
          + targetElement.value.substring(endPos, targetElement.value.length);
      } else {
        targetElement.value += token;
      }
    }
  };

})(jQuery);
