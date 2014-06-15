<?php namespace Zofe\Rapyd\DataGrid;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Zofe\Rapyd\DataSet as DataSet;
use Zofe\Rapyd\Exceptions\DataGridException;

class DataGrid extends DataSet
{

    protected $fields = array();
    /** @var Column[]  */
    public $columns = array();
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
        return $column;
    }

    //todo: like "field" for DataForm, should be nice to work with "cell" as instance and "row" as collection of cells
    public function build($view = '')
    {
        ($view == '') and $view = 'rapyd::datagrid';
        parent::build();

        foreach ($this->data as $tablerow) {

            $row = new Row();

                
            foreach ($this->columns as $column) {

                $cell = new Cell();
                
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
                dd($matches);
                $value = @$tablerow->$matches[1]->$matches[2]->$matches[3];
            } else {
               
                $value = @$tablerow->$matches[1]->$matches[2];
            }
            

        
        //fieldname in a collection
        } elseif (is_object($tablerow)) {

            $value = $tablerow->{$column->name};

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


    public function attributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }


    public function buildAttributes()
    {
        if (is_string($this->attributes))
            return $this->attributes;

        if (count($this->attributes)<1)
            return "";

        $compiled = '';
        foreach($this->attributes as $key => $val)
        {
            $compiled .= ' '.$key.'="'.$val.'"';
        }
        return $compiled;
    }
}
