<?php

/**
 * PHP IDS
 * 
 * Requirements: PHP5, SimpleXML, MultiByte Extension (optional)
 *
 * Copyright (c) 2007 PHPIDS (http://phpids.org)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the license.
 *
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * IDS event object
 *
 * This class represents a certain event which has been occured while applying
 * the filters to the given data. It aggregates a bunch of IDS_Filter_Abstract
 * implementations and is a assembled in IDS_Report.
 *
 * @author	Lars Strojny <lstrojny@neu.de>
 */
class IDS_Event implements Countable, IteratorAggregate {
	
	/**
	 * Event name
	 *
	 * @var scalar
	 */
	protected $_name	= null;

	/**
	 * Value the filter has been applied
	 *
	 * @var scalar
	 */
	protected $_value   = null;

	/**
	 * List of filters
	 *
	 * @var array
	 */
	protected $_filters = array();

	/**
	 * Computed impact
	 *
	 * @var integer|bool
	 */
	protected $_impact  = false;

	/**
	 * Assembled tags
	 *
	 * @var array
	 */
	protected $_tags	= false;

	/**
	 * Generate a new IDS event
	 *
	 * You need to pass the event name (most of the time the name of the key in the
	 * array you have filtered), the value the filters have been applied on and a
	 * list of filters.
	 *
	 * @param scalar $name
	 * @param scalar $value
	 * @param value $filters
	 */
	public function __construct($name, $value, array $filters) {
		if (!is_scalar($name)) {
			throw new InvalidArgumentException('Expected $name to be a scalar, '
				. gettype($name) . ' given');
		}
		$this->_name = $name;

		if (!is_scalar($value)) {
			throw new InvalidArgumentException('Expected $value to be a scalar, '
				. gettype($value) . ' given');
		}
		$this->_value = $value;

		foreach ($filters as $filter) {
			if (!$filter instanceof IDS_Filter_Abstract) {
				throw new InvalidArgumentException(
					'Filter must be derived from IDS_Filter_Abstract');
			}
			$this->_filters[] = $filter;
		}
	}

	/**
	 * Get event name
	 *
	 * Returns the name of the event (most of the time the name of the filtered
	 * key of the array)
	 *
	 * @return scalar
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * Get event value
	 *
	 * Returns the value which has been passed to the filters
	 *
	 * @return scalar
	 */
	public function getValue() {
		return $this->_value;
	}

	/**
	 * Get computed impact
	 *
	 * Returns the overal impact of all filters
	 *
	 * @return integer
	 */
	public function getImpact() {
		
		// Impact is calculated?
		if (!$this->_impact) {
			$this->_impact = 0;
			foreach ($this->_filters as $filter) {
				$this->_impact += $filter->getImpact();
			}
		}
		
		return $this->_impact;
	}

	/**
	 * Get assembled tags
	 *
	 * Collects all the tags of the filters
	 *
	 * @return array
	 */
	public function getTags() {
		if (!$this->_tags) {
			$this->_tags = array();
			foreach ($this->getFilters() as $filter) {
				$this->_tags = array_merge(
					$this->_tags, 
					$filter->getTags()
				);
			}
			
			$this->_tags = array_values(
				array_unique($this->_tags)
			);
		}
		
		return $this->_tags;
	}

	/**
	 * Get list of filters
	 *
	 * @return array
	 */
	public function getFilters() {
		return $this->_filters;
	}

	/**
	 * Get number of filters
	 *
	 * To implement interface Countable this returns the number of filters
	 * appended.
	 *
	 * @return integer
	 */
	public function count() {
		return count($this->getFilters());
	}

	/**
	 * IteratorAggregate iterator getter
	 *
	 * Returns a iterator to iterate over the appended filters.
	 *
	 * @return Iterator|IteratorAggregate
	 */
	public function getIterator() {
		return new ArrayObject($this->getFilters());
	}
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */
