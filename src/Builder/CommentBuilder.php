<?php

namespace Wenprise\ORM\Builder;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class CommentBuilder
 *
 * @package Wenprise\ORM\Model\Builder
 * @author Junior Grossi <juniorgro@gmail.com>
 */
class CommentBuilder extends Builder
{
    /**
     * @return CommentBuilder
     */
    public function approved()
    {
        return $this->where('comment_approved', 1);
    }
}
