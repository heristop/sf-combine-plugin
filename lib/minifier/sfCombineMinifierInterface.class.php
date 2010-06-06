<?php
/**
 * sfCombineMinifierInterface
 *
 * @package    sfCombinePlugin
 * @subpackage minifierInterface
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */
interface sfCombineMinifierInterface
{
  /**
   * Method to minify a string
   *
   * @param   string  $content  String to be minified
   * @param   array   $options  Options for the minifier
   * @return  string
   */
  static public function minify($content, array $options = array());

}