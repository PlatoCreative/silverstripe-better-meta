<?php
/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class ContactPoint extends DataObject
{
    /**
     * Database fields
     * @var array
     */
    private static $db = array(
        'Title' => 'Varchar(100)', // contactType
        'Type' => 'Varchar(100)', // @type
        'Telephone' => 'Varchar(100)',
        'Email' => 'Varchar(100)',
        'FaxNumber' => 'Varchar(100)',
        'ContactOption' => 'Text', // HearingImpairedSupported, TollFree, etc
        'AreaServed' => 'Text', // NZ, US, etc
        'AvailableLanguage' => 'Text',
        'HoursAvailable' => 'Text'
    );

    /**
    * Has_one relationship
    * @var array
    */
   private static $has_one = array(
       'SiteConfig' => 'SiteConfig',
   );

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('SiteConfigID');
        $fields->addFieldsToTab(
            'Root.Main',
            array(
                TextField::create(
                    'Title',
                    _t('MetaContact.CONTACTTYPE', 'Contact Type')
                ),
                TextField::create(
                    'Telephone',
                    _t('MetaContact.TELEPHONE', 'Telephone')
                ),
                TextField::create(
                    'Email',
                    _t('MetaContact.EMAIL', 'Email')
                ),
                TextField::create(
                    'FaxNumber',
                    _t('MetaContact.FAXNUMBER', 'Fax number')
                ),
                TextField::create(
                    'Telephone',
                    _t('MetaContact.TELEPHONE', 'Telephone')
                ),
                StringTagField::create(
                    'ContactOption',
                    _t('MetaContact.CONTACTOPTION', 'Contact Option'),
                    array('HearingImpairedSupported', 'TollFree'),
                    explode(',', $this->ContactOption)
                ),
                StringTagField::create(
                    'AreaServed',
                    _t('MetaContact.AREASERVED', 'Area Served'),
                    array(
                        'AF','AX','AL','DZ','AS','AD','AO','AI','AQ','AG','AR','AM','AW','AU','AT','AZ','BS','BH','BD','BB','BY','BE','BZ','BJ','BM',
                        'BT','BO','BQ','BA','BW','BV','BR','IO','VG','BN','BG','BF','BI','KH','CM','CA','CV','KY','CF','TD','CL','CN','CX','CC','CO',
                        'KM','CK','CR','HR','CU','CW','CY','CZ','CD','DK','DJ','DM','DO','TL','EC','EG','SV','GQ','ER','EE','ET','FK','FO','FJ','FI',
                        'FR','GF','PF','TF','GA','GM','GE','DE','GH','GI','GR','GL','GD','GP','GU','GT','GG','GN','GW','GY','HT','HM','HN','HK','HU',
                        'IS','IN','ID','IR','IQ','IE','IM','IL','IT','CI','JM','JP','JE','JO','KZ','KE','KI','XK','KW','KG','LA','LV','LB','LS','LR',
                        'LY','LI','LT','LU','MO','MK','MG','MW','MY','MV','ML','MT','MH','MQ','MR','MU','YT','MX','FM','MD','MC','MN','ME','MS','MA',
                        'MZ','MM','NA','NR','NP','NL','NC','NZ','NI','NE','NG','NU','NF','KP','MP','NO','OM','PK','PW','PS','PA','PG','PY','PE','PH',
                        'PN','PL','PT','PR','QA','CG','RE','RO','RU','RW','BL','SH','KN','LC','MF','PM','VC','WS','SM','ST','SA','SN','RS','SC','SL',
                        'SG','SX','SK','SI','SB','SO','ZA','GS','KR','SS','ES','LK','SD','SR','SJ','SZ','SE','CH','SY','TW','TJ','TZ','TH','TG','TK',
                        'TO','TT','TN','TR','TM','TC','TV','VI','UG','UA','AE','GB','US','UM','UY','UZ','VU','VA','VE','VN','WF','EH','YE','ZM','ZW'
                    ),
                    explode(',', $this->AreaServed)
                ),
                TextareaField::create(
                    'HoursAvailable',
                    _t('MetaContact.HOURSAVAILABLE', 'Hours Available')
                )
                    ->setDescription(
                        _t(
                            'MetaContact.HOURSAVAILABLEDESCIPTION',
                            'The general opening hours for a business. Opening hours can be specified as a weekly time range, starting with days, then times per day. Multiple days can be listed with commas \',\' separating each day. Day or time ranges are specified using a hyphen \'-\'.
                            Days are specified using the following two-letter combinations: Mo, Tu, We, Th, Fr, Sa, Su.
                            Times are specified using 24:00 time. For example, 3pm is specified as 15:00.
                            Here is an example: <time itemprop="openingHours" datetime="Tu,Th 16:00-20:00">Tuesdays and Thursdays 4-8pm</time>.
                            If a business is open 7 days a week, then it can be specified as <time itemprop="openingHours" datetime="Mo-Su">Monday through Sunday, all day</time>.'
                        )
                    )
            )
        );
        return $fields;
    }

    /**
     * Creating Permissions
     * @return boolean
     */
    public function canCreate($member = null)
    {
        return Permission::check('SITETREE_EDIT_ALL', 'any', $member);;
    }

    /**
     * Editing Permissions
     * @return boolean
     */
    public function canEdit($member = null)
    {
        return Permission::check('SITETREE_EDIT_ALL', 'any', $member);;
    }

    /**
     * Deleting Permissions
     * @return boolean
     */
    public function canDelete($member = null)
    {
        return Permission::check('SITETREE_EDIT_ALL', 'any', $member);;
    }

    /**
     * Viewing Permissions
     * @return boolean
     */
    public function canView($member = null)
    {
        return Permission::check('SITETREE_VIEW_ALL', 'any', $member);
    }
}
