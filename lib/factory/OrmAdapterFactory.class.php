<?php

/**
 * Orm adapter factory
 *
 */
class OrmAdapterFactory
{
    const ORM_DOCTRINE = 'doctrine';
    const ORM_PROPEL = 'propel';

    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }

    /**
     * Create an adpater
     *
     * @param string $orm
     *
     * @return DoctrineAdapter|PropelAdapter
     */
    public static function create($orm)
    {
        switch ($orm) {
            case self::ORM_DOCTRINE:
                $adapter = new DoctrineAdapter();
                break;
            case self::ORM_PROPEL:
                $adapter = new PropelAdapter();
                break;
            default:
                throw new InvalidArgumentException(sprintf('unavailable orm "%s" (doctrine|propel)', $orm));
        }

        return $adapter;
    }

}
