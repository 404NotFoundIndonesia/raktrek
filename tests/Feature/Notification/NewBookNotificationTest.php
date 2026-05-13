<?php

namespace Tests\Feature\Notification;

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use App\Notifications\NewBookByFavouriteAuthorNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NewBookNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_book_notifies_users_who_favourited_the_author(): void
    {
        Notification::fake();

        $staff  = User::factory()->create(['role' => 'staff']);
        $fan1   = User::factory()->create();
        $fan2   = User::factory()->create();
        $author = Author::factory()->create();

        $fan1->favouriteAuthors()->attach($author);
        $fan2->favouriteAuthors()->attach($author);

        $this->actingAs($staff)->post('/admin/books', [
            'title'     => 'New Book',
            'author_id' => $author->id,
        ]);

        Notification::assertSentTo($fan1, NewBookByFavouriteAuthorNotification::class);
        Notification::assertSentTo($fan2, NewBookByFavouriteAuthorNotification::class);
    }

    public function test_user_who_did_not_favourite_author_receives_nothing(): void
    {
        Notification::fake();

        $staff   = User::factory()->create(['role' => 'staff']);
        $fan     = User::factory()->create();
        $notFan  = User::factory()->create();
        $author  = Author::factory()->create();

        $fan->favouriteAuthors()->attach($author);

        $this->actingAs($staff)->post('/admin/books', [
            'title'     => 'New Book',
            'author_id' => $author->id,
        ]);

        Notification::assertSentTo($fan, NewBookByFavouriteAuthorNotification::class);
        Notification::assertNotSentTo($notFan, NewBookByFavouriteAuthorNotification::class);
    }

    public function test_no_notifications_when_book_has_no_author(): void
    {
        Notification::fake();

        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->post('/admin/books', [
            'title'     => 'Orphan Book',
            'author_id' => null,
        ]);

        Notification::assertNothingSent();
    }

    public function test_member_cannot_create_book(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($member)->post('/admin/books', ['title' => 'Test']);

        $response->assertForbidden();
    }
}
