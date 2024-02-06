# NeoxMakeBundle { Symfony 6 }
This bundle provides additional tools command line in your application.
Its main goal is to make it simple for you to manage integration additional tools!

## Installation BETA VERSION !!
Install the bundle for Composer !! as is still on beta version !!

````
  composer require xorgxx/neox-make-bundle --dev
  or 
  composer require xorgxx/neox-make-bundle:0.* --dev
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
* Table-crud [ Command ]( Doc/MakeTable.md )
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