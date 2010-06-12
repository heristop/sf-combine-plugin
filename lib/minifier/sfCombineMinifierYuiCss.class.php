<?php
/**
 * sfCombineMinifierYuiCss
 *
 * @package    sfCombinePlugin
 * @subpackage miniferYuiCss
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */

class sfCombineMinifierYuiCss extends sfCombineMinifierYuiAbstract
implements sfCombineMinifierInterface
{
  const TYPE = 'css';

  /**
   * @see     sfCombineMinifierInterface::minify
   * @param   string  $content
   * @param   array   $options
   * @return  string
   */
  static public function minify($content, array $options = array())
  {
    $obj = new self($content, $options);
    return $obj->execute();
  }

  /**
   * @see parent
   */
  protected function _getType()
  {
    return self::TYPE;
  }
}
