<?php

/**
 * Orm adapter
 *
 */
class OrmAdapter
{
    /**
     * Orm adapter
     *
     * @var OrmAdapterInterface
     */
    private $ormAdapter;

    /**
     * Constructor
     *
     * @param OrmAdapterInterface $ormAdapter
     */
    public function __construct(OrmAdapterInterface $ormAdapter)
    {
        $this->ormAdapter = $ormAdapter;
    }

    /**
     * Get orm adapter
     *
     * @return OrmAdapterInterface
     */
    public function getOrmAdapter()
    {
        return $this->ormAdapter;
    }
    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function findPk($id)
    {
        return $this->getOrmAdapter()->findByPrimaryKey($id);
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function find()
    {
        return $this->getOrmAdapter()->findAllElements();
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed
     */
    public function deleteAll()
    {
        return $this->getOrmAdapter()->deleteAllElements();
    }

    /**
     * {@inheritdoc}
     *
     * @param string $class
     *
     * @return mixed
     */
    public function setClass($class)
    {
        return $this->getOrmAdapter()->setClass($class);
    }
}
