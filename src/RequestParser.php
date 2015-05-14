<?php namespace Bine\Operator;

use Exception;

class RequestParser
{
    private $from;
    private $to;

    /**
     * Parse the caller phone number out of the Twilio request
     *
     * @return string
     */
    public function getCallerPhoneNumber()
    {
        if (!$this->from) {
            if (isset($_REQUEST['From']) && strlen($_REQUEST['From']) > 0) {
                $from = $_REQUEST['From'];
            } elseif (isset($_REQUEST['Caller']) && strlen($_REQUEST['Caller']) > 0) {
                $from = $_REQUEST['Caller'];
            } else {
                throw new Exception("Invalid 'From' parameter");
            }
            $this->from = trim($from);
        }

        return $this->from;
    }

    /**
     * Parse the recipient phone number out of the Twilio request
     *
     * @return string
     */
    public function getRecipientPhoneNumber()
    {
        if (!$this->to) {
            if (isset($_REQUEST['To']) && strlen($_REQUEST['To']) > 0) {
                $to = $_REQUEST['To'];
            } elseif (isset($_REQUEST['Caller']) && strlen($_REQUEST['Called']) > 0) {
                $to = $_REQUEST['Called'];
            } else {
                throw new Exception("Invalid 'To' parameter");
            }
            $this->to = trim($to);
        }

        return $this->to;
    }
}
