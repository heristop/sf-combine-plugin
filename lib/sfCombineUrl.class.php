<?php
/**
 * A class of utility methods for handling URLs in sfCombine
 *
 * @package     sfCombine
 * @subpackage  sfCombineUrl
 * @author      Alexandre Mogère
 * @author      Kevin Dew <kev@dewsolutions.co.uk>
 */
class sfCombineUrl
{
  /**
   * Get the url for a collection of files
   *
   * @param   array $files
   * @return  string
   */
  static public function getUrlString($files, $timestamp)
  {
    switch(sfConfig::get('app_sfCombinePlugin_url_type', 'key'))
    {
      case 'files':
        $string = 'files=' . self::getFileString($files);
        break;
      case 'base64':
        $string = 'base64=' . self::getBase64($files);
        break;
      case 'key':
      default:
        $string = 'key=' . self::getKey($files);
        break;

    }

    if (sfConfig::get('app_sfCombinePlugin_asset_version', false))
    {
      $string .= '&v='
              . sfConfig::get('app_sfCombinePlugin_asset_version');
    }

    if ($timestamp)
    {
      $string .= '&t=' . $timestamp;
    }

    return $string;
  }

  /**
   * Return the array of files into a string that can be used in a URL
   *
   * @param  array  $files      Array of file names
   * @param  string $seperator  (Optional) Default ' '
   * @return string
   */
  static public function getFileString(array $files, $separator = ' ')
  {
    return urlencode(
      implode($separator, array_map('urlencode', $files))
    );
  }

  /**
   * Take a file string and convert it into an array of files
   *
   * @param  string $fileString
   * @param  string $seperator  (Optional) Default ' '
   * @return array
   */
  static public function getFiles(
    $fileString, $urlDecoded = false, $separator = ' '
  )
  {
    if (!$urlDecoded)
    {
      $fileString = urldecode($fileString);
    }

    return array_map('urldecode', explode($separator, $fileString));
  }

  /**
   * Return a base64 encoded list of files
   *
   * @see getFileString
   */
  static public function getBase64(array $files, $separator = ' ')
  {
    return base64_encode(self::getFileString($files, $separator));
  }

  /**
   * Take a base64 string and convert it into an array of files
   *
   * @see getFiles
   */
  static public function getFilesByBase64($base64, $separator = ' ')
  {
    $string = false;

    if ($base64)
    {
      $string = base64_decode($base64);
    }

    return self::getFiles(
      $string ? $string : '',
      false,
      $separator
    );
  }

  /**
   * Return a hash which refers to an entry in the db describing the files
   *
   * @see getFileString
   */
  static public function getKey(array $files, $separator = ' ')
  {
    $content = self::getBase64($files, $separator);
    $key     = sha1($content);
    $check   = false;

    if (function_exists('apc_store') && ini_get('apc.enabled'))
    {
      $cache = new sfAPCCache();
      $check = $cache->has($key);
    }
    else
    {
      $cache = new sfFileCache(array(
        'cache_dir' => sfCombineUtility::getCacheDir()
      ));
      $check = $cache->has($key);
    }

    // Checks if key exists
    if (false === $check)
    {
      // now just doctrine
      if (! class_exists('sfCombine'))
      {
        throw new Exception('Call the task `doctrine:build-model` or use base64 url');
      }
      
      $keyExists = Doctrine::getTable('sfCombine')->find($key);
      if (!$keyExists)
      {
        $combine = new sfCombine();
        $combine->setAssetKey($key);
        $combine->setFiles($content);
        $combine->save();
      }
        
      $cache->set($key, $content);
    }
    
    

    return $key;
  }

  /**
   * Take a db hash and convert it into an array of files
   *
   * @see getFiles
   */
  static public function getFilesByKey($key, $separator = ' ')
  {
    $base64 = false;
  
    // try get base64 from cache
    if (function_exists('apc_store') && ini_get('apc.enabled'))
    {
      $cache = new sfAPCCache();
      $base64 = $cache->get($key);
    }

    if (!$base64)
    {
      $cache = new sfFileCache(array(
        'cache_dir' => sfCombineUtility::getCacheDir()
      ));
      $base64 = $cache->get($key);
    }

    // check db
    if (!$base64 && class_exists('sfCombine'))
    {
      $combine = Doctrine::getTable('sfCombine')->find($key);
      $base64 = $combine ? $combine->getFiles() : false;
    }

    return self::getFilesByBase64($base64, $separator);
  }
}
