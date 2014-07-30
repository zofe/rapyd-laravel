<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Rapyd crud widgets for laravel 4')</title>
    <meta name="description" content="@yield('description', 'crud widgets for laravel 4. datatable, grids, forms, in a simple package')" />
    @section('meta', '')
    
    <link href="http://fonts.googleapis.com/css?family=Bitter" rel="stylesheet" type="text/css" />
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">

    {{ HTML::style('packages/zofe/rapyd/assets/demo/style.css') }}
    {{ Rapyd::styles() }}
</head>

<body>

<div id="wrap">

    <div class="container">

        <br />

        <div class="row">

            
            
                @if (isset($is_rapyd) AND $is_rapyd)

                    <div class="col-sm-9">
                        @yield('content')
                    </div>
                        
                    <div class="col-sm-3">
                        <div id="disqus_thread"></div>
                        <script type="text/javascript">
                            var disqus_shortname = 'rapyd';
                            (function() {
                                var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
                                dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
                                (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
                            })();
                        </script>
                        <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
                        <a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>
                    </div>

                @else

                    <div class="col-sm-12">
                        @yield('content')
                    </div>
            
                @endif
                


        </div>


    </div>



</div>

<div id="footer">
</div>
<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
{{ Rapyd::scripts() }}
</body>
</html>


