<?php

namespace App\Controllers;

use App\Models\PeopleModel;
use App\Models\PushNotificationsModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\Response;
use ErrorException;
use Exception;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use CodeIgniter\API\ResponseTrait;


class People extends BaseController
{
    use ResponseTrait;

    private $_userModel, $_peopleModel, $_pushNotificationsModel;

    /**
     * PeopleController constructor.
     * Init models and validation.
     */
    public function __construct()
    {
        $this->_peopleModel = new PeopleModel();
        $this->_userModel = new UserModel();
        $this->_pushNotificationsModel = new PushNotificationsModel();
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
     * @return string - People View with data
     */
    public function index(): string
    {
        return view('people');
    }

    /**
     * @return mixed
     *
     * Unset all cookies and get all people from the model.
     */
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
            $people[$i]["fullname"] = $people[$i]["prename"] . " " . $people[$i]["surname"];


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

        return $this->respond($people)
            ->setContentType('application/json');
    }

    /**
     * @return string - AddPerson View
     */
    public function addPerson(): string
    {
        return view("addPerson");
    }

    /**
     * @return Response|mixed
     * @throws ErrorException
     *
     * Validate AddPerson.
     */
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

                    $this->sendMessage($keys_auth, $row->endpoint, "added", $people[$i]["new-prename"], $people[$i]["new-surname"]);
                }

            }

        }
        return $this->respondCreated();
    }

    /**
     * @return string - Edit View with data
     */
    function editPerson(): string
    {
        return view("editPerson");
    }

    /**
     * @return Response|mixed
     *
     * Validate EditPerson.
     */
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

                    $this->sendMessage($keys_auth, $row->endpoint, "updated", $people[$i]["edit-prename"], $people[$i]["edit-surname"]);
                }
            }
        }
        return $this->respondCreated();
    }

    /**
     * @return Response|mixed
     *
     * Delete Person.
     */
    function deletePerson()
    {
        if (!isset($_COOKIE["userID"]) || !isset($_COOKIE["userSecret"]) || !$this->isValidRequest($_COOKIE["userID"], $_COOKIE["userSecret"])) {
            return $this->failUnauthorized();
        }
        $people = $this->request->getPost("people");


        for ($i = 0; $i < sizeof($people); $i++) {

            $person = $this->_peopleModel->getSinglePerson($people[$i]["delete-id"]);

            if (!empty($people[$i]["delete-id"])) {

                $this->_peopleModel->deletePerson($people[$i]["delete-id"]);

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

                    $this->sendMessage($keys_auth, $row->endpoint, "deleted", $person->prename, $person->surname);
                }
            }
        }
        return $this->respondCreated();
    }


    /* ------------------------------------------ Web Push Notifications ---------------------------------------------------- */

    /**
     * @param $keys_auth
     * @param $endpoint
     * @param $message
     * @param $prename
     * @param $surname
     * @throws ErrorException
     *
     * Send Push Notification.
     */
    protected function sendMessage($keys_auth, $endpoint, $message, $prename, $surname)
    {
        $subscription = Subscription::create($keys_auth);

        $auth = array(
            'VAPID' => array(
                'subject' => 'test@test.de',
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

    /**
     * Subscribe to or unsubscribe from Push.
     */
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
}
