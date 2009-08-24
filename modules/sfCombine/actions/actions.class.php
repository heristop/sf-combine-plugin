<?php
/**
 * sfCombineActions
 *
 * @package    sfCombinePlugin
 * @author     Alexandre Mogère
 */
class sfCombineActions extends sfActions
{
  public function preExecute()
  {
    sfConfig::set('sf_web_debug', false);
    $this->setTemplate('asset');

    // gzip compression
    if (sfConfig::get('app_sfCombinePlugin_gzip', true) && !self::checkIEFail())
    {
      ob_start("ob_gzhandler");
    }
    
    $max_age = sfConfig::get('app_sfCombinePlugin_client_cache_max_age', false);
    if ($max_age !== false)
    {
      $lifetime = $max_age * 86400; // 24*60*60
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
  
  protected static function checkIEFail()
  {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    if (strpos($userAgent, 'Mozilla/4.0 (compatible; MSIE ') !== 0 || strpos($userAgent, 'Opera') !== false)
    {
      return false;
    }
    $version = floatval(substr($userAgent, 30));
    
    return $version < 6 || ($version == 6 && strpos($userAgent, 'SV1') === false);
  }
}