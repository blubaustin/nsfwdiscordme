<?php
namespace App\Storage\Elastica;

use App\Entity\Server;

/**
 * Class ServerIndexableChecker
 */
class ServerIndexableChecker
{
    /**
     * Called by FOSElasticaBundle to determine whether a server should be indexed
     *
     * @see config/package/fos_elastica.yaml
     *
     * @param Server $server
     *
     * @return bool
     */
    public function isIndexable(Server $server)
    {
        return $server->isEnabled() && $server->isPublic();
    }
}
