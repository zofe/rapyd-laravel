<?php namespace Zofe\Rapyd\DataForm\Field;

use Zofe\Rapyd\Widget;
use Zofe\Rapyd\Helpers\HTML;
use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Input;

abstract class Field extends Widget
{

    //main properties
    public $type = "field";
    public $multiple = false;
    public $label;
    public $name;
    public $relation;
    public $rel_name;
    public $rel_field;
    public $rel_key;
    public $rel_fq_key;
    public $rel_fq_other_key;
    public $rel_other_key;

    public $attributes = array('class' => 'form-control');
    public $output = "";
    public $visible = true;
    public $extra_output = "";
    public $serialization_sep = '|';
    //atributes
    public $maxlength;
    public $onclick;
    public $onchange;
    public $style;
    //datafilter related
    public $operator = "=";
    public $clause = "like";
    public $orvalue = "";
    //field actions & field status
    public $mode = 'editable'; //editable, readonly, autohide
    public $apply_rules = true;
    public $required = false;
    //data settings
    public $model; 
    public $insert_value = null; 
    public $update_value = null; 
    public $show_value = null; //default value in visualization
    public $options = array();
    public $mask = null;
    public $group;
    public $value = null;
    public $values = array();
    public $new_value;
    public $old_value = null;
    public $request_refill = true;
    public $is_refill = false;
    public $is_hidden = false;
    public $options_table = '';
    public $options_key = null;
    public $has_error = '';
    public $messages = array();
    public $query_scope;
    
    /**
     * @var \Zofe\Rapyd\DataForm\DataForm
     */
    public $widget;

    
    // layout
    public $layout = array(
        'field_separator' => '<br />',
        'option_separator' => '',
        'null_label' => '[null]',
    );

    public $rule = '';
    
    
    public $star = '';

    public function __construct($name, $label, &$model = null)
    {
        parent::__construct();

        $this->model = $model;

        $this->name($name);
        $this->label = $label;
    }

    public function name($name)
    {
        //detect relation or relation.field
        $relation = null;
        if (preg_match('#^([a-z0-9_-]+)\.([a-z0-9_-]+)$#i', $name, $matches)) {
            $relation = $matches[1];
            $name = $matches[2];
        } elseif (preg_match('#^[a-z0-9_-]+$#i', $name)) {
            $relation = $name;
        }

        if (isset($this->model) &&
            method_exists($this->model, $relation) &&
            is_a($this->model->$relation(), 'Illuminate\Database\Eloquent\Relations\Relation')
        ) {

            $this->relation = $this->model->$relation($relation);
            $this->rel_key = $this->relation->getModel()->getKeyName();
            $this->rel_fq_key = $this->relation->getModel()->getQualifiedKeyName();
            $this->rel_name = $relation;
            $this->rel_field = $name;
            $this->name = ($name != $relation) ? $relation . "_" . $name : $name;

            $relclass = get_class($this->relation);
            
            if ($relclass == 'Illuminate\Database\Eloquent\Relations\BelongsTo') {
                $this->db_name = $this->relation->getForeignKey();
            } else {
                $this->db_name = $name;
            }

            if (in_array($relclass, array('Illuminate\Database\Eloquent\Relations\BelongsToMany'))) {
                
                $this->rel_other_key = $this->relation->getOtherKey();
 
            }

            return;
        }

        //otherwise replace dots with underscores so field names are html/js friendly
        $this->name = str_replace(array(".", ",", "`"), array("_", "_", "_"), $name);

        if (!isset($this->db_name))
            $this->db_name = $name;
    }

    public function onchange($onchange)
    {

        $this->onchange = $onchange;
        return $this;
    }

    /**
     * add rules for field es.:  required|min:5 ...
     * @param $rule
     * @return $this
     */
    public function rule($rule)
    {
        $this->rule = trim($this->rule."|".$rule, "|");
        if ((strpos($this->rule, "required") !== false) AND !isset($this->no_star)) {
            $this->required = true;
        }
        return $this;
    }

    public function mode($mode)
    {

        $this->mode = $mode;
        return $this;
    }

    public function mask($mask)
    {

        $this->mask = $mask;
        return $this;
    }

    public function clause($clause)
    {
        $this->clause = $clause;
        return $this;
    }

    public function operator($operator)
    {
        $this->operator = $operator;
        return $this;
    }

    public function scope($scope)
    {
        $this->query_scope = $scope;
        return $this;
    }

    public function insertValue($insert_value)
    {
        $this->insert_value = $insert_value;
        return $this;
    }

    public function showValue($show_value)
    {
        $this->show_value = $show_value;
        return $this;
    }

    public function updateValue($update_value)
    {
        $this->update_value = $update_value;
        return $this;
    }

    public function extra($extra)
    {
        $this->extra_output = $extra;
        return $this;
    }

    public function getValue()
    {
        $name = $this->db_name;

        $process = (Input::get('search') || Input::get('save')) ? true : false;

        if ($this->request_refill == true && $process) {
            if ($this->multiple) {

                $this->value = "";
                if (Input::get($this->name)) {
                    $values = Input::get($this->name);
                    if (!is_array($values))
                    {
                        $this->value = $values;
                    } else {
                        $this->value = implode($this->serialization_sep, $values);
                    }
                }
                
            } else {
                $this->value = HTML::xssfilter(Input::get($this->name));
            }
            $this->is_refill = true;

        } elseif (($this->status == "create") && ($this->insert_value != null)) {
            $this->value = $this->insert_value;
        } elseif (($this->status == "modify") && ($this->update_value != null)) {
            $this->value = $this->update_value;
        } elseif (($this->status == "show") && ($this->show_value != null)) {
            $this->value = $this->show_value;
        } elseif (isset($this->model) && $this->relation != null) {

            $methodClass = get_class($this->relation);
 
            switch ($methodClass) {
                //es. "categories" per "Article"  
                case 'Illuminate\Database\Eloquent\Relations\BelongsToMany':
     
                    // some kind of field on belongsToMany works with multiple values, most of time in serialized way
                    //in this case I need to fill value using a serialized array of related collection
                    if (in_array($this->type, array('tags','checks')))
                    {
                        $relatedCollection = $this->relation->get(); //Collection of attached models
                        $relatedIds = $relatedCollection->modelKeys(); //array of attached models ids
                        $this->value = implode($this->serialization_sep, $relatedIds);
                    } else {
                        $this->value = "";
                    }


                    break;
                //es. "author" per "Article"
                case 'Illuminate\Database\Eloquent\Relations\BelongsTo':
                    $fk = $this->relation->getForeignKey(); //value I need is the ForeingKey
                    $this->value = $this->model->getAttribute($fk);
                    break;

                //es. "article_detail" per "article"
                case 'Illuminate\Database\Eloquent\Relations\HasOne':
                    $this->value = @$this->relation->get()->first()->$name; //value I need is the field value on related table
//                     @$this->model->$relation->$name;

                    break;

                //es. "comments" for "Article"
                default:
                    //'Illuminate\Database\Eloquent\Relations\HasOneOrMany':
                    //'Illuminate\Database\Eloquent\Relations\HasMany':
                    //polimorphic, etc.. 
                    throw new \InvalidArgumentException("The field {$this->db_name} is a " . $methodClass
                        . " but Rapyd can handle only BelongsToMany, BelongsTo, and HasOne");
                    break;
            }
        } elseif ((isset($this->model)) && (Input::get($this->name) === null) && ($this->model->offsetExists($this->db_name))) {

            $this->value = $this->model->getAttribute($this->db_name);

        }
        
        //storing old model value in a propery
        if(isset($this->model) && ($this->model->offsetExists($this->db_name))) 
        {
            $this->old_value = $this->model->getAttribute($this->db_name);
        }
        $this->getMode();
    }

    public function getNewValue()
    {
        $process = (Input::get('search') || Input::get('save')) ? true : false;
        //if (Input::get($this->name)) {
        if ($process) {
            if ($this->status == "create") {
                $this->action = "insert";
            } elseif ($this->status == "modify") {
                $this->action = "update";
            }

            if ($this->multiple) {
                $this->value = "";
                if (Input::get($this->name)) {
                    $values = Input::get($this->name);
                    if (!is_array($values))
                    {
                        $this->new_value = $values;
                    } else {
                        $this->new_value = implode($this->serialization_sep, $values);
                    }
                }

            } else {

                $this->new_value = HTML::xssfilter(Input::get($this->name));
            }
        } elseif (($this->action == "insert") && ($this->insert_value != null)) {
            $this->new_value = $this->insert_value;
        } elseif (($this->action == "update") && ($this->update_value != null)) {
            $this->new_value = $this->update_value;
        } else {
            $this->action = "idle";
        }
    }


    public function getMode()
    {
        switch ($this->mode) {
            case "showonly":
                if (($this->status != "show")) {
                    $this->status = "hidden";
                }
                break;
            case "autohide":
                if (($this->status == "modify") || ($this->action == "update")) {
                    $this->status = "show";
                    $this->apply_rules = false;
                }
                break;

            case "readonly":
                $this->status = "show";
                $this->apply_rules = false;
                break;

            case "autoshow":
                if (($this->status == "create") || ($this->action == "insert")) {
                    $this->status = "hidden";
                    $this->apply_rules = false;
                }
                break;
            case "hidden":
                $this->status = "hidden";
                $this->apply_rules = false;
                break;
            case "show":
                break;

            default:
                ;
        }

        if (isset($this->when)) {
            if (is_string($this->when) AND strpos($this->when, '|')) {
                $this->when = explode('|', $this->when);
            }
            $this->when = (array)$this->when;
            if (!in_array($this->status, $this->when) AND !in_array($this->action, $this->when)) {
                $this->visible = false;
                $this->apply_rules = false;
            } else {
                $this->visible = true;
                $this->apply_rules = true;
            }
        }
    }

    public function options($options)
    {
        if (is_array($options)) {
            $this->options += $options;
        }
        return $this;
    }

    public function option($value = '', $description = '')
    {
        $this->options[$value] = $description;
        return $this;
    }

    public function optionGroup($value = '', $description = '', $group_id = '', $group_label = '')
    {
        $this->option_groups[$group_id]['label'] = $group_label;
        $this->option_groups[$group_id]['options'][$value] = $description;
        return $this;
    }

    public function autoUpdate($save = false)
    {
        $this->getValue();
        $this->getNewValue();

        if (is_object($this->model) && isset($this->db_name)) {

            if (
                !Schema::hasColumn($this->model->getTable(), $this->db_name)
                || is_a($this->relation, 'Illuminate\Database\Eloquent\Relations\HasOne') 
                ) {
                $this->model->saved(function () {
                    $this->updateRelations();
                });

                //check for relation then exit
                return true;
            }

            if (isset($this->new_value)) {
                $this->model->setAttribute($this->db_name, $this->new_value);
            } else {
                $this->model->setAttribute($this->db_name, $this->value);
            }
            if ($save) {
                return $this->model->save();
            }
        }
        return true;
    }


    public function updateRelations()
    {

        if (isset($this->new_value)) {
            $data = $this->new_value;
        } else {
            $data = $this->value;
        }
        if ($this->relation != null) {
            
            $methodClass = get_class($this->relation);
            switch ($methodClass) {
                case 'Illuminate\Database\Eloquent\Relations\BelongsToMany':

                    $old_data = $this->relation->get()->modelKeys();
                    $data = explode($this->serialization_sep, $data);

                    $this->relation->detach($old_data);
                    $this->relation->attach($data);
                    break;
                case 'Illuminate\Database\Eloquent\Relations\HasOne':
                    if (isset($this->widget->related_models[$this->rel_name])) {
                        $relation = $this->widget->related_models[$this->rel_name];
                    } else {
                        $relation = $this->relation->get()->first();
                        if (!$relation) $relation = $this->relation->getRelated();
                        $this->widget->related_models[$this->rel_name] = $relation;
                    }
                    $relation->{$this->rel_field} = $data;
                    $this->relation->save( $relation );
                    break;
                case 'Illuminate\Database\Eloquent\Relations\HasOneOrMany':

                case 'Illuminate\Database\Eloquent\Relations\HasMany':

                    //should manage this or not?
                    //if so, how?
                    break;
            }
        }
    }

    public function extraOutput()
    {
        return '<span class="extra">' . $this->extra_output . '</span>';
    }

    /**
     * parse blade syntax string using current model
     * @param $string
     * @return string
     */
    protected function parseString($string)
    {
        if (is_object($this->model) && strpos($string, '{{') !== false)
        {
            $fields = $this->model->getAttributes();
            $relations = $this->model->getRelations();
            $array = array_merge($fields, $relations) ;
            $string = $this->parser->compileString($string, $array);
        }
        return $string;
    }

    

    public function build()
    {
        $this->getValue();
        $this->star = (!$this->status == "show" AND $this->required) ? '&nbsp;*' : '';
        if (($this->status == "hidden" || $this->visible === false || in_array($this->type, array("hidden", "auto")))) {
            $this->is_hidden = true;
        }
        $this->message = implode("<br />\n", $this->messages);
        
        $attributes = array('onchange', 'type', 'size', 'style', 'class', 'rows', 'cols');

        foreach ($attributes as $attribute) {
            if (isset($this->$attribute))
                $this->attributes[$attribute] = $this->$attribute;

            if ($attribute == 'type') {
                $this->attributes['type'] = ($this->$attribute == 'input') ? 'text' : $this->$attribute;
            }
            
            if ($this->orientation == 'inline') {
                $this->attributes["placeholder"] = $this->label;
            } 
            
        }
        if (!isset($this->attributes['id']))
            $this->attributes['id'] = $this->name;
        if (isset($this->css_class))
            $this->attributes['class'] = $this->css_class;

        if ($this->visible === false) {
            return false;
        }
    }


}
