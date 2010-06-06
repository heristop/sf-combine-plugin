<?php
/**
 * sfCombineCombinerCss
 *
 * @package    sfCombinePlugin
 * @subpackage combiner
 * @author     Alexandre Mogères
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */
class sfCombineCombinerCss extends sfCombineCombiner
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
      array('sfCombineMinifierMinifyCss', 'minify')
    );
  }

  /**
   * @see sfCombineCombiner
   */
  static public function getAssetPath($file)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('Asset');
    return stylesheet_path($file);
  }

  /**
   * Return a cache directory for just minified css files
   *
   * @return  string
   */
  static public function getCacheDir()
  {
    return parent::getCacheDir() .  '/css';
  }

  /**
   * Does CSS specific operations such as rewriting URLs
   *
   * @todo  Could be extended to auto load local imported files
   * @see   parent
   */
  protected function _collateFileContents(
    $allowIncludes = false,
    $includeSuffixes = array(),
    $dontInclude = array()
  )
  {
    $fileContents = parent::_collateFileContents(
      $allowIncludes,
      $includeSuffixes,
      $dontInclude
    );

    // @todo @imports

    // fix url paths
    foreach ($fileContents as $file => $contents) {
      $fileContents[$file] = self::rewriteUris(
        $this->getAssetPath($file), $contents
      );
    }

    $this->setFileContents($fileContents);

    return $fileContents;
  }

  /**
   * @see parent
   */
  public function generateOutput(
    $minify = false,
    $minifySkipSuffixes = array(),
    $minifySkip = array()
  )
  {
    $output = parent::generateOutput($minify, $minifySkipSuffixes, $minifySkip);

    $output = self::fixImports(
      $output,
      $this->getConfigOption('prepend_imports', true),
      $this->getConfigOption('prepend_imports_warning', '')
    );

    $output = self::fixCharset(
      $output,
      $this->getConfigOption('keep_charset', false)
    );

    return $output;
  }

  /**
   * Rewrite the urls in a CSS file
   *
   * @param   string  $file     The path to the file
   * @param   string  $content  The file contents
   * @return  string
   */
  static public function rewriteUris($file, $content)
  {
    $path = dirname($file) . '/';

    return Minify_CSS_UriRewriter::prepend($content, $path);
  }
 
  /**
   * Remove CSS comments
   *
   * Taken from the Minify package
   * 
   * @param   string  $content
   * @return  string
   */
  static public function removeComments($content)
  {
    return preg_replace('@/\\*[\\s\\S]*?\\*/@', '', $content);
  }

  /**
   * From a combined bunch of CSS files remove the @charset declarations
   *
   * @param   string  $content
   * @param   string  $useFirstCharset  (Optional) Whether to use the first
   *                                    @charset found at the top
   * @return  string
   */
  static public function fixCharset($content, $useFirstCharset = true)
  {
    // get charsets to remove

    $removedComments = self::removeComments($content);

    preg_match_all('/@charset.*?;/i', $removedComments, $matches);

    if ($matches[0]) {
      // remove charsets
      $content = str_replace($matches[0], '', $content);
      return $matches[0][0] . PHP_EOL . $content;
    } else {
      return $content;
    }
  }

  /**
   * From a combined bunch of CSS files move the @imports to the top (as is
   * required for use of @import)
   *
   * Note because @import has to be at the top it really doesn't work well with
   * combining of files
   *
   * Future scope is to include local ones
   *
   * Based on similar functionality in Minify
   *
   * @param   string  $content
   * @param   bool    $includeImports (Optional) Whether to include imports in
   *                                  the combined file. Default true.
   * @param   string  $prependWarning (Optional) A warning to prepend when
   *                                  imports are used
   * @return  string
   */
  static public function fixImports(
    $content,
    $includeImports = true,
    $prependWarning = ''
  )
  {
    $removedComments = self::removeComments($content);

    preg_match_all('/@import.*?;/', $removedComments, $matches);

    if ($matches[0]) {

      return ($prependWarning ? "/* $prependWarning */" : '') . PHP_EOL
             . ($includeImports ? implode('', $matches[0]) . PHP_EOL : '')
             . str_replace($matches[0], '', $content);
    } else {
      return $content;
    }

    
  }
}