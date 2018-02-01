<?php

namespace Wenprise\ORM\WP;

use Wenprise\ORM\Eloquent\Model;

/**
 * Class TermRelationship.
 *
 * @package Corcel\Model
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class TermRelationship extends Model
{
    /**
     * @var array
     */
    protected $primaryKey = ['object_id', 'term_taxonomy_id'];

    /**
     * @var bool
     */
    public $timestamps = false;

    protected $table = 'term_relationships';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'object_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taxonomy()
    {
        return $this->belongsTo(Taxonomy::class, 'term_taxonomy_id');
    }
}
