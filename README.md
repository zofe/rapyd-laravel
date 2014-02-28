rapyd-laravel
=============

This is a pool of presentation and editing widgets (Grids and Forms) for laravel 4.  
Nothing to "generate", just some classes to let you develop and maintain CRUD backends in few lines of code.

![rapyd laravel](https://raw.github.com/zofe/rapyd-laravel/master/public/assets/rapyd-laravel.png)

## DataSet

DataSet can paginate results starting from query, eloquent collection or multidimensional array.  
It add the ability to order result and keep persistence of all params in query string.

i.e.:
```
/myroute?page=2&ord=-name   will get page 2 order by "name" descending
/myroute?page=3&ord=name&other=xx  will get page 3 order by "name" ascending and keeping "other=xx"
```

in a controller 

```php
   //using table name
   $dataset = DataSet::source("tablename")->paginate(10)->getSet();

   //or using query
   $dataset = DataSet::source(DB::table('users')->select('name', 'email'))->paginate(10)->getSet();

   //or using eloquent model or eloquent builder 
   $dataset = DataSet::source(new Article)->paginate(10)->getSet();
   $dataset = DataSet::source(Article::with('author'))->paginate(10)->getSet();

   //or using array
   $dataset = DataSet::source($multidimensional_array)->paginate(10)->getSet();
```

in a view you can use

```php
<p>
    //order by links
    {{ $dataset->orderbyLink('title', 'asc') }} <br />
    {{ $dataset->orderbyLink('title', 'desc') }}

    @foreach ($dataset->data as $item)

    {{ $item->title }}<br />
       
          //eloquent relation
         {{ $item->author->name }}<br />

    @endforeach

    //pagination links
    {{ $dataset->links() }}    
</p>
```



## DataGrid

DataGrid extend DataSet to make data-grid output with few lines of fluent code.  
It build a bootstrap striped table, with pagination at bottom and order-by links on table header.
It support also  blade syntax inline. 

in a controller 

```php
   //you can use same source types of DataSet 
   $datagrid = DataGrid::source("articles");
   $datagrid->add('title','Title', true); //sortable column
   $datagrid->add('{{ strtolower($sef) }}','Url Segment'); //blade syntax
   $datagrid->paginate(10);
   $grid = $datagrid->getGrid();

  //or if you're unsatisfied of default grid output
   $grid = $datagrid->getGrid("my-custom-view"); 

```

in a view you can just write

```php
  {{ $grid }}
```

## DataForm

 DataForm is a form builder, you can add fields, rules and buttons.  
 It will build a bootstrap form, on submit it  will check rules and if validation pass it'll store new entity.  
 _* in development *_
 

```php
   //empty form
   $dataform = DataForm::create();
   
   //or starting from model (empty or loaded)
   $dataform = DataForm::source(Article::find(1));
   
   $dataform->add('title','Title', 'text'); //name, label, type 
   $dataform->add('sef','Url', 'text')->rule('required');
   //or you can use shorthand methods, which presents for all supported field types
   $dataform->addText('title','Title'); //name, label, type
   $dataform->addText('sef','Url')->rule('required');

   $dataform->submit('Save');
   $form = $dataform->getForm();

```

```php
  {{ $form }}
```

## DataEdit
  DataEdit extends DataForm, it's a full CRUD application for given Entity.  
  It has status (create, modify, show) and actions (insert, update, delete) 
  It detect status by simple query string semantic:
 _* in development *_


```
  ?create=1			   empty form    to CREATE new records 
  ?show={record_id}    filled output to READ record (without form)
  ?modify={record_id}  filled form   to UPDATE a record
  ?delete={record_id}  perform   record DELETE
  ...
```

```php
   //simple crud for Article entity
   $dataedit = DataEdit::source(new Article);
   $dataedit->add('title','Title', 'text')->rule('required');
   $dataedit->add('sef','Url', 'text');
   $dataedit->add('description','Description', 'textarea');
   $dataedit->add('photo','Photo', 'file')->rule('image')->move('uploads/');
   $form = $dataedit->getForm();   
   return $dataedit->view('crud', array('form' => $form));

```

```php
   #crud.blade.php
  {{ $form }}
```


## DataFilter
DataFilter extends DataForm, each field you add and each value you fill in that form is used to build a __where clause__ (by dafault using 'like' operator).   
It should be used in conjunction with a DataSet or DataGrid to filter results.  
 _* in development *_


```php
   $datafilter = DataFilter::source(new Article);
   $datafilter->add('title','Title', 'text');
   $datafilter->submit('search');
   $filter = $datafilter->getForm();
       
   $datagrid = DataGrid::source($datafilter);
   $datagrid->add('nome','Title', true);
   $datagrid->add('sef','Url Segment');
   $datagrid->paginate(10);

   $grid->add('<a href="/article?show={{ $id }}">edit</a>','edit');
   $grid->add('<a href="/article?do_delete={{ $id }}">delete</a>','delete');
   // Or you can specify printing «stock» action buttons.
   // You must pass uri which will handle records of your grid
   $grid->addActions('/article');
   $grid = $datagrid->getGrid();

```
```php
   # filtered.grid.blade
   {{ $filter }}
   {{ $grid }}
```
## Install 


To `composer.json` add: `"zofe/rapyd": "dev-master"` 
and then run: `$ composer update zofe/rapyd`.

In `app/config/app.php` add this service provider: `'Zofe\Rapyd\RapydServiceProvider',`.


## Publish & integrate assets

`php artisan asset:publish zofe/rapyd`

then you need to add this to your views,  to let rapyd add runtime assets:

```php
<head>
  ...
  {{ Rapyd::head() }}
</head>
```


## Including Bootstrap

Rapyd needs Bootstrap 3 css (not included) 

You can use a CDN and include it in your HEAD tags

```html
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
```
_Or_

[Get latest version](http://getbootstrap.com) then include just like any other css and js files

```php
{{ HTML::style('path/to/bootstrap.css') }}
{{ HTML::script('path/to/jquery.js') }}
{{ HTML::script('path/to/bootstrap.js') }}
```


## In short

Default way to create a CRUD for an entity with laravel  is to make a controller, with a method for each action:

- show records (usually a grid with pagination)
- create record (empty form & validation)
- update record (pre-filled form & validation)
- delete record (an action with a redirect)
 
Whit artisan and Generator package you can make a lot of code in few console commands...   
But you've to write/modify at least 3 views for each entity you need to manage.  
You've to take care about forms, errors, redirects, routes etc. 

Rapyd use a different approach (worst in terms of flexibility but fast/rapid in terms of development and maintenance):

_You need to "show" and "edit" record from an entity?_  
Ok so you need a controller with two methods :
- one for a DataGrid widget
- one for a DataEdit widget

For both  you need only to define fields to display / manage.
Would you like to see a sample Backend ?  bum:

/app/routes.php
```php
...
Route::controller('admin', 'AdminController');
```

/app/controllers/AdminController.php
```php
class AdminController extends BaseController {

	public function getArticles()
	{
        $grid = DataGrid::source( Article::with("user"));
        $grid->link('/admin/article?create=1', "New Article",  "TR");
        $grid->add('title','Title', true);
        $grid->add('sef','Url');
        $grid->add('{{ $row->user->email }}','author');
        $grid->addActions('/admin/article');
        $grid->paginate(10);
        $grid = $grid->getGrid();
        return  View::make('admin.edit', array('content' => $grid));

	} 

	public function anyArticle()
	{
        $edit = DataEdit::source(new Article);
        $edit->link('/admin/articles', "Article List",  "TR");
        $edit->add('title','Title', 'text')->rule('required');
        $edit->add('sef','Url', 'text');
        $edit->add('description','Description', 'redactor');
        $edit->add('user_id','Author','select')->options(User::lists("username", "id"))->rule('required');
        $form = $edit->getForm();
        return $edit->view('admin.edit', array('content' => $form));
	} 
 
}
```

/app/views/admin/edit.php
```php
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <link  href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css" rel="stylesheet">
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
    <script src="/assets/js/jquery.js"></script>
    {{ Rapyd::head() }}
  </head>
  <body>
    <div id="wrap">
      <div class="container">
        {{ $content }}
      </div>
    </div>
  </body>
</html>
```