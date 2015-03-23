<?php namespace Zofe\Rapyd\Helpers;

class Arr
{
    public static function orderBy(&$array, $field, $direction = 'asc') {
        $column = array();
        foreach ($array as $key => $row) {
            $column[$key] = is_object($row) ? $row->{$field} : $row[$field];
        }
        if ($direction == 'asc') {
            array_multisort($column, SORT_ASC, $array);
        } else {
            array_multisort($column, SORT_DESC, $array);
        }
    }
}
