<?php
/**
 * sfCombineMinifierYuiAbstract
 *
 * @package    sfCombinePlugin
 * @subpackage miniferYuiAbstract
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */

abstract class sfCombineMinifierYuiAbstract
extends sfCombineMinifierTempFileAbstract
{
  /**
   * @see getDefaultOptions
   */
  public function getDefaultOptions()
  {
    $options = array(
      'jar_location' => dirname(__FILE__)
                       . '/../vendor/yuicompressor-2.4.2'
                       . '/yuicompressor-2.4.2.jar',
      'charset' => 'utf-8',
      'line_break' => '5000'
    );

    return array_merge(parent::getDefaultOptions(), $options);
  }
  
  /**
   * @see     _process
   * @throws  Exception
   */
  protected function _process($tempFile)
  {
    $options = $this->getOptions();

    // create command

    $jarLocation = $options['jar_location'];

    if (!file_exists($jarLocation)) {
      throw new Exception('YUI Jar file does not exist');
    }

    $commandOptions = '';

    $command = 'java -jar ' 
             . $jarLocation . ' '
             . '--type ' . $this->_getType() . ' '
             . $this->_buildCommandOptions() . ' '
             . escapeshellarg($tempFile);

    exec($command, $output, $return);

    if ($return !== 0) {
      throw new Exception('YUI Compressor returned error', $return);
    }

    return implode($output, "\n");

  }

  /**
   * Build options for the command line
   *
   * @return  string
   */
  protected function _buildCommandOptions()
  {
    $options = $this->getOptions();

    $commandOptions = '';

    if (isset($options['charset']) && $options['charset']) {
      $commandOptions .= ' --charset ' . $options['charset'];
    }

    if (isset($options['line_break']) && $options['line_break']) {
      $commandOptions .= ' --line-break ' . $options['line_break'];
    }

    return $commandOptions ? substr($commandOptions, 1) : '';
  }

  /**
   * @return  string
   */
  abstract protected function _getType();
}
