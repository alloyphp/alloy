<?php
namespace Module\{$generator.namespace};

use App;
use Alloy, Alloy\Request;

/**
 * {$generator.name} Module
 */
class Controller extends App\Module\ControllerAbstract
{
    const ENTITY = 'Module\{$generator.namespace}\Entity';

    /**
     * Public listing/index
     * @method GET
     */
    public function indexAction(Request $request)
    {
        $kernel = \Kernel();
        $items = $kernel->mapper()->all(self::ENTITY);
        $fields = $kernel->mapper()->entityManager()->fields(self::ENTITY);

        // Return 404 if no items
        if(!$items) {
            return false;
        }
        
        // HTML template
        if('html' == $request->format) {
            return $this->template(__FUNCTION__)
                ->set(compact('items', 'fields'));
        }
        // Resource object (JSON/XML, etc)
        return $kernel->resource($items);
    }
    

    /**
     * View single item
     * @method GET
     */
    public function viewAction(Request $request)
    {
        $kernel = \Kernel();
        $mapper = $kernel->mapper();

        $item = $mapper->get(self::ENTITY, (int) $request->item);
        if(!$item) {
            return false;
        }

        if('html' == $request->format) {
            return $this->template(__FUNCTION__)
                ->set(compact('item'));   
        } else {
            return $kernel->resource($item);
        }
    }


    /**
     * Create new item form
     * @method GET
     */
    public function newAction(Request $request)
    {
        $kernel = \Kernel();
        $item = $kernel->mapper()->get(self::ENTITY);

        return $this->template(__FUNCTION__)
            ->set(array(
                'item' => $item,
                'form' => $this->formView($item)->data($request->query())
            ));
    }


    /**
     * Helper to save item
     */
    public function saveItem(Entity $item)
    {
        $kernel = \Kernel();
        $mapper = $kernel->mapper();
        $request = $kernel->request();

        // Attempt save
        if($mapper->save($item)) {
            $itemUrl = $kernel->url(array('module' => '{$generator.name_url}', 'item' => $item->id), 'module_item');

            // HTML
            if('html' == $request->format) {
                return $kernel->redirect($itemUrl);
            // Others (XML, JSON)
            } else {
                return $kernel->resource($item->data())
                    ->created($itemUrl);
            }
        // Handle errors
        } else {
            // HTML
            if('html' == $request->format) {
                // Re-display form
                $res = $kernel->spotForm($item);
            // Others (XML, JSON)
            } else {
                $res = $kernel->resource();
            }

            // Set HTTP status and errors
            return $res->status(400)
                ->errors($item->errors());
        }
    }


    /**
     * New item creation
     * @method POST
     */
    public function postMethod(Request $request)
    {
        $kernel = \Kernel();
        $mapper = $kernel->mapper();

        $item = $mapper->get(self::ENTITY);
        $item->data($request->post());

        // Common save functionality
        return $this->saveItem($item);
    }


    /**
     * Edit form for item
     * @method GET
     */
    public function editAction(Request $request)
    {
        $kernel = \Kernel();
        $mapper = $kernel->mapper();

        $item = $mapper->get(self::ENTITY, (int) $request->item);
        if(!$item) {
            return false;
        }

        return $this->template(__FUNCTION__)
            ->set(array(
                'item' => $item,
                'form' => $this->formView($item)
            ));
    }


    /**
     * Edit existing item
     * @method PUT
     */
    public function putMethod(Request $request)
    {
        $kernel = \Kernel();
        $mapper = $kernel->mapper();

        $item = $mapper->get(self::ENTITY, (int) $request->item);
        if(!$item) {
            return false;
        }

        // Set all POST data that can be set
        $item->data($request->post());
        
        // Ensure 'id' cannot be modified
        $item->id = (int) $request->item;

        // Update 'last modified' date
        $item->date_modified = new \DateTime();

        // Common save functionality
        return $this->saveItem($item);
    }


    /**
     * Delete confirmation
     * @method GET
     */
    public function deleteAction(Request $request)
    {
        $kernel = \Kernel();
        $mapper = $kernel->mapper();

        $item = $mapper->get(self::ENTITY, (int) $request->item);
        if(!$item) {
            return false;
        }

        return $this->template(__FUNCTION__)
            ->set(compact('item'));
    }
    

    /**
     * Delete post
     * @method DELETE
     */
    public function deleteMethod(Request $request)
    {
        $kernel = \Kernel();
        $mapper = $kernel->mapper();

        $item = $mapper->get(self::ENTITY, (int) $request->item);
        if(!$item) {
            return false;
        }

        $mapper->delete($item);

        return $kernel->redirect($kernel->url(array('module' => '{$generator.name_url}'), 'module'));
    }


    /**
     * Entity form with Alloy form generic
     */
    protected function formView($entity = null)
    {
        return \Kernel()->spotForm($entity);
    }


    /**
     * Automatic install/migrate method
     */
    public function install()
    {
        $mapper = \Kernel()->mapper();
        $mapper->migrate(self::ENTITY);
    }
}