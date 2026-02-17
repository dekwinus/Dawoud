<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Sale;

class InvoiceDue extends Notification
{
    use Queueable;

    public $sale;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
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
                    ->subject('Invoice Due Reminder: ' . $this->sale->Ref)
                    ->line('The invoice ' . $this->sale->Ref . ' is due.')
                    ->action('View Invoice', url('/app/reports/sales?filter=due_soon'))
                    ->line('Thank you for using our application!');
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
            'sale_id' => $this->sale->id,
            'ref' => $this->sale->Ref,
            'amount' => $this->sale->GrandTotal,
            'due' => $this->sale->GrandTotal - $this->sale->paid_amount,
        ];
    }
}
