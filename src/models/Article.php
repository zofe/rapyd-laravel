<?php namespace Zofe\Rapyd\Models;


/**
 * Article
 */
class Article extends \Eloquent {

    protected $table = 'demo_articles';
    protected $primaryKey = 'article_id';

    public function author(){
        return $this->belongsTo('Zofe\Rapyd\Models\Author', 'author_id');
    }

    public function comments() {
        return $this->hasMany('Zofe\Rapyd\Models\Comment', 'article_id');
    }

    public function categories() {
        return $this->belongsToMany('Zofe\Rapyd\Models\Category', 'demo_article_category', 'article_id','category_id');
    }

    public function detail() {
        return $this->hasOne('Zofe\Rapyd\Models\ArticleDetail', 'article_id');
    }
}