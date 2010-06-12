<?php
/**
 * sfCombineMinifierPacker
 *
 * @package    sfCombinePlugin
 * @subpackage miniferPacker
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */

class sfCombineMinifierPacker implements sfCombineMinifierInterface
{
  /**
   * @see sfCombineMinifierInterface
   * @param   $options    (Optional) array params
   *                      - encoding: 'None', 'Numeric', 'Normal', 'High ASCII'
   *                      (string/int - default 'Normal')
   *                      - fast_decode: include the fast decoder in the packed
   *                      result (bool - default true
   *                      - specialChars: if you are flagged your private and
   *                      local variables (bool - default false
   */
  static public function minify($content, array $options = array())
  {
    $packer = new JavaScriptPacker(
      $content,
      (isset($options['encoding']) ? $options['encoding'] : 'Normal'),
      (isset($options['fast_decode']) ? $options['fast_decode'] : true),
      (isset($options['special_chars']) ? $options['special_chars'] : false)
    );

    return $packer->pack();
  }
}
