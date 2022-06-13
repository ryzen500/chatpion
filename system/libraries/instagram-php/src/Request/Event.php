<?php

namespace InstagramAPI\Request;

use InstagramAPI\Constants;
use InstagramAPI\Signatures;

/**
 * Functions related to Instagram's logging events.
 */
class Event extends RequestCollection
{
    /**
     * Adds the main body information to the batch data.
     *
     * @param array $batch Batch data.
     *
     * @return array
     */
    protected function _addBatchBody(
        $batch)
    {
        $body =
        [
            'seq'               => $this->ig->batchIndex,
            'app_id'            => Constants::FACEBOOK_ANALYTICS_APPLICATION_ID,
            'device_id'         => $this->ig->uuid,
            'family_device_id'  => $this->ig->phone_id,
            'session_id'        => $this->ig->client->getPigeonSession(),
            'conn'              => Constants::X_IG_Connection_Type,
            'uid'               => 0,
            'channel'           => 'regular',
            'log_type'          => 'client_event',
            'app_uid'           => $this->ig->account_id,
            'claims'            => [
                empty($this->ig->settings->get('x_ig_www_claim')) ? 0 : $this->ig->settings->get('x_ig_www_claim')
            ],
            'config_version'    => 'v2',
            'config_checksum'   => empty($this->ig->settings->get('checksum')) ? '' : $this->ig->settings->get('checksum'),
            'data'              => $batch,
        ];

        if ($this->ig->getIsAndroid()) {
            $body['app_ver'] = Constants::IG_VERSION;
            $body['build_num'] = Constants::VERSION_CODE;
            $body['os_ver'] = $this->ig->device->getAndroidVersion();
            $body['carrier'] = 'Android';
        } else {
            $body['app_ver'] = Constants::IG_IOS_VERSION;
            $body['build_num'] = Constants::IG_IOS_VERSION_CODE;
            $body['os_ver'] = Constants::IOS_VERSION;
            $body['carrier'] = 'iOS';
        }

        return $body;
    }

    /**
     * Adds common properties to the event.
     *
     * @param array $array Graph QL event.
     * @param mixed $event
     *
     * @return array
     */
    protected function _addCommonProperties(
        $event)
    {
        $commonProperties =
        [
            'pk'                        => $this->ig->account_id,
            'release_channel'           => 'prod',
            'radio_type'                => $this->ig->getRadioType(),
        ];

        return array_merge($commonProperties, $event);
    }

    /**
     * Adds event body.
     *
     * @param string $name   Name of the event.
     * @param string $module Module name.
     * @param array  $extra  The event data.
     *
     * @return array
     */
    protected function _addEventBody(
        $name,
        $module,
        $extra)
    {
        $event =
        [
            'name'          => $name,
            'time'          => number_format(microtime(true), 3, ".", ""),
            'sampling_rate' => 1,
            $this->getTagsForNameAndModule($name, $module),
            'extra'         => $extra,
        ];

        if ($module !== null) {
            $event['module'] = $module;
        }

        return $event;
    }

    /**
     * Return if tags property is used for the event.
     *
     * @param array $event Batch data.
     */
    protected function getTagsForNameAndModule($name, $module) {
      return
        ($name === 'explore_home_impression' && $module === 'explore_popular'
           || $name === 'instagram_organic_impression' && $module === 'feed_contextual_profile'
           || $name === 'instagram_organic_impression' && $module === 'feed_contextual_chain'
           || $name === 'instagram_organic_impression' && $module === 'feed_timeline'
           || $name === 'instagram_organic_time_spent' && $module === 'feed_contextual_profile'
           || $name === 'instagram_organic_time_spent' && $module === 'feed_contextual_chain'
           || $name === 'instagram_organic_time_spent' && $module === 'feed_timeline'
           || $name === 'instagram_organic_viewed_impression' && $module === 'feed_contextual_profile'
           || $name === 'instagram_organic_viewed_impression' && $module === 'feed_contextual_chain'
           || $name === 'instagram_organic_viewed_impression' && $module === 'feed_timeline'
           || $name === 'instagram_wellbeing_warning_system_success_creation' && $module === 'comments_v2'
           || $name === 'android_string_impressions' && $module === 'IgResourcesAnalyticsModule') ?
        [
          'tags'    => ($name === 'android_string_impressions' || $name === 'instagram_wellbeing_warning_system_success_creation') ? 1 : 32,
        ]
        : array();
    }

    /**
     * Adds event to the event batch and sends it if reached 20 events.
     *
     * @param array $event Batch data.
     */
    protected function _addEventData(
        $event)
    {
        $this->ig->eventBatch[] = $event;

        if (count($this->ig->eventBatch) === 40) {
            $this->_sendBatchEvents();
            $this->ig->eventBatch = [];
            ++$this->ig->batchIndex;
        }
    }

    /**
     * Save pending events for future sessions.
     */
    public function savePendingEvents()
    {
        $this->ig->settings->set('pending_events', json_encode($this->ig->eventBatch));
    }

    /**
     * Force send batch event.
     */
    public function forceSendBatch()
    {
        $this->_sendBatchEvents();
        $this->ig->eventBatch = [];
    }

    /**
     * Sets and updates checksum from Graph API.
     *
     * @param mixed $response
     */
    protected function _updateChecksum(
        $response)
    {
        if (!empty($response['checksum'])) {
            $this->ig->settings->set('checksum', $response['checksum']);
        }
    }

    /**
     * Send the generated event batch to Facebook's Graph API.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    protected function _sendBatchEvents()
    {
        $batchFilename = sprintf('%s_%s_regular.batch.gz', Signatures::generateUUID(), $this->ig->batchIndex);
        $batch = json_encode($this->_addBatchBody($this->ig->eventBatch));

        $response = $this->ig->request(Constants::GRAPH_API_URL)
          ->setSignedPost(false)
          ->setNeedsAuth(false)
          ->addHeader('X-IG-APP-ID', Constants::FACEBOOK_ANALYTICS_APPLICATION_ID)
          ->addHeader('X-IG-Connection-Type', 'WIFI')
          ->addFileData(
              'cmsg',
              gzdeflate($batch),
              $batchFilename
            )
          ->addPost('access_token', Constants::FACEBOOK_ANALYTICS_APPLICATION_ID.'|'.Constants::GRAPH_API_ACCESS_TOKEN)
          ->addPost('format', 'json')
          ->addPost('sent_time', time())
          ->addPost('cmethod', 'deflate')
          ->setAddDefaultHeaders(false)
          ->getDecodedResponse();

        $this->_updateChecksum($response);
    }

    /**
     * Send login steps events.
     *
     * 1) log_in_username_focus
     * 2) log_in_password_focus
     * 3) log_in_attempt
     * 4) sim_card_state
     * At this point we call login()
     * 5) log_in
     *
     * @param string $name        Name of the event.
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param int    $currentTime Current time.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendLoginProcedure(
        $name,
        $waterfallId,
        $startTime,
        $currentTime)
    {
        $extra = [
            'start_time'        => $startTime,
            'waterfall_id'      => $waterfallId,
            'os_version'        => $this->ig->device->getAndroidVersion(),
            'elapsed_time'      => $currentTime - $startTime,
            'guid'              => $this->ig->uuid,
            'step'              => 'login',
            'current_time'      => $currentTime,
            'pk'                => '0',
            'release_channel'   => null,
            'radio_type'        => 'wifi-none',
        ];

        if ($name === 'log_in_attempt') {
            $extra['keyboard'] = false;
            $extra['log_in_token'] = $this->ig->username;
        } elseif ($name === 'sim_card_state') {
            $extra['has_permission'] = false;
            $extra['sim_state'] = 'absent';
        } elseif ($name === 'log_in') {
            $extra['instagram_id'] = $this->ig->account_id;
        }

        $event = $this->_addEventBody($name, 'waterfall_log_in', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send funnel registration.
     *
     * TODO: Relative organic time.
     *
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param int    $instanceId  TODO.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendFunnelRegistration(
        $waterfallId,
        $startTime,
        $instanceId)
    {
        $actions =
        [
            [
                'relative_time' => mt_rand(80, 120),
                'name'          => 'landing:step_loaded',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(420, 460),
                'name'          => 'landing:sim_card_state',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'landing:switch_to_log_in',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'landing:step_loaded',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'landing:first_party_token_acquired',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'landing:first_party_token_acquired',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'login:text_field_focus',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'login:text_field_focus',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'login:next_tapped',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'login:sim_card_state',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'login:log_in_success',
                'tag'           => null,
            ],
            [
                'relative_time' => mt_rand(67000, 70000),
                'name'          => 'funnel_end',
                'tag'           => 'explicit',
            ],
        ];

        $extra = [
            'start_time'        => $startTime,
            'waterfall_id'      => $waterfallId,
            'sampling_rate'     => 1,
            'instance_id'       => $instanceId,
            'app_device_id'     => $this->ig->uuid,
            'funnel_id'         => '8539',
            'actions'           => json_encode($actions),
            'tags'              => json_encode(['waterfallId:'.$waterfallId, 'is_not_add_account']),
            'pseudo_end'        => true,
            'name'              => 'IG_REGISTRATION_FUNNEL',
            'pk'                => '0',
            'release_channel'   => null,
            'radio_type'        => 'wifi-none',
        ];

        $event = $this->_addEventBody('ig_funnel_analytics', null, $extra);

        $this->_addEventData($event);
    }

    /**
     * Send Thumbnail impression or thumbnail click.
     *
     * @param string              $type        'instagram_thumbnail_impression' to send a view impression on a thumbnail.
     *                                         'instagram_thumbnail_click' to send a click event on a thumbnail.
     * @param Response\Model\Item $item        The item object.
     * @param string              $module      'profile', 'feed_timeline' or 'feed_hashtag'.
     * @param string|null         $hashtagId   The hashtag ID. Only used when 'feed_hashtag' is used as module.
     * @param string|null         $hashtagName The hashtag name. Only used when 'feed_hashtag' is used as module.
     * @param array               $options     Options to configure the event.
     *                                         'position', string, the media position.
     *                                         'following', string, 'following' or 'not_following'.
     *                                         'feed_type', string, 'top', 'recent'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendThumbnailImpression(
        $type,
        $item,
        $module,
        $hashtagId = null,
        $hashtagName = null,
        array $options = [])
    {
        if ($type !== 'instagram_thumbnail_impression' && $type !== 'instagram_thumbnail_click') {
            throw new \InvalidArgumentException(sprintf('%s is not a valid event name.', $type));
        }

        if ($module === 'profile' || $module === 'self_profile') {
            $extra = [
                'id'                        => $item->getId(),
                'm_pk'                      => $item->getId(),
                'position'                  => isset($options['position']) ? $options['position'] : '["0", "0"]',
                'media_type'                => $item->getMediaType(),
                'entity_type'               => 'user',
                'entity_id'                 => $item->getUser()->getPk(),
                'entity_name'               => $item->getUser()->getUsername(),
                'entity_page_name'          => $item->getUser()->getUsername(),
                'entity_page_id'            => $item->getUser()->getPk(),
                'media_thumbnail_section'   => 'grid',
            ];
        } elseif ($module === 'feed_timeline') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => 'following',
                'inventory_source'          => 'media_or_ad',
                'm_ix'                      => 0,
                'imp_logger_ver'            => 16,
                'is_eof'                    => false,
                'timespent'                 => mt_rand(1, 4),
                'avgViewPercent'            => 1,
                'maxViewPercent'            => 1,
            ];
        } elseif ($module === 'feed_hashtag') {
            if ($hashtagId === null) {
                throw new \InvalidArgumentException('No hashtag ID provided.');
            }
            if ($hashtagName === null) {
                throw new \InvalidArgumentException('No hashtag name provided.');
            }
            $extra = [
                'id'                        => $item->getId(),
                'm_pk'                      => $item->getId(),
                'hashtag_id'                => $hashtagId,
                'hashtag_name'              => $hashtagName,
                'hashtag_follow_status'     => isset($options['following']) ? 'following' : 'not_following',
                'hashtag_feed_type'         => isset($options['feed_type']) ? $options['feed_type'] : 'top',
                'tab_index'                 => 0,
                'source_of_action'          => 'feed_contextual_hashtag',
                'session_id'                => $this->ig->client->getPigeonSession(),
                'media_type'                => $item->getMediaType(),
                'type'                      => 0,
                'section'                   => 0,
                'position'                  => isset($options['position']) ? $options['position'] : '["0","0"]',
            ];
        } else {
            throw new \InvalidArgumentException('Module not supported.');
        }

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody($type, $module, $extra);

        $this->_addEventData($event);
    }

    /**
     * Send organic time spent.
     *
     * This event tells Instagram how much time do you spent on each module.
     *
     * @param Response\Model\Item $item                The item object.
     * @param string              $followingUserStatus Following status. 'following' or 'not_following'.
     * @param string              $timespent           Time spent in milliseconds.
     * @param string              $module              The current module you are. 'feed_contextual_profile',
     *                                                 'feed_contextual_self_profile',
     *                                                 'feed_contextual_chain',
     * @param array               $clusterData         Cluster data used in 'feed_contextual_chain' module.
     *                                                 'feed_position' zero based position of the media in the feed.
     *                                                 'chaining_session_id' UUIDv4.
     *                                                 'topic_cluster_id' 'explore_all:0' (More info on Discover class).
     *                                                 'topic_cluster_title' 'For You' (More info on Discover class).
     *                                                 'topic_cluster_type' 'explore_all' (More info on Discover class).
     *                                                 'topic_cluster_session_id' UUIDv4.
     * @param array|null          $options
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicTimespent(
        $item,
        $followingUserStatus,
        $timespent,
        $module,
        array $clusterData = [],
        array $options = null)
    {
        if ($module === 'feed_contextual_profile' || $module === 'feed_contextual_self_profile' || $module === 'feed_short_url') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => $followingUserStatus,
                'm_ix'                      => 1,
                'timespent'                 => $timespent,
                'avgViewPercent'            => 1,
                'maxViewPercent'            => 1,
                'media_thumbnail_section'   => 'grid',
                'entity_page_name'          => $item->getUser()->getUsername(),
                'entity_page_id'            => $item->getUser()->getPk(),
            ];
        } elseif ($module === 'feed_contextual_chain') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => $followingUserStatus,
                'connection_id'             => '180',
                'imp_logger_ver'            => 16,
                'timespent'                 => $timespent,
                'avgViewPercent'            => 1,
                'maxViewPercent'            => 1,
                'chaining_position'         => $clusterData['feed_position'],
                'chaining_session_id'       => $clusterData['chaining_session_id'],
                'm_ix'                      => 0,
                'topic_cluster_id'          => $clusterData['topic_cluster_id'], // example: 'explore_all:0'
                'topic_cluster_title'       => $clusterData['topic_cluster_title'], // example: 'For You'
                'topic_cluster_type'        => $clusterData['topic_cluster_type'], // example: 'explore_all'
                'topic_cluster_debug_info'	 => null,
                'topic_cluster_session_id'	 => $clusterData['topic_cluster_session_id'],
            ];
        } elseif ($module === 'feed_contextual_hashtag') {
            $extra = [
                'id'                        => $item->getId(),
                'm_pk'                      => $item->getId(),
                'hashtag_id'                => $options['hashtag_id'],
                'hashtag_name'              => $options['hashtag_name'],
                'hashtag_follow_status'     => isset($options['following']) ? 'following' : 'not_following',
                'hashtag_feed_type'         => isset($options['feed_type']) ? $options['feed_type'] : 'top',
                'tab_index'                 => 0,
                'source_of_action'          => $module,
                'timespent'                 => $timespent,
                'session_id'                => $this->ig->client->getPigeonSession(),
                'media_type'                => $item->getMediaType(),
                'type'                      => 0,
                'section'                   => 0,
                'position'                  => isset($options['position']) ? $options['position'] : '["0","0"]',
            ];
        } elseif ($module === 'feed_timeline') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => 'following',
                'inventory_source'          => 'media_or_ad',
                'm_ix'                      => 0,
                'imp_logger_ver'            => 16,
                'is_eof'                    => false,
                'timespent'                 => $timespent,
            ];
        } else {
            throw new \InvalidArgumentException(sprintf('%s module is not supported.'));
        }

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('instagram_organic_time_spent', $module, $extra);

        if ($module === 'feed_contextual_chain') {
            $event['tags'] = 32;
        }

        $this->_addEventData($event);
    }

    /**
     * Send organic reel/story impression.
     *
     * @param Response\Model\Item $item                The item object.
     * @param string              $viewerSessionId     UUIDv4.
     * @param string              $traySessionId       UUIDv4.
     * @param string              $rankingToken        UUIDv4.
     * @param string              $followingUserStatus Following status. 'following' or 'not_following'.
     * @param string              $source              Source of action. 'reel_feed_timeline'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicReelImpression(
        $item,
        $viewerSessionId,
        $traySessionId,
        $rankingToken,
        $followingUserStatus,
        $source = 'reel_feed_timeline')
    {
        $extra = [
            'm_pk'                      => $item->getId(),
            'a_pk'                      => $item->getUser()->getPk(),
            'm_ts'                      => (int) $item->getTakenAt(),
            'm_t'                       => $item->getMediaType(),
            'tracking_token'            => $item->getOrganicTrackingToken(),
            'action'                    => 'webclick',
            'source_of_action'          => $source,
            'follow_status'             => ($source === 'reel_feed_timeline') ? 'following' : $followingUserStatus,
            'viewer_session_id'         => $viewerSessionId,
            'tray_session_id'           => $traySessionId,
            'reel_id'                   => $item->getUser()->getPk(),
            'is_pride_reel'             => false,
            'is_besties_reel'           => false,
            'story_ranking_token'       => $rankingToken,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('instagram_organic_reel_impression', $source, $extra);

        $this->_addEventData($event);
    }

    /**
     * Reel tray refresh.
     *
     * @param array $options    Options.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function reelTrayRefresh(
        $options)
    {
        $extra = [
            'has_my_reel'               => $options['has_my_reel'],
            'has_my_replay_reel'        => $options['has_my_replay_reel'],
            'viewed_reel_count'         => $options['viewed_reel_count'],
            'live_reel_count'           => $options['live_reel_count'],
            'new_replay_reel_count'     => $options['new_replay_reel_count'],
            'viewed_replay_reel_count'  => $options['viewed_replay_reel_count'],
            'muted_reel_count'          => $options['muted_reel_count'],
            'muted_live_reel_count'     => $options['muted_live_reel_count'],
            'muted_replay_reel_count'   => $options['muted_replay_reel_count'],
            'suggested_reel_count'      => $options['suggested_reel_count'],
            'unfetched_reel_count'      => $options['unfetched_reel_count'],
            'tray_refresh_time'         => $options['tray_refresh_time'], // secs with millis. 0.335
            'tray_refresh_type'         => 'disk',
            'tray_refresh_reason'       => 'cold_start',
            'tray_session_id'           => $options['tray_session_id'],
            'was_successful'            => $options['was_successful'],
            'story_ranking_token'       => null,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('reel_tray_refresh', 'feed_timeline', $extra);

        $this->_addEventData($event);
    }

    /**
     * Reel in feed tray hide.
     *
     * @param string $traySessionId UUIDv4.
     * @param string $hideReason    Hide reason.
     * @param string $trayId        Tray ID..
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function reelInFeedTrayHide(
        $traySessionId,
        $hideReason,
        $trayId)
    {
        $extra = [
            'tray_session_id'   => $traySessionId,
            'hide_reason'       => $hideReason,
            'tray_id'           => $trayId,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('reel_in_feed_tray_hide', 'feed_timeline', $extra);

        $this->_addEventData($event);
    }

    /**
     * Main feed request began.
     *
     * @param int    $mediaDepth Medias loaded so far.
     * @param string $reason     Reason.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendStartMainFeedRequest(
        $mediaDepth,
        $reason = 'pagination')
    {
        $extra = [
            'reason'                    => $reason,
            'is_background'             => false,
            'last_navigation_module'    => 'feed_timeline',
            'nav_in_transit'            => false,
            'media_depth'               => $mediaDepth,
            'view_info_count'           => 20,
            'fetch_action'              => 'load_more',
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_main_feed_request_began', 'feed_timeline', $extra);

        $this->_addEventData($event);
    }

    /**
     * Loading more (Pagination) on main feed.
     *
     * @param int $paginationTime Time when requested pagination.
     * @param int $position       Media position when requested pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendMainFeedLoadingMore(
        $paginationTime,
        $position)
    {
        $extra = [
            'position'                  => $position,
            'last_feed_update_time'     => $paginationTime,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('main_feed_loading_more', 'feed_timeline', $extra);

        $this->_addEventData($event);
    }

    /**
     * Main feed request end.
     *
     * @param int    $mediaDepth Medias loaded so far.
     * @param string $reason     Reason.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendEndMainFeedRequest(
        $mediaDepth,
        $reason = 'pagination')
    {
        $extra = [
            'reason'                    => $reason,
            'is_background'             => false,
            'last_navigation_module'    => 'feed_timeline',
            'nav_in_transit'            => false,
            'media_depth'               => $mediaDepth,
            'view_info_count'           => 20,
            'num_of_items'              => 20,
            'interaction_events'        => ['scroll'],
            'new_items_delivered'       => true,
            'request_duration'          => mt_rand(1000, 1500),
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_main_feed_request_succeeded', 'feed_timeline', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send organic like.
     *
     * @param Response\Model\Item $item        The item object.
     * @param string              $module      'profile', 'feed_contextual_hashtag', 'feed_short_url', 'feed_timeline'.
     * @param string|null         $hashtagId   The hashtag ID. Only used when 'feed_contextual_hashtag' is used as module.
     * @param string|null         $hashtagName The hashtag name. Only used when 'feed_contextual_hashtag' is used as module.
     * @param string|null         $sessionId   Timeline session ID.
     * @param array               $options     Options to configure the event.
     *                                         'follow_status', string, 'following' or 'not_following'.
     *                                         'hashtag_follow_status', string, 'following' or 'not_following'.
     *                                         'hashtag_feed_type', string, 'top', 'recent'.
     * @param bool                $unlike      Wether to send organic like or unlike.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicLike(
        $item,
        $module,
        $hashtagId = null,
        $hashtagName = null,
        $sessionId = null,
        array $options = [],
        $unlike = false)
    {
        if ($module === 'feed_contextual_profile' || $module === 'profile' || $module === 'feed_short_url') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'session_id'                => $sessionId,
                'source_of_action'          => $module,
                'follow_status'             => isset($options['follow_status']) ? $options['follow_status'] : 'not_following',
                'm_ix'                      => isset($options['m_ix']) ? $options['m_ix'] : 7,
                'source_of_like'            => isset($options['source_of_like']) ? $options['source_of_like'] : 'button',
                'entity_page_id'            => $item->getUser()->getPk(),
                'entity_page_name'          => $item->getUser()->getUsername(),
                'media_thumbnail_section'   => 'grid',
            ];
        } elseif ($module === 'feed_contextual_hashtag') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => isset($options['following']) ? 'following' : 'not_following',
                'm_ix'                      => 30, // ?
                'source_of_like'            => isset($options['source_of_like']) ? $options['source_of_like'] : 'button',
                'hashtag_follow_status'     => isset($options['hashtag_follow']) ? 'following' : 'not_following',
                'hashtag_feed_type'         => isset($options['feed_type']) ? $options['feed_type'] : 'top',
                'tab_index'                 => 0,
                'hashtag_id'                => $hashtagId,
                'hashtag_name'              => $hashtagName,
            ];
        } elseif ($module === 'feed_timeline') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'session_id'                => $sessionId,
                'source_of_action'          => $module,
                'follow_status'             => 'following',
                'm_ix'                      => isset($options['m_ix']) ? $options['m_ix'] : 2, // ?
                'inventory_source'          => 'media_or_ad',
                'source_of_like'            => isset($options['source_of_like']) ? $options['source_of_like'] : 'button',
                'is_eof'                    => false,
            ];
        } elseif ($module === 'feed_contextual_chain') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => isset($options['following']) ? 'following' : 'not_following',
                'connection_id'             => '180',
                'imp_logger_ver'            => 16,
                'timespent'                 => $options['timespent'],
                'avgViewPercent'            => 1,
                'maxViewPercent'            => 1,
                'chaining_position'         => $options['feed_position'],
                'chaining_session_id'       => $options['chaining_session_id'],
                'm_ix'                      => 0,
                'topic_cluster_id'          => $options['topic_cluster_id'], // example: 'explore_all:0'
                'topic_cluster_title'       => $options['topic_cluster_title'], // example: 'For You'
                'topic_cluster_type'        => $options['topic_cluster_type'], // example: 'explore_all'
                'topic_cluster_debug_info'	 => null,
                'topic_cluster_session_id'	 => $options['topic_cluster_session_id'],
            ];
        } else {
            throw new \InvalidArgumentException('Module not supported.');
        }

        if ($unlike === false) {
            $name = 'instagram_organic_like';
        } else {
            $name = 'instagram_organic_unlike';
        }

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody($name, $module, $extra);

        $this->_addEventData($event);
    }

    /**
     * Send organic comment.
     *
     * NOTE: After using this event you need to send comment impression on your own comment.
     *       Use sendCommentImpression().
     *
     * @param Response\Model\Item $item            The item object.
     * @param bool                $isFollowingUser If you are following the user that owns the media.
     * @param int                 $composeDuration The time in milliseconds it took to compose the comment.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendOrganicComment(
        $item,
        $isFollowingUser,
        $composeDuration)
    {
        $extra = [
            'm_pk'                      => $item->getId(),
            'a_pk'                      => $item->getUser()->getPk(),
            'm_ts'                      => (int) $item->getTakenAt(),
            'm_t'                       => $item->getMediaType(),
            'tracking_token'            => $item->getOrganicTrackingToken(),
            'source_of_action'          => 'comments_v2',
            'follow_status'             => $isFollowingUser ? 'following' : 'not_following',
            'comment_compose_duration'  => $composeDuration,
            'media_thumbnail_section'   => 'grid',
            'entity_page_name'          => $item->getUser()->getUsername(),
            'entity_page_id'            => $item->getUser()->getPk(),
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('instagram_organic_comment', 'comments_v2', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send organic comment like.
     *
     * @param Response\Model\Item $item        The item object.
     * @param string              $userId      User ID of account who made the comment in Instagram's internal format.
     * @param string              $commentId   Comment ID in Instagram's internal format.
     * @param string              $sessionId   UUID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendOrganicCommentLike(
        $item,
        $userId,
        $commentId,
        $sessionId)
    {
        $extra = [
            'm_pk'                      => $item->getId(),
            'a_pk'                      => $item->getUser()->getPk(),
            'm_ts'                      => (int) $item->getTakenAt(),
            'm_t'                       => $item->getMediaType(),
            'c_pk'                      => $commentId,
            'ca_pk'                     => $userId,
            'inventory_source'          => null,
            'is_media_organic'          => true,
            'session_id'                => $sessionId,
            'm_x'                       => 0
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('instagram_organic_comment_like', 'comments_v2', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send comment create.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendCommentCreate()
    {
        $extra = [
            'source_of_action'  => 'comment_create',
            'text_language'     => null,
            'is_offensive'      => false,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('instagram_wellbeing_warning_system_success_creation', 'comments_v2', $extra);
        $event['tags'] = 1;

        $this->_addEventData($event);
    }

    /**
     * Send comment impression.
     *
     * Whenever you see a comment, a comment impression is sent.
     *
     * @param Response\Model\Item $item             The post item object.
     * @param string              $userId           User ID of account who made the comment in Instagram's internal format.
     * @param string              $commentId        Comment ID in Instagram's internal format.
     * @param int                 $commentLikeCount The number of likes the comment has.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendCommentImpression(
        $item,
        $userId,
        $commentId,
        $commentLikeCount)
    {
        $extra = [
            'm_pk'              => $item->getId(),
            'a_pk'              => $item->getUser()->getPk(),
            'c_pk'              => $commentId,
            'like_count'        => $commentLikeCount,
            'ca_pk'             => $userId,
            'is_media_organic'  => true,
            'imp_logger_ver'    => 16,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('comment_impression', 'comments_v2', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send live comment impression.
     *
     * Whenever you see a live comment, a comment impression is sent.
     *
     * @param Response\Model\Item $comment_item      The comment item object.
     * @param string              $commentId         Comment ID in Instagram's internal format.
     * @param string              $broadcastId       Broadcast ID in Instagram's internal format.
     * @param string              $broadcastOwnerId  Broadcast Owner ID in Instagram's internal format.
     * @param string              $module            'profile', 'reel_feed_timeline', 'feed_short_url', 'feed_contextual_profile'...
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendLiveCommentImpression(
        $comment_item,
        $broadcastId,
        $broadcastOwnerId,
        $module = 'live_feed_timeline') 
    {
        $extra = [
            'm_pk'              => $comment_item->getMediaId() . "_" . $broadcastOwnerId,
            'a_pk'              => $broadcastOwnerId,
            'c_pk'              => number_format(microtime(true), 3, ".", ""),
            'ca_pk'             => $comment_item->getUser()->getPk(),
            'broadcast_id'      => $broadcastId,
            'comment_type' => 'normal',
        ]; 

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_live_comment_impression', $module, $extra);

        $this->_addEventData($event);
    }

    /**
     * Send live reaction
     *
     * Whenever you post comment in live, a live comment reaction is sent.
     *
     * @param Response\Model\Item $comment_item      The comment item object.
     * @param string              $broadcastId       Broadcast ID in Instagram's internal format.
     * @param string              $broadcastOwnerId  Broadcast Owner ID in Instagram's internal format.
     * @param string              $module            'profile', 'reel_feed_timeline', 'feed_short_url', 'feed_contextual_profile'...
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendLiveReaction(
        $comment_item,
        $broadcastId,
        $broadcastOwnerId,
        $module = 'live_feed_timeline')
    {
        $extra = [
            'm_pk'              => $comment_item->getMediaId() . "_" . $broadcastOwnerId,
            'a_pk'              => $broadcastOwnerId,
            'broadcast_id'      => $broadcastId,
            "is_live_streaming" => 1,
            "reaction_type"     => "comment"
        ]; 

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_live_reaction', $module, $extra);

        $this->_addEventData($event);
    }

    /**
     * Send profile action.
     *
     * @param string $action   'follow', 'unfollow', 'tap_follow_sheet', 'mute_feed_posts',
     *                         'unmute_feed_posts', 'mute_stories', 'unmute_stories'.
     * @param string $userId   User ID in Instagram's internal format.
     * @param array  $navstack Array to tell Instagram how we reached the user profile.
     *                         You should set your own navstack. As an example it is added
     *                         a navstack that emulates going from feed_timeline to the explore module,
     *                         search for a user and click on the result.
     * @param bool   $options  Options.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendProfileAction(
        $action,
        $userId,
        $navstack,
        array $options = [])
    {
        $actions = [
            'follow',
            'unfollow',
            'tap_follow_sheet',
            'mute_feed_posts',
            'unmute_feed_posts',
            'mute_stories',
            'unmute_stories',
            'tap_grid_post',
        ];

        if (!in_array($action, $actions)) {
            throw new \InvalidArgumentException(sprintf('%s action is not valid.', $action));
        }

        $extra = [
            'action'            => $action,
            'profile_user_id'   => $userId,
            'navstack'          => json_encode($navstack),
        ];

        if ($action === 'follow') {
            $extra['follow_status'] = 'not_following';
            $extra['click_point'] = 'button_tray';
            $module = 'profile';
        } elseif ($action === 'unfollow') {
            $extra['follow_status'] = 'following';
            $extra['click_point'] = 'button_tray';
            $module = 'profile';
        } elseif ($action === 'mute_feed_posts' && $action === 'unmute_feed_posts' &&
                $action === 'mute_stories' && $action === 'unmute_stories') {
            $extra['follow_status'] = 'following';
            $extra['click_point'] = 'following_sheet';
            $module = 'media_mute_sheet';
        } elseif ($action === 'tap_grid_post') {
            $extra['follow_status'] = $options['follow_status'] ? 'following' : 'not_following';
            $extra['media_id_attribution'] = $options['media_id_attribution'];
            $extra['media_tracking_token_attribution'] = $options['media_tracking_token_attribution'];
            $extra['click_point'] = 'grid_tab';
        }

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_profile_action', $module, $extra);

        $this->_addEventData($event);
    }

    /**
     * Send organic media impression.
     *
     * @param Response\Model\Item $item    The item object.
     * @param string              $module  'profile', 'reel_feed_timeline', 'feed_short_url', 'feed_contextual_profile'.
     * @param array               $options Options to configure the event.
     *                                     'following', string, 'following' or 'not_following'.'.
     *                                     'story_ranking_token' UUIDv4. Used on module 'reel_feed_timeline'.
     *                                     'viewer_session_id' UUIDv4. Used on module 'reel_feed_timeline'.
     *                                     'tray_session_id' UUIDv4. Used on module 'reel_feed_timeline'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicMediaImpression(
        $item,
        $module,
        array $options = [])
    {
        if ($module === 'profile' || $module === 'feed_short_url'
            || $module === 'feed_contextual_profile' || $module === 'feed_timeline') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => isset($options['following']) ? 'following' : 'not_following',
                'm_ix'                      => 3, // ?
                'imp_logger_ver'            => 16,
                'is_app_backgrounded'       => 'false',
                'nav_in_transit'            => 0,
            ];
            if ($module === 'feed_short_url' || $module === 'feed_contextual_profile') {
                $extra['media_thumbnail_section'] = 'grid';
                $extra['entity_page_name'] = $item->getUser()->getUsername();
                $extra['entity_page_id'] = $item->getUser()->getPk();
            }
        } elseif ($module === 'reel_feed_timeline' || $module === 'reel_profile') {
            if (!isset($options['story_ranking_token']) && !isset($options['tray_session_id']) && !isset($options['viewer_session_id'])) {
                throw new \InvalidArgumentException('Required options were not set.');
            }
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'action'                    => 'webclick',
                'source_of_action'          => 'reel_feed_timeline',
                'follow_status'             => isset($options['following']) ? $options['following'] : 'not_following',
                'viewer_session_id'         => $options['viewer_session_id'],
                'tray_session_id'           => $options['tray_session_id'],
                'reel_id'                   => $item->getUser()->getPk(),
                'is_pride_reel'             => false,
                'is_besties_reel'           => false,
                'reel_position'             => 0,
                'reel_viewer_position'      => 0,
                'reel_type'                 => 'story',
                'reel_size'                 => 1,
                'tray_position'             => 1,
                'session_reel_counter'      => 1,
                'time_elapsed'              => 0,
                'reel_start_position'       => 0,
                'story_ranking_token'       => $options['story_ranking_token'],
            ];
        } else {
            throw new \InvalidArgumentException('Module not supported.');
        }

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('instagram_organic_impression', $module, $extra);

        $this->_addEventData($event);
    }

    /**
     * Send explore home impression.
     *
     * @param Response\Model\Item $item    The item object.
     * @param array               $options Options to configure the event.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendExploreHomeImpression(
        $item,
        array $options = [])
    {
        $extra = [
            'm_pk'                      => $item->getId(),
            'media_type'                => $item->getMediaType(),
            'event_id'                  => $item->getId(),
            'tracking_token'            => $item->getOrganicTrackingToken(),
            'connection_id'             => isset($options['connection_id']) ? $options['connection_id'] : 180,
            'position'                  => $options['position'], // [\"24\",\"1\"] (row, column).
            'algorithm'                 => isset($options['algorithm']) ? $options['algorithm'] : 'edge_dedupe_unicorn',
            'type'                      => 1,
            'size'                      => $options['size'], // [\"2\",\"2\"] Size in the media grid.
            'topic_cluster_id'          => $options['topic_cluster_id'], // example: 'explore_all:0'
            'topic_cluster_title'       => $options['topic_cluster_title'], // example: 'For You'
            'topic_cluster_type'        => $options['topic_cluster_type'], // example: 'explore_all'
            'topic_cluster_debug_info'	=> null,
        ];

        if ($item->hasMezqlToken()) {
            $extra['mezql_token'] = $item->getMezqlToken();
        }

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('explore_home_impression', 'explore_popular', $extra);
        $event['tags'] = 32;

        $this->_addEventData($event);
    }

    /**
     * Send organic viewed impression.
     *
     * @param Response\Model\Item $item            The item object.
     * @param string              $module          'feed_contextual_profile', 'reel_profile'.
     * @param string              $viewerSessionId UUIDv4.
     * @param string              $traySessionId   UUIDv4.
     * @param string              $rankingToken    UUIDv4.
     * @param array               $options         Options to configure the event.
     *                                             'following', string, 'following' or 'not_following'.'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendOrganicViewedImpression(
        $item,
        $module,
        $viewerSessionId = null,
        $traySessionId = null,
        $rankingToken = null,
        array $options = [])
    {
        if ($module === 'feed_contextual_profile') {
            $event = 'instagram_organic_viewed_impression';
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => isset($options['following']) ? 'following' : 'not_following',
                'm_ix'                      => 17, // ?
                'imp_logger_ver'            => 21,
                'media_thumbnail_section'   => 'grid',
                'entity_page_name'          => $item->getUser()->getUsername(),
                'entity_page_id'            => $item->getUser()->getPk(),
            ];
        } elseif ($module === 'reel_feed_timeline' || $module === 'reel_profile') {
            $event = 'instagram_organic_reel_viewed_impression';
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'action'					               => 'webclick',
                'source_of_action'          => $module,
                'follow_status'             => isset($options['following']) ? 'following' : 'not_following',
                'viewer_session_id'         => $viewerSessionId,
                'tray_session_id'           => $traySessionId,
                'reel_id'                   => $item->getId(),
                'is_pride_reel'             => false,
                'is_besties_reel'           => false,
                'reel_position'             => 0,
                'reel_viewer_position'      => 0,
                'reel_type'                 => 'story',
                'reel_size'                 => 1,
                'tray_position'             => 1,
                'session_reel_counter'      => 1,
                'time_elapsed'              => 0,
                'media_time_elapsed'        => 0,
                'media_time_remaining'      => mt_rand(1, 2),
                'media_dwell_time'          => mt_rand(5, 6) + mt_rand(100, 900) * 0.001,
                'media_time_paused'         => 0,
                'media_time_to_load'        => mt_rand(35, 67) * 0.01,
                'reel_start_position'       => 0,
                'story_ranking_token'       => $rankingToken,
            ];
        } elseif ($module === 'feed_contextual_hashtag') {
            $event = 'instagram_organic_viewed_impression';
            $extra = [
                'id'                        => $item->getId(),
                'm_pk'                      => $item->getId(),
                'hashtag_id'                => $hashtagId,
                'hashtag_name'              => $hashtagName,
                'hashtag_follow_status'     => isset($options['following']) ? 'following' : 'not_following',
                'hashtag_feed_type'         => isset($options['feed_type']) ? $options['feed_type'] : 'top',
                'tab_index'                 => 0,
                'source_of_action'          => $module,
                'session_id'                => $this->ig->client->getPigeonSession(),
                'media_type'                => $item->getMediaType(),
                'type'                      => 0,
                'section'                   => 0,
                'position'                  => isset($options['position']) ? $options['position'] : '["0","0"]',
            ];
        } elseif ($module === 'feed_timeline') {
            $extra = [
                'm_pk'                      => $item->getId(),
                'a_pk'                      => $item->getUser()->getPk(),
                'm_ts'                      => (int) $item->getTakenAt(),
                'm_t'                       => $item->getMediaType(),
                'tracking_token'            => $item->getOrganicTrackingToken(),
                'source_of_action'          => $module,
                'follow_status'             => 'following',
                'inventory_source'          => 'media_or_ad',
                'm_ix'                      => 0,
                'imp_logger_ver'            => 16,
                'is_eof'                    => false,
            ];
        } elseif ($module === 'feed_short_url') {
            $extra = [
                'id'                        => $item->getId(),
                'm_pk'                      => $item->getId(),
                'position'                  => isset($options['position']) ? $options['position'] : '["0", "0"]',
                'media_type'                => $item->getMediaType(),
                'entity_type'               => 'user',
                'entity_name'               => $item->getUser()->getUsername(),
                'entity_page_name'          => $item->getUser()->getUsername(),
                'entity_page_id'            => $item->getUser()->getPk(),
                'media_thumbnail_section'   => 'grid',
            ];
        } else {
            throw new \InvalidArgumentException('Module not supported.');
        }

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody($event, $module, $extra);

        $this->_addEventData($event);
    }

    /**
     * Send video action.
     *
     * @param string              $action  Action to be made. 'video_displayed', 'video_should_start', 'video_buffering_started',
     *                                     'video_started_playing', 'video_paused', 'video_exited'.
     * @param Response\Model\Item $item    The item object.
     * @param string              $module  'feed_contextual_profile'.
     * @param array               $options Options to configure the event.
     *                                     'following', string, 'following' or 'not_following'.'.
     *                                     'viewer_session_id', string. UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendVideoAction(
        $action,
        $item,
        $module,
        array $options = [])
    {
        if ($module === 'feed_contextual_profile') {
            $extra = [
                'm_pk'                        => $item->getId(),
                'a_pk'                        => $item->getUser()->getPk(),
                'm_ts'                        => (int) $item->getTakenAt(),
                'm_t'                         => $item->getMediaType(),
                'tracking_token'              => $item->getOrganicTrackingToken(),
                'source_of_action'            => $module,
                'follow_status'               => isset($options['following']) ? 'following' : 'not_following',
                'm_ix'                        => 14, // ?
                'is_dash_eligible'            => 1,
                'video_codec'                 => $item->getVideoCodec(),
                'playback_format'             => 'dash',
                'a_i'                         => 'organic',
                'imp_logger_ver'              => 21,
            ];
        } elseif ($module === 'feed_short_url') {
            $extra = [
                'id'                        => $item->getId(),
                'm_pk'                      => $item->getId(),
                'position'                  => isset($options['position']) ? $options['position'] : '["0", "0"]',
                'media_type'                => $item->getMediaType(),
                'entity_type'               => 'user',
                'entity_name'               => $item->getUser()->getUsername(),
                'entity_page_name'          => $item->getUser()->getUsername(),
                'entity_page_id'            => $item->getUser()->getPk(),
                'media_thumbnail_section'   => 'grid',
            ];
        } else {
            throw new \InvalidArgumentException('Module not supported.');
        }

        if ($action === 'video_displayed') {
            $extra['initial'] = 1;
            $extra['media_thumbnail_section'] = 'grid';
            $extra['entity_page_name'] = $item->getUser()->getUsername();
            $extra['entity_page_id'] = $item->getUser()->getPk();
        } elseif ($action === 'video_should_start') {
            $extra['reason'] = 'start';
            $extra['viewer_session_id'] = $options['viewer_session_id'];
            $extra['seq_num'] = $options['seq'];
        } elseif ($action === 'video_buffering_started') {
            $extra['reason'] = 'start';
            $extra['viewer_session_id'] = $options['viewer_session_id'];
            $extra['seq_num'] = $options['seq'];
            $extra['time'] = 0;
            $extra['duration'] = $item->getVideoDuration();
            $extra['timeAsPercent'] = 0;
            $extra['playing_audio'] = '0';
            $extra['lsp'] = 0;
            $extra['loop_count'] = 0;
            $extra['video_width'] = 0;
        } elseif ($action === 'video_started_playing') {
            $extra['duration'] = $item->getVideoDuration();
            $extra['playing_audio'] = '0';
            $extra['viewer_session_id'] = $options['viewer_session_id'];
            $extra['seq_num'] = $options['seq'];
            $extra['reason'] = 'autoplay';
            $extra['start_delay'] = mt_rand(100, 500);
            $extra['cached'] = false;
            $extra['warmed'] = false;
            $extra['streaming'] = true;
            $extra['video_width'] = $item->getVideoVersions()[0]->getWidth();
            $extra['video_heigth'] = $item->getVideoVersions()[0]->getHeight();
            $extra['view_width'] = $item->getVideoVersions()[0]->getWidth();
            $extra['view_height'] = $item->getVideoVersions()[0]->getHeight();
            $extra['app_orientation'] = 'portrait';
        } elseif ($action === 'video_paused') {
            $extra['duration'] = $item->getVideoDuration();
            $extra['time'] = $item->getVideoDuration() - mt_rand(1, 3);
            $extra['playing_audio'] = '0';
            $extra['viewer_session_id'] = $options['viewer_session_id'];
            $extra['seq_num'] = $options['seq'];
            $extra['original_start_reason'] = 'autoplay';
            $extra['reason'] = 'fragment_paused';
            $extra['lsp'] = 0;
            $extra['loop_count'] = mt_rand(2, 5);
            $extra['video_width'] = $item->getVideoVersions()[0]->getWidth();
            $extra['view_width'] = $item->getVideoVersions()[0]->getWidth();
            $extra['view_height'] = $item->getVideoVersions()[0]->getHeight();
        }

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody($action, $module, $extra);

        $this->_addEventData($event);
    }

    /**
     * Send profile view.
     *
     * @param string $userId User ID of account who made the comment in Instagram's internal format.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendProfileView(
        $userId)
    {
        $extra = [
                'm_ix'              => 0,
                'carousel_index'    => 0,
                'target_id'         => $userId,
                'actor_id'          => $this->ig->account_id,
            ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('profile_view', 'profile', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send navigation. It tells Instagram how you reached specific modules.
     *
     * Modules: 'login': When performing login.
     *          'profile': User profile.
     *          'self_profile': Self user profile.
     *          'feed_contextual_profile': Feed from user profile.
     *          'feed_contextual_self_profile': Self feed profile.
     *          'feed_contextual_chain': Chained feed from user profile.
     *          'comments_v2': Comments.
     *          'feed_timeline': Main page, feed timeline.
     *          'direct_inbox': Main page on direct.
     *          'direct_thread': When clicking on a thread on direct.
     *          'direct_thread_toggle': When exiting a thread and going to back to direct_inbox.
     *
     * @param string      $clickPoint  Button or context that made the navigation.
     *                                 'cold start': When doing a clean/cold login (no sessions stored) from 'login' to 'feed_timeline'.
     *                                 'on_launch_direct_inbox': clicking on the airplane (direct) icon. from 'feed_timeline' to 'direct_inbox'.
     *                                 'back': when going from 'direct_inbox' to 'feed_timeline'.
     *                                 when going back from 'direct_thread_toggle' to 'direct_inbox'.
     *                                 'button': when going from 'direct_inbox' to 'direct_thread'.
     *                                 when going from the user profile ('profile') to the user feed 'feed_contextual_profile'.
     *                                 when going from the chained feed ('feed_contextual_chain') to the comments module ('comments_v2').
     * @param string      $fromModule  The module you are coming from.
     * @param string      $toModule    The module you are going to.
     * @param string|null $hashtagId   The hashtag ID. Only used when 'feed_hashtag' is used as module.
     * @param string|null $hashtagName The hashtag name. Only used when 'feed_hashtag' is used as module.
     * @param array       $options     Options to configure the event.
     *                                 'user_id' when going from direct_inbox to direct_thread.
     *                                 'topic_cluster_id' (example: 'hashtag_inspired:23') when going from explore_popular to specific topic.
     *                                 'topic_cluster_title' (example: 'Food') when going from explore_popular to specific topic.
     *                                 'topic_cluster_session_id' (UUIDv4) when going from explore_popular to specific topic.
     *                                 'topic_nav_order' (place of the tab, 3 would be for Food, count starts at 1) when going from explore_popular to specific topic.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendNavigation(
        $clickPoint,
        $fromModule,
        $toModule,
        $hashtagId = null,
        $hashtagName = null,
        array $options = [])
    {
        $extra = [
            'click_point'               => $clickPoint,
            'source_module'             => $fromModule,
            'dest_module'               => $toModule,
            'seq'                       => $this->ig->navigationSequence,
            'nav_time_taken'            => mt_rand(200, 350),
        ];

        $navigation = [
            'feed_timeline' => [
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'on_launch_direct_inbox',
                    'dest_module'   => 'direct_inbox',
                ],
                [
                    'clickpoint'    => 'media_owner',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'media_location',
                    'dest_module'   => 'feed_location',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'comments_v2',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'discover_people',
                ],
                [
                    'clickpoint'    => 'media_caption_hashtag',
                    'dest_module'   => 'feed_hashtag',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'likers',
                ],
                [
                    'clickpoint'    => 'main_camera',
                    'dest_module'   => 'tabbed_gallery_camera',
                ],
            ],
            'newsfeed_you' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'main_camera',
                    'dest_module'   => 'tabbed_gallery_camera',
                ],
                [
                    'clickpoint'    => 'on_launch_direct_inbox',
                    'dest_module'   => 'direct_inbox',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'discover_people',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'feed_hashtag',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'self_comments_v2',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'follow_requests',
                ],
            ],
            'tabbed_gallery_camera' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'photo_filter',
                ],
            ],
            'follow_requests' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'newsfeed_you',
                ],
            ],
            'self_profile' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_camera',
                    'dest_module'   => 'tabbed_gallery_camera',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'feed_contextual_self_profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'self_unified_follow_lists',
                ],
            ],
            'feed_contextual_self_profile' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'self_unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'likers',
                ],
            ],
            'self_unified_follow_lists' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'following',
                    'dest_module'   => 'self_unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'followers',
                    'dest_module'   => 'self_unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'self_profile',
                ],
            ],
            'unified_follow_lists' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'following',
                    'dest_module'   => 'unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'followers',
                    'dest_module'   => 'unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'profile',
                ],
            ],
            'reel_composer_preview' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'story_stickers_tray',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'reel_composer_camera',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'self_profile',
                ],
            ],
            'story_stickers_tray' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'reel_composer_preview',
                ],
            ],
            'explore_popular' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'search',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'on_launch_direct_inbox',
                    'dest_module'   => 'direct_inbox',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'feed_contextual_chain',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'explore_topic_load',
                    'dest_module'   => 'explore_popular',
                ],
            ],
            'search' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'blended_search',
                ],
            ],
            'blended_search' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'blended_search',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'feed_hashtag',
                ],
            ],
            'feed_hashtag' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'blended_search',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'feed_contextual_hashtag',
                ],
            ],
            'feed_contextual_chain' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'on_launch_direct_inbox',
                    'dest_module'   => 'direct_inbox',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'media_owner',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'media_location',
                    'dest_module'   => 'feed_location',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'comments_v2',
                ],
                [
                    'clickpoint'    => 'media_caption_hashtag',
                    'dest_module'   => 'feed_hashtag',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'likers',
                ],
            ],
            'profile' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'blended_search',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'feed_contextual_profile',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'unified_follow_lists',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'on_launch_direct_inbox',
                    'dest_module'   => 'direct_inbox',
                ],
                [
                    'clickpoint'    => 'media_location',
                    'dest_module'   => 'feed_location',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'reel_profile',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'likers',
                ],
                [
                   'clickpoint'    => 'back',
                   'dest_module'   => 'feed_contextual_chain',
                ],
            ],
            'feed_contextual_profile' => [
                [
                    'clickpoint'    => 'main_home',
                    'dest_module'   => 'feed_timeline',
                ],
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'on_launch_direct_inbox',
                    'dest_module'   => 'direct_inbox',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'media_location',
                    'dest_module'   => 'feed_location',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'comments_v2',
                ],
                [
                    'clickpoint'    => 'media_caption_hashtag',
                    'dest_module'   => 'feed_hashtag',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'likers',
                ],
            ],
            'likers' => [
                [
                   'clickpoint'    => 'back',
                   'dest_module'   => 'feed_contextual_profile',
                ],
                [
                   'clickpoint'    => 'back',
                   'dest_module'   => 'feed_contextual_self_profile',
                ],
                [
                   'clickpoint'    => 'back',
                   'dest_module'   => 'feed_timeline',
                ],
                [
                   'clickpoint'    => 'back',
                   'dest_module'   => 'feed_contextual_hashtag',
                ],
                [
                   'clickpoint'    => 'back',
                   'dest_module'   => 'feed_contextual_chain',
                ],
            ],
            'comments_v2' => [
                [
                    'clickpoint'    => 'main_search',
                    'dest_module'   => 'explore_popular',
                ],
                [
                    'clickpoint'    => 'main_inbox',
                    'dest_module'   => 'newsfeed_you',
                ],
                [
                    'clickpoint'    => 'main_profile',
                    'dest_module'   => 'self_profile',
                ],
                [
                    'clickpoint'    => 'on_launch_direct_inbox',
                    'dest_module'   => 'direct_inbox',
                ],
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'profile',
                ],
                [
                    'clickpoint'    => 'media_owner',
                    'dest_module'   => 'profile',
                ],
            ],
            'direct_inbox' => [
                [
                    'clickpoint'    => 'button',
                    'dest_module'   => 'direct_thread',
                ],
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'feed_timeline',
                ],
            ],
            'direct_thread' => [
                [
                    'clickpoint'    => 'back',
                    'dest_module'   => 'direct_inbox',
                ],
            ],
            'login' => [
                [
                    'clickpoint'    => 'cold start',
                    'dest_module'   => 'feed_timeline',
                ],
            ],
        ];

        $found = false;
        foreach ($navigation[$fromModule] as $nav) {
            if ($nav['clickpoint'] === $clickPoint && $nav['dest_module'] === $toModule) {
                $found = true;
                break;
            }
        }

        if ($found === false) {
            throw new \InvalidArgumentException('Invalid navigation provided.');
        }

        switch ($fromModule) {
            case 'feed_timeline':
                if ($toModule === 'explore_popular') {
                    $extra['nav_depth'] = 0;
                    $extra['topic_cluster_title'] = $options['topic_cluster_title'];
                    $extra['topic_cluster_id'] = $options['topic_cluster_id'];
                    $extra['topic_cluster_type'] = $options['topic_cluster_type'];
                    $extra['topic_cluster_debug_info'] = null;
                    $extra['topic_cluster_session_id'] = $options['topic_cluster_session_id'];
                    $extra['topic_nav_order'] = $options['topic_nav_order'];
                }
                break;
            case 'direct_thread_toggle':
            case 'tabbed_gallery_camera':
                $extra['nav_depth'] = 0;
                break;
            case 'direct_inbox':
                if ($toModule === 'feed_timeline') {
                    $extra['nav_depth'] = 1;
                } elseif ($toModule === 'direct_thread') {
                    if (!isset($options['user_id'])) {
                        throw new \InvalidArgumentException('User ID not provided.');
                    }
                    $extra['nav_depth'] = 0;
                    $extra['user_id'] = $options['user_id'];
                }
                break;
            case 'profile':
            case 'feed_contextual_chain':
                if ($toModule === 'feed_contextual_profile' || $toModule === 'comments_v2') {
                    $extra['nav_depth'] = 1;
                }
                break;
            case 'self_profile':
            case 'login':
                if ($toModule === 'feed_contextual_self_profile' || $toModule === 'feed_timeline') {
                    $extra['nav_depth'] = 0;
                    $extra['user_id'] = $this->ig->account_id;
                }
                break;
            case 'feed_hashtag':
                if ($toModule === 'feed_contextual_hashtag') {
                    $extra['nav_depth'] = 2;
                    $extra['hashtag_id'] = $hashtagId;
                    $extra['hashtag_name'] = $hashtagName;
                }
                break;
            case 'explore_popular':
                if ($toModule === 'explore_popular') {
                    $extra['nav_depth'] = 0;
                    $extra['topic_cluster_id'] = $options['topic_cluster_id'];
                    $extra['topic_cluster_title'] = $options['topic_cluster_title'];
                    $extra['topic_cluster_session_id'] = $options['topic_cluster_session_id'];
                    $extra['topic_nav_order'] = $options['topic_nav_order'];
                }
                break;
            default:
                break;
        }

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('navigation', $fromModule, $extra);

        ++$this->ig->navigationSequence;

        $this->_addEventData($event);
    }

    /**
     * Open photo camera tab.
     *
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param int    $currentTime Current time. Timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendOpenPhotoCameraTab(
        $waterfallId,
        $startTime,
        $currentTime)
    {
        $extra = [
            'waterfall_id'  => $waterfallId,
            'start_time'    => $startTime,
            'current_time'  => $currentTime,
            'elapsed_time'  => $currentTime - $startTime,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('photo_camera_tab_opened', 'waterfall_capture_flow', $extra);

        $this->_addEventData($event);
    }

    /**
     * Shutter click in camera.
     *
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param int    $currentTime Current time. Timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendShutterClickInCamera(
        $waterfallId,
        $startTime,
        $currentTime)
    {
        $extra = [
            'waterfall_id'  => $waterfallId,
            'start_time'    => $startTime,
            'current_time'  => $currentTime,
            'elapsed_time'  => $currentTime - $startTime,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('shutter_click_in_camera', 'waterfall_capture_flow', $extra);

        $this->_addEventData($event);
    }

    /**
     * Start gallery edit.
     *
     * When you capture a media, Instagram lets you add stickers, mentions...
     * This is when gallery start session starts.
     *
     * @param string $sessionId Session ID. UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendStartGalleryEditSession(
        $sessionId)
    {
        $extra = [
            'ig_userid'     => $this->ig->account_id,
            'session_id'    => $sessionId,
            'event_type'    => 1,
            'entry_point'   => 58,
            'gallery_type'  => 'old_gallery',
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_feed_gallery_start_edit_session', 'ig_creation_client_events', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send filter photo.
     *
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param int    $currentTime Current time. Timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendFilterPhoto(
        $waterfallId,
        $startTime,
        $currentTime)
    {
        $extra = [
            'waterfall_id'  => $waterfallId,
            'start_time'    => $startTime,
            'current_time'  => $currentTime,
            'elapsed_time'  => $currentTime - $startTime,
            'media_source'  => 1,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('filter_photo', 'waterfall_capture_flow', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send filter finish.
     *
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param int    $currentTime Current time. Timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendFilterFinish(
        $waterfallId,
        $startTime,
        $currentTime)
    {
        $extra = [
            'waterfall_id'  => $waterfallId,
            'start_time'    => $startTime,
            'current_time'  => $currentTime,
            'elapsed_time'  => $currentTime - $startTime,
            'filter_id'     => 0,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('filter_finished', 'waterfall_capture_flow', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send Instagram Media Creation.
     *
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param int    $currentTime Current time. Timestamp.
     * @param string $mediaType   Media type. 'photo'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendIGMediaCreation(
        $waterfallId,
        $startTime,
        $currentTime,
        $mediaType)
    {
        $extra = [
            'waterfall_id'  => $waterfallId,
            'start_time'    => $startTime,
            'current_time'  => $currentTime,
            'elapsed_time'  => $currentTime - $startTime,
        ];

        if ($mediaType === 'photo') {
            $extra['step'] = 'edit_photo';
            $extra['next_step'] = 'share_screen';
            $extra['entry_point'] = 'share_button';
        } else {
            throw new \InvalidArgumentException('Invalid media type.');
        }

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_creation_flow_step', 'waterfall_capture_flow_v2', $extra);

        $this->_addEventData($event);
    }

    /**
     * End gallery edit.
     *
     * @param string $sessionId Session ID. UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendEndGalleryEditSession(
        $sessionId)
    {
        $extra = [
            'ig_userid'     => $this->ig->account_id,
            'session_id'    => $sessionId,
            'event_type'    => 1,
            'entry_point'   => 58,
            'gallery_type'  => 'old_gallery',
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_feed_gallery_end_edit_session', 'ig_creation_client_events', $extra);

        $this->_addEventData($event);
    }

    /**
     * Start share session.
     *
     * @param string $sessionId Session ID. UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendStartShareSession(
        $sessionId)
    {
        $extra = [
            'ig_userid'     => $this->ig->account_id,
            'session_id'    => $sessionId,
            'event_type'    => 1,
            'entry_point'   => 58,
            'gallery_type'  => 'old_gallery',
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_feed_gallery_start_share_session', 'ig_creation_client_events', $extra);

        $this->_addEventData($event);
    }

    /**
     * Share media.
     *
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param int    $currentTime Current time. Timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendShareMedia(
        $waterfallId,
        $startTime,
        $currentTime)
    {
        $extra = [
            'waterfall_id'  => $waterfallId,
            'start_time'    => $startTime,
            'current_time'  => $currentTime,
            'elapsed_time'  => $currentTime - $startTime,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('share_media', 'waterfall_capture_flow', $extra);

        $this->_addEventData($event);
    }

    /**
     * Direct. Send intent/attempt of a message.
     *
     * For sending any direct message, first is must be sent the invent,
     * the 'direct_composer_send_text' and finally the attempt.
     *
     * @param string $action        'send_intent', 'send_attempt' or 'sent'.
     * @param string $clientContext Client context used for sending intent/attempt DM.
     * @param string $type          Message type. 'text', 'visual_photo'.
     * @param string $channel       Channel used for sending the intent/attempt DM. Others: 'rest'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function sendDirectMessageIntentOrAttempt(
        $action,
        $clientContext,
        $type,
        $channel = 'realtime')
    {
        if ($action !== 'send_intent' && $action !== 'send_attempt' && $action !== 'sent') {
            throw new \InvalidArgumentException(sprintf('%s is not a valid action.', $action));
        }

        $extra = [
            'action'         => $action,
            'client_context' => $clientContext,
            'type'           => $type,
            'channel'        => $channel,
            'dedupe_token'   => Signatures::generateUUID(),
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('direct_message_waterfall', null, $extra);

        $this->_addEventData($event);
    }

    /**
     * Direct. Send text direct message.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendTextDirectMessage()
    {
        $extra = [];
        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('direct_composer_send_text', 'direct_thread_toggle', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send string impressions.
     *
     * This is used to send Instagram how many times have you seen user IDs while searching in the direct module.
     * TODO: Probably is used for other modules as well. It will be documented as it
     *       discovered.
     *
     * @param array $impressions Impressions. Format: ['2131821003': 4, '2131821257': 2, '2131821331': 10].
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendStringImpressions(
        $impressions)
    {
        $extra = [
            'impressions'   => $impressions,
            'string_locale' => Constants::USER_AGENT_LOCALE,
        ];
        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('android_string_impressions', 'IgResourcesAnalyticsModule', $extra);
        $event['tags'] = 1;

        $this->_addEventData($event);
    }

    /**
     * Send direct user search picker.
     *
     * This event is sent while searching a user. Everytime you type a character, this event is sent.
     * For example: 'I', 'In', 'Ins', 'Inst', 'Insta'. 5 events sent showing the query.
     *
     * If you click on any of the results, you should call after sending all these events, sendDirectUserSearchSelection().
     *
     * @param string $query The query.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendDirectUserSearchPicker(
        $query)
    {
        $extra = [
            'search_string'   => $query,
        ];
        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('direct_compose_search', 'direct_recipient_picker', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send direct user search selection.
     *
     * This is sent when selection a user from the result.
     *
     * @param string $userId   User ID of account who made the comment in Instagram's internal format.
     * @param int    $position The position on the result list.
     * @param string $uuid     UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendDirectUserSearchSelection(
        $userId,
        $position,
        $uuid)
    {
        $extra = [
            'position'          => $position,
            'recipient_ids'     => [$userId],
            'group_session_id'  => $uuid,
        ];
        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('direct_compose_select_recipient', 'direct_recipient_picker', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send enter direct thread event.
     *
     * Used when entering a thread.
     * TODO. More cases.
     *
     * @param string|null $threadId   The thread ID.
     * @param string      $entryPoint Entry point.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendEnterDirectThread(
        $threadId,
        $entryPoint = 'inbox_new_message')
    {
        $extra = [
            'thread_id'          => $threadId,
            'entry_point'        => $entryPoint,
            'inviter'            => $this->ig->account_id,
        ];
        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('direct_enter_thread', 'direct_thread', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send search results.
     *
     * This event should be sent once you have searched something,
     * and it will send Instagram the results you got.
     *
     * @param string   $queryText       Query text.
     * @param string[] $results         String array of User IDs or hashtag IDs.
     * @param string[] $resultsTypeList String array with the same position as $results with 'USER' or 'HASHTAG'.
     * @param string   $rankToken       The rank token.
     * @param string   $searchSession   Search session. UUIDv4.
     * @param int      $searchTime      The time it took to search.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @see follow example.
     */
    public function sendSearchResults(
        $queryText,
        $results,
        $resultsTypeList,
        $rankToken,
        $searchSession,
        $searchTime)
    {
        $extra = [
            'rank_token'          => $rankToken,
            'query_text'          => $queryText,
            'search_session_id'   => $searchSession,
            'is_cache'            => false,
            'search_time'         => $searchTime,
            'results_list'        => $results,
            'results_type_list'   => $resultsTypeList,
        ];
        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('instagram_search_results', 'blended_search', $extra);

        $this->_addEventData($event);
    }

    /**
     * Sends the selected user from the search results.
     *
     * This event should be sent once you have searched something,
     * and it will send Instagram the results you got.
     *
     * @param string   $queryText       Query text.
     * @param string   $selectedId      Selected User ID or hashtag ID.
     * @param string[] $results         String array of user IDs.
     * @param string[] $resultsTypeList String array with the same position as $results with 'USER' or 'HASHTAG'.
     * @param string   $rankToken       The rank token.
     * @param string   $searchSession   Search session. UUIDv4.
     * @param int      $position        Position in the result page of the selected user.
     * @param string   $selectedType    'USER' or 'HASHTAG'.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendSearchResultsPage(
        $queryText,
        $selectedId,
        $results,
        $resultsTypeList,
        $rankToken,
        $searchSession,
        $position,
        $selectedType)
    {
        $positionList = [];
        for ($c = 0; $c < count($results); ++$c) {
            $positionList[] = $c;
        }

        $extra = [
            'rank_token'             => $rankToken,
            'query_text'             => $queryText,
            'search_session_id'      => $searchSession,
            'search_type'            => 'BLENDED',
            'selected_type'          => $selectedType,
            'selected_id'            => $selectedId,
            'click_type'             => 'server_results',
            'selected_position'      => $position,
            'results_list'           => $results,
            'selected_follow_status' => 'not_following',
            'results_position_list'  => $positionList,
            'results_type_list'      => $resultsTypeList,
            'view_type'              => 'vertical',
        ];
        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('search_results_page', 'blended_search', $extra);

        $this->_addEventData($event);
    }

    /**
     * Sends follow button tapped.
     *
     * This event should be sent when tapped the follow button.
     *
     * @param string            $userId       The user ID.
     * @param string|null       $clickPoint   From where was the follow button tapped.
     * @param string|null       $entryTrigger From which action you reached the follow button.
     * @param string            $entryModule  From which module are you coming from.
     * @param array|null        $navstack     Navstack.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendFollowButtonTapped(
        $userId,
        $clickPoint = 'user_profile_header',
        $entryTrigger = 'search_navigate_to_user',
        $entryModule = 'blended_search',
        $navstack = null)
    {
        if ($navstack === null) {
            $navstack = [
                [
                    'module'        => 'feed_timeline',
                    'click_point'   => 'main_search',
                ],
                [
                    'module'        => 'explore_popular',
                    'click_point'   => 'explore_topic_load',
                ],
                [
                    'module'        => 'explore_popular',
                    'click_point'   => 'button',
                ],
                [
                    'module'        => 'blended_search',
                    'click_point'   => 'button',
                ],
                [
                    'module'        => 'blended_search',
                    'click_point'   => 'search_result',
                ],
            ];
        }
        $extra = [
            'request_type'                    => 'create',
            'nav_events'                      => json_encode($navstack),
            'user_id'                         => $userId,
            'follow_status'                   => 'following', // Yes, this is correct.
            'entity_id'                       => $userId,
            'entity_type'                     => 'user',
            'entity_follow_status'            => 'following', // Yes, this is correct.
            'nav_stack_depth'                 => count($navstack),
            'nav_stack'                       => $navstack,
        ];

        if ($entryModule === 'blended_search') {
            $extra['click_point'] = $clickPoint;
            $extra['entry_trigger'] = $entryTrigger;
            $extra['entry_module'] = $entryModule;
            $extra['view_type'] = 'vertical';
        }

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('follow_button_tapped', 'profile', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send muted media.
     *
     * @param string $type          'post' or 'story'.
     * @param bool   $mute          Wether to mure or not the media.
     * @param bool   $postsMuted    If posts are muted already or not.
     * @param string $userId        Target User ID in Instagram's internal format.
     * @param bool   $targetPrivate If target is private.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendMuteMedia(
        $type,
        $mute,
        $postsMuted,
        $userId,
        $targetPrivate)
    {
        if ($type === 'post' && $mute === true) {
            $name = 'ig_mute_posts';
        } elseif ($type === 'posts' && $mute === false) {
            $name = 'ig_unmute_posts';
        } elseif ($type === 'story' && $mute === true) {
            $name = 'ig_mute_stories';
        } else {
            $name = 'ig_unmute_stories';
        }

        $extra = [
            'target_user_id'        => $userId,
            'target_is_private'     => $targetPrivate,
            'selected_from'         => 'profile_overflow_menu',
            'follow_status'         => 'following',
            'target_posts_muted'    => $postsMuted,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody($name, 'media_mute_sheet', $extra);

        $this->_addEventData($event);
    }

    /**
     * Report media action.
     *
     * @param string $action  Action. 'open_media_dialog'.
     * @param string $mediaId Media ID in Instagram's internal format.
     * @param string $module  Module.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function reportMediaAction(
        $action,
        $mediaId,
        $module = 'feed_contextual_self_profile')
    {
        $extra = [
            'actor_id'  => $this->ig->account_id,
            'action'    => $action,
            'target_id' => $mediaId,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('report_media', $module, $extra);

        $this->_addEventData($event);
    }

    /**
     * Send picked media option.
     *
     * The options it appears when you click on the 'three dot' button and shows
     * different options: Share, Copy link, edit, delete...
     *
     * @param string $action   Option. 'DELETE'.
     * @param string $mediaId  Media ID in Instagram's internal format.
     * @param int    $pos      Zero-based position of the media.
     * @param string $module   Module.
     * @param mixed  $option
     * @param mixed  $position
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendMediaPickedOption(
        $option,
        $mediaId,
        $position,
        $module = 'feed_contextual_self_profile')
    {
        $extra = [
            'media_owner_id'    => $this->ig->account_id,
            'option'            => $option,
            'pos'               => $position,
            'media_id'          => $mediaId,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_media_option_picked', $module, $extra);

        $this->_addEventData($event);
    }

    /**
     * Start ingest media.
     *
     * @param string $uploadId      Upload ID.
     * @param int    $mediaType     Media Type.
     * @param string $waterfallId   UUIDv4
     * @param bool   $isCarousel    Wether is going to be uploaded as album or not.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function startIngestMedia(
        $uploadId,
        $mediaType,
        $waterfallId,
        $isCarousel)
    {
        $extra = [
            'upload_id'         => $uploadId,
            'session_id'        => $uploadId,
            'media_type'        => $mediaType,
            'from'              => 'NOT_UPLOADED',
            'connection'        => 'WIFI',
            'share_type'        => 'UNKNOWN',
            'waterfall_id'      => $waterfallId,
            'ingest_id'         => $uploadId,
            'ingest_surface'    => 'feed',
            'target_surface'    => 'feed',
            'is_carousel_item'  => $isCarousel,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_media_ingest_start', null, $extra);

        $this->_addEventData($event);
    }

    /**
     * Start upload attempt.
     *
     * @param string $uploadId      Upload ID.
     * @param int    $mediaType     Media Type.
     * @param string $waterfallId   UUIDv4
     * @param bool   $isCarousel    Wether is going to be uploaded as album or not.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function startUploadAttempt(
        $uploadId,
        $mediaType,
        $waterfallId,
        $isCarousel)
    {
        if ($mediaType === 1) {
            $name = 'upload_photo_attempt';
        } else {
            $name = 'upload_video_attempt';
        }

        $extra = [
            'upload_id'         => $uploadId,
            'session_id'        => $uploadId,
            'media_type'        => ($mediaType === 1) ? 'PHOTO' : 'VIDEO',
            'from'              => 'NOT_UPLOADED',
            'connection'        => 'WIFI',
            'share_type'        => 'UNKNOWN',
            'waterfall_id'      => $waterfallId,
            'is_carousel_child' => (string) $isCarousel,
            'reason'            => 'fbupload'
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody($name, null, $extra);

        $this->_addEventData($event);
    }

    /**
     * Start upload attempt.
     *
     * @param string $uploadId      Upload ID.
     * @param int    $mediaType     Media Type.
     * @param string $waterfallId   UUIDv4
     * @param bool   $isCarousel    Wether is going to be uploaded as album or not.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function uploadMediaSuccess(
        $uploadId,
        $mediaType,
        $waterfallId,
        $isCarousel)
    {
        if ($mediaType === 1) {
            $name = 'upload_photo_success';
        } else {
            $name = 'upload_video_success';
        }

        $extra = [
            'upload_id'         => $uploadId,
            'session_id'        => $uploadId,
            'media_type'        => ($mediaType === 1) ? 'PHOTO' : 'VIDEO',
            'from'              => 'UPLOADED',
            'connection'        => 'WIFI',
            'share_type'        => 'UNKNOWN',
            'waterfall_id'      => $waterfallId,
            'is_carousel_child' => (string) $isCarousel,
            'reason'            => 'fbupload'
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody($name, null, $extra);
        $this->_addEventData($event);

        $event = $this->_addEventBody('ig_media_upload_success', null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Start upload attempt.
     *
     * @param string $status        'attempt' or 'success'.
     * @param string $uploadId      Upload ID.
     * @param int    $mediaType     Media Type.
     * @param string $waterfallId   UUIDv4
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendConfigureMedia(
        $status,
        $uploadId,
        $mediaType,
        $waterfallId)
    {
        if ($status === 'attempt') {
            $name = 'configure_media_attempt';
            $timeFromShare = 0;
        } else {
            $name = 'configure_media_success';
            $timeFromShare = mt_rand(3000, 5000);
        }

        if ($mediaType === 1) {
            $mediaType = 'PHOTO';
        } elseif ($mediaType === 2) {
            $mediaType = 'VIDEO';
        } elseif ($mediaType === 8) {
            $mediaType = 'CAROUSEL';
        }


        $extra = [
            'upload_id'                             => $uploadId,
            'session_id'                            => $uploadId,
            'media_type'                            => $mediaType,
            'from'                                  => 'UPLOADED',
            'connection'                            => 'WIFI',
            'share_type'                            => 'FOLLOWERS_SHARE',
            'source_type'                           => '4',
            'original_width'                        => 0,
            'original_height'                       => 0,
            'since_share_seconds'                   => (mt_rand(1000, 3000) + $timeFromShare) / 1000,
            'time_since_last_user_interaction_sec'  => mt_rand(1, 3),
            'waterfall_id'                          => $waterfallId,
            'attempt_source'                        => 'user post',
            'target'                                => 'CONFIGURED',
            'reason'                                => null
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody($name, null, $extra);
        $this->_addEventData($event);
    }

    /**
     * Prepares Push Notification Settings. Managed automatically by the API. Set during cold start login (before).
     *
     * @param bool        $enabled
     * @param string|null $module
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function pushNotificationSettings()
    {
        $extra = [
            'all_notifications_status' => 1,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('push_notification_setting', 'NotificationChannelsHelper', $extra);

        $this->_addEventData($event);
    }

    /**
     * Enables Push Notification Settings (event). Managed automatically by the API. Set during cold start login (before).
     *
     * @param string|string[] $channels
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function enableNotificationSettings(
        $channels)
    {
        if (!is_array($channels)) {
            $channels = [$channels];
        }

        foreach ($channels as $channel) {
            $extra = [
                'channel_id' => $channel,
            ];

            $extra = $this->_addCommonProperties($extra);
            $event = $this->_addEventBody('notification_channel_enabled', 'NotificationChannelsHelper', $extra);

            $this->_addEventData($event);
        }
    }

    /**
     * Send Phone ID response received..
     *
     * @param string $waterfallId Waterfall ID. UUIDv4.
     * @param int    $startTime   Start time. Timestamp.
     * @param int    $currentTime Current time.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendPhoneIdResponseReceived(
        $waterfallId,
        $startTime,
        $currentTime)
    {
        $extra = [
            'start_time'        => $startTime,
            'waterfall_id'      => $waterfallId,
            'os_version'        => $this->ig->device->getAndroidVersion(),
            'elapsed_time'      => $currentTime - $startTime,
            'guid'              => $this->ig->uuid,
            'prefill_available' => false,
            'current_time'      => $currentTime,
            'pk'                => '0',
            'release_channel'   => null,
            'radio_type'        => 'wifi-none',
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('phone_id_response_received', 'waterfall_log_in', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send Instagram Netego Delivery.
     *
     * @param Response\Model\Item $item     The item object.
     * @param string $sessionId             UUIDv4.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendNetegoDelivery(
        $item,
        $sessionId)
    {
        $extra = [
            'session_id'        => $sessionId,
            'id'                => $item->getId(),
            'netego_id'         => $item->getId(),
            'tracking_token'    => $item->getOrganicTrackingToken(),
            'type'              => 'suggested_users',
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('instagram_netego_delivery', 'feed_timeline', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send Async ad controller success.
     *
     * @param string $trackingToken Tracking token.
     * @param array  $options       Options to configure the event.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendAsyncAdControllerSuccess(
        $trackingToken,
        array $options = [])
    {
        $extra = [
            'tracking_token'        => $trackingToken,
            'desired_action_pos'    => $options['desired_action_pos'],
            'async_ad_source'       => 'timeline_request',
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('instagram_ad_async_ad_controller_action_success', 'feed_timeline', $extra);

        $this->_addEventData($event);
    }

    /**
     * Send zero carrier signal.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendZeroCarrierSignal()
    {
        $extra = [
            'event_type'    => 'config_update',
            'config'        => json_encode(['pingConfigs' => []]),
            'url'           => null,
            'status'        => null,
            'success'       => null,
            'state_changed' => null,
        ];

        $extra = $this->_addCommonProperties($extra);
        $extra['pk'] = 0;
        $event = $this->_addEventBody('zero_carrier_signal', null, $extra);

        $this->_addEventData($event);
    }

    /**
     * Send active interval.
     *
     * @param int $startTime    Timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendActiveInterval(
        $startTime)
    {
        $extra = [
            'event_type'    => 'interval_start',
            'start_time'    => $startTime,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_active_interval', null, $extra);

        $this->_addEventData($event);
    }

    /**
     * Send zero url rewrite.
     *
     * @param string $url             Url.
     * @param string $rewrittenUrl    Url.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendZeroUrlRewrite(
        $url,
        $rewrittenUrl)
    {
        $extra = [
            'url'               => $url,
            'rewritten_url'     => $rewrittenUrl,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_zero_url_rewrite', null, $extra);

        $this->_addEventData($event);
    }

    /**
     * Send cellular data opt.
     *
     * @param boolean $dataSaver    If the app has enabled data saver mode.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendCellularDataOpt(
        $dataSaver = false)
    {
        $extra = [
            'data_saver_mode'               => $dataSaver,
            'high_quality_network_setting'  => 1,
            'os_data_saver_settings'        => 1,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_cellular_data_opt', null, $extra);

        $this->_addEventData($event);
    }

    /**
     * Send dark mode opt.
     *
     * @param boolean $darkMode    If the app has enabled dark mode.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendDarkModeOpt(
        $darkMode = false)
    {
        $extra = [
            'os_dark_mode_settings' => $darkMode,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_dark_mode_opt', null, $extra);

        $this->_addEventData($event);
    }

    /**
     * Wellbeing time in app migration impression.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function wellbeingTimeInAppMigrationImpression()
    {
        $extra = [
            'action'                => 'schedule_reminder',
            'is_v1_enabled'         => false,
            'migration_timestamp'   => 0,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('wellbeing_timeinapp_ui_migration_impression', null, $extra);

        $this->_addEventData($event);
    }

    /**
     * Perf percent render photos.
     *
     * @param string $module        The module where the app state was updated.
     * @param string $mediaId       Media ID in Instagram's internal format.
     * @param array  $options       Options to configure the event.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendPerfPercentPhotosRendered(
        $module,
        $mediaId,
        array $options = [])
    {
        $extra = [
            'media_id'                          => $mediaId,
            'is_grid_view'                      => isset($options['is_grid_view']) ? true : false,
            'rendered'                          => isset($options['rendered']) ? true : false,
            'is_carousel'                       => isset($options['is_carousel']) ? true : false,
            'did_fallback_render'               => isset($options['did_fallback_render']) ? true : false,
            'is_ad'                             => false,
            'image_attempted_height'            => $options['image_heigth'],
            'image_attempted_width'             => $options['image_width'],
            'load_time_ms'                      => $options['load_time'],
            'estimated_bandwidth'               => $options['estimated_bandwidth'],
            'estimated_bandwidth_totalBytes_b'  => $options['estimated_bandwidth_totalBytes_b'],
            'estimated_bandwidth_totalTime_ms'  => $options['estimated_bandwidth_totalTime_ms'],
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('perf_percent_photos_rendered', $module, $extra);

        $this->_addEventData($event);
    }

    /**
     * Network trace.
     *
     * TODO: More information needs to be acquired.
     *
     *    'ct' => 'WIFI',
     *    'bw' => -1.0,
     *    'sd' => 312,
     *    'sb' => 1301,
     *    'wd' => 0,
     *    'rd' => 1,
     *    'rb' => 29,
     *    'ts' => 1578549166293,
     *    'sip' => '31.xx.yy.53',
     *    'sc' => 200,
     *    'tt' => 'NmRjNWY4ZWY0YmFkNDIyNzhkZGM5N2QyMWI0MTFhMWJ8ODMuMTAyLjIwMy42MQ==',
     *    'url' => 'https://i.instagram.com/api/v1/feed/reels_media/',
     *    'hm' => 'POST',
     *    'nsn' => 'Instagram',
     *
     * @param array $trace  Network trace.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function networkTrace(
        $trace)
    {
        $extra = $this->_addCommonProperties($trace);
        $event = $this->_addEventBody('network_trace', null, $extra);

        $this->_addEventData($event);
    }

    /**
     * qe exposure.
     *
     * @param string $id exposure   ID.
     * @param string $experiment    Experiment.
     * @param string $group         Group.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function qeExposure(
        $id,
        $experiment,
        $group)
    {
        $extra = [
            'id'                => $id,
            'experiment'        => $experiment,
            'group'             => $group,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_qe_exposure', null, $extra);

        $this->_addEventData($event);
    }

    /**
     * Non feed activation impression.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendNonFeedActivationImpression()
    {
        $extra = [
            'card_type'         => 'follow',
            'pos'               => 3,
            'completed'         => true,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_non_feed_activation_impression', null, $extra);

        $this->_addEventData($event);
    }

    /**
     * Send SSO Status.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function sendSsoStatus()
    {
        $extra = [
            'enable_igid'   => $this->ig->account_id,
            'enabled'       => 'NO',
            'app_device_id' => $this->ig->uuid,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('sso_status', null, $extra);

        $this->_addEventData($event);
    }

    /**
     * Launcher badge.
     *
     * @param string $deviceId      UUIDv4.
     * @param int    $badgeCount    Badge count.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function launcherBadge(
        $deviceId,
        $badgeCount)
    {
        $extra = [
            'device_id'         => $deviceId,
            'launcher_name'     => 'com.meizu.flyme.launcher',
            'badge_count'       => $badgeCount,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('ig_launcher_badge', null, $extra);

        $this->_addEventData($event);
    }

    /**
     * Updates the app state.
     *
     * @param string $state  The new app state. 'background', 'foreground'.
     * @param string $module The module where the app state was updated.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     */
    public function updateAppState(
        $state,
        $module = 'feed_timeline')
    {
        if ($state !== 'background' && $state !== 'foreground') {
            throw new \InvalidArgumentException(sprintf('%s is an invalid state.', $state));
        }

        $extra = [
            'state' => $state,
        ];

        $extra = $this->_addCommonProperties($extra);
        $event = $this->_addEventBody('app_state', $module, $extra);

        $this->_addEventData($event);
    }
}
