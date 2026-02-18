<?php

namespace App\Core\Session;

use SessionHandlerInterface;
use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;

class SessionManager implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * The session data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Whether the session has been started.
     * @var bool
     */
    protected $started = false;

    /**
     * The session handler.
     *
     * @var SessionHandlerInterface
     */
    protected $handler;

    /**
     * The flash bag instance.
     *
     * @var FlashBag
     */
    protected $flashBag;

    /**
     * Get the flash bag instance.
     *
     * @return FlashBag
     */
    public function getFlashBag()
    {
        $this->start();
        if (!isset($this->data['_flash']) || !($this->data['_flash'] instanceof FlashBag)) {
            $this->data['_flash'] = new FlashBag();
        }
        $this->flashBag = $this->data['_flash'];
        return $this->flashBag;
    }

    /**
     * Set the session handler.
     *
     * @param  SessionHandlerInterface  $handler
     * @return void
     */
    public function setHandler(SessionHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Session manager constructor.
     *
     * @param SessionHandlerInterface|null $handler
     */
    public function __construct(?SessionHandlerInterface $handler = null)
    {
        $this->handler = $handler ?? new NativeSessionHandler;
        $this->started = false;
    }

    /**
     * Start the session.
     *
     * @return bool
     */
    public function start()
    {
        if ($this->started) {
            return true;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->data = &$_SESSION;
            return true;
        }

        session_start();
        $this->data = &$_SESSION;
        $this->started = true;

        return true;
    }

    /**
     * Get a session value.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $this->start();
        return $this->data[$key] ?? $default;
    }

    /**
     * Set a session value.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->start();
        $this->data[$key] = $value;
    }

    /**
     * Remove a session value.
     *
     * @param  string  $key
     * @return void
     */
    public function remove($key)
    {
        $this->start();
        unset($this->data[$key]);
    }

    /**
     * Check if a session value exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        $this->start();
        return isset($this->data[$key]);
    }

    /**
     * Get all session data.
     *
     * @return array
     */
    public function all()
    {
        $this->start();
        return $this->data;
    }

    /**
     * Clear all session data.
     *
     * @return void
     */
    public function clear()
    {
        $this->start();
        $this->data = [];
    }

    /**
     * Regenerate the session ID.
     *
     * @param  bool  $destroy
     * @return bool
     */
    public function regenerate($destroy = false)
    {
        $this->start();
        return session_regenerate_id($destroy);
    }

    /**
     * Destroy the session.
     *
     * @return bool
     */
    public function destroy()
    {
        $this->start();
        $this->clear();
        $this->started = false;
        return session_destroy();
    }

    /**
     * Get the session ID.
     *
     * @return string
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * Set the session ID.
     *
     * @param  string  $id
     * @return void
     */
    public function setId($id)
    {
        session_id($id);
    }

    /**
     * Get the session name.
     *
     * @return string
     */
    public function getName()
    {
        return session_name();
    }

    /**
     * Set the session name.
     *
     * @param  string  $name
     * @return void
     */
    public function setName($name)
    {
        session_name($name);
    }

    // ArrayAccess implementation
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    // Countable implementation
    public function count(): int
    {
        return count($this->all());
    }

    // IteratorAggregate implementation
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * Get the flash bag (for compatibility with Symfony-style flash messages)
     *
     * @return FlashBag
     */
    public function getFlashBag()
    {
        if (!isset($this->data['_flash'])) {
            $this->data['_flash'] = new FlashBag();
        }
        return $this->data['_flash'];
    }
}

/**
 * Flash message bag for temporary session messages.
 */
class FlashBag
{
    private $messages = [];

    /**
     * Add a flash message.
     *
     * @param string $type
     * @param string $message
     * @return void
     */
    public function add($type, $message)
    {
        if (!isset($this->messages[$type])) {
            $this->messages[$type] = [];
        }
        $this->messages[$type][] = $message;
    }

    /**
     * Get all flash messages.
     *
     * @return array
     */
    public function all()
    {
        return $this->messages;
    }

    /**
     * Get messages by type.
     *
     * @param string $type
     * @return array
     */
    public function get($type)
    {
        return $this->messages[$type] ?? [];
    }

    /**
     * Clear all flash messages.
     *
     * @return void
     */
    public function clear()
    {
        $this->messages = [];
    }
}

/**
 * Native PHP session handler.
 */
class NativeSessionHandler implements SessionHandlerInterface
{
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($sessionId): string|false
    {
        return '';
    }

    public function write($sessionId, $data): bool
    {
        return true;
    }

    public function destroy($sessionId): bool
    {
        return true;
    }

    public function gc($maxLifetime): int|false
    {
        return (int) ini_get('session.gc_maxlifetime');
    }
}
