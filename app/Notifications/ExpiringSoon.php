<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpiringSoon extends Notification
{
    use Queueable;

    public $product;
    public $daysLeft;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($product, $daysLeft)
    {
        $this->product = $product;
        $this->daysLeft = $daysLeft;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Product Expiring: ' . $this->product->name)
                    ->line('The product ' . $this->product->name . ' (' . $this->product->code . ') is expiring in ' . $this->daysLeft . ' days.')
                    ->action('View Inventory', url('/app/reports/stock_alert'))
                    ->line('Please take necessary action.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'product_name' => $this->product->name,
            'product_code' => $this->product->code,
            'expiry_date' => $this->product->expiry_date,
            'days_left' => $this->daysLeft,
        ];
    }
}
