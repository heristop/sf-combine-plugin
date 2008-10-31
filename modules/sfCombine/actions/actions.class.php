<?php

class sfCombineActions extends sfActions
{
  public function preExecute()
  {
    sfConfig::set('sf_web_debug', false);
    $this->setLayout(false);
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
    $minifierClass = sfConfig::get('app_sfCombinePlugin_js_minifier_class', 'sfCombineJs');
    $cj = new $minifierClass();
    $script = $cj->process($this->getRequestParameter('key'));
    
    return $this->renderText($script);
  }
  
  public function executeCss()
  {
    $this->getResponse()->setContentType('text/css');
    $minifierClass = sfConfig::get('app_sfCombinePlugin_css_minifier_class', 'sfCombineCss');
    $cs = new $minifierClass();
    $style = $cs->process($this->getRequestParameter('key'));
    
    return $this->renderText($style);
  }
}