<?php
/**
 * sfCombineActions
 *
 * @package     sfCombinePlugin
 * @subpackage  Controller
 * @author      Alexandre Mogère
 * @author      Kevin Dew  <kev@dewsolutions.co.uk>
 */
class sfCombineActions extends sfActions
{
  /**
   * @return  void
   *
   * @see     sfAction
   */
  public function preExecute()
  {
    sfConfig::set('sf_web_debug', false);
    $this->setTemplate('asset');

    // cache
    sfCombineUtility::setCacheHeaders($this->getResponse());

    // gzip
    sfCombineUtility::setGzip();
  }


  /**
   * @see sfActions::execute
   */
  public function executeJs()
  {
    $this->getResponse()->setContentType('application/x-javascript');
    $config = sfConfig::get('app_sfCombinePlugin_js', array());
    $combinerClass = isset($config['combiner_class'])
      ? $config['combiner_class']
      : 'sfCombineCombinerJs'
    ;

    $combiner = new $combinerClass(
      $config,
      $this->getRequestParameter('key'),
      $this->getRequestParameter('base64'),
      $this->getRequestParameter('files')
    );

    $this->_setLastModifiedHeader($combiner);
    $this->assets = $combiner->process();
  }

  /**
   * @see sfActions::execute
   */
  public function executeCss()
  {
    $this->getResponse()->setContentType('text/css');
    $config = sfConfig::get('app_sfCombinePlugin_css', array());
    $combinerClass = isset($config['combiner_class'])
      ? $config['combiner_class']
      : 'sfCombineCombinerCss'
    ;

    $combiner = new $combinerClass(
      $config,
      $this->getRequestParameter('key'),
      $this->getRequestParameter('base64'),
      $this->getRequestParameter('files')
    );

    $this->_setLastModifiedHeader($combiner);
    $this->assets = $combiner->process();
  }

  /**
   * Set last modified header to response
   *
   * @param   sfCombineCombiner   $combiner
   * @return  void
   */
  protected function _setLastModifiedHeader(sfCombineCombiner $combiner)
  {
    if (sfConfig::get('app_sfCombinePlugin_set_last_modified_header', false))
    {
      $timestamp = $combiner->getLastModifiedTimestamp();

      if ($timestamp)
      {
        $this->getResponse()->setHttpHeader(
          'Last-Modified',
          $this->getResponse()->getDate($timestamp)
        );
      }
    }
  }
}