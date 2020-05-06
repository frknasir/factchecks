<?php

namespace StarfolkSoftware\Factchecks\Tests\Models;

use StarfolkSoftware\Factchecks\Traits\HasFactchecks;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
  use HasFactchecks;

  protected $guarded = [];
}
