<?php
declare(strict_types=1);
namespace App\Controller;

use Cake\Controller\Controller;

class ExampleController extends Controller
{
    /**
     * Checks if the user is authorized for the request
     * @param array $user The user to check the authorization of. If empty
     *  the user in the session will be used
     * @return bool `true` if the user is authorized, otherwise `false`
     */
    public function isAuthorized($user = null)
    {
        if ($this->Auth->user('id') === 2) {
            return false;
        }

        if ($this->Auth->user('group.name') === 'moderator') {
            return false;
        }

        return true;
    }
}
