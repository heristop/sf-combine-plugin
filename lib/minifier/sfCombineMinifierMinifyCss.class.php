<?php
/**
 * sfCombineMinifierMinifyCss
 *
 * @package    sfCombinePlugin
 * @subpackage miniferMinifyCss
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */

class sfCombineMinifierMinifyCss implements sfCombineMinifierInterface
{
  /**
   * @see sfCombineMinifierInterface
   */
  static public function minify($content, array $options = array())
  {
    return Minify_CSS_Compressor::process($content, $options);
  }
}
