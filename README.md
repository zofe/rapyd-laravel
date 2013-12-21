rapyd-laravel
=============


This is a laravel 4 package port of rapyd-framework crud widgets


## install 


To `composer.json` add: `"zofe/rapyd": "dev-master"` 
and then run: `$ composer update zofe/rapyd`.

In `app/config/app.php` add this service provider: `'Zofe\Rapyd\RapydServiceProvider',`.

## usage 

in a controller 
```
   //using db table name
  $dataset = DataSet::source("tablename")->paginate(10)->getSet();
  //or using eloquent
  $dataset = DataSet::source(Article::with("comments"))->paginate(10)->getSet();

```

in a view you can use

```
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