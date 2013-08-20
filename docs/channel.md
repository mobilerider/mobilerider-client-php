# Channel objects

Channels are simple groups to group/categorize [media objects](media.md)

## Here's a simple example to create a channel object

Include necessary classes (this depends on autoload configured correctly):

    use Mr\Api\Http\Client;
    use Mr\Api\Model\Channel;
    use Mr\Api\Repository\ChannelRepository;

These are credentials to authenticate within the API:

    define('MOBILERIDER_HOST', 'api.devmobilerider.com');
    define('MOBILERIDER_APP_ID', 'your-application-id-goes-here');
    define('MOBILERIDER_APP_SECRET', 'your-application-secret-goes-here');

Instantiate the client:

    $client = new Client(MOBILERIDER_HOST, MOBILERIDER_APP_ID, MOBILERIDER_APP_SECRET);

Instantiate the repository. This object is used for "group level" operations like object creation, retrieving a (optionally filtered) collection of objects, etc.

    $repository = new ChannelRepository($client);

Now we can use this repository to create a new Channel object:

    $channel = $repository->create(array(
        'name' => 'My Channel'
    ));

But this object is not yet posted to the API server, so let's call `save()` to do so:

    $channel->save();

We can alter allowed fields afterwards, by simply assigning them a value:

    $channel->title = "A new title for my media object";
    $channel->description = "Also, a new description";

But, again, this changes are not yet on the server; you only have to call `save()` when you are done. To delete an object, just call the `delete()` method on the object:

    $channel->delete();

The the object will be erased from the server, but keep in mind the `$channel` variable still holds a reference to the in-memory object so be careful to not reference the contents of this variable any more.


# Documentation TODO

* All available fields for modifications
* Filtering
* Collections
* Pagination
