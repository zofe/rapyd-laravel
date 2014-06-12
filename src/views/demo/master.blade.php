<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Rapyd crud widgets for laravel 4')</title>
    <meta name="description" content="@yield('description', 'crud widgets for laravel 4. datatable, grids, forms, in a simple package')" />

    <link href="http://fonts.googleapis.com/css?family=Bitter" rel="stylesheet" type="text/css" />
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">


    {{ HTML::style('packages/zofe/rapyd/assets/demo/style.css') }}

    <meta name="google-site-verification" content="nnSN8Q-ln625K5sPUL6OACj01almc9Og9xOyNYXs-tU" />
    <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>

    @section('meta', '')
    {{ Rapyd::head() }}
</head>

<body>

<div id="wrap">

    <div class="container">

        <br />

        <div class="row">

            <div class="col-sm-12">


                @yield('content')



            </div>


        </div>


    </div>



</div>

<div id="footer">
</div>

</body>
</html>


