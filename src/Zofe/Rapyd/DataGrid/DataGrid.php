<?php namespace Zofe\Rapyd\DataGrid;


use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Zofe\Rapyd\DataGrid\Column as Column;
use Zofe\Rapyd\Exceptions\DataGridException;

class DataGrid extends DataSet
{

    protected $fields = array();
    public $columns = array();
    public $rows = array();
    public $output = "";
    public $row_as = null;

    
    public function setColumn($name, $label = null, $orderby = false)
    {
        $config['row_as'] = $this->row_as;
        $config['pattern'] = $name;
        $config['label'] = ($label != "") ? $label : $name;
        $config['orderby'] = $orderby;

        $column = new Column($config);
        $this->columns[] = $column;
        return $this;
    }
    
    public function add($name, $label = null, $orderby = false)
    {
        return $this->setColumn($name, $label, $orderby);
    }

    public function buildGrid($view='rapyd::datagrid')
    {
        parent::build();

        foreach ($this->columns as & $column) {
            if (isset($column->orderby)) {
                $column->orderby_asc_url = $this->orderbyLink($column->orderby, 'asc');
                $column->orderby_desc_url = $this->orderbyLink($column->orderby, 'desc');
            }
        }
        
        foreach ($this->data as $tablerow) {
            unset($row);
            foreach ($this->columns as $column) {

                unset($cell);
                $column->setRow($tablerow);
                $cell = get_object_vars($column);
                $cell["value"] = $column->getValue();
                $cell["type"] = $column->column_type;
                $row[] = $cell;
            }
            $this->rows[] = $row;
        }

        return View::make($view, array('dg' => $this));
    }

    public function getGrid($type = 'Grid')
    {
        $this->build($type);
        return $this->output;
    }

}
