<?php
/**
 * sfCombine Model
 *
 * @package     sfCombinePlugin
 * @subpackage  sfCombine
 * @author      Kevin Dew <kev@dewsolutions.co.uk>
 */
abstract class PluginsfCombine extends BasesfCombine
{
  /**
   * Get an instance by key
   *
   * @param   string  $key
   * @return  sfCombine
   */
  static public function getByKey($key)
  {
    return Doctrine::getTable('sfCombine')
                   ->find($key);
  }

  /**
   * Check if a key exists in the db
   *
   * @param   string   $key
   * @return  bool
   */
  static public function hasKey($key)
  {
    return self::getByKey($key) == true;

  }

  /**
   * Get all instances
   *
   * @return DoctrineCollection
   */
  static public function getAll()
  {
    return Doctrine::getTable('sfCombine')
                   ->findAll();
  }

  /**
   *
   * @return int
   */
  static public function deleteAll()
  {
    return Doctrine::getTable('sfCombine')
                   ->createQuery()
                   ->delete()
                   ->execute();
  }
}