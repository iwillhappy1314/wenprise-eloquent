<?php

namespace Wenprise\ORM\Meta;

use Wenprise\ORM\WP\Post;

/**
 * Class PostMeta
 *
 * @package Wenprise\ORM\Model\Meta
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class PostMeta extends Meta
{

	protected $primaryKey = 'meta_id';

	protected $table = 'postmeta';

    /**
     * @var array
     */
    protected $fillable = ['meta_key', 'meta_value', 'post_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
