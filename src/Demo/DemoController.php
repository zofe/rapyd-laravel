<?php namespace Zofe\Rapyd\Demo;


use Illuminate\Routing\Controller;
use Illuminate\Http\Request;


class DemoController extends Controller
{

    public function getIndex()
    {
        return  view('rapyd::demo.demo');
    }

    public function getModels()
    {
        return  view('rapyd::demo.models');
    }

    public function getSchema()
    {
        \Schema::dropIfExists("demo_users");
        \Schema::dropIfExists("demo_articles");
        \Schema::dropIfExists("demo_article_detail");
        \Schema::dropIfExists("demo_comments");
        \Schema::dropIfExists("demo_categories");
        \Schema::dropIfExists("demo_article_category");

        //create all tables
        \Schema::table("demo_users", function ($table) {
            $table->create();
            $table->increments('id');
            $table->string('firstname', 100);
            $table->string('lastname', 100);
            $table->timestamps();
        });
        \Schema::table("demo_articles", function ($table) {
            $table->create();
            $table->increments('id');
            $table->integer('author_id')->unsigned();
            $table->string('title', 200);
            $table->text('body');
            $table->string('photo', 200)->nullable();
            $table->boolean('public');
            $table->timestamp('publication_date');
            $table->timestamps();
        });
        \Schema::table("demo_article_detail", function ($table) {
            $table->create();
            $table->increments('id');
            $table->integer('article_id')->unsigned();
            $table->text('note');
            $table->string('note_tags', 200);
        });
        \Schema::table("demo_comments", function ($table) {
            $table->create();
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('article_id')->unsigned();
            $table->text('comment');
            $table->timestamps();
        });
        \Schema::table("demo_categories", function ($table) {
            $table->create();
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->nullable();
            $table->string('name', 100);
            $table->timestamps();
        });
        \Schema::table("demo_article_category", function ($table) {
            $table->create();
            $table->integer('article_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->timestamps();
        });

        //populate all tables
        $users = \DB::table('demo_users');
        $users->insert(array('firstname' => 'Jhon', 'lastname' => 'Doe'));
        $users->insert(array('firstname' => 'Jane', 'lastname' => 'Doe'));

        $categories = \DB::table('demo_categories');
        for ($i=1; $i<=5; $i++) {
            $categories->insert(array(
                    'name' => 'Category '.$i)
            );
        }
        $articles = \DB::table('demo_articles');
        for ($i=1; $i<=20; $i++) {
            $articles->insert(array(
                    'author_id' => rand(1,2),
                    'title' => 'Article '.$i,
                    'body' => 'Body of article '.$i,
                    'publication_date' => date('Y-m-d'),
                    'public' => true,)
            );
        }
        $categories =  \DB::table('demo_article_category');
        $categories->insert(array('article_id' => 1,'category_id' => 1));
        $categories->insert(array('article_id' => 1,'category_id' => 2));
        $categories->insert(array('article_id' => 20,'category_id' => 2));
        $categories->insert(array('article_id' => 20,'category_id' => 3));

        $comments =  \DB::table('demo_comments');
        $comments->insert(array(
                'user_id' => 1,
                'article_id' => 2,
                'comment' => 'Comment for Article 2')
        );

        $files = glob(public_path().'/uploads/demo/*');
        foreach ($files as $file) {
            if(is_file($file))
                @unlink($file);
        }

        if (class_exists('\Baum\Node')) {
            $this->getMenusSchema();
        }

        return \Redirect::to("rapyd-demo")->with("message", "Database filled");
    }

    public function getMenusSchema()
    {
        \Schema::dropIfExists("demo_menus");

        \Schema::create('demo_menus', function($table)
        {
            $table->increments('id');
            $table->string('title');

            // These columns are needed for Baum's Nested Set implementation to work.
            // Column names may be changed, but they *must* all exist and be modified
            // in the model.
            // Take a look at the model scaffold comments for details.
            // We add indexes on parent_id, lft, rgt columns by default.

            $table->integer('parent_id')->nullable()->index();
            $table->integer('lft')->nullable()->index();
            $table->integer('rgt')->nullable()->index();
            $table->integer('depth')->nullable();

            $table->timestamps();
        });

        $menus = [
            ['id' => 1, 'title' => '--- the root node of the menu ---', 'children' => [
                ['id' => 2, 'title' => 'Accessories', 'children' => [
                    ['id' => 3, 'title' => 'Cables']
                ]],
                ['id' => 4, 'title' => 'Laptops', 'children' => [
                    ['id' => 5, 'title' => 'PC Laptops'],
                    ['id' => 6, 'title' => 'Macbooks (Air/Pro)']
                ]],
                ['id' => 7, 'title' => 'Desktops'],
                ['id' => 8, 'title' => 'Monitors'],
                ['id' => 9, 'title' => 'Cell Phones']
            ],
        ]];
        Menu::buildTree($menus);
    }
    public function getSet()
    {
        $set = \DataSet::source(Article::with('author', 'categories'));
        $set->addOrderBy(['title','id']);
        $set->paginate(9);
        $set->build();

        return  view('rapyd::demo.set', compact('set'));
    }

    public function getGrid()
    {

        $grid = \DataGrid::source(Article::with('author', 'categories'));

        $grid->add('id','ID', true)->style("width:100px");
        $grid->add('title','Title');
        $grid->add('{!! str_limit($body,4) !!}','Body');
        $grid->add('{{ $author->fullname }}','Author', 'author_id');
        $grid->add('{{ implode(", ", $categories->lists("name")->all()) }}','Categories');

        $grid->edit('/rapyd-demo/edit', 'Edit','show|modify');
        $grid->link('/rapyd-demo/edit',"New Article", "TR");
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

        return  view('rapyd::demo.grid', compact('grid'));
    }

    public function getFilter()
    {
        $filter = \DataFilter::source(Article::with('author','categories'));
        $filter->add('title','Title', 'text');
        $filter->add('categories.name','Categories','tags');
        $filter->add('publication_date','publication date','daterange')->format('m/d/Y', 'en');
        $filter->submit('search');
        $filter->reset('reset');
        $filter->build();

        $grid = \DataGrid::source($filter);
        $grid->attributes(array("class"=>"table table-striped"));
        $grid->add('id','ID', true)->style("width:70px");
        $grid->add('title','Title', true);
        $grid->add('author.fullname','Author');
        $grid->add('{{ implode(", ", $categories->lists("name")->all()) }}','Categories');
        $grid->add('publication_date|strtotime|date[m/d/Y]','Date', true);
        $grid->add('body|strip_tags|substr[0,20]','Body');
        $grid->edit('/rapyd-demo/edit', 'Edit','modify|delete');
        $grid->paginate(10);

        return  view('rapyd::demo.filtergrid', compact('filter', 'grid'));
    }

    public function getCustomfilter()
    {
        $filter = \DataFilter::source(Article::with('author','categories'));
        $filter->text('src','Search')->scope('freesearch');
        $filter->build();

        $set = \DataSet::source($filter);
        $set->paginate(9);
        $set->build();

        return  view('rapyd::demo.customfilter', compact('filter', 'set'));
    }

    public function anyForm()
    {
        $form = \DataForm::source(Article::find(1));

        $form->add('title','Title', 'text')->rule('required|min:5');
        $form->add('body','Body', 'redactor');

        //belongs to
        $form->add('author_id','Author','select')->options(Author::lists('firstname', 'id')->all());

        //belongs to many (field name must be the relation name)
        $form->add('categories','Categories','checkboxgroup')->options(Category::lists('name', 'id')->all());
        $form->add('photo','Photo', 'image')->move('uploads/demo/')->fit(240, 160)->preview(120,80);
        $form->add('color','Color','colorpicker');
        $form->add('public','Public','checkbox');

        $form->submit('Save');

        $form->saved(function () use ($form) {
            $form->message("ok record saved");
            $form->link("/rapyd-demo/form","back to the form");
        });

        return view('rapyd::demo.form', compact('form'));
    }

    public function anyAdvancedform()
    {
        $form = \DataForm::source(Article::find(1));

        $form->add('title','Title', 'text')->rule('required|min:5');

        //simple autocomplete on options (built as local json array)
        $form->add('author_id','Author','autocomplete')->options(Author::lists('firstname', 'id')->all());

        //autocomplete with relation.field to manage a belongsToMany
        $form->add('author.fullname','Author','autocomplete')->search(array("firstname", "lastname"));

        //autocomplete with relation.field,  returned key,  custom remote ajax call (see at bottom)
        $form->add('author.firstname','Author','autocomplete')->remote(null, "id", "/rapyd-demo/authorlist");

        //tags with relation.field to manage a belongsToMany, it support also remote()
        $form->add('categories.name','Categories','tags');

        $form->submit('Save');

        $form->saved(function () use ($form) {
            $form->message("ok record saved");
            $form->link("/rapyd-demo/advancedform","back to the form");
        });

        return view('rapyd::demo.advancedform', compact('form'));
    }

    public function anyStyledform()
    {
        $form = \DataForm::source(Article::find(1));

        $form->add('title','Title', 'text')->rule('required|min:5');
        $form->add('body','Body', 'redactor');
        $form->add('categories.name','Categories','tags');
        $form->add('photo','Photo', 'image')->move('uploads/demo/')->fit(240, 160)->preview(120,80);
        $form->submit('Save');

        $form->saved(function () use ($form) {
            $form->message("ok record saved");
            $form->link("/rapyd-demo/styledform","back to the form");
        });
        $form->build();

        return view('rapyd::demo.styledform', compact('form'));
    }

    public function anyEdit()
    {
        if (\Input::get('do_delete')==1) return  "not the first";

        $edit = \DataEdit::source(new Article());
        $edit->label('Edit Article');
        $edit->link("rapyd-demo/filter","Articles", "TR")->back();
        $edit->add('title','Title', 'text')->rule('required|min:5');

        $edit->add('body','Body', 'redactor');
        $edit->add('detail.note','Note', 'textarea')->attributes(array('rows'=>2));
        $edit->add('detail.note_tags','Note tags', 'text');
        $edit->add('author_id','Author','select')->options(Author::lists("firstname", "id")->all());
        $edit->add('publication_date','Date','date')->format('d/m/Y', 'it');
        $edit->add('photo','Photo', 'image')->move('uploads/demo/')->fit(240, 160)->preview(120,80);
        $edit->add('public','Public','checkbox');
        $edit->add('categories.name','Categories','tags');

        return $edit->view('rapyd::demo.edit', compact('edit'));

    }

    public function anyDatatree()
    {
        if (!class_exists('\Baum\Node')) {
            die("You need to install Baum\\Baum and repopulate the database to use the DataTree");
        }

        // for demo purposes only, ensure the root exists
        $root = Menu::firstOrNew(['id' => 1]);
        $root->save();

        // load the root model
        $root = Menu::find(1) or App::abort(404);

        $tree = \DataTree::source($root);
        $tree->add('title');
        $tree->edit("/rapyd-demo/menuedit", 'Edit', 'modify|delete');
        $tree->submit('Save the order');

        return view('rapyd::demo.tree', compact('tree'));
    }


    public function anyMenuedit()
    {
        if (\Input::get('do_delete') == 1) return "not the first";

        $edit = \DataEdit::source(new Menu());
        $edit->link("rapyd-demo/datatree","Menu", "TR")->back();
        $edit->label('Edit Menu Item');
        $edit->add('title','Title', 'text');
        return $edit->view('rapyd::demo.edit', compact('edit'));
    }

    public function getNudegrid()
    {
        $grid = \DataGrid::source(Article::with('author','categories'));
        $grid->attributes(array("class"=>"table table-striped"));
        $grid->add('id','ID', true)->style("width:70px");
        $grid->add('title','Title', true);
        $grid->edit('/rapyd-demo/nudeedit', 'Edit','modify|delete');
        $grid->paginate(10);
        return $grid;
    }
    
    public function anyNudeedit()
    {
        if (\Input::get('do_delete')==1) return  "not the first";

        $edit = \DataEdit::source(new Article());
        $edit->link("rapyd-demo/nudegrid","Articles", "TR");
        $edit->label('Edit Article');
        $edit->add('title','Title', 'text')->rule('required|min:5');
        $edit->add('public','Public','checkbox');
        return $edit->view();
    }
    
    
    public function getEmbed()
    {
        //embed some widgets and isolate the dom using riot & pjax
        $embed1 = \DataEmbed::source('/rapyd-demo/nudegrid', 'embed1')->build();

        //if you prefer you can simply use an html tag
        $embed2 = '<dataembed id="embed2" remote="/rapyd-demo/nudeedit?modify=1"></dataembed>';
        
        return view('rapyd::demo.embed', compact('embed1','embed2'));
    }

    public function getAuthorlist()
    {
        //needed only if you want a custom remote ajax call for a custom search
        return Author::where("firstname","like", \Input::get("q")."%")
            ->orWhere("lastname","like", \Input::get("q")."%")->take(10)->get();

    }

    public function getCategorylist()
    {
        //needed only if you want a custom remote ajax call for a custom search
        return Category::where("name","like", \Input::get("q")."%")->take(10)->get();

    }

}
