<?php
defined( 'ABSPATH' ) || exit();

class WC_Pay_By_Paynow_PL_Locking_Mechanism {

    const LOCKS_DIR = 'paynow-locks';
    const LOCKS_PREFIX = 'paynow-lock_';
    const LOCKED_TIME = 35;

    /**
     * @string
     */
    public $locks_dir_path;

    /**
     * @var bool
     */
    public $lock_enabled = true;

    /**
     * Constructor of WC_Pay_By_Paynow_PL_Locking_Mechanism
     */
    public function __construct()
    {
        // Setup locks dir
        try {
            $upload_dir = wp_upload_dir();
            if (isset($upload_dir['basedir'])){
                $lock_path = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . self::LOCKS_DIR;
                wp_mkdir_p($lock_path);
            }else{
                $lock_path = sys_get_temp_dir();
                // phpcs:ignore
                @mkdir($lock_path);
            }
            // phpcs:ignore
            if ( is_dir( $lock_path ) && is_writable( $lock_path ) ) {
                $this->locks_dir_path = $lock_path;
            } else {
                $this->locks_dir_path = sys_get_temp_dir();
            }
        } catch ( \Exception $exception ) {
            $this->locks_dir_path = sys_get_temp_dir();
        }
        // phpcs:ignore
        $this->lock_enabled = is_writable( $this->locks_dir_path );
    }

    /**
     * @param $external_id
     * @return bool
     */
    public function checkAndCreate( $external_id )
    {
        if (!$this->lock_enabled) {
            return false;
        }
        $lock_file_path = $this->generate_lock_path($external_id);
        // phpcs:ignore
        $lock_exists = file_exists($lock_file_path);
        // phpcs:ignore
        if ($lock_exists && (filemtime($lock_file_path) + self::LOCKED_TIME) > time()) {
            return true;
        } else {
            $this->create( $external_id, $lock_exists );
            return false;
        }
    }

    /**
     * @param $external_id
     * @return void
     */
    public function delete( $external_id )
    {
        if ( empty( $external_id ) ) {
            return;
        }
        $lock_file_path = $this->generate_lock_path( $external_id );
        // phpcs:ignore
        if ( file_exists( $lock_file_path ) ){
            // phpcs:ignore
            unlink( $lock_file_path );
        }
    }


    /**
     * @param $external_id
     * @param $lock_exists
     * @return void
     */
    private function create( $external_id, $lock_exists )
    {
        $lock_path = $this->generate_lock_path( $external_id );
        if ($lock_exists) {
            // phpcs:ignore
            touch($lock_path);
        } else {
            // phpcs:ignore
            @file_put_contents($lock_path, '');
        }
    }

    /**
     * @param $external_id
     * @return string
     */
    private function generate_lock_path( $external_id )
    {
        return $this->locks_dir_path . DIRECTORY_SEPARATOR . self::LOCKS_PREFIX . $external_id . '.lock';
    }
}
