<?php
/**
 * sfCombine Helper Functions
 * 
 * @package     sfCombine
 * @subpackage  Helper
 * @author      Alexandre Mogères
 * @author      Kevin Dew <kev@dewsolutions.co.uk>
 */

/**
 * Add a javascript file for grouping
 *
 * @param   string $js            The javascript filename
 * @param   string $group         (Optional) The name of the group to be added
 *                                to. Default empty string.
 * @param   bool   $doNotCombine  (Optional) Whether to blacklist this file from
 *                                combining. Default false
 * @param   string $position      (Optional) @see sfWebResponse::addJavascript
 * @param   array  $options       (Optional) @see sfWebResponse::addJavascript
 * @return  void
 */
function use_javascript_grouped(
  $js,
  $group = '',
  $doNotCombine = false,
  $position = '',
  $options = array()
)
{
  $manager = sfCombineManager::getJsManager();

  sfContext::getInstance()
    ->getResponse()
    ->addJavascript($js, $position, $options)
  ;

  $manager->addToGroup($group, $js);

  if ($doNotCombine)
  {
    $manager->addSkip($js);
  }
}

/**
 * Output the combined javascripts
 *
 * @see get_combined_javascripts
 */
function include_combined_javascripts(
  $groups = null,
  $groupType = sfCombineManager::GROUP_INCLUDE,
  $onlyUnusedGroups = true,
  $markGroupsUsed = true
)
{
  echo get_combined_javascripts($groups, $groupType);
}

/**
 * Get the combined Javascripts in script links. Can get all groups or a
 * selection. Calling this method will stop symfony automatically inserting
 * scripts
 *
 *
 * @param   mixed $groupsUse        (Optional) A string or array of groups to
 *                                  include or exclude. Null for this to be
 *                                  ignored. Default null.
 * @param   int   $groupsUseType    (Optional) The type of grouping either
 *                                  sfCombineManager::GROUP_INCLUDE or
 *                                  sfCombineManager::GROUP_EXCLUDE.
 *                                  These dictate whether the group(s) in
 *                                  the previous argument should be marked
 *                                  as used or every group marked as used.
 *                                  Default sfCombineManager::GROUP_INCLUDE
 * @param   bool  $onlyUnusedGroups (Optional) Only use unused groups. Default
 *                                  true.
 * @param   bool  $markGroupsUsed   (Optional) Mark the groups that are used
 *                                  as used. Default true.
 * @return  string
 */
function get_combined_javascripts(
  $groups = null,
  $groupType = sfCombineManager::GROUP_INCLUDE,
  $onlyUnusedGroups = true,
  $markGroupsUsed = true
)
{
  // because of groups its better to run this through when sfCombine is disabled
  // but just set all files to do not combine
  $sfCombineEnabled = sfConfig::get('app_sfCombinePlugin_enabled', false);

  $manager = sfCombineManager::getJsManager();

  sfConfig::set('symfony.asset.javascripts_included', true);

  $response = sfContext::getInstance()->getResponse();
  $config = sfConfig::get('app_sfCombinePlugin_js', array());
  $doNotCombine = isset($config['combine_skip'])
    ? $config['combine_skip']
    : array()
  ;
  $manager->setSkips(array_merge(
    $manager->getSkips(),
    $doNotCombine
  ));

  $groupedFiles = $manager->getAssetsByGroup(
    $response->getJavascripts(),
    // handling disabled plugin
    ($sfCombineEnabled ? $config['combine'] : false),
    $groups,
    $groupType,
    $onlyUnusedGroups,
    $markGroupsUsed,
    array(new sfCombineCombinerJs(), 'getAssetPath')
  );

  $html = '';

  $timestampConfig = sfConfig::get('app_sfCombinePlugin_timestamp', array());
  foreach ($groupedFiles as $fileDetails)
  {
  
    if (!$fileDetails['combinable'])
    {

      $file = $fileDetails['files'];

      if (
        isset($timestampConfig['uncombinable'])
        &&
        $timestampConfig['uncombinable']
        &&
        $fileDetails['timestamp']
      )
      {
        // add timestamp
        if (strpos($file, '?') !== false)
        {
          $file .= '&t=' . $fileDetails['timestamp'];
        }
        else
        {
          $file .= '?t=' . $fileDetails['timestamp'];
        }
      }

      $html .= javascript_include_tag(
        javascript_path($file),
        $fileDetails['options']
      );
    }
    else
    {

      $route = isset($config['route']) ? $config['route'] : 'sfCombine';

      $html .= javascript_include_tag(
        url_for(
          '@' . $route . '?module=sfCombine&action=js&'
          . sfCombineUrl::getUrlString(
            $fileDetails['files'], $fileDetails['timestamp']
          )
        ),
        $fileDetails['options']
      );
    }
  }
  
  return $html;
}

/**
 * @see use_javascript_grouped
 */
function use_stylesheet_grouped(
  $css,
  $group = '',
  $doNotCombine = false,
  $position = '',
  $options = array()
)
{
  $manager = sfCombineManager::getCssManager();

  sfContext::getInstance()->getResponse()->addStylesheet(
    $css, $position, $options
  );

  $manager->addToGroup($group, $css);

  if ($doNotCombine)
  {
    $manager->addSkip($css);
  }
}

/**
 * @see include_combined_javascripts
 */
function include_combined_stylesheets(
  $groups = null,
  $groupType = sfCombineManager::GROUP_INCLUDE,
  $onlyUnusedGroups = true,
  $markGroupsUsed = true
)
{
  echo get_combined_stylesheets();
}

/**
 * @see get_combined_javascripts
 */
function get_combined_stylesheets(
  $groups = null,
  $groupType = sfCombineManager::GROUP_INCLUDE,
  $onlyUnusedGroups = true,
  $markGroupsUsed = true
)
{
  // because of groups its better to run this through when sfCombine is disabled
  // but just set all files to do not combine
  $sfCombineEnabled = sfConfig::get('app_sfCombinePlugin_enabled', false);

  $manager = sfCombineManager::getCssManager();

  sfConfig::set('symfony.asset.stylesheets_included', true);

  $response = sfContext::getInstance()->getResponse();

  $config = sfConfig::get('app_sfCombinePlugin_css', array());

  $doNotCombine = isset($config['combine_skip'])
    ? $config['combine_skip']
    : array()
  ;

  $manager->setSkips(array_merge(
    $manager->getSkips(),
    $doNotCombine
  ));

  $groupedFiles = $manager->getAssetsByGroup(
    $response->getStylesheets(),
    // handling disabled plugin
    ($sfCombineEnabled ? $config['combine'] : false),
    $groups,
    $groupType,
    $onlyUnusedGroups,
    $markGroupsUsed,
    array(new sfCombineCombinerCss(), 'getAssetPath')
  );

  $html = '';

  $timestampConfig = sfConfig::get('app_sfCombinePlugin_timestamp', array());

  foreach ($groupedFiles as $fileDetails)
  {
    if (!$fileDetails['combinable'])
    {

      $file = $fileDetails['files'];

      if (
        isset($timestampConfig['uncombinable'])
        &&
        $timestampConfig['uncombinable']
        &&
        $fileDetails['timestamp']
      )
      {
        if (strpos($file, '?') !== false)
        {
          $file .= '&t=' . $fileDetails['timestamp'];
        }
        else
        {
          $file .= '?t=' . $fileDetails['timestamp'];
        }
      }

      $html .= stylesheet_tag(
        stylesheet_path($file),
        $fileDetails['options']
      );

    } 
    else
    {

      $route = isset($config['route']) ? $config['route'] : 'sfCombine';

      $html .= stylesheet_tag(
        url_for(
          '@' . $route . '?module=sfCombine&action=css&'
          . sfCombineUrl::getUrlString(
              $fileDetails['files'], $fileDetails['timestamp']
            )
        ),
        $fileDetails['options']
      );
    }
  }

  return $html;
}

/**
 * Method to get both stylesheets and javascripts through one function call
 *
 * @see include_combined_javacripts
 */
function include_combined_assets(
  $groups = null,
  $groupType = sfCombineManager::GROUP_INCLUDE,
  $onlyUnusedGroups = true,
  $markGroupsUsed = true
)
{
  echo get_combined_assets(
    $groups, $groupType, $onlyUnusedGroups, $markGroupsUsed
  );
}

/**
 * Method to get both stylesheets and javascripts through one function call
 *
 * @see get_combined_javacripts
 */
function get_combined_assets(
  $groups = null,
  $groupType = sfCombineManager::GROUP_INCLUDE,
  $onlyUnusedGroups = true,
  $markGroupsUsed = true
)
{
  return get_combined_javascripts(
    $groups, $groupType, $onlyUnusedGroups, $markGroupsUsed
  ) . get_combined_stylesheets(
    $groups, $groupType, $onlyUnusedGroups, $markGroupsUsed
  );
}

/**
 * @see     javascript_tag
 * @param   string $content
 * @return  string
 */
function javascript_tag_minified($content = null)
{
  use_helper('JavascriptBase');

  if (!sfConfig::get('app_sfCombinePlugin_enabled', false))
  {
    return javascript_tag($content);
  }
  
  if (null === $content)
  {
    ob_start();
  }
  else
  {
    // minify content
    $content = sfCombineUtility::minifyInlineJs($content);

    return javascript_tag($content);
  }
}

/**
 * @see     end_javascript_tag
 * @return  void
 */
function end_javascript_tag_minified()
{
  $js = ob_get_clean();
  echo javascript_tag_minified($js);
}

/**
 * @see     javascript_tag_minified
 * @param   string $content
 * @return  string
 */
function style_tag_minified($content = null, array $elementOptions = array())
{
  use_helper('Tag');

  if (null === $content)
  {
    ob_start();
  }
  else
  {
    // minify content
    $content = sfCombineUtility::minifyInlineCss($content);

    if (!isset($elementOptions['type']))
    {
      $elementOptions['type'] = 'text/css';
    }

    return content_tag(
      'style',
      "\n/*" . cdata_section("*/\n$content\n/*") . "*/\n",
      $elementOptions
    );
  }
}

/**
 * @see     end_javascript_tag
 * @return  void
 */
function end_style_tag_minified(array $elementOptions = array())
{
  $css = ob_get_clean();
  echo style_tag_minified($css);
}