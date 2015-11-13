## Fork from https://github.com/zofe/rapyd-laravel

## the ‘dev’ branch is newest code, master branch keeps the same as upstream.

## dev branch only tested under Laravel 5.1.

## added features:

1. fix security issue on 'readonly' mode

2. add Datagrid::buildExcel() function （add maatwebsite/excel by composer）

3. Date组件默认的format改成'Y-m-d', lang改为'zh-CN'，方便兼容HTML5自带的日期组件。


4. 添加QNFile Field，七牛的文件上传控件
=======

2. run `$ composer update zofe/rapyd`


3. add this in the "provider" array on your config/app.php:  
 `Zofe\Rapyd\RapydServiceProvider::class,`  
  or for < 5.1  
 `'Zofe\Rapyd\RapydServiceProvider',`


4. then publish assets:  
 `$ php artisan vendor:publish`  
  or for < 5.0  
 `$ php artisan asset:publish zofe/rapyd`  
 `$ php artisan config:publish zofe/rapyd`
  

## DataGrid

DataGrid extend [DataSet](https://github.com/zofe/rapyd-laravel/wiki/DataSet) to make data-grid output with few lines of fluent code.  
It build a bootstrap striped table, with pagination at bottom and order-by links on table header.
It support also blade syntax, filters, closures etc..

in a controller 

```php
   $grid = \DataGrid::source(Article::with('author'));  //same source types of DataSet
   
   $grid->add('title','Title', true); //field name, label, sortable
   $grid->add('author.fullname','author'); //relation.fieldname 
   $grid->add('{{ substr($body,0,20) }}...','Body'); //blade syntax with main field
   $grid->add('{{ $author->firstname }}','Author'); //blade syntax with related field
   $grid->add('body|strip_tags|substr[0,20]','Body'); //filter (similar to twig syntax)
   $grid->add('body','Body')->filter('strip_tags|substr[0,20]'); //another way to filter
   $grid->edit('/articles/edit', 'Edit','modify|delete'); //shortcut to link DataEdit actions
   
   //cell closure
   $grid->add('revision','Revision')->cell( function( $value, $row) {
        return ($value != '') ? "rev.{$value}" : "no revisions for art. {$row->id}";
   });
   
   //row closure
   $grid->row(function ($row) {
       if ($row->cell('public')->value < 1) {
           $row->cell('title')->style("color:Gray");
           $row->style("background-color:#CCFF66");
       }  
   });
   
   $grid->link('/articles/edit',"Add New", "TR");  //add button
   $grid->orderBy('article_id','desc'); //default orderby
   $grid->paginate(10); //pagination

   view('articles', compact('grid'))

```

in a view you can just write

```php

  #articles.blade.php  

  {!! $grid !!} 

```


styling a datagrid

```php
   ...
   $grid->add('title','Title', true)->style("width:100px"); //adding style to th
   $grid->add('body','Body')->attr("class","custom_column"); //adding class to a th
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
   $grid->buildCSV('export_articles', 'Y-m-d.His');  // force download with custom stamp
   $grid->buildCSV('uploads/filename', 'Y-m-d');  // write on file 
    ...
```



## DataForm

 DataForm is a form builder, you can add fields, rules and buttons.  
 It will build a bootstrap form, on submit it  will check rules and if validation pass it'll store new entity.  

```php
   //start with empty form to create new Article
   $form = \DataForm::source(new Article);
   
   //or find a record to update some value
   $form = \DataForm::source(Article::find(1));

   //add fields to the form
   $form->add('title','Title', 'text'); //field name, label, type
   $form->add('body','Body', 'textarea')->rule('required'); //validation

   //some enhanced field (images, wysiwyg, autocomplete, etc..):
   $form->add('photo','Photo', 'image')->move('uploads/images/')->preview(80,80);
   $form->add('body','Body', 'redactor'); //wysiwyg editor
   $form->add('author.name','Author','autocomplete')->search(['firstname','lastname']);
   $form->add('categories.name','Categories','tags'); //tags field
 
   //you can also use now the smart syntax for all fields: 
   $form->text('title','Title'); //field name, label
   $form->textarea('body','Body')->rule('required'); //validation
 
   //change form orientation
   $form->attributes(['class'=>'form-inline']);
 
   ...
 
 
   $form->submit('Save');
   $form->saved(function() use ($form)
   {
        $form->message("ok record saved");
        $form->link("/another/url","Next Step");
   });

   view('article', compact('form'))
```

```php
   #article.blade.php

  {!! $form !!}
```

[DataForm explained](https://github.com/zofe/rapyd-laravel/wiki/DataForm)  

 
### customize form in view

You can directly customize form  using build() in your controller

 ```php
     ...
     $form->build();
     view('article', compact('form'))
 ```
 then in the view you can use something like this:
 
```php
   #article.blade.php
    {!! $form->header !!}

        {!! $form->message !!} <br />

        @if(!$form->message)
            <div class="row">
                <div class="col-sm-4">
                     {!! $form->render('title') !!}
                </div>
                <div class="col-sm-8">
                    {!! $form->render('body') !!}
                </div>
            </div> 
            ...
        @endif

    {!! $form->footer !!}
```
[custom form layout explained](https://github.com/zofe/rapyd-laravel/wiki/Custom-Form-Layout)  
[custom form layout demo](http://www.rapyd.com/rapyd-demo/styledform)  


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
   $edit = \DataEdit::source(new Article);
   $edit->link("article/list","Articles", "TR")->back();
   $edit->add('title','Title', 'text')->rule('required');
   $edit->add('body','Body','textarea')->rule('required');
   $edit->add('author.name','Author','autocomplete')->search(['firstname','lastname']);
   
   //you can also use now the smart syntax for all fields: 
   $edit->textarea('title','Title'); 
   $edit->autocomplete('author.name','Author')->search(['firstname','lastname']);
   
   return $edit->view('crud', compact('edit'));

```

```php
   #crud.blade.php
 
  {!! $edit !!} 
```
[DataEdit explained](https://github.com/zofe/rapyd-laravel/wiki/DataEdit)  


## DataFilter
DataFilter extends DataForm, each field you add and each value you fill in that form is used to build a __where clause__ (by default using 'like' operator).   
It should be used in conjunction with a DataSet or DataGrid to filter results.  
It also support query scopes (see eloquent documentation), closures, and a cool DeepHasScope trait see samples:


```php
   $filter = \DataFilter::source(new Article);

   //simple like 
   $filter->add('title','Title', 'text');
          
   //simple where with exact match
   $filter->add('id', 'ID', 'text')->clause('where')->operator('=');
          
   //custom query scope, you can define the query logic in your model
   $filter->add('search','Search text', 'text')->scope('myscope');
      
   //cool deep "whereHas" (you must use DeepHasScope trait bundled on your model)
   //this can build a where on a very deep relation.field
   $filter->add('search','Search text', 'text')->scope('hasRel','relation.relation.field');
   
   //closure query scope, you can define on the fly the where
   $filter->add('search','Search text', 'text')->scope( function ($query, $value) {
         return $query->whereIn('field', ["1","3",$value]);
   })
   
   $filter->submit('search');
   $filter->reset('reset');
   
   $grid = \DataGrid::source($filter);
   $grid->add('nome','Title', true);
   $grid->add('{{ substr($body,0,20) }}...','Body');
   $grid->paginate(10);

   view('articles', compact('filter', 'grid'))
```

```php
   # articles.blade
   
   {!! $filter !!}  
   {!! $grid !!}

```

[DataFilter explained](https://github.com/zofe/rapyd-laravel/wiki/DataFilter)  
[Custom layout and custom query scope](http://www.rapyd.com/rapyd-demo/customfilter) 




## Namespace consideration, Extending etc.

To use widgets you can: 
- just use the global aliases: `\DataGrid::source()...` (please note the '\')
- or import facades:
```php
    use Zofe\Rapyd\Facades\DataSet;
    use Zofe\Rapyd\Facades\DataGrid;
    use Zofe\Rapyd\Facades\DataForm;
    use Zofe\Rapyd\Facades\DataForm;
    use Zofe\Rapyd\Facades\DataEdit;
    ..
    DataGrid::source()... 
```
- or you can extend each class 
```php
    Class MyDataGrid extends Zofe\Rapyd\DataGrid\DataGrid {
    ...
    }
    Class MyDataEdit extends Zofe\Rapyd\DataEdit\DataEdit {
    ...
    }
    ..
    MyDataGrid::source()
```

## Publish & override configuration and assets

You can quickly publish the configuration file (to override something) 
by running the following Artisan command.  

    $ php artisan vendor:publish  
    


You need also to add this to your views, to let rapyd add runtime assets:

```html
<head>
...
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

{!! Rapyd::head() !!} 

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


Rapyd comes with demo (controller, models, views) a route is defined in `app/Http/rapyd.php`  
so go to:

/rapyd-demo



## License

Rapyd is licensed under the [MIT license](http://opensource.org/licenses/MIT)

If Rapyd saves you time, please __[support Rapyd](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QJFERQGP4ZB6A)__

