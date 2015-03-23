<?php namespace Zofe\Rapyd\DataForm;


class Form {
    
    public static function open($action = '', $attr = array(), $hidden = array())
    {
        if ($action == '') {
            $action = $_SERVER["REQUEST_URI"];
        }
        $form = '<form action="'.$action.'"'.self::attributes($attr).'>'."\n";
        if (is_array($hidden) AND count($hidden) > 0)
        {
            $form .= self::hidden($hidden);
        }
        return $form;
    }
    
    public static function openMultipart($action = '', $attr = array(), $hidden = array())
    {
        $attr['enctype'] = 'multipart/form-data';
        return self::open($action, $attr, $hidden);
    }
    
    public static function input($name, $value = '', $attr = array(), $type = 'text')
    {
        $attr += array(
            'name'  => $name,
            'type'  => $type,
            'value' => htmlspecialchars($value),
            'id'    => (isset($attr['id'])) ? $attr['id'] : $name,
        );
        if ($type == 'submit') unset($attr['name'], $attr['id']);
        return '<input'.self::attributes($attr).' />';
    }

    public static function text($name, $value = '', $attr = array())
    {
        return self::input($name, $value, $attr, 'text');
    }

    public static function hidden($name, $value = '', $attr = array())
    {
        return self::input($name, $value, $attr, 'hidden');
    }
    
    public static function password($name, $value = '', $attr = array())
    {
        return self::input($name, $value, $attr, 'password');
    }
    
    public static function upload($name, $value = '', $attr = array())
    {
        return self::input($name, $value, $attr, 'file');
    }
    
    public static function textarea($name, $value = '', $attr = array())
    {
        $attr['name'] = $name;
        return '<textarea'.self::attributes($attr).'>'.htmlspecialchars($value).'</textarea>';
    }
    
    public static function select($name, $options = array(), $value = '', $attr = array())
    {
        $attr['name'] = $name;
        $input = '<select '.self::attributes($attr).'>'."\n";
        foreach ($options as $key => $val) {
            $opt_attr = array();
            if ($value === $key) $opt_attr['selected'] = 'selected';
            $input .= '<option value="'.$key.'"'.self::attributes($opt_attr).'>'.$val.'</option>'."\n";
        }
        $input .= '</select>';

        return $input;
    }
    
    public static function checkbox($name, $value = '', $checked = FALSE, $attr = array())
    {
        $attr['name'] = $name;
        if ($checked) {
            $attr['checked'] = 'checked';
        } else {
            unset($attr['checked']);
        }

        return self::input($name, $value, $attr, 'checkbox');
    }
    
    public static function radio($name, $value = '', $checked = FALSE, $attr = array())
    {
        $attr['name'] = $name;
        if ($checked) {
            $attr['checked'] = 'checked';
        } else {
            unset($attr['checked']);
        }

        return self::input($name, $value, $attr, 'radio');
    }
    
    public static function submit($value = '', $attr = array())
    {
        return self::input($name='', $value, $attr, 'submit');
    }

    public static function button($name, $value = '', $attr = array())
    {
        $attr['name'] = $name;
        return '<button'.self::attributes($attr).'>'.htmlspecialchars($value).'</button>';
    }

    public static function close($extra = '')
    {
        return '</form>'."\n".$extra;
    }

    public static function label($name, $text = '', $attr = array())
    {
        $attr['for'] = $name;
        return '<label'.self::attributes($attr).'>'.$text.'</label>';
    }

    public static function attributes($attr)
    {
        if (!isset($attr['id']) && isset($attr['name'])){
            $attr['id'] = $attr['name'];            
        }

        $order = array
        (
            'type',
            'id',
            'name',
            'value',
            'src',
            'size',
            'maxlength',
            'rows',
            'cols',
            'accept',
            'tabindex',
            'accesskey',
            'align',
            'alt',
            'title',
            'class',
            'style',
            'selected',
            'checked',
            'readonly',
            'disabled'
        );

        $sorted = array();
        foreach($order as $key) {
            if (isset($attr[$key])) {
                $sorted[$key] = $attr[$key];
            }
        }
        $sorted = array_merge($sorted, $attr);

        $compiled = '';
        foreach($sorted as $key => $val)
        {
            $compiled .= ' '.$key.'="'.$val.'"';
        }

        return $compiled;
    }

}