<?php

// Task description
pake_desc('cleanup sf_combine table');
pake_task('sfcombine-cleanup', 'project_exists');

/**
 *
 * @param object $task
 * @param array $args
 */
function run_sfcombine_cleanup($task, $args)
{
  if (!class_exists('DbFinder'))
  {
    throw new Exception('sfCombine expects DbFinder to call combined asset file');
  }

  if (!count($args))
  {
    echo pakeColor::colorize('Usage: php symfony sfcombine-cleanup [app]'."\n", 'ERR');
    return;
  }
  $app = $args[0];
  if (!is_dir(sfConfig::get('sf_app_dir').DIRECTORY_SEPARATOR.$app))
  {
    throw new Exception('The app "'.$app.'" does not exist.');
  }

  // define constants
  define('SF_ROOT_DIR',    sfConfig::get('sf_root_dir'));
  define('SF_APP', $app);
  define('SF_ENVIRONMENT', $env);
  define('SF_DEBUG',       true);
  // get configuration
  require_once SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';

  // initialize database manager
  $databaseManager = new sfDatabaseManager();
  $databaseManager->initialize();

  $nbAssets = DbFinder::from('sfCombine')->delete();
  
  $cache = new sfProcessCache();
  $cache->clear();
  
  echo pakeColor::colorize(sprintf('Cleanup complete (%d rows deleted)', $nbAssets)."\n", 'INFO');
}
