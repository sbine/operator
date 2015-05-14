<?php namespace Bine\Operator;

class CallScreener
{
    private $blacklist;
    private $whitelist;

    /**
     * Initialize Call Screener
     *
     * @param string $blacklist
     * @param string $whitelist
     */
    public function __construct($blacklist, $whitelist)
    {
        if (!empty($blacklist)) {
            $this->setBlacklist($blacklist);
        }
        if (!empty($whitelist)) {
            $this->setWhitelist($whitelist);
        }
    }

    /**
     * Specify blacklist file
     *
     * @param string $file
     */
    public function setBlacklist($file)
    {
        $blacklist = realpath("../" . $file);
        if (file_exists($blacklist)) {
            $this->blacklist = file($blacklist, FILE_IGNORE_NEW_LINES);
        }
    }

    /**
     * Specify whitelist file
     * @param string $file
     */
    public function setWhitelist($file)
    {
        $whitelist = realpath("../" . $file);
        if (file_exists($whitelist)) {
            $this->whitelist = file($whitelist, FILE_IGNORE_NEW_LINES);
        }
    }

    /**
     * Check if either caller or recipient numbers are blacklisted
     *
     * @param  string  $to
     * @param  string  $from
     *
     * @return boolean
     */
    public function isBlacklisted($to, $from)
    {
        if (empty($this->blacklist)) {
            return false;
        }
        foreach ($this->blacklist as $blockedNumber) {
            if ($this->normalizePhoneNumber($blockedNumber) == $this->normalizePhoneNumber($to)) {
                return true;
            } elseif ($this->normalizePhoneNumber($blockedNumber) == $this->normalizePhoneNumber($from)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if either caller or recipient numbers are whitelisted
     *
     * @param  string  $to
     * @param  string  $from
     *
     * @return boolean
     */
    public function isWhitelisted($to, $from)
    {
        if (empty($this->whitelist)) {
            return true;
        }
        foreach ($this->whitelist as $allowedNumber) {
            if ($this->normalizePhoneNumber($allowedNumber) == $this->normalizePhoneNumber($to)) {
                return true;
            } elseif ($this->normalizePhoneNumber($allowedNumber) == $this->normalizePhoneNumber($from)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize phone numbers
     * - Remove all non-numeric characters
     *
     * @param  string  $phoneNumber
     * @param  boolean $checkOldApiSyntax
     *
     * @return string
     */
    public function normalizePhoneNumber($phoneNumber, $checkOldApiSyntax = false)
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        if ($checkOldApiSyntax) {
            if ($_REQUEST['ApiVersion'] == '2008-08-01' && strlen($phoneNumber) == 11 && substr($phoneNumber, 0, 1) == '1') {
                $phoneNumber = substr($phoneNumber, 1);
            }
        }
        return $phoneNumber;
    }
}
