<?php

/**
 * Orm adapter factory
 *
 */
interface OrmAdapterInterface
{
    /**
     * Get one element by primary key
     *
     * @return mixed
     */
    public function findByPrimaryKey($id);

    /**
     * Get all element in the table
     *
     * @return mixed
     */
    public function findAllElements();

    /**
     * Delete all elements in the table
     *
     * @return mixed
     */
    public function deleteAllElements();

    /**
     * Set query class
     *
     * @param $class
     *
     * @return mixed
     */
    public function setClass($class);
}
