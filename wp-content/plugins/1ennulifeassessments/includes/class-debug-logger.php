<?php
/**
 * ENNU Life Debug Logger
 * Provides centralized logging and debugging functionality
 * 
 * @package ENNU_Life
 * @version 14.1.12
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Debug_Logger {
    
    /**
     * Log levels
     */
    const LEVEL_ERROR = 'error';
    const LEVEL_WARNING = 'warning';
    const LEVEL_INFO = 'info';
    const LEVEL_DEBUG = 'debug';
    
    /**
     * Log file path
     */
    private static $log_file = null;
    
    /**
     * Initialize logger
     */
    public static function init() {
        if ( self::$log_file === null ) {
            $upload_dir = wp_upload_dir();
            $log_dir = $upload_dir['basedir'] . '/ennu-logs/';
            
            // Create log directory if it doesn't exist
            if ( ! file_exists( $log_dir ) ) {
                wp_mkdir_p( $log_dir );
                
                // Add .htaccess to protect log files
                $htaccess_content = "Order deny,allow\nDeny from all\n";
                file_put_contents( $log_dir . '.htaccess', $htaccess_content );
            }
            
            self::$log_file = $log_dir . 'ennu-debug-' . date( 'Y-m-d' ) . '.log';
        }
    }
    
    /**
     * Log a message
     */
    public static function log( $message, $level = self::LEVEL_INFO, $context = array() ) {
        // Only log if debugging is enabled
        if ( ! self::is_debug_enabled() ) {
            return;
        }
        
        self::init();
        
        $timestamp = current_time( 'Y-m-d H:i:s' );
        $user_id = get_current_user_id();
        $ip_address = self::get_client_ip();
        
        // Format the log entry
        $log_entry = sprintf(
            "[%s] [%s] [User:%d] [IP:%s] %s",
            $timestamp,
            strtoupper( $level ),
            $user_id,
            $ip_address,
            $message
        );
        
        // Add context if provided
        if ( ! empty( $context ) ) {
            $log_entry .= ' | Context: ' . wp_json_encode( $context );
        }
        
        $log_entry .= PHP_EOL;
        
        // Write to log file
        error_log( $log_entry, 3, self::$log_file );
        
        // Also log to WordPress debug log if WP_DEBUG is enabled
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'ENNU: ' . $message );
        }
    }
    
    /**
     * Log error message
     */
    public static function error( $message, $context = array() ) {
        self::log( $message, self::LEVEL_ERROR, $context );
    }
    
    /**
     * Log warning message
     */
    public static function warning( $message, $context = array() ) {
        self::log( $message, self::LEVEL_WARNING, $context );
    }
    
    /**
     * Log info message
     */
    public static function info( $message, $context = array() ) {
        self::log( $message, self::LEVEL_INFO, $context );
    }
    
    /**
     * Log debug message
     */
    public static function debug( $message, $context = array() ) {
        self::log( $message, self::LEVEL_DEBUG, $context );
    }
    
    /**
     * Check if debug logging is enabled
     */
    private static function is_debug_enabled() {
        $settings = get_option( 'ennu_life_settings', array() );
        return isset( $settings['enable_logging'] ) ? $settings['enable_logging'] : false;
    }
    
    /**
     * Get client IP address
     */
    private static function get_client_ip() {
        $ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
        
        foreach ( $ip_keys as $key ) {
            if ( array_key_exists( $key, $_SERVER ) === true ) {
                foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
                    $ip = trim( $ip );
                    if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Get recent log entries for admin display
     */
    public static function get_recent_logs( $lines = 50 ) {
        self::init();
        
        if ( ! file_exists( self::$log_file ) ) {
            return array();
        }
        
        $logs = array();
        $file = new SplFileObject( self::$log_file );
        $file->seek( PHP_INT_MAX );
        $total_lines = $file->key();
        
        $start_line = max( 0, $total_lines - $lines );
        $file->seek( $start_line );
        
        while ( ! $file->eof() ) {
            $line = trim( $file->fgets() );
            if ( ! empty( $line ) ) {
                $logs[] = $line;
            }
        }
        
        return array_reverse( $logs );
    }
    
    /**
     * Clear log files
     */
    public static function clear_logs() {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/ennu-logs/';
        
        if ( is_dir( $log_dir ) ) {
            $files = glob( $log_dir . '*.log' );
            foreach ( $files as $file ) {
                if ( is_file( $file ) ) {
                    unlink( $file );
                }
            }
        }
        
        self::info( 'Log files cleared by admin' );
    }
}

