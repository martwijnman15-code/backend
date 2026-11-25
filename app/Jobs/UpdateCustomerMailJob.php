<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\User;
use App\Mail\UpdateProjectMail;

class UpdateProjectMailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $customer
    ) {}

    public function handle(): void
    {
        Mail::to($this->customer->mail)
            ->send(new UpdateProjectMail($this->customer));
    }
}