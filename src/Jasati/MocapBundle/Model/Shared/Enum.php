<?php

namespace Jasati\MocapBundle\Model\Shared;

/**
 * @author Yahya <yahya6789@gmail.com>
 */
trait Enum
{
    /**
     * Get constant name
     *
     * @param mixed $value Constant value
     * @return int|null|string
     */
    public final static function getConstant($value)
    {
        $constants = self::getConstants();
        foreach($constants as $k => $v) {
            if ($v == $value)
                return $k;
        }
        return null;
    }

    /**
     * Get all constants
     *
     * @return array Constants
     */
    public final static function getConstants()
    {
        $class = new \ReflectionClass(get_called_class());
        return $class->getConstants();
    }
}