<?php

namespace Templar;

class Templar
{
    protected $config = [];
    protected $compiler;
    protected $components;

    public function __construct(array $config = [])
    {
        // Merge the passed configuration with default values
        $this->config = array_merge($this->defaultConfig(), $config);

        // Initialize Compiler with directives
        $this->compiler = new Compiler($this->config['directives']);

        // Initialize Components
        $this->components = new Components($this->config['components_path']);

        // Automatically register directives
        Directives::register();
    }

    /**
     * Set or update the configuration dynamically.
     *
     * @param array $config Custom configuration.
     */
    public function setConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Get the default configuration.
     *
     * @return array Default configuration settings.
     */
    protected function defaultConfig(): array
    {
        return [
            'view_path' => './views', // Default view folder
            'cache_path' => './cache', // Cache for compiled templates
            'components_path' => './views/components', // Components path
            'directives' => [], // Custom directives
            'components' => [] // Custom components
        ];
    }

    /**
     * Register a custom directive dynamically.
     *
     * @param string $name The directive name.
     * @param callable $handler The handler for the directive transformation.
     */
    public function registerDirective(string $name, callable $handler)
    {
        $this->compiler->addDirective($name, $handler);
    }

    /**
     * Render a view template with the given data.
     *
     * @param string $view The template name (without extension).
     * @param array $data Data to be passed to the template.
     * @return string The rendered view content.
     */
    public function render(string $view, array $data = []): string
    {
        $viewPath = $this->config['view_path'] . '/' . $view . '.php';

        // Compile the view with the given data
        return $this->compiler->compile($viewPath, $data);
    }

    /**
     * Render a component with given properties and slots.
     *
     * @param string $component The component name.
     * @param array $props Properties for the component.
     * @param array $slots Slots content passed to the component.
     * @return string Rendered component content.
     */
    public function renderComponent(string $component, array $props = [], array $slots = []): string
    {
        // Render the component using the Components class
        return $this->components->render($component, $props, $slots);
    }

    /**
     * Boot method to dynamically add directives and components.
     *
     * @param callable $callback Callback to register custom directives/components.
     */
    public function boot(callable $callback)
    {
        // Call the callback and pass $this to allow modifications
        $callback($this);
    }
}
