<?php

namespace App\Services;

use App\Models\EmailInviteProject;
use App\Models\User;
use App\Models\EmailConfirmation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailService
{
    public function sendConfirmationEmail($user, $email, $mailData)
    {
        $token = $this->generateTokenResetPass($user);
        $mailData['token'] = $token;

        Mail::to($email)->send(new \App\Mail\ConfirmationMail($mailData));
    }
    public function generateTokenResetPass($user)
    {
        $emailConfirmation = EmailConfirmation::where('user_id', $user->id)->first();

        if ($emailConfirmation) {
            $token = strtoupper(Str::random(6));
            $emailConfirmation->token = $token;
            $emailConfirmation->save();
        } else {
            $token = strtoupper(Str::random(6));
            $emailConfirmation = new EmailConfirmation();
            $emailConfirmation->user_id = $user->id;
            $emailConfirmation->token = $token;
            $emailConfirmation->save();
        }
        return $token;
    }

   
    public function sendInvitationEmail($emailInvited, $project, $mailData)
    {
        $token = $this->generateTokenProjects($emailInvited, $project);
        $mailData['token'] = $token;

        Mail::to($emailInvited)->send(new \App\Mail\InviteProjectMail($mailData));
    }
    public function generateTokenProjects($email, $project)
    {
        $emailConfirmation = EmailInviteProject::where('user_email', $email)->first();
        $token = strtoupper(Str::random(8));

        if($emailConfirmation){
            $emailConfirmation->token = $token;
            $emailConfirmation->save();
        }
        else{
            $emailConfirmation = new EmailInviteProject();
            $emailConfirmation->project_id = $project->id;
            $emailConfirmation->user_email = $email;
            $emailConfirmation->token = $token;
            $emailConfirmation->save();
        }
        return $token;
    }
}