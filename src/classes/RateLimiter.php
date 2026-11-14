<?php
/**
 * Rate Limiter Class
 * Implements rate limiting to prevent brute force attacks
 */

class RateLimiter {
    
    /**
     * Check if request should be rate limited
     * 
     * @param string $key Unique identifier (e.g., IP address, user ID)
     * @param int $maxAttempts Maximum number of attempts allowed
     * @param int $windowSeconds Time window in seconds
     * @return bool True if rate limit exceeded, false otherwise
     */
    public static function isRateLimited($key, $maxAttempts = 5, $windowSeconds = 900) {
        $cacheKey = 'rate_limit_' . md5($key);
        $attempts = self::getAttempts($cacheKey);
        
        if ($attempts >= $maxAttempts) {
            return true;
        }
        
        self::incrementAttempts($cacheKey, $windowSeconds);
        return false;
    }
    
    /**
     * Get remaining attempts
     * 
     * @param string $key Unique identifier
     * @param int $maxAttempts Maximum number of attempts allowed
     * @return int Remaining attempts
     */
    public static function getRemainingAttempts($key, $maxAttempts = 5) {
        $cacheKey = 'rate_limit_' . md5($key);
        $attempts = self::getAttempts($cacheKey);
        return max(0, $maxAttempts - $attempts);
    }
    
    /**
     * Get time until rate limit resets (in seconds)
     * 
     * @param string $key Unique identifier
     * @return int Seconds until reset, or 0 if not rate limited
     */
    public static function getResetTime($key) {
        $cacheKey = 'rate_limit_' . md5($key);
        $cacheFile = sys_get_temp_dir() . '/' . $cacheKey . '.json';
        
        if (!file_exists($cacheFile)) {
            return 0;
        }
        
        $data = json_decode(file_get_contents($cacheFile), true);
        if (!$data || !isset($data['expires_at'])) {
            return 0;
        }
        
        $remaining = $data['expires_at'] - time();
        return max(0, $remaining);
    }
    
    /**
     * Reset rate limit for a key
     * 
     * @param string $key Unique identifier
     */
    public static function reset($key) {
        $cacheKey = 'rate_limit_' . md5($key);
        $cacheFile = sys_get_temp_dir() . '/' . $cacheKey . '.json';
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
    }
    
    /**
     * Get current attempt count
     */
    private static function getAttempts($cacheKey) {
        $cacheFile = sys_get_temp_dir() . '/' . $cacheKey . '.json';
        
        if (!file_exists($cacheFile)) {
            return 0;
        }
        
        $data = json_decode(file_get_contents($cacheFile), true);
        if (!$data || !isset($data['expires_at']) || $data['expires_at'] < time()) {
            // Expired, clean up
            @unlink($cacheFile);
            return 0;
        }
        
        return $data['attempts'] ?? 0;
    }
    
    /**
     * Increment attempt count
     */
    private static function incrementAttempts($cacheKey, $windowSeconds) {
        $cacheFile = sys_get_temp_dir() . '/' . $cacheKey . '.json';
        $attempts = self::getAttempts($cacheKey);
        
        $data = [
            'attempts' => $attempts + 1,
            'expires_at' => time() + $windowSeconds
        ];
        
        file_put_contents($cacheFile, json_encode($data), LOCK_EX);
    }
    
    /**
     * Get client identifier: the address of the connection.
     *
     * Headers such as X-Forwarded-For or CF-Connecting-IP are written by whoever sends the request, so trusting them lets anyone
     * make up a fresh address for every attempt and walk straight past every limit. REMOTE_ADDR is the real client address on
     * this host (checked on the live site: a lookup sent with a made-up X-Forwarded-For was logged with the true address).
     * Only if a deployment really sits behind a proxy that rewrites that header can it be named in the environment:
     * TRUSTED_PROXY_HEADER=HTTP_X_FORWARDED_FOR (the proxy must overwrite, not append to, what the client sent).
     */
    public static function getClientIdentifier() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $trusted = (string) getenv('TRUSTED_PROXY_HEADER');
        if ($trusted !== '' && preg_match('/^HTTP_[A-Z0-9_]+$/', $trusted) && !empty($_SERVER[$trusted])) {
            $candidate = trim(explode(',', (string) $_SERVER[$trusted])[0]);
            if (filter_var($candidate, FILTER_VALIDATE_IP)) $ip = $candidate;
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
    }
}
