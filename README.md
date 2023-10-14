# NeoxMakeBundle { Symfony 6 }
This bundle provides additional tools command line in your application.
Its main goal is to make it simple for you to manage integration additional tools!

## Installation BETA VERSION !!
Install the bundle for Composer !! as is still on beta version !!

````
  composer require xorgxx/neox-make-bundle
  or 
  composer require xorgxx/neox-make-bundle:0.*
````

Make sure that is register the bundle in your AppKernel:
```php
Bundles.php
<?php

return [
    .....
    NeoxMake\NeoxMakeBundle\NeoxMakeBundle::class => ['all' => true],
    .....
];
```

**NOTE:** _You may need to use [ symfony composer dump-autoload ] to reload autoloading_

 ..... Done 🎈


## Tools !
* Table-crud [neoxmake:table:crud]( Doc/MakeTable.md )
* ReusableBundle generator [neoxmake:generate:bundle]( Doc/MakeBundle.md )

## Contributing
If you want to contribute \(thank you!\) to this bundle, here are some guidelines:

* Please respect the [Symfony guidelines](http://symfony.com/doc/current/contributing/code/standards.html)
* Test everything! Please add tests cases to the tests/ directory when:
    * You fix a bug that wasn't covered before
    * You add a new feature
    * You see code that works but isn't covered by any tests \(there is a special place in heaven for you\)
## Todo
* Packagist

## Thanks