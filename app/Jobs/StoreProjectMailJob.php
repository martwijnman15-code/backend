<?php

namespace App\Jobs;

use App\Mail\StoreProjectMail;
use App\Models\Customer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class StoreProjectMailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Customer $customer) {}

    public function handle(): void
    {
        Mail::to($this->customer->mail_address)->send(new StoreProjectMail);
    }
}
