eos/com-view-server-bundle
==========================

Symfony integration for [`eos/com-view-server`](https://github.com/eosnewmedia/php-com-view-server)

# Installation

```bash
composer require eos/com-view-server-bundle
```

# Configuration
Create the file `config/packages/eos_com_view_server.yaml` with content like this:

```yaml
eos_com_view_server:
    allow_origin: '*' # cors control; * is the default value
    schema: # the definition which will be available as /cv/schema.json
        views:
            exampleView:
                description: 'Example view'
                data:
                    success: 'exampleSchema'
        commands:
            exampleCommands:
                description: 'Example command'
        schemas:
            exampleSchema:
                description: 'Example schema'
                properties:
                    example:
                        description: 'Example property'
                        type: 'string'
```

Configure your `config/routes.yaml` with:

```yaml
eos_com_view:
    resource: '@EosComViewServerBundle/Resources/config/routes.xml'
```

## Commands
Commands (classes which implement `Eos\ComView\Server\Command\CommandProcessorInterface`) can be registered via the
service tag `com_view.command_processor` in your `config/services.yaml`:

```yaml
services:
    # with "command", if class name is not equal to command name 
    YourCommand\CommandProcessor:
        tags:
            - { name: 'com_view.command_processor', command: 'executeYourCommand' } 

    # without "command", if class name is equal to command name (in this example the command name must be "executeSecondCommand")
    YourCommand\ExecuteSecondCommand:
        tags:
            - { name: 'com_view.command_processor' }
```

## Views
Views (classes which implement `Eos\ComView\Server\View\ViewInterface`) can be registered via the
service tag `com_view.view` in your `config/services.yaml`:

```yaml
services:
    # with "view", if class name is not equal to view name 
    YourView\TestView:
        tags:
            - { name: 'com_view.view', view: 'showTest' } 

    # without "view", if class name is equal to view name (in this example the view name must be "showExample")
    YourView\ShowExample:
        tags:
            - { name: 'com_view.view' }
```

## Health Providers
Health Providers (classes which implement `Eos\ComView\Server\Health\ViewHealthProviderInterface` or `Eos\ComView\Server\Health\CommandHealthProviderInterface`) 
can be registered via the service tag `com_view.health_provider` in your `config/services.yaml`:

```yaml
services:
    YourHealthProvider\TestProvider:
        tags:
            - { name: 'com_view.health_provider' } 
```
