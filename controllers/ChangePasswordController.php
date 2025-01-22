<?php
require_once 'services/UserService.php';
require_once 'services/ChangePasswordService.php';
require_once 'helpers/globalHelper.php';
require_once 'config/PHPMailerConfig.php';
require_once 'helpers/messages.php';

class ChangePasswordController
{
    public static function sendOTP()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $language = isset($data['language']) ? trim($data['language']) : 'es';

            $email = trim($data['email']) ?? null;

            if (empty($email)) {
                return GlobalHelper::generalResponse(null, 'El correo electrónico es obligatorio y no puede estar vacío.', 400);
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return GlobalHelper::generalResponse(null, 'El correo electrónico no es válido.', 400);
            }

            $user = UserService::getByEmail($email);

            if (!$user) {
                return GlobalHelper::generalResponse(null, Messages::$changePasswordMessages[$language]["user.not.found"], 404);
            }

            $code = rand(100000, 999999);
            $now = new DateTime();
            $expiresAt = $now->add(new DateInterval('PT10M'));
            $expiresAt = $expiresAt->format('Y-m-d H:i:s');
            $year = date('Y');

            ChangePasswordService::updateOrInsertPasswordResetCode($email, $code, $expiresAt);

            $htmlTemplate = file_get_contents('resources/templates/send-code.html');

            $htmlTemplate = str_replace('{{username}}', $user['name'], $htmlTemplate);
            $htmlTemplate = str_replace('{{code}}', $code, $htmlTemplate);
            $htmlTemplate = str_replace('{{year}}', $year, $htmlTemplate);

            $send = new SendEmailEntity($email, 'Tu código para restablecer la contraseña', $htmlTemplate);

            $mail = PHPMailerConfig::sendEmail($send);

            if (!$mail->send()) {
                return GlobalHelper::generalResponse(null, Messages::$changePasswordMessages[$language]["email.not.send"], 500);
            } else {
                return GlobalHelper::generalResponse(null, Messages::$changePasswordMessages[$language]["email.send"], 200);
            }
        } catch (\Throwable $th) {
            return GlobalHelper::generalResponse(null, $th->getMessage(), 500);
        }
    }

    public static function resetPassword()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $email = trim($data['email']) ?? null;
            $code = trim($data['otp']) ?? null;
            $password = trim($data['password']) ?? null;
            $password_confirmation = trim($data['password_confirmation']) ?? null;

            $language = isset($data['language']) ? trim($data['language']) : 'es';

            if (empty($email) || empty($code) || empty($password) || empty($password_confirmation)) {
                return GlobalHelper::generalResponse(null, 'Todos los campos son obligatorios y no pueden estar vacíos.', 400);
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return GlobalHelper::generalResponse(null, 'El correo electrónico no es válido.', 400);
            }

            if ($password !== $password_confirmation) {
                return GlobalHelper::generalResponse(null, 'Las contraseñas no coinciden.', 400);
            }

            $user = UserService::getByEmail($email);

            if (!$user) {
                return GlobalHelper::generalResponse(null, Messages::$changePasswordMessages[$language]["user.not.found"], 404);
            }

            $resetPassword   = ChangePasswordService::getPasswordResetCode($email, $code);

            if (!$resetPassword) {
                return GlobalHelper::generalResponse(null, Messages::$changePasswordMessages[$language]["code.invalid"], 400);
            }

            if ($resetPassword['expires_at'] < date('Y-m-d H:i:s')) {
                return GlobalHelper::generalResponse(null, Messages::$changePasswordMessages[$language]["code.expired"], 400);
            }

            $passwordHash = password_hash($password, PASSWORD_DEFAULT) ?? null;

            $resetPassword = UserService::resetPassword($email, $passwordHash);

            if ($resetPassword == 0) {
                return GlobalHelper::generalResponse(null, Messages::$changePasswordMessages[$language]["error.reset.password"], 500);
            }

            try {
                ChangePasswordService::deletePasswordResetCode($email);
            } catch (\Throwable $th) {
            }

            return GlobalHelper::generalResponse(null, Messages::$changePasswordMessages[$language]["success.reset.password"], 200);
        } catch (\Throwable $th) {
            return GlobalHelper::generalResponse(null, $th->getMessage(), 500);
        }
    }
}
