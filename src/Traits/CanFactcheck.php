<?php

namespace StarfolkSoftware\Factchecks\Traits;

trait CanFactcheck
{
  /**
   * Check if a factcheck for a specific model needs to be approved.
   * @param mixed $model
   * @return bool
   */
  public function needsFactcheckApproval($model): bool
  {
    return true;
  }
}
