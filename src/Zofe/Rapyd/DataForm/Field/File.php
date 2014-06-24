<?php

namespace Zofe\Rapyd\DataForm\Field;

use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Input;

class File extends Field
{

    public $type = "file";
    protected $file = null;
    protected $path = 'uploads/';
    protected $filename = '';
    protected $saved = '';
    
    public function autoUpdate($save = false)
    {
        $this->getValue();

        if ((($this->action == "update") || ($this->action == "insert"))) {
            
            
            if (Input::hasFile($this->name))
            {
                $this->file = Input::file($this->name);
                
                $filename = ($this->filename!='') ?  $this->filename : $this->file->getClientOriginalName();
 
                $uploaded = $this->file->move($this->path, $filename);
                $this->saved = $this->path. $filename;
                
                if ($uploaded && is_object($this->model) && isset($this->db_name)) {

                    if (!Schema::hasColumn($this->model->getTable(), $this->db_name))
                    {
                         return true;
                    }

                    $this->new_value = $filename;
    
                    if (isset($this->new_value)) {
                        $this->model->setAttribute($this->db_name, $this->new_value);
                    } else {
                        $this->model->setAttribute($this->db_name, $this->value);
                    }
                    if ($save) {
                        return $this->model->save();
                    }
                }
                
                
            }
        }
        return true;
    }
    
    public function move($path, $name='')
    {   
        $this->path = $path;
        $this->filename = $name;
        return $this;
    }

    public function build()
    {
        $output = "";
        $this->attributes["class"] = "form-control";
        if (parent::build() === false)
            return;

        switch ($this->status) {
            case "disabled":
            case "show":

                if ($this->type == 'hidden' || $this->value == "") {
                    $output = "";
                } elseif ((!isset($this->value))) {
                    $output = $this->layout['null_label'];
                } else {
                    $output = nl2br(htmlspecialchars($this->value));
                }
                $output = "<div class='help-block'>" . $output . "</div>";
                break;

            case "create":
            case "modify":
                $output = Form::file($this->db_name, $this->attributes);
                break;

            case "hidden":
                $output = Form::hidden($this->db_name, $this->value);
                break;

            default:;
        }
        $this->output = "\n" . $output . "\n" . $this->extra_output . "\n";
    }

}
