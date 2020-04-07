<?php
namespace Mtchabok\ClassAlias;

/**
 * Class ClassAlias
 * @package Mtchabok\ClassAlias
 */
class ClassAlias
{
	/** @var string */
	protected $_name = '';

	/** @var array[] */
	protected $_aliasDetails = [];

	/** @var callable[] */
	protected $_onAdd = [];


	/** @var ClassAlias[] */
	protected static $_instances = [];


	/**
	 * @param string|array $alias
	 * 		<br>alias name string
	 * 		<br>array('alias'=>'alias name', 'className'=>'class name', ...)
	 * 		<br>array( array('alias'=>'alias name', 'className'=>'class name', ...), array('alias'=>'alias name', 'className'=>'class name', ...) )
	 * @param string $className [optional]
	 * @param array $details [optional]
	 * @return ClassAlias
	 */
	public function add(string $alias, string $className = null, array $details = null) :ClassAlias
	{
		$aliases = [];
		if(is_array($alias) && !empty($alias['alias']))
			$aliases = [$alias];
		elseif (is_array($alias) && !empty($alias[0]['alias']))
			$aliases = $alias;
		elseif (is_string($alias) && $alias)
			$aliases = [array_merge(
				is_array($details) ?$details :[]
				, ['alias'=>(string) $alias, 'className'=>(string) $className]
			)];
		unset($alias, $className, $details);
		while ($aliasDetails = array_shift($aliases)){
			foreach ($this->_onAdd as $func){
				if(is_array($result = call_user_func($func, $aliasDetails)) && $result)
					$aliasDetails = $result;
			}
			if(!empty($aliasDetails['alias']))
				$this->_aliasDetails[$aliasDetails['alias']] = $aliasDetails;
		}
		return $this;
	}

	/**
	 * @param string $alias
	 * @return bool
	 */
	public function exist(string $alias)
	{ return isset($this->_aliasDetails[$alias]); }

	/**
	 * @param callable $matchFunc
	 * 		<br>function(array $aliasDetails) :bool {
	 * 			<br>>if( is match $aliasDetails ) return true; else return false;
	 * 		<br>}
	 * @param bool $revers [optional]
	 * @return string[]
	 */
	public function find(callable $matchFunc, bool $revers = false) :array
	{
		$foundAliases = [];
		$aliases = array_keys($this->_aliasDetails);
		while (strlen($alias = $revers ?array_shift($aliases) :array_pop($aliases))){
			if(call_user_func($matchFunc, $this->_aliasDetails[$alias]))
				$foundAliases[] = $alias;
		}
		return $foundAliases;
	}

	/**
	 * @param string $alias
	 * @return array|null
	 */
	public function get(string $alias)
	{ return $this->exist($alias) ?$this->_aliasDetails[$alias] :null; }

	/**
	 * @param string $alias
	 * @return string
	 */
	public function getClassName(string $alias) :string
	{ return !empty($this->_aliasDetails[$alias]['className']) ?(string) $this->_aliasDetails[$alias]['className'] :''; }




	/**
	 * @param callable $func
	 * 		function(array $aliasDetails) :array {
	 * 			add anything to details : $aliasDetails['group'] = '';
	 * 			return $aliasDetails
	 * 		}
	 * @param bool $prepend [optional]
	 * @return ClassAlias
	 */
	public function onAdd(callable $func, $prepend = false) :ClassAlias
	{
		if(!in_array($func, $this->_onAdd)){
			if($prepend) array_unshift($this->_onAdd, $func);
			else array_push($this->_onAdd, $func);
		}
		return $this;
	}





	/**
	 * @param string $name
	 * @return ClassAlias
	 */
	public static function getClassAlias(string $name) :ClassAlias
	{
		if(!isset(self::$_instances[$name]))
			self::$_instances[$name] = new static($name);
		return self::$_instances[$name];
	}







	private function __construct(string $name)
	{ $this->_name = $name; }

	public function __call($name, $arguments)
	{
		if(substr($name,0,3)=='get' && ($detailName = substr($name, 3)) && !empty($arguments[0])){
			$detailName = strtolower(substr($detailName,0,1)) . substr($detailName,1);
			if(($details = $this->get($arguments[0])) && isset($details[$detailName]))
				return $details[$detailName];
		}
		return null;
	}

	public function __isset($name)
	{ return $this->exist($name); }

	public function __get($name)
	{ return $this->get($name); }

	public function __set($name, $value)
	{}

	public function __unset($name)
	{}

	public function __sleep()
	{ return ['_name', '_aliasDetails']; }

	public function __wakeup()
	{}


	public function __toString()
	{ return $this->_name; }

}
