<?php 

namespace Mr\Api;

use Mr\Api\Http\Client;

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

    /**
    * var ClientInterface
    */
    protected $_client;

    public function __construct($appId, $secret)
    {
        $this->_client = new Client(self::API_HOST, $appId, $secret);
    }

    protected function getRepository($model)
    {
        $repoName = self::REPO_NAMESPACE . $model . 'Repository';
        return new $repoName($this->_client);
    }

    /**
    * Returns a new object from given model and initial data. 
    * It does not execute any persisten action.
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
    * @param $model string 
    * @return array
    */
    public function getAll($model)
    {
        $repo = $this->getRepository($model);

        return $repo->getAll();
    }
}