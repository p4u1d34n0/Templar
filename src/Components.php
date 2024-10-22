<?php

namespace Templar;

class Components
{
    protected string $viewPath = './views'; // Base path for views

    /**
     * Render a component file with passed data.
     * 
     * @param string $componentName The name of the component (relative to the view path)
     * @param array $props Associative array of data to pass to the component
     * @param array $slots Array of named slots to pass to the component
     * 
     * @return string Compiled HTML content of the component
     */
    public function render(string $componentName, array $props = [], array $slots = []): string
    {
        // Resolve the component file path
        $componentFile = $this->viewPath . '/' . $componentName . '.php';

        if (!file_exists($componentFile)) {
            throw new \Exception("Component file $componentFile not found.");
        }

        // Extract component props so they are available in the component template
        extract($props);

        // Start output buffering to capture the component's output
        ob_start();

        // Include the component file
        include $componentFile;

        // Get the captured output and clean the buffer
        $content = ob_get_clean();

        // Process any slots inside the component content
        $content = $this->injectSlots($content, $slots);

        return $content;
    }

    /**
     * Inject slot content into the component.
     * 
     * @param string $content The component content
     * @param array $slots Array of named slots to inject
     * 
     * @return string The content with slots injected
     */
    protected function injectSlots(string $content, array $slots): string
    {
        foreach ($slots as $slotName => $slotContent) {
            // Replace the slot directive in the component with the actual slot content
            $content = str_replace("@slot('$slotName')", $slotContent, $content);
        }

        // If there's a default slot, inject it into any `@slot` without a name
        if (isset($slots['default'])) {
            $content = str_replace('@slot', $slots['default'], $content);
        }

        return $content;
    }
}
