<?php
declare(strict_types=1);
namespace App\Controller;

use Cake\Controller\Controller;
use MeCms\Model\Entity\User;

class ExampleController extends Controller
{
    /**
     * Checks if the provided user is authorized for the request
     * @param \MeCms\Model\Entity\User $User User entity
     * @return bool `true` if the user is authorized, otherwise `false`
     */
    public function isAuthorized(User $User): bool
    {
        if ($User->get('id') === 2) {
            return false;
        }

        if ($User->get('group')->get('name') === 'moderator') {
            return false;
        }

        return true;
    }
}
