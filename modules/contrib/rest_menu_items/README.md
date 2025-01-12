# REST menu items

This module provides a REST endpoint to retrieve menu items based
on the menu name.

For example _/api/menu_items/main_ provides you with the full menu tree of the
_main_ menu.

By adding query parameter like _?max_depth=1_ or _?min_depth=2_ you control how
to output the menu in the web service.


## Requirements

This module depends on the Drupal core module _RESTful Web Services_, which
will be installed automatically.
Optionally, you can install the [REST UI](http://www.drupal.org/project/restui)
module for enabling REST resources.


## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).


## Configuration

Once the module is installed you must enable the REST resource:

- Through the UI by installing the contrib module
  [REST UI](http://www.drupal.org/project/restui). After this module is
  enabled, navigate to _/admin/config/services/rest_
  (_Configuration > Web Services > REST resources_) to enable the REST menu
  items resource.
- Through configuration changes or programmatically. Read
  [RESTful Web Services API overview](https://www.drupal.org/docs/8/api/restful-web-services-apirestful-web-services-api-overview)
  to see how this can be done.

After the resource has been enabled, you must set access permissions via
_/admin/people/permissions_ (_People > Permissions_)

Navigate to _admin/config/services/rest_menu_items_
(_Configuration > Web Services > REST menu items_ through the administration
panel) and configure the available values you want to output in the JSON (or
XML) response. Also check which menus should be available as REST resource
(for example you may not want to expose the administration menu).

## Customizing

### Change the output

Two hooks are being provided to customize the output:

- hook_rest_menu_items_resource_manipulators_alter
- hook_rest_menu_items_output_alter

See rest_menu_items.api.php for more information.

### Change the endpoint URL

If you ever want to change the endpoint URL you can do this with
_hook_rest_resource_alter_:

```php
/**
 * Implements hook_rest_resource_alter().
 */
function MYMODULE_rest_resource_alter(&$definitions) {
  if (!empty($definitions['rest_menu_item'])) {
    $definitions['rest_menu_item']['uri_paths']['canonical'] = '/api/v2/my-fancy-menu-items/{menu_name}';
  }
}
```


## Troubleshooting

- If you get a _406 - Not Acceptable_ error you need to add the
  "?_format=json|hal_json|xml" attribute to the URL. See [information about this 406 response](https://www.drupal.org/node/2790017)
- Submit bug reports and feature suggestions, or track changes in the
  [issue queue](https://www.drupal.org/project/issues/rest_menu_items).

## More information

- [REST menu items project page](https://www.drupal.org/project/rest_menu_items)


## Maintainers

- Fabian de Rijk - [fabianderijk](https://www.drupal.org/u/fabianderijk)


## Sponsors

This project is sponsored by [Finalist](https://www.drupal.org/finalist)
