<?php
/**
 * Apix Pushover logger.
 * @license     http://opensource.org/licenses/BSD-3-Clause  New BSD License
 * @author Jonathan Spalink <jspalink@info.com>
 */

namespace Apix\Log\Logger;

use Pushy;
use Apix\Log\Exception;
use Apix\Log\LogEntry;
use Psr\Log\InvalidArgumentException;

/**
 * Apix logger for sending logs via Pushover.net.
 *
 * @author Jonathan Spalink <jspalink@info.com>
 */
class Pushover extends AbstractLogger implements LoggerInterface
{
    /**
     * A Pushy\Client instance to use for sending.
     * @type Pushy\Client
     */
    protected $pushy;
    
    /**
     * A Pushy\User instance to use for sending.
     * @type Pushy\User
     */
    protected $user;
    
    /**
     * Configuration array for Pushy\Message.
     * @var array
     */
    protected $options;
    
    protected $apix_levels_to_pushover = [
        0 => 'emergency',
        1 => 'emergency',
        2 => 'high',
        3 => 'high',
        4 => 'normal',
        5 => 'low',
        6 => 'low',
        7 => 'lowest'
    ];
    
    /**
     * Constructor.
     * @param Pushy\Client $pushy A preconfigured Pushy Client instance to use for sending.
     * @param Pushy\User $user A preconfigured Pushy User instance to use for sending.
     */
    public function __construct(Pushy\Client $pushy, Pushy\User $user)
    {
        try {
            $pushy->verifyUser($user);
        } catch (Pushy\Transport\Exception\ApiException $e) {
            throw new InvalidArgumentException(
                'User is not valid', 1
            );
        }
        
        $this->pushy = $pushy;
        $this->user = $user;
    }
    
    /**
     * Set Pushy\Message options.  Acceptable keys include:
     * 
     *  * retry (only applied to EmergencyPriority messages)<br/>
     *  * expire (only applied to EmergencyPriority messages)<br/>
     *  * title - defaults to application name<br/>
     *  * url - a supplementary URL to show with your message<br/>
     *  * url-title - a title for your supplementary URL<br/>
     *  * timestamp - a Unix timestamp of your message's date and time<br/>
     *  * sound - the name of one of the sounds supported by device clients<br/>
     * 
     * @link https://pushover.net/api#tldr Documentation on message paramters
     * 
     * @param array $options
     * @return \Apix\Log\Logger\Pushover
     */
    public function setOptions(array $options) {
        $this->options = $options;
        return $this;
    }
    
    /**
     * Change the set PSR-3 to Pushover log level translation table
     * 
     * @param array $apix_levels_to_pushover
     * @return \Apix\Log\Logger\Pushover
     */
    public function setLogLevels(array $apix_levels_to_pushover) {
        $this->apix_levels_to_pushover = $apix_levels_to_pushover;
        return $this;
    }
    
    /**
     * Turn a LogEntry into a Pushy\Message
     * 
     * @param LogEntry $log
     * @return Pushy\Message
     */
    private function buildMessage(LogEntry $log) {
        $push_level = $this->translateLogLevel($log->level_code);
        $priority = Pushy\Priority\PriorityFactory::createPriority($push_level);
        if($push_level == 2) {
            isset($this->options['retry']) && $priority->setRetry($this->options['retry']);
            isset($this->options['expire']) && $priority->setExpire($this->options['expire']);
        }
        
        $message = (new Pushy\Message(substr((string)$log, 0, 1024)))
                ->setUser($this->user)
                ->setPriority($priority);
        
        isset($this->options['title']) && $message->setTitle($this->options['title']);
        isset($this->options['url']) && $message->setUrl($this->options['url']);
        isset($this->options['url-title']) && $message->setUrlTitle($this->options['url-title']);
        isset($this->options['timestamp']) && $message->setTimestamp($this->options['timestamp']);
        isset($this->options['sound']) && $message->setSound(Pushy\Sound\SoundFactory::createSound($this->options['sound']));
        
        return $message;
    }
    
    /**
     * Translate PSR-3 log level codes into Pushover log levels
     * 
     * @param int $log_level
     * @return string
     */
    private function translateLogLevel($log_level) {
        return $this->apix_levels_to_pushover[$log_level];
    }

    /**
     * {@inheritDoc}
     */
    public function write(LogEntry $log)
    {
        $message = $this->buildMessage($log);
        try {
            return $this->pushy->sendMessage($message);
        } catch (Pushy\Transport\Exception\ApiException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
