<?php namespace Zofe\Rapyd\DataForm;

use Illuminate\Support\Collection;

class FieldCollection extends Collection
{

    public function add($name, $label, $type)
    {
        $classname = '\Zofe\Rapyd\DataForm\Fields\\' . ucfirst($type);
        if (class_exists($classname)) {
            $field = new $classname;
            $field->name = $name;
            $field->label = $label;
            return $this->push($field);

        } else {
            throw new \InvalidArgumentException('\Zofe\Rapyd\DataForm\Fields\Field subclass expected');
        }
        
    }

    /**
     * remove field where type==$type from field list and button container
     *
     * @param $type
     * @return $this
     */
    public function removeType($type)
    {
        $this->filter(function($field) use($type) {
            if ($field->type == $type) $this->forget($field->name);
        });
        return $this;
    }
    
    /**
     * Push an item onto the end of the collection.
     *
     * @param  mixed  $value
     * @return void
     */
    public function push($item)
    {
        $this->items[$item->name] = $item;
        return $item;
    }

    /**
     * Get item from the collection
     * 
     * @param mixed $key
     * @param mixed|null $attributes
     * @return mixed
     */
    public function get($key, $attributes = array())
    {
        if (array_key_exists($key, $this->items))
        {
            $field = $this->items[$key];
            if (count($attributes)) {
                $field->attributes($attributes);
                $field->build();
            }
            return $field;
        }
        
    }
}
