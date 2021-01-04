<?php
// 'src/Mail/WelcomeMail.php'

namespace StarfolkSoftware\Paysub\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use StarfolkSoftware\Paysub\Models\Invoice;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $billable;
    protected $invoice;

    /**
     * Create a new mail instance.
     *
     * @param mixed $billable
     * @param Invoice $invoice
     */
    public function __construct($billable, Invoice $invoice)
    {
        $this->billable = $billable;
        $this->invoice  = $invoice;
    }

    public function build()
    {
        $data = [
            'vendor' => config('paysub.contact_detail.vendor'),
            'street' => config('paysub.contact_detail.street'),
            'location' => config('paysub.contact_detail.location'),
            'phone' => config('paysub.contact_detail.phone'),
            'url' => config('paysub.contact_detail.url'),
            'vatInfo' => config('paysub.contact_detail.vatInfo'),
        ];

        return $this->view('paysub::invoice')->with(array_merge($data, [
            'invoice' => $this,
            'subscription' => $this->invoice->subscription
        ]))->attachData($this->invoice->pdf($data), 'invoice.pdf', [
            'mime' => 'application/pdf',
        ]);
    }
}
