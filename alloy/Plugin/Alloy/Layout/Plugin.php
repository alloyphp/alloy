<?php
namespace Plugin\Alloy\Layout;
use Alloy, RuntimeException;

/**
 * Layout Plugin
 * Wraps layout template around content result from main dispatch loop
 */
class Plugin
{
    protected $kernel;


    /**
     * Initialize plguin
     */
    public function __construct(Alloy\Kernel $kernel)
    {
        // Add 'wrapLayout' method as callback for 'dispatch_content' filter
        $kernel->events()->addFilter('dispatch_content', 'alloy_layout_wrap', array($this, 'wrapLayout'));
    }


    /**
     * Wrap layout around returned content from primary dispatch
     *
     * @return mixed $content Raw content string, Alloy\Module\Response object, or generic object that implements __toString
     */
    public function wrapLayout($content)
    {
        $kernel   = \Kernel();
        $request  = $kernel->request();
        $response = $kernel->response();

        $response->contentType('text/html');

        $layoutName = null;
        if($content instanceof Alloy\View\Template) {
            $layoutName = $content->layout();
        }
        // Use config template if none other specified and request is not Ajax or CLI
        if(null === $layoutName && !$request->isAjax() && !$request->isCli()) {
            $layoutName = $kernel->config('app.layout.template', 'app');
        }

        if($layoutName && true === $kernel->config('app.layout.enabled', false)) {
            $layout = new \Alloy\View\Template($layoutName, $request->format);
            $layout->path($kernel->config('app.path.layouts'))
                ->format($request->format);

            // Ensure layout exists
            if (false === $layout->exists()) {
                return $content;
            }

            // Pass along set response status and data if we can
            if($content instanceof Alloy\Module\Response) {
                $layout->status($content->status());
                $layout->errors($content->errors());
            }

            // Pass set title up to layout to override at template level
            if($content instanceof Alloy\View\Template) {
                // Force render layout so we can pull out variables set in template
                $contentRendered = $content->content();
                $layout->head()->title($content->head()->title());
                $content = $contentRendered;
            }

            $layout->set(array(
                'kernel'  => $kernel,
                'content' => $content
            ));

            return $layout;
        }

        return $content;
    }
}