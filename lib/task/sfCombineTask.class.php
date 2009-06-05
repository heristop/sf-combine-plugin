<?php

/**
 * Cleanup sf_combine table
 *
 * @package    symfony
 * @subpackage task
 * @author     Alexandre Mogère
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
    
    if (!class_exists('DbFinder'))
    {
      throw new Exception('sfCombine expects DbFinder to call combined asset file');
    }

    // initialize database manager
    $databaseManager = new sfDatabaseManager($this->configuration);
    $flag = true;
  
    if (function_exists('apc_store') && ini_get('apc.enabled')) 
    {
      $cache = new sfAPCCache();
      if (!ini_get('apc.enable_cli'))
      {
        $this->logSection('combine', 'Check apc.enabled_cli in your ini file', null, 'ERROR');
        $flag = false;
      }
    }
    else
    {
      $cache = new sfFileCache(array('cache_dir'=>sfConfig::get('sf_cache_dir').'/combiners'));
    }
    
    if ($flag)
    {
      $results = DbFinder::from('sfCombine')->find();
      foreach ($results as $result)
      {
        //$this->logSection('combine', 'Cleaning ' . $result->getAssetsKey());
        $cache->remove($result->getAssetsKey());
      }
      
      $this->logSection('combine', 'Cleanup cache complete', null, 'INFO');
      $nbAssets = DbFinder::from('sfCombine')->delete();
      $this->logSection('combine', sprintf('Cleanup database complete (%d rows deleted)', $nbAssets), null, 'INFO');
    }
  }
}