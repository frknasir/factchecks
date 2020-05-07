<?php

return [
  /*
  * When using the "HasFactchecks" trait from this package, we need to know which
  * Eloquent model should be used to retrieve your roles. Of course, it
  * is often just the "Factcheck" model but you may use whatever you like.
  *
  * The model you want to use as a Factcheck model needs to implement the
  * `StarfolkSoftware\Factchecks\Contracts\Factcheck` contract.
  */
  'factcheck_class' => \StarfolkSoftware\Factchecks\Factcheck::class,

  /*
  * The user model that should be used when associating factchecks with
  * factcheckers. If null, the default user provider from your
  * Laravel authentication configuration will be used.
  */
  'user_model' => \Illuminate\Foundation\Auth\User::class,
];
