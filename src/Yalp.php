<?php

namespace Yasaie\Support;

/**
 * Class    Yalp
 *
 * @author  Payam Yasaie <payam@yasaie.ir>
 * @since   2019-08-15
 *
 * @package Yasaie\Support
 */
class Yalp
{

    /**
     * @author      Payam Yasiae <payam@yasaie.ir>
     * @copyright   2019/08/22
     *
     * @param $object
     * @param $dots
     * @param mixed ...$params
     *
     * @return array|mixed|null
     */
    static public function dot($object, $dots, ...$params)
    {
        # define variables
        $item = null;
        $param = [];

        # Return object if no dots defined
        if ($dots === '') {
            $item = $object;
        } else {
            # Separate dots
            $segments = explode('.', $dots);

            # if empty it is double dot (..) means pipe
            if (empty($segments[0])) {
                array_shift($segments);

                # get the function type if exist
                $func_type = static::parseFunc($segments[0]);

                # check if current is function
                if ($func_type) {
                    if ($func_type == 2 and $params) {
                        $param = array_shift($params);
                    }

                    $item = (new Pipe($object))->pipe($segments[0], ...$param)->get;
                }
            }
            # if * was defined loop through
            elseif ($segments[0] == '*') {
                # Remove first element and glue others
                array_shift($segments);
                $count_empty = array_count_values($segments);
                $first_empty = array_search('', $segments);
                $two_empty = (isset($count_empty['']) and $count_empty[''] > 1);

                if ($two_empty) {
                    $right = implode('.', array_slice($segments, 0, $first_empty));
                    array_shift($segments);
                } else {
                    $right = implode('.', $segments);
                }
                $item = [];

                foreach ($object as $each) {
                    $new = static::dot($each, $right, ...$params);

                    # makes a flat array
                    if (is_array($new)) {
                        $item = array_merge($item, array_values($new));
                    } else {
                        $item[] = $new;
                    }
                }

                if (! $two_empty) {
                    return $item;
                }

            } else {
                # check if current index is array
                if (is_array($object) and isset($object[$segments[0]])) {
                    $item = $object[$segments[0]];
                }

                # get the function type if exist
                $func_type = static::parseFunc($segments[0]);

                # check if current is function
                if ($func_type) {
                    if ($func_type == 2 and $params) {
                        $param = array_shift($params);
                    }

                    $item = static::call($object, $segments[0], ...$param);
                }

                # if item was not set yet try object
                if (!isset($item)) {
                    try {
                        $item = $object->{$segments[0]};
                    } catch (\Exception $e) {
                        unset($e);
                    }
                }
            }

            # check if still has child
            if (count($segments) > 1) {
                # remove first index of object for pass to function again
                array_shift($segments);
                $right = implode('.', $segments);
                return static::dot($item, $right, ...$params);
            }
        }

        # check if $item is array filter or make string
        if (is_array($item)) {
            $item = array_filter($item);
        }

        return $item;
    }

    /**
     * @author      Payam Yasiae <payam@yasaie.ir>
     * @copyright   2019/08/22
     *
     * @param string $method
     * @return int
     */
    static public function parseFunc(&$method)
    {
        $method = str_replace('()', '', $method, $func);
        $method = str_replace('($)', '', $method, $func_param);

        return $func ? 1 : ($func_param ? 2 : 0);
    }

    /**
     * @author      Payam Yasiae <payam@yasaie.ir>
     * @copyright   2019/08/22
     *
     * @param $object
     * @param string $method
     * @param array $param
     *
     * @return mixed
     */
    static public function call($object, $method, ...$param)
    {
        $item = null;

        if (is_array($object)) {
            $item = $object[$method](...$param);
        } elseif ($object) {
            $item = $object->$method(...$param);
        }

        return $item;
    }

    /**
     * @author  Payam Yasaie <payam@yasaie.ir>
     * @since   2019-08-18
     *
     * @param $items
     * @param $names
     *
     * @return array|\Illuminate\Support\Collection
     */
    static public function flatten($items, $names)
    {
        $output = [];
        $id = 0;

        # Loop trough each item
        foreach ($items as $item) {
            # set $output an object
            $output[$id] = new \stdClass();
            # Loop trough each name for each item
            foreach ($names as $name) {
                # if get wasn't set get is the name
                isset($name['get'])
                or $name['get'] = $name['name'];
                # check if result should be string
                $is_string = isset($name['string']) and $name['string'];
                # check if isset params
                $params = isset($name['params']) ? $name['params'] : [];
                if (isset($name['string']) and $name['string']) {
                    $name['get'] .= '...implode($)';
                    $params[][] = PHP_EOL;
                }

                # get item value recursive
                $output[$id]->{$name['name']} = self::dot($item, $name['get'], ...$params);
            }

            $id++;
        }

        # if Illuminate Collection was exist return as collection
        if (class_exists('Illuminate\Support\Collection')) {
            return collect($output);
        }

        return $output;
    }

    /**
     * @author  Payam Yasaie <payam@yasaie.ir>
     * @since   2019-08-15
     *
     * @param     $elements
     * @param int $parentId
     *
     * @return array
     */
    static public function buildTree($elements, $parentId = 0)
    {
        $branch = array();

        foreach ($elements as $element) {
            if ($element->parent_id == $parentId) {
                $children = self::buildTree($elements, $element->id);
                if ($children) {
                    $element->children = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    /**
     * @author  Payam Yasaie <payam@yasaie.ir>
     * @since   2019-08-18
     *
     * @param     $per_page
     * @param     $count
     * @param int $current
     *
     * @return \stdClass
     */
    static public function paginate($per_page, $count, $current = 1)
    {
        $page = new \stdClass();

        $page->current = $current > 0 ? $current : 1;
        $page->perPage = $per_page;
        $page->items_count = $count;
        $page->count = (int)ceil($page->items_count / $page->perPage);

        return $page;
    }

}