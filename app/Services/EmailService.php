<?php

namespace App\Services;

use App\Mail\ConfirmationMail;
use App\Models\User;
use App\Models\EmailConfirmation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailService
{
    public function generateToken($user)
    {
        $token = strtoupper(Str::random(6));
        
        $emailConfirmation = new EmailConfirmation();
        $emailConfirmation->user_id = $user->id;
        $emailConfirmation->token = $token;
        $emailConfirmation->save();
        
        return $token;
    }
    
    public function sendConfirmationEmail($user, $email, $mailData)
    {
        $token = $this->generateToken($user);
        $mailData['token'] = $token;

        Mail::to($email)->send(new ConfirmationMail($mailData));
    }
}