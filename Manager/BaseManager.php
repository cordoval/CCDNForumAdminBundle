<?php

/*
 * This file is part of the CCDNForum AdminBundle
 *
 * (c) CCDN (c) CodeConsortium <http://www.codeconsortium.com/>
 *
 * Available on github <http://www.github.com/codeconsortium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CCDNForum\AdminBundle\Manager;

/**
 *
 * @author Reece Fowell <reece@codeconsortium.com>
 * @version 1.0
 */
class BaseManager
{
	
	/**
	 *
	 * @access protected
	 */
    protected $doctrine;

	/**
	 *
	 * @access protected
	 */
    protected $em;

	/**
	 *
	 * @access protected
	 */
    protected $container;

	/**
	 *
	 * @access public
	 * @param $doctrine
	 */
    public function __construct($doctrine, $container)
    {
        $this->doctrine = $doctrine;

        $this->em = $doctrine->getEntityManager();

		$this->container = $container;
    }

	/**
	 *
	 * @access public
	 * @param $entity
	 * @return $this
	 */
    public function persist($entity)
    {
        $this->em->persist($entity);

        return $this;
    }

	/**
	 *
	 * @access public
	 * @param $entity
	 * @return $this
	 */
    public function remove($entity)
    {
        $this->em->remove($entity);

        return $this;
    }

	/**
	 *
	 * @access public
	 * @return $this
	 */
    public function flush()
    {
        $this->em->flush();

        return $this;
    }

	/**
	 *
	 * @access public
	 * @param $entity
	 * @return $this
	 */
    public function refresh($entity)
    {
        $this->em->refresh($entity);

        return $this;
    }

    /**
     *
     * @access public
     * @param $entity
     * @return $this
     */
    public function update($entity)
    {
        // update the record
        $this->persist($entity);

        return $this;
    }

}
