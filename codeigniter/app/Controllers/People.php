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
        echo view('header');
        echo view('people');
        echo view('footer');
    }

    public function getPeople()
    {
        if (!isset($_COOKIE["userID"]) || !isset($_COOKIE["userSecret"]) || !$this->isValidRequest($_COOKIE["userID"], $_COOKIE["userSecret"])) {
            return $this->failUnauthorized();
        }
        if (isset($_COOKIE["success"])) {
            unset($_COOKIE["success"]);
            setcookie("success", "", -1, "/");
        }


        $people = $this->_peopleModel->getPeople();

        for ($i = 0; $i < sizeof($people); $i++) {
            $id = $people[$i]["id"];

            $people[$i]["address"] = $people[$i]["zip"] . " " . $people[$i]["city"];
            $people[$i]["fullname"] = $people[$i]["prename"] . " " . $people[$i]["name"];


            $people[$i]["offline"] =
                "<div id='tableOffline' class='text-center'></div>";
            $people[$i]["buttons"] =
                "<a type='button' href='http://localhost/people/editPerson?id={$id}' class='btn btn-warning btn-sm mr-2'>
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
        echo view('header');
        echo view("addPerson");
        echo view('footer');
    }

    public function addPerson_Validation()
    {
        if (!isset($_COOKIE["userID"]) || !isset($_COOKIE["userSecret"]) || !$this->isValidRequest($_COOKIE["userID"], $_COOKIE["userSecret"])) {
            return $this->failUnauthorized();
        }
        $people = $this->request->getPost("people");


        for ($i = 0; $i < sizeof($people); $i++) {
            $id = $this->_peopleModel->addPerson(
                $people[$i]["new-prename"],
                $people[$i]["new-surname"],
                $people[$i]["new-street"],
                $people[$i]["new-postcode"],
                $people[$i]["new-city"],
                $_COOKIE["token"]
            );


            if (!empty($id)) {
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

                    $this->sendMessage($keys_auth, $row->endpoint, $message, $people[$i]["new-prename"], $people[$i]["new-surname"]);
                }

            }

        }
        return $this->respondCreated();
    }

    function editPerson()
    {
        echo view('header');
        echo view("editPerson");
        echo view('footer');
    }

    function editPerson_Validation()
    {
        if (!isset($_COOKIE["userID"]) || !isset($_COOKIE["userSecret"]) || !$this->isValidRequest($_COOKIE["userID"], $_COOKIE["userSecret"])) {
            return $this->failUnauthorized();
        }

        $people = $this->request->getPost("people");

        for ($i = 0; $i < sizeof($people); $i++) {
            $this->_peopleModel->updatePerson(
                $people[$i]["edit-id"],
                $people[$i]["edit-prename"],
                $people[$i]["edit-surname"],
                $people[$i]["edit-street"],
                $people[$i]["edit-postcode"],
                $people[$i]["edit-city"],
                $_COOKIE["token"]
            );

            $id = $people[$i]["edit-id"];


            if (!empty($id)) {

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

                    $this->sendMessage($keys_auth, $row->endpoint, $message, $people[$i]["edit-prename"], $people[$i]["edit-surname"]);
                }


            }


        }
        return $this->respondCreated();
    }

    function deletePerson()
    {
        if (!isset($_COOKIE["userID"]) || !isset($_COOKIE["userSecret"]) || !$this->isValidRequest($_COOKIE["userID"], $_COOKIE["userSecret"])) {
            return $this->failUnauthorized();
        }
        $people = $this->request->getPost("people");


        for ($i = 0; $i < sizeof($people); $i++) {


            $person = $this->_peopleModel->getSinglePerson($people[$i]["delete-id"]);



            if (!empty($people[$i]["delete-id"])) {

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

                    $this->_peopleModel->deletePerson($people[$i]["delete-id"]);

                    $this->sendMessage($keys_auth, $row->endpoint, $message, $person->prename, $person->name);
                }


            }

        }
        return $this->respondCreated();
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
