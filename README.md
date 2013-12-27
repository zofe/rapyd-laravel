rapyd-laravel
=============


This is a laravel 4 package port of rapyd-framework crud widgets


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
It build a bootstrap triped table, with pagination at bottom and order-by links on table header.
  
in a controller 

```php
   //you can use seme source types of DataSet 
   $dataset = DataGrid::source("articles");
   $datagrid->add('title','Title', true); //sortable column
   $datagrid->add('sef','Url Segment');
   $datagrid->paginate(10);
   $grid = $datagrid->getGrid();

  //or if you're unsatisfied of default grid output
   $grid = $datagrid->getGrid("my-custom-view"); 

```

in a view you can just output

```php
    {{ $grid }}
```

## DataEdit

todo
