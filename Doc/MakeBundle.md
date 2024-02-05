# NeoxMakeBundle { Symfony 6 }
This bundle provides a simple and flexible to manage generator reusable bundle in your application.
Be aware that there is no testing code !

[![2023-05-24-12-05-31.png](https://i.postimg.cc/zfLGNQ3r/2023-05-24-12-05-31.png)](https://postimg.cc/rdkkCQSn)
[![2023-05-05-00-08-37.png](https://i.postimg.cc/K8DnLR5z/2023-05-05-00-08-37.png)](https://postimg.cc/FY1dXF85)

## Installation BETA VERSION !!
Install the bundle for Composer !! as is still on beta version !!


## Configuration

No configuration !!

How to use in console ?
``` symfony console neox:bundle:generate or s n:b:g```

```
   Name bundle to create without [bundle] in end --> NeoxReusable !!:
   > *
   
   * rule of naming : (camelCase)
   Xorg         = "Xorg\\XorgBundle\\" : "reusableBundle/XorgBundle/src/",
   neoxXorg     = "neoxXorg\\neoxXorgBundle\\" : "reusableBundle/neoxXorgBundle/src/",
   neoxXorgTest = "neoxXorgTest\\neoxXorgTestBundle\\" : "reusableBundle/neoxXorgTestBundle/src/",
```
Enter bundle name you want to create without [bundle]  in end --> parlonCode not paelonsCodeBundle !!

```
   Do you need to have configuration file ? (yes/no) [no]:
   >
```
Enter yes if you want to create configuration. parlonsCode.yml

that all !! it will generate for you all :
## new command to Release reusable Bundle
```
    symfony console neox:bundle:release or s n:b:r
```

If you want to test reusableBundle
```
    #[Route('/', name: 'seo_home')]
	public function index(TestService $service): Response
    {
        $t = $service->test();  //-> $t = "bundle ok"
        
		return $this->render('home/index.html.twig', [
			'controller_name' => 'HomeController',
		]);
	}
```

project
│   assets
│   bin
│   config
|   ....
└─── Library
│   └─── ParlonsCode
│       └─── src
|           └─── DependencyInjection
|               └─── configuration.php
|               └─── parlonsCodeExtension.php
|           └─── Resources
|               └─── services.xml
|               └─── services.yml
|           └─── parlonsCodeBundle.php
|       └─── composer.json
|       └─── readme.md
|       └─── LICENSE
```
Then you need to setup one line at liste Bundles.php & Composer.json :
```
  .....
|     └─── config
│         └─── bundles.php
│     └─── composer.json
```

````

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