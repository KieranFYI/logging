<?php

namespace KieranFYI\Tests\Logging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use KieranFYI\Misc\Traits\KeyedTitle;

class KeyedTitleModel extends Model
{
    use KeyedTitle;
    use SoftDeletes;
}