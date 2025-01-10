<?php
require_once 'config/Database.php';


class ChangePasswordService
{
    public static function updateOrInsertPasswordResetCode(string $email, string $code, string $expiresAt)
    {
        $query = "SELECT COUNT(*) FROM password_resets_otps WHERE email = :email";
        $stmt = Database::getConn()->prepare($query);
        $stmt->execute(['email' => $email]);
        $exists = $stmt->fetchColumn() > 0;

        if ($exists) {
            self::updateOTP($email, $code, $expiresAt);
        } else {
            self::insertOTP($email, $code, $expiresAt);
        }

        return true;
    }

    private static function insertOTP(string $email, string $code, string $expiresAt)
    {
        $query = "INSERT INTO password_resets_otps (email, otp, expires_at, created_at)
                  VALUES (:email, :otp, :expires_at, :created_at)";
        $stmt = Database::getConn()->prepare($query);
        $stmt->execute([
            'email' => $email,
            'otp' => $code,
            'expires_at' => $expiresAt,
            'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    private static function updateOTP(string $email, string $code, string $expiresAt)
    {
        $query = "UPDATE password_resets_otps 
                  SET otp = :otp, expires_at = :expires_at, created_at = :created_at 
                  WHERE email = :email";
        $stmt = Database::getConn()->prepare($query);
        $stmt->execute([
            'email' => $email,
            'otp' => $code,
            'expires_at' => $expiresAt,
            'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    public static function getPasswordResetCode($email, $code)
    {
        $query = "SELECT * from password_resets_otps WHERE email = :email and otp = :code limit 1";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return null;
        }
        return $result;
    }

    public static function deletePasswordResetCode($email)
    {
        $query = "DELETE FROM password_resets_otps WHERE email = :email";
        $stmt = Database::getConn()->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
