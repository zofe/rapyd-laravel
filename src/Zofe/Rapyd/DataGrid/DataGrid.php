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

    private $uri = null;

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

                if ($column->name == '__actions' and $this->uri) {
                    $cell = \View::make('rapyd::datagrid.actions', array('uri' => $this->uri, 'id' => $tablerow->id))->renderSections();
                    $cell = $cell['actions'];
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
            $this->getGrid();
        }
        return $this->output;
    }

    public function addActions($base_uri)
    {
        $this->uri = $base_uri;
        $this->add('__actions', 'Actions');
    }

    public function getColumn($column_name)
    {
        if (isset($this->columns[$column_name])) {
            return $this->columns[$column_name];
        }
    }
}
