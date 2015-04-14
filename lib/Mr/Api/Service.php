<?php

namespace Mr\Api;

use Mr\Api\Http\Client;
use Mr\Api\Collection\ApiObjectCollection;
use Mr\Api\Model\ApiObject;
use Mr\Api\Model\Media;

// Exceptions
use Mr\Api\Exception\InvalidDataOperationException;

/**
 * Service Class file
 *
 * PHP Version 5.3
 *
 * @category Class
 * @package  Mr\Api
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */

/**
 * Service Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Api
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
class Service
{
    const API_HOST = 'api.devmobilerider.com';
    const REPO_NAMESPACE = 'Mr\\Api\\Repository\\';
    const APP_VENDOR_HEADER = 'X-Vendor-App-Id';

    protected $includeSubvendors = false;

    /**
    * var ClientInterface
    */
    protected $_client;

    public function __construct($appId, $secret, $host = '', array $options = array())
    {
        if (array_key_exists('include_subvendors', $options)) {
            $this->includeSubvendors = (bool) $options['include_subvendors'];
        }

        $host = empty($host) ? self::API_HOST : $host;
        $this->_client = new Client($host, $appId, $secret);

        if (!$this->includeSubvendors) {
            $this->_client->setGlobalHeader(self::APP_VENDOR_HEADER, $appId);
        }
    }

    public function getClient()
    {
        return $this->_client;
    }

    protected function getRepository($model)
    {
        $repoName = self::REPO_NAMESPACE . $model . 'Repository';
        return new $repoName($this->_client);
    }

    /**
    * Returns a new object from given model and initial data.
    * It does not execute any persistent action.
    *
    * @param $model string
    * @param $data object | array
    * @return Mr\Api\Model\ApiObject
    */
    public function create($model, $data = null)
    {
        $repo = $this->getRepository($model);

        return $repo->create($data);
    }

    /**
    * Returns an object by its given model and id.
    *
    * @param $model string
    * @param $id mixed
    * @return Mr\Api\Model\ApiObject
    */
    public function get($model, $id)
    {
        $repo = $this->getRepository($model);

        return $repo->get($id);
    }

    /**
    * Returns a all objects from given model.
    *
    * @param string $model
    * @param array $filters
    * @return ApiObjectCollection
    */
    public function getAll($model, $filters = array())
    {
        $repo = $this->getRepository($model);

        return $repo->getAll($filters);
    }

    /**
    * Saves given model object or list of objects.
    *
    * @param ApiObject | ApiObjectCollection | array $object
    * @return array
    */
    public function save($object)
    {
        if ((is_array($object) || $object instanceof ApiObjectCollection) && count($object)) {
            $firstObject = $object[0];
        } else {
            $firstObject = $object;
        }

        if ($firstObject instanceof ApiObject) {
            $model = $firstObject->getModel();
        } else {
            throw new InvalidDataOperationException('Invalid object type or empty data', 'Service Save object');
        }

        $repo = $this->getRepository($model);

        return $repo->save($object);
    }

    // Helpers

    public function getMedia($id)
    {
        return $this->get('Media', $id);
    }

    public function getChannel($id)
    {
        return $this->get('Channel', $id);
    }

    /**
    * Returns all media objects
    *
    * @param array $filters
    * @return ApiObjectCollection
    */
    public function getMedias($filters = array())
    {
        return $this->getAll('Media', $filters);
    }

    /**
    * Returns all channel objects
    *
    * @param array $filters
    * @return ApiObjectCollection
    */
    public function getChannels($filters = array())
    {
        return $this->getAll('Channel', $filters);
    }

    /**
    * Returns a new channel empty or with given initial data.
    * It does not execute any persistent action.
    *
    * @param $data object | array
    * @return Mr\Api\Model\Channel
    */
    public function createChannel($data = null)
    {
        $channel = $this->create('Channel', $data);

        return $channel;
    }

    /**
    * Returns a new VOD media empty or with given initial data.
    * It does not execute any persistent action.
    *
    * @param $data object | array
    * @return Mr\Api\Model\Media
    */
    public function createVODMedia($data = null)
    {
        $media = $this->create('Media', $data);
        $media->type = Media::TYPE_VOD;

        return $media;
    }

    /**
    * Returns a new live media empty or with given initial data.
    * It does not execute any persistent action.
    *
    * @param $data object | array
    * @return \Mr\Api\Model\Media
    */
    public function createLiveMedia($data = null)
    {
        $media = $this->create('Media', $data);
        $media->type = Media::TYPE_LIVE;

        return $media;
    }

    /**
     * Returns a new vod media empty or with given initial data.
     * It does not execute any persistent action.
     *
     * @param $data object | array
     * @return \Mr\Api\Model\Media
     */
    public function createVodMedia($data = null)
    {
        $media = $this->create('Media', $data);
        $media->type = Media::TYPE_VOD;

        return $media;
    }
}
