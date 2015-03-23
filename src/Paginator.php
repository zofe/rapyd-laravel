<?php namespace Zofe\Rapyd;

use Illuminate\Support\Facades\View;

class Paginator
{

    protected $items_per_page;
    protected $total_items;
    protected $hash = '';
    protected $url;
    protected $current_page;
    protected $total_pages;
    protected $current_first_item;
    protected $current_last_item;
    protected $first_page;
    protected $last_page;
    protected $previous_page;
    protected $next_page;
    protected $num_links = 3;
    
    
    public static function make($total_items, $items_per_page, $current_page = 1)
    {
        $ins = new static();

        $ins->total_items = $total_items;
        $ins->items_per_page = $items_per_page;
        $ins->current_page = $current_page;
        
        // Core pagination values
        $ins->total_items = (int) max(0, $ins->total_items);
        $ins->items_per_page = (int) max(1, $ins->items_per_page);
        $ins->total_pages = (int) ceil($ins->total_items / $ins->items_per_page);

        $ins->current_first_item = (int) min((($ins->current_page - 1) * $ins->items_per_page) + 1, $ins->total_items);
        $ins->current_last_item = (int) min($ins->current_first_item + $ins->items_per_page - 1, $ins->total_items);

        // If there is no first/last/previous/next page, relative to the
        // current page, value is set to FALSE. Valid page number otherwise.
        $ins->first_page = ($ins->current_page == 1) ? false : 1;
        $ins->last_page = ($ins->current_page >= $ins->total_pages) ? false : $ins->total_pages;
        $ins->previous_page = ($ins->current_page > 1) ? $ins->current_page - 1 : false;
        $ins->next_page = ($ins->current_page < $ins->total_pages) ? $ins->current_page + 1 : false;

        if ($ins->num_links) {
            $ins->nav_start = (($ins->current_page - $ins->num_links) > 0) ? $ins->current_page - ($ins->num_links - 1) : 1;
            $ins->nav_end = (($ins->current_page + $ins->num_links) < $ins->total_pages) ? $ins->current_page + $ins->num_links : $ins->total_pages;
        } else {
            $ins->nav_start = 1;
            $ins->nav_end = $ins->total_pages;
        }
        return $ins;
    }

    public function links($view = 'rapyd::pagination')
    {
        if ($this->total_pages < 2)
            return '';

        return View::make($view, get_object_vars($this));
    }


    public function offset()
    {
        return (int) ($this->current_page - 1) * $this->items_per_page;
    }

}