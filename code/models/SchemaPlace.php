<?php
/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class SchemaPlace extends SchemaThing
{
    /**
     * Singular name for CMS
     * @var string
     */
    private static $singular_name = 'Place';

    /**
     * Plural name for CMS
     * @var string
     */
    private static $plural_name = 'Places';

    /**
     * Defines the current schema type
     * @var string
     */
    private static $schema_type = 'Place';

    /**
     * Database fields
     * @var array
     */
    private static $db = array(
        'Address' => 'Text',
        'BranchCode' => 'Text'
    );

    /**
     * Many_many relationship
     * @var array
     */
    private static $many_many = array(
        'OpeningHoursSpecification' => 'SchemaOpeningHours'
    );

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('OpeningHoursSpecification');
        $fields->addFieldsToTab(
            'Root.Main',
            array(
                TextField::create(
                    'Address',
                    _t('SchemaPlace.PAYMENTACCEPTED', 'Address')
                ),
                TextField::create(
                    'BranchCode',
                    _t('SchemaPlace.PRICERANGE', 'Branch code')
                ),
                GridField::create(
                    'OpeningHoursSpecification',
                    _t('SchemaPlace.OPENINGHOURSSPECIFICATION', 'Opening hours specification'),
                    $this->OpeningHoursSpecification(),
                    GridFieldConfig_RelationEditor::create()
                )
            )
        );
        return $fields;
    }

    /**
     * Builds array ready to be converted into json schema
     * @return array
     */
    public function buildSchemaArray()
    {
        $array = parent::buildSchemaArray();
        if ($this->Address) {
            $array['address'] = "$this->Address";
        }
        if ($this->BranchCode) {
            $array['branchCode'] = "$this->BranchCode";
        }
        if ($openingHours = $siteconfig->OpeningHoursSpecification()) {
            $openingHoursSchema = array(
                "@context" => "http://schema.org",
                "@type" => "Organization"
            );
            foreach ($openingHours as $openingHoursItem) {
                $openingHoursSchema['openingHoursSpecification'][] = $openingHoursItem->buildSchemaArray();
            }
        }
        return $array;
    }
}
