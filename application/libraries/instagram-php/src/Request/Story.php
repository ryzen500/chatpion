<?php

namespace InstagramAPI\Request;

use InstagramAPI\Constants;
use InstagramAPI\Request\Metadata\Internal as InternalMetadata;
use InstagramAPI\Response;
use InstagramAPI\Utils;
use InstagramAPI\Signatures;

/**
 * Functions for managing your story and interacting with other stories.
 *
 * @see Media for more functions that let you interact with the media.
 */
class Story extends RequestCollection
{
    /**
     * Uploads a photo to your Instagram story.
     *
     * @param string $photoFilename    The photo filename.
     * @param array  $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see Internal::configureSinglePhoto() for available metadata fields.
     */
    public function uploadPhoto(
        $photoFilename,
        array $externalMetadata = [])
    {
        return $this->ig->internal->uploadSinglePhoto(Constants::FEED_STORY, $photoFilename, null, $externalMetadata);
    }

    /**
     * Uploads a photo to your Instagram close friends story.
     *
     * @param string $photoFilename    The photo filename.
     * @param array  $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see Internal::configureSinglePhoto() for available metadata fields.
     * @see https://help.instagram.com/2183694401643300
     */
    public function uploadCloseFriendsPhoto(
        $photoFilename,
        array $externalMetadata = [])
    {
        $internalMetadata = new InternalMetadata(Utils::generateUploadId(true));
        $internalMetadata->setBestieMedia(true);

        return $this->ig->internal->uploadSinglePhoto(Constants::FEED_STORY, $photoFilename, $internalMetadata, $externalMetadata);
    }

    /**
     * Uploads a video to your Instagram story.
     *
     * @param string $videoFilename    The video filename.
     * @param array  $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video upload fails.
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see Internal::configureSingleVideo() for available metadata fields.
     */
    public function uploadVideo(
        $videoFilename,
        array $externalMetadata = [])
    {
        return $this->ig->internal->uploadSingleVideo(Constants::FEED_STORY, $videoFilename, null, $externalMetadata);
    }

    /**
     * Uploads a video to your Instagram close friends story.
     *
     * @param string $videoFilename    The video filename.
     * @param array  $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video upload fails.
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see Internal::configureSingleVideo() for available metadata fields.
     * @see https://help.instagram.com/2183694401643300
     */
    public function uploadCloseFriendsVideo(
        $videoFilename,
        array $externalMetadata = [])
    {
        $internalMetadata = new InternalMetadata();
        $internalMetadata->setBestieMedia(true);

        return $this->ig->internal->uploadSingleVideo(Constants::FEED_STORY, $videoFilename, $internalMetadata, $externalMetadata);
    }

    /**
     * Get the global story feed which contains everyone you follow.
     *
     * Note that users will eventually drop out of this list even though they
     * still have stories. So it's always safer to call getUserStoryFeed() if
     * a specific user's story feed matters to you.
     *
     * @param string $reason (optional) Reason for the request.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsTrayFeedResponse
     *
     * @see Story::getUserStoryFeed()
     */
    public function getReelsTrayFeed(
        $reason = 'pull_to_refresh')
    {
        return $this->ig->request('feed/reels_tray/')
            ->setSignedPost(false)
            ->addPost('supported_capabilities_new', json_encode(Constants::SUPPORTED_CAPABILITIES))
            ->addPost('reason', $reason)
            ->addPost('timezone_offset', '-18000')
            ->addPost('tray_session_id', Signatures::generateUUID(true))
            ->addPost('request_id', Signatures::generateUUID(true))
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('page_size', 50) 
            ->getResponse(new Response\ReelsTrayFeedResponse());
    }

    /**
     * Stiker tray
     * 
     * @throws \InstagramAPI\Exception\InstagramException
     * 
     * @return \InstagramAPI\Response\ReelsTrayFeedResponse
     */
    public function StickerTray(
        $horizontalAccuracy = '0.0',
        $camera_entry_point = 'feed_post_to_story_button',
        $alt = '0.0',
        $lat = '0.0',
        $lng = '0.0',
        $type = 'static_stickers',
        $speed = '0.0') 
    {
        return $this->ig->request('/api/v1/creatives/sticker_tray/')
            ->addPost('horizontalAccuracy', $horizontalAccuracy)
            ->addPost('camera_entry_point', $camera_entry_point)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('alt', $alt)
            ->addPost('lat', $lat)
            ->addPost('lng', $lng)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('type', $type)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('speed', $speed)
            ->getResponse(new Response\ReelsTrayFeedResponse());
    }

    /**
     * Get multiple users' latest stories at once.
     *
     * @param string|string[] $feedList List of numerical UserPK IDs.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsTrayFeedResponse
     */
    public function getLatestStoryMedia(
        $feedList)
    {
        if (!is_array($feedList)) {
            $feedList = [$feedList];
        }

        foreach ($feedList as &$value) {
            $value = (string) $value;
        }
        unset($value); // Clear reference.

        return $this->ig->request('feed/get_latest_reel_media/')
            ->setSignedPost(false)
            ->addPost('user_ids', $feedList) // Must be string[] array.
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\ReelsTrayFeedResponse());
    }

    /**
     * Get a specific user's story reel feed.
     *
     * This function gets the user's story Reel object directly, which always
     * exists and contains information about the user and their last story even
     * if that user doesn't have any active story anymore.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserReelMediaFeedResponse
     *
     * @see Story::getUserStoryFeed()
     */
    public function getUserReelMediaFeed(
        $userId)
    {
        return $this->ig->request("feed/user/{$userId}/reel_media/")
            ->getResponse(new Response\UserReelMediaFeedResponse());
    }

    /**
     * Get a specific user's story feed with broadcast details.
     *
     * This function gets the story in a roundabout way, with some extra details
     * about the "broadcast". But if there is no story available, this endpoint
     * gives you an empty response.
     *
     * NOTE: At least AT THIS MOMENT, this endpoint and the reels-tray endpoint
     * are the only ones that will give you people's "post_live" fields (their
     * saved Instagram Live Replays). The other "get user stories" funcs don't!
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserStoryFeedResponse
     *
     * @see Story::getUserReelMediaFeed()
     */
    public function getUserStoryFeed(
        $userId)
    {
        return $this->ig->request("feed/user/{$userId}/story/")
            ->addParam('supported_capabilities_new', json_encode(Constants::SUPPORTED_CAPABILITIES))
            ->getResponse(new Response\UserStoryFeedResponse());
    }

    /**
     * Get multiple users' story feeds (or specific highlight-details) at once.
     *
     * NOTE: Normally, you would only use this endpoint for stories (by passing
     * UserPK IDs as the parameter). But if you're looking at people's highlight
     * feeds (via `Highlight::getUserFeed()`), you may also sometimes discover
     * highlight entries that don't have any `items` array. In that case, you
     * are supposed to get the items for those highlights via this endpoint!
     * Simply pass their `id` values as the argument to this API to get details.
     *
     * @param string|string[] $feedList           List of numerical UserPK IDs, OR highlight IDs (such as `highlight:123882132324123`).
     * @param string          $source             (optional) Source app-module where the request was made.
     * @param string|string[] $exclude_media_ids  List of numerical UserPK IDs to exclude from response
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsMediaResponse
     *
     * @see Highlight::getUserFeed() More info about when to use this API for highlight-details.
     */
    public function getReelsMediaFeed(
        $feedList,
        $source = 'reel_feed_timeline',
        $exclude_media_ids = [])
    {
        if (!is_array($feedList)) {
            $feedList = [$feedList];
        }

        foreach ($feedList as &$value) {
            $value = (string) $value;
        }
        unset($value); // Clear reference.

        return $this->ig->request('feed/reels_media/')
            ->addPost('supported_capabilities_new', json_encode(Constants::SUPPORTED_CAPABILITIES))
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('user_ids', $feedList) // Must be string[] array.
            ->addPost('exclude_media_ids', $exclude_media_ids)
            ->addPost('source', $source)
            ->getResponse(new Response\ReelsMediaResponse());
    }

    /**
     * Get a specific user's story reel feed with web API
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     *
     * @see Story::getUserStoryFeed()
     */
	public function getReelsMediaFeedGraph(
        $feedList,
        $query_hash = "c9c56db64beb4c9dea2d17740d0259d9") 
    {
        if (!is_array($feedList)) {
            $feedList = [$feedList];
        }

        foreach ($feedList as &$value) {
            $value = (string) $value;
        }
        unset($value); // Clear reference.

        $request = $this->ig->request("graphql/query/")
            ->setVersion(5)
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->setIsBodyCompressed(false)
            ->addHeader('Referer', 'https://www.instagram.com/explore/people/suggested/')
            ->addParam('query_hash', $query_hash)
            ->addParam('variables', json_encode([
                "reel_ids" => $feedList,
                "tag_names" => [],
                "location_ids" => [],
                "highlight_reel_ids" => [],
                "precomposed_overlay" => false,
                "show_story_viewer_list" => true,
                "story_viewer_fetch_count" => 50,
                "story_viewer_cursor" => "",
                "stories_video_dash_manifest" => false
            ]));
        return $request->getResponse(new Response\GenericResponse());
    }

    /**
     * Get a specific user's story reel feed with web API (v2, just name changed for compatibility with some scripts)
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     *
     * @see Story::getUserStoryFeed()
     */
	public function getReelsMediaFeedWeb(
        $feedList,
        $query_hash = "c9c56db64beb4c9dea2d17740d0259d9") 
    {
        if (!is_array($feedList)) {
            $feedList = [$feedList];
        }

        foreach ($feedList as &$value) {
            $value = (string) $value;
        }
        unset($value); // Clear reference.

        $request = $this->ig->request("graphql/query/")
            ->setVersion(5)
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->setIsBodyCompressed(false)
            ->addHeader('Referer', 'https://www.instagram.com/explore/people/suggested/')
            ->addParam('query_hash', $query_hash)
            ->addParam('variables', json_encode([
                "reel_ids" => $feedList,
                "tag_names" => [],
                "location_ids" => [],
                "highlight_reel_ids" => [],
                "precomposed_overlay" => false,
                "show_story_viewer_list" => true,
                "story_viewer_fetch_count" => 50,
                "story_viewer_cursor" => "",
                "stories_video_dash_manifest" => false
            ]));
        return $request->getResponse(new Response\GenericResponse());
    }

    /**
     * Mark story media item as seen with web API
     */
    public function markMediaSeenGraph(
        $reelMediaId, 
        $reelMediaOwnerId, 
        $reelMediaTakenAt,
        $rollout_hash)
    {    
        if ($reelMediaId == null) {
            throw new \InvalidArgumentException('Empty $reelMediaId sent to markMediaSeenGraph() function.');
        }

        if ($reelMediaOwnerId == null) {
            throw new \InvalidArgumentException('Empty $reelMediaOwnerId sent to markMediaSeenGraph() function.');
        }

        if ($reelMediaTakenAt == null) {
            throw new \InvalidArgumentException('Empty $reelMediaTakenAt sent to markMediaSeenGraph() function.');
        }

        if ($rollout_hash == null) {
            throw new \InvalidArgumentException('Empty $rollout_hash sent to markMediaSeenGraph() function.');
        }

        $request = $this->ig->request("https://www.instagram.com/stories/reel/seen")
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->setIsBodyCompressed(false)
            ->addHeader('X-CSRFToken', $this->ig->client->getToken())
            ->addHeader('Referer', 'https://www.instagram.com/')
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('X-Instagram-AJAX', $rollout_hash)
            ->addHeader('X-IG-App-ID', Constants::IG_WEB_APPLICATION_ID);
            if ($this->ig->getIsAndroid()) {
                $request->addHeader('User-Agent', sprintf('Mozilla/5.0 (Linux; Android %s; Google) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Mobile Safari/537.36', $this->ig->device->getAndroidRelease()));
            } else {
                $request->addHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS ' . Constants::IOS_VERSION . ' like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.4 Mobile/15E148 Safari/604.1');
            }
            $request->addPost('reelMediaId', $reelMediaId)
                    ->addPost('reelMediaOwnerId', $reelMediaOwnerId)
                    ->addPost('reelId', $reelMediaOwnerId)
                    ->addPost('reelMediaTakenAt', $reelMediaTakenAt)
                    ->addPost('viewSeenAt', $reelMediaTakenAt);

        return $request->getResponse(new Response\GenericResponse());
    }

    /**
     * Mark story media item as seen with web API (v2, just name changed for compatibility with some scripts)
     */
    public function markMediaSeenWeb($reelMediaId, $reelMediaOwnerId, $reelMediaTakenAt) {
        $csrftoken  = $this->ig->client->getToken();
        $mid        = $this->ig->client->getMid();
        $ds_user_id = $this->ig->client->getDSUserId();
        $sessionid  = $this->ig->client->getSessionID();
        $urlgen     = $this->ig->client->getURLGen();
        $rur        = $this->ig->client->getRUR();
        $proxy      = $this->ig->client->getProxy();

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => "https://www.instagram.com/stories/reel/seen",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_USERAGENT      => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36",
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => [
                'reelMediaId'      => $reelMediaId,
                'reelMediaOwnerId' => $reelMediaOwnerId,
                'reelId'           => $reelMediaOwnerId,
                'reelMediaTakenAt' => $reelMediaTakenAt,
                'viewSeenAt'       => $reelMediaTakenAt
            ],
            CURLOPT_HTTPHEADER     => [
                "referrer: https://www.instagram.com/",
                "x-ig-app-id: 936619743392459",
                "x-instagram-ajax: f9e28d162740",
                "sec-fetch-site: same-origin",
                "sec-fetch-mode: cors",
                "sec-fetch-dest: empty",
                "accept: */*",
                "accept-encoding: gzip, deflate, br",
                "x-ig-www-claim: hmac.AR3VWu1WbtgZTYm1LI-JdffO71nek5ezN2CM-bQ6iN6n1Dka",
                "x-requested-with: XMLHttpRequest",
                "x-csrftoken: " . $csrftoken,
                "Content-Type: application/x-www-form-urlencoded",
                "Cookie: mid=".$mid."; ds_user_id=$ds_user_id; csrftoken=$csrftoken; sessionid=$sessionid; rur=$rur; urlgen=$urlgen"
            ],
        ]);
        
        if ($proxy) {
            $parts = parse_url($proxy);
    
            if (!$parts || !isset($parts['host'])) {
                throw new \InvalidArgumentException('Invalid proxy URL "' . $proxy . '"'); 
            }
                    
            if (!isset($parts['scheme']) || ($parts['scheme'] !== 'http' && $parts['scheme'] !== 'https')) { 
				$parts['scheme'] = 'http';
			}
        
            if (isset($parts['user'])) {
                $proxyAuth = $parts['user'] . ':' . $parts['pass'];
            } else {
                $proxyAuth = false;
            }
    
            $proxyAddress = $parts['scheme'] . '://' . $parts['host'] . ':' . $parts['port'];
    
            curl_setopt($curl, CURLOPT_PROXY, $proxyAddress);
    
            if ($proxyAuth) {
                curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxyAuth);
            }
        }

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    /**
     * Get injected stories (ads).
     *
     * @param string[]|int[] $storyUserIds  Array of numerical UserPK IDs.
     * @param string         $traySessionId UUID v4.
     * @param int            $entryIndex.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsMediaResponse
     */
    public function getInjectedStories(
        array $storyUserIds,
        $traySessionId,
        $entryIndex = 0)
    {
        if ($entryIndex < 0) {
            throw new \InvalidArgumentException('Entry index must be a positive number.');
        }

        if (!count($storyUserIds)) {
            throw new \InvalidArgumentException('Please provide at least one user.');
        }
        foreach ($storyUserIds as &$storyUserId) {
            if (!is_scalar($storyUserId)) {
                throw new \InvalidArgumentException('User identifier must be scalar.');
            } elseif (!ctype_digit($storyUserId) && (!is_int($storyUserId) || $storyUserId < 0)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid user identifier.', $storyUserId));
            }
            $storyUserId = (string) $storyUserId;
        }

        $request = $this->ig->request('feed/injected_reels_media/')
            ->setIsBodyCompressed(true)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('inserted_netego_indices', [])
            ->addPost('ad_and_netego_request_information', [])
            ->addPost('inserted_ad_indices', [])
            ->addPost('ad_request_index', '0')
            ->addPost('is_inventory_based_request_enabled', '0')
            ->addPost('is_ad_pod_enabled', '0')
            ->addPost('battery_level', $this->ig->getBatteryLevel())
            ->addPost('tray_session_id', $traySessionId)
            ->addPost('viewer_session_id', md5($traySessionId))
            ->addPost('reel_position', '0')
            ->addPost('is_charging', $this->ig->getIsDeviceCharging())
            ->addPost('will_sound_on', '1')
            ->addPost('surface_q_id', '2247106998672735')
            ->addPost('tray_user_ids', $storyUserIds)
            ->addPost('is_media_based_insertion_enabled', '1')
            ->addPost('entry_point_index', ($entryIndex !== 0) ? strval($entryIndex) : '0')
            ->addPost('is_first_page', ($entryIndex !== 0) ? '0' : '1');

        if ($this->ig->getIsAndroid()) {
            $request->addPost('phone_id', $this->ig->phone_id)
                    ->addPost('device_id', $this->ig->uuid);
        }

        return $request->getResponse(new Response\ReelsMediaResponse());
    }

    /**
     * Get your archived story media feed.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ArchivedStoriesFeedResponse
     */
    public function getArchivedStoriesFeed()
    {
        return $this->ig->request('archive/reel/day_shells/')
            ->addParam('include_suggested_highlights', false)
            ->addParam('is_in_archive_home', true)
            ->addParam('include_cover', 0)
            ->addParam('timezone_offset', (!is_null($this->ig->getTimezoneOffset())) ? $this->ig->getTimezoneOffset() : date('Z'))
            ->getResponse(new Response\ArchivedStoriesFeedResponse());
    }

    /**
     * Get archive badge count.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ArchiveBadgeCountResponse
     */
    public function getArchiveBadgeCount()
    {
        return $this->ig->request('archive/reel/profile_archive_badge/')
        ->addParam('timezone_offset', (!is_null($this->ig->getTimezoneOffset())) ? $this->ig->getTimezoneOffset() : date('Z'))
            ->addParam('_uuid', $this->ig->uuid)
            ->addParam('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\ArchiveBadgeCountResponse());
    }

    /**
     * Get the list of users who have seen one of your story items.
     *
     * Note that this only works for your own story items. Instagram doesn't
     * allow you to see the viewer list for other people's stories!
     *
     * @param string      $storyPk The story media item's PK in Instagram's internal format (ie "3482384834").
     * @param string|null $maxId   Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelMediaViewerResponse
     */
    public function getStoryItemViewers(
        $storyPk,
        $maxId = null)
    {
        $request = $this->ig->request("media/{$storyPk}/list_reel_media_viewer/")
            ->addParam('supported_capabilities_new', json_encode(Constants::SUPPORTED_CAPABILITIES));
        if ($maxId !== null) {
            $request->addParam('max_id', $maxId);
        }

        return $request->getResponse(new Response\ReelMediaViewerResponse());
    }

    /**
     * Vote on a story poll.
     *
     * Note that once you vote on a story poll, you cannot change your vote.
     *
     * @param string $storyId      The story media item's ID in Instagram's internal format (ie "1542304813904481224_6112344004").
     * @param string $pollId       The poll ID in Instagram's internal format (ie "17956159684032257").
     * @param int    $votingOption Value that represents the voting option of the voter. 0 for the first option, 1 for the second option.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelMediaViewerResponse
     */
    public function votePollStory(
        $storyId,
        $pollId,
        $votingOption)
    {
        if (($votingOption !== 0) && ($votingOption !== 1)) {
            throw new \InvalidArgumentException('You must provide a valid value for voting option.');
        }

        return $this->ig->request("media/{$storyId}/{$pollId}/story_poll_vote/")
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('radio_type', $this->ig->radio_type)
            ->addPost('vote', $votingOption)
            ->addPost('delivery_class', 'organic')
            ->addPost('container_module', 'reel_profile')
            ->getResponse(new Response\ReelMediaViewerResponse());
    }

    /**
     * Vote on a story slider.
     *
     * Note that once you vote on a story poll, you cannot change your vote.
     *
     *
     * @param string $storyId      The story media item's ID in Instagram's internal format (ie "1542304813904481224_6112344004").
     * @param string $sliderId     The slider ID in Instagram's internal format (ie "17956159684032257").
     * @param float  $votingOption Value that represents the voting option of the voter. Should be a float from 0 to 1 (ie "0.25").
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelMediaViewerResponse
     */
    public function voteSliderStory(
        $storyId,
        $sliderId,
        $votingOption)
    {
        if ($votingOption < 0 || $votingOption > 1) {
            throw new \InvalidArgumentException('You must provide a valid value from 0 to 1 for voting option.');
        }

        return $this->ig->request("media/{$storyId}/{$sliderId}/story_slider_vote/")
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('radio_type', $this->ig->radio_type)
            ->addPost('vote', $votingOption)
            ->addPost('delivery_class', 'organic')
            ->addPost('container_module', 'profile')
            ->getResponse(new Response\ReelMediaViewerResponse());
    }

    /**
     * Vote on a story quiz.
     *
     * Note that once you vote on a story quiz, you cannot change your vote.
     *
     *
     * @param string $storyPk       The story media item's PK in Instagram's internal format (ie "3482384834").  
     * @param string $quizId        The quiz ID in Instagram's internal format (ie "17956159684032257").    
     * @param int    $votingOption  Value that represents the voting option of the voter. Should be a float from 0 to 3.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelMediaViewerResponse
     */
    public function voteQuizStory(
        $storyPk,
        $quizId,
        $votingOption)
    {
        if ($votingOption < 0 || $votingOption > 3) {
            throw new \InvalidArgumentException('You must provide a valid value from 0 to 3 for voting quiz.');
        }

        return $this->ig->request("media/{$storyPk}/{$quizId}/story_quiz_answer/")
            ->setSignedPost(false)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('answer', $votingOption)
            ->addPost('delivery_class', 'organic')
            ->addPost('container_module', 'profile')
            ->getResponse(new Response\ReelMediaViewerResponse());
    }

    /**
     * Get the list of users who have voted an option in a story poll.
     *
     * Note that this only works for your own story polls. Instagram doesn't
     * allow you to see the results from other people's polls!
     *
     * @param string      $storyId      The story media item's ID in Instagram's internal format (ie "1542304813904481224_6112344004").
     * @param string      $pollId       The poll ID in Instagram's internal format (ie "17956159684032257").
     * @param int         $votingOption Value that represents the voting option of the voter. 0 for the first option, 1 for the second option.
     * @param string|null $maxId        Next "maximum ID", used for pagination.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\StoryPollVotersResponse
     */
    public function getStoryPollVoters(
        $storyId,
        $pollId,
        $votingOption,
        $maxId = null)
    {
        if (($votingOption !== 0) && ($votingOption !== 1)) {
            throw new \InvalidArgumentException('You must provide a valid value for voting option.');
        }

        $request = $this->ig->request("media/{$storyId}/{$pollId}/story_poll_voters/")
            ->addParam('vote', $votingOption);

        if ($maxId !== null) {
            $request->addParam('max_id', $maxId);
        }

        return $request->getResponse(new Response\StoryPollVotersResponse());
    }

    /**
     * Respond to a question sticker on a story.
     *
     * @param string $storyId      The story media item's ID in Instagram's internal format (ie "1542304813904481224_6112344004").
     * @param string $questionId   The question ID in Instagram's internal format (ie "17956159684032257").
     * @param string $responseText The text to respond to the question with. (Note: Android App limits this to 94 characters).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function answerStoryQuestion(
        $storyId,
        $questionId,
        $responseText)
    {
        $mutationToken = rand(6709511111111111111, 6709599999999999999);
        return $this->ig->request("media/{$storyId}/{$questionId}/story_question_response/")
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('response', $responseText)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('type', 'text')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('delivery_class', 'organic')
            ->addPost('client_context', $mutationToken)
            ->addPost('mutation_token', $mutationToken)
            ->addPost('container_module', 'profile')
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get all responses of a story question.
     *
     * @param string      $storyId    The story media item's ID in Instagram's internal format (ie "1542304813904481224_6112344004").
     * @param string      $questionId The question ID in Instagram's internal format (ie "17956159684032257").
     * @param string|null $maxId      Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\StoryAnswersResponse
     */
    public function getStoryAnswers(
         $storyId,
         $questionId,
         $maxId = null)
    {
        $request = $this->ig->request("media/{$storyId}/{$questionId}/story_question_responses/");

        if ($maxId !== null) {
            $request->addParam('max_id', $maxId);
        }

        return $request->getResponse(new Response\StoryAnswersResponse());
    }

    /**
     * Deletes an answer to a story question.
     *
     * Note that you must be the owner of the story
     * to delete an answer!
     *
     * @param string $storyId  The story media item's ID in Instagram's internal format (ie "1542304813904481224").
     * @param string $answerId The question ID in Instagram's internal format (ie "17956159684032257").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function deleteStoryQuestionAnswer(
        $storyId,
        $answerId)
    {
        return $this->ig->request("media/{$storyId}/delete_story_question_response/")
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('question_id', $answerId)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Gets the created story countdowns of the current account.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\StoryCountdownsResponse
     */
    public function getStoryCountdowns()
    {
        return $this->ig->request('media/story_countdowns/')
            ->getResponse(new Response\StoryCountdownsResponse());
    }

    /**
     * Follows a story countdown to subscribe to a notification when the countdown is finished.
     *
     * @param string $countdownId The countdown ID in Instagram's internal format (ie "17956159684032257").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function followStoryCountdown(
        $countdownId)
    {
        return $this->ig->request("media/{$countdownId}/follow_story_countdown/")
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Unfollows a story countdown to unsubscribe from a notification when the countdown is finished.
     *
     * @param string $countdownId The countdown ID in Instagram's internal format (ie "17956159684032257").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function unfollowStoryCountdown(
        $countdownId)
    {
        return $this->ig->request("media/{$countdownId}/unfollow_story_countdown/")
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\GenericResponse());
    }

     /**
     * Respond to a quiz sticker on a story.
     *
     * Note that once you vote on a story quiz, you cannot change your vote.
     *
     * @param string $storyId        The story media item's ID in Instagram's internal format (ie "1542304813904481224_6112344004").
     * @param string $quizId         The quiz ID in Instagram's internal format (ie "17956159684032257").
     * @param int    $selectedOption The option you select (Can be 0, 1, 2, 3).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function answerStoryQuiz(
        $storyId,
        $quizId,
        $selectedOption)
    {
        return $this->ig->request("media/{$storyId}/{$quizId}/story_quiz_answer/")
            ->setSignedPost(false)
            ->addPost('answer', $selectedOption)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get all responses of a story quiz.
     *
     * @param string      $storyId The story media item's ID in Instagram's internal format (ie "1542304813904481224_6112344004").
     * @param string      $quizId  The question ID in Instagram's internal format (ie "17956159684032257").
     * @param string|null $maxId   Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\StoryQuizAnswersResponse
     */
    public function getStoryQuizAnswers(
        $storyId,
        $quizId,
        $maxId = null)
    {
        $request = $this->ig->request("media/{$storyId}/{$quizId}/story_quiz_participants/");

        if ($maxId !== null) {
            $request->addParam('max_id', $maxId);
        }

        return $request->getResponse(new Response\StoryQuizAnswersResponse());
    }

    /**
     * Get list of charities for use in the donation sticker on stories.
     *
     * @param string|null $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CharitiesListResponse
     */
    public function getCharities(
        $maxId = null)
    {
        $request = $this->ig->request('fundraiser/story_charities_nullstate/');

        if ($maxId !== null) {
            $request->addParam('max_id', $maxId);
        }

        return $request->getResponse(new Response\CharitiesListResponse());
    }

    /**
     * Searches a list of charities for use in the donation sticker on stories.
     *
     * @param string      $query Search query.
     * @param string|null $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CharitiesListResponse
     */
    public function searchCharities(
        $query,
        $maxId = null)
    {
        $request = $this->ig->request('fundraiser/story_charities_search/')
            ->addParam('query', $query);

        if ($maxId !== null) {
            $request->addParam('max_id', $maxId);
        }

        return $request->getResponse(new Response\CharitiesListResponse());
    }

    /**
     * Creates the array for a donation sticker.
     *
     * @param \InstagramAPI\Response\Model\User $charityUser          The User object of the charity's Instagram account.
     * @param float                             $x
     * @param float                             $y
     * @param float                             $width
     * @param float                             $height
     * @param float                             $rotation
     * @param string|null                       $title                The title of the donation sticker.
     * @param string                            $titleColor           Hex color code for the title color.
     * @param string                            $subtitleColor        Hex color code for the subtitle color.
     * @param string                            $buttonTextColor      Hex color code for the button text color.
     * @param string                            $startBackgroundColor
     * @param string                            $endBackgroundColor
     *
     * @return array
     *
     * @see Story::getCharities()
     * @see Story::searchCharities()
     * @see Story::uploadPhoto()
     * @see Story::uploadVideo()
     */
    public function createDonateSticker(
        $charityUser,
        $x = 0.5,
        $y = 0.5,
        $width = 0.6805556,
        $height = 0.254738,
        $rotation = 0.0,
        $title = null,
        $titleColor = '#000000',
        $subtitleColor = '#999999ff',
        $buttonTextColor = '#3897f0',
        $startBackgroundColor = '#fafafa',
        $endBackgroundColor = '#fafafa')
    {
        return [
            [
                'x'                      => $x,
                'y'                      => $y,
                'z'                      => 0,
                'width'                  => $width,
                'height'                 => $height,
                'rotation'               => $rotation,
                'title'                  => ($title !== null ? strtoupper($title) : ('HELP SUPPORT '.strtoupper($charityUser->getFullName()))),
                'ig_charity_id'          => $charityUser->getPk(),
                'title_color'            => $titleColor,
                'subtitle_color'         => $subtitleColor,
                'button_text_color'      => $buttonTextColor,
                'start_background_color' => $startBackgroundColor,
                'end_background_color'   => $endBackgroundColor,
                'source_name'            => 'sticker_tray',
                'user'                   => [
                    'username'                      => $charityUser->getUsername(),
                    'full_name'                     => $charityUser->getFullName(),
                    'profile_pic_url'               => $charityUser->getProfilePicUrl(),
                    'profile_pic_id'                => $charityUser->getProfilePicId(),
                    'has_anonymous_profile_picture' => $charityUser->getHasAnonymousProfilePicture(),
                    'id'                            => $charityUser->getPk(),
                    'usertag_review_enabled'        => false,
                    'mutual_followers_count'        => $charityUser->getMutualFollowersCount(),
                    'show_besties_badge'            => false,
                    'is_private'                    => $charityUser->getIsPrivate(),
                    'allowed_commenter_type'        => 'any',
                    'is_verified'                   => $charityUser->getIsVerified(),
                    'is_new'                        => false,
                    'feed_post_reshare_disabled'    => false,
                ],
                'is_sticker' => true,
            ],
        ];
    }

    /**
     * Mark story media items as seen.
     *
     * The various story-related endpoints only give you lists of story media.
     * They don't actually mark any stories as "seen", so the user doesn't know
     * that you've seen their story. Actually marking the story as "seen" is
     * done via this endpoint instead. The official app calls this endpoint
     * periodically (with 1 or more items at a time) while watching a story.
     *
     * Tip: You can pass in the whole "getItems()" array from a user's story
     * feed (retrieved via any of the other story endpoints), to easily mark
     * all of that user's story media items as seen.
     *
     * WARNING: ONLY USE *THIS* ENDPOINT IF THE STORIES CAME FROM THE ENDPOINTS
     * IN *THIS* REQUEST-COLLECTION FILE: From "getReelsTrayFeed()" or the
     * user-specific story endpoints. Do NOT use this endpoint if the stories
     * came from any OTHER request-collections, such as Location-based stories!
     * Other request-collections have THEIR OWN special story-marking functions!
     *
     * @param Response\Model\Item[] $items Array of one or more story media Items.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaSeenResponse
     *
     * @see Location::markStoryMediaSeen()
     * @see Hashtag::markStoryMediaSeen()
     */
    public function markMediaSeen(
        array $items,
        $validateStories = false)
    {
        // NOTE: NULL = Use each item's owner ID as the "source ID".
        return $this->ig->internal->markStoryMediaSeen($items, null, 'profile', $validateStories);
    }

    /**
     * Get your story settings.
     *
     * This has information such as your story messaging mode (who can reply
     * to your story), and the list of users you have blocked from seeing your
     * stories.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelSettingsResponse
     */
    public function getReelSettings()
    {
        return $this->ig->request('users/reel_settings/')
            ->getResponse(new Response\ReelSettingsResponse());
    }

    /**
     * Set your story settings.
     *
     * @param string      $messagePrefs      Who can reply to your story. Valid values are "anyone" (meaning
     *                                       your followers), "following" (followers that you follow back),
     *                                       or "off" (meaning that nobody can reply to your story).
     * @param bool|null   $allowStoryReshare Allow story reshare.
     * @param string|null $autoArchive       Auto archive stories for viewing them later. It will appear in your
     *                                       archive once it has disappeared from your story feed. Valid values
     *                                       "on" and "off".
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelSettingsResponse
     */
    public function setReelSettings(
        $messagePrefs,
        $allowStoryReshare = null,
        $autoArchive = null)
    {
        if (!in_array($messagePrefs, ['anyone', 'following', 'off'])) {
            throw new \InvalidArgumentException('You must provide a valid message preference value.');
        }

        $request = $this->ig->request('users/set_reel_settings/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('message_prefs', $messagePrefs);

        if ($allowStoryReshare !== null) {
            if (!is_bool($allowStoryReshare)) {
                throw new \InvalidArgumentException('You must provide a valid value for allowing story reshare.');
            }
            $request->addPost('allow_story_reshare', $allowStoryReshare);
        }

        if ($autoArchive !== null) {
            if (!in_array($autoArchive, ['on', 'off'])) {
                throw new \InvalidArgumentException('You must provide a valid value for auto archive.');
            }
            $request->addPost('reel_auto_archive', $autoArchive);
        }

        return $request->getResponse(new Response\ReelSettingsResponse());
    }

     /**
     * Validate story URL
     * 
     * @param string $url Link that will be validated by Instagram server       
     * 
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function validateStoryURL(
        $url)
    {
        if ($url == null) {
            throw new \InvalidArgumentException('You must provide a valid story url for validation.');
        }

        return $this->ig->request("media/validate_reel_url/")
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('url', $url)
            ->getResponse(new Response\GenericResponse());
    }

    /**  
     * Refresh stories feed (web API)
     *
     * @param $queryhash hash for query.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * 
     * @return \InstagramAPI\Response\GenericResponse
     *
     */
    public function getRefreshStory(
        $query_hash = "24a36f49b32dea22e33c2e6e35bad4d3") 
    {
        $request = $this->ig->request("graphql/query/")
            ->setVersion(5)
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->setIsBodyCompressed(false)
            ->addHeader('Referer', 'https://www.instagram.com/')
            ->addParam('query_hash', $query_hash)
            ->addParam('variables', json_encode([
                "only_stories" => true,
                "stories_prefetch" => false,
                "stories_video_dash_manifest" => false
            ]));
        return $request->getResponse(new Response\GenericResponse());
    }

    /**  
     * Get User story reel (web API)
     *
     * @param $queryhash hash for query.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * 
     * @return \InstagramAPI\Response\GenericResponse
     *
     */
    public function getUserStoryReelGraph(
        $userID,
        $query_hash = "d4d88dc1500312af6f937f7b804c68c3") 
    {
        $request = $this->ig->request("graphql/query/")
            ->setVersion(5)
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->setIsBodyCompressed(false)
            ->addHeader('Referer', 'https://www.instagram.com/')
            ->addParam('query_hash', $query_hash)
            ->addParam('variables', json_encode([
                "user_ids" => $userID,
                "include_chaining" => true,
                "include_reel" => true,
                "include_suggested_users" => true,
                "include_logged_out_extras" => false,
                "include_highlight_reels" => true,
                "include_live_status" => true
            ]));
            return $request->getResponse(new Response\GenericResponse());
    }

    /**
     * Vote on a story poll On Web.
     *
     * Note that once you vote on a story poll, you cannot change your vote.
     *
     * @param string $storyId      The story media item's ID in Instagram's internal format (ie "1542304813904481224_6112344004").
     * @param string $pollId       The poll ID in Instagram's internal format (ie "17956159684032257").
     * @param int    $votingOption Value that represents the voting option of the voter. 0 for the first option, 1 for the second option.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelMediaViewerResponse
     */
    public function votePollStoryWeb(
        $storyId,
        $pollId,
        $votingOption,
        $rollout_hash)
    {    
        if (($votingOption !== 0) && ($votingOption !== 1)) {
            throw new \InvalidArgumentException('You must provide a valid value for voting option.');
        }

        $request = $this->ig->request("https://www.instagram.com/media/{$storyId}/{$pollId}/story_poll_vote/")
            ->setAddDefaultHeaders(false)
            ->setSignedPost(false)
            ->setIsBodyCompressed(false)
            ->addHeader('X-CSRFToken', $this->ig->client->getToken())
            ->addHeader('Referer', 'https://www.instagram.com/')
            ->addHeader('X-Requested-With', 'XMLHttpRequest')
            ->addHeader('X-Instagram-AJAX', $rollout_hash)
            ->addHeader('X-IG-App-ID', Constants::IG_WEB_APPLICATION_ID);
            if ($this->ig->getIsAndroid()) {
                $request->addHeader('User-Agent', sprintf('Mozilla/5.0 (Linux; Android %s; Google) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Mobile Safari/537.36', $this->ig->device->getAndroidRelease()));
            } else {
                $request->addHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS ' . Constants::IOS_VERSION . ' like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.4 Mobile/15E148 Safari/604.1');
            }
            $request->addPost('vote', $votingOption);

        return $request->getResponse(new Response\GenericResponse());
    }
}
