<?php


/**
 * Base class that represents a query for the 'sf_combine' table.
 *
 * 
 *
 * @method     sfCombineQuery orderByAssetKey($order = Criteria::ASC) Order by the asset_key column
 * @method     sfCombineQuery orderByFiles($order = Criteria::ASC) Order by the files column
 *
 * @method     sfCombineQuery groupByAssetKey() Group by the asset_key column
 * @method     sfCombineQuery groupByFiles() Group by the files column
 *
 * @method     sfCombineQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     sfCombineQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     sfCombineQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     sfCombine findOne(PropelPDO $con = null) Return the first sfCombine matching the query
 * @method     sfCombine findOneOrCreate(PropelPDO $con = null) Return the first sfCombine matching the query, or a new sfCombine object populated from the query conditions when no match is found
 *
 * @method     sfCombine findOneByAssetKey(string $asset_key) Return the first sfCombine filtered by the asset_key column
 * @method     sfCombine findOneByFiles(string $files) Return the first sfCombine filtered by the files column
 *
 * @method     array findByAssetKey(string $asset_key) Return sfCombine objects filtered by the asset_key column
 * @method     array findByFiles(string $files) Return sfCombine objects filtered by the files column
 *
 * @package    propel.generator.plugins.sfCombinePlugin.lib.model.om
 */
abstract class BasesfCombineQuery extends ModelCriteria
{

  /**
   * Initializes internal state of BasesfCombineQuery object.
   *
   * @param     string $dbName The dabase name
   * @param     string $modelName The phpName of a model, e.g. 'Book'
   * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
   */
  public function __construct($dbName = 'propel', $modelName = 'sfCombine', $modelAlias = null)
  {
    parent::__construct($dbName, $modelName, $modelAlias);
  }

  /**
   * Returns a new sfCombineQuery object.
   *
   * @param     string $modelAlias The alias of a model in the query
   * @param     Criteria $criteria Optional Criteria to build the query from
   *
   * @return    sfCombineQuery
   */
  public static function create($modelAlias = null, $criteria = null)
  {
    if ($criteria instanceof sfCombineQuery)
    {
      return $criteria;
    }
    $query = new sfCombineQuery();
    if (null !== $modelAlias)
    {
      $query->setModelAlias($modelAlias);
    }
    if ($criteria instanceof Criteria)
    {
      $query->mergeWith($criteria);
    }
    return $query;
  }

  /**
   * Find object by primary key
   * Use instance pooling to avoid a database query if the object exists
   * <code>
   * $obj  = $c->findPk(12, $con);
   * </code>
   * @param     mixed $key Primary key to use for the query
   * @param     PropelPDO $con an optional connection object
   *
   * @return    sfCombine|array|mixed the result, formatted by the current formatter
   */
  public function findPk($key, $con = null)
  {
    if ((null !== ($obj = sfCombinePeer::getInstanceFromPool((string) $key))) && $this->getFormatter()->isObjectFormatter())
    {
      // the object is alredy in the instance pool
      return $obj;
    }
    else
    {
      // the object has not been requested yet, or the formatter is not an object formatter
      $criteria = $this->isKeepQuery() ? clone $this : $this;
      $stmt = $criteria
        ->filterByPrimaryKey($key)
        ->getSelectStatement($con);
      return $criteria->getFormatter()->init($criteria)->formatOne($stmt);
    }
  }

  /**
   * Find objects by primary key
   * <code>
   * $objs = $c->findPks(array(12, 56, 832), $con);
   * </code>
   * @param     array $keys Primary keys to use for the query
   * @param     PropelPDO $con an optional connection object
   *
   * @return    PropelObjectCollection|array|mixed the list of results, formatted by the current formatter
   */
  public function findPks($keys, $con = null)
  {  
    $criteria = $this->isKeepQuery() ? clone $this : $this;
    return $this
      ->filterByPrimaryKeys($keys)
      ->find($con);
  }

  /**
   * Filter the query by primary key
   *
   * @param     mixed $key Primary key to use for the query
   *
   * @return    sfCombineQuery The current query, for fluid interface
   */
  public function filterByPrimaryKey($key)
  {
    return $this->addUsingAlias(sfCombinePeer::ASSET_KEY, $key, Criteria::EQUAL);
  }

  /**
   * Filter the query by a list of primary keys
   *
   * @param     array $keys The list of primary key to use for the query
   *
   * @return    sfCombineQuery The current query, for fluid interface
   */
  public function filterByPrimaryKeys($keys)
  {
    return $this->addUsingAlias(sfCombinePeer::ASSET_KEY, $keys, Criteria::IN);
  }

  /**
   * Filter the query on the asset_key column
   * 
   * @param     string $assetKey The value to use as filter.
   *            Accepts wildcards (* and % trigger a LIKE)
   * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
   *
   * @return    sfCombineQuery The current query, for fluid interface
   */
  public function filterByAssetKey($assetKey = null, $comparison = null)
  {
    if (null === $comparison)
    {
      if (is_array($assetKey))
      {
        $comparison = Criteria::IN;
      }
      elseif (preg_match('/[\%\*]/', $assetKey))
      {
        $assetKey = str_replace('*', '%', $assetKey);
        $comparison = Criteria::LIKE;
      }
    }
    return $this->addUsingAlias(sfCombinePeer::ASSET_KEY, $assetKey, $comparison);
  }

  /**
   * Filter the query on the files column
   * 
   * @param     string $files The value to use as filter.
   *            Accepts wildcards (* and % trigger a LIKE)
   * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
   *
   * @return    sfCombineQuery The current query, for fluid interface
   */
  public function filterByFiles($files = null, $comparison = null)
  {
    if (null === $comparison)
    {
      if (is_array($files))
      {
        $comparison = Criteria::IN;
      }
      elseif (preg_match('/[\%\*]/', $files))
      {
        $files = str_replace('*', '%', $files);
        $comparison = Criteria::LIKE;
      }
    }
    return $this->addUsingAlias(sfCombinePeer::FILES, $files, $comparison);
  }

  /**
   * Exclude object from result
   *
   * @param     sfCombine $sfCombine Object to remove from the list of results
   *
   * @return    sfCombineQuery The current query, for fluid interface
   */
  public function prune($sfCombine = null)
  {
    if ($sfCombine)
    {
      $this->addUsingAlias(sfCombinePeer::ASSET_KEY, $sfCombine->getAssetKey(), Criteria::NOT_EQUAL);
    }
    
    return $this;
  }

}
