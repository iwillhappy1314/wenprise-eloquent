<?php

namespace Wenprise\ORM\Meta;

use Wenprise\ORM\WP\User;

/**
 * Class UserMeta
 *
 * @package Wenprise\ORM\Model\Meta
 * @author Mickael Burguet <www.rundef.com>
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class UserMeta extends Meta
{

	protected $primaryKey = 'umeta_id';

	public $timestamps    = false;

	/**
	 * @var string
	 */
	protected $table = 'usermeta';

    /**
     * @var array
     */
    protected $fillable = ['meta_key', 'meta_value', 'user_id'];


	/**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
