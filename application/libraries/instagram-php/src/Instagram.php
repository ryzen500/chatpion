<?php

namespace InstagramAPI;

/**
 * Nextpost Private API for Instagram v.5.0.7 with iOS emulation (iOS + Android)
 * 
 * @version NEXTPOST
 * Last update: 18.06.2021
 *
 * Each buyer of Nextpost PHP-script (originally developed by Postcode) 
 * have a license for the script and license for use this library.
 * 
 * * TERMS OF USE:
 * - This code is in no way affiliated with, authorized, maintained, sponsored
 *   or endorsed by Instagram or any of its affiliates or subsidiaries. This is
 *   an independent and unofficial API. Use at your own risk.
 * - We do NOT support or tolerate anyone who wants to use this API to send spam
 *   or commit other online crimes.
 * - You will NOT use this API for marketing or other abusive purposes (spam,
 *   botting, harassment, massive bulk messaging...).
 * 
 * Originaly developed by:
 * @author mgp25: Founder, Reversing, Project Leader (https://github.com/mgp25)
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 *
 */
class Instagram implements ExperimentsInterface
{
    /**
     * Experiments refresh interval in sec.
     *
     * @var int
     */
    const EXPERIMENTS_REFRESH = 7200;

    /**
     * Currently active Instagram username.
     *
     * @var string
     */
    public $username;

    /**
     * Currently active Instagram password.
     *
     * @var string
     */
    public $password;

    /**
     * Currently active Facebook access token.
     *
     * @var string
     */
    public $fb_access_token;

    /**
     * The Android device for the currently active user.
     *
     * @var \InstagramAPI\Devices\DeviceInterface
     */
    public $device;

    /**
     * Toggles API query/response debug output.
     *
     * @var bool
     */
    public $debug;

    /**
     * Toggles truncating long responses when debugging.
     *
     * @var bool
     */
    public $truncatedDebug;

    /**
     * For internal use by Instagram-API developers!
     *
     * Toggles the throwing of exceptions whenever Instagram-API's "Response"
     * classes lack fields that were provided by the server. Useful for
     * discovering that our library classes need updating.
     *
     * This is only settable via this public property and is NOT meant for
     * end-users of this library. It is for contributing developers!
     *
     * @var bool
     */
    public $apiDeveloperDebug = false;

    /**
     * Global flag for users who want to run the library incorrectly online.
     *
     * YOU ENABLE THIS AT YOUR OWN RISK! WE GIVE _ZERO_ SUPPORT FOR THIS MODE!
     * EMBEDDING THE LIBRARY DIRECTLY IN A WEBPAGE (WITHOUT ANY INTERMEDIARY
     * PROTECTIVE LAYER) CAN CAUSE ALL KINDS OF DAMAGE AND DATA CORRUPTION!
     *
     * YOU HAVE BEEN WARNED. ANY DATA CORRUPTION YOU CAUSE IS YOUR OWN PROBLEM!
     *
     * The right way to run the library online is described in `webwarning.htm`.
     *
     * @var bool
     *
     * @see Instagram::__construct()
     */
    public static $allowDangerousWebUsageAtMyOwnRisk = false;

    /**
     * Global flag for users who want to run the library incorrectly.
     *
     * YOU ENABLE THIS AT YOUR OWN RISK! WE GIVE _ZERO_ SUPPORT FOR THIS MODE!
     * THIS WILL SKIP ANY PRE AND POST LOGIN FLOW!
     *
     * THIS SHOULD BE ONLY USED FOR RESEARCHING AND EXPERIMENTAL PURPOSES.
     *
     * YOU HAVE BEEN WARNED. ANY DATA CORRUPTION YOU CAUSE IS YOUR OWN PROBLEM!
     *
     * @var bool
     */
    public static $skipLoginFlowAtMyOwnRisk = false;

    /**
     * Global flag for users who want to manage login exceptions on their own.
     *
     * YOU ENABLE THIS AT YOUR OWN RISK! WE GIVE _ZERO_ SUPPORT FOR THIS MODE!
     *
     * @var bool
     *
     * @see Instagram::__construct()
     */
    public static $manuallyManageLoginException = false;

    /**
     * Global flag for users who want to run deprecated functions.
     *
     * YOU ENABLE THIS AT YOUR OWN RISK! WE GIVE _ZERO_ SUPPORT FOR THIS MODE!
     *
     * @var bool
     *
     * @see Instagram::__construct()
     */
    public static $overrideDeprecatedThrower = false;

    /**
     * Set this value to false for the recent 
     * logins. This is required to use this API
     * in a webpage for consecutive
     * @var boolean
     */
    public static $sendLoginFlow = true;

    /**
     * UUID.
     *
     * @var string
     */
    public $uuid;

    /**
     * Google Play Advertising ID.
     *
     * The advertising ID is a unique ID for advertising, provided by Google
     * Play services for use in Google Play apps. Used by Instagram.
     *
     * @var string
     *
     * @see https://support.google.com/googleplay/android-developer/answer/6048248?hl=en
     */
    public $advertising_id;

    /**
     * Facebook Tracking ID.
     *
     * @var string
     */
    public $fb_tracking_id;

    /**
     * Device ID.
     *
     * @var string
     */
    public $device_id;

    /**
     * Phone ID.
     *
     * @var string
     */
    public $phone_id;

    /**
     * Numerical UserPK ID of the active user account.
     *
     * @var string
     */
    public $account_id;

    /**
     * Our current guess about the session status.
     *
     * This contains our current GUESS about whether the current user is still
     * logged in. There is NO GUARANTEE that we're still logged in. For example,
     * the server may have invalidated our current session due to the account
     * password being changed AFTER our last login happened (which kills all
     * existing sessions for that account), or the session may have expired
     * naturally due to not being used for a long time, and so on...
     *
     * NOTE TO USERS: The only way to know for sure if you're logged in is to
     * try a request. If it throws a `LoginRequiredException`, then you're not
     * logged in anymore. The `login()` function will always ensure that your
     * current session is valid. But AFTER that, anything can happen... It's up
     * to Instagram, and we have NO control over what they do with your session!
     *
     * @var bool
     */
    public $isMaybeLoggedIn = false;

    /**
     * Raw API communication/networking class.
     *
     * @var Client
     */
    public $client;

    /**
     * The account settings storage.
     *
     * @var \InstagramAPI\Settings\StorageHandler|null
     */
    public $settings;

    /**
     * The current application session ID.
     *
     * This is a temporary ID which changes in the official app every time the
     * user closes and re-opens the Instagram application or switches account.
     *
     * @var string
     */
    public $session_id;

    /**
     * A list of experiments enabled on per-account basis.
     *
     * @var array
     */
    public $experiments;

    /**
     * Custom Device string.
     *
     * @var string|null
     */
    public $customDeviceString = null;

    /**
     * Custom Device string.
     *
     * @var string|null
     */
    public $customDeviceId = null;

    /**
     * Login attempt counter.
     *
     * @var int
     */
    public $loginAttemptCount = 0;

    /**
     * The radio type used for requests.
     *
     * @var array
     */
    public $radio_type = 'wifi-none';

    /**
     * Timezone offset.
     *
     * @var int
     */
    public $timezoneOffset = null;

    /**
     * The platform used for requests.
     *
     * @var string
     */
    public $platform;

    /**
     * Connection speed.
     *
     * @var string
     */
    public $connectionSpeed = '-1kbps';

    /**
     * VP9 Capable.
     *
     * @var string
     */
    public $vp9Capable = 'true';

    /**
     * EU user.
     *
     * @var bool
     */
    public $isEUUser = true;

    /**
     * Battery level.
     *
     * @var int
     */
    public $batteryLevel = 100;

    /**
     * Device charging.
     *
     * @var bool
     */
    public $isDeviceCharging = true;

    /**
     * Locale.
     *
     * @var string
     */
    public $locale = '';

    /**
     * Accept language.
     *
     * @var string
     */
    public $acceptLanguage = '';

    /**
     * Event batch collection.
     *
     * @var array
     */
    public $eventBatch = [];

    /**
     * Batch index.
     *
     * @var int
     */
    public $batchIndex = 0;

    /**
     * Navigation sequence.
     *
     * @var int
     */
    public $navigationSequence = 0;

    /**
     * Checkpoints
     *
     * @var bool
     */
    public $isWebLogin = false;
    public $_needsAuth = true;

    /** @var Request\Account Collection of Account related functions. */
    public $account;
    /** @var Request\Business Collection of Business related functions. */
    public $business;
    /** @var Request\Collection Collection of Collections related functions. */
    public $collection;
    /** @var Request\Creative Collection of Creative related functions. */
    public $creative;
    /** @var Request\Direct Collection of Direct related functions. */
    public $direct;
    /** @var Request\Discover Collection of Discover related functions. */
    public $discover;
    /** @var Request\Event Collection of Event related functions. */
    public $event;
    /** @var Request\Hashtag Collection of Hashtag related functions. */
    public $hashtag;
    /** @var Request\Highlight Collection of Highlight related functions. */
    public $highlight;
    /** @var Request\TV Collection of Instagram TV functions. */
    public $tv;
    /** @var Request\Internal Collection of Internal (non-public) functions. */
    public $internal;
    /** @var Request\Live Collection of Live related functions. */
    public $live;
    /** @var Request\Location Collection of Location related functions. */
    public $location;
    /** @var Request\Media Collection of Media related functions. */
    public $media;
    /** @var Request\People Collection of People related functions. */
    public $people;
    /** @var Request\Push Collection of Push related functions. */
    public $push;
    /** @var Request\Shopping Collection of Shopping related functions. */
    public $shopping;
    /** @var Request\Story Collection of Story related functions. */
    public $story;
    /** @var Request\Timeline Collection of Timeline related functions. */
    public $timeline;
    /** @var Request\Usertag Collection of Usertag related functions. */
    public $usertag;

    /**
     * Constructor.
     *
     * @param bool  $debug          Show API queries and responses.
     * @param bool  $truncatedDebug Truncate long responses in debug.
     * @param array $storageConfig  Configuration for the desired
     *                              user settings storage backend.
     * @param bool   $platform      The platform to be emulated. 'android' or 'ios'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function __construct(
        $debug = false,
        $truncatedDebug = false,
        array $storageConfig = [],
        $platform = 'android')
    {
        if ($platform !== 'android' && $platform !== 'ios') {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid platform.', $platform));
        } else {
            $this->platform = $platform;
        }
        
        // Disable incorrect web usage by default. People should never embed
        // this application emulator library directly in a webpage, or they
        // might cause all kinds of damage and data corruption. They should
        // use an intermediary layer such as a database or a permanent process!
        // NOTE: People can disable this safety via the flag at their own risk.
        if (!self::$allowDangerousWebUsageAtMyOwnRisk && (!defined('PHP_SAPI') || PHP_SAPI !== 'cli')) {
            // IMPORTANT: We do NOT throw any exception here for users who are
            // running the library via a webpage. Many webservers are configured
            // to hide all PHP errors, and would just give the user a totally
            // blank webpage with "Error 500" if we throw here, which would just
            // confuse the newbies even more. Instead, we output a HTML warning
            // message for people who try to run the library on a webpage.
            echo file_get_contents(__DIR__.'/../webwarning.htm');
            echo '<p>If you truly want to enable <em>incorrect</em> website usage by directly embedding this application emulator library in your page, then you can do that <strong>AT YOUR OWN RISK</strong> by setting the following flag <em>before</em> you create the <code>Instagram()</code> object:</p>'.PHP_EOL;
            echo '<p><code>\InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;</code></p>'.PHP_EOL;
            exit(0); // Exit without error to avoid triggering Error 500.
        }

        // Prevent people from running this library on ancient PHP versions, and
        // verify that people have the most critically important PHP extensions.
        // NOTE: All of these are marked as requirements in composer.json, but
        // some people install the library at home and then move it somewhere
        // else without the requirements, and then blame us for their errors.
        if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50600) {
            throw new \InstagramAPI\Exception\InstagramException(
                'You must have PHP 5.6 or higher to use the Instagram API library.'
            );
        }

        static $extensions = ['curl', 'mbstring', 'gd', 'exif', 'zlib'];
        foreach ($extensions as $ext) {
            if (!@extension_loaded($ext)) {
                throw new \InstagramAPI\Exception\InstagramException(sprintf(
                    'You must have the "%s" PHP extension to use the Instagram API library.',
                    $ext
                ));
            }
        }


        // Check Ramsey\Uuid library exist
        if (!class_exists('Ramsey\Uuid\Uuid')) {
            throw new \InstagramAPI\Exception\InstagramException(sprintf(
                'You must have the %s to use the Instagram API library.',
                '<a target="_blank" href="https://github.com/ramsey/uuid">Ramsey\Uuid library</a>'
            ));
        }

        // Debugging options.
        $this->debug = $debug;
        $this->truncatedDebug = $truncatedDebug;

        // Load all function collections.
        $this->account = new Request\Account($this);
        $this->business = new Request\Business($this);
        $this->collection = new Request\Collection($this);
        $this->creative = new Request\Creative($this);
        $this->direct = new Request\Direct($this);
        $this->discover = new Request\Discover($this);
        $this->event = new Request\Event($this);
        $this->hashtag = new Request\Hashtag($this);
        $this->highlight = new Request\Highlight($this);
        $this->tv = new Request\TV($this);
        $this->internal = new Request\Internal($this);
        $this->live = new Request\Live($this);
        $this->location = new Request\Location($this);
        $this->media = new Request\Media($this);
        $this->people = new Request\People($this);
        $this->push = new Request\Push($this);
        $this->shopping = new Request\Shopping($this);
        $this->story = new Request\Story($this);
        $this->timeline = new Request\Timeline($this);
        $this->usertag = new Request\Usertag($this);

        // Configure the settings storage and network client.
        $self = $this;
        $this->settings = Settings\Factory::createHandler(
            $storageConfig,
            [
                // This saves all user session cookies "in bulk" at script exit
                // or when switching to a different user, so that it only needs
                // to write cookies to storage a few times per user session:
                'onCloseUser' => function ($storage) use ($self) {
                    if ($self->client instanceof Client) {
                        $self->client->saveCookieJar();
                    }
                },
            ]
        );
        $this->client = new Client($this);
        $this->experiments = [];
    }

    /**
     * Controls the SSL verification behavior of the Client.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#verify
     *
     * @param bool|string $state TRUE to verify using PHP's default CA bundle,
     *                           FALSE to disable SSL verification (this is
     *                           insecure!), String to verify using this path to
     *                           a custom CA bundle file.
     */
    public function setVerifySSL(
        $state)
    {
        $this->client->setVerifySSL($state);
    }

    /**
     * Gets the current SSL verification behavior of the Client.
     *
     * @return bool|string
     */
    public function getVerifySSL()
    {
        return $this->client->getVerifySSL();
    }

    /**
     * Set the proxy to use for requests.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#proxy
     *
     * @param string|array|null $value String or Array specifying a proxy in
     *                                 Guzzle format, or NULL to disable
     *                                 proxying.
     */
    public function setProxy(
        $value)
    {
        $this->client->setProxy($value);
    }

    /**
     * Gets the current proxy used for requests.
     *
     * @return string|array|null
     */
    public function getProxy()
    {
        return $this->client->getProxy();
    }

    /**
     * Set a custom device string.
     *
     * If the provided device string is not valid, a device from
     * the good devices list will be chosen randomly.
     *
     * @param string|null $value Device string.
     */
    public function setDeviceString(
        $value)
    {
        $this->customDeviceString = $value;
    }

    /**
     * Set a custom device ID.
     *
     * @param string|null $value Device string.
     */
    public function setCustomDeviceId(
        $value)
    {
        $this->customDeviceId = $value;
    }

    /**
     * Sets the network interface override to use.
     *
     * Only works if Guzzle is using the cURL backend. But that's
     * almost always the case, on most PHP installations.
     *
     * @see http://php.net/curl_setopt CURLOPT_INTERFACE
     *
     * @param string|null $value Interface name, IP address or hostname, or NULL
     *                           to disable override and let Guzzle use any
     *                           interface.
     */
    public function setOutputInterface(
        $value)
    {
        $this->client->setOutputInterface($value);
    }

    /**
     * Gets the current network interface override used for requests.
     *
     * @return string|null
     */
    public function getOutputInterface()
    {
        return $this->client->getOutputInterface();
    }

    /**
     * Set the radio type used for requests.
     *
     * @param string $value String specifying the radio type.
     */
    public function setRadioType(
        $value)
    {
        if ($value !== 'wifi-none' && $value !== 'mobile-lte') {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid radio type.', $value));
        }

        $this->radio_type = $value;
    }

    /**
     * Get the radio type used for requests.
     *
     * @return string
     */
    public function getRadioType()
    {
        return $this->radio_type;
    }

    /**
     * Set the timezone offset.
     *
     * @param int $value Timezone offset.
     */
    public function setTimezoneOffset(
        $value)
    {
        $this->timezoneOffset = $value;
    }

    /**
     * Get timezone offset.
     *
     * @return string
     */
    public function getTimezoneOffset()
    {
        return $this->timezoneOffset;
    }

    /**
     * Set locale.
     *
     * @param string $value
     */
    public function setLocale(
        $value)
    {
        preg_match('/^[a-z]{2}_[A-Z]{2}$/', $value, $matches, PREG_OFFSET_CAPTURE, 0);

        if (!empty($matches)) {
            $this->locale = $value;
        } else {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid locale.', $value));
        }
    }

    /**
     * Get locale.
     *
     * @return string
     */
    public function getLocale()
    {
        if ($this->locale === '') {
            return Constants::USER_AGENT_LOCALE;
        } else {
            return $this->locale;
        }
    }

    /**
     * Get startup country.
     *
     * @return string
     */
    public function getStartupCountry()
    {
        if ($this->locale === '') {
            return Constants::APP_STARTUP_COUNTRY;
        } else {
            return $this->locale;
        }
    }

    /**
     * Set accept Language.
     *
     * @param string $value
     */
    public function setAcceptLanguage(
        $value)
    {
        preg_match('/^[a-z]{2}-[A-Z]{2}$/', $value, $matches, PREG_OFFSET_CAPTURE, 0);

        if (!empty($matches)) {
            $this->acceptLanguage = $value;
        } else {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid accept language value.', $value));
        }
    }

    /**
     * Get Accept Language.
     *
     * @return string
     */
    public function getAcceptLanguage()
    {
        if ($this->acceptLanguage === '') {
            return Constants::ACCEPT_LANGUAGE;
        } else {
            return $this->acceptLanguage;
        }
    }

    /**
     * Set the platform used for requests.
     *
     * @return string
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
    }

    /**
     * Get the platform used for requests.
     *
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Check if running on Android platform.
     *
     * @return string
     */
    public function getIsAndroid()
    {
        return $this->platform === 'android';
    }

    /**
     * Check if using an android session.
     *
     * @return bool
     */
    public function getIsAndroidSession()
    {
        if (strpos($this->settings->get('device_id'), 'android') !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the connection speed.
     *
     * @return string
     */
    public function getConnectionSpeed()
    {
        // return $this->connectionSpeed;
        return mt_rand(1000, 3700) . 'kbps';
    }

    /**
     * Set the connection speed.
     *
     * @param string $value Connection Speed. Format: '53kbps'.
     */
    public function setConnectionSpeed(
        $value)
    {
        $this->connectionSpeed = $value;
    }

    /**
     * Get VP9 capable.
     *
     * @return string
     */
    public function getVP9Capable()
    {
        return $this->vp9Capable;
    }

    /**
     * Enable/Disable VP9 capable.
     *
     * @param string $value. 'true' or 'false'
     */
    public function enableVP9Capable(
        $value = 'true')
    {
        $this->vp9Capable = $value;
    }

    /**
     * Get if user is in EU.
     *
     * @return bool
     */
    public function getIsEUUser()
    {
        return $this->isEUUser;
    }

    /**
     * Set if user is from EU.
     *
     * @param bool $value. 'true' or 'false'
     */
    public function setIsEUUser(
        $value)
    {
        $this->isEUUser = $value;
    }

    /**
     * Get battery level.
     *
     * @return int
     */
    public function getBatteryLevel()
    {
        return $this->batteryLevel;
    }

    /**
     * Set battery level.
     *
     * @param int $value.
     */
    public function setBatteryLevel(
        $value)
    {
        if ($value < 1 && $value > 100) {
            throw new \InvalidArgumentException(sprintf('"%d" is not a valid battery level.', $value));
        }

        $this->batteryLevel = $value;
    }

    /**
     * Get if device is charging.
     *
     * @return string
     */
    public function getIsDeviceCharging()
    {
        return strval($this->isDeviceCharging);
    }

    /**
     * Set if device is charging.
     *
     * @param bool $value.
     */
    public function setIsDeviceCharging(
        $value)
    {
        $this->isDeviceCharging = $value;
    }

    /**
     * Get if device is VP9 compatible.
     *
     * @return bool
     */
    public function getIsVP9Compatible()
    {
        return $this->device->getIsVP9Compatible();
    }

    /**
     * Login to Instagram or automatically resume and refresh previous session.
     *
     * Sets the active account for the class instance. You can call this
     * multiple times to switch between multiple Instagram accounts.
     *
     * WARNING: You MUST run this function EVERY time your script runs! It
     * handles automatic session resume and relogin and app session state
     * refresh and other absolutely *vital* things that are important if you
     * don't want to be banned from Instagram!
     *
     * WARNING: This function MAY return a CHALLENGE telling you that the
     * account needs two-factor login before letting you log in! Read the
     * two-factor login example to see how to handle that.
     *
     * @param string $username           Your Instagram username.
     *                                   You can also use your email or phone,
     *                                   but take in mind that they won't work
     *                                   when you have two factor auth enabled.
     * @param string $password           Your Instagram password.
     * @param int    $appRefreshInterval How frequently `login()` should act
     *                                   like an Instagram app that's been
     *                                   closed and reopened and needs to
     *                                   "refresh its state", by asking for
     *                                   extended account state details.
     *                                   Default: After `1800` seconds, meaning
     *                                   `30` minutes after the last
     *                                   state-refreshing `login()` call.
     *                                   This CANNOT be longer than `6` hours.
     *                                   Read `_sendLoginFlow()`! The shorter
     *                                   your delay is the BETTER. You may even
     *                                   want to set it to an even LOWER value
     *                                   than the default 30 minutes!
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse|null A login response if a
     *                                                   full (re-)login
     *                                                   happens, otherwise
     *                                                   `NULL` if an existing
     *                                                   session is resumed.
     */
    public function login(
        $username,
        $password,
        $appRefreshInterval = 1800)
    {
        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException('You must provide a username and password to login().');
        }
        return $this->_login($username, $password, false, $appRefreshInterval);
    }

    /**
     * Login to Instagram (Web API)
     * 
     * @param string $username
     * @param string $password
     * 
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return string
     */
    public function loginGraph(
        $username,
        $password) 
    {
        if ($this->isWebLogin) {
            // This is web session
            $this->_setUser($username, $password);
        } else {
            // Switch mobile session to web session
            $this->login($username, $password);
        }

        $this->isWebLogin = true;

        $constants = $this->getDataFromWeb();
        $rollout_hash = isset($constants["rollout_hash"]) ? (string)$constants["rollout_hash"] : null;
        $query_hash_gfs = isset($constants["query_hash_gfs"]) ? (string)$constants["query_hash_gfs"] : null; // query_hash for getFollowersGraph() function
        $query_hash_gfg = isset($constants["query_hash_gfg"]) ? (string)$constants["query_hash_gfg"] : null; // query_hash for getFollowingGraph() function
        $query_hash_guf = isset($constants["query_hash_guf"]) ? (string)$constants["query_hash_guf"] : null; // query_hash for getUserFeedGraph() function
        $query_hash_glk = isset($constants["query_hash_glk"]) ? (string)$constants["query_hash_glk"] : null; // query_hash for getLikersGraph() function
        $asbd_id = isset($constants["asbd_id"]) ? (string)$constants["asbd_id"] : '437806';

        // Deprecated query hashes
        $this->settings->set('query_hash_gh', "9b498c08113f1e09617a1703c22b2f32"); // query_hash for getFeedGraph() function for hashtag 
        $this->settings->set('query_hash_gl', "36bd0f2bf5911908de389b8ceaa3be6d"); // query_hash for getFeedGraph() function for location
        $this->settings->set('query_hash_gr', "c9c56db64beb4c9dea2d17740d0259d9"); // query_hash for getReelsMediaFeedGraph() function for location

        if ($rollout_hash) {
            $this->settings->set('rollout_hash', $rollout_hash);
        } else {
            throw new \InstagramAPI\Exception\InstagramException("Couldn't detect rollout_hash in loginGraph() function.");
        }

        if ($query_hash_gfs) {
            $this->settings->set('query_hash_gfs', $query_hash_gfs);
        } else {
            throw new \InstagramAPI\Exception\InstagramException("Couldn't detect query_hash_gfs for getFollowersGraph() in loginGraph() function.");
        }

        if ($query_hash_gfg) {
            $this->settings->set('query_hash_gfg', $query_hash_gfg);
        } else {
            throw new \InstagramAPI\Exception\InstagramException("Couldn't detect query_hash_gfg for getFollowingGraph() in loginGraph() function.");
        }

        if ($query_hash_guf) {
            $this->settings->set('query_hash_guf', $query_hash_guf);
        } else {
            throw new \InstagramAPI\Exception\InstagramException("Couldn't detect query_hash_guf for getUserFeedGraph() in loginGraph() function.");
        }

        if ($query_hash_glk) {
            $this->settings->set('query_hash_glk', $query_hash_glk);
        } else {
            throw new \InstagramAPI\Exception\InstagramException("Couldn't detect query_hash_glk for getLikersGraph() in loginGraph() function.");
        }

        if ($asbd_id) {
            $this->settings->set('asbd_id', $asbd_id);
        } else {
            throw new \InstagramAPI\Exception\InstagramException("Couldn't detect asbd_id for web API");
        }

        $query = [
            'next'        => '/accounts/access_tool/',
            'oneTapUsers' => [$this->account_id],
        ];

        $request = $this->request('accounts/login/ajax/')
            ->setVersion(5)
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->setAddDefaultHeaders(false)
            ->addHeader('X-CSRFToken', $this->client->getToken())
            ->addHeader('Referer', 'https://www.instagram.com/')
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('X-IG-App-ID', Constants::IG_WEB_APPLICATION_ID)
            ->addHeader('X-Instagram-AJAX', $rollout_hash);
        if ($this->getIsAndroid()) {
            $request->addHeader('User-Agent', sprintf('Mozilla/5.0 (Linux; Android %s; Google) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Mobile Safari/537.36', $this->device->getAndroidRelease()));
        } else {
            $request->addHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS ' . Constants::IOS_VERSION . ' like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.4 Mobile/15E148 Safari/604.1');
        }
        $request->addHeader('Referer', 'https://www.instagram.com/accounts/login/')
            ->addPost('username', $username)
            ->addPost('enc_password', Utils::encryptPassword($password, $this->settings->get('public_key_id'), $this->settings->get('public_key')))
            ->addPost('query_params', json_encode($query));
        return $request->getDecodedResponse(false);
    }

    /**
     * Create session settings for user 
     *
     * @param string $username
     * @param string $password
     * 
     */
    public function changeUser(
        $username,
        $password)
    {
        $this->_setUser($username, $password);
    }

    /**
     * Internal login handler.
     *
     * @param string $username
     * @param string $password
     * @param bool   $forceLogin         Force login to Instagram instead of
     *                                   resuming previous session. Used
     *                                   internally to do a new, full relogin
     *                                   when we detect an expired/invalid
     *                                   previous session.
     * @param int    $appRefreshInterval
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse|null
     *
     * @see Instagram::login() The public login handler with a full description.
     */
    protected function _login(
        $username,
        $password,
        $forceLogin = false,
        $appRefreshInterval = 1800)
    {
        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException('You must provide a username and password to _login().');
        }

        // Switch the currently active user/pass if the details are different.
        if ($this->username !== $username || $this->password !== $password) {
            $this->_setUser($username, $password);

            if ($this->settings->get('pending_events') !== null) {
                $this->eventBatch = json_decode($this->settings->get('pending_events'));
                $this->settings->set('pending_events', '');
            }
        }

        // Licensing section
        // if (empty($this->settings->get('jazoest'))) {
        //     if (empty(Constants::LICENSE_KEY) || Constants::LICENSE_KEY == "YOUR-LICENSE-KEY") {
        //         throw new \InvalidArgumentException('You must define a valid "LICENSE_KEY" to use this API in file /instagram-php/src/Constants.php.');
        //     }
        //     $proxy = $this->getProxy();
        //     $this->setProxy(null);
        //     $nextpost = $this->nextpost_api(Constants::LICENSE_KEY, "ig-api", $_SERVER['SERVER_NAME'], $this->phone_id);
        //     $this->setProxy($proxy);
        //     $nextpost_resp = json_decode($nextpost);
        //     if (isset($nextpost_resp->jazoest)) {
        //         $this->settings->set('jazoest', $nextpost_resp->jazoest);
        //     } else {
        //         throw new \InvalidArgumentException('You must define a valid "LICENSE_KEY" to use this API in file /instagram-php/src/Constants.php.');
        //     }
        // }

        $jazoest = Utils::generateJazoest($this->phone_id);
        $this->settings->set('jazoest', $jazoest);

        // Perform a full relogin if necessary.
        if (!$this->isMaybeLoggedIn || $forceLogin) {
            $this->client->_wwwClaim = 0;

            $this->_sendPreLoginFlow();

            $startTime = round(microtime(true) * 1000);
            $waterfallId = \InstagramAPI\Signatures::generateUUID();

            $this->event->sendLoginProcedure('log_in_username_focus', $waterfallId, $startTime, round(microtime(true) * 1000));
            $this->event->sendLoginProcedure('log_in_password_focus', $waterfallId, $startTime, round(microtime(true) * 1000));
            $this->event->sendLoginProcedure('log_in_attempt', $waterfallId, $startTime, round(microtime(true) * 1000));
            $this->event->sendLoginProcedure('sim_card_state', $waterfallId, $startTime, round(microtime(true) * 1000));

            try {
                $request = $this->request('accounts/login/')
                    ->setNeedsAuth(false)
                    ->addPost('jazoest', $this->settings->get('jazoest'))
                    ->addPost('device_id', $this->device_id)
                    ->addPost('username', $this->username)
                    ->addPost('enc_password', Utils::encryptPassword($password, $this->settings->get('public_key_id'), $this->settings->get('public_key')))
                    ->addPost('_csrftoken', $this->client->getToken())
                    ->addPost('phone_id', $this->phone_id)
                    ->addPost('adid', $this->advertising_id)
                    ->addPost('login_attempt_count', $this->loginAttemptCount);

                if ($this->getPlatform() === 'android') {
                    $request->addPost('country_codes', '[{"country_code":"1","source":["default"]}]')
                            ->addPost('guid', $this->uuid)
                            ->addPost('google_tokens', '[]');
                } elseif ($this->getPlatform() === 'ios') {
                    $request->addPost('req_login', '0');
                }
                $response = $request->getResponse(new Response\LoginResponse());
            } catch (\InstagramAPI\Exception\Checkpoint\ChallengeRequiredException $e) {
                // Login failed because checkpoint is required.
                // Return server response to tell user they to bypass checkpoint.
                throw $e;
            } catch (\InstagramAPI\Exception\InstagramException $e) {
                if ($e->hasResponse() && $e->getResponse()->isTwoFactorRequired()) {
                    // Login failed because two-factor login is required.
                    // Return server response to tell user they need 2-factor.
                    return $e->getResponse();
                } elseif ($e->hasResponse() && ($e->getResponse()->getInvalidCredentials() === true)) {
                    ++$this->loginAttemptCount;

                    throw $e;
                } else {
                    if ($e->getResponse() === null) {
                        throw new \InstagramAPI\Exception\NetworkException($e);
                    }
                    // Login failed for some other reason... Re-throw error.
                    throw $e;
                }
            }

            $this->event->sendLoginProcedure('log_in', $waterfallId, $startTime, round(microtime(true) * 1000));
            $this->event->pushNotificationSettings();
            $this->event->enableNotificationSettings([
                'ig_other', 'ig_direct', 'uploads', 'ig_direct_requests', 'ig_direct_video_chat',
            ]);
            $this->event->sendNavigation('cold start', 'login', 'feed_timeline');
            $this->loginAttemptCount = 0;
            $this->_updateLoginState($response);

            $this->_sendLoginFlow(true, $appRefreshInterval);

            // Full (re-)login successfully completed. Return server response.
            return $response;
        }

        // Attempt to resume an existing session, or full re-login if necessary.
        // NOTE: The "return" here gives a LoginResponse in case of re-login.
        return $this->_sendLoginFlow(false, $appRefreshInterval);
    }

    /**
     * Login to Instagram with Facebook or automatically resume and refresh previous session.
     *
     * Sets the active account for the class instance. You can call this
     * multiple times to switch between multiple Instagram accounts.
     *
     * WARNING: You MUST run this function EVERY time your script runs! It
     * handles automatic session resume and relogin and app session state
     * refresh and other absolutely *vital* things that are important if you
     * don't want to be banned from Instagram!
     *
     * WARNING: This function MAY return a CHALLENGE telling you that the
     * account needs two-factor login before letting you log in! Read the
     * two-factor login example to see how to handle that.
     *
     * @param string $username           Your Instagram username.
     * @param string $fbAccessToken      Your Facebook access token.
     * @param int    $appRefreshInterval How frequently `loginWithFacebook()` should act
     *                                   like an Instagram app that's been
     *                                   closed and reopened and needs to
     *                                   "refresh its state", by asking for
     *                                   extended account state details.
     *                                   Default: After `1800` seconds, meaning
     *                                   `30` minutes after the last
     *                                   state-refreshing `login()` call.
     *                                   This CANNOT be longer than `6` hours.
     *                                   Read `_sendLoginFlow()`! The shorter
     *                                   your delay is the BETTER. You may even
     *                                   want to set it to an even LOWER value
     *                                   than the default 30 minutes!
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse|null A login response if a
     *                                                   full (re-)login
     *                                                   happens, otherwise
     *                                                   `NULL` if an existing
     *                                                   session is resumed.
     */
    public function loginWithFacebook(
        $username,
        $fbAccessToken,
        $appRefreshInterval = 1800)
    {
        if (empty($username) || empty($fbAccessToken)) {
            throw new \InvalidArgumentException('You must provide a Facebook access token to loginWithFacebook().');
        }

        return $this->_loginWithFacebook($username, $fbAccessToken, false, $appRefreshInterval);
    }

    /**
     * Internal Facebook login handler.
     *
     * @param string $username           Your Instagram username.
     * @param string $fbAccessToken      Facebook access token.
     * @param bool   $forceLogin         Force login to Instagram instead of
     *                                   resuming previous session. Used
     *                                   internally to do a new, full relogin
     *                                   when we detect an expired/invalid
     *                                   previous session.
     * @param int    $appRefreshInterval
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse|null
     *
     * @see Instagram::loginWithFacebook() The public Facebook login handler with a full description.
     */
    protected function _loginWithFacebook(
        $username,
        $fbAccessToken,
        $forceLogin = false,
        $appRefreshInterval = 1800)
    {
        if (empty($fbAccessToken)) {
            throw new \InvalidArgumentException('You must provide an fb_access_token to _loginWithFacebook().');
        }
        // Switch the currently active access token if it is different.
        if ($this->fb_access_token !== $fbAccessToken) {
            $this->_setFacebookUser($username, $fbAccessToken);
        }
        if (!$this->isMaybeLoggedIn || $forceLogin) {
            $this->client->_wwwClaim = 0;

            $this->_sendPreLoginFlow();

            try {
                $response = $this->request('fb/facebook_signup/')
                    ->setNeedsAuth(false)
                    ->addPost('dryrun', 'false')
                    ->addPost('phone_id', $this->phone_id)
                    ->addPost('adid', $this->advertising_id)
                    ->addPost('device_id', $this->device_id)
                    ->addPost('waterfall_id', Signatures::generateUUID())
                    ->addPost('fb_access_token', $this->fb_access_token)
                    ->getResponse(new Response\LoginResponse());
            } catch (\InstagramAPI\Exception\InstagramException $e) {
                if ($e->hasResponse() && $e->getResponse()->isTwoFactorRequired()) {
                    // Login failed because two-factor login is required.
                    // Return server response to tell user they need 2-factor.
                    return $e->getResponse();
                } elseif ($e->hasResponse() && ($e->getResponse()->getInvalidCredentials() === true)) {
                    ++$this->loginAttemptCount;
                } else {
                    if ($e->getResponse() === null) {
                        throw new \InstagramAPI\Exception\NetworkException($e);
                    }
                    // Login failed for some other reason... Re-throw error.
                    throw $e;
                }
            }
            $this->loginAttemptCount = 0;
            $this->_updateLoginState($response);

            $this->_sendLoginFlow(true, $appRefreshInterval);

            // Full (re-)login successfully completed. Return server response.
            return $response;
        }

        // Attempt to resume an existing session, or full re-login if necessary.
        // NOTE: The "return" here gives a LoginResponse in case of re-login.
        return $this->_sendLoginFlow(false, $appRefreshInterval);
    }

    /**
     * Finish a two-factor authenticated login.
     *
     * This function finishes a two-factor challenge that was provided by the
     * regular `login()` function. If you successfully answer their challenge,
     * you will be logged in after this function call.
     *
     * @param string      $username            Your Instagram username used for login.
     *                                         Email and phone aren't allowed here.
     * @param string      $password            Your Instagram password.
     * @param string      $twoFactorIdentifier Two factor identifier, obtained in
     *                                         login() response. Format: `123456`.
     * @param string      $verificationCode    Verification code you have received
     *                                         via SMS.
     * @param string      $verificationMethod  The verification method for 2FA. 1 is SMS,
     *                                         2 is backup codes and 3 is TOTP.
     * @param int         $appRefreshInterval  See `login()` for description of this
     *                                         parameter.
     * @param string|null $usernameHandler     Instagram username sent in the login response.
     *                                         Email and phone aren't allowed here.
     * @param bool        $trustDevice         If you want to trust the used Device ID.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse
     */
    public function finishTwoFactorLogin(
        $username,
        $password,
        $twoFactorIdentifier,
        $verificationCode,
        $verificationMethod = '1',
        $appRefreshInterval = 1800,
        $usernameHandler = null,
        $trustDevice = true)
    {
        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException('You must provide a username and password to finishTwoFactorLogin().');
        }
        if (!in_array($verificationMethod, ['1', '2', '3', '4'], true)) {
            throw new \InvalidArgumentException('You must provide a valid verification method value.');
        }
        if ($verificationMethod !== '4') {
            if (empty($verificationCode) || empty($twoFactorIdentifier)) {
                throw new \InvalidArgumentException('You must provide a verification code and two-factor identifier to finishTwoFactorLogin().');
            }
        } else {
            $verificationCode = ''; 
        }

        // Switch the currently active user/pass if the details are different.
        // NOTE: The username and password AREN'T actually necessary for THIS
        // endpoint, but this extra step helps people who statelessly embed the
        // library directly into a webpage, so they can `finishTwoFactorLogin()`
        // on their second page load without having to begin any new `login()`
        // call (since they did that in their previous webpage's library calls).
        if ($this->username !== $username || $this->password !== $password) {
            $this->_setUser($username, $password);
        }

        $username = ($usernameHandler !== null) ? $usernameHandler : $username;

        // Remove all whitespace from the verification code.
        $verificationCode = preg_replace('/\s+/', '', $verificationCode);

        $response = $this->request('accounts/two_factor_login/')
            ->setNeedsAuth(false)
            // 1 - SMS, 2 - Backup codes, 3 - TOTP, 4 - Trusted device, 0 - ??
            ->addPost('verification_method', $verificationMethod)
            ->addPost('phone_id', $this->phone_id)
            ->addPost('verification_code', $verificationCode)
            ->addPost('trust_this_device', ($trustDevice) ? '1' : '0')
            ->addPost('two_factor_identifier', $twoFactorIdentifier)
            ->addPost('_csrftoken', $this->client->getToken())
            ->addPost('username', $username)
            ->addPost('device_id', $this->device_id)
            ->addPost('guid', $this->uuid)
            ->getResponse(new Response\LoginResponse());

        $this->_updateLoginState($response);

        $this->_sendLoginFlow(true, $appRefreshInterval);

        return $response;
    }

    /**
     * Check trusted notification status
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse
     */
    public function checkTrustedNotificationStatus(
        $username,
        $password,
        $two_factor_identifier)
    {
        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException('You must provide a username and password to finishTwoFactorLogin().');
        }

        // Switch the currently active user/pass if the details are different.
        // NOTE: The username and password AREN'T actually necessary for THIS
        // endpoint, but this extra step helps people who statelessly embed the
        // library directly into a webpage, so they can `finishTwoFactorLogin()`
        // on their second page load without having to begin any new `login()`
        // call (since they did that in their previous webpage's library calls).
        if ($this->username !== $username || $this->password !== $password) {
            $this->_setUser($username, $password);
        }
        
        return $this->request('two_factor/check_trusted_notification_status/')
            ->setNeedsAuth(false)
            ->addPost('two_factor_identifier', $two_factor_identifier)
            ->addPost('username', $username)
            ->addPost('device_id', $this->uuid)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Request a new security code SMS for a Two Factor login account.
     *
     * NOTE: You should first attempt to `login()` which will automatically send
     * you a two factor SMS. This function is just for asking for a new SMS if
     * the old code has expired.
     *
     * NOTE: Instagram can only send you a new code every 60 seconds.
     *
     * @param string $username            Your Instagram username.
     * @param string $password            Your Instagram password.
     * @param string $twoFactorIdentifier Two factor identifier, obtained in
     *                                    `login()` response.
     * @param string $usernameHandler     Instagram username sent in the login response,
     *                                    Email and phone aren't allowed here.
     *                                    Default value is the first argument $username
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\TwoFactorLoginSMSResponse
     */
    public function sendTwoFactorLoginSMS(
        $username,
        $password,
        $twoFactorIdentifier,
        $usernameHandler = null)
    {
        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException('You must provide a username and password to sendTwoFactorLoginSMS().');
        }
        if (empty($twoFactorIdentifier)) {
            throw new \InvalidArgumentException('You must provide a two-factor identifier to sendTwoFactorLoginSMS().');
        }

        // Switch the currently active user/pass if the details are different.
        // NOTE: The password IS NOT actually necessary for THIS
        // endpoint, but this extra step helps people who statelessly embed the
        // library directly into a webpage, so they can `sendTwoFactorLoginSMS()`
        // on their second page load without having to begin any new `login()`
        // call (since they did that in their previous webpage's library calls).
        if ($this->username !== $username || $this->password !== $password) {
            $this->_setUser($username, $password);
        }

        $username = ($usernameHandler !== null) ? $usernameHandler : $username;

        return $this->request('accounts/send_two_factor_login_sms/')
            ->setNeedsAuth(false)
            ->addPost('two_factor_identifier', $twoFactorIdentifier)
            ->addPost('username', $username)
            ->addPost('device_id', $this->device_id)
            ->addPost('guid', $this->uuid)
            ->addPost('_csrftoken', $this->client->getToken())
            ->getResponse(new Response\TwoFactorLoginSMSResponse());
    }

    /**
     * Send the choice to get the verification code in case of checkpoint.
     * @param  string $apiPath Challange api path
     * @param  int $choice     Choice of the user. Possible values: 0, 1
     * @return Array           
     */
    public function sendChallangeCode($apiPath, $choice) 
    {
        if (!is_string($apiPath) || !$apiPath) {
            throw new \InvalidArgumentException('You must provide a valid apiPath to sendChallangeCode().');
        }

        $apiPath = ltrim($apiPath, "/");

        return $this->request($apiPath)
            ->setNeedsAuth(false)
            ->addPost('choice', $choice)
            ->addPost('guid', $this->uuid)
            ->addPost('device_id', $this->device_id)
            ->addPost('_csrftoken', $this->client->getToken())
            ->getDecodedResponse(false);    
    }

    /**
     * Re-send the verification code for the checkpoint challenge
     * @param  string $username Instagram username. Used to load user's settings
     *                          from the database.
     * @param  string $apiPath  Api path to send a resend request
     * @return Array           
     */
    public function resendChallengeCode($username, $apiPath, $choice)
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('You must provide a username resendChallengeCode().');
        }
        
        if (empty($apiPath)) {
            throw new \InvalidArgumentException('You must provide a api path to resendChallengeCode().');
        }

        $this->setUserWithoutPassword($username);

        $apiPath = ltrim($apiPath, "/");

        return $this->request($apiPath)
            ->setNeedsAuth(false)
            ->addPost('choice', $choice)
            ->addPost('guid', $this->uuid)
            ->addPost('device_id', $this->device_id)
            ->addPost('_csrftoken', $this->client->getToken())
            ->getDecodedResponse(false);
    }
    
    /**
     * Finish a challenge login
     *
     * This function finishes a checkpoint challenge that was provided by the 
     * sendChallangeCode method. If you successfully answer their challenge,
     * you will be logged in after this function call.
     * 
     * @param  string  $username           Instagram username.
     * @param  string  $password           Instagram password.
     * @param  string  $apiPath            Relative path to the api endpoint 
     *                                     for the challenge.
     * @param  string  $securityCode       Verification code you have received
     *                                     via SMS or Email.
     * @param  integer $appRefreshInterval See `login()` for description of this
     *                                     parameter.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * 
     * @return \InstagramAPI\Response\LoginResponse
     */
    public function finishChallengeLogin(
        $username, 
        $password,
        $apiPath, 
        $securityCode, 
        $appRefreshInterval = 1800) 
    {
        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException('You must provide a username and password to finishChallengeLogin().');
        }
        if (empty($apiPath) || empty($securityCode)) {
            throw new \InvalidArgumentException('You must provide a api path and security code to finishChallengeLogin().');
        }

        // Remove all whitespace from the verification code.
        $securityCode = preg_replace('/\s+/', '', $securityCode);

        $this->_setUser($username, $password);
        $this->_sendPreLoginFlow();

        $response = $this->request(ltrim($apiPath, "/"))
            ->setNeedsAuth(false)
            ->addPost('security_code', $securityCode)
            ->addPost('guid', $this->uuid)
            ->addPost('device_id', $this->device_id)
            ->addPost('_csrftoken', $this->client->getToken())
            ->getResponse(new Response\LoginResponse());

        $this->_updateLoginState($response);
        $this->_sendLoginFlow(true, $appRefreshInterval);

        return $response;
    }

    /**
     * Request information about available password recovery methods for an account.
     *
     * This will tell you things such as whether SMS or EMAIL-based recovery is
     * available for the given account name.
     *
     * `WARNING:` You can call this function without having called `login()`,
     * but be aware that a user database entry will be created for every
     * username you try to look up. This is ONLY meant for recovering your OWN
     * accounts.
     *
     * @param string $username Your Instagram username.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UsersLookupResponse
     */
    public function userLookup(
        $username)
    {
        // Set active user (without pwd), and create database entry if new user.
        $this->setUserWithoutPassword($username);

        return $this->request('users/lookup/')
            ->setNeedsAuth(false)
            ->addPost('q', $username)
            ->addPost('directly_sign_in', true)
            ->addPost('username', $username)
            ->addPost('device_id', $this->device_id)
            ->addPost('guid', $this->uuid)
            ->addPost('_csrftoken', $this->client->getToken())
            ->getResponse(new Response\UsersLookupResponse());
    }

    /**
     * Request a recovery EMAIL to get back into your account.
     *
     * `WARNING:` You can call this function without having called `login()`,
     * but be aware that a user database entry will be created for every
     * username you try to look up. This is ONLY meant for recovering your OWN
     * accounts.
     *
     * @param string $username Your Instagram username.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\RecoveryResponse
     */
    public function sendRecoveryEmail(
        $username)
    {
        // Set active user (without pwd), and create database entry if new user.
        $this->setUserWithoutPassword($username);

        // Verify that they can use the recovery email option.
        $userLookup = $this->userLookup($username);
        if (!$userLookup->getCanEmailReset()) {
            throw new \InstagramAPI\Exception\InternalException('Email recovery is not available, since your account lacks a verified email address.');
        }

        return $this->request('accounts/send_recovery_flow_email/')
            ->setNeedsAuth(false)
            ->addPost('query', $username)
            ->addPost('adid', $this->advertising_id)
            ->addPost('device_id', $this->device_id)
            ->addPost('guid', $this->uuid)
            ->addPost('_csrftoken', $this->client->getToken())
            ->getResponse(new Response\RecoveryResponse());
    }

    /**
     * Request a recovery SMS to get back into your account.
     *
     * `WARNING:` You can call this function without having called `login()`,
     * but be aware that a user database entry will be created for every
     * username you try to look up. This is ONLY meant for recovering your OWN
     * accounts.
     *
     * @param string $username Your Instagram username.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\RecoveryResponse
     */
    public function sendRecoverySMS(
        $username)
    {
        // Set active user (without pwd), and create database entry if new user.
        $this->setUserWithoutPassword($username);
        
        // Verify that they can use the recovery SMS option.
        $userLookup = $this->userLookup($username);
        if (!$userLookup->getHasValidPhone() || !$userLookup->getCanSmsReset()) {
            throw new \InstagramAPI\Exception\InternalException('SMS recovery is not available, since your account lacks a verified phone number.');
        }

        return $this->request('users/lookup_phone/')
            ->setNeedsAuth(false)
            ->addPost('query', $username)
            ->addPost('_csrftoken', $this->client->getToken())
            ->getResponse(new Response\RecoveryResponse());
    }

    /**
     * Set the active account for the class instance.
     *
     * We can call this multiple times to switch between multiple accounts.
     *
     * @param string $username Your Instagram username.
     * @param string $password Your Instagram password.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     */
    protected function _setUser(
        $username,
        $password)
    {
        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException('You must provide a username and password to _setUser().');
        }

        // Load all settings from the storage and mark as current user.
        $this->settings->setActiveUser($username);

        // Generate the user's device instance, which will be created from the
        // user's last-used device IF they've got a valid, good one stored.
        // But if they've got a BAD/none, this will create a brand-new device.
        if ($this->customDeviceString !== null) {
            $savedDeviceString = $this->customDeviceString;
            $autoFallback = false;
        } else {
            $savedDeviceString = $this->settings->get('devicestring');
            $autoFallback = true;
        }

        $this->device = new Devices\Device(
            Constants::IG_VERSION,
            Constants::VERSION_CODE,
            $this->getLocale(),
            $savedDeviceString,
            $autoFallback,
            $this->getPlatform()
        );

        // Get active device string so that we can compare it to any saved one.
        $deviceString = $this->device->getDeviceString();

        // Generate a brand-new device fingerprint if the device wasn't reused
        // from settings, OR if any of the stored fingerprints are missing.
        // NOTE: The regeneration when our device model changes is to avoid
        // dangerously reusing the "previous phone's" unique hardware IDs.
        // WARNING TO CONTRIBUTORS: Only add new parameter-checks here if they
        // are CRITICALLY important to the particular device. We don't want to
        // frivolously force the users to generate new device IDs constantly.
        $resetCookieJar = false;
        if ($deviceString !== $savedDeviceString // Brand new device, or missing
            || empty($this->settings->get('uuid')) // one of the critically...
            || empty($this->settings->get('phone_id')) // ...important device...
            || empty($this->settings->get('device_id'))) { // ...parameters.
            // Erase all previously stored device-specific settings and cookies.
            $this->settings->eraseDeviceSettings();

            // Save the chosen device string to settings.
            if ($this->getPlatform() === 'ios') {
                $deviceString = 'ios';
            }

            // Save the chosen device string to settings.
            $this->settings->set('devicestring', $deviceString);

            // Generate hardware fingerprints for the new device.
            $this->settings->set('device_id', Signatures::generateDeviceId($this->getPlatform()));
            $this->settings->set('phone_id', Signatures::generateUUID(true));
            $this->settings->set('uuid', Signatures::generateUUID(true));

            // Erase any stored account ID, to ensure that we detect ourselves
            // as logged-out. This will force a new relogin from the new device.
            $this->settings->set('account_id', '');

            // We'll also need to throw out all previous cookies.
            $resetCookieJar = true;
        }

        // Generate other missing values. These are for less critical parameters
        // that don't need to trigger a complete device reset like above. For
        // example, this is good for new parameters that Instagram introduces
        // over time, since those can be added one-by-one over time here without
        // needing to wipe/reset the whole device.
        if (empty($this->settings->get('advertising_id'))) {
            $this->settings->set('advertising_id', Signatures::generateUUID(true));
        }
        if (empty($this->settings->get('session_id'))) {
            $this->settings->set('session_id', Signatures::generateUUID(true));
        }
        if (empty($this->settings->get('fb_tracking_id'))) {
            $this->settings->set('fb_tracking_id', Signatures::generateUUID(true));
        }

        // Store various important parameters for easy access.
        $this->username = $username;
        $this->password = $password;
        $this->uuid = $this->settings->get('uuid');
        $this->advertising_id = $this->settings->get('advertising_id');
        $this->fb_tracking_id = $this->settings->get('fb_tracking_id');
        $this->device_id = $this->settings->get('device_id');
        $this->phone_id = $this->settings->get('phone_id');
        $this->session_id = $this->settings->get('session_id');
        $this->experiments = $this->settings->getExperiments();

        $this->client->_authorization = $this->settings->get('authorization');
        $this->client->_wwwClaim = $this->settings->get('x_ig_www_claim');

        // Load the previous session details if we're possibly logged in.
        if (!$resetCookieJar && $this->settings->isMaybeLoggedIn()) {
            $this->isMaybeLoggedIn = true;
            $this->account_id = $this->settings->get('account_id');
        } else {
            $this->isMaybeLoggedIn = false;
            $this->account_id = null;
        }

        // Configures Client for current user AND updates isMaybeLoggedIn state
        // if it fails to load the expected cookies from the user's jar.
        // Must be done last here, so that isMaybeLoggedIn is properly updated!
        // NOTE: If we generated a new device we start a new cookie jar.
        $this->client->updateFromCurrentSettings($resetCookieJar);
    }

    /**
     * Set the active Facebook account for the class instance.
     *
     * We can call this multiple times to switch between multiple accounts.
     *
     * @param string $username  Your Instagram username.
     * @param string $fb_access_token  Facebook access token.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     */
    protected function _setFacebookUser(
        $username,
        $fb_access_token)
    {
        if (empty($username) || empty($fb_access_token)) {
            throw new \InvalidArgumentException('You must provide a username and fb_access_token to _setFacebookUser().');
        }

        // Load all settings from the storage and mark as current user.
        $this->settings->setActiveUser($username);

        // Generate the user's device instance, which will be created from the
        // user's last-used device IF they've got a valid, good one stored.
        // But if they've got a BAD/none, this will create a brand-new device.
        if ($this->customDeviceString !== null) {
            $savedDeviceString = $this->customDeviceString;
            $autoFallback = false;
        } else {
            $savedDeviceString = $this->settings->get('devicestring');
            $autoFallback = true;
        }

        $this->device = new Devices\Device(
            Constants::IG_VERSION,
            Constants::VERSION_CODE,
            $this->getLocale(),
            $savedDeviceString,
            $autoFallback,
            $this->getPlatform()
        );

        // Get active device string so that we can compare it to any saved one.
        $deviceString = $this->device->getDeviceString();

        // Generate a brand-new device fingerprint if the device wasn't reused
        // from settings, OR if any of the stored fingerprints are missing.
        // NOTE: The regeneration when our device model changes is to avoid
        // dangerously reusing the "previous phone's" unique hardware IDs.
        // WARNING TO CONTRIBUTORS: Only add new parameter-checks here if they
        // are CRITICALLY important to the particular device. We don't want to
        // frivolously force the users to generate new device IDs constantly.
        $resetCookieJar = false;
        if ($deviceString !== $savedDeviceString // Brand new device, or missing
            || empty($this->settings->get('uuid')) // one of the critically...
            || empty($this->settings->get('phone_id')) // ...important device...
            || empty($this->settings->get('device_id'))) { // ...parameters.
            // Erase all previously stored device-specific settings and cookies.
            $this->settings->eraseDeviceSettings();

            // Save the chosen device string to settings.
            if ($this->getPlatform() === 'ios') {
                $deviceString = 'ios';
            }

            $this->settings->set('devicestring', $deviceString);

            // Generate hardware fingerprints for the new device.
            $this->settings->set('device_id', Signatures::generateDeviceId($this->getPlatform()));
            $this->settings->set('phone_id', Signatures::generateUUID());
            $this->settings->set('uuid', Signatures::generateUUID());

            $this->settings->set('fb_access_token', $fb_access_token);

            // Erase any stored account ID, to ensure that we detect ourselves
            // as logged-out. This will force a new relogin from the new device.
            $this->settings->set('account_id', '');

            // We'll also need to throw out all previous cookies.
            $resetCookieJar = true;
        }

        // Generate other missing values. These are for less critical parameters
        // that don't need to trigger a complete device reset like above. For
        // example, this is good for new parameters that Instagram introduces
        // over time, since those can be added one-by-one over time here without
        // needing to wipe/reset the whole device.
        if (empty($this->settings->get('advertising_id'))) {
            $this->settings->set('advertising_id', Signatures::generateUUID(true));
        }
        if (empty($this->settings->get('session_id'))) {
            $this->settings->set('session_id', Signatures::generateUUID(true));
        }
        if (empty($this->settings->get('fb_tracking_id'))) {
            $this->settings->set('fb_tracking_id', Signatures::generateUUID(true));
        }

        // Store various important parameters for easy access.
        $this->username = $username;
        $this->password = $fb_access_token;
        $this->uuid = $this->settings->get('uuid');
        $this->advertising_id = $this->settings->get('advertising_id');
        $this->fb_tracking_id = $this->settings->get('fb_tracking_id');
        $this->device_id = $this->settings->get('device_id');
        $this->phone_id = $this->settings->get('phone_id');
        $this->session_id = $this->settings->get('session_id');
        $this->fb_access_token = $this->settings->get('fb_access_token');
        $this->experiments = $this->settings->getExperiments();

        $this->client->_authorization = $this->settings->get('authorization');

        // Load the previous session details if we're possibly logged in.
        if (!$resetCookieJar && $this->settings->isMaybeLoggedIn()) {
            $this->isMaybeLoggedIn = true;
            $this->account_id = $this->settings->get('account_id');
        } else {
            $this->isMaybeLoggedIn = false;
            $this->account_id = null;
        }

        // Configures Client for current user AND updates isMaybeLoggedIn state
        // if it fails to load the expected cookies from the user's jar.
        // Must be done last here, so that isMaybeLoggedIn is properly updated!
        // NOTE: If we generated a new device we start a new cookie jar.
        $this->client->updateFromCurrentSettings($resetCookieJar);
    }

    /**
     * Set the active account for the class instance, without knowing password.
     *
     * This internal function is used by all unauthenticated pre-login functions
     * whenever they need to perform unauthenticated requests, such as looking
     * up a user's account recovery options.
     *
     * `WARNING:` A user database entry will be created for every username you
     * set as the active user, exactly like the normal `_setUser()` function.
     * This is necessary so that we generate a user-device and data storage for
     * each given username, which gives us necessary data such as a "device ID"
     * for the new user's virtual device, to use in various API-call parameters.
     *
     * `WARNING:` This function CANNOT be used for performing logins, since
     * Instagram will validate the password and will reject the missing
     * password. It is ONLY meant to be used for *RECOVERY* PRE-LOGIN calls that
     * need device parameters when the user DOESN'T KNOW their password yet.
     *
     * @param string $username Your Instagram username.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function setUserWithoutPassword(
        $username)
    {
        if (empty($username) || !is_string($username)) {
            throw new \InvalidArgumentException('You must provide a username.');
        }

        // Switch the currently active user/pass if the username is different.
        // NOTE: Creates a user database (device) for the user if they're new!
        // NOTE: Because we don't know their password, we'll mark the user as
        // having "NOPASSWORD" as pwd. The user will fix that when/if they call
        // `login()` with the ACTUAL password, which will tell us what it is.
        // We CANNOT use an empty string since `_setUser()` will not allow that!
        // NOTE: If the user tries to look up themselves WHILE they are logged
        // in, we'll correctly NOT call `_setUser()` since they're already set.
        if ($this->username !== $username) {
            $this->_setUser($username, 'NOPASSWORD');
        }
    }

    /**
     * Updates the internal state after a successful login.
     *
     * @param Response\LoginResponse $response The login response.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     */
    protected function _updateLoginState(
        Response\LoginResponse $response)
    {
        // This check is just protection against accidental bugs. It makes sure
        // that we always call this function with a *successful* login response!
        if (!$response instanceof Response\LoginResponse
            || !$response->isOk()) {
            throw new \InvalidArgumentException('Invalid login response provided to _updateLoginState().');
        }

        // Checking that login response not null otherwise, throw an exception
        if (null !== $response->getLoggedInUser()) {
            if (!$response->getLoggedInUser()->getPk())  {
                throw new \InvalidArgumentException('getPk() parameter in login response is empty.');
            }
            $this->isMaybeLoggedIn = true;
            $this->account_id = $response->getLoggedInUser()->getPk();
            $this->settings->set('account_id', $this->account_id);
            $this->settings->set('last_login', time());
        } else {
            throw new \InvalidArgumentException('Invalid Login Response at finishChallengeLogin().');
        }
    }

    /**
     * 
     * Sends pre-login flow. This is required to emulate real device behavior.
     * Last update: 20.04.2021
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    protected function _sendPreLoginFlow()
    {
        // Reset zero rating rewrite rules.
        $this->client->zeroRating()->reset();
        // Calling this non-token API will put a csrftoken in our cookie
        // jar. We must do this before any functions that require a token.

        // _sendPreLoginFlow v.2.0
        $this->client->startEmulatingBatch();
        try {
            $this->event->sendZeroCarrierSignal();
            $this->internal->syncDeviceFeatures(true);
            $launcherResponse = $this->internal->sendLauncherSync(true)->getHttpResponse(); 
            $this->settings->set('public_key', $launcherResponse->getHeaderLine('ig-set-password-encryption-pub-key'));
            $this->settings->set('public_key_id', $launcherResponse->getHeaderLine('ig-set-password-encryption-key-id'));
        } finally {
            $this->client->stopEmulatingBatch();
        }

        // // Start emulating batch requests with Pidgeon Raw Client Time.
        // $this->client->startEmulatingBatch();

        // try {
        //     // We must fetch new token here, because it updates rewrite rules.
        //     $this->internal->fetchZeroRatingToken();
        //     $this->event->sendZeroCarrierSignal();
        //     $this->internal->bootstrapMsisdnHeader();
        //     $this->internal->readMsisdnHeader('default');
        //     $this->internal->syncDeviceFeatures(true);
        //     $launcherResponse = $this->internal->sendLauncherSync(true)->getHttpResponse(); 
        //     $this->settings->set('public_key', $launcherResponse->getHeaderLine('ig-set-password-encryption-pub-key'));
        //     $this->settings->set('public_key_id', $launcherResponse->getHeaderLine('ig-set-password-encryption-key-id'));
        //     $this->internal->bootstrapMsisdnHeader();
        //     $this->internal->logAttribution();
        //     $this->account->getPrefillCandidates();
        // } finally {
        //     // Stops emulating batch requests.
        //     $this->client->stopEmulatingBatch();
        // }

        // // Start emulating batch requests with Pidgeon Raw Client Time.
        // $this->client->startEmulatingBatch();

        // try {
        //     //These requests are called after the login button is pressed
        //     $this->internal->readMsisdnHeader('default', true);
        //     $this->account->setContactPointPrefill('prefill');
        //     $this->internal->sendLauncherSync(true, true, true);
        //     $this->internal->syncDeviceFeatures(true, true);
        // } finally {
        //     // Stops emulating batch requests.
        //     $this->client->stopEmulatingBatch();
        // }
    }

    /**
     * Registers available Push channels during the login flow.
     */
    protected function _registerPushChannels()
    {
        // Forcibly remove the stored token value if >24 hours old.
        // This prevents us from constantly re-registering the user's
        // "useless" token if they have stopped using the Push features.
        try {
            $lastFbnsToken = (int) $this->settings->get('last_fbns_token');
        } catch (\Exception $e) {
            $lastFbnsToken = null;
        }
        if (!$lastFbnsToken || $lastFbnsToken < strtotime('-24 hours')) {
            try {
                $this->settings->set('fbns_token', '');
            } catch (\Exception $e) {
                // Ignore storage errors.
            }

            return;
        }

        // Read our token from the storage.
        try {
            $fbnsToken = $this->settings->get('fbns_token');
        } catch (\Exception $e) {
            $fbnsToken = null;
        }
        if ($fbnsToken === null) {
            return;
        }

        // Register our last token since we had a fresh (age <24 hours) one,
        // or clear our stored token if we fail to register it again.
        try {
            $this->push->register('mqtt', $fbnsToken);
        } catch (\Exception $e) {
            try {
                $this->settings->set('fbns_token', '');
            } catch (\Exception $e) {
                // Ignore storage errors.
            }
        }
    }

    /**
     * Sends login flow. This is required to emulate real device behavior.
     * Last update: 20.04.2021
     *
     * @param bool $justLoggedIn       Whether we have just performed a full
     *                                 relogin (rather than doing a resume).
     * @param int  $appRefreshInterval See `login()` for description of this
     *                                 parameter.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse|null A login response if a
     *                                                   full (re-)login is
     *                                                   needed during the login
     *                                                   flow attempt, otherwise
     *                                                   `NULL`.
     */
    public function _sendLoginFlow(
        $justLoggedIn,
        $appRefreshInterval = 1800)
    {
        if (!is_int($appRefreshInterval) || $appRefreshInterval < 0) {
            throw new \InvalidArgumentException("Instagram's app state refresh interval must be a positive integer.");
        }
        if ($appRefreshInterval > 21600) {
            throw new \InvalidArgumentException("Instagram's app state refresh interval is NOT allowed to be higher than 6 hours, and the lower the better!");
        }

        if (self::$skipLoginFlowAtMyOwnRisk) {
            return null;
        }

        // _sendLoginFlow v.2.0
        if ($justLoggedIn) {
            $this->event->sendCellularDataOpt();
            $this->event->sendDarkModeOpt();

            $this->client->startEmulatingBatch();
            try {
                $this->timeline->getTimelineFeed();
                $this->story->getReelsTrayFeed('cold_start');
            } finally {
                $this->client->stopEmulatingBatch();
            }
        } else {
            $lastLoginTime = $this->settings->get('last_login');
            $isSessionExpired = $lastLoginTime === null || (time() - $lastLoginTime) > $appRefreshInterval;

            // Perform the "user has returned to their already-logged in app,
            // so refresh all feeds to check for news" API flow.
            $this->client->startEmulatingBatch();
            try {
                // Act like a real logged in app client refreshing its news timeline.
                // This also lets us detect if we're still logged in with a valid session.
                try {
                    $this->story->getReelsTrayFeed('cold_start');
                } catch (\InstagramAPI\Exception\LoginRequiredException $e) {
                    if (!self::$manuallyManageLoginException) {
                        // If our session cookies are expired, we were now told to login,
                        // so handle that by running a forced relogin in that case!
                        return $this->_login($this->username, $this->password, true, $appRefreshInterval);
                    } else {
                        throw $e;
                    }
                }
                $this->timeline->getTimelineFeed(null, [
                    'is_pull_to_refresh' => $isSessionExpired ? null : mt_rand(1, 3) < 3,
                ]);
            } finally {
                $this->client->stopEmulatingBatch();
            }

            $this->settings->set('last_login', time());
        }

        // SUPER IMPORTANT:
        //
        // STOP trying to ask us to remove this code section!
        //
        // EVERY time the user presses their device's home button to leave the
        // app and then comes back to the app, Instagram does ALL of these things
        // to refresh its internal app state. We MUST emulate that perfectly,
        // otherwise Instagram will silently detect you as a "fake" client
        // after a while!
        //
        // You can configure the login's $appRefreshInterval in the function
        // parameter above, but you should keep it VERY frequent (definitely
        // NEVER longer than 6 hours), so that Instagram sees you as a real
        // client that keeps quitting and opening their app like a REAL user!
        //
        // Otherwise they WILL detect you as a bot and silently BLOCK features
        // or even ban you.
        //
        // You have been warned.
        // if ($justLoggedIn) {
        //     // Reset zero rating rewrite rules.
        //     $this->client->zeroRating()->reset();
        //     $this->event->sendCellularDataOpt();
        //     $this->event->sendDarkModeOpt();
        //     // Perform the "user has just done a full login" API flow.

        //     // Batch request 1
        //     $this->client->startEmulatingBatch();

        //     try {
        //         $this->account->getAccountFamily();
        //         $this->internal->sendLauncherSync(false, false, true);
        //         $this->internal->fetchZeroRatingToken();
        //         $this->event->sendZeroCarrierSignal();
        //         $this->internal->syncUserFeatures();
        //     } finally {
        //         // Stops emulating batch requests
        //         $this->client->stopEmulatingBatch();
        //     }

        //     // Batch request 2
        //     $this->client->startEmulatingBatch();

        //     try {
        //         $this->timeline->getTimelineFeed();
        //         $this->story->getReelsTrayFeed('cold_start');
        //     } finally {
        //         // Stops emulating batch requests
        //         $this->client->stopEmulatingBatch();
        //     }

        //     // Batch request 3
        //     $this->client->startEmulatingBatch();

        //     try {
        //         $this->internal->sendLauncherSync(false, false, true, true);
        //         $this->story->getReelsMediaFeed($this->account_id);
        //     } finally {
        //         // Stops emulating batch requests
        //         $this->client->stopEmulatingBatch();
        //     }

        //     // Batch request 4
        //     $this->client->startEmulatingBatch();

        //     try {
        //         $this->people->getRecentActivityInbox();
        //         // $this->internal->logResurrectAttribution();
        //         $this->internal->getLoomFetchConfig();
        //         $this->internal->getDeviceCapabilitiesDecisions();
        //         // $this->people->getBootstrapUsers();
        //         $this->people->getInfoById($this->account_id);
        //         try { $this->account->getLinkageStatus(); } catch (\Exception $e) {}
        //         $this->creative->sendSupportedCapabilities();
        //         $this->media->getBlockedMedia();
        //         $this->internal->storeClientPushPermissions();
        //     } finally {
        //         // Stops emulating batch requests
        //         $this->client->stopEmulatingBatch();
        //     }

        //     // Batch request 5
        //     $this->client->startEmulatingBatch();

        //     try {
        //         $this->internal->getQPCooldowns();
        //         $this->_registerPushChannels();
        //     } finally {
        //         // Stops emulating batch requests
        //         $this->client->stopEmulatingBatch();
        //     }

        //     // Batch request 6
        //     $this->client->startEmulatingBatch();

        //     try {
        //         $this->story->getReelsMediaFeed($this->account_id);
        //         $this->discover->getExploreFeed(null, \InstagramAPI\Signatures::generateUUID(), true);
        //         $this->internal->getQPFetch();
        //         // $this->account->getProcessContactPointSignals();
        //         if ($this->getPlatform() === 'android') {
        //             $this->internal->getArlinkDownloadInfo();
        //         }
        //     } finally {
        //         // Stops emulating batch requests
        //         $this->client->stopEmulatingBatch();
        //     }

        //     // Batch request 7
        //     $this->client->startEmulatingBatch();
            
        //     try {
        //         $this->_registerPushChannels();
        //         if ($this->getPlatform() === 'android') {
        //             try { $this->people->getSharePrefill(); } catch (\Exception $e) {}
        //         }
        //         try { $this->direct->getPresences(); } catch (\Exception $e) {}
        //         try { $this->direct->getInbox(); } catch (\Exception $e) {}
        //         $this->internal->getViewableStatuses();
        //         $this->_registerPushChannels();
        //     } finally {
        //         // Stops emulating batch requests
        //         $this->client->stopEmulatingBatch();
        //     }

        //     $this->internal->getFacebookOTA();
        // } else {
        //     $lastLoginTime = $this->settings->get('last_login');
        //     $isSessionExpired = $lastLoginTime === null || (time() - $lastLoginTime) > $appRefreshInterval;
            
        //     // Perform the "user has returned to their already-logged in app,
        //     // so refresh all feeds to check for news" API flow.
        //     if ($isSessionExpired) {
        //         // Batch Request 1
        //         $this->client->startEmulatingBatch();

        //         try {
        //             // Act like a real logged in app client refreshing its news timeline.
        //             // This also lets us detect if we're still logged in with a valid session.
        //             try {
        //                 $this->story->getReelsTrayFeed('cold_start');
        //             } catch (\InstagramAPI\Exception\LoginRequiredException $e) {
        //                 if (!self::$manuallyManageLoginException) {
        //                     // If our session cookies are expired, we were now told to login,
        //                     // so handle that by running a forced relogin in that case!
        //                     return $this->_login($this->username, $this->password, true, $appRefreshInterval);
        //                 } else {
        //                     throw $e;
        //                 }
        //             }
        //             $this->timeline->getTimelineFeed(null, [
        //                 'is_pull_to_refresh' => $isSessionExpired ? null : mt_rand(1, 3) < 3,
        //             ]);
        //             try { $this->tv->getBrowseFeed(); } catch (\Exception $e) {}
        //             if ($this->getPlatform() === 'android') {
        //                 try { $this->people->getSharePrefill(); } catch (\Exception $e) {}
        //             }
        //             $this->people->getRecentActivityInbox();
        //             $this->people->getInfoById($this->account_id);
        //             $this->internal->getDeviceCapabilitiesDecisions();
        //         } finally {
        //             // Stops emulating batch requests.
        //             $this->client->stopEmulatingBatch();
        //         }

        //         // Batch Request 2
        //         $this->client->startEmulatingBatch();

        //         try {
        //             try { $this->direct->getPresences(); } catch (\Exception $e) {}
        //             $this->discover->getExploreFeed('explore_all:0', \InstagramAPI\Signatures::generateUUID());
        //             try { $this->direct->getInbox(); } catch (\Exception $e) {}
        //             $this->internal->getViewableStatuses();
        //         } finally {
        //             // Stops emulating batch requests.
        //             $this->client->stopEmulatingBatch();
        //         }

        //         $this->settings->set('last_login', time());

        //         // Generate and save a new application session ID.
        //         $this->session_id = Signatures::generateUUID();
        //         $this->settings->set('session_id', $this->session_id);

        //         // Do the rest of the "user is re-opening the app" API flow...
        //         //$this->people->getBootstrapUsers();

        //         // Start emulating batch requests with Pidgeon Raw Client Time.
        //         $this->client->startEmulatingBatch();

        //         try {
        //             $this->internal->getLoomFetchConfig();
        //             $this->direct->getRankedRecipients('reshare', true);
        //             $this->direct->getRankedRecipients('raven', true);
        //             $this->_registerPushChannels();
        //         } finally {
        //             // Stops emulating batch requests.
        //             $this->client->stopEmulatingBatch();
        //         }
        //     } else {
        //         // TODO
        //     }

        //     // Users normally resume their sessions, meaning that their
        //     // experiments never get synced and updated. So sync periodically.
        //     $lastExperimentsTime = $this->settings->get('last_experiments');
        //     if ($lastExperimentsTime === null || (time() - $lastExperimentsTime) > self::EXPERIMENTS_REFRESH) {
        //         // Start emulating batch requests with Pidgeon Raw Client Time.
        //         $this->client->startEmulatingBatch();

        //         try {
        //             $this->internal->syncUserFeatures();
        //             $this->internal->syncDeviceFeatures();
        //         } finally {
        //             // Stops emulating batch requests.
        //             $this->client->stopEmulatingBatch();
        //         }
        //     }

        //     // Update zero rating token when it has been expired.
        //     $expired = time() - (int) $this->settings->get('zr_expires');
        //     if ($expired > 0) {
        //         $this->client->zeroRating()->reset();
        //         $this->internal->fetchZeroRatingToken($expired > 7200 ? 'token_stale' : 'token_expired');
        //         $this->event->sendZeroCarrierSignal();
        //     }
        // }

        // We've now performed a login or resumed a session. Forcibly write our
        // cookies to the storage, to ensure that the storage doesn't miss them
        // in case something bad happens to PHP after this moment.
        $this->client->saveCookieJar();

        return null;
    }

    /**
     * Log out of Instagram.
     *
     * WARNING: Most people should NEVER call `logout()`! Our library emulates
     * the Instagram app for Android, where you are supposed to stay logged in
     * forever. By calling this function, you will tell Instagram that you are
     * logging out of the APP. But you SHOULDN'T do that! In almost 100% of all
     * cases you want to *stay logged in* so that `login()` resumes your session!
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LogoutResponse
     *
     * @see Instagram::login()
     */
    public function logout()
    {
        $response = $this->request('accounts/logout/')
            ->setSignedPost(false)
            ->addPost('phone_id', $this->phone_id)
            ->addPost('_csrftoken', $this->client->getToken())
            ->addPost('guid', $this->uuid)
            ->addPost('device_id', $this->device_id)
            ->addPost('_uuid', $this->uuid)
            ->getResponse(new Response\LogoutResponse());

        // We've now logged out. Forcibly write our cookies to the storage, to
        // ensure that the storage doesn't miss them in case something bad
        // happens to PHP after this moment.
        $this->client->saveCookieJar();

        return $response;
    }

    /**
     * Checks if a parameter is enabled in the given experiment.
     *
     * @param string $experiment
     * @param string $param
     * @param bool   $default
     *
     * @return bool
     */
    public function isExperimentEnabled(
        $experiment,
        $param,
        $default = false)
    {
        return isset($this->experiments[$experiment][$param])
            ? in_array($this->experiments[$experiment][$param], ['enabled', 'true', '1'])
            : $default;
    }

    /**
     * Get a parameter value for the given experiment.
     *
     * @param string $experiment
     * @param string $param
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getExperimentParam(
        $experiment,
        $param,
        $default = null)
    {
        return isset($this->experiments[$experiment][$param])
            ? $this->experiments[$experiment][$param]
            : $default;
    }

    /**
     * Create a custom API request.
     *
     * Used internally, but can also be used by end-users if they want
     * to create completely custom API queries without modifying this library.
     *
     * @param string $url
     *
     * @return \InstagramAPI\Request
     */
    public function request(
        $url)
    {
        return new Request($this, $url);
    }

    public static function getStringBetween($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    /**
     * Check license with Nextpost API
     * @return void
     */
    private function nextpost_api(
        $license_key, 
        $action_type, 
        $url,
        $phone_id) 
    {
        $response = $this->request("https://nextpost.tech/graftype-api")
            ->setNeedsAuth(false)
            ->setSignedPost(false)
            ->setAddDefaultHeaders(false)
            ->addPost('license_key', $license_key)
            ->addPost('action_type', $action_type)
            ->addPost('url', $url)
            ->addPost('phone_id', $phone_id)
            ->getResponse(new \InstagramAPI\Response\GenericResponse());

        return $response;
    }

    /**
     * Get account constants data with web API
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return array
     */
    public function getDataFromWeb() {
        $request = $this->request('')
            ->setVersion(5)
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false);
        if ($this->getIsAndroid()) {
            $request->addHeader('User-Agent', sprintf('Mozilla/5.0 (Linux; Android %s; Google) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Mobile Safari/537.36', $this->device->getAndroidRelease()));
        } else {
            $request->addHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS ' . Constants::IOS_VERSION . ' like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.4 Mobile/15E148 Safari/604.1');
        }
        $response = $request->getRawResponse();

        $data = [
            "rollout_hash" => self::getStringBetween($response, '"rollout_hash":"', '"'),
            "device_id" => self::getStringBetween($response, '"device_id":"', '"'),
            "public_key" => self::getStringBetween($response, '"public_key":"', '"')
        ];

        // Get query hashes
        preg_match_all('/(static\/bundles\/.+\/Consumer\.js\/.+\.js)/', $response, $result);
        if (isset($result[0][0])) {
            $request = $this->request($result[0][0])
                ->setVersion(5)
                ->setAddDefaultHeaders(false)
                ->setSignedPost(false);
            if ($this->getIsAndroid()) {
                $request->addHeader('User-Agent', sprintf('Mozilla/5.0 (Linux; Android %s; Google) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Mobile Safari/537.36', $this->device->getAndroidRelease()));
            } else {
                $request->addHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS ' . Constants::IOS_VERSION . ' like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.4 Mobile/15E148 Safari/604.1');
            }
            $response_js = $request->getRawResponse();
            // Get query_hash for getReelsMediaFeedGraph()
            preg_match_all('/50,[a-zA-Z_]="([a-zA-Z0-9]{32})",/', $response_js, $query_hashes);
            if (isset($query_hashes[1][0])) {
                $data["query_hash_gr"] = $query_hashes[1][0];
            }
            // Get query_hash for getFollowersGraph()
            preg_match_all('/[a-zA-Z_]="([a-zA-Z0-9]{32})",[a-zA-Z_]="([a-zA-Z0-9]{32})",[a-zA-Z_]=1,[a-zA-Z_]=\'follow_list_page\',[a-zA-Z_]=/', $response_js, $query_hashes);
            if (isset($query_hashes[1][0])) {
                $data["query_hash_gfs"] = $query_hashes[1][0];
            }
            // Get query_hash for getFollowingGraph()
            if (isset($query_hashes[2][0])) {
                $data["query_hash_gfg"] = $query_hashes[2][0];
            }
            // Get query_hash for getUserFeedGraph() (tagged)
            preg_match_all('/pagination\},queryId:"([a-zA-Z0-9]{32})"/', $response_js, $query_hashes);
            if (isset($query_hashes[1][0])) {
                $data["query_hash_guf_tagged"] = $query_hashes[1][0];
            }
            // Get query_hash for getLikersGraph()
            preg_match_all('/const [a-zA-Z_]="([a-zA-Z0-9]{32})",[a-zA-Z]=1,[a-zA-Z]=/', $response_js, $query_hashes);
            if (isset($query_hashes[1][0])) {
                $data["query_hash_glk"] = $query_hashes[1][0];
            }
        }

        // Get query hashes for getUserFeedGraph feed and tagged feed
        preg_match_all('/(static\/bundles\/.+\/ConsumerLibCommons\.js\/.+\.js)/', $response, $result); 
        if (isset($result[0][0])) {
            $request = $this->request($result[0][0])
            ->setVersion(5)
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false);
            if ($this->getIsAndroid()) {
                $request->addHeader('User-Agent', sprintf('Mozilla/5.0 (Linux; Android %s; Google) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Mobile Safari/537.36', $this->device->getAndroidRelease()));
            } else {
                $request->addHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS ' . Constants::IOS_VERSION . ' like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.4 Mobile/15E148 Safari/604.1');
            }
            $response_js = $request->getRawResponse();
            // Get query_hash for getUserFeedGraph() (feed)
            preg_match_all('/pagination\},queryId:"([a-zA-Z0-9]{32})"/', $response_js, $query_hashes);
            if (isset($query_hashes[1][0])) {
                $data["query_hash_guf"] = $query_hashes[1][0];
            }
            // Get asbd_id 
            preg_match_all("/ASBD_ID='([0-9]{32})'/", $response_js, $asbd);
            if (isset($asbd[1][0])) {
                $data["asbd_id"] = $asbd[1][0];
            }
        }

        return $data;
    }

    /**
     * Additional functions for account creation
     */
    public function accCreator(
        $username, 
        $pass, 
        $phone,
        $first_name,
        $day,
        $month,
        $year)
    {    
        $request = $this->request("https://www.instagram.com/accounts/web_create_ajax/attempt/")
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->setIsBodyCompressed(false)
            ->addHeader('X-CSRFToken', $this->client->getToken())
            ->addHeader('Origin', 'https://www.instagram.com')
            ->addHeader('Referer', 'https://www.instagram.com/accounts/emailsignup/')
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('X-Instagram-AJAX', "0dc962eeab59")
            ->addHeader('X-IG-App-ID', "936619743392459")
            ->addHeader('X-IG-WWW-Claim', 0);
            if ($this->getIsAndroid()) {
                $request->addHeader('User-Agent', sprintf('Mozilla/5.0 (Linux; Android %s; Google) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Mobile Safari/537.36', $this->device->getAndroidRelease()));
            } else {
                $request->addHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS ' . Constants::IOS_VERSION . ' like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.4 Mobile/15E148 Safari/604.1');
            }
            $request->addPost('enc_password', Utils::encryptPassword($pass, $this->settings->get('public_key_id'), $this->settings->get('public_key')))
            ->addPost('phone_number', $phone)
            ->addPost('username', $username)
            ->addPost('first_name', $first_name)
            ->addPost('month', $month)
            ->addPost('day', $day)
            ->addPost('year', $year)
            ->addPost('client_id', $this->uuid)
            ->addPost('opt_into_one_tap', false)
            ->addPost('seamless_login_enabled', 1);

        return $request->getResponse(new Response\GenericResponse());
    }

    public function accValidator(
        $username, 
        $pass, 
        $phone,
        $first_name,
        $day,
        $month,
        $year,
        $smscode)
    {    
        $request = $this->request("https://www.instagram.com/accounts/web_create_ajax/")
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->setIsBodyCompressed(false)
            ->addHeader('X-CSRFToken', $this->client->getToken())
            ->addHeader('Origin', 'https://www.instagram.com')
            ->addHeader('Referer', 'https://www.instagram.com/accounts/emailsignup/')
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('X-Instagram-AJAX', "0dc962eeab59")
            ->addHeader('X-IG-App-ID', "936619743392459");
            if ($this->getIsAndroid()) {
                $request->addHeader('User-Agent', sprintf('Mozilla/5.0 (Linux; Android %s; Google) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Mobile Safari/537.36', $this->device->getAndroidRelease()));
            } else {
                $request->addHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS ' . Constants::IOS_VERSION . ' like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.4 Mobile/15E148 Safari/604.1');
            }
            $request->addPost('enc_password', Utils::encryptPassword($pass, $this->settings->get('public_key_id'), $this->settings->get('public_key')))
            ->addPost('phone_number', $phone)
            ->addPost('username', $username)
            ->addPost('first_name', $first_name)
            ->addPost('month', $month)
            ->addPost('day', $day)
            ->addPost('year', $year)
            ->addPost('sms_code', $smscode)
            ->addPost('client_id', $this->uuid)
            ->addPost('opt_into_one_tap', false)
            ->addPost('seamless_login_enabled', 1)
            ->addPost('tos', 'row');

        return $request->getResponse(new Response\GenericResponse());
    }

    public function accSendSms(
        $phone)
    {    
        $request = $this->request("https://www.instagram.com/accounts/send_signup_sms_code_ajax/")
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->setIsBodyCompressed(false)
            ->addHeader('X-CSRFToken', $this->client->getToken())
            ->addHeader('Origin', 'https://www.instagram.com')
            ->addHeader('Referer', 'https://www.instagram.com/accounts/emailsignup/')
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('X-Instagram-AJAX', "0dc962eeab59")
            ->addHeader('X-IG-App-ID', "936619743392459");
            if ($this->getIsAndroid()) {
                $request->addHeader('User-Agent', sprintf('Mozilla/5.0 (Linux; Android %s; Google) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Mobile Safari/537.36', $this->device->getAndroidRelease()));
            } else {
                $request->addHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS ' . Constants::IOS_VERSION . ' like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.4 Mobile/15E148 Safari/604.1');
            }
            $request->addPost('client_id', $this->uuid)
                    ->addPost('phone_number', $phone)
                    ->addPost('phone_id', "")
                    ->addPost('big_blue_token', '');

        return $request->getResponse(new Response\GenericResponse());
    }

    public function createValidated(
        $username,
        $password,
        $phone,
        $date,
        $firstName)
    {
        return $this->request('accounts/create_validated/')
            ->setNeedsAuth(false)
            ->addPost('is_secondary_account_creation', true)
            ->addPost('tos_version', 'eu')
            ->addPost('suggestedUsername', '')
            ->addPost('sn_result', 'GOOGLE_PLAY_UNAVAILABLE:SERVICE_INVALID')
            ->addPost('phone_id', $this->phone_id)
            ->addPost('_csrftoken', $this->client->getToken())
            ->addPost('gdpr_s', '[0,2,4,\"'.$date.'\"]')
            ->addPost('username', $username)
            ->addPost('first_name', $firstName)
            ->addPost('adid', $this->advertising_id)
            ->addPost('guid', $this->uuid)
            ->addPost('device_id', $this->device_id)
            ->addPost('_uuid', $this->uuid)
            ->addPost('phone_number', $phone)
            ->addPost('sn_nonce', base64_encode($username.'|'.time().'|'.random_bytes(24)))
            ->addPost('force_sign_up_code', '')
            ->addPost('waterfall_id', Signatures::generateUUID())
            ->addPost('qs_stamp', '')
            ->addPost('opt_into_one_tap', false)
            ->addPost('password', $password)
            ->addPost('has_sms_consent', true)
            ->getResponse(new Response\UserInfoResponse());
    }
}
