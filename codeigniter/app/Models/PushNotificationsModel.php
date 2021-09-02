<?php namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class PushNotificationsModel extends Model{
    private $_subscribers;


    public function __construct(){
        $this->db = Database::connect();
        $this->_subscribers = $this->db->table('subscribers');
    }


    public function getSubscribersByEndpoint($endpoint): array
    {
        return $this->_subscribers
            ->where("endpoint", $endpoint)
            ->get()
            ->getResult();
    }

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


    public function deleteSubscriber($id): bool
    {
        $this->_subscribers->where("id", $id);
        $this->_subscribers->delete();
        return true;
    }

    public function getAllSubscribers(): array
    {
        return $this->_subscribers->get()->getResult();
    }



}