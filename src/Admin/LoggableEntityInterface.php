<?php
namespace App\Admin;

/**
 * Provides a method to describe the entity in log files
 *
 * Used by AdminLogsSubscriber to log admin actions.
 */
interface LoggableEntityInterface
{
    /**
     * @return string
     */
    public function getLoggableMessage();
}
