<?php namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class PushNotificationsModel extends Model{
    private $_subscribers;

    /**
     * PushNotificationModel constructor.
     * Connect to the database.
     */
    public function __construct(){
        $this->db = Database::connect();
        $this->_subscribers = $this->db->table('subscribers');
    }

    /**
     * @param $endpoint
     * @return array - Subscriber with endpoint
     */
    public function getSubscribersByEndpoint($endpoint): array
    {
        return $this->_subscribers
            ->where("endpoint", $endpoint)
            ->get()
            ->getResult();
    }

    /**
     * @param $endpoint
     * @param $auth
     * @param $p256dh
     * @return bool
     *
     * Insert new subscriber into the database
     */
    public function insertSubscriber($endpoint, $auth, $p256dh): bool
    {
        $data = [
            'endpoint' => $endpoint,
            'auth' => $auth,
            'p256dh' => $p256dh
        ];
        $this->_subscribers->insert($data);
        return true;
    }

    /**
     * @param $id
     * @return bool
     *
     * Delete person with $id
     */
    public function deleteSubscriber($id): bool
    {
        $this->_subscribers->where("id", $id);
        $this->_subscribers->delete();
        return true;
    }

    /**
     * @return array - all subscribers from the database
     */
    public function getAllSubscribers(): array
    {
        return $this->_subscribers->get()->getResult();
    }



}