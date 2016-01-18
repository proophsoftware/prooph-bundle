<?php
/**
 * prooph (http://getprooph.org/)
 *
 * @see       https://github.com/proophsoftware/prooph-bundle for the canonical source repository
 * @copyright Copyright (c) 2016 prooph software GmbH (http://prooph-software.com/)
 * @license   https://github.com/proophsoftware/prooph-bundle/blob/master/LICENSE.md New BSD License
 */
// default example configuration for prooph components, see http://getprooph.org/
$config = [
    'service_bus' => [
        'command_bus' => [
            'router' => [
                'routes' => [
                    \Prooph\Snapshotter\TakeSnapshot::class => \Prooph\Snapshotter\Snapshotter::class,
                    // define your command routes here
                ]
            ]
        ],
        'event_bus' => [
            'plugins' => [
                \Prooph\ServiceBus\Plugin\InvokeStrategy\OnEventStrategy::class,
            ],
            'router' => [
                'routes' => [
                    // define your event routes/projectors here
                ],
            ]
        ]
    ],
    'event_store' => [
        'adapter' => [
            'type' => 'Prooph\\EventStore\\Adapter\\Doctrine\\DoctrineEventStoreAdapter',
            'options' => [
                'connection_alias' => 'doctrine.dbal.default_connection', // Symfony Doctrine Bundle default
            ],
        ],
        'plugins' => [
            \Prooph\EventStoreBusBridge\EventPublisher::class,
            \Prooph\EventStoreBusBridge\TransactionManager::class,
            \Prooph\Snapshotter\SnapshotPlugin::class,
        ],
    ],
    'snapshot_store' => [
        'adapter' => [
            'type' => \Prooph\EventStore\Snapshot\Adapter\Doctrine\DoctrineSnapshotAdapter::class,
            'options' => [
                'connection_alias' => 'doctrine.dbal.default_connection', // Symfony Doctrine Bundle default
                'snapshot_table_map' => [
                ]
            ]
        ]
    ],
    'snapshotter' => [
        'version_step' => 5, //every 5 events a snapshot
        'aggregate_repositories' => [
        ]
    ],
];

if (!isset($container) || !$container instanceof \Symfony\Component\DependencyInjection\ContainerBuilder) {
    return ['prooph' => $config];
}
$container->prependExtensionConfig('prooph', $config);
