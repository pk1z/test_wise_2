<?php

namespace App;

class UserEmailChangerService
{
    private \PDO $db;
    private UserEmailSenderInterface $emailSender;

    public function __construct(\PDO $db, UserEmailSenderInterface $emailSender)
    {
        $this->db = $db;
        $this->emailSender = $emailSender;
    }

    /**
     * @throws \PDOException
     * @throws EmailSendException
     */
    public function changeEmail(int $userId, string $newEmail): void
    {
        $this->db->beginTransaction();

        try {
            $oldEmail = $this->getOldEmailForUpdate($userId); // Получаем старый адрес электронной почты с блокировкой "FOR UPDATE"
            $this->updateEmail($userId, $newEmail); // Обновляем адрес электронной почты в базе данных
            $this->emailSender->sendEmailChangedNotification($oldEmail, $newEmail); // Отправляем уведомление о смене адреса электронной почты

            $this->db->commit();
        } catch (\PDOException|EmailSendException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Получает старый адрес электронной почты пользователя с блокировкой "FOR UPDATE.
     *
     * @throws \PDOException
     */
    private function getOldEmailForUpdate(int $userId): ?string
    {
        $statement = $this->db->prepare('SELECT email FROM users WHERE id = :id FOR UPDATE');
        $statement->bindValue(':id', $userId, \PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        return $result['email'] ?? null;
    }

    /**
     * Устанавливает новый адрес электронной почты пользователю.
     *
     * @throws \PDOException
     */
    private function updateEmail(int $userId, string $newEmail): void
    {
        $statement = $this->db->prepare('UPDATE users SET email = :email WHERE id = :id');
        $statement->bindValue(':id', $userId, \PDO::PARAM_INT);
        $statement->bindValue(':email', $newEmail);
        $statement->execute();
    }
}
