<?php
/**
 * sfCombineMinifierJsMin
 *
 * @package    sfCombinePlugin
 * @subpackage miniferJsMin
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */

class sfCombineMinifierJsMin implements sfCombineMinifierInterface
{
  /**
   * @see sfCombineMinifierInterface
   */
  static public function minify($content, array $options = array())
  {
    return JSMin::minify($content);
  }
}
