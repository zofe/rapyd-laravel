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

    /**
     * @param string $name
     * @param string $label
     * @param bool $orderby
     *
     * @return $this
     */
    public function add($name, $label = null, $orderby = false)
    {
        $column = new Column($name, $label, $orderby);
        $this->columns[] = $column;
        return $this;
    }

    public function build($view = '')
    {
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
                $cell = '';
         
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
                    $cell =  '<a href="'.$this->parser->compileString($column->link, (array)$tablerow).'">'.$cell.'</a>'; 
                }
                
                $row[] = $cell;
            }
            $this->rows[] = $row;
        }

        if ($view == '')
            $view = 'rapyd::datagrid';
        return View::make($view, array('dg' => $this, 'buttons'=>$this->button_container, 'label'=>$this->label));
    }

    public function getGrid($view = '')
    {
        $this->output = $this->build($view);
        return $this->output;
    }

}
