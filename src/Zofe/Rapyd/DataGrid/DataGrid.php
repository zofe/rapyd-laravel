<?php namespace Zofe\Rapyd\DataGrid;


use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View as View;
use Zofe\Rapyd\DataSet as DataSet;
use Zofe\Rapyd\DataGrid\Column as Column;
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

    public function build($view='')
    {
        parent::build();

        foreach ($this->columns as $column) {
            if (isset($column->orderby)) {
                $column->orderby_asc_url = $this->orderbyLink($column->orderby, 'asc');
                $column->orderby_desc_url = $this->orderbyLink($column->orderby, 'desc');
            }
        }
       // $this->rows = $this->data;
        
       
        foreach ($this->data as $tablerow) {
            $row = array(); 
            foreach ($this->columns as $column) {

                //todo: move in a setdata > getvalue to the column class
                if (is_object($tablerow) && property_exists($tablerow, $column->name))
                {
                    $row[] =  $tablerow->{$column->name};
                } elseif(is_array($tablerow) && isset($tablerow[$column->name])) {
                    $row[] = $tablerow[$column->name];
                }
                
                
            }
            $this->rows[] = $row;
        }
        if ($view == '') $view = 'rapyd::datagrid';
        return View::make($view, array('dg' => $this));
    }

    public function getGrid($view='')
    {
        $this->output = $this->build($view);
        return $this->output;
    }

}
