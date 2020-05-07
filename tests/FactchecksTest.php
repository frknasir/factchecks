<?php

namespace StarfolkSoftware\Factchecks\Tests;

use StarfolkSoftware\Factchecks\Tests\Models\ApprovedUser;
use StarfolkSoftware\Factchecks\Tests\Models\Post;
use Illuminate\Foundation\Auth\User;

class FactchecksTest extends TestCase
{
  private $claim1 = "You dont have a name";
  private $conclusion1 = "Thats not true. I have a name";
  private $claim2 = "Messi is the greatest of all time";
  private $conclusion2 = "You are absolutely right";

  /** @test */
  public function users_without_factchecker_interface_do_not_get_approved()
  {
    $post = Post::create([
      'title' => 'Some post'
    ]);

    $post->factcheck($this->claim1, $this->conclusion1);

    $factcheck = $post->factchecks()->first();

    $this->assertNull($factcheck->approved_at);
  }

  /** @test */
  public function models_can_store_factchecks()
  {
    $post = Post::create([
      'title' => 'Some post'
    ]);

    $post->factcheck($this->claim1, $this->conclusion1);
    $post->factcheck($this->claim2, $this->conclusion2);

    $this->assertCount(2, $post->factchecks);

    $this->assertSame('You dont have a name', $post->factchecks[0]->claim);
    $this->assertSame('Messi is the greatest of all time', $post->factchecks[1]->claim);
  }

  /** @test */
  public function factchecks_without_users_have_no_relation()
  {
    $post = Post::create([
      'title' => 'Some post'
    ]);

    $factcheck = $post->factcheck($this->claim1, $this->conclusion1);

    $this->assertNull($factcheck->factchecker);
    $this->assertNull($factcheck->user_id);
  }

  /** @test */
  public function factchecks_can_be_posted_as_authenticated_users()
  {
    $user = User::first();

    auth()->login($user);

    $post = Post::create([
      'title' => 'Some post'
    ]);

    $factcheck = $post->factcheck($this->claim2, $this->conclusion2);

    $this->assertSame($user->toArray(), $factcheck->factchecker->toArray());
  }

  /** @test */
  public function factchecks_can_be_posted_as_different_users()
  {
    $user = User::first();

    $post = Post::create([
      'title' => 'Some post'
    ]);

    $factcheck = $post->factcheckAsUser($user, $this->claim1, $this->conclusion1);

    $this->assertSame($user->toArray(), $factcheck->factchecker->toArray());
  }

  /** @test */
  public function factchecks_can_be_submitted()
  {
    $user = User::first();

    auth()->login($user);

    $post = Post::create([
      'title' => 'Some post'
    ]);

    $factcheck = $post->factcheck($this->claim1, $this->conclusion1);

    $this->assertNull($factcheck->submitted_at);

    $factcheck->submit();

    $this->assertTrue($factcheck->submitted_at instanceof \Illuminate\Support\Carbon);
    $this->assertTrue($factcheck->getSubmittedAttribute());
  }

  /** @test */
  public function factchecks_can_be_approved()
  {
    $user = User::first();

    auth()->login($user);

    $post = Post::create([
      'title' => 'Some post'
    ]);

    $factcheck = $post->factcheck($this->claim1, $this->conclusion1);

    $this->assertNull($factcheck->approved_at);

    $factcheck->approve();

    $this->assertTrue($factcheck->approved_at instanceof \Illuminate\Support\Carbon);
    $this->assertSame($user->id, $factcheck->approved_by);
    $this->assertTrue($factcheck->getApprovedAttribute());
  }

  /** @test */
  public function factchecks_can_be_published()
  {
    $user = User::first();

    auth()->login($user);

    $post = Post::create([
      'title' => 'Some post'
    ]);

    $factcheck = $post->factcheck($this->claim1, $this->conclusion1);

    $this->assertNull($factcheck->published_at);

    $factcheck->publish();

    $this->assertTrue($factcheck->published_at instanceof \Illuminate\Support\Carbon);
    $this->assertTrue($factcheck->getPublishedAttribute());
  }

  /** @test */
  public function factchecks_resolve_the_factchecked_model()
  {
    $user = User::first();

    $post = Post::create([
      'title' => 'Some post'
    ]);

    $factcheck = $post->factcheck($this->claim1, $this->conclusion1);

    $this->assertSame($factcheck->factcheckable->id, $post->id);
    $this->assertSame($factcheck->factcheckable->title, $post->title);
  }

  /** @test */
  public function users_can_be_auto_approved()
  {
    $user = ApprovedUser::first();

    $post = Post::create([
      'title' => 'Some post'
    ]);

    $factcheck = $post->factcheckAsUser($user, $this->claim1, $this->conclusion1);

    $this->assertSame($user->id, $factcheck->approved_by);
  }

  /** @test */
  public function factchecks_have_a_draft_scope()
  {
    $user = User::first();

    auth()->login($user);

    $post = Post::create([
      'title' => 'Some post'
    ]);

    $post->factcheck($this->claim1, $this->conclusion1);
    $post->factcheckAsUser($user, $this->claim2, $this->conclusion2);

    $this->assertCount(2, $post->factchecks()->draft()->get());
  }

  /** @test */
  public function factchecks_have_a_submitted_scope()
  {
    $user = User::first();

    auth()->login($user);

    $post = Post::create([
      'title' => 'Some post'
    ]);

    $factcheck = $post->factcheck($this->claim1, $this->conclusion1);
    $factcheck->submit();
    $post->factcheck($this->claim2, $this->conclusion2);

    $this->assertCount(1, $post->factchecks()->submitted()->get());
  }

  /** @test */
  public function factchecks_have_an_approved_scope()
  {
    $user = User::first();

    auth()->login($user);

    $post = Post::create([
      'title' => 'Some post'
    ]);

    $factcheck = $post->factcheck($this->claim1, $this->conclusion1);
    $this->assertCount(0, $post->factchecks()->submitted()->get());

    $factcheck->submit();
    $this->assertCount(1, $post->factchecks()->submitted()->get());

    $factcheck->approve();
    $this->assertCount(0, $post->factchecks()->submitted()->get());
    $this->assertCount(1, $post->factchecks()->approved()->get());
  }

  /** @test */
  public function factchecks_have_a_published_scope()
  {
    $user = ApprovedUser::first();

    $post = Post::create([
      'title' => 'Some post'
    ]);

    $post->factcheck($this->claim1, $this->conclusion1);
    $post->factcheckAsUser($user, $this->claim2, $this->conclusion2);

    $this->assertCount(2, $post->factchecks);
    $this->assertCount(1, $post->factchecks()->published()->get());
  }
}
