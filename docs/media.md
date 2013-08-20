# Media objects

### Here's a simple example to create a live media object

Include necessary classes (this depends on autoload configured correctly):

    use Mr\Api\Http\Client;
    use Mr\Api\Model\Media;
    use Mr\Api\Repository\MediaRepository;

These are credentials to authenticate within the API:

    define('MOBILERIDER_HOST', 'api.devmobilerider.com');
    define('MOBILERIDER_APP_ID', 'your-application-id-goes-here');
    define('MOBILERIDER_APP_SECRET', 'your-application-secret-goes-here');

Instantiate the client:

    $client = new Client(MOBILERIDER_HOST, MOBILERIDER_APP_ID, MOBILERIDER_APP_SECRET);

Instantiate the repository. This object is used for "group level" operations like object creation, retrieving a (optionally filtered) collection of objects, etc.

    $repository = new MediaRepository($client);

Now we can use this repository to create a new Live Media object:

    $media = $repository->create(array(
        'title' => 'Live Media Creation Test',
        'type' => Media::TYPE_LIVE,
        'description' => 'Test live media from client',
        'DescriptionSmall' => 'tag1, tag2',
        'encoderPrimaryIp' => '127.0.0.1',
        'encoderBackupIp' => '127.0.0.1',
        'encoderPassword' => 'test',
        'bitrates' => array(696, 1096, 2096)
    ));

But this object is not yet posted to the API server, so let's call `save()` to do so:

    $media->save();

We can alter allowed fields afterwards, by simply assigning them a value:

    $media->title = "A new title for my media object";
    $media->description = "Also, a new description";

But, again, this changes are not yet on the server; you only have to call `save()` when you are done. To delete an object, just call the `delete()` method on the object:

    $media->delete();

The the object will be erased from the server, but keep in mind the `$media` variable still holds a reference to the in-memory object so be careful to not reference the contents of this variable any more.


### Live Media objects

When an media object is created the server returns additional fields (and some of them are read-only). In the case of live media objects, there are some important fields to take into account, like those under the `stream` attribute (specially `encoderPrimaryIp`, `encoderBackupIp`, `encoderUsername` and `encoderPassword`). Using the above example:

    var_dump($media->stream);
    >>> {
    >>>   $id => "23423"
    >>>   $name =>"78986"
    >>>   $primary-contact =>"John Doe"
    >>>   $secondary-contact =>"John Doe"
    >>>   $status =>"Not yet provisioned"
    >>>   $email =>"my@email.address"
    >>>   $encoderPrimaryIp =>"127.0.0.1"
    >>>   $encoderBackupIp =>"127.0.0.1"
    >>>   $encoderUsername =>"183198"
    >>>   $encoderPassword =>"test"
    >>>   $entrypoints => {
    >>>     public $Backup => "b.ep137525.i.akamaientrypoint.net"
    >>>     public $Primary => "p.ep137525.i.akamaientrypoint.net"
    >>>   }
    >>> }

So you can use these attributes by direct access:

    print($media->stream->encoderUsername);   // Prints "183198"
    print($media->stream->encoderPassword);   // Prints "test"
    print($media->stream->encoderPrimaryIp);  // Prints "127.0.0.1"
    print($media->stream->encoderBackupIp);   // Prints "127.0.0.1"

If you need to inspect all data available returned by the API, you can use the `getData()` method to retrieve it.


## Documentation TODO

* All available fields for modifications
* Filtering
* Collections
* Pagination
* More about encoding attributes
* Media status
