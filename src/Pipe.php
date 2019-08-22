<?php
/**
 * Project: Composer
 * User:    Payam Yasaie <payam@yasaie.ir>
 * Date:    2019/08/22 - 10:33 PM
 */

namespace Yasaie\Support;

/**
 * @author      Payam Yasaie <payam@yasaie.ir>
 * @copyright   2019/08/22
 *
 * Class Pipe
 * @package     Yasaie\Support
 */
class Pipe
{
    public $get = null;

    /**
     * Pipe constructor.
     *
     * @param $object
     */
    public function __construct($object)
    {
        $this->get = $object;
    }

    /**
     * @author      Payam Yasiae <payam@yasaie.ir>
     * @copyright   2019/08/22
     *
     * @param $object
     *
     * @return Pipe
     */
    static public function take($object)
    {
        return new static($object);
    }

    /**
     * @author      Payam Yasiae <payam@yasaie.ir>
     * @copyright   2019/08/22
     *
     * @param $method
     * @param mixed ...$param
     *
     * @return mixed
     */
    public function pipe($method, ...$param)
    {
        $obj_index = array_search('$$', $param);

        if ($obj_index === false) {
            array_unshift($param, '$$');
            $obj_index = 0;
        }

        $param[$obj_index] = $this->get;

        $this->get = $method(...$param);

        return $this;
    }

    /**
     * @author      Payam Yasiae <payam@yasaie.ir>
     * @copyright   2019/08/22
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->pipe($name, ...$arguments);
    }

}