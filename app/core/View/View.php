<?php

namespace App\Core\View;

use App\Core\App;
use App\Core\Http\Response;
use InvalidArgumentException;

class View
{
    /**
     * The base path for views
     *
     * @var string
     */
    protected $basePath;

    /**
     * The data available to the view
     *
     * @var array
     */
    protected $data = [];

    /**
     * The name of the view to render
     *
     * @var string
     */
    protected $view;

    /**
     * The layout to use
     *
     * @var string
     */
    protected $layout;

    /**
     * The sections of the view
     *
     * @var array
     */
    protected $sections = [];

    /**
     * The current section being captured
     *
     * @var string
     */
    protected $currentSection;

    /**
     * The stack of sections being rendered
     *
     * @var array
     */
    protected $sectionStack = [];

    /**
     * The view composers
     *
     * @var array
     */
    protected static $composers = [];

    /**
     * The shared data for all views
     *
     * @var array
     */
    protected static $shared = [];

    /**
     * The view extensions
     *
     * @var array
     */
    protected static $extensions = ['php'];

    /**
     * Create a new View instance
     *
     * @param string $view
     * @param array $data
     * @param string $basePath
     */
    public function __construct($view = null, $data = [], $basePath = null)
    {
        $this->view = $view;
        $this->data = $data;
        $this->basePath = $basePath ?: $this->getDefaultBasePath();
    }

    /**
     * Get the default base path for views
     *
     * @return string
     */
    protected function getDefaultBasePath()
    {
        return App::getInstance()->basePath('resources/views');
    }

    /**
     * Create a new view instance
     *
     * @param string $view
     * @param array $data
     * @return static
     */
    public static function make($view, $data = [])
    {
        return new static($view, $data);
    }

    /**
     * Set the base path for views
     *
     * @param string $path
     * @return $this
     */
    public function setBasePath($path)
    {
        $this->basePath = $path;
        return $this;
    }

    /**
     * Get the evaluated view contents
     *
     * @param string $view
     * @param array $data
     * @return string
     */
    public function render($view = null, $data = [])
    {
        $view = $view ?: $this->view;

        // Update instance data so it persists for layout rendering
        $this->data = array_merge($this->data, $data);
        $data = $this->data;

        $path = $this->findView($view);

        // Extract the data to be available in the view
        extract(array_merge(static::$shared, $data));

        // Start output buffering
        ob_start();

        // Include the view file
        include $path;

        $content = ob_get_clean();

        // If a layout is set, render the content within the layout
        if ($this->layout) {
            $content = $this->renderLayout($content);
        }

        return $content;
    }

    /**
     * Render a view and return it as a response
     *
     * @param string $view
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public static function renderResponse($view, $data = [], $status = 200, $headers = [])
    {
        $content = static::make($view, $data)->render();
        return new Response($content, $status, $headers);
    }

    /**
     * Render a view to a string
     *
     * @param string $view
     * @param array $data
     * @return string
     */
    public static function renderString($view, $data = [])
    {
        return static::make($view, $data)->render();
    }

    /**
     * Find the fully qualified path to the view
     *
     * @param string $view
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function findView($view)
    {
        $view = str_replace('.', '/', $view);

        foreach (static::$extensions as $extension) {
            // First check in the default base path (resources/views)
            $path = $this->basePath . '/' . $view . '.' . $extension;

            if (file_exists($path)) {
                return $path;
            }

            // Also check in app/views directory for admin views
            $appViewsPath = App::getInstance()->basePath('app/views') . '/' . $view . '.' . $extension;

            if (file_exists($appViewsPath)) {
                return $appViewsPath;
            }
        }

        throw new InvalidArgumentException("View [{$view}] not found.");
    }

    /**
     * Render the layout with the given content
     *
     * @param string $content
     * @return string
     */
    protected function renderLayout($content)
    {
        $layout = $this->layout;
        $this->layout = null;

        return $this->render($layout, ['content' => $content]);
    }

    /**
     * Set the layout to use
     *
     * @param string $layout
     * @return $this
     */
    public function layout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Start a section
     *
     * @param string $name
     * @param string $content
     * @return void
     */
    public function section($name, $content = null)
    {
        if ($content === null) {
            if (ob_start()) {
                $this->sectionStack[] = $name;
                $this->currentSection = $name;
            }
        } else {
            $this->extendSection($name, $content);
        }
    }

    /**
     * Append to a section
     *
     * @param string $name
     * @param string $content
     * @return void
     */
    protected function extendSection($name, $content)
    {
        if (isset($this->sections[$name])) {
            $this->sections[$name] = str_replace('@parent', $content, $this->sections[$name]);
        } else {
            $this->sections[$name] = $content;
        }
    }

    /**
     * End a section
     *
     * @return string
     */
    public function endSection()
    {
        if (empty($this->sectionStack)) {
            throw new \RuntimeException('Cannot end a section without first starting one.');
        }

        $name = array_pop($this->sectionStack);
        $this->sections[$name] = ob_get_clean();
        $this->currentSection = end($this->sectionStack) ?: null;

        return $name;
    }


    /**
     * Get the content of a section
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public function yieldContent($name, $default = '')
    {
        return $this->sections[$name] ?? $default;
    }

    /**
     * Alias for yieldContent to support legacy view calls
     */
    public function yield($name, $default = '')
    {
        return $this->yieldContent($name, $default);
    }

    /**
     * Check if a section exists
     *
     * @param string $name
     * @return bool
     */
    public function hasSection($name)
    {
        return isset($this->sections[$name]);
    }

    /**
     * Get the content for a section
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getSection($name, $default = '')
    {
        return $this->sections[$name] ?? $default;
    }

    /**
     * Include a sub-view
     *
     * @param string $view
     * @param array $data
     * @return void
     */
    public function include($view, $data = [])
    {
        echo $this->make($view, array_merge($this->data, $data))->render();
    }

    /**
     * Share data across all views
     *
     * @param string|array $key
     * @param mixed $value
     * @return void
     */
    public static function share($key, $value = null)
    {
        if (is_array($key)) {
            static::$shared = array_merge(static::$shared, $key);
        } else {
            static::$shared[$key] = $value;
        }
    }

    /**
     * Add a view composer
     *
     * @param string|array $views
     * @param \Closure|string $callback
     * @return void
     */
    public static function composer($views, $callback)
    {
        foreach ((array) $views as $view) {
            static::$composers[$view][] = $callback;
        }
    }

    /**
     * Get the shared data
     *
     * @return array
     */
    public static function getShared()
    {
        return static::$shared;
    }

    /**
     * Check if a view exists
     *
     * @param string $view
     * @return bool
     */
    public function exists($view)
    {
        try {
            $this->findView($view);
            return true;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Add a view extension
     *
     * @param string $extension
     * @return void
     */
    public static function addExtension($extension)
    {
        if (!in_array($extension, static::$extensions)) {
            static::$extensions[] = $extension;
        }
    }

    /**
     * Get the view extensions
     *
     * @return array
     */
    public static function getExtensions()
    {
        return static::$extensions;
    }

    /**
     * Convert the view to a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Get a piece of data from the view
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Set a piece of data on the view
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Check if a piece of data is bound to the view
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Remove a piece of data from the view
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }
}
