/**
 * @file
 * Mailchimp Campaign javascript.
 */

(function ($) {
  'use strict';

  Drupal.behaviors.mailchimp_campaign = {
    attach: function (context, settings) {
      var google;

      function drawCharts() {
        var dataTable = new google.visualization.DataTable();
        dataTable.addColumn('datetime', Drupal.t('Date'));
        dataTable.addColumn('number', Drupal.t('Emails sent'));
        dataTable.addColumn('number', Drupal.t('Unique opens'));
        dataTable.addColumn('number', Drupal.t('Clicks'));

        // Use Object.keys() to iterate over the stats object.
        Object.keys(settings.mailchimp_campaign.stats).forEach(function (key) {
          const stat = settings.mailchimp_campaign.stats[key];
          dataTable.addRow([
            new Date(stat['timestamp']),
            stat['emails_sent'],
            stat['unique_opens'],
            stat['recipients_click']
          ]);
        });

        var options = {
          pointSize: 5,
          hAxis: {format: 'MM/dd/y hh:mm aaa'}
        };

        var chart = new google.visualization.LineChart(document.getElementById('mailchimp-campaign-chart'));
        chart.draw(dataTable, options);
      }

      google.load('visualization', '1', {packages: ['corechart'], callback: drawCharts});
    }
  };

})(jQuery);
