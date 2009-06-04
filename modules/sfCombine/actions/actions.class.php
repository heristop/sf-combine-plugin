<?php

class sfCombineActions extends sfActions
{
  public function preExecute()
  {
    sfConfig::set('sf_web_debug', false);
    $this->setTemplate('asset');
    $max_age = sfConfig::get('app_sfCombinePlugin_client_cache_max_age', false);
    if ($max_age !== false)
    {
      $lifetime = $max_age * 86400000; // 24*60*60*1000
      $this->getResponse()->addCacheControlHttpHeader('max-age', $lifetime);
      $this->getResponse()->setHttpHeader('Pragma', null, false);
      $this->getResponse()->setHttpHeader('Expires', null, false);
    }
  }
  
  public function executeJs()
  {
    $this->getResponse()->setContentType('application/x-javascript');
    $config = sfConfig::get('app_sfCombinePlugin_js', array());
    $minifierClass = isset($config['minifier_class']) ? $config['minifier_class'] : 'sfCombineJs';
    $cj = new $minifierClass();
    $this->assets = $cj->process($this->getRequestParameter('key'));
  }
  
  public function executeCss()
  {
    $this->getResponse()->setContentType('text/css');
    $config = sfConfig::get('app_sfCombinePlugin_css', array());
    $minifierClass = isset($config['minifier_class']) ? $config['minifier_class'] : 'sfCombineCss';
    $cs = new $minifierClass();
    $this->assets = $cs->process($this->getRequestParameter('key'));
  }
}