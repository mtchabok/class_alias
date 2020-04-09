<?php
namespace Mtchabok\ClassAlias;

use ArrayAccess;

/**
 * Class ClassAlias
 * @package Mtchabok\ClassAlias
 *
 * @property-read string name
 * @property null|ClassAlias parent
 */
class ClassAlias implements ArrayAccess
{
	/** @var string */
	protected $_name = '';

	/** @var string|ClassAlias */
	protected $_parent = '';

	/** @var ClassAliasDetails[] */
	protected $_aliasDetails = [];



	/** @var ClassAlias[] */
	private static $_instances = [];
	private static $_instancesHash = [];




	/**
	 * @param string|array|ClassAliasDetails|ClassAliasDetails[] $alias
	 * 		<br>alias name string
	 * 		<br>new ClassAliasDetails(array('alias'=>'alias name', 'className'=>'class name', ...))
	 * 		<br>array('alias'=>'alias name', 'className'=>'class name', ...)
	 * 		<br>array( array('alias'=>'alias name', 'className'=>'class name', ...), array('alias'=>'alias name', 'className'=>'class name', ...) )
	 * @param string $className [optional]
	 * @param array $details [optional]
	 * @param array $addOptions [optional]
	 * @return ClassAlias
	 */
	public function add($alias, string $className = null, array $details = null, array $addOptions = null) :ClassAlias
	{
		$addOptions = array_merge(['add'=>true, 'update'=>true, 'merge'=>true], is_array($addOptions) ?$addOptions :[]);
		$aliases = [];
		if(is_array($alias)) {
			$aliases = !empty($alias['alias']) ? [$alias] : $alias;
		}elseif ($alias instanceof ClassAliasDetails)
			$aliases = [$alias];
		elseif (is_string($alias))
			$aliases = [array_merge(
				is_array($details) ?$details :[]
				, ['alias'=>(string) $alias, 'className'=>(string) $className]
			)];
		unset($alias, $className, $details);
		if(!$ClassAliasDetailsCN = $this->getClassName('MtchabokClassAliasDetails')) {
			if (''===$this->_name || !$ClassAliasDetailsCN = static::getClassAlias()->getClassName('MtchabokClassAliasDetails'))
				$ClassAliasDetailsCN = ClassAliasDetails::class;
		}
		while ($aliasDetails = array_shift($aliases)){
			if((is_array($aliasDetails) || $aliasDetails instanceof ClassAliasDetails) && !empty($aliasDetails['alias'])){
				$exist = array_key_exists($aliasDetails['alias'], $this->_aliasDetails);
				if($exist && $addOptions['update']){
					if($addOptions['merge'])
						$this->_aliasDetails[$aliasDetails['alias']]->merge($aliasDetails);
					else
						$this->_aliasDetails[$aliasDetails['alias']] = $aliasDetails instanceOf ClassAliasDetails
							?$aliasDetails
							:new $ClassAliasDetailsCN($aliasDetails);
				}elseif (!$exist && $addOptions['add']){
					$this->_aliasDetails[$aliasDetails['alias']] = $aliasDetails instanceOf ClassAliasDetails
						?$aliasDetails
						:new $ClassAliasDetailsCN($aliasDetails);
				}
			}
		}
		return $this;
	}

	/**
	 * @param string|array|ClassAliasDetails|ClassAliasDetails[] $alias
	 * 		<br>alias name string
	 * 		<br>new ClassAliasDetails(array('alias'=>'alias name', 'className'=>'class name', ...))
	 * 		<br>array('alias'=>'alias name', 'className'=>'class name', ...)
	 * 		<br>array( array('alias'=>'alias name', 'className'=>'class name', ...), array('alias'=>'alias name', 'className'=>'class name', ...) )
	 * @param string $className [optional]
	 * @param array $details [optional]
	 * @return ClassAlias
	 */
	public function addOnNotExist($alias, string $className = null, array $details = null)
	{ return $this->add($alias, $className, $details, ['add'=>true, 'update'=>false]); }

	/**
	 * @param string $alias
	 * @param bool $localOnly [optional]
	 * @return bool
	 */
	public function exist(string $alias, bool $localOnly = null)
	{ return isset($this->_aliasDetails[$alias]) || ( !$localOnly && ($CA=$this->parent) && $CA->exist($alias)); }

	/**
	 * @param callable $matchFunc
	 * 		<br>function(ClassAliasDetails $aliasDetails) :bool {
	 * 			<br>>if( is match $aliasDetails ) return true; else return false;
	 * 		<br>}
	 * @param bool $revers [optional]
	 * @param bool $localOnly [optional]
	 * @return string[]
	 */
	public function find(callable $matchFunc, bool $revers = null, $localOnly = null) :array
	{
		$foundAliases = [];
		$aliases = array_keys($this->_aliasDetails);
		while (strlen($alias = $revers ?array_shift($aliases) :array_pop($aliases))){
			if(call_user_func($matchFunc, $this->_aliasDetails[$alias]))
				$foundAliases[] = $alias;
		}
		if(!$localOnly && ($CA = $this->parent))
			$foundAliases+= $CA->find($matchFunc, $revers);
		return $foundAliases;
	}

	/**
	 * @param string $alias
	 * @param bool $localOnly
	 * @return ClassAliasDetails|null
	 */
	public function get(string $alias, bool $localOnly = null)
	{
		$details = null;
		if(isset($this->_aliasDetails[$alias]))
			$details = $this->_aliasDetails[$alias];
		elseif (!$localOnly && $CA = $this->parent)
			$details = $CA->get($alias);
		if(!empty($details->link)){
			if(is_string($details->link) && $alias!=$details->link && $this->exist($details->link))
				$details = $this->get($details->link);
			elseif (is_array($details->link) && !empty($details->link[1]) && $CA = static::getClassAlias((string) $details->link[0])){
				$details = $CA->get((string) $details->link[1]);
			}
		}
		return $details;
	}

	/**
	 * @param string $alias
	 * @param string $default [optional]
	 * @param bool $localOnly [optional]
	 * @return string
	 */
	public function getClassName(string $alias, string $default = null, bool $localOnly = null) :string
	{
		$detail = $this->get($alias, $localOnly);
		return !empty($detail['className']) ?(string) $detail['className'] :(string) $default;
	}



















	/**
	 * @param string $name
	 * @return bool
	 */
	public static function existClassAlias(string $name) :bool
	{ return empty($name) || isset(self::$_instances[(string) $name]); }

	/**
	 * @param string $name
	 * @return ClassAlias|null
	 */
	public static function getClassAlias(string $name = null)
	{
		if(empty($name) && !isset(static::$_instances[''])){
			$CA = new static();
			$CA->_name = '';
			$CA->_parent = null;
			self::$_instances[''] = $CA;
			self::$_instancesHash[''] = spl_object_hash($CA);
			$CA->add([
				['alias'=>'MtchabokClassAlias', 'className'=>ClassAlias::class],
				['alias'=>'MtchabokClassAliasDetails', 'className'=>ClassAliasDetails::class],
			]);
		}
		return array_key_exists((string)$name, self::$_instances) ?self::$_instances[(string)$name] :null;
	}

	/**
	 * @param ClassAlias $classAlias
	 */
	public static function addClassAlias(ClassAlias $classAlias)
	{
		if(!in_array(spl_object_hash($classAlias), self::$_instancesHash)) {
			if (empty($classAlias->_name) || !is_string($classAlias->_name))
				$classAlias->_name = uniqid('CLASS_ALIAS_');
			if (assert(!isset(self::$_instances[$classAlias->_name]))) {
				self::$_instances[$classAlias->_name] = $classAlias;
				self::$_instancesHash[$classAlias->_name] = spl_object_hash($classAlias);
			}
		}
	}

	/**
	 * @param array|string $properties [optional]
	 * @return ClassAlias|null
	 */
	public static function newClassAlias($properties = null)
	{
		if(!is_array($properties)) $properties = ['name'=>(string) $properties];
		else $properties+=['name'=>null];

		$className = static::class;
		/** @var ClassAlias $obj */
		if(!assert(($obj = new $className()) instanceof ClassAlias))
			return null;

		$obj->_name = !empty($properties['name']) && is_string($properties['name']) ?$properties['name'] :uniqid('CLASS_ALIAS_');

		if(isset($properties['parent']))
			$obj->parent = $properties['parent'];

		return $obj;
	}









	final private function __construct()
	{}

	public function offsetExists($offset)
	{ return $this->exist($offset); }

	public function offsetGet($offset)
	{ return $this->get($offset); }

	public function offsetSet($offset, $value)
	{
		if(is_array($value))
			$this->add(array_merge($value, ['alias'=>$offset]));
		elseif (is_string($value))
			$this->add($offset, $value);
	}

	public function offsetUnset($offset)
	{}

	public function __call($name, $arguments)
	{
		if(substr($name,0,3)=='get' && ($detailName = substr($name, 3)) && !empty($arguments[0])){
			$detailName = strtolower(substr($detailName,0,1)) . substr($detailName,1);
			if(($details = $this->get($arguments[0], empty($arguments[2]) ?null :(bool) $arguments[2])))
				return isset($details[$detailName]) ?$details[$detailName] :(isset($arguments[1]) ?$arguments[1] :null);
		}
		return null;
	}

	public function __get($name)
	{
		switch ($name){
			case 'name': return $this->_name; break;
			case 'parent':
				if(is_string($this->_parent) && $this->_parent!=$this->_name)
					return static::getClassAlias($this->_parent);
				elseif ($this->_parent instanceof ClassAlias)
					return $this->_parent;
				else
					return null;
				break;
		}
		return null;
	}

	public function __set($name, $value)
	{
		switch ($name){
			case 'parent':
				if (!empty($this->_name)){
					if(false===$value || is_null($value))
						$this->_parent = null;
					elseif ($value instanceof ClassAlias && $this->_name!=$value->_name)
						$this->_parent = static::existClassAlias($value) ?$value->_name :$value;
					elseif (is_string($value) && $this->_name!=$value && static::existClassAlias($value))
						$this->_parent = $value;
				}
				break;
		}
	}

	public function __isset($name)
	{
		switch ($name){
			case 'name': return ''===$this->_name || !empty($this->_name); break;
			case 'parent': return ''===$this->_parent || !empty($this->_parent); break;
		} return false;
	}

	public function __unset($name)
	{}


	final public function __toString()
	{ return $this->_name; }

}
