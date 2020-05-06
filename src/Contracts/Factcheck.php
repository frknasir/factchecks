<?php

namespace StarfolkSoftware\Factchecks\Contracts;

use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphTo};

interface Factcheck
{
  /**
   * Check to see if the post is published.
   *
   * @return bool
   */
  public function getPublishedAttribute(): bool;

  /**
   * Check to see if the post is submitted for approval.
   *
   * @return bool
   */
  public function getSubmittedAttribute(): bool;

  /**
   * Check to see if the post is approved.
   *
   * @return bool
   */
  public function getApprovedAttribute(): bool;

  /**
   * Scope a query to only include published factchecks.
   *
   * @param Builder $query
   * @return Builder
   */
  public function scopePublished($query): Builder;

  /**
   * Scope a query to only include factchecks submitted for approval.
   *
   * @param Builder $query
   * @return Builder
   */
  public function scopeSubmitted($query): Builder;

  /**
   * Scope a query to only include approved factchecks.
   *
   * @param Builder $query
   * @return Builder
   */
  public function scopeApproved($query): Builder;

  /**
   * Scope a query to only include drafted factchecks.
   *
   * @param Builder $query
   * @return Builder
   */
  public function scopeDraft($query): Builder;

  public function factcheckable(): MorphTo;

  public function factchecker(): BelongsTo;

  /**
   * submit a factcheck
   *
   * @return Model
   */
  public function submit(): Model;

  /**
   * approve a factcheck
   *
   * @return Model
   */
  public function approve(): Model;

  /**
   * publish a factcheck
   *
   * @return Model
   */
  public function publish(): Model;

  /**
   * Scope a query to only include factchecks for the current logged in user.
   *
   * @param Builder $query
   * @return Builder
   */
  public function scopeForCurrentUser($query): Builder;
}
