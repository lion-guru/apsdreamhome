<?php return array (
  'server' => 
  array (
    'host' => '0.0.0.0',
    'port' => 8080,
    'max_connections' => 1000,
  ),
  'rooms' => 
  array (
    0 => 'property_discussion',
    1 => 'team_collaboration',
    2 => 'customer_support',
    3 => 'general_chat',
  ),
  'features' => 
  array (
    'chat' => true,
    'typing_indicators' => true,
    'collaborative_editing' => true,
    'user_presence' => true,
    'file_sharing' => true,
  ),
  'security' => 
  array (
    'authentication_required' => true,
    'rate_limiting' => true,
    'message_validation' => true,
    'room_access_control' => true,
  ),
);