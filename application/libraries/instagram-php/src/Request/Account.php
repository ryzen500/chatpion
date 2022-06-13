<?php

namespace InstagramAPI\Request;

use InstagramAPI\Constants;
use InstagramAPI\Exception\InternalException;
use InstagramAPI\Exception\SettingsException;
use InstagramAPI\Request\Metadata\Internal as InternalMetadata;
use InstagramAPI\Response;
use InstagramAPI\Signatures;
use InstagramAPI\Utils;

/**
 * Account-related functions, such as profile editing and security.
 */
class Account extends RequestCollection
{
    /**
     * Get login activity and suspicious login attempts.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginActivityResponse
     */
    public function getLoginActivity()
    {
        return $this->ig->request('session/login_activity/')
            ->addParam('device_id', $this->ig->device_id)
            ->getResponse(new Response\LoginActivityResponse());
    }

    /**
     * Approve (Confirm it was you) a suspicious login.
     *
     * @param string $loginId        Login ID.
     * @param string $loginTimestamp Login timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     *
     * @see getLoginActivity() for obtaining login IDs, login timestamps and suspicious logins
     */
    public function approveSuspiciousLogin(
        $loginId,
        $loginTimestamp)
    {
        return $this->ig->request('session/login_activity/avow_login/')
            ->setSignedPost(false)
            ->addPost('login_timestamp', $loginTimestamp)
            ->addPost('login_id', $loginId)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Unapprove (Confirm it was you) a suspicious login.
     *
     * @param string $loginId        Login ID.
     * @param string $loginTimestamp Login timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     *
     * @see getLoginActivity() for obtaining login IDs, login timestamps and suspicious logins
     */
    public function unapproveSuspiciousLogin(
        $loginId,
        $loginTimestamp)
    {
        return $this->ig->request('session/login_activity/undo_avow_login/')
            ->setSignedPost(false)
            ->addPost('login_timestamp', $loginTimestamp)
            ->addPost('login_id', $loginId)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Logout session at login activity
     * 
     * @param string $loginId        Login ID.
     * 
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     *
     * @see getLoginActivity() for obtaining login IDs, login timestamps and suspicious logins
     */
    public function logoutSession(
        $loginId)
    {
        return $this->ig->request('session/login_activity/logout_session/')
            ->setSignedPost(false)
            ->addPost('session_id',  $loginId)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get details about child and main IG accounts.
     *
     * @param bool $useAuth Indicates if auth is required for this request
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function getAccountFamily(
        $useAuth = true)
    {
        return $this->ig->request('multiple_accounts/get_account_family/')
            ->getResponse(new Response\MultipleAccountFamilyResponse());
    }

    /**
     * Get details about the currently logged in account.
     *
     * Also try People::getSelfInfo() instead, for some different information.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     *
     * @see People::getSelfInfo()
     */
    public function getCurrentUser()
    {
        return $this->ig->request('accounts/current_user/')
            ->addParam('edit', true)
            ->getResponse(new Response\UserInfoResponse());
    }

        /**
     * Edit your gender.
     *
     * WARNING: Remember to also call `editProfile()` *after* using this
     * function, so that you act like the real app!
     *
     * @param string $gender this can be male, female, empty or null for 'prefer not to say' or anything else for custom
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function setGender(
        $gender = '')
    {
        switch (strtolower($gender)) {
            case 'male':$gender_id = 1; break;
            case 'female':$gender_id = 2; break;
            case null:
            case '':$gender_id = 3; break;
            default:$gender_id = 4;
        }

        return $this->ig->request('accounts/set_gender/')
            ->setSignedPost(false)
            ->addPost('gender', $gender_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('custom_gender', $gender_id === 4 ? $gender : '')
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Edit your biography.
     *
     * You are able to add `@mentions` and `#hashtags` to your biography, but
     * be aware that Instagram disallows certain web URLs and shorteners.
     *
     * Also keep in mind that anyone can read your biography (even if your
     * account is private).
     *
     * WARNING: Remember to also call `editProfile()` *after* using this
     * function, so that you act like the real app!
     *
     * @param string $biography Biography text. Use "" for nothing.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     *
     * @see Account::editProfile() should be called after this function!
     */
    public function setBiography(
        $biography)
    {
        if (!is_string($biography) || mb_strlen($biography, 'utf8') > 150) {
            throw new \InvalidArgumentException('Please provide a 0 to 150 character string as biography.');
        }

        return $this->ig->request('accounts/set_biography/')
            ->addPost('raw_text', $biography)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Changes your account's profile picture.
     *
     * @param string $photoFilename The photo filename.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function changeProfilePicture(
        $photoFilename)
    {
        $photo = new \InstagramAPI\Media\Photo\InstagramPhoto($photoFilename);
        $internalMetadata = new InternalMetadata(Utils::generateUploadId(true));
        $internalMetadata->setPhotoDetails(Constants::FEED_TIMELINE, $photo->getFile());
        $uploadResponse = $this->ig->internal->uploadPhotoData(Constants::FEED_TIMELINE, $internalMetadata);

        return $this->ig->request('accounts/change_profile_picture/')
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('use_fbuploader', true)
            ->addPost('upload_id', $internalMetadata->getUploadId())
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Remove your account's profile picture.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function removeProfilePicture()
    {
        return $this->ig->request('accounts/remove_profile_picture/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Edit your profile.
     *
     * Warning: You must provide ALL parameters to this function. The values
     * which you provide will overwrite all current values on your profile.
     * You can use getCurrentUser() to see your current values first.
     *
     * @param string      $url         Website URL. Use "" for nothing.
     * @param string      $phone       Phone number. Use "" for nothing.
     * @param string      $name        Full name. Use "" for nothing.
     * @param string      $biography   Biography text. Use "" for nothing.
     * @param string      $email       Email. Required!
     * @param string|null $newUsername (optional) Rename your account to a new username,
     *                                 which you've already verified with checkUsername().
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     *
     * @see Account::getCurrentUser() to get your current account details.
     * @see Account::checkUsername() to verify your new username first.
     */
    public function editProfile(
        $url,
        $phone,
        $name,
        $biography,
        $email,
        $newUsername = null)
    {
        // We must mark the profile for editing before doing the main request.
        $userResponse = $this->ig->request('accounts/current_user/')
            ->addParam('edit', true)
            ->getResponse(new Response\UserInfoResponse());

        // Get the current user's name from the response.
        $currentUser = $userResponse->getUser();
        if (!$currentUser || !is_string($currentUser->getUsername())) {
            throw new InternalException('Unable to find current account username while preparing profile edit.');
        }
        $oldUsername = $currentUser->getUsername();

        // Determine the desired username value.
        $username = is_string($newUsername) && strlen($newUsername) > 0
                  ? $newUsername
                  : $oldUsername; // Keep current name.

        return $this->ig->request('accounts/edit_profile/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('external_url', $url)
            ->addPost('phone_number', $phone)
            ->addPost('username', $username)
            ->addPost('first_name', $name)
            ->addPost('biography', $biography)
            ->addPost('email', $email)
            ->addPost('device_id', $this->ig->device_id)
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Get anonymous profile picture.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AnonymousProfilePictureResponse
     */
    public function getAnonymousProfilePicture()
    {
        return $this->ig->request('accounts/anonymous_profile_picture/')
            ->getResponse(new Response\AnonymousProfilePictureResponse());
    }

    /**
     * Sets your account to public.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function setPublic()
    {
        $request = $this->ig->request('accounts/set_public/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_csrftoken', $this->ig->client->getToken());

        if ($this->ig->getIsAndroid()) {
            $request->addPost('_uid', $this->ig->account_id);
        }

        return $request->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Sets your account to private.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function setPrivate()
    {
        $request = $this->ig->request('accounts/set_private/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_csrftoken', $this->ig->client->getToken());

        if ($this->ig->getIsAndroid()) {
            $request->addPost('_uid', $this->ig->account_id);
        }

        return $request->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Switches your account from/to creator to/from personal profile
     * 
     * @throws \InstagramAPI\Exception\InstagramException
     * 
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function switchToCreatorProfile(
        $to_creator = true,
        $to_account_type = 3,
        $should_show_category = 1,
        $should_show_public_contacts = 1,
        $category_id = "361282040719868")
    {
        $request = $this->ig->request('business/account/convert_account/')
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('to_account_type', $to_account_type);
        if ($to_creator) {
            $request->addPost('should_bypass_contact_check', true)
                    ->addPost('set_public', false)
                    ->addPost('entry_point', 'setting')
                    ->addPost('should_show_category', $should_show_category)
                    ->addPost('should_show_public_contacts', $should_show_public_contacts)
                    ->addPost('category_id', $category_id);
        }
        return $request->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Search Business Category
     * 
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GraphqlResponse
     */
    public function searchBusinessCategory(
        $query)
    {
        return $this->ig->request('wwwgraphql/ig/query/')
            ->addUnsignedPost('doc_id', '2293534497375714')
            ->addUnsignedPost('locale', $this->ig->getLocale())
            ->addUnsignedPost('vc_policy', 'default')
            ->addUnsignedPost('strip_nulls', true)
            ->addUnsignedPost('strip_defaults', true)
            ->addUnsignedPost('signed_body', Constants::IG_SIG_KEY)
            ->addUnsignedPost('ig_sig_key_version',	Constants::SIG_KEY_VERSION)
            ->addUnsignedPost('query_params', json_encode([
                'query'    => $query,
                'locale'   => $this->ig->getLocale(),
                'vertical' => '1836018136453734'
            ]))
            ->getResponse(new Response\GraphqlResponse());
    }

    /**
     * Switches your account to business profile.
     *
     * In order to switch your account to Business profile you MUST
     * call Account::setBusinessInfo().
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SwitchBusinessProfileResponse
     *
     * @see Account::setBusinessInfo() sets required data to become a business profile.
     */
    public function switchToBusinessProfile()
    {
        return $this->ig->request('business_conversion/get_business_convert_social_context/')
            ->getResponse(new Response\SwitchBusinessProfileResponse());
    }

    /**
     * Switches your account to personal profile.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SwitchPersonalProfileResponse
     */
    public function switchToPersonalProfile()
    {
        return $this->ig->request('accounts/convert_to_personal/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\SwitchPersonalProfileResponse());
    }

    /**
     * Sets contact information for business profile.
     *
     * @param string $phoneNumber              Phone number with country code. Format: +34123456789.
     * @param string $email                    Email.
     * @param string $categoryId               Category ID, you can find this ID with Account::searchBusinessCategory().
     * @param string $business_contact_method  Business contact method 'CALL' or 'TEXT'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CreateBusinessInfoResponse
     */
    public function setBusinessInfo(
        $phoneNumber,
        $email,
        $categoryId,
        $business_contact_method = 'CALL')
    {
        return $this->ig->request('accounts/create_business_info/')
            ->addPost('set_public', 'true')
            ->addPost('entry_point', 'setting')
            ->addPost('public_phone_contact', json_encode([
                'public_phone_number'       => $phoneNumber,
                'business_contact_method'   => $business_contact_method,
            ]))
            ->addPost('public_email', $email)
            ->addPost('category_id', $categoryId)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\CreateBusinessInfoResponse());
    }

    /**
     * Check if an Instagram username is available (not already registered).
     *
     * Use this before trying to rename your Instagram account,
     * to be sure that the new username is available.
     *
     * @param string $username Instagram username to check.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CheckUsernameResponse
     *
     * @see Account::editProfile() to rename your account.
     */
    public function checkUsername(
        $username)
    {
        $this->ig->setUserWithoutPassword($username);

        return $this->ig->request('users/check_username/')
            ->setNeedsAuth(false)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('username', $username)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->getResponse(new Response\CheckUsernameResponse());
    }

    /**
     * Check if an email is available (not already registered).
     *
     * @param string $email       Email to check.
     * @param string $waterfallId UUIDv4.
     * @param string $username    Username.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CheckEmailResponse
     */
    public function checkEmail(
        $email,
        $waterfallId,
        $username)
    {
        $this->ig->setUserWithoutPassword($username);

        return $this->ig->request('users/check_email/')
            ->setNeedsAuth(false)
            ->addPost('android_device_id', $this->ig->device_id)
            ->addPost('login_nonce_map', '{}')
            ->addPost('login_nonces', '[]')
            ->addPost('email', $email)
            ->addPost('qe_id', Signatures::generateUUID())
            ->addPost('waterfall_id', $waterfallId)
            ->getResponse(new Response\CheckEmailResponse());
    }

    /**
     * Get username suggestions.
     *
     * @param string $email         Email to check.
     * @param string $waterfallId   UUIDv4.
     * @param string $usernameQuery Username query for username suggestions.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UsernameSuggestionsResponse
     */
    public function getUsernameSuggestions(
        $email,
        $waterfallId,
        $usernameQuery = '')
    {
        return $this->ig->request('accounts/username_suggestions/')
            ->setNeedsAuth(false)
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('guid', $this->ig->uuid)
            ->addPost('name', $usernameQuery)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('email', $email)
            ->addPost('waterfall_id', $waterfallId)
            ->getResponse(new Response\UsernameSuggestionsResponse());
    }

    /**
     * Update contact information for business profile.
     *
     * @param string $phoneNumber             Phone number with country code. Format: +34123456789.
     * @param string $email                   Email.
     * @param string $business_contact_method Business contact method 'CALL' or 'TEXT'
     * @param string $address_street
     * @param string $city_id                 City ID
     * @param string $zip
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CreateBusinessInfoResponse
     */
    public function updateBusinessInfo(
        $phoneNumber,
        $email,
        $business_contact_method,
        $address_street = '',
        $city_id = 0,
        $zip = '')
    {
        return $this->ig->request('accounts/update_business_info/')
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('public_phone_contact', json_encode([
                'public_phone_number'       => $phoneNumber,
                'business_contact_method'   => $business_contact_method,
            ]))
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('is_call_to_action_enabled', 0)
            ->addPost('public_email', $email)
            ->addPost('business_address', json_encode([
                'address_street' => $address_street,
                'city_id'        => $city_id,
                'zip'            => $zip
            ]))
            ->getResponse(new Response\CreateBusinessInfoResponse());
    }

    /**
     * Set visibility of business profile call to action or category
     *
     * @param string $should_show_public_contacts Display public contacts
     * @param string $should_show_category        Display profile category
     * @param string $categoryId  
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GraphqlResponse
     */
    public function BusinessProfileDisplayOptions(
        $should_show_public_contacts = 1,
        $should_show_category = 1)
    {
        return $this->ig->request('business/account/edit_account/')
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('should_show_public_contacts', $should_show_public_contacts)
            ->addPost('should_show_category', $should_show_category)
            ->getResponse(new Response\GraphqlResponse());
    }

    /**
     * Get account spam filter status.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentFilterResponse
     */
    public function getCommentFilter()
    {
        return $this->ig->request('accounts/get_comment_filter/')
            ->getResponse(new Response\CommentFilterResponse());
    }

    /**
     * Set account spam filter status (on/off).
     *
     * @param int $config_value Whether spam filter is on (0 or 1).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentFilterSetResponse
     */
    public function setCommentFilter(
        $config_value)
    {
        return $this->ig->request('accounts/set_comment_filter/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('config_value', $config_value)
            ->getResponse(new Response\CommentFilterSetResponse());
    }

    /**
     * Get whether the comment category filter is disabled.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentCategoryFilterResponse
     */
    public function getCommentCategoryFilterDisabled()
    {
        return $this->ig->request('accounts/get_comment_category_filter_disabled/')
            ->getResponse(new Response\CommentCategoryFilterResponse());
    }

    /**
     * Get account spam filter keywords.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentFilterKeywordsResponse
     */
    public function getCommentFilterKeywords()
    {
        return $this->ig->request('accounts/get_comment_filter_keywords/')
            ->getResponse(new Response\CommentFilterKeywordsResponse());
    }

    /**
     * Set account spam filter keywords.
     *
     * @param string $keywords List of blocked words, separated by comma.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentFilterSetResponse
     */
    public function setCommentFilterKeywords(
        $keywords)
    {
        return $this->ig->request('accounts/set_comment_filter_keywords/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('keywords', $keywords)
            ->getResponse(new Response\CommentFilterSetResponse());
    }

    /**
     * Change your account's password.
     *
     * @param string $oldPassword Old password.
     * @param string $newPassword New password.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ChangePasswordResponse
     */
    public function changePassword(
        $oldPassword,
        $newPassword)
    {
        return $this->ig->request('accounts/change_password/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('enc_old_password', Utils::encryptPassword($oldPassword, $this->ig->settings->get('public_key_id'), $this->ig->settings->get('public_key')))
            ->addPost('enc_new_password1', Utils::encryptPassword($newPassword, $this->ig->settings->get('public_key_id'), $this->ig->settings->get('public_key')))
            ->addPost('enc_new_password2', Utils::encryptPassword($newPassword, $this->ig->settings->get('public_key_id'), $this->ig->settings->get('public_key')))
            ->getResponse(new Response\ChangePasswordResponse());
    }

    /**
     * Get account security info and backup codes.
     *
     * WARNING: STORE AND KEEP BACKUP CODES IN A SAFE PLACE. THEY ARE EXTREMELY
     *          IMPORTANT! YOU WILL GET THE CODES IN THE RESPONSE. THE BACKUP
     *          CODES LET YOU REGAIN CONTROL OF YOUR ACCOUNT IF YOU LOSE THE
     *          PHONE NUMBER! WITHOUT THE CODES, YOU RISK LOSING YOUR ACCOUNT!
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AccountSecurityInfoResponse
     *
     * @see Account::enableTwoFactorSMS()
     */
    public function getSecurityInfo()
    {
        return $this->ig->request('accounts/account_security_info/')
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\AccountSecurityInfoResponse());
    }

    /**
     * Request that Instagram enables two factor SMS authentication.
     *
     * The SMS will have a verification code for enabling two factor SMS
     * authentication. You must then give that code to enableTwoFactorSMS().
     *
     * @param string $phoneNumber Phone number with country code. Format: +34123456789.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendTwoFactorEnableSMSResponse
     *
     * @see Account::enableTwoFactorSMS()
     */
    public function sendTwoFactorEnableSMS(
        $phoneNumber)
    {
        $cleanNumber = '+'.preg_replace('/[^0-9]/', '', $phoneNumber);

        return $this->ig->request('accounts/send_two_factor_enable_sms/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('phone_number', $cleanNumber)
            ->getResponse(new Response\SendTwoFactorEnableSMSResponse());
    }

    /**
     * Enable Two Factor authentication.
     *
     * WARNING: STORE AND KEEP BACKUP CODES IN A SAFE PLACE. THEY ARE EXTREMELY
     *          IMPORTANT! YOU WILL GET THE CODES IN THE RESPONSE. THE BACKUP
     *          CODES LET YOU REGAIN CONTROL OF YOUR ACCOUNT IF YOU LOSE THE
     *          PHONE NUMBER! WITHOUT THE CODES, YOU RISK LOSING YOUR ACCOUNT!
     *
     * @param string $phoneNumber      Phone number with country code. Format: +34123456789.
     * @param string $verificationCode The code sent to your phone via `Account::sendTwoFactorEnableSMS()`.
     * @param bool   $trustDevice      If you want to trust the used Device ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AccountSecurityInfoResponse
     *
     * @see Account::sendTwoFactorEnableSMS()
     * @see Account::getSecurityInfo()
     */
    public function enableTwoFactorSMS(
        $phoneNumber,
        $verificationCode,
        $trustDevice = true)
    {
        $cleanNumber = '+'.preg_replace('/[^0-9]/', '', $phoneNumber);

        $this->ig->request('accounts/enable_sms_two_factor/')
            ->addPost('trust_this_device', ($trustDevice) ? '1' : '0')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('phone_number', $cleanNumber)
            ->addPost('verification_code', $verificationCode)
            ->getResponse(new Response\EnableTwoFactorSMSResponse());

        return $this->getSecurityInfo();
    }

    /**
     * Disable Two Factor authentication.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DisableTwoFactorSMSResponse
     */
    public function disableTwoFactorSMS()
    {
        return $this->ig->request('accounts/disable_sms_two_factor/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\DisableTwoFactorSMSResponse());
    }

    /**
     * Save presence status to the storage.
     *
     * @param bool $disabled
     */
    protected function _savePresenceStatus(
        $disabled)
    {
        try {
            $this->ig->settings->set('presence_disabled', $disabled ? '1' : '0');
        } catch (SettingsException $e) {
            // Ignore storage errors.
        }
    }

    /**
     * Get presence status.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PresenceStatusResponse
     */
    public function getPresenceStatus()
    {
        /** @var Response\PresenceStatusResponse $result */
        $result = $this->ig->request('accounts/get_presence_disabled/')
            ->setSignedGet(true)
            ->getResponse(new Response\PresenceStatusResponse());

        $this->_savePresenceStatus($result->getDisabled());

        return $result;
    }

    /**
     * Enable presence.
     *
     * Allow accounts you follow and anyone you message to see when you were
     * last active on Instagram apps.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function enablePresence()
    {
        /** @var Response\GenericResponse $result */
        $result = $this->ig->request('accounts/set_presence_disabled/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('disabled', '0')
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());

        $this->_savePresenceStatus(false);

        return $result;
    }

    /**
     * Disable presence.
     *
     * You won't be able to see the activity status of other accounts.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function disablePresence()
    {
        /** @var Response\GenericResponse $result */
        $result = $this->ig->request('accounts/set_presence_disabled/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('disabled', '1')
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());

        $this->_savePresenceStatus(true);

        return $result;
    }

    /**
     * Tell Instagram to send you a message to verify your email address.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendConfirmEmailResponse
     */
    public function sendConfirmEmail()
    {
        return $this->ig->request('accounts/send_confirm_email/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('send_source', 'edit_profile')
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\SendConfirmEmailResponse());
    }

    /**
     * Tell Instagram to send you an SMS code to verify your phone number.
     *
     * @param string $phoneNumber Phone number with country code. Format: +34123456789.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendSMSCodeResponse
     */
    public function sendSMSCode(
        $phoneNumber)
    {
        $cleanNumber = '+'.preg_replace('/[^0-9]/', '', $phoneNumber);

        return $this->ig->request('accounts/send_sms_code/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('phone_number', $cleanNumber)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\SendSMSCodeResponse());
    }

    /**
     * Submit the SMS code you received to verify your phone number.
     *
     * @param string $phoneNumber      Phone number with country code. Format: +34123456789.
     * @param string $verificationCode The code sent to your phone via `Account::sendSMSCode()`.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\VerifySMSCodeResponse
     *
     * @see Account::sendSMSCode()
     */
    public function verifySMSCode(
        $phoneNumber,
        $verificationCode)
    {
        $cleanNumber = '+'.preg_replace('/[^0-9]/', '', $phoneNumber);

        return $this->ig->request('accounts/verify_sms_code/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('phone_number', $cleanNumber)
            ->addPost('verification_code', $verificationCode)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\VerifySMSCodeResponse());
    }

    /**
     * Set contact point prefill.
     *
     * @param string $usage Either "prefill" or "auto_confirmation".
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function setContactPointPrefill(
        $usage)
    {
        return $this->ig->request('accounts/contact_point_prefill/')
            ->setNeedsAuth(false)
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('usage', $usage)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     *  Get prefill candidates.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PrefillCandidatesResponse
     */
    public function getPrefillCandidates()
    {
        return $this->ig->request('accounts/get_prefill_candidates/')
            ->setNeedsAuth(false)
            ->addPost('android_device_id', $this->ig->device_id)
            ->addPost('device_id', $this->ig->uuid)
            ->addPost('usages', '["account_recovery_omnibox"]')
            ->getResponse(new Response\PrefillCandidatesResponse());
    }

    /**
     * Get account badge notifications for the "Switch account" menu.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\BadgeNotificationsResponse
     */
    public function getBadgeNotifications()
    {
        return $this->ig->request('notifications/badge/')
            ->setSignedPost(false)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('users_ids', $this->ig->account_id)
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('device_id', $this->ig->uuid)
            ->getResponse(new Response\BadgeNotificationsResponse());
    }

    /**
     * Get Facebook ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FacebookIdResponse
     */
    public function getFacebookId()
    {
        return $this->ig->request('fb/get_connected_fbid/')
            ->setSignedPost(false)
            ->setIsSilentFail(true)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\FacebookIdResponse());
    }

    /**
     * Get linked accounts status.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LinkageStatusResponse
     */
    public function getLinkageStatus()
    {
        return $this->ig->request('linked_accounts/get_linkage_status/')
            ->getResponse(new Response\LinkageStatusResponse());
    }

    /**
     * Accounts Access Tool
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getAccountsAccessTool()
    {
        return $this->ig->request('accounts/process_contact_point_signals/')
            ->addPost('device_id', $this->ig->device_id)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Accounts Access Tool (Graph API)
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getAccountsAccessToolGraph(
        $query_hash = "68b15837c4c60cf5bb0c3df17a4791f8",
        $referer = "")
    {
        $request = $this->ig->request("graphql/query/")
            ->setVersion(5)
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->setIsBodyCompressed(false);

            if (!empty($referer)) {
                $request->addHeader('Referer', 'https://www.instagram.com/' . $referer);
            } else {
                $request->addHeader('Referer', 'https://www.instagram.com/');
            }

            $request->addParam('query_hash', $query_hash)
                    ->addParam('variables', '{}');

        return $request->getResponse(new Response\GraphqlResponse());
    }

    /**
     * TODO.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getProcessContactPointSignals()
    {
        return $this->ig->request('accountsaccess_tool/')
            ->addPost('google_tokens', '[]')
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Send recovery flow via email.
     *
     * @param string $query Username or email.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendRecoveryFlowResponse
     */
    public function sendRecoveryFlowEmail(
        $query)
    {
        return $this->ig->request('accounts/send_recovery_flow_email/')
            ->addPost('guid', $this->ig->uuid)
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('adid', $this->ig->advertising_id)
            ->addPost('query', $query)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\SendRecoveryFlowResponse());
    }

    /**
     * Send recovery flow via phone.
     *
     * @param string $query             Username or email.
     * @param bool   $whatsAppInstalled Wether WhatsApp is installed or not.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LookupPhoneResponse
     */
    public function lookupPhone(
        $query,
        $whatsAppInstalled = false)
    {
        return $this->ig->request('users/lookup_phone/')
            ->addPost('supports_sms_code', 'true')
            ->addPost('use_whatsapp', $whatsAppInstalled)
            ->addPost('guid', $this->ig->uuid)
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('adid', $this->ig->advertising_id)
            ->addPost('query', $query)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\LookupPhoneResponse());
    }

    /**
     * Get User Xposting Destination (Sharing to Other App)
     * 
     * @throws \InstagramAPI\Exception\InstagramException
     * 
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getXpostingAccountLinking() 
    {
        return $this->ig->request('ig_fb_xposting/account_linking/user_xposting_destination/')
            ->setSignedGet(true)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get User Xposting Setting (Sharing to Other App)
     * 
     * @throws \InstagramAPI\Exception\InstagramException
     * 
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getXpostingUserSetting() 
    {
        return $this->ig->request('ig_fb_xposting/user_setting/')
            ->getResponse(new Response\GenericResponse());
    } 
}
