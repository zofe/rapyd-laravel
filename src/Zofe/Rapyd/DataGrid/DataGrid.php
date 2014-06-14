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

        /*$this->data->each(function($row)
        {
            if ($row->article_id > 15)  $row->title = "mena";
        });*/
        
        foreach ($this->columns as $column) {
            if (isset($column->orderby)) {
                $column->orderby_asc_url = $this->orderbyLink($column->orderby, 'asc');
                $column->orderby_desc_url = $this->orderbyLink($column->orderby, 'desc');
            }
        }
        
        foreach ($this->data as $tablerow) {

            $row = new Row();

                
            foreach ($this->columns as $column) {

                $cell = new Cell();
                
                if (strpos($column->name, '{{') !== false) {


                    if (is_object($tablerow) && method_exists($tablerow, "getAttributes")) {
                        $array = $tablerow->getAttributes();
                        $array['row'] = $tablerow;

                    } else {
                        $array = (array)$tablerow;
                    }

                     $value = $this->parser->compileString($column->name, $array);

                } elseif (is_object($tablerow)) {

                     $value = $tablerow->{$column->name};
                    
                } elseif (is_array($tablerow) && isset($tablerow[$column->name])) {

                     $value = $tablerow[$column->name];
                } else {
                     $value = $column->name;
                }
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
