<?php

/**
 * @file
 * Stub file for basf_menu_tree() and suggestion(s).
 */

/**
 * Returns HTML for a wrapper for a menu sub-tree.
 *
 * @param array $variables
 *   An associative array containing:
 *   - tree: An HTML string containing the tree's items.
 *
 * @return string
 *   The constructed HTML.
 *
 * @see template_preprocess_menu_tree()
 * @see theme_menu_tree()
 *
 * @ingroup theme_functions
 */
function basf_menu_tree(array &$variables) {
  return '<ul class="navbar-nav">' . $variables['tree'] . '</ul>';
}

/**
 * BASF theme wrapper function for the primary menu links.
 *
 * @param array $variables
 *   An associative array containing:
 *   - tree: An HTML string containing the tree's items.
 *
 * @return string
 *   The constructed HTML.
 */
function basf_menu_tree__primary(array &$variables) {
  return '<ul class="navbar-nav mr-auto">' . $variables['tree'] . '</ul>';
}

/**
 * BASF theme wrapper function for the secondary menu links.
 *
 * @param array $variables
 *   An associative array containing:
 *   - tree: An HTML string containing the tree's items.
 *
 * @return string
 *   The constructed HTML.
 */
function basf_menu_tree__secondary(array &$variables) {
  return '<ul class="menu nav navbar-nav secondary">' . $variables['tree'] . '</ul>';
}

/**
 * Overrides theme_menu_tree() for book module.
 *
 * @param array $variables
 *   An associative array containing:
 *   - tree: An HTML string containing the tree's items.
 *
 * @return string
 *   The constructed HTML.
 */
function basf_menu_tree__book_toc(array &$variables) {
  $output = '<div class="book-toc btn-group pull-right">';
  $output .= '  <button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown">';
  $output .= t('!icon Outline !caret', array(
    '!icon' => _basf_icon('list'),
    '!caret' => '<span class="caret"></span>',
  ));
  $output .= '</button>';
  $output .= '<ul class="dropdown-menu" role="menu">' . $variables['tree'] . '</ul>';
  $output .= '</div>';
  return $output;
}

/**
 * Overrides theme_menu_tree() for book module.
 *
 * @param array $variables
 *   An associative array containing:
 *   - tree: An HTML string containing the tree's items.
 *
 * @return string
 *   The constructed HTML.
 */
function basf_menu_tree__book_toc__sub_menu(array &$variables) {
  return '<ul class="dropdown-menu" role="menu">' . $variables['tree'] . '</ul>';
}

/**
 * BASF theme wrapper function for the secondary menu links.
 */
function basf_menu_tree__user_menu(&$vars) {
  $html = '' . $vars['tree'] . '';
  return $html;
}

/**
 * BASF theme wrapper function for the footer menu links.
 */
function basf_menu_tree__menu_footer(&$vars) {
  $html = '<ul class="nav justify-content-md-end justify-content-center basf-footer-text">' . $vars['tree'] . '</ul>';
  return $html;
}

/**
 * Overrides theme_menu_link().
 */
function basf_menu_link(array $variables) {
  $element = $variables['element'];
  $sub_menu = '';

  $options = !empty($element['#localized_options']) ? $element['#localized_options'] : array();

  // Check plain title if "html" is not set, otherwise, filter for XSS attacks.
  $title = empty($options['html']) ? check_plain($element['#title']) : filter_xss_admin($element['#title']);

  // Set saleslines brand color
  $domain = $_SERVER['HTTP_HOST'];
  if ( strpos($domain, 'ap') !== false) {
    $saleslineclasslink = 'basf-light-green-link';
  } else {
    $saleslineclasslink = 'basf-light-blue-link';
  }

  // Ensure "html" is now enabled so l() doesn't double encode. This is now
  // safe to do since both check_plain() and filter_xss_admin() encode HTML
  // entities. See: https://www.drupal.org/node/2854978
  $options['html'] = TRUE;
  $options['attributes']['class'][] = 'nav-link ' . $saleslineclasslink;

  $href = $element['#href'];
  $attributes = !empty($element['#attributes']) ? $element['#attributes'] : array();

  if ($element['#below']) {
    // Prevent dropdown functions from being added to management menu so it
    // does not affect the navbar module.
    if (($element['#original_link']['menu_name'] == 'management') && (module_exists('navbar'))) {
      $sub_menu = drupal_render($element['#below']);
    }
    elseif ((!empty($element['#original_link']['depth'])) && ($element['#original_link']['depth'] == 1)) {
      // Add our own wrapper.
      unset($element['#below']['#theme_wrappers']);
      $sub_menu = '<ul class="dropdown-menu">' . drupal_render($element['#below']) . '</ul>';

      // Generate as standard dropdown.
      $title .= ' <span class="caret"></span>';
      $attributes['class'][] = 'dropdown';

      // Set dropdown trigger element to # to prevent inadvertant page loading
      // when a submenu link is clicked.
      $options['attributes']['data-target'] = '#';
      $options['attributes']['class'][] = 'dropdown-toggle';
      $options['attributes']['data-toggle'] = 'dropdown';
      $options['attributes']['aria-expanded'] = 'false';
      $options['attributes']['aria-haspopup'] = 'true';
    }
  }

  return l($title, $href, $options) . $sub_menu . "\n";
}

/**
 * BASF theme wrapper function for the menu blocks 1
 *
 * @param array $variables
 *   An associative array containing:
 *   - tree: An HTML string containing the tree's items.
 *
 * @return string
 *   The constructed HTML.
 */
function basf_menu_tree__menu_block(&$variables) {
  return '<ul class="nav flex-column">' . $variables['tree'] . '</ul>';
}

// Alter menu menu l
function basf_menu_link__menu_block(&$variables) {
  $element = $variables['element'];
  $sub_menu = '';

  $element['#attributes']['class'][] = 'menu-' . $element['#original_link']['mlid'];

  $options = !empty($element['#localized_options']) ? $element['#localized_options'] : array();

  // Check plain title if "html" is not set, otherwise, filter for XSS attacks.
  $title = empty($options['html']) ? check_plain($element['#title']) : filter_xss_admin($element['#title']);

  // Ensure "html" is now enabled so l() doesn't double encode. This is now
  // safe to do since both check_plain() and filter_xss_admin() encode HTML
  // entities. See: https://www.drupal.org/node/2854978
  $options['html'] = TRUE;
  $options['attributes']['class'][] = 'nav-link p-0';

  $href = $element['#href'];

  if ($element['#below'] && $element['#original_link']['in_active_trail']) {
    if ((!empty($element['#original_link']['depth'])) && ($element['#original_link']['depth'] == 1)) {

      // Unset global subtree wrapper
      unset($element['#below']['#theme_wrappers']);

      $sub_menu = '<div class="timeline-main"><ul class="timeline-light pl-0">';
      $sub_menu_items = $element['#below'];
      foreach($sub_menu_items as $sub_menu_item) {
        $sub_options = !empty($sub_menu_item['#localized_options']) ? $sub_menu_item['#localized_options'] : array();

        // Check plain title if "html" is not set, otherwise, filter for XSS attacks.
        $sub_title = empty($sub_options['html']) ? check_plain($sub_menu_item['#title']) : filter_xss_admin($sub_menu_item['#title']);

        // Ensure "html" is now enabled so l() doesn't double encode. This is now
        // safe to do since both check_plain() and filter_xss_admin() encode HTML
        // entities. See: https://www.drupal.org/node/2854978
        $sub_options['html'] = TRUE;
        $sub_options['attributes']['class'][] = 'basf-grey-text';

        $sub_href = $sub_menu_item['#href'];

        // Check if subitem exists
        if( !empty($sub_title)) {
          $sub_menu .= '<li class="timeline-light-item timeline-light-item-light"><div class="timeline-light-item-tail"></div><div class="timeline-light-item-head"></div><div class="timeline-light-item-content">';
          $sub_menu .= l($sub_title, $sub_href, $sub_options);
          $sub_menu .= '</div></li>';
        }
      }
      $sub_menu .= '</ul></div>';
    }
    $options['attributes']['class'][] = 'py-0 basf-blue-text font-weight-bold';
    return '<li class="nav-item">' . l($title, $href, $options) . $sub_menu . '</li>';

  }
  else {
    return '<li class="nav-item">' . l($title, $href, $options) .'</li>' . $sub_menu;
  }
}
