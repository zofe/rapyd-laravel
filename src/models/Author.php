<?php namespace Zofe\Rapyd\Models;


/**
 * Author
 */
class Author extends \Eloquent
{

	protected $table = 'demo_users';
    protected $primaryKey = 'user_id';

    public function articles() {
        return $this->hasMany('Zofe\Rapyd\Models\Article');
    }

    public function comments() {
        return $this->hasMany('Zofe\Rapyd\Models\Comment');
    }
}