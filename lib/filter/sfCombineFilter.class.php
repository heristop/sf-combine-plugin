<?php
/**
 * sfCombineFilter
 *
 * @package    sfCombinePlugin
 * @subpackage filter
 * @author     Alexandre Mogère
 */
class sfCombineFilter extends sfFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain $filterChain A sfFilterChain instance
   */
  public function execute($filterChain)
  {
    // execute next filter
    $filterChain->execute();

    // execute this filter only once
    $response = $this->context->getResponse();

    // include javascripts and stylesheets
    $content = $response->getContent();
    if (false !== ($pos = strpos($content, '</head>'))          // has a </head> tag
     && false !== strpos($response->getContentType(), 'html'))  // is html content
    {
      $this->context->getConfiguration()->loadHelpers(array('Tag', 'Asset', 'Url', 'sfCombine'));
      $html = '';
      if (!sfConfig::get('symfony.asset.javascripts_included', false))
      {
        $html .= get_combined_javascripts();
      }
      if (!sfConfig::get('symfony.asset.stylesheets_included', false))
      {
        $html .= get_combined_stylesheets();
      }

      if ($html)
      {
        $response->setContent(substr($content, 0, $pos).$html.substr($content, $pos));
      }
    }

    sfConfig::set('symfony.asset.javascripts_included', false);
    sfConfig::set('symfony.asset.stylesheets_included', false);
  }
}