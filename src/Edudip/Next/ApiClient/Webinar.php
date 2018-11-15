<?php

namespace Edudip\Next\ApiClient;

use Edudip\Next\Error\InvalidArgumentException;

/**
 * @author Marc Steinert <m.steinert@edudip.com>
 * @copyright edudip GmbH
 * @package edudip next Api Client
 */

class Webinar extends AbstractRequest
{
    // @var array
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->data['title'];
    }

    /**
     * @return array A list of WebinarDates objects, representing
     *  the dates, the webinar takes place.
     */
    public function getDates()
    {
        $webinarDates = [ ];

        foreach ($this->data['dates'] as $date) {
            $webinarDates []= WebinarDate::deserialize($date);
        }

        return $webinarDates;
    }

    public function getId()
    {
        return $this->data['id'];
    }

    public function getParticipants() : array
    {
        if (! array_key_exists('participants', $this->data)) {
            return [ ];
        }

        return $this->data['participants'];
    }

    public function getModerators() : array
    {
        if (! array_key_exists('moderators', $this->data)) {
            return [ ];
        }

        return $this->data['moderators'];
    }

    public function getUser() : array
    {
        if (! array_key_exists('user', $this->data)) {
            return [ ];
        }

        return $this->data['user'];
    }

    /**
     * @throws \Edudip\Next\Error\InvalidArgumentException;
     * @param \Edudip\Next\ApiClient\Participant $participant
     * @param string $date If the webinar registration type is "date", provide a webinar
     *  date in the format "Y-m-d H:i:s" to which the participant should be registered to
     * @return array A list of dates, the participant may now attend with a personalized
     *  link, that can be used on that date to enter the webinar room
     */
    public function registerParticipant(Participant $participant, $date = null)
    {
        $params = $participant->toArray();

        if ($this->data['registration_type'] === 'date') {
            if ($date === null || ! WebinarDate::validateDateString($date)) {
                throw new InvalidArgumentException(
                    'Registration type for the webinar is "date". Please provide a valid webinar date to register a participant'
                );
            }
            
            $params['webinar_date'] = $date;
        }

        $resp = self::postRequest('/webinars/' . $this->getId() . '/register-participant', $params);
        
        return $resp['registeredDates'];
    }

    /**
     * Returns a list of all webinars
     * @return array
     */
    public static function all()
    {
        $resp = self::getRequest('/webinars');

        $webinars = [ ];
        foreach ($resp['webinars'] as $webinarData) {
            $webinars []= new self($webinarData);
        }

        return $webinars;
    }

    /**
     * Retrieves a single webinar by id
     * @return array
     */
    public static function getById(int $webinarId)
    {
        $resp = self::getRequest('/webinars/' . $webinarId);
        $webinar = new self($resp['webinar']);

        return $webinar;
    }

    /**
     * Creates a new webinar
     * @throws \Edudip\Next\ApiClient\Error\InvalidArgumentException
     */
    public static function create(
        string $title,
        array $webinarDates,
        int $maxParticipants,
        bool $recording,
        string $registrationType = 'series',
        string $access = 'all'
    ) : Webinar {
        if (count($webinarDates) === 0) {
            throw new InvalidArgumentException('Please provide at least one webinar date');
        }

        foreach ($webinarDates as $webinarDate) {
            if (! ($webinarDate instanceof WebinarDate)) {
                throw new InvalidArgumentException('Expected type WebinarDate');
            }
        }

        $params = [
            'title' => $title,
            'max_participants' => $maxParticipants,
            'recording' => $recording ? true : false,
            'registration_type' => $registrationType,
            'access' => $access,
            'dates' => json_encode($webinarDates),
        ];

        $resp = self::postRequest('/webinars', $params);

        $webinar = new self($resp['webinar']);
        
        return $webinar;
    }
}
