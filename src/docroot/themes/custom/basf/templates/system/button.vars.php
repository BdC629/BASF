<?php

/**
 * @file
 * Stub file for "button" theme hook [pre]process functions.
 */

/**
 * Pre-processes variables for the "button" theme hook.
 *
 * See theme function for list of available variables.
 *
 * @param array $variables
 *   An associative array of variables, passed by reference.
 *
 * @see bootstrap_button()
 * @see theme_button()
 *
 * @ingroup theme_preprocess
 */

function basf_preprocess_button(array &$variables) {
  // Set saleslines brand color
  $domain = $_SERVER['HTTP_HOST'];
  if ( strpos($domain, 'ap') !== false) {
    $saleslineclassbg = 'basf-light-green';
  } else {
    $saleslineclassbg = 'basf-light-blue';
  }

  $element = &$variables['element'];

  // Drupal buttons should be of type 'submit'.
  // @see https://www.drupal.org/node/2540452
  $element['#attributes']['type'] = 'submit';

  // Set the element's other attributes.
  element_set_attributes($element, array('id', 'name', 'value'));

  // Add the base Bootstrap button class.
  $element['#attributes']['class'][] = 'btn ' . $saleslineclassbg .' ml-0 rounded-0 z-depth-0';

  // Add button size, if necessary.
  if ($size = bootstrap_setting('button_size')) {
    $element['#attributes']['class'][] = $size;
  }

  // Add special attributes for create app submit button
  if($element['#value'] == 'Create App') {
    $element['#attributes']['id'] = 'create-app';
  }

  // Colorize button.
  _bootstrap_colorize_button($element);

  // Iconize button.
  _bootstrap_iconize_button($element);

  // Add in the button type class.
  $element['#attributes']['class'][] = 'form-' . $element['#button_type'];

  // Ensure that all classes are unique, no need for duplicates.
  $element['#attributes']['class'] = array_unique($element['#attributes']['class']);
}
