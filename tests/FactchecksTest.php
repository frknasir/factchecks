<?php

namespace StarfolkSoftware\Factchecks\Tests;

use StarfolkSoftware\Factchecks\Tests\Models\ApprovedUser;
use StarfolkSoftware\Factchecks\Tests\Models\Post;
use Illuminate\Foundation\Auth\User;

class FactchecksTest extends TestCase
{
  private $factcheck1 = [
    'claim' => 'You dont have a name',
    'conclusion' => 'Thats not true. I have a name'
  ];

  private $factcheck2 = [
    'claim' => 'Messi is the greatest of all time',
    'conclusion' => 'You are absolutely right'
  ];

  /** @test */
  public function users_without_factchecker_interface_do_not_get_approved()
  {
    $post = Post::create([
      'title' => 'Some post'
    ]);

    $post->factcheck(array($this->factcheck1, $this->factcheck2));

    $factcheck = $post->factchecks()->first();

    $this->assertNull($factcheck->approved_at);
  }

  /** @test */
  public function models_can_store_factchecks()
  {
    $post = Post::create([
      'title' => 'Some post'
    ]);

    $post->factcheck(array($this->factcheck1));
    $post->factcheck(array($this->factcheck2));

    $this->assertCount(2, $post->factchecks);

    // $this->assertSame('this is a comment', $post->factchecks[0]->comment);
    // $this->assertSame('this is a different comment', $post->factchecks[1]->comment);
  }

  /** @test */
  public function factchecks_without_users_have_no_relation()
  {
    $post = Post::create([
      'title' => 'Some post'
    ]);

    $factcheck = $post->factcheck(array($this->factcheck1));

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

    $factcheck = $post->factcheck(array($this->factcheck2));

    $this->assertSame($user->toArray(), $factcheck->factchecker->toArray());
  }

  /** @test */
  public function factchecks_can_be_posted_as_different_users()
  {
    $user = User::first();

    $post = Post::create([
      'title' => 'Some post'
    ]);

    $factcheck = $post->factcheckAsUser($user, array($this->factcheck1));

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

    $factcheck = $post->factcheck(array($this->factcheck1));

    $this->assertNull($factcheck->submitted_at);

    $factcheck->submit();

    $this->assertTrue($factcheck->submitted_at instanceof \Illuminate\Support\Carbon);
  }

  /** @test */
  public function factchecks_can_be_approved()
  {
    $user = User::first();

    auth()->login($user);

    $post = Post::create([
      'title' => 'Some post'
    ]);

    $factcheck = $post->factcheck(array($this->factcheck1));

    $this->assertNull($factcheck->approved_at);

    $factcheck->approve();

    $this->assertTrue($factcheck->approved_at instanceof \Illuminate\Support\Carbon);
    $this->assertSame($user->id, $factcheck->approved_by);
  }

  /** @test */
  public function factchecks_can_be_published()
  {
    $user = User::first();

    auth()->login($user);

    $post = Post::create([
      'title' => 'Some post'
    ]);

    $factcheck = $post->factcheck(array($this->factcheck1));

    $this->assertNull($factcheck->published_at);

    $factcheck->publish();

    $this->assertTrue($factcheck->published_at instanceof \Illuminate\Support\Carbon);
  }

  /** @test */
  public function factchecks_resolve_the_factchecked_model()
  {
    $user = User::first();

    $post = Post::create([
      'title' => 'Some post'
    ]);

    $factcheck = $post->factcheck(array($this->factcheck1));

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

    $factcheck = $post->factcheckAsUser($user, array($this->factcheck1));

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

    $post->factcheck(array($this->factcheck1));
    $post->factcheckAsUser($user, array($this->factcheck2));

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

    $factcheck = $post->factcheck(array($this->factcheck1));
    $factcheck->submit();
    $post->factcheck(array($this->factcheck2));

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

    $factcheck = $post->factcheck(array($this->factcheck1));
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

    $post->factcheck(array($this->factcheck1));
    $post->factcheckAsUser($user, array($this->factcheck2));

    $this->assertCount(2, $post->factchecks);
    $this->assertCount(1, $post->factchecks()->published()->get());
  }
}
