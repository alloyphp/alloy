<?php
namespace Plugin\Alloy\Layout;
use Alloy;

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
        $this->kernel = $kernel;

        // Add 'wrapLayout' method as callback for 'dispatch_content' filter
        $kernel->events()->addFilter('dispatch_content', 'layout_wrap', array($this, 'wrapLayout'));
    }


    /**
     * Wrap new layout view around result content
     */
    public function wrapLayout($content)
    {
        $kernel = $this->kernel;
        $request = $kernel->request();
        $response = $kernel->response();

        // Wrap returned content in a layout
        if($request->format == 'html' && !$request->isAjax() && !$request->isCli()) {
            if(true === $kernel->config('layout.enabled', false)) {
                $layout = new \Alloy\View\Template($kernel->config('layout.template', 'app'));

                // Pass set title up to layout to override at template level
                if($content instanceof Alloy\View\Template) {
                    // Force render layout so we can pull out variables set in template
                    $contentRendered = $content->content();
                    $layout->head()->title($content->head()->title());
                    $content = $contentRendered;
                }

                $layout->path($kernel->config('path.layouts'))
                    ->format($request->format)
                    ->set(array(
                        'kernel' => $kernel,
                        'content' => $content
                        ));

                $content = $layout;
            }
            $response->contentType('text/html');
            
        } elseif(in_array($request->format, array('json', 'xml'))) {
            // No cache and hide potential errors
            ini_set('display_errors', 0);
            $response->header("Expires", "Mon, 26 Jul 1997 05:00:00 GMT"); 
            $response->header("Last-Modified", gmdate( "D, d M Y H:i:s" ) . "GMT"); 
            $response->header("Cache-Control", "no-cache, must-revalidate"); 
            $response->header("Pragma", "no-cache");
            
            // Correct content-type
            if('json' == $request->format) {
                $response->contentType('application/json');
            } elseif('xml' == $request->format) {
                $response->contentType('text/xml');
            }
        }

        // Pass along set response status and data if we can
        if($content instanceof Alloy\Module\ResponseAbstract) {
            $response->status($content->status());
        }

        return $content;
    }
}