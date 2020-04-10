<?php
namespace Mtchabok\ClassAlias;

/**
 * Class ClassAliasDetails
 * @package Mtchabok\ClassAlias
 *
 * @property-read 	string 				alias
 * @property 		string 				className
 * @property 		string|array 		link
 *
 * @method 			string 				getAlias()
 *
 * @method 			string 				getClassName( string $default = null )
 * @method 			ClassAliasDetails 	setClassName( string $className )
 *
 * @method 			bool 				existLink()
 * @method 			string 				getLink( string|array $default = null )
 * @method 			ClassAliasDetails 	setLink( string|array $link )
 * @method 			ClassAliasDetails 	deleteLink()
 */
class ClassAliasDetails implements \ArrayAccess
{
	/** @var mixed[] */
	protected $_details = [];





	/**
	 * @param string $index
	 * @return bool
	 */
	public function exist(string $index) :bool
	{ return array_key_exists($index, $this->_details); }

	/**
	 * @param string $index
	 * @param mixed $default
	 * @return mixed
	 */
	public function get(string $index, $default = null)
	{ return array_key_exists($index, $this->_details) ?$this->_details[$index] :$default; }

	/**
	 * @param string $index
	 * @param mixed $value
	 * @return $this
	 */
	public function set(string $index, $value)
	{ if(!in_array($index, ['alias'])) $this->_details[$index] = $value; return $this; }

	/**
	 * @param string $index
	 * @return $this
	 */
	public function delete(string $index)
	{
		if(in_array($index, ['link', 'className'])) {
			if (array_key_exists($index, $this->_details)) $this->_details[$index] = '';
		}elseif(!in_array($index, ['alias'])) unset($this->_details[$index]);
		return $this;
	}




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

	public function __call($name, $arguments)
	{
		if(preg_match('#^(get|set|exist|delete)(.*)$#', $name, $parsed)){
			$index = strtolower(substr($parsed[2], 0, 1)) . substr($parsed[2], 1);
			switch ($parsed[1]){
				case 'exist': return $this->exist($index); break;
				case 'get': return $this->get($index, isset($arguments[0]) ?$arguments[0] :null); break;
				case 'set': return $this->set($index, $arguments[0]); break;
				case 'delete': return $this->delete($index);
			}
		}
		return null;
	}


	public function offsetExists($offset)
	{ return $this->exist($offset); }

	public function offsetGet($offset)
	{ return $this->get($offset); }

	public function offsetSet($offset, $value)
	{ $this->set($offset, $value); }

	public function offsetUnset($offset)
	{ $this->delete($offset); }


	public function __isset($name)
	{ return $this->exist($name); }

	public function __get($name)
	{ return $this->get($name); }

	public function __set($name, $value)
	{ $this->set($name, $value); }

	public function __unset($name)
	{ $this->delete($name); }


	public function __toString()
	{ return (string) $this->_details['alias']; }

}
