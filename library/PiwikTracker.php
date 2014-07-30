<?php

use PiwikPhpTracker\Tracker;

class PiwikTracker extends Tracker
{

    /**
     * Some Tracking API functionnality requires express authentication, using either the
     * Super User token_auth, or a user with 'admin' access to the website.
     *
     * The following features require access:
     * - force the visitor IP
     * - force the date & time of the tracking requests rather than track for the current datetime
     * - force Piwik to track the requests to a specific VisitorId rather than use the standard visitor matching heuristic
     *
     * @param string $tokenAuth token_auth 32 chars token_auth string
     */
    public function setTokenAuth($tokenAuth)
    {
        $this->setParameter('token_auth', $tokenAuth);
    }

    /**
     * Sets local visitor time
     *
     * @param string $time HH:MM:SS format
     */
    public function setLocalTime($time)
    {
        list($hour, $minute, $second) = explode(':', $time);
        $localHour = (int) $hour;
        $localMinute = (int) $minute;
        $localSecond = (int) $second;

        if (($localHour !== false) && ($localMinute !== false) && ($localSecond !== false)) {
            $this->setParameter('h', $localHour);
            $this->setParameter('m', $localMinute);
            $this->setParameter('s', $localSecond);
        }
    }

    /**
     * Sets the latitude of the visitor. If not used, Piwik may try to find the visitor's
     * latitude using the visitor's IP address (if configured to do so).
     *
     * Allowed only for Admin/Super User, must be used along with setTokenAuth().
     * @param float $lat
     */
    public function setLatitude($lat)
    {
        $this->setParameter('lat', $lat);
    }

    /**
     * Sets the longitude of the visitor. If not used, Piwik may try to find the visitor's
     * longitude using the visitor's IP address (if configured to do so).
     *
     * Allowed only for Admin/Super User, must be used along with setTokenAuth().
     * @param float $long
     */
    public function setLongitude($long)
    {
        $this->setParameter('long', $long);
    }

    /**
     * Sets user resolution width and height.
     *
     * @param int $width
     * @param int $height
     */
    public function setResolution($width, $height)
    {
        $this->setParameter('res', $width . 'x' . $height);
    }

}
