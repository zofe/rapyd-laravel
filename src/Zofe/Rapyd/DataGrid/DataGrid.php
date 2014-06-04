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

        foreach ($this->columns as $column) {
            if (isset($column->orderby)) {
                $column->orderby_asc_url = $this->orderbyLink($column->orderby, 'asc');
                $column->orderby_desc_url = $this->orderbyLink($column->orderby, 'desc');
            }
        }

        foreach ($this->data as $tablerow) {
            $row = array();

            foreach ($this->columns as $column) {

                 if (strpos($column->name, '{{') !== false) {


                    if (is_object($tablerow) && method_exists($tablerow, "getAttributes")) {
                        $array = $tablerow->getAttributes();
                        $array['row'] = $tablerow;

                    } else {
                        $array = (array)$tablerow;
                    }

                    $cell= $this->parser->compileString($column->name, $array);

                } elseif (is_object($tablerow)) {

                    $cell = $tablerow->{$column->name};
                    
                } elseif (is_array($tablerow) && isset($tablerow[$column->name])) {

                    $cell = $tablerow[$column->name];
                } else {
                    $cell = $column->name;
                }
                if ($column->link) {
                    if (is_object($tablerow) && method_exists($tablerow, "getAttributes")) {
                        $array = $tablerow->getAttributes();
                        $array['row'] = $tablerow;
                    } else {
                        $array = (array)$tablerow;
                    }
                    $cell =  '<a href="'.$this->parser->compileString($column->link, $array).'">'.$cell.'</a>';
                }
                if (count($column->actions)>0) {
                    $key = ($column->key != '')?  $column->key : $this->key;
                    $cell = \View::make('rapyd::datagrid.actions', array('uri' => $column->uri, 'id' => $tablerow->getAttribute($key), 'actions' => $column->actions));

                }
                $row[] = $cell;
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
}
