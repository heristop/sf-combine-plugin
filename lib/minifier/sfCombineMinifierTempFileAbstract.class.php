<?php
/**
 * sfCombineMinifierTempFileAbstract
 *
 * @package    sfCombinePlugin
 * @subpackage miniferTempFileAbstract
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 */

abstract class sfCombineMinifierTempFileAbstract
{
  /**
   * @var   string
   */
  protected $_content;

  /**
   * @var   array
   */
  protected $_options = array();

  /**
   * @param   string  $content
   * @param   array   $options
   * @return  void
   */
  public function  __construct($content = '', array $options = array())
  {
    $defaultOptions = $this->getDefaultOptions();

    $this->setContent($content)
         ->setOptions(array_merge($defaultOptions, $options));
  }

  /**
   * Method to minify the content
   * Creates the temp file / deletes it and runs the process method
   *
   * @return  string
   * @throws  Exception
   */
  public function execute()
  {
    try {

      $tempFile = $this->createTempFile();
      $minified = $this->_process($tempFile);
      $this->deleteTempFile($tempFile);
      return $minified;

    } catch (Exception $e) {

      if ($tempFile) {
        $this->deleteTempFile($tempFile);
        throw $e;
      }

    }
  }

  /**
   * Method to process the minification
   *
   * @param string  $tempFile
   */
  abstract protected function _process($tempFile);

  /**
   * @return  string
   */
  public function getContent()
  {
    return $this->_content;
  }

  /**
   * @param   string  $content
   * @return  self
   */
  public function setContent($content)
  {
    $this->_content = $content;
    return $this;
  }

  /**
   * @return  array
   */
  public function getOptions()
  {
    return $this->_options;
  }

  /**
   * @param   array $options
   * @return  array
   */
  public function setOptions(array $options)
  {
    $this->_options = $options;
    return $this;
  }

  /**
   * Method to retrieve default options, expected to be overwritten
   *
   * @return  array
   */
  public function getDefaultOptions()
  {
    return array(
      'tempPrefix' => 'sfCombine'
    );
  }

  /**
   * Creates a temporary file
   *
   * @return  string  The path to the temp file
   */
  public function createTempFile()
  {
    $options = $this->getOptions();

    $tempFile = tempnam(
      (isset($options['tempDir']) ? $options['tempDir'] : ''),
      (isset($options['tempPrefix']) ? $options['tempPrefix'] : '')
    );

    if (!$tempFile) {
      throw new Exception('Temporary file could not be created');
    }

    $result = file_put_contents($tempFile, $this->getContent());

    if ($result === false) {
      throw new Exception('Writing to temporary file failed');
    }

    return $tempFile;
  }

  /**
   * Delete a temporary file
   *
   * @param   string  $tempFile   Path to the temp file
   * @return  bool
   */
  public function deleteTempFile($tempFile)
  {
    return @unlink($tempFile);
  }
}
