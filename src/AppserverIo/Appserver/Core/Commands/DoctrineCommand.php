<?php

/**
 * AppserverIo\Appserver\Core\Commands\DoctrineCommand
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

namespace AppserverIo\Appserver\Core\Commands;

use React\Socket\ConnectionInterface;
use AppserverIo\Appserver\Core\Interfaces\ApplicationServerInterface;

/**
 * The doctrine command implementation.
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/appserver-io/appserver
 * @link      http://www.appserver.io
 */
class DoctrineCommand implements CommandInterface
{

    /**
     * The unique command name.
     *
     * @var string
     */
    const COMMAND = 'doctrine';

    /**
     * Initializes the command with the connection and the application server
     * instance to execute the command on.
     *
     * @param \React\Socket\ConnectionInterface                                 $connection        The connection instance
     * @param \AppserverIo\Appserver\Core\Interfaces\ApplicationServerInterface $applicationServer The application server instance
     */
    public function __construct(ConnectionInterface $connection, ApplicationServerInterface $applicationServer)
    {
        $this->connection = $connection;
        $this->applicationServer = $applicationServer;
    }

    /**
     * Executes the command.
     *
     * @param array $params The arguments passed to the command
     *
     * @return mixed|null The result of the command
     * @see \AppserverIo\Appserver\Core\Commands\CommandInterface::execute()
     */
    public function execute(array $params = array())
    {
        $this->applicationServer->doctrine($this->connection, $params);
    }
}
