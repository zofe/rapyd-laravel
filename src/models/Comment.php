<?php namespace Zofe\Rapyd\Models;

/**
 * Comment
 */
class Comment extends \Eloquent
{

	protected $table = 'demo_comments';
    protected $primaryKey = 'comment_id';

    public function article(){
        return $this->belongsTo('Zofe\Rapyd\Models\Article');
    }

    public function author(){
        return $this->belongsTo('Zofe\Rapyd\Models\Author');
    }
}