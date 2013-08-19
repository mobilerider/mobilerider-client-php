# Installation

In your `composer.json`, declare a new repository from Github and add the required dependency:

    {
        "repositories": [
            // ...
            {
                "type": "vcs",
                "url": "https://github.com/mobilerider/mobilerider-client-php"
            }
        ],
        "require": {
            // ...
            "mobilerider/mobilerider-client-php": "dev-develop"
        }
    }

Then execute `composer install` to make Composer install the library and its dependencies.


# Configuration

To use the classes provided by this library you just have to initialize them using a `Client` instance. This client is created with your corresponding application ID/Secret pair:

    define('MOBILERIDER_HOST', 'api.devmobilerider.com');
    define('MOBILERIDER_APP_ID', 'your-application-id-goes-here');
    define('MOBILERIDER_APP_SECRET', 'your-application-secret-goes-here');

    $api_client = new Client(MOBILERIDER_HOST, MOBILERIDER_APP_ID, MOBILERIDER_APP_SECRET);

    // So we want get some channels...
    $channel_repository = new ChannelRepository($api_client);
    foreach ($channel_repository->->getAll() as $my_channel) {
        do_something_with($my_channel);
    }
