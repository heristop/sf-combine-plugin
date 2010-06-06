<?php
/**
 * sfCombineMinifierCssMin
 *
 * @package    sfCombinePlugin
 * @subpackage miniferCssMin
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */

class sfCombineMinifierCssMin implements sfCombineMinifierInterface
{
  /**
   * @see sfCombineMinifierInterface
   */
  static public function minify($content, array $options = array())
  {
    return cssmin::minify($content, $options);
  }
}
