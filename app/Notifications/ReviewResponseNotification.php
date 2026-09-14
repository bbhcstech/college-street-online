<?php

namespace App\Notifications;

use App\Models\BookReview;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewResponseNotification extends Notification
{
    use Queueable;

    public function __construct(public BookReview $review) {}

    public function via(object $notifiable): array { return ['database', 'mail']; }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Admin replied to your review',
            'message' => $this->review->admin_response,
            'book_title' => $this->review->book?->title ?? 'your purchased book',
            'url' => route('books.show', $this->review->book_id).'#reader-reviews',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('We replied to your book review')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('An administrator replied to your review of “'.($this->review->book?->title ?? 'your book').'”.')
            ->line($this->review->admin_response)
            ->action('View the reply', route('books.show', $this->review->book_id).'#reader-reviews');
    }
}
