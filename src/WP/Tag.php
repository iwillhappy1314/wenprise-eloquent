<?php

namespace Wenprise\ORM\WP;

/**
 * Tag class.
 *
 * @package Corcel\Model
 * @author Mickael Burguet <www.rundef.com>
 */
class Tag extends Taxonomy
{
    /**
     * @var string
     */
    protected $taxonomy = 'post_tag';
}
