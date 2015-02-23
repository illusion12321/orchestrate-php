<?php
namespace andrefelipe\Orchestrate\Common;

use andrefelipe\Orchestrate\Common\ObjectArrayTrait;

/**
 * 
 * Part of this code was appropriated from:
 * https://github.com/phalcon/cphalcon/blob/2.0.0/phalcon/config.zep
 * Credit goes to Andres Gutierrez and Eduar Carvajal of the Phalcon team.
 */
class ObjectArray implements \ArrayAccess, \Countable, ToObjectInterface
{
    use ObjectArrayTrait;

    /**
     * @param array $values Values to set to the object on construct.
     */
    public function __construct(array $values = null)
    {
        if ($values) {
            foreach ($values as $key => $value) {
                $key = (string) $key;

                if (is_array($value)) {
                    $this->{$key} = new self($value);
                } else {
                    $this->{$key} = $value;
                }
            }
        }
    }
}
