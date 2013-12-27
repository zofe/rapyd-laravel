<?php namespace Zofe\Rapyd\DataGrid;


use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Zofe\Rapyd\DataGrid\Column;
use Zofe\Rapyd\Exceptions\DataGridException;

class DataGrid extends DataSet
{

    protected $fields = array();
    public $columns = array();
    public $rows = array();
    public $output = "";
   
    public function add($name, $label = null, $orderby = false)
    {
        $column = new Column($name, $label, $orderby);
        $this->columns[] = $column;
        return $this;
    }

    public function build($view)
    {
        parent::build();

        foreach ($this->columns as & $column) {
            if (isset($column->orderby)) {
                $column->orderby_asc_url = $this->orderbyLink($column->orderby, 'asc');
                $column->orderby_desc_url = $this->orderbyLink($column->orderby, 'desc');
            }
        }
        $this->rows = $this->data;
        /*foreach ($this->data as $tablerow) {
            foreach ($this->columns as $column) {

                if $column is closure unset($cell);
                $column->setRow($tablerow);
                $cell = get_object_vars($column);
                $cell["value"] = $column->getValue();
                $cell["type"] = $column->column_type;
                $row[] = $cell;
            }
            $this->rows[] = $row;
            unset($row);
        }*/

        return View::make($view, array('dg' => $this));
    }

    public function getGrid($view='rapyd::datagrid')
    {
        $this->output = $this->build($view);
        return $this->output;
    }

}
