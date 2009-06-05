<?php

function include_combined_javascripts()
{
  echo get_combined_javascripts();
}

/**
 * Returns <script> tags for all javascripts configured in view.yml or added to the response object.
 *
 * You can use this helper to decide the location of javascripts in pages.
 * By default, if you don't call this helper, symfony will automatically include javascripts before </head>.
 * Calling this helper disables this behavior.
 *
 * @return string <script> tags
 */
function get_combined_javascripts()
{
  if (!sfConfig::get('app_sfCombinePlugin_enabled', false)) return get_javascripts();
  
  sfConfig::set('symfony.asset.javascripts_included', true);

  $html = '';
  $jsFiles = array();
  $regularJsFiles = array();
  $response = sfContext::getInstance()->getResponse();
  $config = sfConfig::get('app_sfCombinePlugin_js', array());
  $doNotCombine = isset($config['combine_skip']) ? $config['combine_skip'] : array();

  foreach ($response->getJavascripts() as $files => $options)
  {
    if (!is_array($files))
    {
      $files = array($files);
    }
    // check for js files that should not be combined
    foreach ($files as $key => $value)
    {
      if (in_array(str_replace('.js', '', $value), str_replace('.js', '', $doNotCombine)))
      {
        array_push($regularJsFiles, $value);
        unset($files[$key]);
      }
    }
    $jsFiles = array_merge($jsFiles, $files);
  }
  
  if ($jsFiles)
  {
    $html .= str_replace('.js', '', javascript_include_tag(url_for('sfCombine/js?key=' . _get_key($jsFiles))));
  }
  foreach ($regularJsFiles as $file)
  {
    $file = javascript_path($file);
    $html .= javascript_include_tag($file);
  }
  
  return $html;
}

function include_combined_stylesheets()
{
  echo get_combined_stylesheets();
}

/**
 * Returns <link> tags with the url toward all stylesheets configured in view.yml or added to the response object.
 *
 * You can use this helper to decide the location of stylesheets in pages.
 * By default, if you don't call this helper, symfony will automatically include stylesheets before </head>.
 * Calling this helper disables this behavior.
 *
 * @return string <link> tags
 */
function get_combined_stylesheets()
{
  if (!sfConfig::get('app_sfCombinePlugin_enabled', false)) return get_stylesheets();
  
  sfConfig::set('symfony.asset.stylesheets_included', true);
  
  $html = '';
  $cssFiles = array();
  $response = sfContext::getInstance()->getResponse();
  foreach ($response->getStylesheets() as $files => $options)
  {
    if (!is_array($files))
    {
      $files = array($files);
    }
    $cssFiles = array_merge($cssFiles, $files);
  }
  
  if($cssFiles)
  {
    $html .= str_replace('.css', '', stylesheet_tag(url_for('sfCombine/css?key=' . _get_key($cssFiles))));
  }
  
  return $html;
}

/**
 * Returns a key which combined all assets file
 *
 * @return string md5
 */
function _get_key($files)
{
  $content = base64_encode(serialize($files));
  $key = md5($content . sfConfig::get('app_sfCombine_asset_version', ''));

  if (!class_exists('DbFinder'))
  {
    throw new Exception('sfCombine expects DbFinder to call combined asset file');
  }

  if (function_exists('apc_store') && ini_get('apc.enabled')) 
  {
    $cache = new sfAPCCache();
    // FIXME: APCCache "has" method dosn't work 
    $checkFunction = apc_fetch($key);
  }
  else
  {
    $cache = new sfFileCache(array('cache_dir'=>sfConfig::get('sf_cache_dir').'/combiners'));
    $checkFunction = $cache->has($key);
  }

  // Checks if key exists
  if(false === $checkFunction)
  {
    $keyExists = DbFinder::from('sfCombine')->
      where('AssetsKey', $key)->
      count();  
    if (!$keyExists)
    {
      $combine = new sfCombine();
      $combine->setAssetsKey($key);
      $combine->setFiles($content);
      $combine->save();
    }
    $cache->set($key, true);
  }

  return $key;
}