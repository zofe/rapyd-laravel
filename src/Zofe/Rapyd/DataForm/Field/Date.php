<?php

namespace Zofe\Rapyd\DataForm\Field;

use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\Input;
use Zofe\Rapyd\Rapyd;

class Date extends Field
{
    public $type = "date";
    public $format = 'm/d/Y';
    public $language = 'en';

    /**
     * set instarnal preview date format
     * @param $format valid php date format
     * @param string $language valid DatePicker language string http://bootstrap-datepicker.readthedocs.org/en/release/options.html#language 
     */
    public function format($format, $language = 'en')
    {
        $this->format = $format;
        $this->language = $language;
    }


    /**
     * convert from iso date to user format
     * fix empty value and remove H:i:s
     */
    protected function isoDateToHuman($isodate)
    {
        $isodate = str_replace(" 00:00:00", "", $isodate);
        $datetime = \DateTime::createFromFormat( 'Y-m-d', $isodate);
        $timestamp = $datetime->getTimestamp();
        if ($timestamp < 1) {
            $timestamp = 0;
        }
        $isodate = date($this->format, $timestamp);
        return $isodate;
    }

    /**
     * convert from current user format to iso date
     */
    protected function humanDateToIso($humandate)
    {
        $datetime = \DateTime::createFromFormat( $this->format, $humandate);
        $timestamp = $datetime->getTimestamp();
        $humandate = date('Y-m-d', $timestamp);
        return $humandate;
    }

    /**
     * overwrite new value to store a iso date
     */
    public function getNewValue()
    {
        parent::getNewValue();
        $this->new_value = $this->humanDateToIso($this->new_value);
    }


    /**
     * simple translation from php date format to DatePicker format
     * only basic translation of numeric timestamps m/d/Y, d/m/Y, ... 
     * @param $format valid php date format http://www.php.net/manual/it/function.date.php
     * @return string valid datepicker format http://bootstrap-datepicker.readthedocs.org/en/release/options.html#format
     */
    protected function formatToDate()
    {
        $format = $this->format;
        $format = str_replace(
            array('m/d/Y',      'd/m/Y',      'm.d.Y',      'd.m.Y'),
            array('mm/dd/yyyy', 'dd/mm/yyyy', 'mm.dd.yyyy', 'dd.mm.yyyy'),
            $format
        );
        
        return $format;
        
        /*
        d, dd: Numeric date, no leading zero and leading zero, respectively. Eg, 5, 05.
        D, DD: Abbreviated and full weekday names, respectively. Eg, Mon, Monday.
        m, mm: Numeric month, no leading zero and leading zero, respectively. Eg, 7, 07.
        M, MM: Abbreviated and full month names, respectively. Eg, Jan, January
        yy, yyyy: 2- and 4-digit years, respectively. Eg, 12, 2012.
        */
    }
    
    
    public function build()
    {
        $output = "";

        unset($this->attributes['type']);
        if (parent::build() === false) return;

        switch ($this->status)
        {

            case "show":
                if (!isset($this->value))
                {
                    $value = $this->layout['null_label'];
                } else {
                    $value = $this->isoDateToHuman($this->value);
                }
                $output = $value;
                break;

            case "create":
            case "modify":
                if ($this->value != ""){
                    if (!$this->is_refill){
                        $this->value = $this->isoDateToHuman($this->value);
                    }
                }

                Rapyd::css('packages/zofe/rapyd/assets/datepicker/datepicker3.css');
                Rapyd::js('packages/zofe/rapyd/assets/datepicker/bootstrap-datepicker.js');
                if ($this->language != "en")
                {
                    Rapyd::js('packages/zofe/rapyd/assets/datepicker/locales/bootstrap-datepicker.'.$this->language.'.js');                    
                }

                $output  = Form::text($this->name, $this->value,  $this->attributes);
                $output .= Rapyd::script("
                    $(document).ready(function() {
                        $('#".$this->name."').datepicker({
                            format: '{$this->formatToDate()}',
                            language: '{$this->language}',
                            todayBtn: 'linked'
                        });
                    });");

                break;
            case "hidden":
                $output = Form::hidden($this->db_name, $this->value);
                break;
            default:;
        }
        $this->output = $output;
    }

}
