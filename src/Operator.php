<?php namespace Bine\Operator;

require_once('../vendor/autoload.php');

use Bine\Operator\CallScreener;
use Bine\Operator\RequestValidator;
use Bine\Operator\RequestParser;
use Services_Twilio_Twiml;
use Services_Twilio_RequestValidator;
use Dotenv;
use Katzgrau\KLogger\Logger;
use Psr\Log\LogLevel;

class Operator
{
    private $isDebugging = false;

    private $forwardNumber;
    private $twilioAuthToken;
    private $callScreener;
    private $logger;

    protected $from;
    protected $to;
    protected $response;

    /**
     * Configure the Operator. Load dotenv config, initialize logger, etc.
     */
    public function __construct()
    {
        $this->logger = new Logger('/var/log', LogLevel::DEBUG, array(
            'filename' => 'twilio.log'
        ));
        $this->response = new Services_Twilio_Twiml;
        $this->requestParser = new RequestParser;

        Dotenv::load("../");
        Dotenv::required(array('FORWARD_NUMBER', 'TWILIO_AUTH_TOKEN'));

        $this->isDebugging = filter_var(getenv('DEBUG_MODE'), FILTER_VALIDATE_BOOLEAN);

        $this->forwardNumber = getenv('FORWARD_NUMBER');
        $this->twilioAuthToken = getenv('TWILIO_AUTH_TOKEN');
        $this->callScreener = new CallScreener(getenv('BLACKLIST'), getenv('WHITELIST'));

        $this->logger->info("Call from " . $this->requestParser->getCallerPhoneNumber() . " (" . $_SERVER['REMOTE_ADDR'] . ")");
    }

    /**
     * Check if either the caller or the recipient is on our blacklist
     *
     * @return boolean
     */
    public function callerIsBlocked()
    {
        $isRejected = $this->callScreener->isBlacklisted($this->requestParser->getRecipientPhoneNumber(), $this->requestParser->getCallerPhoneNumber());

        if ($isRejected) {
            $this->logger->warning("Caller " . $this->requestParser->getCallerPhoneNumber() . " is blocked.");
        }

        return $isRejected;
    }

    /**
     * Check if either the caller or the recipient is on our whitelist
     *
     * @return boolean
     */
    public function callerIsAllowed()
    {
        $isAllowed = $this->callScreener->isWhitelisted($this->requestParser->getRecipientPhoneNumber(), $this->requestParser->getCallerPhoneNumber());

        if (!$isAllowed) {
            $this->logger->warning("Caller " . $this->requestParser->getCallerPhoneNumber() . " is not allowed.");
        }

        return $isAllowed;
    }

    /**
     * Dial the configured forwarding number
     */
    public function dialForwardingNumber()
    {
        $actionUrl = $_SERVER['SERVER_NAME'] . '?Dial=true';
        if (isset($_REQUEST['FailUrl'])) {
            $actionUrl .= '&FailUrl=' . urlencode($_REQUEST['FailUrl']);
        }
        $attributes = array(
            'action' => $actionUrl,
            'timeout' => isset($_REQUEST['Timeout']) ? $_REQUEST['Timeout'] : 20,
        );
        if (isset($_GET['CallerId'])) {
            $attributes['callerId'] = $_GET['CallerId'];
        }
        $this->logger->debug("Dialing fowardNumber");

        if ($this->isDebugging != true) {
            $this->response->dial($this->forwardNumber, $attributes);
            $this->logger->info("Dialed out.");
        }
    }

    /**
     * Check if we're returning from an attempted Dial
     * - Taken from Twimlet Forward code: https://www.twilio.com/labs/twimlets/source/forward
     *
     * @return boolean
     */
    public function isDialRequest()
    {
        // if The Dial flag is present, it means we're returning from an attempted Dial
        if (isset($_REQUEST['Dial']) && (strlen($_REQUEST['DialStatus']) || strlen($_REQUEST['DialCallStatus']))) {
            return true;
        }
        return false;
    }

    /**
     * Handle a Dial request
     * - Taken from Twimlet Forward code: https://www.twilio.com/labs/twimlets/source/forward
     */
    public function handleDialRequest()
    {
        if ($_REQUEST['DialCallStatus'] == 'completed' || $_REQUEST['DialStatus'] == 'answered' || !strlen($_REQUEST['FailUrl'])) {
            // answered, or no failure url given, so just hangup
            $this->hangup();
        } else {
            // DialStatus was not answered, so redirect to FailUrl
            header('Location: ' . $_REQUEST['FailUrl']);
            die;
        }
    }

    /**
     * Check if this is a valid request originating from Twilio.com
     * - This check is bypassed if application is in debug mode
     *
     * @return boolean
     */
    public function isValidRequest()
    {
        if ($this->isDebugging) {
            return true;
        }

        $validator = new RequestValidator(new Services_Twilio_RequestValidator($this->twilioAuthToken));

        if ($validator->validate()) {
            $this->logger->info("Call originated from Twilio");
            return true;
        }

        $this->logger->warning("Invalid Twilio token");
        return false;
    }

    /**
     * Wrapper method around Twilio's say()
     *
     * @param  string $message
     */
    public function say($message)
    {
        $this->logger->debug("Saying: $message");
        $this->response->say($message);
    }

    /**
     * Wrapper method around Twilio's hangup()
     */
    public function hangup()
    {
        $this->logger->info("Hanging up");
        $this->response->hangup();
    }

    /**
     * Wrapper method around Twilio's reject()
     */
    public function reject()
    {
        $this->logger->info("Rejecting call.");
        $this->response->reject();
    }

    /**
     * Retrieve our Twilio response instance
     *
     * @return Services_Twilio_Twiml
     */
    public function getResponse()
    {
        return $this->response;
    }
}
