@extends('rapyd::demo.master')

@section('title','Demo')

@section('body')
    <h1>Demo Index</h1>

    @if(Session::has('message'))
    <div class="alert alert-success">
        {{ Session::get('message') }}
    </div>
    @endif

    <p>
        Welcome to Rapyd Demo.<br />
        
        @if (isset($is_rapyd) AND $is_rapyd)
        
        @else
            first click on Populate Database button, then click on menu<br />
            <br />
            {{ link_to('rapyd-demo/schema', "Populate Database", array("class"=>"btn btn-default")) }}
        @endif
    </p>


@stop


@section('content')

    @include('rapyd::demo.menu')


    <div class="row">

        @if (isset($is_rapyd) AND $is_rapyd)

            <div class="col-sm-9">
                @yield('body')
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
                @yield('body')
            </div>

        @endif
        
    </div>




@stop

