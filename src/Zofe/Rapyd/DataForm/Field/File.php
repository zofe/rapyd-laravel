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
    protected $unlink_file = true;

    public function rule($rule)
    {
        //we should consider rules only on upload
        if (Input::hasFile($this->name)) 
        {
            parent::rule($rule);
        }
        return $this;
    }
    
    public function autoUpdate($save = false)
    {

        $this->getValue();

        if ((($this->action == "update") || ($this->action == "insert"))) {
            
            
            if (Input::hasFile($this->name))
            {
                $this->file = Input::file($this->name);
                
                $filename = ($this->filename!='') ?  $this->filename : $this->file->getClientOriginalName();
                $filename = $this->sanitize_filename($filename);
                
                if ($this->unlink_file) {
                    @unlink(public_path().'/'.$this->path.$this->old_value);
                }
                
                $uploaded = $this->file->move($this->path, $filename);
                $this->saved = $this->path. $filename;
                
                if ($uploaded && is_object($this->model) && isset($this->db_name)) {

                    $this->new_value = $filename;
                    
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
                
                
            } else {


                if (Input::get($this->name . "_remove")) 
                {

                    if ($this->unlink_file) {
                        @unlink(public_path().'/'.$this->path.$this->old_value);
                    }
                    if (isset($this->model) && $this->model->offsetExists($this->db_name)) {
                        $this->model->setAttribute($this->db_name, null);
                    }
                    if (is_a($this->relation, 'Illuminate\Database\Eloquent\Relations\HasOne')) {

                        $this->new_value = null;
                        $this->updateRelations();
                        
                    }
                    if ($save) {
                        return $this->model->save();
                    }
                }
                
            }
        }
        return true;
    }
    
    protected function sanitize_filename($filename)
    {
        $filename = preg_replace('/\s+/', '_', $filename);
        $filename = preg_replace('/[^a-zA-Z0-9\._-]/', '', $filename);
        $ext = strtolower(substr(strrchr($filename, '.'), 1));
        $name = rtrim($filename, strrchr($filename, '.'));
        $i = 0;
        $finalname = $name;
        while (file_exists(public_path().'/'.$this->path . $finalname. '.'.$ext))
        {
            $i++;
            $finalname = $name . (string)$i;
        }
        return $finalname. '.'.$ext;
    }
    
    
    /**
     * move uploaded file to the destination path, optionally raname it
     * name param can be passed also as blade syntax
     * unlinkable  is a bool, tell to the field to unlink or not if "remove" is checked
     * @param $path
     * @param string $name
     * @param bool $unlinkable
     * @return $this
     */
    public function move($path, $name = '', $unlinkable = true)
    {
        $this->path = rtrim($path,"/")."/";
        $this->filename = $this->parseString($name);
        $this->unlink_file = $unlinkable;
        return $this;
    }

    public function build()
    {
        $output = "";
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
                $output = "<div class='help-block'>" . $output . "&nbsp;</div>";
                break;

            case "create":
            case "modify":

                if ($this->old_value){
                    $output .= link_to($this->path.$this->value, $this->value). "&nbsp;";
                    $output .= Form::checkbox($this->name.'_remove', 1, (bool)Input::get($this->name.'_remove'))."<br/>\n";
                }
                $output .= Form::file($this->name, $this->attributes);                    
                break;

            case "hidden":
                $output = Form::hidden($this->name, $this->value);
                break;

            default:;
        }
        $this->output = "\n" . $output . "\n" . $this->extra_output . "\n";
    }

}
