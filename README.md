rapyd-laravel
=============

<a href="https://packagist.org/packages/zofe/rapyd">
    <img src="http://img.shields.io/packagist/v/zofe/rapyd.svg?style=flat" style="vertical-align: text-top">
</a>
<a href="https://packagist.org/packages/zofe/rapyd">
    <img src="http://img.shields.io/packagist/dt/zofe/rapyd.svg?style=flat" style="vertical-align: text-top">
</a>

This is a pool of presentation and editing widgets (Grids and Forms) for laravel 4.  
Nothing to "generate", just some classes to let you develop and maintain CRUD backends in few lines of code.  
 
Main Website: [rapyd.com](http://www.rapyd.com)  
Demo: [rapyd.com/demo](http://www.rapyd.com/demo)  
Documentation: [Wiki](https://github.com/zofe/rapyd-laravel/wiki)  

![rapyd laravel](https://raw.github.com/zofe/rapyd-laravel/master/public/assets/rapyd-laravel.png)

## DataSet

DataSet can paginate results starting from query, eloquent collection or multidimensional array.  
It add the ability to order result and keep persistence of all params in query string.

i.e.:
```
/dataset/uri?page=2&ord=-name   will get page 2 order by "name" descending
/dataset/uri?page=3&ord=name&other=xx  will get page 3 order by "name" and keeping "other=xx"
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
    //cycle
    @foreach ($dataset->data as $item)

        {{ $item->title }}<br />
        {{ $item->author->name }}<br />

    @endforeach

    {{ $dataset->links() }} <br />

    //sort link
    {{ $dataset->orderbyLink('title', 'asc') }} <br />
</p>
```

As you see you can build a dataset  using "one row of code" (using method chaining),
however I suggest you this alternative syntax
to be more in "standard"  with other widgets:

```php
   $set = DataSet::source(Article::with('author'));
   $set->paginate(10);
   $set->build();
   
   View::make('articles', compact('set'))
```
 
   

## DataGrid

DataGrid extend DataSet to make data-grid output with few lines of fluent code.  
It build a bootstrap striped table, with pagination at bottom and order-by links on table header.
It support also blade syntax inline.  
It support also some filter (native php functions) like substr, strip_tags, date.. etc.


in a controller 

```php
   $grid = DataGrid::source(Article::with('author'));  //same source types of DataSet
   
   $grid->add('title','Title', true); //field name, label, sortable
   $grid->add('author.firstname','Body'); //relation.fieldname 
   $grid->add('{{ substr($body,0,20) }}...','Body'); //blade syntax with main field
   $grid->add('{{ $author->firstname }}','Author'); //blade syntax with related field
   $grid->add('body|strip_tags|substr[0,20]','Body'); //filter (similar to twig syntax)
   $grid->add('body','Body')->filter('strip_tags|substr[0,20]'); //another way to filter
   $grid->edit('/dataedit/uri', 'Edit','modify|delete'); //shortcut to link DataEdit actions
   $grid->orderBy('article_id','desc'); //default orderby
   $grid->paginate(10); //pagination

   View::make('articles', compact('grid'))

```

in a view you can just write

```php
  #articles.blade.php
  {{ $grid }}
```


styling a datagrid

```php
   ...
   $grid->add('title','Title', true)->style("width:100px"); //adding style to th
   $grid->add('body','Body')->attributes(array("class"=>"custom_column")); //adding class to a th
   ...
    //row and cell manipulation via closure
    $grid->row(function ($row) {
       if ($row->cell('public')->value < 1) {
           $row->cell('title')->style("color:Gray");
           $row->style("background-color:#CCFF66");
       }  
    });
    ...
```

datagrid supports also csv output, so it can be used as "report" tool.

```php
   ...
   $grid->add('title','Title');
   $grid->add('body','Body')
   ...
   $grid->buildCSV();  //  force download 
   $grid->buildCSV('export_articles', 'Y-m-d.His');  //  force download with custom name and stamp
   $grid->buildCSV('uploads/filename', 'Y-m-d');  // write on file 
    ...
```



## DataForm

 DataForm is a form builder, you can add fields, rules and buttons.  
 It will build a bootstrap form, on submit it  will check rules and if validation pass it'll store new entity.  

```php
   //start with empty form to create new Article
   $form = DataForm::source(new Article);
   
   //or find a record to update some value
   $form = DataForm::source(Article::find(1));

   //add fields to the form
   $form->add('title','Title', 'text'); //field name, label, type
   $form->add('body','Body', 'textarea')->rule('required'); //validation
   
   //some enhanced field (images, wysiwyg, autocomplete, etc..):
   $form->add('photo','Photo', 'image')->move('uploads/images/')->preview(80,80);
   $form->add('body','Body', 'redactor'); //wysiwyg editor
   $form->add('author.fullname','Author','autocomplete')->search(array('firstname','lastname'));
   $form->add('categories.name','Categories','tags'); //tags field
   
   //you can also use shorthand methods, add{Type}(...
   $form->addText('title','Title'); //field name, label
   $form->addTextarea('body','Body')->rule('required');

    //can also support readonly 
   $form->addText('nickname','Nickname')->mode('readonly');

   //then a submit button
   $form->submit('Save');

   //at the end you can use closure to add stuffs or redirect after save
   $form->saved(function() use ($form)
   {
        $form->message("ok record saved");
        $form->link("/another/url","Next Step");
   });

   View::make('article', compact('form'))
```

```php
   #article.blade.php
  {{ $form }}
```

 note: DataForm can also work without entity, just as Form builder, use __DataForm::create()__ instead of DataForm::source in this case..
 
### customize form in view

 There is not only {{ $form }} to show a form, if you need to customize something: wrap fields, grouping elements etc..
 Simply call build method:
 ```php
     ...
     $form->build();
     View::make('article', compact('form'))
 ```
 then in the view you can use something like this:
 
```php
   #article.blade.php
    {{ $form->header }}

        {{ $form->message }} <br />

        @if(!$form->message)
        
            Title:  {{ $form->field('title') }}<br /> 
            Body:  {{ $form->field('body') }}
            ...
            
        @endif

    {{ $form->footer }}
```

| Attribute or method            | Output or content                                           |
| ---------------------------    | ----------------------------------------------------------- |
| $form->header                  | form open and header buttons                                |
| $form->footer                  | footer buttons and form close                               |
| $form->message                 | show if present a succes/confirmation message               |
| $form->fields                  | array of fields  (to do a @foreach)                         |
| $form->field('fieldname')      | field instance, used without property it show field output  |
| $form->error                   | error string containing all errors                          |
| ..field('fieldname')->messages | array of error messages for current field                   |
| ..field('fieldname')->message  | string with all error messages for current field            |
|                                |                                                             |
| $form->open                    |  form open (it's contained in "header")                     |
| $form->close                   |  form close (it's contained in "footer")                    |
| $form->button_container        |  array of buttons (it's contained in both header & footer)  |


## DataEdit
  DataEdit extends DataForm, it's a full CRUD application for given Entity.  
  It has status (create, modify, show) and actions (insert, update, delete) 
  It detect status by simple query string semantic:


```
  /dataedit/uri                     empty form    to CREATE new records
  /dataedit/uri?show={record_id}    filled output to READ record (without form)
  /dataedit/uri?modify={record_id}  filled form   to UPDATE a record
  /dataedit/uri?delete={record_id}  perform   record DELETE
  ...
```

```php
   //simple crud for Article entity
   $edit = DataEdit::source(new Article);
   $edit->link("article/list","Articles", "TR")->back();
   $edit->add('title','Title', 'text')->rule('required');
   $edit->add('body','Body','textarea')->rule('required');
   $edit->add('download','Attachment', 'file')->rule('mime:pdf')->move('uploads/pdf/');
   $edit->add('photo','Photo', 'image')->rule('mimes:jpeg')->move('uploads/images/')->fit(320,240);
   $edit->add('author.fullname','Author','autocomplete')->search(array('firstname','lastname'));
   
   return $edit->view('crud', compact('edit'));

```

```php
   #crud.blade.php
  {{ $edit }}
```
As you see you can append fields and links, while the "buttons" (save, undo, delete, etc..) and messages (like delete confirmation) are fully managed by dataedit.  
You can add a ```->back()``` or more specific ```->back('do_delete|update')``` to a link if you want to auto-pull back  after all actions (or after some of these).

note: we use _$edit->view_  method  instead _View::make_ for a reason: DataEdit must manage  redirects. With other widgets you should use View facade as default.    

## DataFilter
DataFilter extends DataForm, each field you add and each value you fill in that form is used to build a __where clause__ (by default using 'like' operator).   
It should be used in conjunction with a DataSet or DataGrid to filter results.  


```php
   $filter = DataFilter::source(new Article);
   $filter->attributes(array('class'=>'form-inline'));
   $filter->add('title','Title', 'text');
   $filter->submit('search');
   $filter->reset('reset');
   
   $grid = DataGrid::source($filter);
   $grid->add('nome','Title', true);
   $grid->add('{{ substr($body,0,20) }}...','Body');
   $grid->paginate(10);

   View::make('articles', compact('filter', 'grid'))
```
```php
   # articles.blade
   {{ $filter }}
   {{ $grid }}
```

Since DataFilter use GET method, it's trivial to make filter-links:
```
/datafilter/uri?title=Article+10&search=1     search for "Article 10" in title
/datafilter/uri?category_id=1&search=1       search for articles related to "Category 1" ..
```

DataFilter can work with multiple fields  or, if you prefer,  you can add a generic single field to do a free search.
This is how to: http://www.rapyd.com/rapyd-demo/customfilter  

Note that, parameter's name is generally related to "datafilter fields" and not directly to the Entity.  
DataFilter works making only "where", "whereHas" and using  **PDO parameter binding**  (so It's safe from SQL injection), 
however you must pay attention if you customize queries using _scopes_ (you should use _parameter binding_ too).
 



## Install in Laravel 4.1 & 4.2


To `composer.json` add:  
`"zofe/rapyd": "1.3.*"` for both, not frequently updated (should be stable)  
`"zofe/rapyd": "dev-master"` for both, with latest stuffs (may be unstable)  


In `app/config/app.php` add:  
`'Zofe\Rapyd\RapydServiceProvider',`

then run: `$ composer update zofe/rapyd`.


## Publish & override configuration (optional)

You can quickly publish the configuration file (to override something) 
by running the following Artisan command.  

    $ php artisan config:publish zofe/rapyd

## Publish & integrate assets (needed)


You need to publish the assets from this package.

    $ php artisan asset:publish zofe/rapyd

Note: The public assets can change overtime (because of upstream changes), it is recommended to re-publish them after update.  
Alternatively you can add the publish command in composer.json.

    "post-update-cmd": [
        "php artisan asset:publish zofe/rapyd"
    ],


You need also to add this to your views, to let rapyd add runtime assets:

```html
<head>
  ...
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

   {{ Rapyd::head() }}
</head>
```
note: widget output is in standard with __Boostrap 3+__, and some widget need support of __JQuery 1.9+__
so be sure to include dependencies as above

A better choice is to split css and javascipts and move javascript at bottom, just before body to speedup the page,
you can do this with:

```html
<head>
  ...
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
   {{ Rapyd::styles() }}
</head>
....

    <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
   {{ Rapyd::scripts() }}
</body>
```



## In short

Rapyd use a "widget" approach to make a crud, without "generation".
(this approach is worst in terms of flexibility but fast/rapid in terms of development and maintenance):

_You need to "show" and "edit" record from an entity?_  
Ok so you need a DataGrid and DataEdit.
You can build widgets where you want (even multiple widgets on same route).
An easy way to work with rapyd is:
  * make a route to a controller for each entity you need to manage
  * make the controller with one method for each widget (i.e.: one for a datagrid and one for a dataedit)
  * make an empty view, include bootstrap and display content that rapyd will build for you


Rapyd comes with demo (controller, models, views)  to run it just add:


/app/routes.php
```php
...
Route::controller('rapyd-demo', 'Zofe\\Rapyd\\Controllers\\DemoController');
```

then go to:

/rapyd-demo

or use the one that is online:  
[http://rapyd.com/rapyd-demo](http://www.rapyd.com/rapyd-demo)



## License

Rapyd is licensed under the [MIT license](http://opensource.org/licenses/MIT)

If Rapyd saves you time, please consider [tipping via gittip](https://www.gittip.com/zofe)