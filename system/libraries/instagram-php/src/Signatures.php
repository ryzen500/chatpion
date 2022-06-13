<?php

namespace InstagramAPI;

use Ramsey\Uuid\Uuid;

class Signatures
{

    /**
     * Generate a keyed hash value using the HMAC method.
     *
     * @param string $data
     * @param string $platform Platform to be used for requests.
     *
     * @return string
     */
    public static function generateSignature(
        $data,
        $platform = 'android')
    {
        if ($platform === 'android') { 
            return hash_hmac('sha256', $data, Constants::IG_SIG_KEY);
        } else {
            return hash_hmac('sha256', $data, Constants::IOS_IG_SIG_KEY);
        }
    }

    /**
     * @deprecated Use signData() instead. Only for Android.
     *
     * @param string $data
     *
     * @return string
     */
    public static function generateSignatureForPost(
        $data)
    {
        return 'ig_sig_key_version='.Constants::SIG_KEY_VERSION.'&signed_body='.self::generateSignature($data).'.'.urlencode($data);
    }

    /**
     * Generate signed array.
     *
     * @param array    $data
     * @param string[] $exclude
     * @param string   $platform Platform to be used for requests.
     *
     * @return array
     */
    public static function signData(
        array $data,
        array $exclude = [],
        $platform = 'android')
    {
        $result = [];
        // Exclude some params from signed body.
        foreach ($exclude as $key) {
            if (isset($data[$key])) {
                $result[$key] = $data[$key];
                unset($data[$key]);
            }
        }
        // Typecast all scalar values to string.
        foreach ($data as &$value) {
            if (is_scalar($value)) {
                $value = (string) $value;
            }
        }
        unset($value); // Clear reference.
        // Reorder and convert data to JSON string.
        $data = json_encode((object) Utils::reorderByHashCode($data), JSON_PRESERVE_ZERO_FRACTION);
        // Sign data.
        if ($platform === 'android') {
            $keyVersion = Constants::SIG_KEY_VERSION;
        } else {
            $keyVersion = Constants::SIG_KEY_IOS_VERSION;
        }
        $result['ig_sig_key_version'] = $keyVersion;
        $result['signed_body'] = self::generateSignature($data, $platform).'.'.$data;
        // Return value must be reordered.
        return Utils::reorderByHashCode($result);
    }

    /**
     * Generate device ID.
     *
     * @param string $platform Platform to be used for requests.
     *
     * @return string
     */
    public static function generateDeviceId(
        $platform)
    {
        if ($platform === 'android') {
            // Instagram's internal security IDs which no device is allowed to use.
            // NOTE: This list is from debugging their APK, which disallows these.
            static $securityIds = ['9774d56d682e549c', '9d1d1f0dfa440886', 'fc067667235b8f19'];

            // This has 10 million possible hash subdivisions per clock second.
            do {
                $megaRandomHash = md5(number_format(microtime(true), 7, '', ''));
            } while (in_array($megaRandomHash, $securityIds, true));

            return 'android-'.substr($megaRandomHash, 16);
        } else {
            return Uuid::uuid4();
        }
    }

    /**
     * Checks whether supplied UUID is valid or not.
     *
     * @param string $uuid UUID to check.
     *
     * @return bool
     */
    public static function isValidUUID(
        $uuid)
    {
        if (!is_string($uuid)) {
            return false;
        }

        return (bool) preg_match('#^[a-f\d]{8}-(?:[a-f\d]{4}-){3}[a-f\d]{12}$#D', $uuid);
    }
    
    public static function generateUUID(
        $keepDashes = true)
    {
        // Instagram generates the device's UUID in a special way which differs
        // from the way all other in-app UUIDs are generated. They insert a
        // predictable marker which lets them identify real UUIDs vs fake ones!
        // If we don't emulate this marker, they punish the user in many ways!
        //
        // Our algorithm has the exact same effect as the real algorithm used by
        // the real app APK. They first generate a totally random UUID v4. Then
        // they split it on "-" boundaries. Then they replace the 2nd chunk with
        // a random hex digit (0-f) followed by the 3-character result of this:
        // "hex(1635 + ((currentTimeMillis - constructionTimeMillis) / 1000))".
        //
        // Since the class is constructed almost at the exact same time that it
        // is told to generate a UUID, AND is divided by 1000, the time-stuff is
        // such a tiny number that it can be ignored (it's < 1.0 and gets
        // truncated / discarded, such as "1635.002").
        //
        // Therefore the result of the calculation is simply the hex encoding of
        // "1635" (base 10), which is "663" (base 16). So: "[1 random hex]663".
        $uuidParts = explode('-', Uuid::uuid4());
        $uuidParts[1] = $uuidParts[1][0].'663'; // Keep 1st old random hex char.
        if ($keepDashes === true) {
            $uuid = implode('-', $uuidParts);
        } else {
            $uuid = implode('', $uuidParts);
        }

        return $uuid;
    }
}
