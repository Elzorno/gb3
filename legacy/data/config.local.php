<?php
declare(strict_types=1);

/**
 * GB2 local runtime config (branding and local overrides; not committed).
 */

return array (
  'data_dir' => '/var/www-rewrite/legacy/data',
  'admin_password_hash' => '$2y$10$NFLIWy0Qf4TwWUm8oG6RTuGO4Qibffvz5vp7h0qHynm9bGjo.WvRy',
  'session' => 
  array (
    'cookie_name' => 'gb2_sess',
    'kid_device_cookie' => 'gb2_dev',
    'csrf_key' => 'gb2_csrf',
    'kid_session_days' => 30,
    'admin_session_minutes' => 20,
    'pin_min_len' => 6,
    'pin_max_len' => 6,
  ),
  'rotation' => 
  array (
    'kids_order' => 
    array (
      0 => 'Megan',
      1 => 'Stacey',
      2 => 'Barry',
      3 => 'Brady',
      4 => 'Parker',
    ),
    'slot_titles' => 
    array (
      0 => 'Dishes',
      1 => 'Trash + Bathrooms',
      2 => 'Help Cook',
      3 => 'Common Areas',
      4 => 'Help Everybody',
    ),
  ),
  'uploads' => 
  array (
    'max_bytes' => 7340032,
  ),
  'branding' => 
  array (
    'brand' => 'GB2',
    'family' => 'Zornes Family',
  ),
);
