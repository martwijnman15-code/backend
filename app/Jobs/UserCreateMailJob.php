<?php

namespace App\Jobs;

use App\Mail\UserCreateMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class UserCreateMailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user) {}

    public function handle(): void
    {
        Mail::to($this->user->email)->send(new UserCreateMail);
    }
}
