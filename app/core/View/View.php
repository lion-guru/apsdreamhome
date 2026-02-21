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
        return App::getInstance()->basePath('app/views');
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

        try {
            $path = $this->findView($view);
            file_put_contents(dirname(__DIR__, 3) . '/debug_view.log', "View::render view: $view -> path: $path\n", FILE_APPEND);
        } catch (\Exception $e) {
            file_put_contents(dirname(__DIR__, 3) . '/debug_view.log', "View::render error finding view $view: " . $e->getMessage() . "\n", FILE_APPEND);
            throw $e;
        }

        // Update instance data so it persists for layout rendering
        $this->data = array_merge($this->data, $data);
        $data = $this->data;

        try {
            $path = $this->findView($view);
        } catch (\Exception $e) {
            throw $e;
        }

        // Extract the data to be available in the view
        extract(array_merge(static::$shared, $data));

        // Start output buffering
        ob_start();

        // Include the view file
        try {
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

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

            // Normalize path separator
            $path = str_replace(['\\', '//'], '/', $path);

            if (file_exists($path)) {
                return $path;
            }

            // Also check in app/views directory for admin views
            $appViewsPath = App::getInstance()->basePath('app/views') . '/' . $view . '.' . $extension;
            $appViewsPath = str_replace(['\\', '//'], '/', $appViewsPath);

            if (file_exists($appViewsPath)) {
                return $appViewsPath;
            }
        }

        throw new InvalidArgumentException("View [{$view}] not found in paths: {$this->basePath}");
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
     * Stop injecting content into a section
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function endSection()
    {
        if (empty($this->sectionStack)) {
            throw new InvalidArgumentException('Cannot end a section without first starting one.');
        }

        $last = array_pop($this->sectionStack);
        $content = ob_get_clean();

        $this->extendSection($last, $content);
        $this->currentSection = end($this->sectionStack);

        return $last;
    }

    /**
     * Extend a section with content
     *
     * @param string $name
     * @param string $content
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
     * Get the content of a section
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public function yieldSection($name, $default = '')
    {
        return $this->sections[$name] ?? $default;
    }

    /**
     * Share data with all views
     *
     * @param string|array $key
     * @param mixed $value
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
     */
    public static function composer($views, $callback)
    {
        $views = (array) $views;

        foreach ($views as $view) {
            static::$composers[$view][] = $callback;
        }
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
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }
}
