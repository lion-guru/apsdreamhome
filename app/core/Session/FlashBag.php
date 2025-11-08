<?php

namespace App\Core\Session;

class FlashBag implements \IteratorAggregate, \Countable {
    /**
     * The session store.
     *
     * @var SessionManager
     */
    protected $session;

    /**
     * The session key for the flash messages.
     *
     * @var string
     */
    protected $key = '_flash';

    /**
     * The flash messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * The messages that were just added.
     *
     * @var array
     */
    protected $new = [];

    /**
     * Create a new flash bag instance.
     *
     * @param  SessionManager  $session
     * @param  string  $key
     * @return void
     */
    public function __construct(SessionManager $session, $key = '_flash') {
        $this->key = $key;
        $this->session = $session;
        $this->load();
    }

    /**
     * Load the flash messages from the session.
     *
     * @return void
     */
    protected function load() {
        $this->messages = $this->session->get($this->key, []);
        $this->new = [];
    }

    /**
     * Save the flash messages to the session.
     *
     * @return void
     */
    public function save() {
        $this->session->set($this->key, $this->messages);
    }

    /**
     * Flash a message to the session.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function add($key, $value) {
        $this->messages[$key][] = $value;
        $this->new[$key] = true;
        $this->save();
    }

    /**
     * Get a flash message by key.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null) {
        return $this->messages[$key] ?? $default;
    }

    /**
     * Get all flash messages.
     *
     * @return array
     */
    public function all() {
        return $this->messages;
    }

    /**
     * Get and remove a flash message by key.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function pull($key, $default = null) {
        $value = $this->get($key, $default);
        $this->remove($key);
        return $value;
    }

    /**
     * Remove a flash message by key.
     *
     * @param  string  $key
     * @return void
     */
    public function remove($key) {
        unset($this->messages[$key]);
        unset($this->new[$key]);
        $this->save();
    }

    /**
     * Clear all flash messages.
     *
     * @return void
     */
    public function clear() {
        $this->messages = [];
        $this->new = [];
        $this->save();
    }

    /**
     * Check if a flash message exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key) {
        return isset($this->messages[$key]);
    }

    /**
     * Get the number of flash messages.
     *
     * @return int
     */
    public function count(): int {
        return count($this->messages);
    }

    /**
     * Get an iterator for the flash messages.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): \Traversable {
        return new \ArrayIterator($this->messages);
    }

    /**
     * Get the session store.
     *
     * @return SessionManager
     */
    public function getSession() {
        return $this->session;
    }

    /**
     * Set the session store.
     *
     * @param  SessionManager  $session
     * @return void
     */
    public function setSession(SessionManager $session) {
        $this->session = $session;
    }
}
