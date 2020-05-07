<?php

namespace StarfolkSoftware\Factchecks\Traits;


use Illuminate\Database\Eloquent\Model;
use StarfolkSoftware\Factchecks\Contracts\Factchecker;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFactchecks
{
  /**
   * Return all factchecks for this model.
   *
   * @return MorphMany
   */
  public function factchecks()
  {
    return $this->morphMany(config('factchecks.factcheck_class'), 'factcheckable');
  }

  /**
   * Attach a factcheck to this model.
   *
   * @param string $claim
   * @param string $conclusion
   * @return \Illuminate\Database\Eloquent\Model
   */
  public function factcheck(string $claim, string $conclusion)
  {
    return $this->factcheckAsUser(auth()->user(), $claim, $conclusion);
  }

  /**
   * Attach a factcheck to this model as a specific user.
   *
   * @param Model|null $user
   * @param string $claim
   * @param string $conclusion
   * @return \Illuminate\Database\Eloquent\Model
   */
  public function factcheckAsUser(?Model $user, string $claim, string $conclusion)
  {
    $factcheckClass = config('factchecks.factcheck_class');

    $factcheck = new $factcheckClass([
      'claim' => $claim,
      'conclusion' => $conclusion,
      'submitted_at' => ($user instanceof Factchecker && !$user->needsFactcheckApproval($this)) ? now() : NULL,
      'approved_at' => ($user instanceof Factchecker && !$user->needsFactcheckApproval($this)) ? now() : NULL,
      'published_at' => ($user instanceof Factchecker && !$user->needsFactcheckApproval($this)) ? now() : NULL,
      'approved_by' => ($user instanceof Factchecker && !$user->needsFactcheckApproval($this)) ? $user->getKey() : NULL,
      'user_id' => is_null($user) ? null : $user->getKey(),
      'factcheckable_id' => $this->getKey(),
      'factcheckable_type' => get_class(),
    ]);

    return $this->factchecks()->save($factcheck);
  }
}
