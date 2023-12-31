<?php



/**
 * Implements hook_form_FORM_ID_alter().
 */
function basf_form_system_theme_settings_alter(&$form, &$form_state) {
  $form['theme_settings']['toggle_search'] = array(
    '#type' => 'checkbox',
    '#title' => t('Search box'),
    '#default_value' => theme_get_setting('toggle_search'),
  );
  $form['theme_settings']['toggle_breadcrumbs'] = array(
    '#type' => 'checkbox',
    '#title' => t('Breadcrumbs'),
    '#default_value' => theme_get_setting('toggle_breadcrumbs'),
  );
}

