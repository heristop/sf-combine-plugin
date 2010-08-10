<?php
/**
 * sfCombineMinifierGoogleClosure
 *
 * @package    sfCombinePlugin
 * @subpackage miniferGoogleClosure
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */

class sfCombineMinifierGoogleClosure extends sfCombineMinifierTempFileAbstract
implements sfCombineMinifierInterface
{
  /**
   * @see getDefaultOptions
   */
  public function getDefaultOptions()
  {
    $options = array(
      'jar_location' => dirname(__FILE__)
                       . '/../vendor/google-closure'
                       . '/compiler.jar',
      'charset' => 'utf-8',
      // must be WHITESPACE_ONLY, SIMPLE_OPTIMIZATIONS, ADVANCED_OPTIMIZATIONS
      'compilation_level' => 'SIMPLE_OPTIMIZATIONS'
    );

    return array_merge(parent::getDefaultOptions(), $options);
  }

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
   * @see     _process
   * @throws  Exception
   */
  protected function _process($tempFile)
  {
    $options = $this->getOptions();

    // create command

    $jarLocation = $options['jar_location'];

    if (!file_exists($jarLocation))
    {
      throw new Exception('Google Closure Jar file does not exist');
    }

    $commandOptions = '';

    $command = 'java -jar ' 
             . $jarLocation . ' '
             . $this->_buildCommandOptions() . ' '
             . '--js ' . escapeshellarg($tempFile)
    ;

    exec($command, $output, $return);
    
    if ($return !== 0)
    {
      throw new Exception('Google Closure Compiler returned error', $return);
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

    if (isset($options['charset']) && $options['charset'])
    {
      $commandOptions .= ' --charset ' . $options['charset'];
    }

    if (isset($options['compilation_level']) && $options['compilation_level'])
    {
      $commandOptions .= ' --compilation_level ' . $options['compilation_level'];
    }

    return $commandOptions ? substr($commandOptions, 1) : '';
  }

}
