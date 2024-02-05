# This file is the entry point to configure your own services.
# Files in the packages/ subdirectory configure your dependencies.

# Put parameters here that don't need to change on each machine where the app is deployed
# https://symfony.com/doc/current/best_practices.html#use-parameters-for-application-configuration
parameters:


services:
    # default configuration for services in *this* file
    _defaults:
        autowire: true      # Automatically injects dependencies in your services.
        autoconfigure: true # Automatically registers your services as commands, event subscribers, etc.

    # makes classes in src/ available to be used as services
    # this creates a service per class whose id is the fully-qualified class name
    Twig\Extension\StringLoaderExtension:
    
    <?= $name_space ?>\:
        resource: '../../../src/'
        exclude:
            - '../../../src/DependencyInjection/'
            - '../../../src/Entity/'
            - '../../../src/Kernel.php'

    # add more service definitions when explicit configuration is needed
    # please note that last definitions always *replace* previous ones

    <?= $name_space ?>\Services\TestService:
        