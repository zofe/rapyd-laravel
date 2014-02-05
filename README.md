rapyd-laravel
=============

This is a pool of presentation and editing widgets (Grids and Forms) for laravel 4.  
Nothing to "generate", just some classes to let you develop and maintain CRUD backends in few lines of code.

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
   //or using eloquent
   $dataset = DataSet::source(Article::all())->paginate(10)->getSet();
   //or using array
   $dataset = DataSet::source($multidimensional_array)->paginate(10)->getSet();
```

in a view you can use

```php
<p>
    ORDER LINKS<br />
    {{ $dataset->orderbyLink('title', 'asc') }} <br />
    {{ $dataset->orderbyLink('title', 'desc') }}

    @foreach ($dataset->data as $item)
    {{ $item->title }}<br />
    @endforeach

    PAGINATION <br />
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
   $form = $dataedit->getForm();   
   return $dataedit->view('crud', array('form' => $form));

```

```php
   #crud.blade.php
  {{ $form }}
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

Rapyd need Bootstrap 3 css (not included) 

You can use a CDN  and include in your HEAD tags

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