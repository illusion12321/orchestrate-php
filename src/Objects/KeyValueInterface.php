<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Common\ToJsonInterface;
use andrefelipe\Orchestrate\Query\PatchBuilder;

/**
 * Define the KeyValue minimum required interface.
 */
interface KeyValueInterface extends
\ArrayAccess,
ValueInterface,
ToJsonInterface,
ReusableObjectInterface,
ConnectionInterface
{
    /**
     * @param boolean $required
     *
     * @return string
     */
    public function getCollection($required = false);

    /**
     * @param string $collection
     *
     * @return self
     */
    public function setCollection($collection);

    /**
     * @param boolean $required
     *
     * @return string
     */
    public function getKey($required = false);

    /**
     * @param string $key
     *
     * @return self
     */
    public function setKey($key);

    /**
     * @return string
     */
    public function getRef($required = false);

    /**
     * @param string $ref
     *
     * @return self
     */
    public function setRef($ref);

    /**
     * @return int
     */
    public function getReftime();

    /**
     * @return float
     */
    public function getScore();

    /**
     * @return float
     */
    public function getDistance();

    /**
     * @return boolean
     */
    public function isTombstone();

    /**
     * @param string $ref
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-get
     */
    public function get($ref = null);

    /**
     * @param array $value
     * @param string $ref
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-put
     */
    public function put(array $value = null, $ref = null);

    /**
     * @param array $value
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-post
     */
    public function post(array $value = null);

    /**
     * @param PatchBuilder $operations
     * @param string $ref
     * @param boolean $reload
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch
     */
    public function patch(PatchBuilder $operations, $ref = null, $reload = false);

    /**
     * @param array $value
     * @param string $ref
     * @param boolean $reload
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch-merge
     */
    public function patchMerge(array $value, $ref = null, $reload = false);

    /**
     * @param string $ref
     *
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function delete($ref = null);

    /**
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function purge();

    /**
     * @return Refs
     */
    public function refs();

    /**
     * @return Events
     */
    public function events($type = null);

    /**
     * @return Event
     */
    public function event($type = null, $timestamp = null, $ordinal = null);

    /**
     * @return Relations
     */
    public function relations($kind);

    /**
     * @return Relation
     */
    public function relation($kind, KeyValueInterface $destination);
}
