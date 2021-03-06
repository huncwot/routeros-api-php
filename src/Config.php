<?php

namespace RouterOS;

use RouterOS\Exceptions\ConfigException;
use RouterOS\Interfaces\ConfigInterface;

/**
 * Class Config with array of parameters
 * @package RouterOS
 * @since 0.1
 */
class Config implements ConfigInterface
{
    /**
     * Array of parameters (with some default values)
     * @var array
     */
    private $_parameters = [
        'legacy' => Client::LEGACY,
        'ssl' => Client::SSL,
        'timeout' => Client::TIMEOUT,
        'attempts' => Client::ATTEMPTS,
        'delay' => Client::ATTEMPTS_DELAY
    ];

    /**
     * Check if key in list of parameters
     *
     * @param   string $key
     * @param   array $array
     * @throws  ConfigException
     */
    private function exceptionIfKeyNotExist(string $key, array $array)
    {
        if (!array_key_exists($key, $array)) {
            throw new ConfigException("Requested parameter '$key' not found in list [" . implode(',',
                    array_keys($array)) . ']');
        }
    }

    /**
     * Compare data types of some value
     *
     * @param   string $name Name of value
     * @param   mixed $whatType What type has value
     * @param   mixed $isType What type should be
     * @throws  ConfigException
     */
    private function exceptionIfTypeMismatch(string $name, $whatType, $isType)
    {
        if ($whatType !== $isType) {
            throw new ConfigException("Parameter '$name' has wrong type '$whatType' but should be '$isType'");
        }
    }

    /**
     * Set parameter into array
     *
     * @param   string $name
     * @param   mixed $value
     * @return  ConfigInterface
     * @throws  ConfigException
     */
    public function set(string $name, $value): ConfigInterface
    {
        // Check of key in array
        $this->exceptionIfKeyNotExist($name, self::ALLOWED);

        // Check what type has this value
        $this->exceptionIfTypeMismatch($name, \gettype($value), self::ALLOWED[$name]);

        // Save value to array
        $this->_parameters[$name] = $value;

        return $this;
    }

    /**
     * Return port number (get from defaults if port is not set by user)
     *
     * @param   string $parameter
     * @return  bool|int
     */
    private function getPort(string $parameter)
    {
        // If client need port number and port is not set
        if ($parameter === 'port' && !isset($this->_parameters['port'])) {
            // then use default with or without ssl encryption
            return (isset($this->_parameters['ssl']) && $this->_parameters['ssl'])
                ? Client::PORT_SSL
                : Client::PORT;
        }
        return null;
    }

    /**
     * Remove parameter from array by name
     *
     * @param   string $parameter
     * @return  ConfigInterface
     * @throws  ConfigException
     */
    public function delete(string $parameter): ConfigInterface
    {
        // Check of key in array
        $this->exceptionIfKeyNotExist($parameter, self::ALLOWED);

        // Save value to array
        unset($this->_parameters[$parameter]);

        return $this;
    }

    /**
     * Return parameter of current config by name
     *
     * @param   string $parameter
     * @return  mixed
     * @throws  ConfigException
     */
    public function get(string $parameter)
    {
        // Check of key in array
        $this->exceptionIfKeyNotExist($parameter, self::ALLOWED);

        return $this->getPort($parameter) ?? $this->_parameters[$parameter];
    }

    /**
     * Return array with all parameters of configuration
     *
     * @return  array
     */
    public function getParameters(): array
    {
        return $this->_parameters;
    }
}
