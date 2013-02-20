<?php

/**
 * Cleanup sf_combine table
 *
 * @package    sfCombinePlugin
 * @subpackage task
 * @author     Alexandre Mogère
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */
class sfCombineCleanUpTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('orm', null, sfCommandOption::PARAMETER_REQUIRED, 'The orm', 'doctrine')
    ));

    $this->namespace = 'combine';
    $this->name = 'cleanup';
    $this->briefDescription = 'Cleanup sf_combine table';

    $this->detailedDescription = <<<EOF
The [combine:cleanup|INFO] task cleanup sf_combine table:

  [./symfony asset:create-root|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    // initialize database manager
    $databaseManager = new sfDatabaseManager($this->configuration);
    $flag = true;

    $ormAdapter = new OrmAdapter(OrmAdapterFactory::create($options['orm']));
    $ormAdapter->setClass('sfCombine');

    if (function_exists('apc_store') && ini_get('apc.enabled'))
    {
      $cache = new sfAPCCache();
      if (!ini_get('apc.enable_cli'))
      {
        $this->logSection('combine', 'Check apc.enable_cli in your ini file', null, 'ERROR');
        $flag = false;
      }
    }
    else
    {
      $cache = new sfFileCache(array(
        'cache_dir' => sfCombineUtility::getCacheDir()
      ));
    }

    if ($flag)
    {
      if (! class_exists('sfCombine'))
      {
        $this->logSection('combine', 'Call the task `doctrine:build-model`', null, 'ERROR');
        return false;
      }

      $results = $ormAdapter->find();
      foreach ($results as $result)
      {
        $cache->remove($result->getAssetKey());
      }

      $this->logSection('combine', 'Cleanup cache complete', null, 'INFO');
      $deleted = $ormAdapter->deleteAll();
      $this->logSection(
        'combine',
        sprintf(
          'Cleanup database complete (%d rows deleted)',
          $deleted
        ),
        null,
        'INFO'
      );
    }
  }
}