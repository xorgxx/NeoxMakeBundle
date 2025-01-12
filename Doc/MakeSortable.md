# NeoxTableBundle { Symfony 6 }

This bundle provides a simple and flexible to manage crud render in your application.
Its main goal is to make it simple for you to manage integration "crud" render and to let you configure less common ones with ease.
Be aware that there is no testing code !

[![2024-09-01-17-37-59.png](https://i.postimg.cc/RZVp0b4D/2024-09-01-17-37-59.png)](https://postimg.cc/BXkBYp8T)
[![2024-08-26-21-10-16.png](https://i.postimg.cc/2SccLF58/2024-08-26-21-10-16.png)](https://postimg.cc/nXmY6m76)

## Configuration

No configuration except that you have install stimulus/turbo-ux and setup correctly !!
Base css on Bootstrap 5 so if you have install on your project all css and js from Bs5 going to be applique.

How to use in console ?
``` symfony console neoxmake:sortable:entity ```

```
The class name of the entity to create --> NeoxSortable Entity !!<-- CRUD
 > ->Post
```

that all !! it will generate for you all :

```
project
│   assets
│   bin
│   config
|   ....
└─── twig
|   └─── components
|       └─── yaml
|           └─── PostSortable.yaml

```
to use it in twig :
```twig
   .....
       <div class="container" >
        <twig:Sortable :class="class"/>
    </div>
   .....
```

Generic example :

```yaml
# To use this template you must use this file :
# 1 - in one of your controller :
#        public function sortable(): Response
#        return $this->render('yourBundle/sortable.html.twig', [
#                'class'     => post::class,
#            ]);
# 2 - In twig : yourBundle/sortable.html.twig
#        <twig:Sortable :class="class"/>
#
# OR you can use in twig like this :
#
#   <twig:Sortable :class="class"/>  --- > dont foreget to provide class !!
#
#

iniSortable :
  config  :
    height : 900        # height of sortable table in px
  initial :
    title  :
      label : "post.home.title"
    action :
      label : "post.form.action.label"
  
  table   : # list of headers view
    # key       = name of column in your table very important to have strict same as in entity !!
    # trans     = name of your translation [messages].form.view.label = message are domain use by default to translate
    # format    = optional, for date format use date format like (Y-m-d) or can by use as truncate twig function
    # voter     = optional, if you need to use voter you can use it like this (voter: ["ROLE_ADMIN", "ROLE_OWNER"])
    # thumbtack = optional, if you need to use thumbtack you can use it like this (thumbtack: "true") Publish or unpublish
    
    - { key : "id", trans : "post.form.id.label" }
    - { key : "name", trans : "post.form.name.label" }
    - { key : "content", trans : "post.form.content.label", format : "100, ...", voter : [ "ROLE_ADMIN", "ROLE_OWNER" ] }
    - { key : "slug", trans : "post.form.slug.label", thumbtack : "true" }
    - { key : "creatAt", trans : "post.form.createAt.label", format : "Y-m-d" }
  
  actions : # list of actions to display on top of table header and item have the same structure!!
    header : # array of button you may need on top navbar
      - button : # Optional : can use or not
          label  : "Add"                                   # name of your translation [messages].form.view.label = message are domain use by default to translate
          action : # Optional : custom action !!! you can use any path CRUD or what ever you want
            type: button # form | a | button if you need a button you can use it like this. type: a for <a> tag or type: form for form tag
            pathName : "replace_content"                # name of your route, twig will use it to generate url
            params   : { "id" : "id", "slug" : "slug" }   # it wil loop on item [entity] and key : [post.id]
          # frame : "bar"                                 # it will add on frame in advance mode !!
            attributs :
              #onclick: "Turbo.visit('{{ path(action.button.action.pathName) }}', { frame:'sortable-container' });"
              data-controller : "crud"      # make sure you have install stimulus/turbo-ux
              data-crud-endpoint-value      : "{{ path(action.button.action.pathName) }}"
              data-crud-form-template-value : "{{ path(action.button.action.pathName) }}"
              data-action        : "crud#showCreateForm"
          
          icon   : "fa6-solid:plus"                         # use fontawesome 6 icon https://ux.symfony.com/icons
          color  : "success"                               # color for button
      - button :
          label  : "messages.form.view.label"
          action :
            type: a # form | a | button
            voter    : [ "ROLE_ADMIN", "ROLE_OWNER" ]
            pathName : "replace_content"
            params   : { }
            attributs :
              href   : "{{ path(action.button.action.pathName) }}"
              class  : "btn btn-primary" # Exemple de classe Bootstrap
              target : "_blank" # Peut être modifié selon vos besoins (_blank, etc.)
                      
          icon   : "fa6-solid:eye"
          color  : "warning"
    item   :
      - button :
          label  : "messages.form.view.label"
          action : # custom action !!! you can use any path CRUD or what ever you want
            type: button # form | a | button
            voter    : [ "ROLE_ADMIN", "ROLE_OWNER" ]
            pathName : "replace_content"
            params   : { "id" : "id", "slug" : "slug" }
            frame    : "sortable-container"
          icon   : "fa6-solid:eye"
          color  : "warning"
      - button :
          label  : "messages.form.update.label"
          action :
            type: button # form | a | button
            pathName : "replace_content"
            params   : { }
            frame    : "sortable-container"
          icon   : "fa6-solid:pen-to-square"
          color  : "primary"
      - button :
          label  : "messages.form.delete.label"
          action :
            type: button # form | a | button
            pathName : "replace_content"
            params   : { }
            frame    : "sortable-container"
          icon   : "fa6-solid:trash"
          color  : "danger"
```
Controller :
````php
.....
       #[Route('/sortable-content', name: 'sortable_content', methods: ['GET'])]
        public function getSortableContent(): Response
        {
            return $this->render('neox_make_bundle/sortable.html.twig', [
                'class'     => post::class,
            ]);
        }
        ......
````
Twig :
````twig
    <div class="container" >
        <twig:Sortable :class="class"/>
    </div>
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