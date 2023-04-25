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

//Database
$dbName=getenv('DEVPORTAL_DB_AP') ?: 'mysql';
$dbUser=getenv('DEVPORTAL_DBUSER_AP') ?: 'root';
$dbPass=getenv('DEVPORTAL_DBPASSWORD_AP') ?: 'root';
$host=getenv('DEVPORTAL_HOST_AP') ?: 'localhost';
$port=getenv('DEVPORTAL_PORT_AP') ?: '3306';

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
$settings['file_private_path'] = 'sites/ap.developer.basf.com/files/private';

// Enable cache rebuild via url /core/rebuild.php
if ( getenv('DEVPORTAL_HOST') === 'localhost' ||  $siteEnvironment === 'local'){
  $settings['rebuild_access'] = TRUE;
}

// DB Hashsalt
$settings['hash_salt'] = getenv('DB_HASHSALT') ?: '';

// Basic configuration
$config['system.site']['name'] = 'AP BASF Developer Portal';

// Trusted domains pattern
$settings['trusted_host_patterns'] = [
  '^ap\.localhost\:8080$',
  '^dev\.ap\.developer\.basf\.com$',
  '^qa\.ap\.developer\.basf\.com$',
  '^ap\.developer\.basf\.com$',
  '^www\.ap\.developer\.basf\.com$',
];


