<?php
namespace andrefelipe\Orchestrate\Common;

/**
 * 
 * This class was inspired by:
 * https://github.com/phalcon/cphalcon/blob/2.0.0/phalcon/config.zep
 * Which credit goes to Andres Gutierrez and Eduar Carvajal of the
 * wonderful Phalcon project.
 */
class ObjectArray implements \ArrayAccess, \Countable, ToJsonInterface
{
    use ObjectArrayTrait;

    /**
     * @param array $values Values to set to the object on construct.
     */
    public function __construct(array $values = null)
    {
        if ($values) {
            foreach ($values as $key => $value) {
                $this->{(string) $key} = $value;
            }
        }
    }

    /**
     * Merges an array's or object's values into the object.
     *
     * @param array|object $object
     * @return ObjectArray this 
     */
    public function merge($object)
    {
        if (is_object($object)) {
            
            $this->_mergeObject($object);

        } else if (is_array($object)) {

            $index = count($this);

            foreach ($object as $key => $value) {

                if (is_int($key)) {
                    $key = $index++;
                }
                $key = (string) $key;

                if (isset($this->{$key}) && is_object($value) && is_object($this->{$key})) {
                    $this->_mergeObject($value, $this->{$key});
                } else {
                    $this->{$key} = $value;
                }
            }
        }
        return $this;
    }   
}
