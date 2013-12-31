rapyd-laravel
=============

This is a pool of presentation and editing widgets (Grids and Forms) for laravel 4.  
Nothing to "generate", just some classes to let you develop and maintain CRUD backends in few lines of code.

## install 


To `composer.json` add: `"zofe/rapyd": "dev-master"` 
and then run: `$ composer update zofe/rapyd`.

In `app/config/app.php` add this service provider: `'Zofe\Rapyd\RapydServiceProvider',`.

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
  
in a controller 

```php
   //you can use same source types of DataSet 
   $datagrid = DataGrid::source("articles");
   $datagrid->add('title','Title', true); //sortable column
   $datagrid->add('sef','Url Segment');
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

 _* in development *_
 

```php
   //empty form
   $dataform = DataForm::create();
   //starting from model (empty or loaded)
   $dataform = DataForm::source(Attrice::find(1));
   
   $dataform->add('nome','Nome', 'text');
   $dataform->add('sef','Url', 'text');
   $dataform->submit('Save');
   $form = $dataform->getForm();

```

```php
  {{ $form }}
```

## DataEdit

 _* in development *_
 


## Including Bootstrap

Rapyd is compatible with Bootstrap 3  

You can use a CDN 

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
- update record (pre-filled form)
- delete record
 
Eloquent to get & edit data, then there is artisan & generator package to make a lot of code in few console commands...   
But what about Views? You've to write at least 3-4 views, for each entity you need to manage.

Rapyd use a different approach, widget based.  

_You need to "show" and "edit" record from an entity?_  
Ok so you need a controller with two methods :
- one for a DataGrid 
- one for a DataEdit

For both  you need only to define fields to display / manage.