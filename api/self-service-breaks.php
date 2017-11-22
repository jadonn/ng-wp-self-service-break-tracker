<?php
defined( 'ABSPATH' ) or die( 'Access denied' );
require 'vendor/autoload.php';
require 'self-service-break.php';
/*
Plugin Name: Self Service Break Tracker
Description: Self service break tracking application
Version: 1.0
Author: Jadon Naas
*/

class SelfServiceBreakRestController extends WP_REST_Controller{

    private $db_name = 'self-service-break-app';

    public function register_routes(){
        $version = '1';
        $namespace = '/self-service-breaks/v' . $version;
        $base = 'api';
        register_rest_route( $namespace, '/' . $base . '/breaks', array(
            array(
                'methods'               => 'GET',
                'callback'              => array( $this, 'get_breaks' ),
                'permission_callback'   => array( $this, 'get_breaks_permission_callback' )
                )
            ));
        register_rest_route( $namespace, '/' . $base . '/submit', array(
            array(
                'methods'               => 'POST',
                'callback'              => array( $this, 'add_break' ),
                'permission_callback'   => array( $this, 'add_break_permission_callback' )
            )
            ));
        register_rest_route( $namespace, '/' . $base . '/close', array(
            array(
                'methods'               => 'POST',
                'callback'              => array( $this, 'close_break' ),
                'permission_callback'   => array( $this, 'close_break_permission_callback' )
            )
            ));
        register_rest_route( $namespace, '/' . $base . '/admin/users', array(
            array(
                'methods'               => 'GET',
                'callback'              => array( $this, 'get_users' ),
                'permission_callback'   => array( $this, 'get_users_permission_callback' )
            ),
            array(
                'methods'               => 'POST',
                'callback'              => array( $this, 'add_user' ),
                'permission_callback'   => array( $this, 'add_user_permission_callback' )
            ),
            array(
                'methods'               => 'DELETE',
                'callback'              => array( $this, 'delete_user' ),
                'permission_callback'   => array( $this, 'delete_user_permission_callback' )
            )
            ));
        register_rest_route( $namespace, '/' . $base . '/admin/leaders', array(
            array(
                'methods'               => 'GET',
                'callback'              => array( $this, 'get_leaders' ),
                'permission_callback'   => array( $this, 'get_leaders_permission_callback' )
            ),
            array(
                'methods'               => 'POST',
                'callback'              => array( $this, 'add_leader' ),
                'permission_callback'   => array( $this, 'add_leader_permission_callback' )
            ),
            array(
                'methods'               => 'DELETE',
                'callback'              => array( $this, 'delete_leader' ),
                'permission_callback'   => array( $this, 'delete_leader_permission_callback' )
            )
            ));
        register_rest_route( $namespace, '/' . $base . '/admin/lock-breaks', array(
            array(
                'methods'               => 'GET',
                'callback'              => array( $this, 'get_lock_breaks_status' ),
                'permission_callback'   => array( $this, 'get_lock_breaks_status_permission_callback' )
            ),
            array(
                'methods'               => 'POST',
                'callback'              => array( $this, 'lock_breaks' ),
                'permission_callback'   => array( $this, 'lock_breaks_permission_callback' )
            ),
            array(
                'methods'               => 'DELETE',
                'callback'              => array( $this, 'unlock_breaks' ),
                'permission_callback'   => array( $this, 'unlock_breaks_permission_callback' )
            )
            ));
    }

    public function get_breaks( $request ){
        $client = new MongoDB\Client('mongodb://localhost/');
        $collection = $client->selectCollection( $this->db_name, 'breaks');

        try{
            $cursor = $collection->find( [ 'active' => true ] );
            $results = array();
            foreach($cursor as $document){
                array_push($results, $document->bsonSerialize());
            }
            $response_data = array( 'success' => true, 'result' => $results );
        }catch( Exception $e ){
            $response_data = array( 'success' => false, 'result' => 'Caught exception: ' . $e->getMessage() . '\n');
        }
        $response = new WP_REST_Response( $response_data, 200 );
        return $response;
    }

    public function get_breaks_permission_callback( $request ){
        return true;
    }

    public function add_break( $request ){
        $username = $request->get_param( 'username' );
        $users = get_option( 'self_service_users' );
        if( $username === null ){
            $response_data = array( 'success' => false, 'result' => 'You must provide a username.' );
        }elseif( !in_array( $username, $users ) ){
            $response_data = array( 'success' => false, 'result' => $username . ' is not allowed to request breaks.' );
        }
        else{
            $response_data = array( 'success' => false, 'result' => 'There was an error.' );
            $client = new MongoDB\Client( 'mongodb://localhost/' );
            $collection = $client->selectCollection( $this->db_name, 'breaks' );
            try{
                $newBreak = new SelfService( $username );
                $insertResult = $collection->insertOne( $newBreak );
                if( $insertResult->isAcknowledged() ){
                    $response_data = array( 'success' => true, 'result' => 'Your break request has been submitted!', 'hash' => $newBreak->bsonSerialize()[ 'hash' ] );
                }else{
                    $response_data = array( 'success' => false, 'result' => 'There was an error. No change was saved.' );
                }
            }
            catch( Exception $e ){
                $response_data = array( 'success' => false, 'result' => 'Caught exception: ' . $e->getMessage() . '\n' );
            }
        }
        $this->checkCurrentBreak();
        $response = new WP_REST_Response( $response_data, 200 );
        return $response;
    }

    public function add_break_permission_callback( $request ){
        return true;
    }

    public function close_break( $request ){
        $client = new MongoDB\Client( 'mongodb://localhost' );
        $collection = $client->selectCollection( $this->db_name, 'breaks' );
        $response_data = array( 'success' => false, 'result' => 'There was a problem or error.' );
        try{
            $result = $collection->findOne( [ 'active' => true, 'hash' => $request->get_param( 'break' ) ] );
            if( $result ){
                $break = $result->bsonSerialize();
                $updateTime = new MongoDB\BSON\UTCDateTime();
                if( $break[ 'status' ] === 'clearing' ){
                    $update_result = $collection->findOneAndUpdate( [ 'hash' => $break[ 'hash' ] ], [ '$set' => [ 'status' => 'on break', 'updatedAt' => $updateTime  ] ] );
                    $response_data = array( 'success' => true, 'result' => 'Break updated!' );
                    $this->sendLeaderNotification( $break[ 'username' ] . ' has finished clearing contacts.' );
                }else{
                    $update_result = $collection->findOneAndUpdate( [ 'hash' => $break[ 'hash' ] ], [ '$set' => [ 'status' => 'closed', 'active' => false, 'updatedAt' => $updateTime ] ] );
                    $response_data = array( 'success' => true, 'result' => 'Break closed!' );
                    $this->sendLeaderNotification( $break[ 'username' ] . ' has returned from break.' );
                }
            }else{
                $response_data = array( 'success' => false, 'result' => 'No break could be found for the given hash.' );
            }
        }catch( Exception $e ){
            $result = $e->getMessage();
        }
        $this->checkCurrentBreak();
        $response = new WP_REST_Response( $response_data, 200 );
        return $response;
    }

    public function close_break_permission_callback( $request ){
        return true;
    }
    
    public function get_users( $request ){
        $users = get_option( 'self_service_users', false );
        if( $users === false){
            $response_data = array( 'success' => false, 'result' => 'There are no users defined.' );
        }else{
            $response_data = array( 'success' => true, 'result' => $users );
        }
        $response = new WP_REST_Response( $response_data, 200 );
        return $response;
    }

    public function get_users_permission_callback( $request ){
        return true;
    }

    public function add_user( $request ){
        $user = $request->get_param( 'username' );
        $response_data = array( 'success' => false, 'result' => 'An unknown error was encountered.' );
        if( $user === null ){
            $response_data = array( 'success' => false, 'result' => 'You must provide a username.' );
        }else{
            $users = get_option( 'self_service_users', array());
            if( in_array( $user, $users ) ){
                $response_data = array( 'success' => false, 'result' => $user . ' is already a registered user.' );
            }else{
                array_push( $users, $user );
                $result = update_option( 'self_service_users', $users, false );
                if( $result ){
                    $response_data = array( 'success' => true, 'result' => $user . ' added successfully to list of users.' );
                }else{
                    $response_data = array( 'success' => false, 'result' => 'There was a problem when adding the username.' );
                }
            }
        }
        $response = new WP_REST_Response( $response_data, 200 );
        return $response;
    }

    public function add_user_permission_callback( $request ){
        return true;
    }

    public function delete_user( $request ){
        $user = $request->get_param( 'username' );
        $response_data = array( 'success' => false, 'result' => 'An unknown error was encountered.' );
        if( $user === null){
            $response_data = array( 'success' => false, 'result' => 'You must provide a username.' );
        }else{
            $users = get_option( 'self_service_users', false );
            if( $users === false ){
                $response_data = array( 'success' => false, 'result' => 'There are no users defined.' );
            }else{
                if( in_array( $user, $users ) ){
                    $user_name_index = array_search( $user, $users );
                    unset( $users[ $user_name_index ] );
                    $result = update_option( 'self_service_users', $users, false );
                    if( $result ){
                        $response_data = array( 'success' => true, 'result' => $user . ' was removed from the list of users.' );
                    }else{
                        $response_data = array( 'success' => false, 'result' => 'There was a problem when removing the username.' );
                    }
                }else{
                    $response_data = array( 'success' => false, 'result' => $user . ' is not a registered user.' );
                }
            }
        }
        $response = new WP_REST_Response( $response_data, 200 );
        return $response;
    }

    public function delete_user_permission_callback( $request ){
        return true;
    }

    public function get_leaders( $request ){
        $leaders = get_option( 'self_service_leaders', false );
        $response_data = array( 'success' => false, 'result' => 'An unknown error was encountered.' );
        if( $leaders === false ){
            $response_data = array( 'success' => false, 'result' => 'There are no leaders defined.' );
        }else{
            $response_data = array( 'success' => true, 'result' => $leaders );
        }
        $response = new WP_REST_Response( $response_data, 200 );
        return $response;
    }

    public function get_leaders_permission_callback( $request ){
        return true;
    }

    public function add_leader( $request ){
        $leader_name = $request->get_param( 'leaderName' );
        $response_data = array( 'success' => false, 'result' => 'An unknown error was encountered.' );
        if( $leader_name === null ){
            $response_data = array( 'success' => false, 'result' => 'Missing argument leaderName.' );
        }else{
            $leaders = get_option( 'self_service_leaders', array() );
            if( in_array( $leader_name, $leaders ) ){
                $response_data = array( 'success' => false, 'result' => $leader_name . ' is already a registered leader.' );
            }else{
                array_push( $leaders, $leader_name );
                $result = update_option( 'self_service_leaders', $leaders, false );
                if( $result ){
                    $response_data = array( 'success' => true, 'result' => $leader_name . ' added successfully to list of leaders.' );
                }else{
                    $response_data = array( 'success' => false, 'result' => 'There was a problem when adding the leader.' );
                }
            }
        }
        $response = new WP_REST_Response( $response_data, 200 );
        return $response;
    }

    public function add_leader_permission_callback( $request ){
        return true;
    }

    public function delete_leader( $request ){
        $leader_name = $request->get_param( 'leaderName' );
        $response_data = array( 'success' => false, 'result' => 'An unknown error was encountered.' );
        if( $leader_name === null ){
            $response_data = array( 'success' => false, 'result' => 'Missing argument leaderName.' );
        }else{
            $leaders = get_option( 'self_service_leaders', false );
            if( $leaders === false ){
                $response_data = array( 'success' => false, 'result' => 'There are no leaders defined.' );
            }else{
                if( in_array( $leader_name, $leaders ) ){
                    $leader_name_index = array_search( $leader_name, $leaders );
                    unset( $leaders[ $leader_name_index ] );
                    $result = update_option( 'self_service_leaders', $leaders, false );
                    if( $result ){
                        $response_data = array( 'success' => true, 'result' => $leader_name . ' was removed from the list of leaders.' );
                    }else{
                        $response_data = array( 'success' => false, 'result' => 'There was a problem when removing the leader.' );
                    }
                }else{
                    $response_data = array( 'success' => false, 'result' => $leader_name . ' is not a registered user.' );
                }
            }
        }
        $response = new WP_REST_Response( $response_data, 200 );
        return $response;
    }

    public function delete_leader_permission_callback( $request ){
        return true;
    }

    public function get_lock_breaks_status( $request ){
        $lock_breaks_status = get_option( 'self_service_lock_breaks', false );
        $response_data = array( 'success' => true, 'result' => $lock_breaks_status );
        $response = new WP_REST_Response( $response_data, 200 );
        return $response;
    }

    public function get_lock_breaks_status_permission_callback( $request ){
        return true;
    }

    public function lock_breaks( $request ){
        $lock_breaks_status = get_option( 'self_service_lock_breaks', false );
        $response_data = array( 'success' => false, 'result' => 'There was an error of some kind.' );
        if( $lock_breaks_status ){
            $response_data = array( 'success' => false, 'result' => 'Breaks are already locked.' );
        }else{
            $lock_breaks_result = update_option( 'self_service_lock_breaks', true, false );
            if( $lock_breaks_result ){
                $response_data = array( 'success' => true, 'result' => 'Breaks have been locked.' );
            }
        }
        $response = new WP_REST_Response( $response_data, 200 );
        return $response;
    }

    public function lock_breaks_permission_callback( $request ){
        return true;
    }

    public function unlock_breaks( $request ){
        $response_data = array( 'success' => false, 'result' => 'There was an error of some kind.' );
        $lock_breaks_status = get_option( 'self_service_lock_breaks', false );
        if( $lock_breaks_status === false ){
            $response_data = array( 'success' => false, 'result' => 'Breaks are not currently locked. No action was taken.' );
        }else{
            $result = delete_option( 'self_service_lock_breaks' );
            if( $result ){
                $response_data = array( 'success' => true, 'result' => 'Breaks have been unlocked.' );
                $this->checkCurrentBreak();
            }
        }
        $response = new WP_REST_Response( $response_data, 200 );
        return $response;
    }

    public function unlock_breaks_permission_callback( $request ){
        return true;
    }

    function checkCurrentBreak(){
        $lock_breaks_status = get_option( 'self_service_lock_breaks', false );
        if( $lock_breaks_status === false ){
            $client = new MongoDB\Client( 'mongodb://localhost' );
            $collection = $client->selectCollection( $this->db_name, 'breaks' );
            $result = [];
            try{
                $result = $collection->findOne( [ 'active' => true ] );
                if( $result ){
                    $break = $result->bsonSerialize();
                    if( $break['status'] === 'pending' ){
                        $hash = $break[ 'hash' ];
                        $updateTime = new MongoDB\BSON\UTCDateTime();
                        $collection->findOneAndUpdate( [ 'hash' => $hash ], [ '$set' => [ 'status' => 'clearing', 'updatedAt' => $updateTime ] ] );
                        $this->sendBreakNotification( $break[ 'username' ], "It is time for your break! Use the following hash code to update your break:\n" . $hash );
                        $this->sendLeaderNotification( $break[ 'username' ] . " is now clearing for break with hash:\n" . $hash );
                    }
                }
            }catch( Exception $e){
                $result = $e->getMessage();
            }
            return $result;
        }else{
            return null;
        }
    }

    function sendLeaderNotification( $message ){
        $leaders = get_option( 'self_service_leaders', false );
        if( $leaders ){
            foreach( $leaders as $leader ){
                $breakPost = curl_init(SLACK_API_URL);
                curl_setopt($breakPost, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $payload = json_encode(array('text' => $message, 'channel' => "@$leader", 'username' => 'SelfServices'));
                curl_setopt($breakPost, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($breakPost, CURLOPT_RETURNTRANSFER, true);
                curl_exec($breakPost);
                curl_close($breakPost);
            }
        }
        $breakChannelPost = curl_init(SLACK_API_URL);
        curl_setopt($breakChannelPost, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $payload = json_encode(array('text' => $message, 'username' => 'SelfServices'));
        curl_setopt($breakChannelPost, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($breakChannelPost, CURLOPT_RETURNTRANSFER, true);
        curl_exec($breakChannelPost);
        curl_close($breakChannelPost);
    }

    function sendBreakNotification( $pidginUser, $message ){
        $users = get_option( 'self_service_users', false );
        if( $users ){
            if( in_array( $pidginUser, $users ) ){
                $breakPost = curl_init(SLACK_API_URL);
                curl_setopt($breakPost, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $payload = json_encode(array('text' => $message, 'channel' => "@$pidginUser", 'username' => 'SelfServices'));
                curl_setopt($breakPost, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($breakPost, CURLOPT_RETURNTRANSFER, true);
                curl_exec($breakPost);
                curl_close($breakPost);
                return 'Attempted to send message.';
            }else{
                return 'User is not allowed to receive chat messages';
            }
        }else{
            return 'You must configure users in order to use chat messaging.';
        }
    }
}

$self_service_breaks = new SelfServiceRestController();

add_action( 'rest_api_init', [ $self_service_breaks, 'register_routes' ] );

function self_service_break_app_shortcode(){
    wp_register_script( 'self-service-break-app-main-bundle', plugins_url( '/ng/breaks/main.bundle.js', __FILE__ ) );
    wp_localize_script( 'self-service-break-app-main-bundle', 'breaksAppInfo', array(
        'home_url' => home_url()
    ));
    ob_start();
    wp_enqueue_script( 'self-service-break-app-inline-bundle', plugins_url( '/ng/breaks/inline.bundle.js', __FILE__ ) );
    wp_enqueue_script( 'self-service-break-app-polyfills-bundle', plugins_url( '/ng/breaks/polyfills.bundle.js', __FILE__ ) );
    wp_enqueue_script( 'self-service-break-app-styles-bundle', plugins_url( '/ng/breaks/styles.bundle.js', __FILE__ ) );
    wp_enqueue_script( 'self-service-break-app-vendor-bundle', plugins_url( '/ng/breaks/vendor.bundle.js', __FILE__ ) );
    wp_enqueue_script( 'self-service-break-app-main-bundle', plugins_url( '/ng/breaks/main.bundle.js', __FILE__ ) );
    echo '<base href="' . $_SERVER[ 'REQUEST_URI' ] . '">';
    echo '<app-root></app-root>';
    echo ob_get_clean();
}

add_shortcode( 'self_service_break_app', 'self_service_break_app_shortcode' );

add_action( 'admin_menu', 'self_service_break_app_menu' );
function self_service_break_app_menu(){
    add_menu_page( 'Self Service Breaks', 'Self Service Breaks', 'manage_options', 'self-service-breaks/self-service-breaks.php', 'view_self_service_breaks_app', 'dashicons-clock', '4.105' );
}

function view_self_service_breaks_app(){
    if( !current_user_can( 'manage_options' ) ){
        wp_die( __( 'You do not have permission to access this page.' ) );
    }
    wp_register_script( 'self-service-break-app-admin-main-bundle', plugins_url( '/ng/admin/main.bundle.js', __FILE__ ) );
    wp_localize_script( 'self-service-break-app-admin-main-bundle', 'adminAppInfo', array(
        'home_url'  => home_url(),
        'nonce'     => wp_create_nonce( 'wp_rest' )
    ));
    ob_start();
    wp_enqueue_script( 'self-service-break-app-admin-inline-bundle', plugins_url( '/ng/admin/inline.bundle.js', __FILE__ ) );
    wp_enqueue_script( 'self-service-break-app-admin-polyfills-bundle', plugins_url( '/ng/admin/polyfills.bundle.js', __FILE__ ) );
    wp_enqueue_script( 'self-service-break-app-admin-styles-bundle', plugins_url( '/ng/admin/styles.bundle.js', __FILE__ ) );
    wp_enqueue_script( 'self-service-break-app-admin-vendor-bundle', plugins_url( '/ng/admin/vendor.bundle.js', __FILE__) );
    wp_enqueue_script( 'self-service-break-app-admin-main-bundle', plugins_url( '/ng/admin/main.bundle.js', __FILE__ ) );
    echo '<base href="' . $_SERVER[ 'REQUEST_URI' ] . '">';
    echo '<app-root></app-root>';
    echo ob_get_clean();
}