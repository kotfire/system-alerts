<?php

namespace Kotfire\SystemAlerts;

use Carbon\Carbon;
use JsonSerializable;
use \Exception;

class Alert implements JsonSerializable {

    // Alert Types
    const MAINTENANCE_TYPE = 'maintenance';
    const INFO_TYPE = 'info';
    
    /**
     * Message ID.
     *
     * @var string
     */
    protected $id;

    /**
     * The message.
     *
     * @var string
     */
    protected $message;

    /**
     * Alert Type
     *
     * @var string
     */
    protected $type;

    /**
     * Valid Alert Types
     *
     * @var array
     */
    protected $validTypes = [self::MAINTENANCE_TYPE, self::INFO_TYPE];

    /**
     * DateTime
     *
     * @var DateTime
     */
    protected $datetime;

    /**
     * Create a new Alert instance.
     *
     * @param  string  $msg
     * @param  string  $type
     * @return void
     */
    public function __construct($msg, $type, $minutes)
    {
        if (empty($msg) || empty($type)) {
            throw new Exception("Invalid Parameters");
        }

        if (!$this->isValidType($type)) {
            throw new Exception("Invalid alert type");
        }

        $this->id = uniqid();
        $this->message = $msg;
        $this->type = $type;

        if (!is_null($minutes)) {
            $minutes = intval($minutes);
        }

        if (is_integer($minutes) && $minutes >= 0) {
            $dt = Carbon::now();
            $dt->addMinutes($minutes);
            $this->datetime = $dt->toDateTimeString();
        } else {
            $this->datetime = null;
        }
    }

    /**
     * Check if type is valid
     *
     * @param  string $type
     * @return void
     */
    public function isValidType($type)
    {
        return in_array($type, $this->validTypes);
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            $this->id => [
                'id' => $this->id,
                'message' => $this->message,
                'type' => $this->type,
                'datetime' => $this->datetime,
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ];
    }

}