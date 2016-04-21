<?php

namespace Zofe\Rapyd\DataTree;

use Baum\Node;
use Illuminate\Support\Collection;
use Collective\Html\FormFacade as Form;
use Zofe\Rapyd\DataGrid\Cell;
use Zofe\Rapyd\DataGrid\DataGrid;
use Zofe\Rapyd\DataGrid\Row;
use Zofe\Rapyd\Persistence;

class DataTree extends DataGrid
{
    public $attributes = array("class" => "datatree", "method" => "POST");
    public $data;
    public $source;

    /**
     * @var Node
     */
    protected $maxDepth = 5;
    protected $group = 0;
    protected $name = 'items';
    protected $onChange;
    protected $autoSave;

    public static function source($source)
    {
        if (!$source instanceof Node) {
            throw new \InvalidArgumentException('DataTree only works with Baum\Node instances');
        }
        $instance = parent::source($source);
        $instance->attr('data-instance-id', spl_object_hash($instance));
        \Rapyd::css('datatree/css/datatree.css');
        \Rapyd::js('datatree/js/nestable/jquery.nestable.js');
        \Rapyd::js('datatree/js/datatree.js');
        return $instance;
    }

    public function build($view = '')
    {
        $this->initJsWidget();

        $view == '' and $view = 'rapyd::datatree';

        $this->open = Form::open($this->attributes);
        $this->close = Form::hidden('save', 1) . Form::close();

        // we save on POST and only if the widget's own input variable is filled
        // because sometimes we have more than a tree widget on the same page
        // but just one save

        if (\Request::method() == 'POST' && \Input::get($this->name)) {
            $this->lockAndSave();
        }

        $this->data = $this->source->find($this->source->getKey())->getDescendants()->toHierarchy();
        Persistence::save();
        $this->rows = $this->makeRowsRecursive($this->data);

        $this->output = \View::make($view, array('dg' => $this, 'buttons' => $this->button_container, 'label' => $this->label))->render();
        return $this->output;
    }

    protected function makeRowsRecursive($data, $depth = 0)
    {
        $rows = [];
        foreach ($data as $item) {
            $row = $this->makeRow($item);
            $row->children = $this->makeRowsRecursive($item['children'], $depth + 1);
            $rows[] = $row;
        }
        return $rows;
    }

    protected function lockAndSave()
    {
        // use a transaction to prevent concurrent writes and, hopefully,
        // improve the performance
        \DB::transaction(function () {
            // lets lock all the table and get away with it, nested sets
            // are so fragile that this really seems the better option
            \DB::table($this->source->getTable())->select($this->source->getKeyName())->lockForUpdate()->get();

            // rebuild the tree and save all the changed nodes
            $this->performSave();
        });
    }

    protected function performSave()
    {
        // there are two situation here, an orthodox form submittal and a ajax one:
        // - the orthodox will send a json string
        // - the ajax version will send an array

        $var = \Input::get($this->name);
        if (is_string($var)) {
            $var = json_decode($var, true);
        }

        if (!$var) {

            // the client submitted an empty tree, there's nothing to move

            return;
        }

        $movements = [];
        $subtreeId = $this->source->getKey();

        // We now invert the order of movements and group/sort them by
        // depth. This is done to avoid the situation where a node wants
        // to become the descendant of one of its own descendants.
        // This kind of sort will prevent the issue ensuring all the descendants
        // are moved first.

        $this->sortMovementsByDepth($var, $movements, $subtreeId);
        ksort($movements);
        $movements = call_user_func_array('array_merge', $movements);
        $movements = Collection::make($movements)->keyBy('id');

        /** @var \Baum\Extensions\Eloquent\Collection $nodes */
        $root = $this->source->getRoot();

        // store depth and left ot the root, to build upon when
        // we will rebuild the tree.

        $rootDepth = $root->depth;
        $rootLeft = $root->lft;

        // now we read the entire tree. We need to do that because
        // of the nested set way workings: Baum provides handy methods
        // to move the nodes, but they trigger an awful lot of queries.
        // We'd rather read the whole tree once instead, and perform all
        // the calculations in-memory.

        $nodes = $root->getDescendantsAndSelf([
            $this->source->getKeyName(),
            'lft', 'rgt', 'depth', 'parent_id'
        ]);

        // the ids of all the moved elements

        $movedIds = $movements->keys()->toArray();

        // index the elements by primary key for speedy retrieval

        $dictionary = $nodes->getDictionary();

        // the elements of the bigger tree that did not change their
        // parent_id

        $unmoved = new \Baum\Extensions\Eloquent\Collection();

        foreach ($dictionary as $n) {
            if (!in_array($n->getKey(), $movedIds)) {
                $unmoved[] = $n;
            }
        }

        // the elements that were moved to a different parent

        $moved = new \Baum\Extensions\Eloquent\Collection();

        foreach ($movedIds as $i) {
            $moved[] = $dictionary[$i];
        }

        // this is the column that Baum uses to order the tree
        // the default is `lft`

        $orderColumn = $this->source->getOrderColumnName();

        // we backup the order column, because we have to mess with
        // it later and we want to be able to restore it so we can
        // still use `$node->isDirty()` to see if the the node needs
        // to be updated or not.

        foreach ($dictionary as $n) {
            $n->__order = $n->$orderColumn;
        }

        // what now? We put all the moved nodes before the rest of the
        // tree. This way they'll be put before their unmoved siblings
        // shall they exist.

        $orderedNodes = $moved->merge($unmoved);

        // shady stuff going on here: Baum collections build the hierarchy
        // based on parent id AND the order column (lft). We thus update the
        // order column with an incremental value to be sure the siblings
        // order is preserved.

        $order = 1;
        foreach ($orderedNodes as $n) {
            $n->$orderColumn = $order++;
            if (isset($movements[$n->getKey()])) {
                // is the parent_id changed? If so, let's update it
                $n->parent_id = $movements[$n->getKey()]['parent_id'];
            }
        }

        // let Baum build the new tree

        $newTree = $orderedNodes->toHierarchy();

        // lets restore the order column and delete the previous backup,
        // so we can use `$node->isDirty` later

        foreach ($dictionary as $n) {
            $n->$orderColumn = $n->__order;
            unset($n->__order);
        }

        // if everything worked correctly we should have a nested collection
        // with only one root element. The root ID should be unchanged.

        $newRoot = $newTree->first();
        if ($newRoot->getKey() != $root->getKey() || count($newTree) != 1) {
            throw new \LogicException("Invalid tree");
        }

        // now we take the new tree and recursively recalculate the left, right
        // and depth fields.

        $left = $rootLeft - 1;
        $depth = $rootDepth;
        $reindex = function ($tree, $reindex, $depth) use (&$left) {
            foreach ($tree as $node) {
                $left++;
                $node->lft = $left;
                $node->depth = $depth;
                $reindex($node->getRelation('children'), $reindex, $depth + 1);
                $left++;
                $node->rgt = $left;
            }
        };
        $reindex($newTree, $reindex, $depth);

        // compute the changes and only save the changed ones!

        $bulk = [];
        foreach ($dictionary as $n) {
            if ($n->isDirty()) {
                $bulk[$n->getKey()] = [
                    'lft' => $n->lft,
                    'rgt' => $n->rgt,
                    'depth' => $n->depth,
                    'parent_id' => $n->parent_id
                ];
            }
        }
        foreach ($bulk as $id => $fields) {
            \DB::table($this->source->getTable())
                ->where($this->source->getKeyName(), $id)
                ->update($fields);
        }
    }

    protected function sortMovementsByDepth($tree, &$children, $id, $depth = 1)
    {
        foreach ($tree as $node) {
            if (!empty($node['children'])) {
                $this->sortMovementsByDepth($node['children'], $children, $node['id'], $depth + 1);
            }
            $new = $node;
            $new['parent_id'] = $id;
            unset($new['children']);

            // note that we don't trust the client determined depth
            // at all.
            $children[$depth][] = $new;
        }
    }

    protected function makeRow($item)
    {
        $row = new Row($item);

        $row->children = array();

        $row->attributes(array(
            'class' => 'datatree-item',
            'data-id' => $row->data->getKey()
        ));
        $index = 0;
        foreach ($this->columns as $column) {
            $index++;

            $cell = new Cell($column->name);
            $attrs = array();
            $attrs['data-field-name'] = strpos($column->name,
                '{{') === false ? $column->name : '_blade_' . $index;
            $cell->attributes($attrs);

            $sanitize = (count($column->filters) || $column->cell_callable) ? false : true;
            $value = $this->getCellValue($column, $item, $sanitize);
            $cell->value($value);
            $cell->parseFilters($column->filters);
            if ($column->cell_callable) {
                $callable = $column->cell_callable;
                $cell->value($callable($cell->value));
            }

            $row->add($cell);
        }

        if (count($this->row_callable)) {
            foreach ($this->row_callable as $callable) {
                $callable($row);
            }
        }
        return $row;
    }

    /**
     * Add a submit button to allow saving the current order of the nodes
     *
     * @param string $name
     * @param string $position
     * @param array $options
     *
     * @return $this
     */
    public function submit($name, $position = "BL", $options = array())
    {
        $options = array_merge(array("class" => "btn btn-primary"), $options);
        $this->button_container[$position][] = Form::submit($name, $options);

        return $this;
    }

    /**
     * Get/Set maximum depth of the tree. Nesting beyond this limit will not
     * be allowed.
     *
     * Defaults to 5.
     *
     * @param integer $value
     * @return $this|int
     */
    public function maxDepth($value = null)
    {
        if (func_num_args()) {
            $this->maxDepth = $value;
            return $this;
        }
        return $this->maxDepth;
    }

    /**
     * Get/Set a custom javascript string to be executed at each order change.
     *
     * @param string $value
     * @return $this|string
     */
    public function onChange($value = null)
    {
        if (func_num_args()) {
            $this->onChange = $value;
            return $this;
        }
        return $this->onChange;
    }

    /**
     * Enable/Disable automatic ajax-saving at each change
     * @param null $value
     * @return $this
     */

    public function autoSave($value = null)
    {
        if (func_num_args()) {
            $this->autoSave = $value;
            return $this;
        }
        return $this->autoSave;
    }

    /**
     * Set the group of the DataTree. Multiple DataTrees of the same
     * group will be able to exchange items.
     *
     * @param null $value
     * @return $this|int
     */

    public function group($value = null)
    {
        if (func_num_args()) {
            $this->group = $value;
            return $this;
        }
        return $this->group;
    }

    /**
     * Get/Set the input name of the DataTree data. You have to change
     * this if you display multiple datatrees in the same page.
     *
     * @param null $value
     * @return $this|string
     */
    public function name($value = null)
    {
        if (func_num_args()) {
            $this->name = $value;
            return $this;
        }
        return $this->name;
    }

    // inline script

    public function initJsWidget()
    {
        $onChange = $this->onChange ?: '';
        $ajax = $this->autoSave ? '$.post("", {"'.$this->name().'": $(this).nestable("serialize")});' : '';

        $script = '

$("[data-instance-id=\\"' . $this->attributes['data-instance-id'] . '\\"]").each(function(){
 var root = $(this);
 var form = root.find(".datatree-values");
 root.find(".datatree-inner-wrapper").nestable({
        listNodeName: "ol",
        itemNodeName: "li",
        rootClass: "datatree-inner-wrapper",
        listClass: "datatree-list",
        itemClass: "datatree-item",
        dragClass: "datatree-dragel",
        handleClass: "datatree-handle",
        collapsedClass: "datatree-collapsed",
        placeClass: "datatree-placeholder",
        noDragClass: "datatree-nodrag",
        emptyClass: "datatree-empty",
        expandBtnHTML: "<button data-action=\"expand\" type=\"button\">Expand</button>",
        collapseBtnHTML: "<button data-action=\"collapse\" type=\"button\">Collapse</button>",
        group: ' . $this->group . ',
        maxDepth: ' . $this->maxDepth . ',
        threshold: 20
    }).on("mousedown", "a", function (e) {
        e.stopImmediatePropagation();
    }).each(function () {
        var ol = $(this).children(".datatree-list");
        if (ol.length) rapyd.datatree.updateDepth(ol);
        rapyd.datatree.updateForm($(this), form, "' . $this->name . '");
    }).on("change", function () {
        var ol = $(this).children(".datatree-list");
        if (ol.length) rapyd.datatree.updateDepth(ol);
        var updated = rapyd.datatree.updateForm($(this), form, "' . $this->name . '");
        '.$ajax.'
        '.$onChange.'
    });
    $(".datatree").submit(function () {
        var action = $(this).attr("action") || document.location.href;
        //return false;
    });
 });
        ';

        \Rapyd::script($script);
    }
}
