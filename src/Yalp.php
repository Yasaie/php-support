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
     * @author  Payam Yasaie <payam@yasaie.ir>
     * @since   2019-08-15
     *
     * @param      $object
     * @param      $dots
     * @param bool $string
     *
     * @return array|mixed|string|null
     */
    static public function dot($object, $dots, $string = false)
    {
        # set item to null if no results found
        $item = null;
        # Return object if no dots defined
        if ($dots === '') {
            $item = $object;
        } else {
            # Separate dots
            $segments = explode('.', $dots);

            # if * was defined loop through
            if ($segments[0] == '*') {
                # Remove first element and glue others
                array_shift($segments);
                $right = implode('.', $segments);
                $item = [];

                foreach ($object as $each) {
                    $new = static::dot($each, $right, $string);

                    # makes a flat array
                    if (is_array($new)) {
                        $item = array_merge($item, array_values($new));
                    } else {
                        $item[] = $new;
                    }
                }

            } else {
                # check if current index is array
                if (isset($object[$segments[0]])) {
                    $item = $object[$segments[0]];
                }

                # check if current is function
                if (strpos($segments[0], '()') and !is_array($object)) {
                    $segments[0] = str_replace('()', '', $segments[0]);
                    $item = $object->{$segments[0]}();
                }

                # if item was not set yet try object
                if (!isset($item)) {
                    try {
                        $item = $object->{$segments[0]};
                    } catch (\Exception $e) {
                        unset($e);
                    }
                }

                # check if still has child
                if (count($segments) > 1) {
                    # remove first index of object for pass to function again
                    array_shift($segments);
                    $right = implode('.', $segments);
                    return static::dot($item, $right, $string);
                }
            }
        }

        # check if $item is array filter or make string
        if (is_array($item)) {
            $item = array_filter($item);

            # if string was true array will be string
            if ($string) {
                return implode(PHP_EOL, $item);
            }
        }

        return $item;
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