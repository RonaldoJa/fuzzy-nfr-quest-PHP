<?php


class Messages
{

    static $authMessages = [
        'en' => [
            'invalid.credentials' => 'Invalid email or password.',
            'password.length' => 'Password must be more than 5 characters.',
            'user.exists' => 'The user already exists.',
            'user.created' => 'User created successfully.',
        ],
        'es' => [
            'invalid.credentials' => 'Correo electrónico o contraseña no válidos.',
            'password.length' => 'La contraseña debe tener más de 5 caracteres.',
            'user.exists' => 'El usuario ya existe.',
            'user.created' => 'Usuario creado con éxito.',
        ]
    ];

    static $changePasswordMessages = [
        'en' => [
           'user.not.found' => 'The user is not registered.',
           'email.not.send' => 'The email could not be sent.',
           'email.send' => 'Your code has been sent to your email. Please check your inbox.',
           'code.invalid' => 'The code entered is not valid.',
           'code.expired' => 'The code entered has expired.',
           'error.reset.password' => 'The password could not be reset, try again.',
           'success.reset.password' => 'Password reset successfully.',
        ],
        'es' => [
            'user.not.found' => 'El usuario no se encuentra registrado.',
            'email.not.send' => 'El correo electrónico no pudo ser enviado.',
            'email.send' => 'Tu código ha sido enviado a tu correo electrónico. Por favor, verifica tu bandeja de entrada.',
            'code.invalid' => 'El código ingresado no es valido.',
            'code.expired' => 'El código ingresado ha expirado.',
            'error.reset.password' => 'No se pudo restablecer la contraseña, intente de nuevo.',
            'success.reset.password' => 'Contraseña restablecida con éxito.',
        ]
    ];

    static $gameRoomMessages = [
        'en' => [
            'game.room.not.found' => 'The game room you are looking for does not exist. Check the code and try again.',
            'game.room.completed' => 'You have already completed the game in this room. Please join another room.',
            'game.room.not.available' => 'This game room is no longer available. Please check with your teacher.',
            'game.room.expired' => 'This game room is no longer available because it has expired. Please check with the teacher or start a new room.',
            'game.room.change.status' => 'The status of the game room has been changed successfully.',
            'game.room.update.ending.date' => 'The ending date of the game room has been updated successfully.',
            'questions.update' => 'The questions have been updated successfully.',
        ],
        'es' => [
            'game.room.not.found' => 'La sala de juego que estás buscando no existe. Verifica el código e inténtalo nuevamente.',
            'game.room.completed' => 'Ya completaste el juego en esta sala. Por favor, únete a otra sala.',
            'game.room.not.available' => 'Esta sala de juego ya no está disponible. Por favor, verifique con su docente.',
            'game.room.expired' => 'Esta sala de juego ya no está disponible porque ha expirado. Por favor, verifique con el docente o inicie una nueva sala',
            'game.room.change.status' => 'El estado de la sala de juego ha sido cambiado con éxito.',
            'game.room.update.ending.date' => 'La fecha de finalización de la sala de juego ha sido actualizada con éxito.',
            'questions.update' => 'Las preguntas han sido actualizadas con éxito.',
        ]
    ];
}
