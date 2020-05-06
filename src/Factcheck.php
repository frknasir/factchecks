<?php

namespace StarfolkSoftware\Factchecks;

use Exception;
use Illuminate\Database\Eloquent\{Model, Builder};
use StarfolkSoftware\Factchecks\Traits\HasFactchecks;
use StarfolkSoftware\Factchecks\Contracts\Factcheck as FactcheckContract;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphTo};

class Factcheck extends Model implements FactcheckContract
{
  use HasFactchecks;

  protected $guarded = [];

  protected $casts = [
    'factchecks' => 'array'
  ];

  /**
   * The attributes that should be mutated to dates.
   *
   * @var array
   */
  protected $dates = [
    'submitted_at',
    'approved_at',
    'published_at',
  ];

  /**
   * Check to see if the post is published.
   *
   * @return bool
   */
  public function getPublishedAttribute(): bool
  {
    return ! is_null($this->published_at) && $this->published_at <= now()->toDateTimeString();
  }

  /**
   * Check to see if the post is submitted for approval.
   *
   * @return bool
   */
  public function getSubmittedAttribute(): bool
  {
    return ! is_null($this->submitted_at) && $this->submitted_at <= now()->toDateTimeString();
  }

  /**
   * Check to see if the post is approved.
   *
   * @return bool
   */
  public function getApprovedAttribute(): bool
  {
    return ! is_null($this->approved_at) && $this->approved_at <= now()->toDateTimeString();
  }

  /**
   * Scope a query to only include published factchecks.
   *
   * @param Builder $query
   * @return Builder
   */
  public function scopePublished($query): Builder
  {
    return $query->where([
      ['submitted_at', '<=', now()->toDateTimeString()],
      ['approved_at', '<=', now()->toDateTimeString()],
      ['published_at', '<=', now()->toDateTimeString()]
    ]);
  }

  /**
   * Scope a query to only include factchecks submitted for approval.
   *
   * @param Builder $query
   * @return Builder
   */
  public function scopeSubmitted($query): Builder
  {
    return $query->where([
      ['submitted_at', '<=', now()->toDateTimeString()],
      ['approved_at', '=', NULL],
      ['published_at', '=', NULL]
    ]);
  }

  /**
   * Scope a query to only include approved factchecks.
   *
   * @param Builder $query
   * @return Builder
   */
  public function scopeApproved($query): Builder
  {
    return $query->where([
      ['submitted_at', '<=', now()->toDateTimeString()],
      ['approved_at', '<=', now()->toDateTimeString()],
      ['published_at', '=', NULL]
    ]);
  }

  /**
   * Scope a query to only include drafted factchecks.
   *
   * @param Builder $query
   * @return Builder
   */
  public function scopeDraft($query): Builder
  {
    return $query->where([
      ['submitted_at', '=', NULL],
      ['approved_at', '=', NULL],
      ['published_at', '=', NULL]
    ])->orWhere([
      ['submitted_at', '>', now()->toDateTimeString()],
      ['approved_at', '>', now()->toDateTimeString()],
      ['published_at', '>', now()->toDateTimeString()]
    ]);
  }

  public function factcheckable(): MorphTo
  {
    return $this->morphTo();
  }

  public function factchecker(): BelongsTo
  {
    return $this->belongsTo($this->getAuthModelName(), 'user_id');
  }

  /**
   * submit a factcheck
   *
   * @return Model
   */
  public function submit(): Model {
    $this->update([
      'submitted_at' => now()
    ]);

    return $this;
  }

  /**
   * approve a factcheck
   *
   * @return Model
   */
  public function approve(): Model
  {
    $this->update([
      'approved_at' => now(),
      'approved_by' => auth()->user()->getKey()
    ]);

    return $this;
  }

  /**
   * publish a factcheck
   *
   * @return Model
   */
  public function publish(): Model
  {
    $this->update([
      'published_at' => now(),
    ]);

    return $this;
  }

  /**
   * get authentication model name
   *
   * @return String
   */
  protected function getAuthModelName(): String
  {
    if (config('factchecks.user_model')) {
      return config('factchecks.user_model');
    }

    if (!is_null(config('auth.providers.users.model'))) {
      return config('auth.providers.users.model');
    }

    throw new Exception('Could not determine the factchecker model name.');
  }

  /**
   * Scope a query to only include factchecks for the current logged in user.
   *
   * @param Builder $query
   * @return Builder
   */
  public function scopeForCurrentUser($query): Builder
  {
    return $query->where('user_id', auth()->user()->id ?? null);
  }
}
