<?php namespace Zofe\Rapyd\Controllers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

use Zofe\Rapyd\Facades\Rapyd;
use Zofe\Rapyd\Facades\DataGrid;
use Zofe\Rapyd\Facades\DataForm;
use Zofe\Rapyd\Facades\DataEdit;
use Zofe\Rapyd\Facades\DataFilter;

use Zofe\Rapyd\Models\Article;
use Zofe\Rapyd\Models\Author;
use Zofe\Rapyd\Models\Category;

class TestController extends \Controller
{
    public function getGrid()
    {
        //\Debugbar::disable();
        $grid = DataGrid::source(Article::with('author', 'categories'));

        $grid->add('id','ID', true)->style("width:100px");
        $grid->add('title','Title');
        $grid->add('{{ Str::words($body,4) }}','Body');
        $grid->add('{{ $author->fullname }}','Author', 'author_id');
        $grid->add('{{ implode(", ", $categories->lists("name")) }}','Categories');

        $grid->edit('/rapyd-demo/edit', 'Edit','show|modify');
        $grid->orderBy('id','desc');
        $grid->paginate(10);

        $grid->row(function ($row) {
           if ($row->cell('id')->value == 20) {
               $row->style("background-color:#CCFF66");
           } elseif ($row->cell('id')->value > 15) {
               $row->cell('title')->style("font-weight:bold");
               $row->style("color:#f00");
           }
        });
        $grid->link("/authors/edit","Add author", "TR");
        //return $grid->buildCSV('uploads/filename', 'Y-m-d');
        return  View::make('rapyd::demo.grid', compact('grid'));
    }

    public function getSet()
    {
        $set = DataSet::source(Article::with('author', 'categories'));
        $set->sortable('order',0);
        $set->paginate(9);
        $set->build();

        return  View::make('rapyd::demo.set', compact('set'));
    }

    public function getFilter()
    {
        //\Debugbar::disable();
        $filter = DataFilter::source(Article::with('author','categories'));
        $filter->attributes(array('class'=>'capocchie'));

        //$filter->add('categories.name','Categories','checkboxgroup');
        $filter->add('categories','Categories','checkboxgroup')
            ->options(Category::lists('name', 'id'));
        $filter->add('title','Title', 'text');

        $filter->submit('search');
        $filter->reset('reset');
        //$filter->build();

        $grid = DataGrid::source($filter);
        $grid->attributes(array("class"=>"table table-striped"));
        $grid->add('id','ID', true)->style("width:70px");
        $grid->add('title','Title', true);
        $grid->add('{{ $row->author->fullname }}','Author');
        $grid->add('{{ implode(", ", $categories->lists("name")) }}','Categories');
        $grid->add('{{ date("d/m/Y",strtotime($publication_date)) }}','Date', 'publication_date');
        $grid->add('body','Body');
        $grid->edit('/rapyd-demo/edit', 'Edit','modify|delete');
        $grid->paginate(10);

        return  View::make('rapyd::demo.filtergrid', compact('filter', 'grid'));
    }

    public function getGrid2()
    {
        //\Debugbar::disable();
        $grid = DataGrid::source(DB::table('demo_articles'));

        $grid->add('id','ID', true)->style("width:100px");
        $grid->add('row.title','Title');
        $grid->edit('/rapyd-demo/edit', 'Edit','show|modify');
        $grid->orderBy('id','desc');
        $grid->paginate(10);

        return  View::make('rapyd::demo.grid', compact('grid'));
    }

    public function getQs()
    {
        dd(Rapyd::url('mena')->append('edit',3)->get());
    }

    public function anyEdit()
    {
        if (Input::get('do_delete')==1) return  "not the first";

        $edit = DataEdit::source(new Article());
        $edit->label('Edit Article');
        $edit->link("rapyd-demo/filter","Articles", "TR")->back();
        $edit->add('title','Title', 'text')->rule('required|min:5');

        $edit->add('body','Body', 'redactor');
        $edit->add('detail.note','Note', 'textarea')->attributes(array('rows'=>2));
        $edit->add('detail.note_tags','Note tags', 'file')->move('uploads/demo/');
        $edit->add('author_id','Author','select')->options(Author::lists("firstname", "id"));
        $edit->add('publication_date','Date','date')->format('d/m/Y', 'it');
        $edit->add('photo','Photo', 'image')->move('uploads/demo/')->fit(240, 160)->preview(120,80);
        $edit->add('public','Public','checkbox');
        $edit->add('categories.name','Categories','tags');

        return $edit->view('rapyd::demo.edit', compact('edit'));

    }

    public function anyStyledform()
    {
        $form = DataForm::source(Article::find(1));

        $form->add('title','Title', 'text')->rule('required|min:5');
        $form->add('body','Body', 'redactor');
        $form->add('categories.name','Categories','tags');
        $form->add('photo','Photo', 'image')->rule('required')->move('uploads/demo/')->fit(240, 160)->preview(120,80);
        $form->submit('Save');

        $form->saved(function () use ($form) {
            $form->message("ok record saved");
            $form->link("/rapyd-demo/styledform","back to the form");
        });
        $form->build();

        return View::make('rapyd::demo.test', compact('form'));
    }

    public function anyForm()
    {
        $form = DataForm::source(Article::find(37));

        $form->add('title','Title', 'text')->rule('required|min:5');
        $form->add('body','Body', 'redactor');

        //belongs to
        $form->add('author_id','Author','select')->options(Author::lists('firstname', 'id'));

        //belongs to many (field name must be the relation name)
        $form->add('categories','Categories','checkboxgroup')->options(Category::lists('name', 'id'));
        //$form->add('photo','Photo', 'image')->moveDeferred('uploads/demo/{{ $id }}')->fit(210, 160)->preview(120,80);
        $form->add('detail.note','Note', 'file')->moveDeferred('uploads/demo/{{ $id }}');
        $form->add('public','Public','checkbox');

        $form->submit('Save');

        $form->saved(function () use ($form) {
            $form->message("ok record saved");
            $form->link("/test/form","back to the form");
        });

        return View::make('rapyd::demo.form', compact('form'));
    }
}
