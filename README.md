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
use \Mtchabok\ClassAlias\ClassAliasDetails;

$CA = ClassAlias::newClassAlias();
$CA = ClassAlias::newClassAlias('name of class alias object');
$CA = ClassAlias::newClassAlias(['name'=>'name of class alias object']);
```

#### Add Alias ####
```php
$CA->add('alias name', 'class name', ['index other option'=>'value other option', ]);
$CA->add(['alias'=>'alias name', 'className'=>'class name', 'index other option'=>'value other option', ]);
$CA->add(new ClassAliasDetails(['alias'=>'alias name', 'className'=>'class name', 'index other option'=>'value other option', ]));
$CA['alias name'] = ['className'=>'class name', 'index other option'=>'value other option',];
```

#### Get ClassName By Alias ####
```php
$CA->get('alias name')->className;
$CA->getClassName('alias name');
$CA['alias name']->className;
```

#### Get Other Detail By Alias ####
```php
$CA->get('alias name')->otherOption;
$CA->getOtherOption('alias name');
$CA['alias name']->otherOption;
```

#### Find Alias By Class Detail ####
```php
$result = $CA->Find(function(ClassAliasDetails $aliasDetails){
    if(0===strpos($aliasDetails->className, 'Mtchabok'))
        return true;
    else
        return false;
});
// in $result variable => array('alias name', 'alias name', ...);
```


#### For More Usage Documentation, Use This ClassAlias Package By IDE ####
