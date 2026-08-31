<?php

return [
    'accepted' => 'O campo :attribute deve ser aceito.',
    'email' => 'O campo :attribute deve ser um e-mail válido.',
    'required' => 'O campo :attribute é obrigatório.',
    'confirmed' => 'A confirmação de :attribute não confere.',
    'unique' => 'Este :attribute já está em uso.',
    'digits' => 'O campo :attribute deve ter :digits dígitos.',
    'min' => [
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'password' => [
        'letters' => 'A :attribute deve conter pelo menos uma letra.',
        'mixed' => 'A :attribute deve conter letras maiúsculas e minúsculas.',
        'numbers' => 'A :attribute deve conter pelo menos um número.',
        'uncompromised' => 'A :attribute escolhida apareceu em um vazamento de dados. Escolha outra.',
    ],
    'attributes' => [
        'name' => 'nome',
        'email' => 'e-mail',
        'password' => 'senha',
        'role' => 'perfil',
        'code' => 'código',
        '2fa_code' => 'código OTP',
        'recovery_code' => 'código de recuperação',
    ],
];
