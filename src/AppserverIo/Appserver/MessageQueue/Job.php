<?php

/**
 * \AppserverIo\Appserver\MessageQueue\Job
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/appserver
 * @link      http://www.appserver.io
 */

namespace AppserverIo\Appserver\MessageQueue;

use AppserverIo\Psr\Pms\JobInterface;
use AppserverIo\Psr\Pms\MessageInterface;
use AppserverIo\Psr\Pms\QueueContextInterface;
use AppserverIo\Psr\Naming\InitialContext;
use AppserverIo\Psr\Application\ApplicationInterface;

/**
 * A simple job implementation.
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/appserver
 * @link      http://www.appserver.io
 */
class Job extends \Thread implements JobInterface
{

    /**
     * Initializes the job with the application and the storage it should work on.
     *
     * @param \AppserverIo\Psr\Pms\MessageInterface             $message     The message we have to handle
     * @param \AppserverIo\Psr\Application\ApplicationInterface $application The application instance
     */
    public function __construct(MessageInterface $message, ApplicationInterface $application)
    {

        // we want to start working
        $this->finished = false;

        // initialize message and application instance
        $this->message = $message;
        $this->application = $application;

        // start the job
        $this->start(PTHREADS_INHERIT_NONE|PTHREADS_INHERIT_CONSTANTS);
    }

    /**
     * Returns the application instance.
     *
     * @return \AppserverIo\Psr\Application\ApplicationInterface The application instance
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Returns the message instance the job is bound to.
     *
     * @return \AppserverIo\Psr\Pms\MessageInterface The message instance
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Queries whether the timer task has been finished or not.
     *
     * @return boolean TRUE if the timer task has been finished, else FALSE
     */
    public function isFinished()
    {
        return $this->finished;
    }

    /**
     * We process the timer here.
     *
     * @return void
     */
    public function run()
    {
        try {
            // register the default autoloader
            require SERVER_AUTOLOADER;

            // register shutdown handler
            register_shutdown_function(array(&$this, "shutdown"));

            // we need to register the class loaders again
            $application = $this->application;
            $application->registerClassLoaders();

            // load application and message instance
            $message = $this->message;

            // load class name and session ID from remote method
            $queueProxy = $message->getDestination();
            $sessionId = $message->getSessionId();

            // lookup the queue and process the message
            if ($queue = $application->search(QueueContextInterface::IDENTIFIER)->locate($queueProxy)) {
                // the queues receiver type
                $queueType = $queue->getType();

                // create an intial context instance
                $initialContext = new InitialContext();
                $initialContext->injectApplication($application);

                // lookup the bean instance
                $instance = $initialContext->lookup($queueType);

                // inject the application to the receiver and process the message
                $instance->onMessage($message, $sessionId);
            }

            // mark the job finished
            $this->finished = true;

        } catch (\Exception $e) {
            $application->getInitialContext()->getSystemLogger()->error($e->__toString());
        }
    }

    /**
     * Does shutdown logic for request handler if something went wrong and
     * produces a fatal error for example.
     *
     * @return void
     */
    public function shutdown()
    {

        // check if there was a fatal error caused shutdown
        if ($lastError = error_get_last()) {
            // initialize type + message
            $type = 0;
            $message = '';
            // extract the last error values
            extract($lastError);
            // query whether we've a fatal/user error
            if ($type === E_ERROR || $type === E_USER_ERROR) {
                $this->getApplication()->getInitialContext()->getSystemLogger()->error($message);
            }
        }
    }
}
