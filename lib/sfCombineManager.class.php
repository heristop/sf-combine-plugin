<?php
/**
 * A manager for sfCombine files
 *
 * @package     sfCombine
 * @subpackage  sfCombineManager
 * @author      Alexandre Mogère
 * @author      Kevin Dew <kev@dewsolutions.co.uk>
 */
class sfCombineManager
{
  /**
   * Grouping types
   */
  const GROUP_EXCLUDE = 0;
  const GROUP_INCLUDE = 1;

  /**
   * Javascript Manager
   *
   * @var sfCombineManager
   */
  static protected $_jsManager = null;

  /**
   * CSS Manager
   *
   * @var sfCombineManager
   */
  static protected $_cssManager = null;

  /**
   * An array of file names => group names
   *
   * @var array
   */
  protected $_groups = array();

  /**
   * An array of file names that are to be skipped
   *
   * @var array
   */
  protected $_skips = array();

  /**
   * An array of group names that have already been used
   *
   * @var array
   */
  protected $_usedGroups = array();


  /**
   * Retrieve the Javascript Manager, creates one if it doesn't exist
   *
   * @return sfCombineManager
   */
  static public function getJsManager()
  {
    if (self::$_jsManager === null)
    {
      self::$_jsManager = new self();
    }
    return self::$_jsManager;
  }

  /**
   * Retrieve the CSS Manager, creates one if it doesn't exist
   *
   * @return sfCombineManager
   */
  static public function getCssManager()
  {
    if (self::$_cssManager === null)
    {
      self::$_cssManager = new self();
    }
    return self::$_cssManager;
  }

  /**
   * Resets the grouping rules
   *
   * @return  void
   */
  public function reset()
  {
    $this->setGroups(array());
    $this->setSkips(array());
    $this->setUsedGroups(array());
  }

  /**
   * Get an array of files and their group
   *
   * @param   bool    $withFileNames  (Optional) whether to return an array of
   *                                  every file => group or just an array of 
   *                                  groups
   *
   * @return  array   file name => group name
   */
  public function getGroups($withFileNames = true)
  {
    return $withFileNames ? $this->_groups : array_values($this->_groups);
  }

  /**
   * Set the groups
   *
   * @param   array   $groups   file name => group name
   * @return  sfCombineManager
   */
  public function setGroups(array $groups)
  {
    $this->_groups = $groups;
    return $this;
  }

  /**
   * Add a grouped file
   *
   * @param   string $groupName Name of the group
   * @param   string $file      Name of the file
   * @return  sfCombineManager
   */
  public function addToGroup($groupName, $file)
  {
    $this->_groups[$file] = $groupName;
    return $this;
  }

  /**
   * Remove a file from a particular group
   *
   * @param   string $groupName
   * @param   string $file
   * @return  sfCombineManager
   */
  public function removeFromGroup($groupName, $file)
  {
    if (
      array_key_exists($file, $this->_groups)
      &&
      $this->_groups[$file] == $groupName
    )
    {
      unset($this->_groups[$file]);
    }

    return $this;
  }

  /**
   * Get files to be skipped
   *
   * @return array  An array of file names
   */
  public function getSkips()
  {
    return $this->_skips;
  }

  /**
   * Set the files to be skipped
   *
   * @param   array $skips  An array of file names
   * @return  sfCombineManager
   */
  public function setSkips(array $skips)
  {
    $this->_skips = $skips;
    return $this;
  }

  /**
   * Add a file to be skipped
   *
   * @param   string  $file
   * @return  sfCombineManager
   */
  public function addSkip($file)
  {
    $this->_skips[] = $file;
    return $this;
  }

  /**
   * Remove a file from the list of files to be skipped
   *
   * @param   string  $file
   * @return  sfCombineManager
   */
  public function removeFromSkips($file)
  {
    foreach (array_keys($this->_skips, $file) as $key)
    {
      unset($this->_skips[$key]);
    }
    return $this;
  }

  /**
   * Set the used groups
   *
   * @param   array $groups An array of group names
   * @return  sfCombineManager
   */
  public function setUsedGroups(array $groups)
  {
    $this->_usedGroups = $groups;
    return $this;
  }

  /**
   * Get the used groups
   *
   * @return  array An array of group names
   */
  public function getUsedGroups()
  {
    return $this->_usedGroups;
  }

  /**
   * Update the array of used groups
   *
   * @param   string|array  $groups     Either a single group name or an array
   *                                    of them
   * @param   int           $groupsType (Optional) The type of grouping either
   *                                    sfCombineManager::GROUP_INCLUDE or
   *                                    sfCombineManager::GROUP_EXCLUDE.
   *                                    These dictate whether the group(s) in
   *                                    the previous argument should be marked
   *                                    as used or every group marked as used.
   *                                    Default
   *                                    sfCombineManager::GROUP_INCLUDE
   * @return  sfCombineManager
   */
  public function updateUsedGroups(
    $groups,
    $groupsType = self::GROUP_INCLUDE
  )
  {
    $usedGroups = $this->getUsedGroups();

    if (is_string($groups))
    {
      $groups = array($groups);
    }

    if (($groups !== null) && ($groupsType === self::GROUP_INCLUDE))
    {
      // only allow specific groups

      $usedGroups = array_merge($usedGroups, $groups);

    } 
    else if (($groups === null) || ($groupsType === self::GROUP_EXCLUDE))
    {
      // only exclude specific groups

      $toMerge = array_diff(
        array_merge(
          array(''),
          $this->getGroups(false)
        ),
        ($groups !== null ? $groups : array())
      );

      $usedGroups = array_merge($usedGroups, $toMerge);
    }

    $this->setUsedGroups($usedGroups);

    return $this;
  }

  /**
   * Group the various assets together into an array. Determines which files
   * can be grouped together and the ordering.
   *
   * @param   array $assets           An array of file names
   * @param   bool  $combine          (Optional) Whether to allow combining or
   *                                  not. Default true.
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
   * @return  array An array of grouped files ready for combining. Array is in
   *                the form of files => array of file names, options => array
   *                of options for that grouping, combinable => bool (whether
   *                to put the file through sfCombine (otherwise links
   *                outside of sfCombine))
   */
  public function getAssetsByGroup(
    $assets,
    $combine = true,
    $groupsUse = null,
    $groupsUseType = self::GROUP_INCLUDE,
    $onlyUnusedGroups = true,
    $markGroupsUsed = true,
    $assetPathMethod = null
  )
  {
    $groupData = $this->getGroups();

    $notCombined = $combined = array();

    foreach($assets as $file => $options)
    {
      // check asset still needs to be added
      if (!array_key_exists($file, $assets))
      {
        continue;
      }

      // get the group this file is in
      $group = array_key_exists($file, $groupData)
               ? $groupData[$file]
               : '';

      if ($groupsUse !== null)
      {

        if (is_string($groupsUse))
        {
          $groupsUse = array($groupsUse);
        }

        if ($groupsUseType === self::GROUP_INCLUDE)
        {
          
          // only allow specific groups
          if (!in_array($group, $groupsUse))
          {
            // don't include this group
            continue;
          }

        }
        else if ($groupsUseType === self::GROUP_EXCLUDE)
        {

          // only exclude specific groups
          if (in_array($group, $groupsUse))
          {
            // don't include this group
            continue;
          }

        }

      }

      // don't output a used group
      if ($onlyUnusedGroups && in_array($group, $this->getUsedGroups()))
      {
        continue;
      }

      $timestampConfig = sfConfig::get('app_sfCombinePlugin_timestamp', array());
      $timestampEnabled = 
        isset($timestampConfig['enabled']) && $timestampConfig['enabled']
      ;

      if (
        !$combine
        ||
        !sfCombineUtility::combinableFile($file, $this->getSkips())
      )
      {

        if ($timestampEnabled)
        {
          $timestamp = sfCombineUtility::getModifiedTimestamp(
            $file, $assetPathMethod
          );
        }
        else
        {
          $timestamp = 0;
        }

        // file not combinable
        $notCombined[] = array(
          'files' => $file,
          'options' => $options,
          'combinable' => false,
          'timestamp' => $timestamp
        );
        unset($assets[$file]);
      } 
      else
      {

        // get the group this file is in
        $group = array_key_exists($file, $groupData)
          ? $groupData[$file]
          : ''
        ;

        $combinedFiles = array($file);

        // get timestamp
        if ($timestampEnabled)
        {
          $timestamp = sfCombineUtility::getModifiedTimestamp(
            $file, $assetPathMethod
          );
        } 
        else
        {
          $timestamp = 0;
        }

        unset($assets[$file]);

        foreach ($assets as $groupedFile => $groupedOptions)
        {

          if (
            !sfCombineUtility::combinableFile($groupedFile, $this->getSkips())
            ||
            $options != $groupedOptions
          )
          {
            continue;
          }

          $groupedFileGroup = array_key_exists($groupedFile, $groupData)
            ? $groupData[$groupedFile]
            : ''
          ;

          // add this file to this combine
          if ($group == $groupedFileGroup)
          {

            if ($timestampEnabled)
            {
              $groupedTimestamp = sfCombineUtility::getModifiedTimestamp(
                $groupedFile, $assetPathMethod
              );

              if ($groupedTimestamp > $timestamp)
              {
                $timestamp = $groupedTimestamp;
              }
            }

            $combinedFiles[] = $groupedFile;
            unset($assets[$groupedFile]);
          }
        }

        $combined[] = array(
          'files' => $combinedFiles,
          'options' => $options,
          'combinable' => true,
          'timestamp' => $timestamp
        );
      }
    }

    if ($markGroupsUsed)
    {
      $this->updateUsedGroups($groupsUse, $groupsUseType);
    }

    return array_merge($notCombined, $combined);
  }
}
