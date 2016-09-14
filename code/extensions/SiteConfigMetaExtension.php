<?php
/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class SiteConfigMetaExtension extends DataExtension
{
    /**
     * Has_many relationship
     * @var array
     */
    private static $has_many = array(
        'ContactPoints' => 'ContactPoint'
    );

    /**
     * Update Fields
     * @return FieldList
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab(
            'Root.Meta',
            array(
                GridField::create(
                    'ContactPoints',
                    _t('SiteConfig.CONTACTPOINTS', 'Contact Points'),
                    $this->owner->ContactPoints(),
                    GridFieldConfig_RecordEditor::create()
                )
            )
        );
        return $fields;
    }
}
