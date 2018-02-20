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
     * Many_many relationship
     * @var array
     */
    private static $many_many = array(
        'ContactPoints' => 'SchemaContactPoint',
        'LocalBusiness' => 'SchemaPlaceLocalBusiness'
    );
    
    private static $has_one = array(
        'MetaImageCustom' => 'Image'
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
                UploadField::create('MetaImageCustom', 'Default Meta Image'),
                GridField::create(
                    'ContactPoints',
                    _t('SiteConfig.CONTACTPOINTS', 'Contact points'),
                    $this->owner->ContactPoints(),
                    GridFieldConfig_RecordEditor::create()
                ),
                GridField::create(
                    'LocalBusiness',
                    _t('SiteConfig.LOCALBUSINESS', 'Local business'),
                    $this->owner->LocalBusiness(),
                    GridFieldConfig_RecordEditor::create()
                )
            )
        );
        return $fields;
    }
}
