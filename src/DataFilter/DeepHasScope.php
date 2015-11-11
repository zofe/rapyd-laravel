<?php

namespace Zofe\Rapyd\DataFilter;

trait DeepHasScope {

    public function scopeHasRel($query, $value, $relation, $operator = 'LIKE', $value_pattern='%%%s%%')
    {
        if ($value===null) {
            return $query;
        }
        $relations = explode('.', $relation);
        if (strtoupper(trim($operator)) == "LIKE") {
            $value = sprintf($value_pattern, $value);
        }
        if (count($relations) < 2)
        {
            throw new \LogicException('Relation param must contain at least 2 elements: "relation.field"'. $relation);
        }
        return $this->recurseRelation($query, $value, $relations, $operator);
    }

    protected function recurseRelation($query, $value, $relations, $operator)
    {
        $field = end($relations);
        if (count($relations)==1)
        {
            return $query->where($field, $operator, $value);
        }
        else
        {
            $rel = array_shift($relations);
            return $query->whereHas($rel, function ($q) use ($value, $relations, $operator) {
                return $this->recurseRelation($q, $value, $relations, $operator);
            });
        }
    }
}
