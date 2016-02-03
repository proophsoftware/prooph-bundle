# Symfony bundle for prooph components

## Overview
This is a Symfony bundle for prooph components to get started out of the box with message bus, CQRS, event sourcing and 
snapshots with the Symfony Doctrine Bundle. It uses Doctrine DBAL. There are more adapters available.

It provides all [service definitions and a default configuration](src/Resources/config "Symfony Bundle Resources"). 
This is more like a Quick-Start bundle. If you want to use the prooph components in production, we recommend to use only
[prooph-interop-bundle](https://github.com/proophsoftware/prooph-interop-bundle) and configure the prooph components for
your requirements. See the [documentation](http://getprooph.org/) for more details of the prooph components.

For rapid prototyping we recommend to use our 
[prooph-cli](https://github.com/proophsoftware/prooph-cli "prooph command line interface") tool.

### Available services
* `prooph.service_bus.command_bus`: Dispatches commands
* `prooph.service_bus.event_bus`: Dispatches events
* `prooph.event_bus.transaction_manager`: Transaction manager for service bus and event store
* `prooph.event_bus.event_publisher`: Publishes events on the event bus
* `prooph.event_store.doctrine_adapter`: Doctrine adapter for event store
* `prooph.event_store.snapshot_store`: Event store snapshot adapter
* `prooph.event_store.doctrine_snapshot_adapter`: Doctrine snapshot adapter

## Installation
You can install `proophsoftware/prooph-bundle` via composer by adding `"proophsoftware/prooph-bundle": "^0.1"` as 
requirement to your composer.json. 

### Database 
Setup your Doctrine database [migrations](https://github.com/prooph/event-store-doctrine-adapter#database-set-up)
for the Event Store and Snapshot with:

```bash
$ php bin/console doctrine:migrations:generate
```

Update the generated migration class with prooph Doctrine event store schema helper:

```php
class Version20160202155238 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        \Prooph\EventStore\Adapter\Doctrine\Schema\EventStoreSchema::createSingleStream($schema, 'event_stream', true);
    }

    public function down(Schema $schema)
    {
        \Prooph\EventStore\Adapter\Doctrine\Schema\EventStoreSchema::dropStream($schema, 'event_stream');
    }
}
```

And now for the snapshot table.

```bash
$ php bin/console doctrine:migrations:generate
```

Update the generated migration class with prooph Doctrine snapshot schema helper:

```php
class Version20160202160810 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        \Prooph\EventStore\Snapshot\Adapter\Doctrine\Schema\SnapshotStoreSchema::create($schema, 'snapshot');
    }

    public function down(Schema $schema)
    {
        \Prooph\EventStore\Snapshot\Adapter\Doctrine\Schema\SnapshotStoreSchema::drop($schema, 'snapshot');
    }
}
```

Now it's time to execute the migrations:

```bash
$ php bin/console doctrine:migrations:migrate
```

## Example
You have only to define your models (Entities, Repositories) and commands/routes. You find all these things in the 
[prooph components documentation](http://getprooph.org/ "prooph components documentation"). Here is an example YAML config
from the [proophessor-do example app](https://github.com/prooph/proophessor-do "prooph components in action").

> You have to use a single quote `'` in the YAML configuration

Define the aggregate repository, command route and event route for `RegisterUser` in `app/config/config.yml`.

```yml
prooph:
  service_bus:
    command_bus:
      router:
        routes:
          # list of commands with corresponding command handler
          'Prooph\ProophessorDo\Model\User\Command\RegisterUser': 'Prooph\ProophessorDo\Model\User\Handler\RegisterUserHandler'
    event_bus:
      router:
        routes:
          # list of events with a list of projectors
          'Prooph\ProophessorDo\Model\User\Event\UserWasRegistered':
            - 'Prooph\ProophessorDo\Projection\User\UserProjector'
  event_store:
    # list of aggregate repositories
    user_collection:
      repository_class: 'Prooph\ProophessorDo\Infrastructure\Repository\EventStoreUserCollection'
      aggregate_type: 'Prooph\ProophessorDo\Model\User\User'
      aggregate_translator: 'Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator'
      snapshot_store: 'Prooph\EventStore\Snapshot\SnapshotStore'
```

Add the service container factories. Here is an example of the corresponding service XML configuration with 
container-interop for the example above.

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
    <services>

        <service id="Prooph\ProophessorDo\Model\User\Handler\RegisterUserHandler.factory"
                 class="Prooph\ProophessorDo\Container\Model\User\RegisterUserHandlerFactory"/>
        <service id="Prooph\ProophessorDo\Model\User\Handler\RegisterUserHandler" class="Prooph\ProophessorDo\Model\User\Handler\RegisterUserHandler">
            <factory service="Prooph\ProophessorDo\Model\User\Handler\RegisterUserHandler.factory" method="__invoke"/>
            <argument type="service" id="interop_container"/>
        </service>

        <service id="Prooph\ProophessorDo\Model\User\UserCollection.factory"
                 class="Prooph\ProophessorDo\Container\Infrastructure\Repository\EventStoreUserCollectionFactory"/>
        <service id="Prooph\ProophessorDo\Model\User\UserCollection" class="Prooph\ProophessorDo\Model\User\UserCollection">
            <factory service="Prooph\ProophessorDo\Model\User\UserCollection.factory" method="__invoke"/>
            <argument type="service" id="interop_container"/>
        </service>

        <service id="Prooph\ProophessorDo\Projection\User\UserProjector.factory"
                 class="Prooph\ProophessorDo\Container\Projection\User\UserProjectorFactory"/>
        <service id="Prooph\ProophessorDo\Projection\User\UserProjector" class="Prooph\ProophessorDo\Projection\User\UserProjector">
            <factory service="Prooph\ProophessorDo\Projection\User\UserProjector.factory" method="__invoke"/>
            <argument type="service" id="interop_container"/>
        </service>

        <service id="Prooph\ProophessorDo\Projection\User\UserFinder.factory"
                 class="Prooph\ProophessorDo\Container\Projection\User\UserFinderFactory"/>
        <service id="Prooph\ProophessorDo\Projection\User\UserFinder" class="Prooph\ProophessorDo\Projection\User\UserFinder">
            <factory service="Prooph\ProophessorDo\Projection\User\UserFinder.factory" method="__invoke"/>
            <argument type="service" id="interop_container"/>
        </service>

    </services>
</container>
```

Here is an example how to call the `RegisterUser` command:

```php
    /* @var $container \Symfony\Component\DependencyInjection\ContainerBuilder */
    
    /* @var $commandBus \Prooph\ServiceBus\CommandBus */
    $commandBus = $container->get('prooph.service_bus.command_bus');

    $command = new \Prooph\ProophessorDo\Model\User\Command\RegisterUser(
        [
            'user_id' => \Rhumsaa\Uuid\Uuid::uuid4()->toString(),
            'name' => 'prooph',
            'email' => 'my@domain.com',
        ]
    );

    $commandBus->dispatch($command);
```

Here is an example how to get a list of all users from the example above:

```php
    /* @var $container \Symfony\Component\DependencyInjection\ContainerBuilder */
    $userFinder = $container->get('Prooph\ProophessorDo\Projection\User\UserFinder');

    $users = $userFinder->findAll();
```

## Support

- Ask questions on [prooph-users](https://groups.google.com/forum/?hl=de#!forum/prooph) mailing list.
- File issues at [https://github.com/proophsoftware/prooph-bundle/issues](https://github.com/proophsoftware/prooph-bundle/issues).
- Say hello in the [prooph gitter](https://gitter.im/prooph/improoph) chat.

## Contribute

Please feel free to fork and extend existing or add new plugins and send a pull request with your changes!
To establish a consistent code quality, please provide unit tests for all your changes and may adapt the documentation.

## License

Released under the [New BSD License](LICENSE.md).
