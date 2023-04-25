<?php

$theme_path = drupal_get_path('theme', 'basf');

function basf_preprocess_page(&$variables) {
    // Add information about the number of sidebars.
    if (!empty($variables['page']['sidebar_first']) && !empty($variables['page']['sidebar_second'])) {
        $variables['columns'] = 3;
    }
    elseif (!empty($variables['page']['sidebar_first'])) {
        $variables['columns'] = 2;
    }
    elseif (!empty($variables['page']['sidebar_second'])) {
        $variables['columns'] = 2;
    }
    else {
        $variables['columns'] = 1;
    }

    // Primary nav
    $variables['primary_nav'] = FALSE;
    if ($variables['main_menu']) {
        // Build links
        $variables['primary_nav'] = menu_tree(variable_get('menu_main_links_source', 'main-menu'));
        // Provide default theme wrapper function
        $variables['primary_nav']['#theme_wrappers'] = array('menu_tree__primary');
    }

    // Secondary nav
    $variables['secondary_nav'] = FALSE;
    if ($variables['secondary_menu']) {
        // Build links
        $variables['secondary_nav'] = menu_tree(variable_get('menu_secondary_links_source', 'user-menu'));
        // Provide default theme wrapper function
        $variables['secondary_nav']['#theme_wrappers'] = array('menu_tree__secondary');
    }

    //add responsive support for SmartDocs
    if (module_exists('smartdocs') && !empty( $variables['node']) && $variables['node']->type == 'smart_method') {
        $responsive_path = drupal_get_path('theme', 'basf');
        drupal_add_css($responsive_path . '/scss/main.min.css', array('group' => CSS_SYSTEM));
    }

    drupal_add_js(path_to_theme() . '/js/basf.min.js');
  $variables['scripts'] = drupal_get_js();

    if (arg(0) == 'taxonomy' && arg(1) == 'term' && is_numeric(arg(2))) {
        $term = taxonomy_term_load(arg(2));
        $vocab = $term->vocabulary_machine_name;
        $variables['theme_hook_suggestions'][] = 'page__' . $vocab ;
    }
}

/**
 * Adds Bootstrap repsonsive images
 */
function basf_preprocess_image_style(&$vars) {
  $vars['attributes']['class'][] = 'img-fluid';
}

/**
 * We override the normal theme_select function that takes care
 * of theming the select element.
 * Change THEME to your theme name.
 */
function basf_select($variables) {
    $element = $variables['element'];
    element_set_attributes($element, array('id', 'name', 'size'));
    _form_set_class($element, array('mdb-select'));
    return '<select name="' .$element["#name"]. '" class="browser-default custom-select">' . form_select_options($element) . '</select>';
}


/**
 * Make regions rendable in template files
 */
function basf_preprocess_node(&$variables) {

    $view_mode = $variables['view_mode'];
    $content_type = $variables['type'];
    $variables['theme_hook_suggestions'][] = 'node__' . $view_mode;
    $variables['theme_hook_suggestions'][] = 'node__' . $view_mode . '_' . $content_type;

    $view_mode_preprocess = 'basf_preprocess_node_' . $view_mode . '_' . $content_type;
    if (function_exists($view_mode_preprocess)) {
        $view_mode_preprocess($variables, $hook);
    }

    $view_mode_preprocess = 'basf_preprocess_node_' . $view_mode;
    if (function_exists($view_mode_preprocess)) {
        $view_mode_preprocess($variables, $hook);
    }

    // Get a list of all the regions for this theme
    foreach (system_region_list($GLOBALS['theme']) as $region_key => $region_name) {

        // Get the content for each region and add it to the $region variable
        if ($blocks = block_get_blocks_by_region($region_key)) {
            $variables['region'][$region_key] = $blocks;
        }
        else {
            $variables['region'][$region_key] = array();
        }
    }

    // Send content-type of a node to tealium
    if (!path_is_admin(current_path())
      && isset($variables['page'])
      && isset($variables['node']->type)
    ) {
      $node_type = $variables['node']->type;
      $page_name = drupal_get_title();
      $is_page = $variables['page'];
      if ($is_page && !empty($node_type)) {
        tealium_add_data('page_name', $page_name);
        tealium_add_data('page_type', $node_type);
      }
    }
}

/**
 * Implements hook_preprocess().
 */
function basf_preprocess_bootstrap_modal_forms(&$vars) {
    switch ($vars['identifier']) {
        case 'login':
            if (module_exists('openid')) {
                $vars['modal_form']['openid_identifier']['#prefix'] = '<div class="apigee-responsive-openidhide" style="display:none">';
                $vars['modal_form']['openid_identifier']['#suffix'] = '</div>';
            }
            if (isset($vars['modal_form']['sso_buttons'])) {
                $vars['sso'] = $vars['modal_form']['sso_buttons'];
                unset($vars['modal_form']['sso_buttons']);
            }
            else {
                $vars['sso'] = '';
            }
            break;

        case 'register':
            $vars['sso'] = '';
            if (isset($vars['modal_form']['sign_in_with_google_apps'])) {
                $vars['sso'] = $vars['modal_form']['sign_in_with_google_apps']['#markup'];
                unset($vars['modal_form']['sign_in_with_google_apps']);
            }
            break;
    }
}

function basf_form_alter(&$form, &$form_state, $form_id) {
    // user login
    if($form_id === 'views-exposed-form-api-categories-api-categories-page'){
        $form['sort_by']['#title_display'] = 'invisible';
        $form['pass']['#title_display'] = 'invisible';
    }

    // contact us form
    if($form_id === 'webform_client_form_64') {
      $form['#attributes']['class'][] = '';
    }

    // user delete
    if($form_id === 'user_cancel_confirm_form') {
      $form['#attributes']['class'][] = 'container mt-md-5 pt-5 py-5';
    }
}

/**
 * Formats a status label for the developer apps list.
 */
function _basf_status_label_callback($status, $pull_right = FALSE) {
    if ($status === 'revoked') {
        return '<span class="basf-red-text' . ($pull_right ? ' pull-right' : '') . '">' . t('Revoked') . '</span>';
    } elseif ($status === 'pending') {
        return '<span class="basf-orange-text' . ($pull_right ? ' pull-right' : '') . '">' . t('Pending') . '</span>';
    } else {
      return '<span class="basf-light-green-text' . ($pull_right ? ' pull-right' : '') . '">' . t('Approved') . '</span>';
    }
}

/**
 * Implements hook_preprocess_hook().
 */
function basf_preprocess_devconnect_developer_apps_list(&$vars) {
    $user = (isset($vars['user']) ? $vars['user'] : $GLOBALS['user']);


    // Set saleslines brand color
    $domain = $_SERVER['HTTP_HOST'];
    if ( strpos($domain, 'ap') !== false) {
      $saleslineclassbg = 'basf-light-green';
    } else {
      $saleslineclassbg = 'basf-light-blue';
    }

    if (user_access('create developer apps')) {
        $link_text = '<span class="glyphicon glyphicon-plus"></span> ' . t('Add a new !app_label', array('!app_label' => _devconnect_developer_apps_get_app_label(FALSE)));
        $vars['add_app'] = l($link_text, 'user/' . $user->uid . '/apps/add', array('html' => TRUE, 'attributes' => array('class' => array('btn ' . $saleslineclassbg . ' rounded-0 z-depth-0 text-white'))));
    }

    foreach ($vars['applications'] as $key => $detail) {
        $vars['applications'][$key]['id'] = uniqid();
    }
}

/**
 * Implements hook_preprocess_taxonomy_term()
 *
 * @param $vars
 */
function basf_preprocess_taxonomy_term(&$vars) {
    $vars['theme_hook_suggestions'][] = 'taxonomy_term__' . $vars['view_mode'];
    $vars['theme_hook_suggestions'][] = 'taxonomy_term__' . $vars['vocabulary_machine_name'] . '__' . $vars['view_mode'];
}

function set_mycustom_api_headers()
{
    drupal_add_http_header('Content-Security-Policy', 'default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\'; img-src \'self\'');
    drupal_add_http_header('Access-Control-Allow-Origin', "*");
    drupal_add_http_header('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE');
}

/**
 * Returns HTML for a query pager. Override of theme_pager().
 *
 * Overridden to change some class names. Bootstrap uses some of the same
 * classes for different types of elements, and offers other classes are used
 * for pagers. In particular, the 'pager' class (used in core) is for rounded
 * buttons, while Bootstrap uses the 'pagination' class for multi-button pagers
 * like these.
 *
 * Also format the current page number as a link so that it will be themed like
 * the other links around it.
 *
 * @param $vars
 *   An associative array containing:
 *   - tags: An array of labels for the controls in the pager.
 *   - element: An optional integer to distinguish between multiple pagers on
 *     one page.
 *   - parameters: An associative array of query string parameters to append to
 *     the pager links.
 *   - quantity: The number of pages in the list.
 *
 * @ingroup themeable
 */
function basf_pager($variables) {
    $tags = $variables['tags'];
    $element = $variables['element'];
    $parameters = $variables['parameters'];
    $attributes = $variables['attributes'];
    $quantity = $variables['quantity'];
    global $pager_page_array, $pager_total;

    // Calculate various markers within this pager piece:
    // Middle is used to "center" pages around the current page.
    $pager_middle = ceil($quantity / 2);
    // current is the page we are currently paged to
    $pager_current = $pager_page_array[$element] + 1;
    // first is the first page listed by this pager piece (re quantity)
    $pager_first = $pager_current - $pager_middle + 1;
    // last is the last page listed by this pager piece (re quantity)
    $pager_last = $pager_current + $quantity - $pager_middle;
    // max is the maximum page number
    $pager_max = $pager_total[$element];
    // End of marker calculations.

    // Prepare for generation loop.
    $i = $pager_first;
    if ($pager_last > $pager_max) {
        // Adjust "center" if at end of query.
        $i = $i + ($pager_max - $pager_last);
        $pager_last = $pager_max;
    }
    if ($i <= 0) {
        // Adjust "center" if at start of query.
        $pager_last = $pager_last + (1 - $i);
        $i = 1;
    }
    // End of generation loop preparation.

    $li_first = theme('pager_first', array('text' => (isset($tags[0]) ? $tags[0] : t('« first')), 'element' => $element, 'parameters' => $parameters));
    $li_previous = theme('pager_previous', array('text' => (isset($tags[1]) ? $tags[1] : t('‹ previous')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
    $li_next = theme('pager_next', array('text' => (isset($tags[3]) ? $tags[3] : t('next ›')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
    $li_last = theme('pager_last', array('text' => (isset($tags[4]) ? $tags[4] : t('last »')), 'element' => $element, 'parameters' => $parameters));
    $items = array();

    if ($pager_total[$element] > 1) {
        if ($li_first) {
            $items[] = array(
                'class' => array('pager-first'),
                'data' => $li_first,
            );
        }
        if ($li_previous) {
            $items[] = array(
                'class' => array('prev'),
                'data' => $li_previous,
            );
        }

        // When there is more than one page, create the pager list.
        if ($i != $pager_max) {
            // Now generate the actual pager piece.
            for (; $i <= $pager_last && $i <= $pager_max; $i++) {
                if ($i < $pager_current) {
                    $items[] = array(
                        'class' => array('page-item p-2'),
                        'data' => theme('pager_previous', array(
                          'text' => $i,
                          'element' => $element,
                          'interval' => ($pager_current - $i),
                          'parameters' => $parameters
                          )
                        ),
                    );
                }
                if ($i == $pager_current) {
                    $items[] = array(
                        'class' => array('page-item active p-2'), // Add the active class
                        'data' => l($i, '#', array('fragment' => '', 'external' => TRUE, 'attributes' => array('class' => array('page-link')))),
                    );
                }
                if ($i > $pager_current) {
                    $items[] = array(
                        'class' => array('page-item p-2'),
                        'data' => theme('pager_next', array(
                          'text' => $i,
                          'element' => $element,
                          'interval' => ($i - $pager_current),
                          'parameters' => $parameters,
                          )
                        ),
                    );
                }
            }
        }
        // End generation.
        if ($li_next) {
            $items[] = array(
                'class' => array('next'),
                'data' => $li_next,
            );
        }
        if ($li_last) {
            $items[] = array(
                'class' => array('pager-last'),
                'data' => $li_last,
            );
        }

        if (empty($items)) {
            return '';
        }

        return '<div class="container"><nav aria-label="page-navigation">' . theme('item_list', array(
                'items' => $items,
                // pager class is used for rounded, bubbly boxes in Bootstrap
                'attributes' => array('class' => array('pagination justify-content-center')),
            )) . '</nav></div>';
    }
}

// Add responsive image class
function basf_preprocess_image(array &$variables) {
    // Add image shape, if necessary.
    if ($shape = bootstrap_setting('image_shape')) {
        _bootstrap_add_class($shape, $variables);
    }

    // Add responsiveness, if necessary.
    if (bootstrap_setting('image_responsive')) {
        _bootstrap_add_class('img-fluid', $variables);
    }
}

// Add meaningful template suggestions for blocks
function basf_preprocess_block(&$variables) {
  $variables['theme_hook_suggestions'][] = 'block__' . $variables['block']->region;
  $variables['theme_hook_suggestions'][] = 'block__' . $variables['block']->module;
  $variables['theme_hook_suggestions'][] = 'block__' . $variables['block']->delta;

  // Add block description as template suggestion
  $block = block_custom_block_get($variables['block']->delta);

  // Transform block description to a valid machine name
  if (!empty($block['info'])) {
    setlocale(LC_ALL, 'en_US');

    // required for iconv()
    $variables['theme_hook_suggestions'][] = 'block__' . str_replace(' ', '_', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $block['info'])));
  }
}

// Remove apigee boostrap css
function basf_css_alter(&$css) {
  unset($css[drupal_get_path('theme', 'apigee_responsive') . '/css/bootstrap.min.css']);
}

// Theme checkboxes
function basf_checkbox($variables) {
  $element = $variables['element'];
  $element['#attributes']['type'] = 'checkbox';
  element_set_attributes($element, array('id', 'name', '#return_value' => 'value'));

  // Unchecked checkbox has #value of integer 0.
  if (!empty($element['#checked'])) {
    $element['#attributes']['checked'] = 'checked';
  }
  _form_set_class($element, array('form-checkbox', 'form-check-input'));

  return '<input' . drupal_attributes($element['#attributes']) . ' />';
}

// Theme user account page
function basf_theme() {
  return array(
    'user_profile_form' => array(
      // Forms always take the form argument.
      'arguments' => array('form' => NULL),
      'render element' => 'form',
      'template' => 'templates/user/user-profile-edit',
    ),
  );
}

/**
 * Get app status.
 */
function basf_app_status($app) {
  $pending = $revoked = FALSE;
  switch ($app['entity']->status) {
    case 'pending':
      $pending = TRUE;
      break;

    case 'revoked':
      $revoked = TRUE;
      break;
  }
  foreach ($app['credential']['apiProducts'] as $product) {
    switch ($product['status']) {
      case 'pending':
        $pending = TRUE;
        break;

      case 'revoked':
        $revoked = TRUE;
        break;
    }
  }
  if ($revoked) {
    return 'Revoked';
  }
  if ($pending) {
    return 'Pending';
  }
  return 'Approved';
}

// Theme hash link
function _basf_developer_app_hash_link(array $variables) {
  $active = '';
  if ($variables['path'] === '#details')
    $active = ' active';
  return '<a data-toggle="tab" href="' . $variables['path'] . '" class="nav-link'. $active .'">' . check_plain($variables['text']) . '</a>';
}

/**
 * @param array $variables
 */
function basf_developer_app_tabs(array $variables) {
  $output = '<ul class="nav nav-tabs md-tabs nav-justified basf-light-blue z-depth-0 mx-0 rounded-0">';
  foreach ($variables['tab_links'] as $tab_link) {
    $output .= '<li class="nav-item" data-link-type="' . (substr($tab_link['path'], 0, 1) === '#' ? 'local' : 'external') . '" >';
    $tab_link += array('text' => '', 'path' => '#', 'options' => array('attributes' => array('class' => array('nav-link'))) );
    if (substr($tab_link['path'], 0, 1) === '#') {
      $output .= _basf_developer_app_hash_link($tab_link);
    } else {
      $output .= l($tab_link['text'], $tab_link['path'], $tab_link['options']);
    }
    $output .=  '</li>';
  }
  $output .= '</ul>';
  return $output;
}

function basf_developer_app_panes(array $variables) {
  $output = '<div class="tab-content app-details">';
  foreach ($variables['panes'] as $pane) {
    $active = '';
    if ($pane['#id'] == 'details') {
      $active = ' active';
    }
    $output .= '<div class="tab-pane'.$active.'" id="'.$pane['#id'].'">';
    $output .= drupal_render($pane) . '</div>';
  }
  $output .= '</div>';
  return $output;
}


/**
 * Override or insert variables into the html template.
 */
function basf_preprocess_html(&$vars) {
  // Add the "adminimal" class to the body for better css selection.
  $vars['classes_array'][] = 'adminimal-menu';

  // Add frontend and backend classes to the body.
  if (path_is_admin(current_path())) {
    $vars['classes_array'][] = 'adminimal-backend';
  }
  else {
    $vars['classes_array'][] = 'adminimal-frontend';
  }

  // Add the shortcut render mode class.
  $vars['classes_array'][] = 'menu-render-' . variable_get('adminimal_admin_menu_render', 'collapsed');

  // DIL-3876: Call CASP basre-uri directive
  basf_csp_attribute_alter("base-uri", "'self'");

}

/**
 * DIL-3876: Define a CASP base-uri directive
 */
function basf_csp_attribute_alter($attribute, $value){
  $headers = drupal_get_http_header();
  $targetHeaders = array('content-security-policy', 'X-Content-Security-Policy', 'X-Webkit-CSP');
  foreach( $targetHeaders as $headerName){
    $editedHeader = FALSE;
    if(isset($headers[$headerName])) {
      //
      $editedHeader = $headers[$headerName];
      $attrpos = strpos($headers[$headerName], $attribute);
      if($attrpos !== false){
        // the attribute is already set.
        $endpos = strpos($headers[$headerName], ';', $attrpos); //end ot the attribute declaration.
        if($endpos > 0 && $endpos > $attrpos) {
          // the  attribute is in the middle of the string.
          $editedHeader = substr_replace($headers[$headerName], '', $attrpos, $endpos - $attrpos);
        } else {
          // the attribute is at the end of the string.
          $editedHeader = substr_replace($headers[$headerName], '', $attrpos);
        }
      }
      // the attribute doesn't exist or has already been removed from the string.
      $editedHeader .= '; '.$attribute.' '.$value;
    }
    drupal_add_http_header($headerName, $editedHeader, FALSE);
  }
}
