<?php

namespace App\Controllers;

use App\Models\PeopleModel;
use App\Models\PushNotificationsModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\Response;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use CodeIgniter\API\ResponseTrait;


class People extends BaseController
{
    use ResponseTrait;

    private $_userModel, $_peopleModel, $_session, $_pushNotificationsModel, $_validation;


    public function __construct()
    {
        $this->_peopleModel = new PeopleModel();
        $this->_userModel = new UserModel();
        $this->_pushNotificationsModel = new PushNotificationsModel();
        $this->_session = \Config\Services::session();
        $this->_validation = \Config\Services::validation();
    }

    /**
     * Checks if user ID and given secret matches the one in the DB
     *
     * @param int $userID userID (from GET-Request)
     * @param string $secretFromCookie secret (from GET-Request)
     * @return bool true if both match
     */
    function isValidRequest(int $userID, string $secretFromCookie): bool
    {
        if ($this->_userModel == null) {
            return false;
        }
        $user = $this->_userModel->getSingleUser($userID);
        if ($user === null) {
            return false;
        }
        return hash_equals($secretFromCookie, hash('sha256', $user["secret"]));
    }


    /**
     * @return Response
     */
    public function checkCookie(): Response
    {
        if (!isset($_COOKIE["userID"]) || !isset($_COOKIE["userSecret"])) {
            return $this->respond(true)->setContentType("application/json");
        } else {
            return $this->respond(false)->setContentType("application/json");
        }
    }


    public function index()
    {
//        if (!isset($_COOKIE["userID"])) {
//            return $this->response->redirect(site_url("/"));
//        }
        echo view('header');
        echo view('people');
        echo view('footer');
    }

    public function getPeople()
    {
        if (!isset($_COOKIE["userID"]) || !isset($_COOKIE["userSecret"]) || !$this->isValidRequest($_COOKIE["userID"], $_COOKIE["userSecret"])) {
            return $this->failUnauthorized();
        }
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
        if (isset($_COOKIE["success"])){
            unset($_COOKIE["success"]);
            setcookie("success", "", -1, "/");
        }






        $people = $this->_peopleModel->getPeople();

        for ($i = 0; $i < sizeof($people); $i++) {
            $id = $people[$i]["id"];

            $people[$i]["address"] = $people[$i]["zip"] . " " . $people[$i]["city"];
            $people[$i]["fullname"] = $people[$i]["prename"] . " " . $people[$i]["name"];


            $people[$i]["buttons"] =
                "<a type='button' href='http://localhost/people/getSinglePerson?id={$id}' class='btn btn-warning btn-sm mr-2'>
                    <i class='bi bi-pencil-fill'></i>
                </a>
                <button type='button' onclick='deletePerson($id)' id='delete-button' class='btn btn-danger btn-sm'>
                    <i class='bi bi-trash-fill'></i>
                </button>";
        }

        $this->_session->destroy();
        return $this->respond($people)
            ->setContentType('application/json');
    }


    public function addPerson()
    {
//        if (!isset($_COOKIE["userID"])) {
//            return $this->response->redirect(site_url("/"));
//        }
        echo view('header');
        echo view("addPerson");
        echo view('footer');
    }

    public function addPerson_Validation()
    {
        if (!isset($_COOKIE["userID"]) || !isset($_COOKIE["userSecret"]) || !$this->isValidRequest($_COOKIE["userID"], $_COOKIE["userSecret"])) {
            return $this->failUnauthorized();
        }
        helper(['form', 'url']);

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

        $rules = [
            'new-prename' => 'required',
            'new-surname' => 'required',
            'new-street' => 'required',
            'new-postcode' => 'required|min_length[5]|max_length[5]|numeric',
            'new-city' => 'required'
        ];
        $error_message = [
            'new-prename' => [
                'required' => 'A prename is required'
            ],
            'new-surname' => [
                'required' => 'A surname is required'
            ],
            'new-street' => [
                'required' => 'A street is required'
            ],
            'new-postcode' => [
                'required' => 'A postcode is required',
                'min_length' => 'Postcode must be of length 5',
                'max_length' => 'Postcode must be of length 5',
                'numeric' => 'Postcode can only consist of numbers'
            ],
            'new-city' => [
                'required' => 'A city is required'
            ],
        ];


        $this->_validation->setRules($rules, $error_message);

        $error = $this->validate($rules, $error_message);

        $errors = $this->_validation->getErrors();

        if (!$error) {
            if (isset($errors["new-prename"])) {
                setcookie("error-new-prename", $errors["new-prename"], time() + (86400 * 30), "/");
            }
            if (isset($errors["new-surname"])) {
                setcookie("error-new-surname", $errors["new-surname"], time() + (86400 * 30), "/");
            }
            if (isset($errors["new-street"])) {
                setcookie("error-new-street", $errors["new-street"], time() + (86400 * 30), "/");
            }
            if (isset($errors["new-postcode"])) {
                setcookie("error-new-postcode", $errors["new-postcode"], time() + (86400 * 30), "/");
            }
            if (isset($errors["new-city"])) {
                setcookie("error-new-city", $errors["new-city"], time() + (86400 * 30), "/");
            }
            return $this->response->redirect(site_url("addPerson"));
        } else {
            $id = $this->_peopleModel->addPerson(
                $this->request->getVar('new-prename'),
                $this->request->getVar('new-surname'),
                $this->request->getVar('new-street'),
                $this->request->getVar('new-postcode'),
                $this->request->getVar('new-city'),
                $_COOKIE["token"]
            );


            if (!empty($id)) {
                setcookie("success", "Person added.", time() + (86400 * 30), "/");


                $subscribers = $this->_pushNotificationsModel->getAllSubscribers();
                foreach ($subscribers as $row) {

                    $keys_auth = array(
                        "contentEncoding" => "aesgcm",
                        "endpoint" => $row->endpoint,
                        "keys" => array(
                            "auth" => $row->auth,
                            "p256dh" => $row->p256dh
                        )
                    );

                    $message = "added";

                    $this->sendMessage($keys_auth, $row->endpoint, $message, $this->request->getVar('new-prename'), $this->request->getVar('new-surname'));
                }


            } else {
                setcookie("error", "Person not added.", time() + (86400 * 30), "/");
            }


            return $this->response->redirect(site_url("people"));
        }
    }

    function getSinglePerson()
    {
//        if (!isset($_COOKIE["userID"])) {
//            return $this->response->redirect(site_url("/"));
//        }
        echo view('header');
        echo view("editPerson");
        echo view('footer');
    }

    function editPerson_Validation()
    {
        if (!isset($_COOKIE["userID"]) || !isset($_COOKIE["userSecret"]) || !$this->isValidRequest($_COOKIE["userID"], $_COOKIE["userSecret"])) {
            return $this->failUnauthorized();
        }
        helper(['form', 'url']);


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


        $rules = [
            'edit-prename' => 'required',
            'edit-surname' => 'required',
            'edit-street' => 'required',
            'edit-postcode' => 'required|min_length[5]|max_length[5]|numeric',
            'edit-city' => 'required'
        ];
        $error_message = [
            'edit-prename' => [
                'required' => 'A prename is required'
            ],
            'edit-surname' => [
                'required' => 'A surname is required'
            ],
            'edit-street' => [
                'required' => 'A street is required'
            ],
            'edit-postcode' => [
                'required' => 'A postcode is required',
                'min_length' => 'Postcode must be of length 5',
                'max_length' => 'Postcode must be of length 5',
                'numeric' => 'Postcode can only consist of numbers'
            ],
            'edit-city' => [
                'required' => 'A city is required'
            ],
        ];

        $this->_validation->setRules($rules, $error_message);

        $error = $this->validate($rules, $error_message);

        $errors = $this->_validation->getErrors();


        if (!$error) {
            $id = $this->request->getVar('id');

            if (isset($errors["edit-prename"])) {
                setcookie("error-edit-prename", $errors["edit-prename"], time() + (86400 * 30), "/");
            }
            if (isset($errors["edit-surname"])) {
                setcookie("error-edit-surname", $errors["edit-surname"], time() + (86400 * 30), "/");
            }
            if (isset($errors["edit-street"])) {
                setcookie("error-edit-street", $errors["edit-street"], time() + (86400 * 30), "/");
            }
            if (isset($errors["edit-postcode"])) {
                setcookie("error-edit-postcode", $errors["edit-postcode"], time() + (86400 * 30), "/");
            }
            if (isset($errors["edit-city"])) {
                setcookie("error-edit-city", $errors["edit-city"], time() + (86400 * 30), "/");
            }
            return $this->response->redirect(site_url("editPerson?id={$id}"));
        } else {
            $this->_peopleModel->updatePerson(
                $this->request->getVar('id'),
                $this->request->getVar('edit-prename'),
                $this->request->getVar('edit-surname'),
                $this->request->getVar('edit-street'),
                $this->request->getVar('edit-postcode'),
                $this->request->getVar('edit-city'),
                $_COOKIE["token"]
            );


            if (!empty($this->request->getVar('id'))) {
                setcookie("success", "Person updated.", time() + (86400 * 30), "/");

                $subscribers = $this->_pushNotificationsModel->getAllSubscribers();
                foreach ($subscribers as $row) {

                    $keys_auth = array(
                        "contentEncoding" => "aesgcm",
                        "endpoint" => $row->endpoint,
                        "keys" => array(
                            "auth" => $row->auth,
                            "p256dh" => $row->p256dh
                        )
                    );

                    $message = "updated";

                    $this->sendMessage($keys_auth, $row->endpoint, $message, $this->request->getVar('edit-prename'), $this->request->getVar('edit-surname'));
                }


            } else {
                setcookie("error", "Person not updated.", time() + (86400 * 30), "/");
            }


            return $this->response->redirect(site_url("people"));
        }

    }

    function deletePerson($id)
    {
        if (!isset($_COOKIE["userID"]) || !isset($_COOKIE["userSecret"]) || !$this->isValidRequest($_COOKIE["userID"], $_COOKIE["userSecret"])) {
            return $this->failUnauthorized();
        }
        $person = $this->_peopleModel->getSinglePerson($id);


        if (!empty($id)) {
            setcookie("success", "Person deleted.", time() + (86400 * 30), "/");

            $subscribers = $this->_pushNotificationsModel->getAllSubscribers();
            foreach ($subscribers as $row) {

                $keys_auth = array(
                    "contentEncoding" => "aesgcm",
                    "endpoint" => $row->endpoint,
                    "keys" => array(
                        "auth" => $row->auth,
                        "p256dh" => $row->p256dh
                    )
                );

                $message = "deleted";

                $this->sendMessage($keys_auth, $row->endpoint, $message, $person->prename, $person->name);
            }


        } else {
            setcookie("success", "Person not deleted.", time() + (86400 * 30), "/");
        }
        $this->_peopleModel->deletePerson($id);
        return $this->response->redirect(site_url("people"));
    }


    /* ------------------------------------------ Web Push Notifications ---------------------------------------------------- */


    protected function sendMessage($keys_auth, $endpoint, $message, $prename, $surname)
    {
        $subscription = Subscription::create($keys_auth);

        $auth = array(
            'VAPID' => array(
                'subject' => 'PHP Codeigniter Web Push Notification',
                'publicKey' => env('public_key'),
                'privateKey' => env('private_key')
            )
        );

        $webPush = new WebPush($auth);

        $options = [
            'title' => 'A person has been ' . $message,
            'body' => $prename . ' ' . $surname . ' has been ' . $message,
            'icon' => base_url() . '/icon/icon128.png',
            'badge' => base_url() . '/icon/icon128.png',
            'url' => 'http://localhost'
        ];
        $report = $webPush->sendOneNotification(
            $subscription,
            json_encode($options)
        );

        if ($report->isSuccess()) {
            echo "[v] Message sent successfully for subscription {$endpoint}";
        } else {
            echo "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
        }
    }

    public function push_subscription()
    {
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if ($contentType === "application/json") {
            $content = trim(file_get_contents("php://input"));
            $decoded = json_decode($content, true);

            if (!isset($decoded['endpoint'])) {
                echo 'Error: not a subscription';
                return;
            }

            $method = $_SERVER['REQUEST_METHOD'];


            switch ($method) {
                case 'POST':
                    $subscribers = $this->_pushNotificationsModel->getSubscribersByEndpoint($decoded['endpoint']);
                    try {
                        if (empty($subscribers)) {
                            if ($this->_pushNotificationsModel->insertSubscriber($decoded['endpoint'], $decoded['authToken'], $decoded['publicKey'])) {
                                echo 'Subscription successful.';
                            } else {
                                echo 'Sorry there is some problem';
                            }
                        }
                    } catch (Exception $error) {
                        echo 'Sorry there has been an error processing your request!';
                    }
                    break;
                case 'PUT':
                    $subscribers = $this->_pushNotificationsModel->getSubscribersByEndpoint($decoded['endpoint']);
                    print_r($subscribers);
                    try {
                        if ($subscribers[0]->id !== NULL) {
                            if ($this->_pushNotificationsModel->updateSubscriber($subscribers[0]->id, $decoded['endpoint'], $decoded['authToken'], $decoded['publicKey'])) {
                                echo 'Subscription updated successful.';
                            } else {
                                echo 'Sorry there is some problem';
                            }
                        }
                    } catch (Exception $error) {
                        echo 'Sorry there has been an error processing your request!';
                    }
                    break;
                case 'DELETE':
                    $subscribers = $this->_pushNotificationsModel->getSubscribersByEndpoint($decoded['endpoint']);
                    print_r($subscribers);
                    try {
                        if (!empty($subscribers[0]->id)) {
                            if ($this->_pushNotificationsModel->deleteSubscriber($subscribers[0]->id)) {
                                echo 'Unsubscribtion successful.';
                            } else {
                                echo 'Sorry there is some problem';
                            }
                        }
                    } catch (Exception $error) {
                        echo 'Sorry there has been an error processing your request!';
                    }
                    break;
                default:
                    echo 'Error: method not handled';
                    return;
            }
        }
    }


    public function send_push_notification()
    {
        $subscribers = $this->_pushNotificationsModel->getAllSubscribers();

        foreach ($subscribers as $row) {

            $data = array(
                "contentEncoding" => "aesgcm",
                "endpoint" => $row->endpoint,
                "keys" => array(
                    "auth" => $row->auth,
                    "p256dh" => $row->p256dh
                )
            );

            $subscription = Subscription::create($data);

            $auth = array(
                'VAPID' => array(
                    'subject' => 'PHP Codeigniter Web Push Notification',
                    'publicKey' => env('public_key'),
                    'privateKey' => env('private_key')
                )
            );

            $webPush = new WebPush($auth);

            $options = [
                'title' => 'Test',
                'body' => 'This is a body',
                'icon' => base_url() . '/icon/icon128.png',
                'badge' => base_url() . '/icon/icon128.png',
                'url' => 'http://localhost'
            ];
            $report = $webPush->sendOneNotification(
                $subscription,
                json_encode($options)
            );

            $endpoint = $report->getRequest()->getUri()->__toString();

            if ($report->isSuccess()) {
                echo "[v] Message sent successfully for subscription {$endpoint}";
            } else {
                echo "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
            }
        }
    }
}
