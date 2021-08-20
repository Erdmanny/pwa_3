<?php

namespace App\Validation;

use App\Models\UserModel;

class UserRules
{

    public function validateUser(string $str, string $fields, array $data)
    {
        $_userModel = new UserModel();


        $user = $_userModel->validatePassword($data['email'], $data['password']);

        if (!$user) {
            return false;
        } else {
            return true;
        }
    }
}