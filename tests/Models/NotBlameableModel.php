<?php

namespace Culpa\Tests\Models;

use Culpa\Models\Blameable;
use Culpa\Observers\BlameableObserver;
use Illuminate\Database\Eloquent\Model;

/**
 * A model that adds the observer but doesn't say which events to track.
 */
class NotBlameableModel extends Model
{
    use Blameable;

    protected $table = 'posts';
}

NotBlameableModel::observe(new BlameableObserver());
