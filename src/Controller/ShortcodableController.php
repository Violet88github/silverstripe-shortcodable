<?php

namespace Silverstripe\Shortcodable\Controller;

use BadMethodCallException;
use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use Silverstripe\Shortcodable;
use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\Form;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\View\SSViewer;

/**
 * ShortcodableController.
 *
 * @author shea@livesource.co.nz
 **/
class ShortcodableController extends LeftAndMain
{
    private static $url_segment = '_shortcodable';
    private static $required_permission_codes = 'CMS_ACCESS_LeftAndMain';

    /**
     * @var array
     */
    private static $allowed_actions = array(
        'popup' => 'CMS_ACCESS_LeftAndMain',
        'shortcode' => 'CMS_ACCESS_LeftAndMain',
    );

    /**
     * @var array
     */
    private static $url_handlers = array(
        'edit/$ShortcodeType!/$Action//$ID/$OtherID' => 'handleEdit'
    );

    /**
     * Get the json data for the shortcodable popup
     *
     * @return string|false JSON data
     * @throws BadMethodCallException
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function popup()
    {
        $classes = Shortcodable::get_shortcodable_classes();

        $fields = array(
            'phrases' => [
                'edit_shortcode' => _t('Shortcodable.EDIT_SHORTCODE', 'Edit Shortcode'),
                'new_shortcode' => _t('Shortcodable.NEW_SHORTCODE', 'New Shortcode'),
                'select_shortcode' => _t('Shortcodable.SELECT_SHORTCODE', 'Select a shortcode type'),
                'select_source' => _t('Shortcodable.SELECT_SOURCE', 'Select an entity'),
                'cancel' => _t('Shortcodable.CANCEL', 'Cancel'),
                'insert' => _t('Shortcodable.INSERT', 'Insert'),
            ],
            'shortcodes' => []
        );

        foreach ($classes as $class) {
            $properties = [];
            $properties = $class::config()->get('shortcode_fields') ?: [];

            $fields['shortcodes'][$class] = array(
                'class' => $class,
                'title' => singleton($class)->singular_name(),
                'source' => singleton($class)->hasMethod('getShortcodableRecords') ?
                    singleton($class)->getShortcodableRecords() :
                    $class::get()->map()->toArray(),
                'fields' => $properties,
            );
        }

        $this->extend('updateShortcodeForm', $form);

        return json_encode($fields);
    }

    /**
     * Generate a shortcode based on the request params
     *
     * @param HTTPRequest $request The request on which the shortcode is based
     * @return string The JSON response
     */
    public function shortcode()
    {
        $class = $this->request->getVar('class');
        $validClasses = Shortcodable::get_shortcodable_classes();
        $id = $this->request->getVar('id');

        // Optional improvement: instead of fetching the entire object, just check if the class exists and has the ID
        if (
            $id &&
            is_subclass_of($class, DataObject::class) &&
            in_array($class, $validClasses) &&
            $object = $class::get()->byID($id)
        ) {
            $this->response->addHeader('Content-Type', 'application/json');

            $vars = $this->request->getVars();
            $filteredVars = array_diff_key($vars, array_flip(['class', 'id']));

            return json_encode([
                'shortcode' => self::build_shortcode($class, $id, $filteredVars)
            ]);
        } else {
            $this->httpError(404);
        }
    }

    /**
     * Build a shortcode from a class, id and attributes
     *
     * @param string $class The class name
     * @param int $id The object id
     * @param array $attributes The shortcode attributes
     * @return string The shortcode
     */
    private static function build_shortcode($class, $id, $attributes)
    {
        $shortcode = '[' . $class;
        if ($id)
            $shortcode .= ' id="' . $id . '"';

        if ($attributes)
            foreach ($attributes as $key => $value) {
                $shortcode .= ' ' . $key . '="' . $value . '"';
            }

        $shortcode .= ']';

        return $shortcode;
    }
}
