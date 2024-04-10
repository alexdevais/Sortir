<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class LoginSuccessEventListener
{
    private $mailer;
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer =$mailer;
    }

    #[AsEventListener(event: LoginSuccessEvent::class)]
    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
//        dd($event);
    $user = $event->getUser();
    $ipAddress = $event->getRequest()->getClientIp();

    //send email to the user
        $email = (new Email())
            ->from('your@example.com')
            ->to($user->getEmail())
            ->subject('Login Successful')
            ->html(sprintf('You have successfully logged in. Your IP address: %s', $ipAddress));

        $this->mailer->send($email);

    }
}