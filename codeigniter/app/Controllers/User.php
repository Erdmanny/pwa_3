<?php namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class User extends BaseController
{
    private $_userModel;

    /**
     * UserController constructor.
     * Init model.
     */
    public function __construct()
    {
        $this->_userModel = new UserModel();
    }

    /**
     * @return string - Login View after unsetting cookies
     */
    public function index(): string
    {
        echo '<script type="text/Javascript">caches.delete("dynamic-v1")</script>';
        if (isset($_COOKIE["error-edit-prename"])) {
            unset($_COOKIE["error-edit-prename"]);
            setcookie("error-edit-prename", "", -1, "/");
        }
        if (isset($_COOKIE["error-edit-surname"])) {
            unset($_COOKIE["error-edit-surname"]);
            setcookie("error-edit-surname", "", -1, "/");
        }
        if (isset($_COOKIE["error-edit-street"])) {
            unset($_COOKIE["error-edit-street"]);
            setcookie("error-edit-street", "", -1, "/");
        }
        if (isset($_COOKIE["error-edit-postcode"])) {
            unset($_COOKIE["error-edit-postcode"]);
            setcookie("error-edit-postcode", "", -1, "/");
        }
        if (isset($_COOKIE["error-edit-city"])) {
            unset($_COOKIE["error-edit-city"]);
            setcookie("error-edit-city", "", -1, "/");
        }
        if (isset($_COOKIE["error-new-prename"])) {
            unset($_COOKIE["error-new-prename"]);
            setcookie("error-new-prename", "", -1, "/");
        }
        if (isset($_COOKIE["error-new-surname"])) {
            unset($_COOKIE["error-new-surname"]);
            setcookie("error-new-surname", "", -1, "/");
        }
        if (isset($_COOKIE["error-new-street"])) {
            unset($_COOKIE["error-new-street"]);
            setcookie("error-new-street", "", -1, "/");
        }
        if (isset($_COOKIE["error-new-postcode"])) {
            unset($_COOKIE["error-new-postcode"]);
            setcookie("error-new-postcode", "", -1, "/");
        }
        if (isset($_COOKIE["error-new-city"])) {
            unset($_COOKIE["error-new-city"]);
            setcookie("error-new-city", "", -1, "/");
        }
        if (isset($_COOKIE["userSecret"])) {
            unset($_COOKIE["userSecret"]);
            setcookie("userSecret", "", -1, "/");
        }
        if (isset($_COOKIE["userID"])) {
            unset($_COOKIE["userID"]);
            setcookie("userID", "", -1, "/");
        }
        if (isset($_COOKIE["token"])) {
            unset($_COOKIE["token"]);
            setcookie("token", "", -1, "/");
        }
        return view('login');
    }

    /**
     * @return RedirectResponse|string
     *
     * Validate Login
     */
    public function login()
    {
        helper(['form', 'url']);

        $mail = $this->request->getVar('email');
        $password = $this->request->getVar('password');


        $error = $this->validate([
            'email' => 'required|valid_email',
            'password' => 'required|validateUser[email,password]'
        ],
            [
                'email' => [
                    'required' => 'A valid email is required',
                    'valid_email' => 'A valid email is required'
                ],
                'password' => [
                    'required' => 'A password is required',
                    'validateUser' => 'Email or Password don\'t match.'
                ]
            ]
        );

        if (!$error) {
            return view('login', [
                'validation' => $this->validator
            ]);
        } else if ($user = $this->_userModel->validatePassword($mail, $password)) {
            setcookie("userID", $user->id, time() + (86400 * 30), "/");
            setcookie("token", $user->token, time() + (86400 * 30), "/");
            setcookie("userSecret", hash('sha256', $user->secret), time() + (86400 * 30), "/");
            return redirect()->to('people');
        } else {
            return redirect()->to('/');
        }
    }

    /**
     * @return string - Registration View
     */
    public function showRegistration(): string
    {
        return view('register');
    }

    /**
     * @return RedirectResponse|string
     *
     * Validate Registration
     */
    public function register()
    {
        helper(['form', 'url']);

        $mail = $this->request->getVar('email');
        $password = $this->request->getVar('password');
        $token = $this->request->getVar('token');


        $error = $this->validate([
            'email' => 'required|max_length[50]|valid_email|is_unique[user.email]',
            'token' => 'required|min_length[4]|max_length[4]|is_unique[user.token]',
            'password' => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'matches[password]'
        ],
            [
                'email' => [
                    'required' => 'A valid email is required',
                    'max_length' => 'Email can\'t be longter than 50',
                    'valid_email' => 'A valid email is required',
                    'is_unique' => 'Email does already exist'
                ],
                'token' => [
                    'required' => 'A token is required',
                    'min_length' => 'Token must be of length 4',
                    'max_length' => 'Token must be of length 4',
                    'is_unique' => 'Token does already exist'
                ],
                'password' => [
                    'required' => 'A password is required',
                    'min_length' => 'Password must have more than 8 signs',
                    'max_length' => 'Password must have less than 255 signs'
                ],
                'password_confirm' => [
                    'matches' => 'Passwords don\'t match'
                ]
            ]);

        if (!$error) {
            return view('register', [
                'validation' => $this->validator
            ]);
        } else {
            $this->_userModel->createUser($mail, $password, $token);
            return redirect()->to('/');
        }
    }

    /**
     * @return RedirectResponse
     *
     * Redirect to Login.
     */
    public function logout(): RedirectResponse
    {
        return redirect()->to('/');
    }

}