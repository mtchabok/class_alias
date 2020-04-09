<?php
namespace Mtchabok\ClassAlias;

/**
 * Class ClassAliasDetails
 * @package Mtchabok\ClassAlias
 *
 * @property-read string alias
 * @property string className
 * @property string|array link
 */
class ClassAliasDetails implements \ArrayAccess
{
	/** @var mixed[] */
	protected $_details = [];

	/**
	 * @param array|ClassAliasDetails $details
	 * @return $this
	 */
	public function merge($details)
	{
		if(!is_array($details)){
			if($details instanceof ClassAliasDetails)
				$details = $details->toArray();
			else return $this;
		}
		unset($details['alias']);
		foreach ($details as $name=>$value)
			$this->_details[$name] = $value;
		return $this;
	}


	/** @return array */
	public function toArray() :array
	{ return $this->_details; }


	public function __construct(array $details = null)
	{
		if(is_array($details))
			$this->_details = (array) $details;
		if(empty($this->_details['alias']))
			$this->_details['alias'] = uniqid('CAD_');
		if(!isset($this->_details['className']))
			$this->_details['className'] = '';
	}


	public function offsetExists($offset)
	{ return array_key_exists($offset, $this->_details); }

	public function offsetGet($offset)
	{ return array_key_exists($offset, $this->_details) ?$this->_details[$offset] :null; }

	public function offsetSet($offset, $value)
	{ if(!in_array($offset, ['alias'])) $this->_details[$offset] = $value; }

	public function offsetUnset($offset)
	{
		if(in_array($offset, ['link', 'className'])) {
			if (isset($this->_details[$offset])) $this->_details[$offset] = '';
		}elseif(!in_array($offset, ['alias'])) unset($this->_details[$offset]);
	}


	public function __get($name)
	{ return array_key_exists($name, $this->_details) ?$this->_details[$name] :null; }

	public function __set($name, $value)
	{ if(!in_array($name, ['alias'])) $this->_details[$name] = $value; }

	public function __isset($name)
	{ return array_key_exists($name, $this->_details); }

	public function __unset($name)
	{
		if(in_array($name, ['link', 'className'])) {
			if (isset($this->_details[$name])) $this->_details[$name] = '';
		}elseif(!in_array($name, ['alias'])) unset($this->_details[$name]);
	}


	public function __toString()
	{ return (string) $this->_details['alias']; }

}
