<?php
     declare(strict_types=1);
require_once("Home.php"); // loading home controller

require APPPATH.'../ci/vendor/abraham/twitteroauth/autoload.php';
require APPPATH.'../ci/vendor/autoload.php';




use Instagram\SDK\Instagram;
use Instagram\SDK\Response\DTO\Direct\Thread;
use Instagram\SDK\Response\DTO\General\ItemType;
use Abraham\TwitterOAuth\TwitterOAuth;


class message_manager extends Home
{

    
    public function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('logged_in') != 1)
        redirect('home/login_page', 'location');   
        if($this->session->userdata('user_type') != 'Admin' && !in_array(82,$this->module_access))
        redirect('home/login_page', 'location'); 

    
        $this->load->library("fb_rx_login");
        $this->important_feature();
        $this->member_validity();        
    }


    public function index()
    {
        if($this->session->userdata("facebook_rx_fb_user_info")==0){
        redirect('social_accounts/index','refresh');
}

      $this->message_dashboard();
    }
   

    public function instagram_message_dashboard()
    {  
        // if($this->session->userdata('selected_global_media_type') == 'fb') {
        //     redirect('message_manager/message_dashboard');
        // }

        $page_table_id = '';
        if($this->session->userdata('selected_global_page_table_id') != '') {
            $page_table_id = $this->session->userdata('selected_global_page_table_id');
        }

        $page_info = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("facebook_rx_fb_user_info_id"=>$this->session->userdata('facebook_rx_fb_user_info'),'bot_enabled'=>'1','has_instagram'=>'1')),array('page_name','id','bot_enabled','has_instagram','insta_username'));
        
        $data['page_info'] = $page_info;

        if($page_table_id == '') $page_table_id = isset($page_info[0]['id']) ? $page_info[0]['id']:0;

        $page_data = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("id"=>$page_table_id)),"page_name,insta_username,page_id");
        // if(!isset($page_data[0])) exit();

        $insta_username = $page_data[0]['insta_username'] ?? '';

        $data['page_name'] =  "<a href='https://instagram.com/".$insta_username."'>".$insta_username."</a>";

        $data['body'] = 'message_manager/instagram_message_dashboard';
        $data['page_title'] = $insta_username.' - '.$this->lang->line('Instagram Live Chat');
        $data['page_table_id'] = $page_table_id;        
        $data['tag_list'] = $this->get_broadcast_tags('ig');
        $data['postback_list'] = $page_table_id>0 ? $this->get_dropdown_postback($page_table_id,'ig') : [];
        $this->_viewcontroller($data);
    }

    public function get_pages_conversation_instagram()
    {
        $this->ajax_check();
        $page_table_id = $this->input->post('page_table_id',true);

        
        // $username = "azizah7882";
        // $password = "maspion";

// $username = "prima_visi";
// $password = "ryzendell";

// $username = "testers284";
// $password = "ryzen500";

// $username = "akundemo887";
// $password = "ryzen500";


// $username = "akundemo79";
// $password = "ryzen500";

        
// $username="lili.s3150";
// $username="hello_world.192";
// $password="ryzen500";

// $username = "akundemo888";
// $password = "ryzen500";

// Authenticate
$instagram = Instagram::builder()->build();
$response = $instagram->login($username, $password);

// Retrieve the session for the authenticated user
$session = $response->getSession();

// Persist to session to a local file (or database, cache, session)
file_put_contents($session->getUser()->getUsername(), serialize($session));

// Restore the session from local file
$session = unserialize(file_get_contents($session->getUser()->getUsername()));

// Initialize a new instance of the instagram library
$instagram = Instagram::builder()
    ->setSession($session)
    ->build();


$response = $instagram->inbox();
$inbox = $response->getInbox();

// Retrieve the first thread in the inbox
if (!$thread = current($inbox->getThreads())) {
    // No thread found
    return;
}

// var_dump($thread);
// die();
// Retrieve the whole thread including thread items
$thread = $thread->whole()->getThread();

// Retrieve the available threads
// $thread = $inbox->getThreads();

foreach ($thread->getItems() as $item) {
    // Check whether the item is of type media
    if ($item->isItemType(ItemType::MEDIA)) {
        // Retrieve the images
        $images = $item->getMedia()->getImages()->getCandidates();

        foreach ($images as $image) {
            // Output the image url
            // var_dump($image->getUrl());
        }
    }else{
        // var_dump($item->getText(),$item->getTimestamp(),$item->getUserId());

        $data = array(
            'id'=>NULL,
            'threadId'=>$thread->getThreadId(),
            'user_id'=>$item->getUserId(),
            'timestamp'=>date("d M Y H:i:s",(int) $item->getTimestamp()),
            'message'=>$item->getText()
        );

        $this->db->where('threadId',$thread->getThreadId());
        $this->db->where('message',$item->getText());
       $data_validation= $this->db->get('instagram_chat')->row();

       var_dump($data_validation);
       if ($data_validation == NULL|| $data_validation == false) {
           $this->db->insert('instagram_chat',$data);
        # code...
       }

    }
}


        $where['where'] = array(
            'user_id' => $this->user_id,
            'facebook_rx_fb_user_info_id' => $this->session->userdata('facebook_rx_fb_user_info'),
            'bot_enabled' => '1',
            'id' => $page_table_id
            );
        $select = array('id','page_name','page_profile','page_id');
        // $select = array('id','page_name','page_profile','page_id as fb_page_id');

        $page_list = $this->basic->get_data('facebook_rx_fb_page_info',$where,$select,'','','', $order_by='page_name asc');

        // if(empty($page_list))
        // {
        //     echo '<br><div class="alert alert-danger text-center w-100"><b class="m-0">'.$this->lang->line("You do not have any bot enabled page").'</b></div>';
        //     exit();
        // }

        $user_info = $this->basic->get_data('users',array('where'=>array('id'=>$this->user_id)));
        if(isset($user_info[0]['time_zone']) && $user_info[0]['time_zone'] != '')
            date_default_timezone_set($user_info[0]['time_zone']);

            
// $consumer_key = "upB4rDa30tmM2rpJRFLcTQRzi";
// $consumer_secret = "9gzdKxxjgbkN8FOsEa1apLtOs1jehcuyQthpUFNRWs4Q3k6loA";
// $access_token = "1534821335574388736-4TRtMmMKcnTJpfEv9xCCDNVOHna1M8";
// $access_token_secret = "bh6cjSizEtJmyU06y7pkK9mWLbJbIlSRKtXywW7UBE9GG";
// $bearer_token = "AAAAAAAAAAAAAAAAAAAAADYjdgEAAAAAvt7IoiMUFmUbIKc%2BMZM2VYA%2FPlk%3DabSENs97CNSdqvcMzZObzNTt4ZYBnpLYbcNyPGuZReTmRsO8WJ";

// $connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
// $response = $connection->get('direct_messages/events/list', ["count" => 400]);
        //  $response= $this->messenger_sync_page_messages($page_table_id,"ig");
        
     $this->db->group_by("user_id");
     $response = $this->db->get('instagram_chat')->result_array();
        // $response="";
     //   $response= "Ini adalah Halaman Twitter";

     //var_dump($response);
        // $this->load->library("Instagram");


        // $response=$this->Instagram->direct->getInbox($this->input->post('cursor_id',true),10);


        if(isset($response['error']))
        {
            echo '<br><div class="alert alert-danger text-center w-100"><b class="m-0">'.$response['error_message'].'</b></div>';
            exit();
        }
        else 
        foreach($response as $r){
	
        //     $rand = rand(1,4);
        //     $default = base_url('assets/img/avatar/avatar-'.$rand.'.png');	
        
        // if ($r['user_id'] != "1534821335574388736") {
        //     # code...
            
            
		// $str='
		// <li class="media py-2 my-0 px-4 open_conversation"style="cursor:pointer" data-id="'.$r['user_id'].'">
		// 	<img alt="image" class="mr-3 rounded-circle border" width="50" height="50" src="'.$default.'">
		// 	<div class="media-body">
		// 	  <div class="mt-0 mb-1 font-weight-bold text-primary">'.$r['user_id'].'<span class="badge badge-danger badge-pill ml-2 px-2 py-1 d-none">2</span></div>
		// 	  <div class="text-small font-600-bold"><i class="fas fa-circle text-success pb-1" style="font-size:8px"></i> '.$r['user_id'].'</div>
		// 	</div>
		// </li>';

        // echo $str;
        // }    
    }       
    echo json_encode($response);
        
    }


    public function get_pages_conversation_yaestar()
    {
        $this->ajax_check();
        $page_table_id = $this->input->post('page_table_id',true);

        
   
        $where['where'] = array(
            'user_id' => $this->user_id,
            'facebook_rx_fb_user_info_id' => $this->session->userdata('facebook_rx_fb_user_info'),
            'bot_enabled' => '1',
            'id' => $page_table_id
            );
        $select = array('id','page_name','page_profile','page_id');
        // $select = array('id','page_name','page_profile','page_id as fb_page_id');

        $page_list = $this->basic->get_data('facebook_rx_fb_page_info',$where,$select,'','','', $order_by='page_name asc');

        // if(empty($page_list))
        // {
        //     echo '<br><div class="alert alert-danger text-center w-100"><b class="m-0">'.$this->lang->line("You do not have any bot enabled page").'</b></div>';
        //     exit();
        // }

        $user_info = $this->basic->get_data('users',array('where'=>array('id'=>$this->user_id)));
        if(isset($user_info[0]['time_zone']) && $user_info[0]['time_zone'] != '')
            date_default_timezone_set($user_info[0]['time_zone']);

            
// $consumer_key = "upB4rDa30tmM2rpJRFLcTQRzi";
// $consumer_secret = "9gzdKxxjgbkN8FOsEa1apLtOs1jehcuyQthpUFNRWs4Q3k6loA";
// $access_token = "1534821335574388736-4TRtMmMKcnTJpfEv9xCCDNVOHna1M8";
// $access_token_secret = "bh6cjSizEtJmyU06y7pkK9mWLbJbIlSRKtXywW7UBE9GG";
// $bearer_token = "AAAAAAAAAAAAAAAAAAAAADYjdgEAAAAAvt7IoiMUFmUbIKc%2BMZM2VYA%2FPlk%3DabSENs97CNSdqvcMzZObzNTt4ZYBnpLYbcNyPGuZReTmRsO8WJ";

// $connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
// $response = $connection->get('direct_messages/events/list', ["count" => 400]);
        //  $response= $this->messenger_sync_page_messages($page_table_id,"ig");
        
        $db2 =$this->load->database('otherdb',TRUE);

     $db2->group_by("src");
     $db2->where('date_format(datetime,"%Y-%m-%d")', 'CURDATE()', FALSE);

     $response = $db2->get('cdr_202206')->result_array();

    //  var_dump($response);
        // $response="";
     //   $response= "Ini adalah Halaman Twitter";

     //var_dump($response);
        // $this->load->library("Instagram");


        // $response=$this->Instagram->direct->getInbox($this->input->post('cursor_id',true),10);


        if(isset($response['error']))
        {
            echo '<br><div class="alert alert-danger text-center w-100"><b class="m-0">'.$response['error_message'].'</b></div>';
            exit();
        }
        else 
        foreach($response as $r){
	
            $rand = rand(1,4);
            $default = base_url('assets/img/avatar/avatar-'.$rand.'.png');	
        
        if ($r['src'] != NULL) {
            # code...
            
            
		$str='
		<li class="media py-2 my-0 px-4 open_conversation"style="cursor:pointer" data-id="'.$r['src'].'">
			<img alt="image" class="mr-3 rounded-circle border" width="50" height="50" src="'.$default.'">
			<div class="media-body">
			  <div class="mt-0 mb-1 font-weight-bold text-primary">'.$r['src'].'<span class="badge badge-danger badge-pill ml-2 px-2 py-1 d-none">2</span></div>
			  <div class="text-small font-600-bold"><i class="fas fa-circle text-success pb-1" style="font-size:8px"></i> '.$r['src'].'</div>
			</div>
		</li>';

        echo $str;
        }    
    }       
        
    }


    public function get_post_conversation_instagram()
    {
        $this->ajax_check();
        // error_reporting(0);

        // for time zone checking
        $where = array();
        $where['where'] = array(
            'user_id' => $this->user_id,
            'facebook_rx_fb_user_info_id' => $this->session->userdata('facebook_rx_fb_user_info')
            );

        $from_user_id = $this->input->post('from_user_id',true);
        $thread_id = $this->input->post('thread_id',true);
        $page_table_id = $this->input->post('page_table_id',true);
        $last_message_id = $this->input->post('last_message_id',true);  
        $id = $this->input->post('id',true);
        $data_id = $this->input->post('data-id',true);
        $page_info = $this->basic->get_data('facebook_rx_fb_page_info',array('where'=>array('id'=>$page_table_id)));


        //$post_access_token = $page_info[0]['page_access_token'];
       // $page_name = $page_info[0]['page_name'];

        
        // $conversations = $this->fb_rx_login->get_messages_from_thread($thread_id,$post_access_token);

//	$id = 5;
        // $conversations = $this->fb_rx_login->get_messages_from_thread_whats_app($id);

        // $this->basic->insert_data("fb_chat_data",array("user_id"=>$from_user_id,"message"=>serialize($conversations['data'])));


        // $username = "testers284";
        // $password = "ryzen500";
            
        // $username = "ryzen1458";
        // $password = "ryzen500";
        
        // $username = "akundemo_79";
        // $password = "ryzen500";
        // $name_ig = "akundemo_79";
        

//         $username = "akundemo888";
// $password = "ryzen500";

        // $username="hello_world.192";
        $username="lili.s3150";
        $password="ryzen500";
        $name_ig="lili.s3150";


        // $username="akundemo79";
        // $password="ryzen500";

        // Authenticate
        $content = @file_get_contents($name_ig);
if($content){
    $session = @unserialize($content);
    $instagram = Instagram::builder()
        ->setSession($session)
        ->build();
}else{
    $instagram = Instagram::builder()->build();
    $response = $instagram->login($username, $password);
    $session = $response->getSession();
    file_put_contents($name_ig, serialize($session));

}

$response = $instagram->inbox();
$inbox = $response->getInbox();

if (!$thread = current($inbox->getThreads())) {
    return;
}

$thread = $thread->whole()->getThread();

foreach ($thread->getItems() as $item) {
    if ($item->isItemType(ItemType::MEDIA)) {
        $images = $item->getMedia()->getImages()->getCandidates();

        foreach ($images as $image) {
            var_dump($image->getUrl());
        }
    }else{
        // var_dump($item->getText());
        $data = array(
                    'id'=>NULL,
                    'threadId'=>$thread->getThreadId(),
                    'user_id'=>$item->getUserId(),
                    'timestamp'=>date("d M Y H:i:s",(int) $item->getTimestamp()),
                    'message'=>$item->getText()
                );
        
                $this->db->where('threadId',$thread->getThreadId());
                $this->db->where('message',$item->getText());
               $data_validation= $this->db->get('instagram_chat')->row();
        
            //    var_dump($data_validation);
               if ($data_validation == NULL|| $data_validation == false) {
                   $this->db->insert('instagram_chat',$data);
                # code...
               }
    }
}
        // $this->db->where("user_id",$id);
        $this->db->order_by("id",'DESC');
        $data_chat_twitter = $this->db->get('instagram_chat')->result_array();

        
        // $this->db->where("user_id",$id);
        $this->db->order_by("id","ASC");
        $this->db->limit(1);
        $last_id=$this->db->get('instagram_chat')->row("id");

        // var_dump($data_chat_twitter);

        // var_dump($this->db->last_query());
        // foreach ($conversations['events'] as $key => $value) {
        //     # code...
      
        //     $data = array(
        //         'id'=>NULL,
        //         'recept_id' =>$value->message_create->target->recipient_id,
        //         'sender_id' =>$value->message_create->sender_id,
        //         'message'=>$value->message_create->message_data->text
        //     );
        //     $this->db->insert('twitter_chat',$data);

        // }
      



        // var_dump($conversations['events'][0]);
        if(!isset($conversations['events'])) $conversations['events']=array();
        $conversations['events']= array_reverse($conversations['events']);
        // var_dump($conversations['events']);
        // echo "<pre>"; print_r($conversations['data']); exit;

        $lalas = $conversations['events'];


        $show_after_this_index = NULL;
        if(!empty($last_message_id))
        foreach($data_chat_twitter as $key=>$value)
        {
            if($value['user_id']==$last_id) {
                $show_after_this_index = $key;
                break;
            }
        }
// error_reporting(0);
        // $str = '';
        // var_dump($conversations['events']);
       
        foreach($data_chat_twitter as $key=>$value)
        {
            // var_dump($key);
            if(!is_null($show_after_this_index) && $key<=$show_after_this_index) continue;
         
            //  $created_time = $value['tanggal']." UTC";
            // isset($value['from']['name']) ? $value['from']['name'] = $value['from']['name'] : $value['from']['name'] = '';
            // if($value['user_id'] == $value['threadId'])
            // {
            //     $str ='
            //     <div class="chat-item chat-right" style="">
            //          <div class="chat-details mr-0 ml-0" message_id="'.$value['user_id'].'">
            //             <div class="chat-text">'.chunk_split($value['message'], 50, '<br>').'</div>
            //             <div class="chat-time">'.$value['user_id'].' </div>
            //          </div>
            //     </div>';
            // }
            // else
            // {
            //     $str='
            //     <div class="chat-item chat-left" style="">
            //          <div class="chat-details mr-0 ml-0" message_id="'.$value['user_id'].'">
            //             <div class="chat-text">'.chunk_split($value['message'], 50, '<br>').'</div>
            //             <div class="chat-time">'.$value['user_id'].'</div>
            //          </div>
            //     </div>';
            // }
            // echo $str;
        }

        $lalas = (array) $data_chat_twitter;
        echo json_encode($lalas);
    }
   


    // public function get_post_conversation_instagram()
    // {
    //     $this->ajax_check();

    //     // for time zone checking
    //     $where = array();
    //     $where['where'] = array(
    //         'user_id' => $this->user_id,
    //         'facebook_rx_fb_user_info_id' => $this->session->userdata('facebook_rx_fb_user_info')
    //         );
       

    //     $from_user_id = $this->input->post('from_user_id',true);
    //     $thread_id = $this->input->post('thread_id',true);
    //     $page_table_id = $this->input->post('page_table_id',true);
    //     $last_message_id = $this->input->post('last_message_id',true);

    //     $page_info = $this->basic->get_data('facebook_rx_fb_page_info',array('where'=>array('id'=>$page_table_id)));

    //     $post_access_token = $page_info[0]['page_access_token'];
    //     $page_name = $page_info[0]['page_name'];

    //     $conversations = $this->fb_rx_login->get_messages_from_thread_instagram($thread_id,$post_access_token);
    //     if(!isset($conversations['data'])) $conversations['data']=array();
    //     $conversations['data'] = array_reverse($conversations['data']);

    //     // pre($conversations['data']);

    //     $show_after_this_index = NULL;
    //     if(!empty($last_message_id))
    //     foreach($conversations['data'] as $key=>$value)
    //     {
    //         if($value['id']==$last_message_id) {
    //             $show_after_this_index = $key;
    //             break;
    //         }
    //     }

    //     // $str = '';
    //     foreach($conversations['data'] as $key=>$value)
    //     {
    //         if(!is_null($show_after_this_index) && $key<=$show_after_this_index) continue;

    //         $temp_from_user_id = isset($value['from']['id']) ? $value['from']['id'] :'';
    //         $temp_from_user_name = isset($value['from']['username']) ? $value['from']['username'] :'';
    //         $position_class = $from_user_id!=$temp_from_user_id ? "chat-item chat-right" : "chat-item chat-left";
    //         $thumbnail = $from_user_id!=$temp_from_user_id ? base_url('assets/img/icon/instagram.png') : base_url('assets/img/avatar/avatar-1.png');

    //         $created_time = $value['created_time']." UTC";

    //         $message = '';

    //         if(isset($value['message']) && !empty($value['message'])) $message = '<div class="chat-text">'.$value["message"].'</div>';
    //         if(isset($value['is_unsupported']) && $value['is_unsupported']=='1') $message = '<div class="chat-text text-muted">Message not supported</div>';
 
    //         $attachments='';
    //         if(isset($value['attachments']['data'][0]))
    //         {                
    //             if(isset($value['attachments']['data'][0]['image_data']))
    //             {
    //                  $image_url = isset($value['attachments']['data'][0]['image_data']['url']) ? $value['attachments']['data'][0]['image_data']['url'] : '';
    //                  $attachments .= '<img class="img-thumbnail img-fluid d-block" style="max-width:300px;" src="'.$image_url.'">';
    //             }
    //             else if(isset($value['attachments']['data'][0]['video_data']))
    //             {
    //                  $image_url = isset($value['attachments']['data'][0]['video_data']['url']) ? $value['attachments']['data'][0]['video_data']['url'] : '';
    //                  $attachments .= '
    //                  <video width="300" height="" src="'.$image_url.'" onClick=\'openTab("'.$image_url.'")\'></video>';
    //             }
                
    //         }

    //         $str='
    //         <div class="'.$position_class.'" style="">
    //              <div class="chat-details mr-0 ml-0" key="'.$key.'" message_id="'.$value['id'].'">
    //                 '.$message.'
    //                 '.$attachments.'
    //                 <div class="chat-time">'.date('d M Y H:i:s',strtotime($created_time)).'</div>
    //              </div>
    //         </div>';
    //     }
    //     echo $str;
    // }

    public function reply_to_conversation_instagram()
    {
        if($this->is_demo == '1')
        {
            echo "<div class='alert alert-danger text-center'>This feature is disabled in this demo.</div>"; 
            exit();
        }

        $from_user_id = $this->input->post('from_user_id',true);
        $page_table_id = $this->input->post('page_table_id',true);
        $reply_message = $this->input->post('reply_message',true);
        $message_tag = $this->input->post('message_tag',true);
        if($message_tag=="") $message_tag =  $this->session->userdata('username');


        $page_info = $this->basic->get_data('facebook_rx_fb_page_info',array('where'=>array('id'=>$page_table_id)));
        $post_access_token = $page_info[0]['page_access_token'] ?? '';


        $message = array
        (
            'recipient' =>array('id'=>$from_user_id),
            'message'=>array('text'=>$reply_message),
            'tag'=>$message_tag
        );
        $message = json_encode($message);

        try
        {            
            $response = $this->fb_rx_login->send_non_promotional_message_subscription($message,$post_access_token);
           
            if(isset($response['message_id']))
            {
                echo
                '<div class="chat-item chat-right" style="">
                     <div class="chat-details mr-0 ml-0" message_id="'.$response['message_id'].'">
                        <div class="chat-text">'.$reply_message.'</div>
                        <div class="chat-time">'.date('d M Y H:i:s').'</div>
                     </div>
                </div>';
            }
            else 
            {
                if(isset($response["error"]["message"])) $message_sent_id = $response["error"]["message"];  
                if(isset($response["error"]["code"])) $message_error_code = $response["error"]["code"]; 

                if(isset($message_error_code) && $message_error_code=="368") // if facebook marked message as spam 
                {
                    $error_msg=$message_sent_id;
                }

                $error_msg = $message_sent_id;
                echo "<div class='alert alert-danger text-center'>".$error_msg."</div>";

            } 
        }
        catch(Exception $e) 
        {
          echo "<div class='alert alert-danger text-center'>".$e->getMessage()."</div>";
        }
    }


    public function message_dashboard()
    {
        if($this->session->userdata('selected_global_media_type') == 'ig') {
            redirect('message_manager/instagram_message_dashboard');
        }
        $page_table_id = '';
        if($this->session->userdata('selected_global_page_table_id')) {
            $page_table_id = $this->session->userdata('selected_global_page_table_id');
        }
        $page_info = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("facebook_rx_fb_user_info_id"=>$this->session->userdata("facebook_rx_fb_user_info"),'bot_enabled'=>'1')),array('page_name','id','bot_enabled','has_instagram','insta_username'));
        
        $data['page_info'] = $page_info;

        if($page_table_id == '') {
            $page_table_id = $page_info[0]['id'] ?? 0;
        }

        $page_data = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("id"=>$page_table_id)),"id,page_name,insta_username,page_id");
        // if(!isset($page_data[0])) exit();

        $page_id = $page_data[0]['page_id'] ?? '';
        $page_name = $page_data[0]['page_name'] ?? '';

        $data['page_name'] =  "<a href='https://facebook.com/".$page_id."'>".$page_name."</a>";

        $data['body'] = 'message_manager/message_dashboard';
        $data['page_title'] = $page_name.' - '.$this->lang->line('Facebook Live Chat');
        $data['page_table_id'] = $page_table_id;
        $data['tag_list'] = $this->get_broadcast_tags();
        $data['postback_list'] = $page_table_id>0 ? $this->get_dropdown_postback($page_table_id,'fb') : [];
        
        $this->_viewcontroller($data);
    }


    public function whatsapp_message_dashboard()
    {
        if($this->session->userdata('selected_global_media_type') == 'ig') {
            redirect('message_manager/instagram_message_dashboard');
        }
        $page_table_id = '';
        if($this->session->userdata('selected_global_page_table_id')) {
            $page_table_id = $this->session->userdata('selected_global_page_table_id');
        }
        $page_info = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("facebook_rx_fb_user_info_id"=>$this->session->userdata("facebook_rx_fb_user_info"),'bot_enabled'=>'1')),array('page_name','id','bot_enabled','has_instagram','insta_username'));
        
        $data['page_info'] = $page_info;

        if($page_table_id == '') {
            $page_table_id = $page_info[0]['id'] ?? 0;
        }

        $page_data = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("id"=>$page_table_id)),"id,page_name,insta_username,page_id");
        // if(!isset($page_data[0])) exit();

        $page_id = $page_data[0]['page_id'] ?? '';
        $page_name = $page_data[0]['page_name'] ?? '';

        $data['page_name'] =  "<a href='https://facebook.com/".$page_id."'>".$page_name."</a>";

        $data['body'] = 'message_manager/whatsapp_message_dashboard';
        $data['page_title'] = $page_name.' - '.$this->lang->line('Whats App Live Chat');
        $data['page_table_id'] = $page_table_id;
        $data['tag_list'] = $this->get_broadcast_tags();
        $data['postback_list'] = $page_table_id>0 ? $this->get_dropdown_postback($page_table_id,'fb') : [];
        
        $this->_viewcontroller($data);
    }

    public function simpan_login_twitter(){
        $consumer_key = $this->input->post('consumer_key',true);
        $consumer_secret = $this->input->post('consumer_secret',true);
        $access_token = $this->input->post('access_token',true);
        $access_token_secret = $this->input->post('access_token_secret',true);
        $bearer_token = $this->input->post('bearer_token',true);

        $data = array(
            'consumer_key'=>$consumer_key,
            'consumer_secret'=>$consumer_secret,
            'access_token'=>$access_token,
            'access_token_secret'=>$access_token_secret,
            'bearer_token'=>$bearer_token
        );
        $this->db->insert('account_info',$data);
        redirect('message_manager/twitter_message_dashboard');

    }
    public function twitter_message_dashboard()
    {
        if($this->session->userdata('selected_global_media_type') == 'ig') {
            redirect('message_manager/instagram_message_dashboard');
        }
        $page_table_id = '';
        if($this->session->userdata('selected_global_page_table_id')) {
            $page_table_id = $this->session->userdata('selected_global_page_table_id');
        }
        $page_info = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("facebook_rx_fb_user_info_id"=>$this->session->userdata("facebook_rx_fb_user_info"),'bot_enabled'=>'1')),array('page_name','id','bot_enabled','has_instagram','insta_username'));
        
        // $account = $this->db->get('account_info')->result_array();
       // $data['page_info'] = $account;

        if($page_table_id == '') {
            $page_table_id = $page_info[0]['id'] ?? 0;
        }

        $page_data = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("id"=>$page_table_id)),"id,page_name,insta_username,page_id");
        // if(!isset($page_data[0])) exit();

        $page_id = $page_data[0]['page_id'] ?? '';
        $page_name = $page_data[0]['page_name'] ?? '';

        $data['page_name'] =  "<a href='https://facebook.com/".$page_id."'>".$page_name."</a>";

        $data['body'] = 'message_manager/twitter_message_dashboard';
        $data['page_title'] = $page_name.' - '.$this->lang->line('Twitter Chat');
        $data['page_table_id'] = $page_table_id;
        $data['tag_list'] = $this->get_broadcast_tags();
        $data['postback_list'] = $page_table_id>0 ? $this->get_dropdown_postback($page_table_id,'fb') : [];
        
        $this->_viewcontroller($data);
    }

    public function yaestar_message_dashboard()
    {
        if($this->session->userdata('selected_global_media_type') == 'ig') {
            redirect('message_manager/instagram_message_dashboard');
        }
        $page_table_id = '';
        if($this->session->userdata('selected_global_page_table_id')) {
            $page_table_id = $this->session->userdata('selected_global_page_table_id');
        }
        $page_info = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("facebook_rx_fb_user_info_id"=>$this->session->userdata("facebook_rx_fb_user_info"),'bot_enabled'=>'1')),array('page_name','id','bot_enabled','has_instagram','insta_username'));
        
        $data['page_info'] = $page_info;

        if($page_table_id == '') {
            $page_table_id = $page_info[0]['id'] ?? 0;
        }

        $page_data = $this->basic->get_data("facebook_rx_fb_page_info",array("where"=>array("id"=>$page_table_id)),"id,page_name,insta_username,page_id");
        // if(!isset($page_data[0])) exit();

        $page_id = $page_data[0]['page_id'] ?? '';
        $page_name = $page_data[0]['page_name'] ?? '';

        $data['page_name'] =  "<a href='https://facebook.com/".$page_id."'>".$page_name."</a>";

        $data['body'] = 'message_manager/yaestar_message_dashboard';
        $data['page_title'] = $page_name.' - '.$this->lang->line('Yaestar Record');
        $data['page_table_id'] = $page_table_id;
        $data['tag_list'] = $this->get_broadcast_tags();
        $data['postback_list'] = $page_table_id>0 ? $this->get_dropdown_postback($page_table_id,'fb') : [];
        
        $this->_viewcontroller($data);
    }

    public function get_selected_page_data()
    {
        // code...
    }


    public function get_pages_conversation()
    {

        $this->ajax_check();
        $page_table_id = $this->input->post('page_table_id',true);
        $where['where'] = array(
            'user_id' => $this->user_id,
            'facebook_rx_fb_user_info_id' => $this->session->userdata('facebook_rx_fb_user_info'),
            'bot_enabled' => '1',
            'id' => $page_table_id
            );
        $select = array('id','page_name','page_profile','page_id as fb_page_id');
        $page_list = $this->basic->get_data('facebook_rx_fb_page_info',$where,$select,'','','', $order_by='page_name asc');

        if(empty($page_list))
        {
            echo '<br><div class="alert alert-danger text-center w-100"><b class="m-0">'.$this->lang->line("You do not have any bot enabled page").'</b></div>';
            exit();
        }

        $user_info = $this->basic->get_data('users',array('where'=>array('id'=>$this->user_id)));
        if(isset($user_info[0]['time_zone']) && $user_info[0]['time_zone'] != '')
            date_default_timezone_set($user_info[0]['time_zone']);
       $response= $this->messenger_sync_page_messages($page_table_id);
	
//	$response = "Ini Adalah  USer"; 

        if(isset($response['error']))
        {
            echo '<br><div class="alert alert-danger text-center w-100"><b class="m-0">'.$response['error_message'].'</b></div>';
            exit();
        }
        else echo $response;
    
        
    }
    

    public function get_pages_conversation_whatsapp()
    {

        $this->ajax_check();
        $page_table_id = $this->input->post('page_table_id',true);
        $where['where'] = array(
            'user_id' => $this->user_id,
            'facebook_rx_fb_user_info_id' => $this->session->userdata('facebook_rx_fb_user_info'),
            'bot_enabled' => '1',
            'id' => $page_table_id
            );
        $select = array('id','page_name','page_profile','page_id as fb_page_id');
        $page_list = $this->basic->get_data('facebook_rx_fb_page_info',$where,$select,'','','', $order_by='page_name asc');

        // if(empty($page_list))
        // {
        //     echo '<br><div class="alert alert-danger text-center w-100"><b class="m-0">'.$this->lang->line("You do not have any bot enabled page").'</b></div>';
        //     exit();
        // }

        $user_info = $this->basic->get_data('users',array('where'=>array('id'=>$this->user_id)));
        if(isset($user_info[0]['time_zone']) && $user_info[0]['time_zone'] != '')
            date_default_timezone_set($user_info[0]['time_zone']);
      // $response= $this->messenger_sync_page_messages($page_table_id);
	
    	$response = $this->fb_rx_login->get_messages_from_thread_pelapor(); 

        if(isset($response['error']))
        {
            echo '<br><div class="alert alert-danger text-center w-100"><b class="m-0">'.$response['error_message'].'</b></div>';
            exit();
        }
        else  
// $str ='';

// var_dump($response['data']);
// $$response = array_unique($response['data'])
	foreach($response['data'] as $r){
            $rand = rand(1,4);
            $default = base_url('assets/img/avatar/avatar-'.$rand.'.png');	

            // var_dump($r['id']);
            $str='
		<li class="media py-2 my-0 px-4 open_conversation"style="cursor:pointer" data-id="'.$r['id'].'">
			<img alt="image" class="mr-3 rounded-circle border" width="50" height="50" src="'.$default.'">
			<div class="media-body">
			  <div class="mt-0 mb-1 font-weight-bold text-primary">'.$r['nama'].'<span class="badge badge-danger badge-pill ml-2 px-2 py-1 d-none">2</span></div>
			  <div class="text-small font-600-bold"><i class="fas fa-circle text-success pb-1" style="font-size:8px"></i> '.$r['nama'].'</div>
			</div>
		</li>';

        echo $str;
    }

    
        
    }

    public function get_pages_conversation_twitter()
    {

        $this->ajax_check();
        $page_table_id = $this->input->post('page_table_id',true);
        $where['where'] = array(
            'user_id' => $this->user_id,
            'facebook_rx_fb_user_info_id' => $this->session->userdata('facebook_rx_fb_user_info'),
            'bot_enabled' => '1',
            'id' => $page_table_id
            );
        $select = array('id','page_name','page_profile','page_id as fb_page_id');
        $page_list = $this->basic->get_data('facebook_rx_fb_page_info',$where,$select,'','','', $order_by='page_name asc');

        // if(empty($page_list))
        // {
        //     echo '<br><div class="alert alert-danger text-center w-100"><b class="m-0">'.$this->lang->line("You do not have any bot enabled page").'</b></div>';
        //     exit();
        // }

        $user_info = $this->basic->get_data('users',array('where'=>array('id'=>$this->user_id)));
        if(isset($user_info[0]['time_zone']) && $user_info[0]['time_zone'] != '')
            date_default_timezone_set($user_info[0]['time_zone']);
      // $response= $this->messenger_sync_page_messages($page_table_id);
	
      

            
$consumer_key = "upB4rDa30tmM2rpJRFLcTQRzi";
$consumer_secret = "9gzdKxxjgbkN8FOsEa1apLtOs1jehcuyQthpUFNRWs4Q3k6loA";
$access_token = "1534821335574388736-4TRtMmMKcnTJpfEv9xCCDNVOHna1M8";
$access_token_secret = "bh6cjSizEtJmyU06y7pkK9mWLbJbIlSRKtXywW7UBE9GG";
$bearer_token = "AAAAAAAAAAAAAAAAAAAAADYjdgEAAAAAvt7IoiMUFmUbIKc%2BMZM2VYA%2FPlk%3DabSENs97CNSdqvcMzZObzNTt4ZYBnpLYbcNyPGuZReTmRsO8WJ";

$connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
$response = $connection->get('direct_messages/events/list', ["count" => 400]);
        // $response= $this->messenger_sync_page_messages($page_table_id,"ig");
     //   $response= "Ini adalah Halaman Twitter";

     error_reporting(0);
     $array = get_object_vars($response);
     $conversations = get_object_vars($response);


     $this->db->group_by("sender_id");
     $data_chat_twitter = $this->db->get('twitter_chat')->result_array();

     foreach ($conversations['events'] as $key => $value) {
        # code...
  
        $data = array(
            'id'=>NULL,
            'recept_id' =>$value->message_create->target->recipient_id,
            'sender_id' =>$value->message_create->sender_id,
            'message'=>$value->message_create->message_data->text
        );
        
        $this->db->where('sender_id',$value->message_create->sender_id);
        $this->db->where('message',$value->message_create->message_data->text);
       $data_validation= $this->db->get('twitter_chat')->row();
        if ($data_validation == NULL || $data_validation == false) {
            # code...
            $this->db->insert('twitter_chat',$data);
        }

    }
  

    //  var_dump($array);
    	// $response = $this->fb_rx_login->get_messages_from_thread_pelapor(); 

        if($array['errors'])
        {
            echo '<br><div class="alert alert-danger text-center w-100"><b class="m-0">'.$array['errors'][0]->message.'</b></div>';
            exit();
        }
        else  
// $str ='';

	foreach($data_chat_twitter as $r){
	
            $rand = rand(1,4);
            $default = base_url('assets/img/avatar/avatar-'.$rand.'.png');	
        
        if ($r['sender_id'] != "1534821335574388736") {
            # code...
            
            
		$str='
		<li class="media py-2 my-0 px-4 open_conversation"style="cursor:pointer" data-id="'.$r['sender_id'].'">
			<img alt="image" class="mr-3 rounded-circle border" width="50" height="50" src="'.$default.'">
			<div class="media-body">
			  <div class="mt-0 mb-1 font-weight-bold text-primary">'.$r['sender_id'].'<span class="badge badge-danger badge-pill ml-2 px-2 py-1 d-none">2</span></div>
			  <div class="text-small font-600-bold"><i class="fas fa-circle text-success pb-1" style="font-size:8px"></i> '.$r['sender_id'].'</div>
			</div>
		</li>';

        echo $str;
        }    
    }

    
        
    }

    public function get_post_conversation()
    {
        $this->ajax_check();

        // for time zone checking
        $where = array();
        $where['where'] = array(
            'user_id' => $this->user_id,
            'facebook_rx_fb_user_info_id' => $this->session->userdata('facebook_rx_fb_user_info')
            );

        $from_user_id = $this->input->post('from_user_id',true);
        $thread_id = $this->input->post('thread_id',true);
        $page_table_id = $this->input->post('page_table_id',true);
        $last_message_id = $this->input->post('last_message_id',true);  
        

       $page_info = $this->basic->get_data('facebook_rx_fb_page_info',array('where'=>array('id'=>$page_table_id)));


        $post_access_token = $page_info[0]['page_access_token'];
        $page_name = $page_info[0]['page_name'];

        
        $conversations = $this->fb_rx_login->get_messages_from_thread($thread_id,$post_access_token);

        foreach($conversations['data'] as $key=>$value)
        {
           // if(!is_null($show_after_this_index) && $key<=$show_after_this_index) continue;

            $data = array(
                'id'=>NULL,
                'message'=>$value['message'],
                'name'=>$value['from']['name'],
                'created_time'=>$value['created_time']
            );

            $this->db->insert("fb_chat_data",$data);

        }

        $this->db->order_by("id",'DESC');
        $data_chat_twitter = $this->db->get('fb_chat_data')->result_array();

        if(!isset($conversations['data'])) $conversations['data']=array();
        $conversations['data'] = array_reverse($conversations['data']);
        // echo "<pre>"; print_r($conversations['data']); exit;

        $show_after_this_index = NULL;
        if(!empty($last_message_id))
        foreach($conversations['data'] as $key=>$value)
        {
            if($value['id']==$last_message_id) {
                $show_after_this_index = $key;
                break;
            }
        }

        $str = '';
        foreach($conversations['data'] as $key=>$value)
        {
            if(!is_null($show_after_this_index) && $key<=$show_after_this_index) continue;

             $created_time = $value['created_time']." UTC";
             isset($value['from']['name']) ? $value['from']['name'] = $value['from']['name'] : $value['from']['name'] = '';
             if($value['from']['name'] == $page_name)
             {
                 $str.='
                 <div class="chat-item chat-right" style="">
                      <div class="chat-details mr-0 ml-0" message_id="'.$value['id'].'">
                         <div class="chat-text">'.chunk_split($value['message'], 50, '<br>').'</div>
                         <div class="chat-time">'.$value['from']['name'].' @'.date('d M Y H:i:s',strtotime($created_time)).'</div>
                      </div>
                 </div>';
             }
             else
             {
                 $str.='
                 <div class="chat-item chat-left" style="">
                      <div class="chat-details mr-0 ml-0" message_id="'.$value['id'].'">
                         <div class="chat-text">'.chunk_split($value['message'], 50, '<br>').'</div>
                         <div class="chat-time">'.$value['from']['name'].' @'.date('d M Y H:i:s',strtotime($created_time)).'</div>
                      </div>
                 </div>';
             }
        }

       // echo json_encode($data_chat_twitter);
         echo $str;
    }


    
       public function get_post_conversation_whatsapp()
    {
        // create curl resource 
        $ch = curl_init(); 
        // set url 
        curl_setopt($ch, CURLOPT_URL, "http://lewatwa.disdukcapilsurabaya.id/api/getUserChatLogAll"); 

        //return the transfer as a string 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        // $output contains the output string 
        $output = curl_exec($ch); 

        print_r($output);
    }

    
    public function get_post_conversation_whatsapp_view()
    {
        // create curl resource 
        $ch = curl_init(); 
        // set url 
        curl_setopt($ch, CURLOPT_URL, "http://lewatwa.disdukcapilsurabaya.id/api/getUserChatLogAll"); 

        //return the transfer as a string 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        // $output contains the output string 
        $output = curl_exec($ch); 

        return $output; 
        // print_r($output);
        // var_dump($output);
    }


    public function get_post_conversation_yaestar()
    {
        $this->ajax_check();
        // error_reporting(0);

        // for time zone checking
        $where = array();
        $where['where'] = array(
            'user_id' => $this->user_id,
            'facebook_rx_fb_user_info_id' => $this->session->userdata('facebook_rx_fb_user_info')
            );

        $from_user_id = $this->input->post('from_user_id',true);
        $thread_id = $this->input->post('thread_id',true);
        $page_table_id = $this->input->post('page_table_id',true);
        $last_message_id = $this->input->post('last_message_id',true);  
        $id = $this->input->post('id',true);
        $data_id = $this->input->post('data-id',true);
        $page_info = $this->basic->get_data('facebook_rx_fb_page_info',array('where'=>array('id'=>$page_table_id)));


        //$post_access_token = $page_info[0]['page_access_token'];
       // $page_name = $page_info[0]['page_name'];

        
        // $conversations = $this->fb_rx_login->get_messages_from_thread($thread_id,$post_access_token);

//	$id = 5;
        // $conversations = $this->fb_rx_login->get_messages_from_thread_whats_app($id);

        // $this->basic->insert_data("fb_chat_data",array("user_id"=>$from_user_id,"message"=>serialize($conversations['data'])));



        $db2 =$this->load->database('otherdb',TRUE);

        // $db2->where("src",$id);
        $db2->where('date_format(datetime,"%Y-%m-%d")', 'CURDATE()', FALSE);
        $db2->order_by("src",'DESC');
        $data_chat_twitter = $db2->get('cdr_202206')->result_array();
        // var_dump($data_chat_twitter);

        // var_dump($data_chat_twitter);
        
        $db2->where("src",$id);
        $db2->order_by("src","ASC");
        $db2->limit(1);
        $last_id=$db2->get('cdr_202206')->row("src");

        // var_dump($data_chat_twitter);

        // var_dump($this->db->last_query());
        // foreach ($conversations['events'] as $key => $value) {
        //     # code...
      
        //     $data = array(
        //         'id'=>NULL,
        //         'recept_id' =>$value->message_create->target->recipient_id,
        //         'sender_id' =>$value->message_create->sender_id,
        //         'message'=>$value->message_create->message_data->text
        //     );
        //     $this->db->insert('twitter_chat',$data);

        // }
      



        // var_dump($conversations['events'][0]);
        if(!isset($conversations['events'])) $conversations['events']=array();
        $conversations['events']= array_reverse($conversations['events']);
        // var_dump($conversations['events']);
        // echo "<pre>"; print_r($conversations['data']); exit;

        $lalas = $conversations['events'];


        $show_after_this_index = NULL;
        if(!empty($last_message_id))
        foreach($data_chat_twitter as $key=>$value)
        {
            if($value['sender_id']==$last_id) {
                $show_after_this_index = $key;
                break;
            }
        }
// error_reporting(0);
        // $str = '';
        // var_dump($conversations['events']);

        // var_dump($data_chat_twitter);

        foreach($data_chat_twitter as $key=>$value)
        {
            if(!is_null($show_after_this_index) && $key<=$show_after_this_index) continue;
           
            //  $created_time = $value['tanggal']." UTC";
            // isset($value['from']['name']) ? $value['from']['name'] = $value['from']['name'] : $value['from']['name'] = '';
        //     if($value['sender_id'] == $value['recept_id'])
        //     {
        //         // $str ='
        //         // <div class="chat-item chat-right" style="">
        //         //      <div class="chat-details mr-0 ml-0" message_id="'.$value['uniqueid'].'">
        //         //         <div class="chat-text">'.chunk_split($value['disposition'], 50, '<br>').'</div>
        //         //         <div class="chat-time">'.$value['uniqueid'].' </div>
        //         //      </div>
        //         // </div>';


        //         if ($value['personalcontact'] == NULL) {
        //             # code...
               
        //      $str = '<div class="card">
        //      <div class="card-header">
        //      <h5>'.$value['src'].'</h5>
        //      <br>
        //      <span>'.$value['datetime'].'</span>
        //      </div>
        //      <div class="card-body">
        //        <h6 class="card-title">Status Panggilan :'.$value['disposition'].'</h6>
        //        <p class="card-text">Durasi Telfon : '.$value['duration'].'</p>
        //        <p class="card-text">Tipe Telfon : '.$value['calltype'].'</p>

        //        </div>
        //    </div>';
        //         }else {
                    
        //      $str = '<div class="card">
        // <div class="card-header">
        // <h5>'.$value['personalcontact'].'</h5>
        // <br>
        // <span>'.$value['datetime'].'</span>
        // </div>
        
        //      <div class="card-body">
        //        <h5 class="card-title">'.$value['disposition'].'</h5>
        //        <p class="card-text">Durasi Telfon : '.$value['duration'].'</p>
        //        <p class="card-text">Tipe Telfon : '.$value['calltype'].'</p>

        //      </div>
        //    </div>';
        //         }
        //     }
            
        // echo json_encode($value);

        }
        // var_dump($data_chat_twitter);
        
        echo json_encode($data_chat_twitter);

    }
   



    

    public function get_post_conversation_twitter()
    {
        //$this->ajax_check();
        //error_reporting(0);

        // for time zone checking
        $where = array();
        $where['where'] = array(
            'user_id' => $this->user_id,
            'facebook_rx_fb_user_info_id' => $this->session->userdata('facebook_rx_fb_user_info')
            );

        $from_user_id = $this->input->post('from_user_id',true);
        $thread_id = $this->input->post('thread_id',true);
        $page_table_id = $this->input->post('page_table_id',true);
        $last_message_id = $this->input->post('last_message_id',true);  
        $id = $this->input->post('id',true);
        $data_id = $this->input->post('data-id',true);
        $page_info = $this->basic->get_data('facebook_rx_fb_page_info',array('where'=>array('id'=>$page_table_id)));


        //$post_access_token = $page_info[0]['page_access_token'];
       // $page_name = $page_info[0]['page_name'];

        
        // $conversations = $this->fb_rx_login->get_messages_from_thread($thread_id,$post_access_token);

        //	$id = 5;
        // $conversations = $this->fb_rx_login->get_messages_from_thread_whats_app($id);

        // $this->basic->insert_data("fb_chat_data",array("user_id"=>$from_user_id,"message"=>serialize($conversations['data'])));



            
        $consumer_key = "upB4rDa30tmM2rpJRFLcTQRzi";
        $consumer_secret = "9gzdKxxjgbkN8FOsEa1apLtOs1jehcuyQthpUFNRWs4Q3k6loA";
        $access_token = "1534821335574388736-4TRtMmMKcnTJpfEv9xCCDNVOHna1M8";
        $access_token_secret = "bh6cjSizEtJmyU06y7pkK9mWLbJbIlSRKtXywW7UBE9GG";
        $bearer_token = "AAAAAAAAAAAAAAAAAAAAADYjdgEAAAAAvt7IoiMUFmUbIKc%2BMZM2VYA%2FPlk%3DabSENs97CNSdqvcMzZObzNTt4ZYBnpLYbcNyPGuZReTmRsO8WJ";
        
        $connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
        $response = $connection->get('direct_messages/events/list', ["count" => 400]);

        $conversations = get_object_vars($response);

        $this->db->distinct('message','sender_id','recept_id');
        // $this->db->where("sender_id",$id);
        $this->db->order_by("id",'DESC');
        $data_chat_twitter = $this->db->get('twitter_chat')->result_array();

        // var_dump($data_chat_twitter);

        $this->db->group_by("sender_id");
        $this->db->where("sender_id",$id);
        $this->db->order_by("id","ASC");
        $this->db->limit(1);
        $last_id=$this->db->get('twitter_chat')->row("id");

        // var_dump($data_chat_twitter);

        // var_dump($this->db->last_query());
        foreach ($conversations['events'] as $key => $value) {
            # code...
      
            $data = array(
                'id'=>NULL,
                'recept_id' =>$value->message_create->target->recipient_id,
                'sender_id' =>$value->message_create->sender_id,
                'message'=>$value->message_create->message_data->text,
                'datetime'=>$value->created_timestamp,
            );
            $this->db->insert('twitter_chat',$data);

        }
      



        // var_dump($conversations['events'][0]);
        if(!isset($conversations['events'])) $conversations['events']=array();
        $conversations['events']= array_reverse($conversations['events']);
        // var_dump($conversations['events']);
        // echo "<pre>"; print_r($conversations['data']); exit;

        $lalas = $conversations['events'];


        $show_after_this_index = NULL;
        if(!empty($last_message_id))
        foreach($data_chat_twitter as $key=>$value)
        {
            if($value['sender_id']==$last_id) {
                $show_after_this_index = $key;
                break;
            }
        }
        // error_reporting(0);
        // $str = '';
        // var_dump($conversations['events']);
       
        foreach($data_chat_twitter as $key=>$value)
        {
            if(!is_null($show_after_this_index) && $key<=$show_after_this_index) continue;
           
            //  $created_time = $value['tanggal']." UTC";
            // isset($value['from']['name']) ? $value['from']['name'] = $value['from']['name'] : $value['from']['name'] = '';
            // if($value['sender_id'] == $value['recept_id'])
            // {
            //     $str ='
            //     <div class="chat-item chat-right" style="">
            //          <div class="chat-details mr-0 ml-0" message_id="'.$value['sender_id'].'">
            //             <div class="chat-text">'.chunk_split($value['message'], 50, '<br>').'</div>
            //             <div class="chat-time">'.$value['sender_id'].' </div>
            //          </div>
            //     </div>';
            // }
            // else
            // {
            //     $str ='
            //     <div class="chat-item chat-left" style="">
            //          <div class="chat-details mr-0 ml-0" message_id="'.$value['sender_id'].'">
            //             <div class="chat-text">'.chunk_split($value['message'], 50, '<br>').'</div>
            //             <div class="chat-time">'.$value['sender_id'].'</div>
            //          </div>
            //     </div>';
            // }
            // echo $str;

            // $timestamps =(int) $value['datetime']/1000;
            // $datas = date('Y-m-d h:i:s', $timestamps);
            // echo json_encode($datas);

        }
        echo json_encode($data_chat_twitter);

    }  

    public function reply_to_conversation()
    {
        if($this->is_demo == '1')
        {
            echo "<div class='alert alert-danger text-center'>This feature is disabled in this demo.</div>"; 
            exit();
        }

        $thread_id = $this->input->post('thread_id',true);
        $from_user_id = $this->input->post('from_user_id',true);
        $page_table_id = $this->input->post('page_table_id',true);
        $reply_message = $this->input->post('reply_message',true);
        $message_tag = $this->input->post('message_tag',true);
        if($message_tag=="") $message_tag = "HUMAN_AGENT";

        $message = array
        (
            'recipient' =>array('id'=>$from_user_id),
            'message'=>array('text'=>$reply_message),
            'tag'=>$message_tag
        );
        $message = json_encode($message);


        $page_info = $this->basic->get_data('facebook_rx_fb_page_info',array('where'=>array('id'=>$page_table_id)));
        $post_access_token = $page_info[0]['page_access_token'];

        try
        {            
            $response = $this->fb_rx_login->send_non_promotional_message_subscription($message,$post_access_token);

            if(isset($response['message_id']))
            {
                echo
                '<div class="chat-item chat-right" style="">
                     <div class="chat-details mr-0 ml-0" message_id="'.$response['message_id'].'">
                        <div class="chat-text">'.$reply_message.'</div>
                        <div class="chat-time">'.date('d M Y H:i:s').'</div>
                     </div>
                </div>';
            }
            else 
            {
                if(isset($response["error"]["message"])) $message_sent_id = $response["error"]["message"];  
                if(isset($response["error"]["code"])) $message_error_code = $response["error"]["code"]; 

                if(isset($message_error_code) && $message_error_code=="368") // if facebook marked message as spam 
                {
                    $error_msg=$message_sent_id;
                }

                $error_msg = $message_sent_id;
                echo "<div class='alert alert-danger text-center'>".$error_msg."</div>";
            } 
        }
        catch(Exception $e) 
        {
          echo "<div class='alert alert-danger text-center'>".$e->getMessage()."</div>";
        }

    }
  

    public function messenger_sync_page_messages($page_table_id=0,$social_media="fb"){
        
        $user_id = $this->user_id;
        $where=array('where'=>array('id'=>$page_table_id)); 
        $pages_info_for_sync = $this->basic->get_data("facebook_rx_fb_page_info",$where);
        if(empty($pages_info_for_sync)) {
            return '<br><div class="alert alert-danger text-center w-100"><b class="m-0">'.$this->lang->line("Page not found.").'</b></div>';
        }
        $str = '';       

        // getting latest 200 data
        $get_concersation_info = $this->fb_rx_login->get_all_conversation_page($pages_info_for_sync[0]['page_access_token'],$pages_info_for_sync[0]['page_id'],1,'','',$social_media);

    

        if(isset($get_concersation_info['error'])){
            $response['error']='1';
            $response['error_message']=isset($get_concersation_info['error_msg']) ? $get_concersation_info['error_msg']:"Unknown Error Occurred";
            return $response;
        }
        $subscriber_ids = array_column($get_concersation_info, 'id');
        $get_subscriber_info = [];
        if(!empty($subscriber_ids))
        $get_subscriber_info = $this->basic->get_data("messenger_bot_subscriber",['where_in'=>['subscribe_id'=>$subscriber_ids]],'profile_pic,image_path,subscribe_id');

        $subscriber_info = [];
        foreach($get_subscriber_info as $key=>$val){
            $subscriber_info[$val['subscribe_id']] = ['profile_pic'=>$val['profile_pic'],'image_path'=>$val['image_path']];
        }

        foreach($get_concersation_info as $conversion_info)
        {

            $from_user     = $conversion_info['name'] ?? "";
            $from_user_id  = $conversion_info['id'] ?? "";
            $last_snippet  = $conversion_info['snippet'] ?? "";
            $message_count = $conversion_info['message_count'] ?? 0;
            $thread_id     = $conversion_info['thead_id'] ?? "";
            $inbox_link    = $conversion_info['link'] ?? "";
            $unread_count  = $conversion_info['unread_count'] ?? 0;

            $rand = rand(1,4);
            $default = base_url('assets/img/avatar/avatar-'.$rand.'.png');
            $profile_pic = isset($subscriber_info[$from_user_id]['profile_pic']) && $subscriber_info[$from_user_id]['profile_pic']!="" ? $subscriber_info[$from_user_id]["profile_pic"] :  $default;
            $subscriber_image =isset($subscriber_info[$from_user_id]["image_path"]) && $subscriber_info[$from_user_id]["image_path"]!="" ? base_url($subscriber_info[$from_user_id]["image_path"]) : $profile_pic;

            $str.='
            <li class="media py-2 my-0 px-4 open_conversation" thread_id="'.$thread_id.'" from_user="'.htmlspecialchars($from_user).'" from_user_id="'.$from_user_id.'" page_table_id="'.$page_table_id.'" style="cursor:pointer">
                <img alt="image" class="mr-3 rounded-circle border" width="50" height="50" src="'.$subscriber_image.'">
                <div class="media-body">
                  <div class="mt-0 mb-1 font-weight-bold text-primary">'.$from_user.'<span class="badge badge-danger badge-pill ml-2 px-2 py-1 d-none">2</span></div>
                  <div class="text-small font-600-bold"><i class="fas fa-circle text-success pb-1" style="font-size:8px"></i> '.$from_user_id.'</div>
                </div>
            </li>';
        }
        return $str;                
    
        
        
    }

    public function get_dropdown_postback($page_table_id=0,$social_media='fb',$return='1')
    {
        if($return!='1') $this->ajax_check();
        if($social_media=='') $social_media = 'fb';

        $postback_data=$this->basic->get_data("messenger_bot_postback",array("where"=>array("page_id"=>$page_table_id,"media_type" => $social_media,"user_id"=>$this->user_id,"is_template"=>"1",'template_for'=>'reply_message')),'postback_id,template_name,id','','','','postback_type asc');

        $push_postback = '<div class="list-group">';
        foreach ($postback_data as $key => $value) 
        {
            $push_postback .= '
            <a href="#" data-id="'.$value['postback_id'].'" class="list-group-item list-group-item-action flex-column align-items-start postback-item">
                <div class="d-flex w-100 justify-content-between">
                  <h6 class="mb-1"><i class="fas fa-circle text-success"></i> '.$this->lang->line('Send').' : '.$value['template_name'].'</h6>
                </div>
            </a>';
        }
        $push_postback .=' </div>';
        if($return=='1') return $push_postback;
        else echo $push_postback;

    }

    public function send_postback_reply(){
        $page_table_id = $this->input->post('page_table_id');
        $subscriber_id = $this->input->post('subscriber_id');
        $postback_id = $this->input->post('postback_id');
        $social_media = $this->input->post('social_media');

        $subscriber_data = $this->basic->get_data("messenger_bot_subscriber",['where'=>['subscribe_id'=>$subscriber_id]]);

        if(empty($subscriber_data)) $subscriber_info = 
        [
            0 => [
                'subscribe_id'=>$subscriber_id,
                'social_media'=>$social_media,
                'status'=>'1'
            ]
        ];
        else $subscriber_info = $subscriber_data ?? [];

        $get_page_info = $this->basic->get_data("facebook_rx_fb_page_info",['where'=>['id'=>$page_table_id]],'page_id');
        $page_id = $get_page_info[0]['page_id'] ?? '';

        $where['where'] = array('messenger_bot.fb_page_id' => $page_id,"postback_id"=>$postback_id,'facebook_rx_fb_page_info.bot_enabled' => '1');
        $join = array('facebook_rx_fb_page_info'=>"facebook_rx_fb_page_info.id=messenger_bot.page_id,left");   
        $get_postback = $this->basic->get_data('messenger_bot',$where,array("messenger_bot.*","facebook_rx_fb_page_info.page_access_token as page_access_token","facebook_rx_fb_page_info.enable_mark_seen as enable_mark_seen","facebook_rx_fb_page_info.enbale_type_on as enbale_type_on","facebook_rx_fb_page_info.reply_delay_time as reply_delay_time"),$join,'','','messenger_bot.id asc');

        $get_postback = $get_postback[0] ?? [];

        $post_data = ['value'=>json_encode($get_postback),'sender_id'=>$subscriber_id,'subscriber_info'=>json_encode($subscriber_info),'page_id'=>$page_id];

        $url = base_url('messenger_bot/send_message_bot_reply');
        $ch = curl_init();
        $headers = array("Content-type: application/json");          
        curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);        
        
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data); 
        
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
        // curl_setopt($ch, CURLOPT_COOKIEJAR,'cookie.txt');  
        // curl_setopt($ch, CURLOPT_COOKIEFILE,'cookie.txt');  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");          
        echo $st=curl_exec($ch);
    }

    public function search_subscriber_database(){
        $this->ajax_check();
        $page_table_id = $this->input->post('page_table_id',true);
        $social_media = $this->input->post('social_media',true);
        $search_value = $this->input->post('filter',true);
        if($social_media=='') $social_media = 'fb';

        if($social_media=='ig') $search_columns = array('full_name','subscribe_id');
        else $search_columns = array('first_name','last_name','subscribe_id');

        $where_custom="messenger_bot_subscriber.user_id = ".$this->user_id." AND subscriber_type='messenger' AND social_media = '".$social_media."' AND page_table_id = ". $page_table_id;
        if ($search_value != '') 
        {
            foreach ($search_columns as $key => $value) 
            $temp[] = $value." LIKE "."'%$search_value%'";
            $imp = implode(" OR ", $temp);
            $where_custom .=" AND (".$imp.") ";
        }
        $this->db->where($where_custom);
        $info = $this->basic->get_data('messenger_bot_subscriber',$where='',['client_thread_id','subscribe_id','image_path','profile_pic','first_name','last_name','full_name']);

        $str = '';

        foreach($info as $key=>$value){
            $thread_id = $value['client_thread_id'];
            $from_user_id = $value['subscribe_id'];
            $from_user = $value['full_name']!='' ? $value['full_name'] : $value['first_name'].' '.$value['last_name'];

            $rand = rand(1,4);
            $default = base_url('assets/img/avatar/avatar-'.$rand.'.png');
            $profile_pic = $value['profile_pic']!="" ? $value["profile_pic"] :  $default;
            $subscriber_image = $value["image_path"]!="" ? base_url($value["image_path"]) : $profile_pic;

            $str.='
            <li class="media py-2 my-0 px-4 open_conversation database_search_item" thread_id="'.$thread_id.'" from_user="'.htmlspecialchars($from_user).'" from_user_id="'.$from_user_id.'" page_table_id="'.$page_table_id.'" style="cursor:pointer">
                <img alt="image" class="mr-3 rounded-circle border" width="50" height="50" src="'.$subscriber_image.'">
                <div class="media-body">
                  <div class="mt-0 mb-1 font-weight-bold text-primary">'.$from_user.'<span class="badge badge-danger badge-pill ml-2 px-2 py-1 d-none">2</span></div>
                  <div class="text-small font-600-bold"><i class="fas fa-circle text-success pb-1" style="font-size:8px"></i> '.$from_user_id.'</div>
                </div>
            </li>';
        }
        echo $str;

        
    }




}