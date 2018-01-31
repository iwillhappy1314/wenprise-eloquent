<?php

namespace Wenprise\ORM\WP;


use Wenprise\ORM\Eloquent\Model;

class User extends Model
{
    protected $primaryKey = 'ID';
    protected $timestamp = false;

    public function meta()
    {
        return $this->hasMany('Wenprise\ORM\WP\UserMeta', 'user_id');
    }
}