<?php namespace Zofe\Rapyd\Controllers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use Zofe\Rapyd\Facades\DataSet;
use Zofe\Rapyd\Facades\DataGrid;
use Zofe\Rapyd\Facades\DataForm;
use Zofe\Rapyd\Facades\DataEdit;
use Zofe\Rapyd\Facades\DataFilter;
use Zofe\Rapyd\Facades\Documenter;

use Zofe\Rapyd\Models\Article;
use Zofe\Rapyd\Models\Author;
use Zofe\Rapyd\Models\Category;


class DemoController extends \Controller {

    public function getIndex()
    {
        $is_rapyd = (Request::server('HTTP_HOST') == "www.rapyd.com") ? true : false;
        return  View::make('rapyd::demo.demo', compact('is_rapyd'));
    }

    public function getModels()
    {
        return  View::make('rapyd::demo.models');
    }

    
    public function getSchema()
    {
        Schema::dropIfExists("demo_users");
        Schema::dropIfExists("demo_articles");
        Schema::dropIfExists("demo_comments");
        Schema::dropIfExists("demo_categories");
        Schema::dropIfExists("demo_article_category");

        //create all tables
        Schema::table("demo_users", function ($table) {
            $table->create();
            $table->increments('user_id');
            $table->string('firstname', 100);
            $table->string('lastname', 100);
            $table->timestamps();
        });
        Schema::table("demo_articles", function ($table) {
            $table->create();
            $table->increments('article_id');
            $table->integer('author_id')->unsigned();
            $table->string('title', 200);
            $table->text('body');
            $table->boolean('public');
            $table->timestamps();
        });
        Schema::table("demo_comments", function ($table) {
            $table->create();
            $table->increments('comment_id');
            $table->integer('user_id')->unsigned();
            $table->integer('article_id')->unsigned();
            $table->text('comment');
            $table->timestamps();
        });
        Schema::table("demo_categories", function ($table) {
            $table->create();
            $table->increments('category_id');
            $table->string('name', 100);
            $table->timestamps();
        });
        Schema::table("demo_article_category", function ($table) {
            $table->create();
            $table->integer('article_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->timestamps();
        });

        //populate all tables
        $users = DB::table('demo_users');
        $users->insert(array('firstname' => 'Jhon', 'lastname' => 'Doe'));
        $users->insert(array('firstname' => 'Jane', 'lastname' => 'Doe'));

        $categories = DB::table('demo_categories');
        for ($i=1; $i<=5; $i++){
            $categories->insert(array(
                    'name' => 'Category '.$i)
            );
        }
        $articles = DB::table('demo_articles');
        for ($i=1; $i<=20; $i++){
            $articles->insert(array(
                    'author_id' => rand(1,2),
                    'title' => 'Article '.$i,
                    'body' => 'Body of article '.$i,
                    'public' => true,)
            );
        }
        $comments =  DB::table('demo_article_category');
        $comments->insert(array(
                'article_id' => 1,
                'category_id' => 1)
        );


        $comments =  DB::table('demo_comments');
        $comments->insert(array(
                'user_id' => 1,
                'article_id' => 2,
                'comment' => 'Comment for Article 2')
        );
        
        return \Redirect::to("rapyd-demo")->with("message", "Database filled");
    }



    public function getSet()
    {
        $set = DataSet::source(DB::table('demo_articles')->select('title'))->orderBy('title','DESC')->paginate(10)->getSet();
        return  View::make('rapyd::demo.set', compact('set'));
    }

    public function getGrid()
    {
        $grid = DataGrid::source(Article::with('author', 'categories'));
        $grid->add('article_id','ID', true)->attributes(array("style"=>"width:100px"));
        $grid->add('title','Title', true);
        $grid->add('{{ $row->author->firstname }}','Author', 'author_id');
        $grid->add('body','Body');
        $grid->edit('/rapyd-demo/edit', 'Edit','show|modify');
        $grid->paginate(10);
        $grid->orderBy('article_id','desc');

        return  View::make('rapyd::demo.grid', compact('grid'));
    }

    public function getFilter()
    {
        $filter = DataFilter::source(Article::with('author'));
        $filter->add('title','Title', 'text');
        $filter->add('author_id','Author','select')
               ->option("","")
               ->options(Author::lists("firstname", "user_id"));
        $filter->submit('search');
        $filter->reset('reset');

        $grid = DataGrid::source($filter);
        $grid->add('article_id','ID', true);
        $grid->add('title','Title', true);
        $grid->add('{{ $row->author->firstname }}','Author');
        $grid->add('body','Body');
        $grid->edit('/rapyd-demo/edit', 'Edit','modify');
        $grid->paginate(10);
        return  View::make('rapyd::demo.filtergrid', compact('filter', 'grid'));
    }

    public function anyForm()
    {
        $form = DataForm::source(Article::find(1));

        $form->add('title','Title', 'text')->rule('required|min:5');
        $form->add('body','Body', 'redactor');

        //belongs to  
        $form->add('author_id','Author','select')
            ->options(Author::lists('firstname', 'user_id'));

        //belongs to many (field name must be the relation name)
        $form->add('categories','Categories','checkboxgroup')
            ->options(Category::lists('name', 'category_id'));

        $form->add('public','Public','checkbox');
        $form->submit('Save');
        
        $form->saved(function() use ($form)
        {
            $form->message("ok record saved");
            $form->link("/rapyd-demo/form","back to the form");
        });

        return View::make('rapyd::demo.form', compact('form'));
    }

    public function anyEdit()
    {
        if (Input::get('do_delete')==1) return  "not the first";

        $edit = DataEdit::source(new Article);
        $edit->link("rapyd-demo/filter","Articles", "TR");
        $edit->add('title','Title', 'text')->rule('required|min:5');
        $edit->add('body','Body', 'textarea');
        $edit->add('author_id','Author','select')
            ->options(Author::lists("firstname", "user_id"));
        $edit->add('public','Public','checkbox');
        $edit->add('categories','Categories','checkboxgroup')
             ->options(Category::lists("name", "category_id"));

        return $edit->view('rapyd::demo.edit', compact('edit'));

    }

    
    public function getAuthorlist()
    {
       if (Input::get("id"))
       {
            return Author::where("user_id","=", Input::get("id")."%")->first()->get();
       } else {
            return Author::where("firstname","like", Input::get("q")."%")->take(10)->get();
       }
        
    }

}