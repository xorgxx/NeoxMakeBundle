# NeoxMakeBundle { Symfony 6 }
This bundle provides additional tool command line in your application.
Its main goal is to make it simple for you to manage integration with additional tools!

[![2024-09-01-17-37-59.png](https://i.postimg.cc/RZVp0b4D/2024-09-01-17-37-59.png)](https://postimg.cc/BXkBYp8T)
[![2024-08-25-17-45-03.png](https://i.postimg.cc/HnGbSH19/2024-08-25-17-45-03.png)](https://postimg.cc/cgmKHpHv)
[![2024-09-04-16-46-56.png](https://i.postimg.cc/y8w63Nmj/2024-09-04-16-46-56.png)](https://postimg.cc/rDN2vMYR)

## Installation Release !!
Install the bundle for Composer

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
* Table-crud [ Tuto ]( Doc/MakeTable.md )
* Sortable-entity [ Tuto ]( Doc/MakeSortable.md )
* ReusableBundle generator [ Command ]( Doc/MakeBundle.md )
* Configuration [GitHub]( Doc/GitHubRelease.md )
* Configuration [Packagist]( Doc/PackagistRelease.md )
* Tutorial Official
  * [Symfony Reusable Bundles]( https://symfony.com/doc/current/bundles/best_practices.html )
  * [Symfonycasts Creating a Reusable]( https://symfonycasts.com/screencast/symfony-bundle )
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