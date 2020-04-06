# PHP Class Alias Control
PHP Class Alias Manager.

Installation
------------
This package is listed on [Packagist](https://packagist.org/packages/mtchabok/class_alias).
```
composer require mtchabok/class_alias
```

How To Usage
------------

#### Create ClassAlias Object ####
```php
use \Mtchabok\ClassAlias\ClassAlias;

$CA = ClassAlias::getClassAlias('myCA');
```

#### Add Alias ####
```php
$CA->add('aliasName', 'class name');
$CA->add(array('alias'=>'aliasName', 'className'=>'class name'));

$CA->add('aliasName', 'class name', array('other detail index'=>'other detail value', ...));
$CA->add(array('alias'=>'aliasName', 'className'=>'class name', 'other detail index'=>'other detail value', ...));
```

#### Get ClassName By Alias ####
```php
$CA->get('aliasName')['className'];
$CA->getClassName('aliasName');
$CA->aliasName['className'];
```

#### Get Other Detail By Alias ####
```php
$CA->get('aliasName')['other detail'];
$CA->{'get' . 'other detail'}('aliasName');
$CA->aliasName['other detail'];
```

#### Find Alias By Class Detail ####
```php
$result = $CA->Find(function($aliasDetails){
    if(0===strpos($aliasDetails['className'], 'Mtchabok'))
        return true;
    else
        return false;
});
// in $result variable => array('alias name', 'alias name', ...);
```

#### OnAdd Event ####
```php
$CA->onAdd(function(array $aliasDetails){
    if(0===strpos($aliasDetails['alias], 'my'))
        $aliasDetails['group'] = 'my';
    else
        $aliasDetails['group'] = '';
    return $aliasDetails;
});
```

#### For More Usage Documentation, Use This ClassAlias Package By IDE ####