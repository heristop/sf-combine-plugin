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
  public function execute($filterChain)
  {
    // execute next filter
    $filterChain->execute();
    
    $response = $this->context->getResponse();
    $content = $response->getContent();
    
    // include javascripts and stylesheets
    if (false !== ($pos = strpos($content, '</head>'))          // has a </head> tag
     && false !== strpos($response->getContentType(), 'html'))  // is html content
    {
      sfLoader::loadHelpers(array('sfCombine'));
      $html = '';
      
      if (!$response->getParameter('javascripts_included', false, 'symfony/view/asset'))
      {
        $html .= get_combined_javascripts();
      }
      
      if (!$response->getParameter('stylesheets_included', false, 'symfony/view/asset'))
      {
        $html .= get_combined_stylesheets();
      }
      
      if ($html)
      {
        $response->setContent(substr($content, 0, $pos) . $html . substr($content, $pos));
      }
    }
    
    $response->setParameter('javascripts_included', false, 'symfony/view/asset');
    $response->setParameter('stylesheets_included', false, 'symfony/view/asset');
  }
}