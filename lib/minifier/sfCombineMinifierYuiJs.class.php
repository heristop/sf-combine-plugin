<?php
/**
 * sfCombineMinifierYuiJs
 *
 * @package    sfCombinePlugin
 * @subpackage miniferYuiJs
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */

class sfCombineMinifierYuiJs extends sfCombineMinifierYuiAbstract
implements sfCombineMinifierInterface
{
  const TYPE = 'js';

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

  /**
   * @see parent
   */
  public function getDefaultOptions()
  {
    $options = array(
      'nomunge' => false,
      'preserve_semi' => false,
      'disable_optimizations' => false
    );

    return array_merge(parent::getDefaultOptions(), $options);
  }

  /**
   * @see parent
   */
  protected function _buildCommandOptions()
  {
    $options = $this->getOptions();

    $commandOptions = '';

    $parentOptions = parent::_buildCommandOptions();

    if ($parentOptions) {
      $commandOptions .= ' ' . $parentOptions;
    }

    if (isset($options['nomunge']) && $options['nomunge']) {
      $commandOptions .= ' --nomunge';
    }

    if (isset($options['preserve_semi']) && $options['preserve_semi']) {
      $commandOptions .= ' --preserve-semi';
    }
    if (isset($options['disable_optimizations'])
      && $options['disable_optimizations']
    ) {
      $commandOptions .= ' --disable-optimizations';
    }

    return $commandOptions ? substr($commandOptions, 1) : '';
  }

}
