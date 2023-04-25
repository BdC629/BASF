<?php

/**
 * @file
 * Drupal site-specific configuration file.
 */

require_once DRUPAL_ROOT . '/sites/developer.basf.com/settings.php';

// Fast 404 pages
$config['system.performance']['fast_404']['exclude_paths'] = '/\/(?:styles)|(?:system\/files)\//';
$config['system.performance']['fast_404']['paths'] = '/\.(?:txt|png|gif|jpe?g|css|js|ico|swf|flv|cgi|bat|pl|dll|exe|asp)$/i';
$config['system.performance']['fast_404']['html'] = '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL "@path" was not found on this server.</p></body></html>';

// Set public file path
$settings['file_public_path'] = 'sites/developer.basf.com/files';

// Set translations folder.
$config['locale.settings']['translation.path'] = $settings['file_public_path'] . '/translations';

// On Acquia Cloud, this include file configures Drupal to use the correct
// database in each site environment (Dev, Stage, or Prod).
if (file_exists('/var/www/site-php')) {
  die('Insert your acquia settings in settings.php');
  require('<insert your acquia settings file>');
}

// Config path.
$config_directories[CONFIG_SYNC_DIRECTORY] = '../config/sync';


/**
 * Load local development override configuration, if available.
 *
 * Use settings.local.php to override variables on secondary (staging,
 * development, etc) installations of this site. Typically used to disable
 * caching, JavaScript/CSS compression, re-routing of outgoing emails, and
 * other things that should not happen on development and testing sites.
 *
 * Keep this code block at the end of this file to take full effect.
 */
$siteEnvironment = getenv('AH_SITE_ENVIRONMENT');
// If we are not on an Acquia Server
if(empty($siteEnvironment)) {
  if (isset($_SERVER['DEVDESKTOP_DRUPAL_SETTINGS_DIR'])) {
    $siteEnvironment = 'devdesktop'; // use devdesktop
  }
  else {
    $siteEnvironment = 'local'; // or local environment
  }
}

$stageSettingsFilePath = DRUPAL_ROOT . '/sites/developer.basf.com/settings.' . $siteEnvironment . '.php';
if (file_exists($stageSettingsFilePath)) {
  include $stageSettingsFilePath;
}

// Force thunder as install profile.
$settings['install_profile'] = 'apigee_devportal_kickstart';

//Database
$dbName=getenv('DEVPORTAL_DB') ?: 'mysql';
$dbUser=getenv('DEVPORTAL_DBUSER') ?: 'root';
$dbPass=getenv('DEVPORTAL_DBPASSWORD') ?: 'root';
$host=getenv('DEVPORTAL_HOST') ?: 'localhost';
$port=getenv('DEVPORTAL_PORT') ?: '3306';

// DB settings
$databases['default']['default'] = [
  'database' => $dbName,
  'username' => $dbUser,
  'password' => $dbPass,
  'host' => $host,
  'port' => $port,
  'driver' => 'mysql',
  'prefix' => '',
  'charset' => 'utf8mb4',
  'collation' => 'utf8mb4_general_ci',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
];

// Set private file path
$settings['file_private_path'] = 'sites/developer.basf.com/files/private';

// Enable cache rebuild via url /core/rebuild.php
if ( getenv('DEVPORTAL_HOST') === 'localhost' ||  $siteEnvironment === 'local'){
  $settings['rebuild_access'] = TRUE;
}

$settings['hash_salt'] = file_get_contents(__DIR__ . '../../../../salt.txt');
$databases['default']['default'] = array (
  'database' => 'drupaldev',
  'username' => 'drupaldev',
  'password' => 'drupaldev',
  'prefix' => '',
  'host' => 'drupal8-mariadb-dev',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
$settings['hash_salt'] = '4U-2Z9OIS3eh1E2DpZhxL6i2z4a7hZBxfa4FGUodrKPHKnGMIiWYVQ2X3_97rZPzOtEK3TFCoA';

// Basic configuration
$config['system.site']['name'] = 'BASF Developer Portal';
