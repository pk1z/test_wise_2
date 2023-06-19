<?php

namespace App;

interface UserEmailSenderInterface
{
    /**
     * @throws EmailSendException
     */
    public function sendEmailChangedNotification(string $oldEmail, string $newEmail): void;
}
