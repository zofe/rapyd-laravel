<?php namespace Zofe\Rapyd\Models;


/**
 * Category
 */
class Category extends \Eloquent
{

	protected $table = 'demo_categories';
    protected $primaryKey = 'category_id';

    public function articles() {
        return $this->belongsToMany('Zofe\Rapyd\Models\Article', 'demo_article_category', 'category_id','article_id');
    }

}