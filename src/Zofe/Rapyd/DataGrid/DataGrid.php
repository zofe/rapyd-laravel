<?php namespace Zofe\Rapyd\DataGrid;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Zofe\Rapyd\DataSet as DataSet;
use Zofe\Rapyd\Exceptions\DataGridException;
use Zofe\Rapyd\Persistence;

class DataGrid extends DataSet
{

    protected $fields = array();
    /** @var Column[]  */
    public $columns = array();
    public $headers = array();
    public $rows = array();
    public $output = "";
    public $attributes = array("class" => "table");
    protected $row_callable = false;


    /**
     * @param string $name
     * @param string $label
     * @param bool $orderby
     *
     * @return Column
     */
    public function add($name, $label = null, $orderby = false)
    {
        $column = new Column($name, $label, $orderby);
        $this->columns[$name] = $column;
        $this->headers[] = $label;
        return $column;
    }

    //todo: like "field" for DataForm, should be nice to work with "cell" as instance and "row" as collection of cells
    public function build($view = '')
    {
        ($view == '') and $view = 'rapyd::datagrid';
        parent::build();

        Persistence::save();
        
        foreach ($this->data as $tablerow) {

            $row = new Row($tablerow);

                
            foreach ($this->columns as $column) {

                $cell = new Cell($column->name);
                
                $value = $this->getCellValue($column, $tablerow);
               
                $cell->value($value);
                $row->add($cell);
            }

            if ($this->row_callable) {
                $callable = $this->row_callable;
                $callable($row);
            }            
            $this->rows[] = $row;
        }

        return \View::make($view, array('dg' => $this, 'buttons'=>$this->button_container, 'label'=>$this->label));
    }


    public function buildCSV($file = '', $timestamp = '') 
    {
        parent::build();
        $segments = \Request::segments();

        $filename = ($file != '') ? basename($file, '.csv') : end($segments);
        $filename = preg_replace('/[^0-9a-z\._-]/i', '',$filename);
        $filename .= ($timestamp != "") ? date($timestamp).".csv" : ".csv";
        
        $save = (bool)strpos($file,"/");

        if ($save)
        {
            $handle = fopen(dirname($file)."/".$filename, 'w');
            
        } else {

            
            $headers  = array(
                'Content-Type' => 'text/csv',
                'Pragma'=>'no-cache',
                '"Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Content-Disposition' => 'attachment; filename="' . $filename.'"');

            $handle = fopen('php://output', 'w');
            ob_start();
        }


        fputs($handle, '"'.implode('";"', $this->headers) .'"'."\n");
        
        foreach ($this->data as $tablerow) 
        {
            $row = new Row($tablerow);

            foreach ($this->columns as $column) {

                $cell = new Cell($column->name);
                $value =  str_replace('"', '""',str_replace(PHP_EOL, '', strip_tags($this->getCellValue($column, $tablerow))));
                $cell->value($value);
                $row->add($cell);
            }

            if ($this->row_callable) {
                $callable = $this->row_callable;
                $callable($row);
            }

            fputs($handle, '"' . implode('";"', $row->toArray()) . '"'."\n");
        }
       
        fclose($handle);
        if ($save)
        {
            //redirect, boolean or filename?
        } else {
            $output = ob_get_clean();
            return \Response::make(rtrim($output, "\n"), 200, $headers);
        }
    }


    protected function getCellValue($column, $tablerow)
    {
        //blade
        if (strpos($column->name, '{{') !== false) 
        {

            if (is_object($tablerow) && method_exists($tablerow, "getAttributes")) {
                $fields = $tablerow->getAttributes();
                $relations = $tablerow->getRelations();
                $array = array_merge($fields, $relations) ;
                
                $array['row'] = $tablerow;

            } else {
                $array = (array)$tablerow;
            }

            $value = $this->parser->compileString($column->name, $array);

        //eager loading smart syntax  relation.field
        } elseif (preg_match('#^([a-z0-9_-]+)(?:\.([a-z0-9_-]+)){1,3}$#i',$column->name, $matches)
            && is_object($tablerow) ) 
        {

            if (count($matches) > 3 )
            {
                //todo (if possible) handle third level relations  
                dd($matches);
                $value = @$tablerow->$matches[1]->$matches[2]->$matches[3];
            } else {
               
                $value = @$tablerow->$matches[1]->$matches[2];
                $value = $this->sanitize($value);
            }
            

        
        //fieldname in a collection
        } elseif (is_object($tablerow)) {

            $value = $this->sanitize($tablerow->{$column->name});

        //fieldname in an array
        } elseif (is_array($tablerow) && isset($tablerow[$column->name])) {

            $value = $tablerow[$column->name];
        
        //none found, cell will have the column name
        } else {
            $value = $column->name;
        }
        
        //decorators, should be moved in another method
        if ($column->link) {
            if (is_object($tablerow) && method_exists($tablerow, "getAttributes")) {
                $array = $tablerow->getAttributes();
                $array['row'] = $tablerow;
            } else {
                $array = (array)$tablerow;
            }
            $value =  '<a href="'.$this->parser->compileString($column->link, $array).'">'.$value.'</a>';
        }
        if (count($column->actions)>0) {
            $key = ($column->key != '')?  $column->key : $this->key;
            $value = \View::make('rapyd::datagrid.actions', array('uri' => $column->uri, 'id' => $tablerow->getAttribute($key), 'actions' => $column->actions));

        }

        return $value;
    }

    
    public function getGrid($view = '')
    {
        $this->output = $this->build($view)->render();
        return $this->output;
    }

    public function __toString()
    {
        if ($this->output == "")
        {
           try {
                $this->getGrid();
           }
           //to avoid the error "toString() must not throw an exception" (PHP limitation)
           //just return error as string
           catch (\Exception $e) {
               return $e->getMessage(). " Line ".$e->getLine();
           }

        }
        return $this->output;
    }

    public function edit($uri, $label='Edit', $actions='show|modify|delete', $key = '')
    {
        return $this->add('mena', $label)->actions($uri, explode('|', $actions))->key($key);
    }

    public function getColumn($column_name)
    {
        if (isset($this->columns[$column_name])) {
            return $this->columns[$column_name];
        }
    }

    public function addActions($uri, $label='Edit', $actions='show|modify|delete', $key = '')
    {

        return $this->edit($uri, $label, $actions, $key);
    }

    
    public function row( \Closure $callable)
    {
        $this->row_callable = $callable;
        return $this;
    }

    protected function sanitize($string)
    {
        return \Str::words(nl2br(htmlspecialchars($string)), 30);
    }

}
