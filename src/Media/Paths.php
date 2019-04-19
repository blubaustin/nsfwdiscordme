<?php
namespace App\Media;

use RuntimeException;

/**
 * Creates file paths.
 */
class Paths
{
    /**
     * @param string     $type      Either "banner" or "icon"
     * @param int|string $serverID  ID of the discord server
     * @param int|string $snowflake Snowflake for the file
     * @param string     $extension The file extension
     *
     * @return string
     */
    public function getPathByType($type, $serverID, $snowflake, $extension)
    {
        switch($type) {
            case 'banner':
                return $this->getBannerPath($serverID, $snowflake, $extension);
                break;
            case 'icon':
                return $this->getIconPath($serverID, $snowflake, $extension);
                break;
            default:
                throw new RuntimeException(
                    "Invalid file type ${type}."
                );
                break;
        }
    }

    /**
     * Generates a path to save an icon image
     *
     * @param int|string $serverID
     * @param int|string $snowflake
     * @param string     $extension
     *
     * @return string
     */
    public function getIconPath($serverID, $snowflake, $extension)
    {
        return sprintf(
            'icons/%s/%s.%s',
            $serverID,
            $snowflake,
            $extension
        );
    }

    /**
     * Generates a path to save an banner image
     *
     * @param int|string $serverID
     * @param int|string $snowflake
     * @param string     $extension
     *
     * @return string
     */
    public function getBannerPath($serverID, $snowflake, $extension)
    {
        return sprintf(
            'banners/%s/%s.%s',
            $serverID,
            $snowflake,
            $extension
        );
    }
}
