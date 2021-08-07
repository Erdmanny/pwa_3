<?php namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Model;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class User extends BaseController
{
    private $_userModel, $_session;

    public function __construct()
    {
        $this->_userModel = new UserModel();
        $this->_session = \Config\Services::session();
    }


    public function index()
    {
        $this->_session->destroy();
        if (isset($_COOKIE["error-edit-prename"])){
            unset($_COOKIE["error-edit-prename"]);
            setcookie("error-edit-prename", "", -1, "/");
        }
        if (isset($_COOKIE["error-edit-surname"])){
            unset($_COOKIE["error-edit-surname"]);
            setcookie("error-edit-surname", "", -1, "/");
        }
        if (isset($_COOKIE["error-edit-street"])){
            unset($_COOKIE["error-edit-street"]);
            setcookie("error-edit-street", "", -1, "/");
        }
        if (isset($_COOKIE["error-edit-postcode"])){
            unset($_COOKIE["error-edit-postcode"]);
            setcookie("error-edit-postcode", "", -1, "/");
        }
        if (isset($_COOKIE["error-edit-city"])){
            unset($_COOKIE["error-edit-city"]);
            setcookie("error-edit-city", "", -1, "/");
        }
        if (isset($_COOKIE["error-new-prename"])){
            unset($_COOKIE["error-new-prename"]);
            setcookie("error-new-prename", "", -1, "/");
        }
        if (isset($_COOKIE["error-new-surname"])){
            unset($_COOKIE["error-new-surname"]);
            setcookie("error-new-surname", "", -1, "/");
        }
        if (isset($_COOKIE["error-new-street"])){
            unset($_COOKIE["error-new-street"]);
            setcookie("error-new-street", "", -1, "/");
        }
        if (isset($_COOKIE["error-new-postcode"])){
            unset($_COOKIE["error-new-postcode"]);
            setcookie("error-new-postcode", "", -1, "/");
        }
        if (isset($_COOKIE["error-new-city"])){
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
        echo view('login');
    }

    public function login()
    {
        $mail = $this->request->getVar('email');
        $password = $this->request->getVar('password');
        if ($user = $this->_userModel->validatePassword($mail, $password)){



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
            setcookie("userID", $user->id, time() + (86400 * 30), "/");
            setcookie("token", $user->token, time() + (86400 * 30), "/");
            setcookie("userSecret", hash('sha256', $user->secret), time() + (86400 * 30), "/");



            return redirect()->to('people');
        } else {
            return redirect()->to('/');
        }
    }

    public function showRegistration()
    {
        echo view('register');
    }


    public function register()
    {
        $mail = $this->request->getVar('email');
        $password = $this->request->getVar('password');
        $token = $this->request->getVar('token');

        $error = $this->validate([
            'email' => 'required|max_length[50]|valid_email|is_unique[user.email]',
            'token' => 'required|min_length[4]|max_length[4]|is_unique[user.token]',
            'password' => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'matches[password]'
        ]);

        if (!$error){
            return redirect()->to('/registration');
        } else {
            $this->_userModel->createUser($mail, $password, $token);

            return redirect()->to('/');
        }
    }


    public function logout()
    {
        return redirect()->to('/');
    }

}