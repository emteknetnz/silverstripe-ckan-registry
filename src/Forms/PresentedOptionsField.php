<?php

namespace SilverStripe\CKANRegistry\Forms;

use SilverStripe\CKANRegistry\Model\Resource;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\TextField;

/**
 * A PresentedOptionsField renders either a list of options that can be chosen for a {@link DropdownFilter}, or
 * a text area allowing free text entry per line.
 *
 * The values of these options are serialised and saved as JSON.
 */
class PresentedOptionsField extends TextField
{
    /**
     * @var int
     */
    public const SELECT_TYPE_ALL = 0;

    /**
     * @var int
     */
    public const SELECT_TYPE_CUSTOM = 1;

    /**
     * @var int
     */
    public const SELECT_TYPE_DEFAULT = self::SELECT_TYPE_ALL;

    protected $schemaComponent = 'PresentedOptions';

    /**
     * The resource that this options field will suggest options from
     *
     * @var Resource
     */
    protected $resource;

    public function __construct(
        $name,
        Resource $resource,
        $title = null,
        $value = '',
        $maxLength = null,
        Form $form = null
    ) {
        $this->setResource($resource);

        parent::__construct($name, $title, $value, $maxLength, $form);

        $this->addExtraClass('ckan-presented-options__container');
    }

    public function Type()
    {
        return 'ckan-presented-options';
    }

    public function getSchemaDataDefaults()
    {
        $data = parent::getSchemaDataDefaults();
        $data['data']['selectTypeDefault'] = self::SELECT_TYPE_DEFAULT;
        $data['data']['selectTypes'] = self::getSelectTypes();
        $data['data']['endpoint'] = $this->getResource()->Endpoint;
        $data['data']['resource'] = $this->getResource()->Identifier;
        $data['data']['fieldMap'] = $this->getResource()->Fields()->map('ID', 'OriginalLabel')->toArray();
        return $data;
    }

    /**
     * Get a list of options for filtering with a human readable (translated) label
     *
     * @return array[]
     */
    public static function getSelectTypes()
    {
        return [
            ['value' => self::SELECT_TYPE_ALL, 'title' => _t(__CLASS__ . '.SELECT_ALL', 'Select from all options')],
            ['value' => self::SELECT_TYPE_CUSTOM, 'title' => _t(__CLASS__ . '.SELECT_CUSTOM', 'Manually add options')],
        ];
    }

    public function performReadonlyTransformation()
    {
        return clone $this->setReadonly(true);
    }

    /**
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param Resource $resouce
     * @return $this
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }
}
