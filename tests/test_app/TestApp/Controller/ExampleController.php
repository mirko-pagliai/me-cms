<?php
declare(strict_types=1);
namespace App\Controller;

use Cake\Controller\Controller;

class ExampleController extends Controller
{
    /**
     * Checks if the user is authorized for the request
     * @return bool `true` if the user is authorized, otherwise `false`
     */
    public function isAuthorized(): bool
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
