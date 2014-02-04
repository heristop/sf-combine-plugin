<?php

/**
 * Propel adapter
 *
 */
class PropelAdapter implements OrmAdapterInterface
{
    /**
     * Object query
     *
     * @var null
     */
    private $objectQuery;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->objectQuery = null;
    }

    /**
     * Get object query
     *
     * @return null
     */
    public function getObjectQuery()
    {
        return $this->objectQuery;
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function findByPrimaryKey($id)
    {
        return $this->getObjectQuery()->findPk($id);
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function findAllElements()
    {
        return $this->getObjectQuery()->find();
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function deleteAllElements()
    {
        return $this->getObjectQuery()->deleteAll();
    }

    /**
     * {@inheritdoc}
     *
     * @param $class
     *
     * @return mixed
     */
    public function setClass($class)
    {
        $class = $class . "Query";
        $this->objectQuery = $class::create();
    }
}
