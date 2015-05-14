<?php namespace Bine\Operator;

class RequestValidator
{
    /**
     * Initialize the Twilio request validator
     *
     * @param Services_Twilio_RequestValidator $twilioValidator
     */
    public function __construct($twilioValidator)
    {
        $this->twilioValidator = $twilioValidator;
    }

    /**
     * Validate that this request originated from Twilio
     *
     * @return boolean
     */
    public function validate()
    {
        if (isset($_SERVER["HTTP_X_TWILIO_SIGNATURE"])) {
            $url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
            $postVars = $_REQUEST;
            $signature = $_SERVER["HTTP_X_TWILIO_SIGNATURE"];

            if ($this->twilioValidator->validate($signature, $url, $postVars)) {
                return true;
            }
        }

        return false;
    }
}
