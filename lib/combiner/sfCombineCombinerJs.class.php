<?php
/**
 * sfCombineCombinerJs
 *
 * @package    sfCombinePlugin
 * @subpackage combiner
 * @author     Alexandre Mogères
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */
class sfCombineCombinerJs extends sfCombineCombiner
{
  /**
   * @see sfCombineCombiner
   */
  public function minify(
    $content, $minifyMethod = false, $minifyMethodOptions = array()
  )
  {
    return parent::minify(
      $content,
      $minifyMethod,
      $minifyMethodOptions,
      array('sfCombineMinifierJsMin', 'minify')
    );
  }

  /**
   * @see sfCombineCombiner
   */
  static public function getAssetPath($file)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('Asset');
    return javascript_path($file);
  }

  /**
   * Get cache directory thats specifically for js files
   *
   * @return string
   */
  static public function getCacheDir()
  {
    return parent::getCacheDir() .  '/js';
  }

  /**
   * Takes an array of filenames and returns each of them prefixed by //
   *
   * @param array $files
   * @return string
   */
  protected function _addFilenameComments($files)
  {
    $return = '';

    foreach ($files as $fileName) {
      $return .= '// ' . $fileName . PHP_EOL;
    }

    return $return;
  }

}