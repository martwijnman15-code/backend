<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\User;
use App\Mail\StoreProjectMail;

class StoreProjectMailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $customer
    ) {}

    public function handle(): void
    {
        Mail::to($this->customer->mail)
            ->send(new StoreProjectMail($this->customer));
    }
}