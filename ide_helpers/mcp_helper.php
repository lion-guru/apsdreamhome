<?php
/**
 * MCP helper for IDE enhancement
 */

class McpHelper {
    private $servers = [];
    
    public function __construct() {
        $this->loadServers();
    }
    
    private function loadServers() {
        $this->servers = [
            'git' => [
                'command' => 'npx',
                'args' => ['-y', '@modelcontextprotocol/server-git']
            ],
            'filesystem' => [
                'command' => 'npx',
                'args' => ['-y', '@modelcontextprotocol/server-filesystem']
            ],
            'mysql' => [
                'command' => 'npx',
                'args' => ['-y', '@modelcontextprotocol/server-mysql']
            ],
            'puppeteer' => [
                'command' => 'npx',
                'args' => ['-y', '@modelcontextprotocol/server-puppeteer']
            ]
        ];
    }
    
    public function getServer($name) {
        return $this->servers[$name] ?? null;
    }
    
    public function getAllServers() {
        return $this->servers;
    }
    
    public function addServer($name, $config) {
        $this->servers[$name] = $config;
    }
    
    public function executeCommand($serverName, $context = []) {
        $server = $this->getServer($serverName);
        if (!$server) {
            return false;
        }
        
        $command = $server['command'];
        $args = $server['args'] ?? [];
        
        // Execute command with context
        $fullCommand = $command . ' ' . implode(' ', $args);
        
        return [
            'command' => $fullCommand,
            'context' => $context,
            'status' => 'ready'
        ];
    }
}
