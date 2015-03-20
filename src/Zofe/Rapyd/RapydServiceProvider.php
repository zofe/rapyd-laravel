<?php namespace Zofe\Rapyd;

use Illuminate\Support\ServiceProvider;

class RapydServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('zofe/rapyd', 'rapyd');
        include __DIR__ . '/../../routes.php';
        include __DIR__ . '/../../macro.php';

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Rapyd::setContainer($this->app);

        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Rapyd', 'Zofe\Rapyd\Facades\Rapyd');
            $loader->alias('DataSet', 'Zofe\Rapyd\Facades\DataSet');
            $loader->alias('DataGrid', 'Zofe\Rapyd\Facades\DataGrid');
            $loader->alias('DataForm', 'Zofe\Rapyd\Facades\DataForm');
            $loader->alias('DataEdit', 'Zofe\Rapyd\Facades\DataEdit');
            $loader->alias('DataFilter', 'Zofe\Rapyd\Facades\DataFilter');
            $loader->alias('Documenter', 'Zofe\Rapyd\Facades\Documenter');
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('dataset', 'datagrid');
    }

}
