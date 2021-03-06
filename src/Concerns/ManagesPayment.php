<?php

namespace StarfolkSoftware\Paysub\Concerns;

use Carbon\Carbon;
use StarfolkSoftware\Paysub\Events\InvoicePaid;
use StarfolkSoftware\Paysub\Exceptions\PaymentError;
use StarfolkSoftware\Paysub\Models\Invoice;
use StarfolkSoftware\Paysub\Models\Payment;

trait ManagesPayment
{
    /**
     * Make a payment on invoice.
     * Fires InvoicePaid event
     *
     * @param Invoice $invoice
     * @return bool
     * @throws PaymentError
     */
    public function payInvoice($invoice = null)
    {
        if (! $invoice) {
            $invoice = $this->subscription()->openInvoice();
        }

        if ($paymentResult = $this->pay($invoice)) {
            event(new InvoicePaid($invoice));
        }

        return $paymentResult;
    }

    /**
     * Make a payment on invoice
     *
     * @param Invoice $invoice
     * @return bool
     * @throws PaymentError
     */
    public function pay(Invoice $invoice)
    {
        $auth = $this->defaultAuth();

        $response = $this->chargeUsingPaystack(
            $invoice->total,
            $auth->email,
            $auth->code
        );

        if ($response->status) {
            $dataObject = $response->data;

            // save payment
            Payment::create([
                'paystack_id' => $dataObject->id,
                'authorization_id' => $auth->id,
                'reference' => $dataObject->reference,
                'invoice_id' => $invoice->id,
                'amount' => $dataObject->amount,
                'paid_at' => Carbon::now(),
            ]);

            return true;
        }

        throw PaymentError::default($response->message);
    }
}
