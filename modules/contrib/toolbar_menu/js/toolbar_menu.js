/**
 * @file
 * Defines the behavior of the toolbar_menu module.
 *
 * Rewrite part of toolbar.js to render all toolbar element in collapsible menu.
 */

((Drupal, drupalSettings, once) => {
  const options = {};
  // Merge run-time settings with the defaults.
  Object.assign(
    options,
    {
      breakpoints: {
        'toolbar.narrow': '',
        'toolbar.standard': '',
        'toolbar.wide': '',
      },
    },
    drupalSettings.toolbar,
    // Merge strings on top of drupalSettings so that they are not mutable.
    {
      strings: {
        horizontal: Drupal.t('Horizontal orientation'),
        vertical: Drupal.t('Vertical orientation'),
      },
    },
  );

  Drupal.behaviors.toolbar_menu = {
    attach(context) {
      // Process the administrative toolbar.
      once('toolbar-menu', '#toolbar-administration', context).forEach(
        (element) => {
          // Establish the toolbar models and views.
          Drupal.toolbar.models.toolbarModel = new Drupal.toolbar.ToolbarModel({
            locked:
              JSON.parse(
                localStorage.getItem('Drupal.toolbar.trayVerticalLocked'),
              ) || false,
            activeTab: document.getElementById(
              JSON.parse(localStorage.getItem('Drupal.toolbar.activeTabID')),
            ),
          });
          const model = Drupal.toolbar.models.toolbarModel;

          // Render collapsible menus.
          Drupal.toolbar.models.menuModel = new Drupal.toolbar.MenuModel();
          const { menuModel } = Drupal.toolbar.models;
          Drupal.toolbar.views.menuVisualView =
            new Drupal.toolbar.MenuVisualView({
              el: element.querySelectorAll('.toolbar-menu-administration'),
              model: menuModel,
              strings: options.strings,
            });

          // Handle the resolution of Drupal.toolbar.setSubtrees.
          // This is handled with a deferred so that the function may be invoked
          // asynchronously.
          Drupal.toolbar.setSubtrees.done((subtrees) => {
            menuModel.set('subtrees', subtrees);
            const { theme } = drupalSettings.ajaxPageState;
            localStorage.setItem(
              `Drupal.toolbar.subtrees.${theme}`,
              JSON.stringify(subtrees),
            );
            // Indicate on the toolbarModel that subtrees are now loaded.
            model.set('areSubtreesLoaded', true);
          });
        },
      );
    },
  };
})(Drupal, drupalSettings, once);
