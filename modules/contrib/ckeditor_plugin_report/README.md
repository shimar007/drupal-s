# CKEditor Plugin Report

This module provides a report of CKEditor plugins
(/admin/reports/ckeditor-plugins), including the plugin ID, the provider, and
the class.

It may be useful in instances where a site administrator needs to identify
CKEditor plugins, such as upgrading from CKEditor 4 to CKEditor 5.

Supported plugin types include the following:

- CKEditorPlugin (CKEditor 4)
- CKEditor5Plugin
- CKEditor4To5Upgrade

For a full description of the module, visit the
[project page](https://www.drupal.org/project/ckeditor_plugin_report).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/ckeditor_plugin_report).

## Requirements

This module has no hard dependencies. If the CKEditor 4 (ckeditor) module is
installed, reporting for its plugins will be enabled. If the
CKEditor 5 (ckeditor5) module is installed, reporting for its plugins will be
enabled.

## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).

## Configuration

Access to the report page is controlled by the *View CKEditor plugin report*
(view ckeditor plugin report) permission.

## Maintainers

- Chris Burge - [chris burge](https://www.drupal.org/u/chris-burge).
