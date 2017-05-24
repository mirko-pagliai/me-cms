<?php
namespace App\Controller;

use Cake\Event\Event;

/**
 * Error Handling Controller
 * Controller used by ExceptionRenderer to render error responses.
 */
class ErrorController extends AppController
{
    /**
     * Initialization hook method
     * @return void
     */
    public function initialize()
    {
        $this->loadComponent('RequestHandler');
    }

    /**
     * beforeRender callback
     * @param \Cake\Event\Event $event Event
     * @return \Cake\Network\Response|null|void
     */
    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);

        $this->viewBuilder()->setLayout('error');
        $this->viewBuilder()->setTemplatePath('Error');
    }
}
