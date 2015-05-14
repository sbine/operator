# Operator for Twilio

A small library I made to blacklist solicitors and forward all other Twilio calls to my cell. It also has whitelist capability, because why not!

See [example/operator.php](example/operator.php) for a sample webhook script to use in your TwiML app.  
See [.env.example](.env.example) for configuration options (at minimum you will need `FORWARD_NUMBER` and `TWILIO_AUTH_TOKEN`)

## Logic
If a whitelist file is present and not empty, all non-whitelisted numbers will be rejected.  
If a blacklist file is present and not empty, all blacklisted numbers will be rejected.

## Logging
This library will attempt to log all activity to /var/log/twilio.log

### Inspiration
- BradyOsborne's [Twilio-Block-Numbers](https://github.com/BradyOsborne/Twilio-Block-Numbers)  
- Twilio's [Forward Twimlet](https://www.twilio.com/labs/twimlets/forward)