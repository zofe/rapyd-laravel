<?php namespace Zofe\Rapyd\DataForm\Field;

use Zofe\Rapyd\Widget;
use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Input;

abstract class Field extends Widget
{

    //main properties
    public $type = "field";
    public $label;
    public $name;
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
    public $mode = 'editable';  //editable, readonly, autohide
    public $apply_rules = true;
    public $required = false;
    //data settings
    public $model;  //dataobject model
    public $insert_value = null; //default value for insert
    public $update_value = null; //default value for update
    public $show_value = null; //default value in visualization
    public $options = array(); //associative&multidim. array ($value => $description)
    public $mask = null;
    public $group;
    public $value = null;
    public $values = array();
    public $new_value;
    public $request_refill = true;
    public $is_refill = false;
    public $options_table = '';
    public $options_key = null;
    // layout
    public $layout = array(
        'field_separator' => '<br />',
        'option_separator' => '',
        'null_label' => '[null]',
    );
    public $star = '';

    public function __construct($name, $label)
    {
        parent::__construct();
        $this->name($name);
        $this->label = $label;
    }

    public function name($name)
    {
        //replace dots with underscores so field names are html/js friendly
        $this->name = str_replace(array(".", ",", "`"), array("_", "_", "_"), $name);

        if (!isset($this->db_name))
            $this->db_name = $name;
    }

    public function group($group)
    {
        $this->group = $group;
        return $this;
    }
    
    public function onchange($onchange)
    {

        $this->onchange = $onchange;
        return $this;
    }

    public function rule($rule)
    {
        //keep CI/kohana serialization
        if (is_array($rule))
            $rule = join('|', $rule);
        $this->rule = $rule;
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

    public function in($in)
    {
        $this->in = $in;
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

    public function attributes($attributes)
    {

        $this->attributes = $attributes;
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

    // --------------------------------------------------------------------
    //http://svn.bitflux.ch/repos/public/popoon/trunk/classes/externalinput.php
    function xssfilter($string)
    {
        if (is_array($string)) {
            return $string;
        }
        if ($this->type == "html") {
            return $string;
        }
        $string = str_replace(array("&amp;", "&lt;", "&gt;"), array("&amp;amp;", "&amp;lt;", "&amp;gt;",), $string);
        // fix &entitiy\n;

        $string = preg_replace('#(&\#*\w+)[\x00-\x20]+;#u', "$1;", $string);
        $string = preg_replace('#(&\#x*)([0-9A-F]+);*#iu', "$1$2;", $string);
        $string = html_entity_decode($string, ENT_COMPAT, "UTF-8");

        // remove any attribute starting with "on" or xmlns
        $string = preg_replace('#(<[^>]+[\x00-\x20\"\'])(on|xmlns)[^>]*>#iUu', "$1>", $string);
        // remove javascript: and vbscript: protocol
        $string = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*)[\\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu', '$1=$2nojavascript...', $string);
        $string = preg_replace('#([a-z]*)[\x00-\x20]*=([\'\"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu', '$1=$2novbscript...', $string);
        $string = preg_replace('#([a-z]*)[\x00-\x20]*=([\'\"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#Uu', '$1=$2nomozbinding...', $string);
        //<span style="width: expression(alert('Ping!'));"></span>
        // only works in ie...
        $string = preg_replace('#(<[^>]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*expression[\x00-\x20]*\([^>]*>#iU', "$1>", $string);
        $string = preg_replace('#(<[^>]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*behaviour[\x00-\x20]*\([^>]*>#iU', "$1>", $string);
        $string = preg_replace('#(<[^>]+)style[\x00-\x20]*=[\x00-\x20]*([\`\'\"]*).*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*>#iUu', "$1>", $string);
        //remove namespaced elements (we do not need them...)
        $string = preg_replace('#</*\w+:\w[^>]*>#i', "", $string);
        //remove really unwanted tags

        do {
            $oldstring = $string;
            $string = preg_replace('#</*(applet|meta|xml|blink|link|style|script|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', "", $string);
        } while ($oldstring != $string);

        return $string;
    }

    public function getValue()
    {

        $name = $this->db_name;
        if (($this->request_refill == true) && Input::get($this->name) != null) {
           if (is_array(Input::get($this->name))) {
                $values = array();
                $this->value = implode($this->serialization_sep, $values);
            } else {
                $request_value = self::xssfilter(Input::get($this->name));
                $this->value = $request_value;
            }
            $this->is_refill = true;
        } elseif (($this->status == "create") && ($this->insert_value != null)) {
            $this->value = $this->insert_value;
        } elseif (($this->status == "modify") && ($this->update_value != null)) {
            $this->value = $this->update_value;
        } elseif (($this->status == "show") && ($this->show_value != null)) {
            $this->value = $this->show_value;
        } elseif (isset($this->model)  
                  && method_exists($this->model, $name) 
                  && is_a($this->model->$name(),'Illuminate\Database\Eloquent\Relations\Relation') ) {


            $methodClass =  get_class($this->model->$name());
            switch($methodClass)
            {
                case 'Illuminate\Database\Eloquent\Relations\BelongsToMany':

                    $relatedCollection = $this->model->$name()->get(); //Collection of attached models
                    $relatedIds = $relatedCollection->modelKeys(); //array of attached models ids
                    $this->value = implode($this->serialization_sep,$relatedIds);

                    break;
                case 'Illuminate\Database\Eloquent\Relations\HasOneOrMany':
                case 'Illuminate\Database\Eloquent\Relations\HasOne':
                case 'Illuminate\Database\Eloquent\Relations\HasMany':

                    $this->value = 2;
                    break;
            }
        } elseif ((isset($this->model))  && (Input::get($this->name)===null) && ($this->model->offsetExists($this->db_name))) {

            $this->value = $this->model->getAttribute($this->db_name);
        }
        
        $this->getMode();
    }

    public function getNewValue()
    {
        if (Input::get($this->name)) {
            if ($this->status == "create") {
                $this->action = "insert";
            } elseif ($this->status == "modify") {
                $this->action = "update";
            }

            if (is_array(Input::get($this->name))) {
                $values = array();
                foreach (Input::get($this->name) as $value) {
                    $values[] = self::xssfilter($value);
                }
                $this->new_value = implode($this->serialization_sep, $values);
            } else {
                $request_value = self::xssfilter(Input::get($this->name));
                $this->new_value = $request_value;
            }
        } elseif (($this->action == "insert") && ($this->insert_value != null)) {
            $this->new_value = $this->insert_value;
        } elseif (($this->action == "update") && ($this->update_value != null)) {
            $this->new_value = $this->update_value;
        } else {
            $this->action = "idle";
        }
    }

    // --------------------------------------------------------------------

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

            default:;
        }

        if (isset($this->when)) {
            if (is_string($this->when) AND strpos($this->when, '|')) {
                $this->when = explode('|', $this->when);
            }
            $this->when = (array) $this->when;
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

            if (!Schema::hasColumn($this->model->getTable(), $this->db_name))
            {
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


    public function updateRelations() {

        $relation = $this->db_name;
        if (isset($this->new_value)) {
            $data = $this->new_value;
        } else {
            $data = $this->value;
        }
        $data = explode($this->serialization_sep, $data);
        
        if ( method_exists($this->model, $relation) && is_a($this->model->$relation(),'Illuminate\Database\Eloquent\Relations\Relation') ) {
            
                $methodClass =  get_class($this->model->$relation());
                switch($methodClass)
                {
                    case 'Illuminate\Database\Eloquent\Relations\BelongsToMany':
                        $old_data =  $this->model->$relation()->get()->modelKeys(); 
                        $this->model->$relation()->detach($old_data);
                        $this->model->$relation()->attach($data);
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\HasOneOrMany':
                    case 'Illuminate\Database\Eloquent\Relations\HasOne':
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

    public function build()
    {
        $this->getValue();
        $this->star = (!$this->status == "show" AND $this->required) ? '&nbsp;*' : '';

        $attributes = array('onchange', 'name', 'type', 'size', 'style', 'class', 'rows', 'cols');

        foreach ($attributes as $attribute) {
            if (isset($this->$attribute))
                $this->attributes[$attribute] = $this->$attribute;

            if ($attribute == 'type') {
                $this->attributes['type'] = ($this->$attribute == 'input') ? 'text' : $this->$attribute;
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
