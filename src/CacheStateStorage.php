<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\authclient;

use Yii;
use yii\authclient\StateStorageInterface;
use yii\caching\Cache;
use yii\base\Component;
use yii\di\Instance;

/**
 * CacheStateStorage provides Auth client state storage based in cache component.
 *
 * @see StateStorageInterface
 *
 */
class CacheStateStorage extends Component implements StateStorageInterface
{
    /**
     * @var Cache|array|string cache object or the application component ID of the cache object to be used.
     *
     * After the CacheStateStorage object is created, if you want to change this property,
     * you should only assign it with a cache object.
     *
     * If not set - application 'cache' component will be used, but only, if it is available (e.g. in web application),
     * otherwise - no cache will be used and no data saving will be performed.
     */
    public $cache;
    public $prefix = 'cacheStorage';


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if ($this->cache === null) {
            if (Yii::$app->has('cache')) {
                $this->cache = Yii::$app->get('cache');
            }
        } else {
            $this->cache = Instance::ensure($this->cache, Cache::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        if ($this->cache !== null) {
            $this->cache->set($this->prefix . $key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        if ($this->cache !== null) {
            return $this->cache->get($this->prefix . $key);
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        if ($this->cache !== null) {
            return $this->cache->delete($this->prefix . $key);
        }
        return true;
    }
}
