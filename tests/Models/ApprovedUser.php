<?php

namespace StarfolkSoftware\Factchecks\Tests\Models;

use Illuminate\Foundation\Auth\User;
use StarfolkSoftware\Factchecks\Contracts\Factchecker;

class ApprovedUser extends User implements Factchecker
{
  protected $table = 'users';

  /**
   * Check if a factcheck for a specific model needs to be approved.
   * @param mixed $model
   * @return bool
   */
  public function needsFactcheckApproval($model): bool
  {
    return false;
  }
}
