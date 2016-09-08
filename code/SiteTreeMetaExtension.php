<?php
/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class SiteTreeMetaExtension extends DataExtension
{
    /**
     * Database fields
     * @var array
     */
    private static $db = array(
        'MetaTitle' => 'Varchar(255)',
        'OGTitleCustom' => 'Varchar(100)',
        'OGDescriptionCustom' => 'Varchar(150)',
        'NoFollow' => 'Boolean',
        'NoVisit' => 'Boolean'
    );

    /**
     * Has_one relationship
     * @var array
     */
    private static $has_one = array(
        'MetaImageCustom' => 'Image',
        'OGImageCustom' => 'Image',
        'PinterestImageCustom' => 'Image',
        'BreadcrumbIcon' => 'Image'
    );

    /**
     * Twitter username to be attributed as owner/author of this page.
     * Example: 'mytwitterhandle'.
     *
     * @var string
     * @config
     */
    private static $twitter_username = '';

    /**
     * Whether or not to generate a twitter card for this page.
     * More info: https://dev.twitter.com/cards/overview.
     *
     * @var bool
     * @config
     */
    private static $twitter_card = true;

    /**
     * Whether or not to enable a Pinterest preview and fields.
     * You need to be using the $PinterestShareLink for this to be useful.
     *
     * @var bool
     * @config
     */
    private static $pinterest = false;

    /**
     * Update Fields
     * @return FieldList
     */
    public function updateCMSFields(FieldList $fields)
    {
        if ($Metadata = $fields->fieldByName("Root.Main.Metadata")) {
            $fields->removeFieldFromTab('Root.Main', 'Metadata');
            $fields->addFieldToTab(
                'Root.Meta',
                TextField::create(
                    "MetaTitle",
                    _t('SiteTree.METATITLE', 'Meta Title')
                )
            );
            $fields->addFieldsToTab(
                'Root.Meta',
                $Metadata->getChildren()
            );
        }
        $fields->addFieldsToTab(
            'Root.Meta',
            array(
                UploadField::create(
                    'MetaImageCustom',
                    _t('SiteTree.METAIMAGECUSTOM', 'Meta Image')
                )
                    ->setAllowedFileCategories('image')
                    ->setAllowedMaxFileNumber(1)
                CheckboxField::create(
                    'NoIndex',
                    _t('SiteTree.NOINDEX', 'No Index')
                ),
                CheckboxField::create(
                    'NoFollow',
                    _t('SiteTree.NOFOLLOW', 'No Follow')
                ),
                TextField::create(
                    'OGTitleCustom',
                    _t('SiteTree.SHARETITLE', 'Share Title')
                )
                    ->setAttribute('placeholder', $this->owner->getDefaultOGTitle())
                    ->setMaxLength(90),
                TextAreaField::create(
                    'OGDescriptionCustom',
                    _t('SiteTree.SHAREDESCRIPTION', 'Share Description')
                )
                    ->setAttribute('placeholder', $this->owner->getDefaultOGDescription())
                    ->setRows(2),
                UploadField::create(
                    'OGImageCustom',
                    _t('SiteTree.SHAREIMAGE', 'Share Image')
                )
                    ->setAllowedFileCategories('image')
                    ->setAllowedMaxFileNumber(1)
                    ->setDescription('<a href="https://developers.facebook.com/docs/sharing/best-practices#images" target="_blank">Optimum image ratio</a> is 1.91:1. (1200px wide by 630px tall or better)'),
                UploadField::create(
                    'BreadcrumbIcon',
                    _t('SiteTree.BREADCRUMBICON', 'Breadcrumb Icon')
                )
                    ->setAllowedFileCategories('image')
                    ->setAllowedMaxFileNumber(1)
            )
        );

        if (Config::inst()->get('ShareCare', 'pinterest')) {
            $fields->addFieldToTab(
                'Root.Meta',
                UploadField::create(
                    'PinterestImageCustom',
                    _t('SiteTree.PINTERESTIMAGE', "Pinterest image")
                )
                    ->setAllowedFileCategories('image')
                    ->setAllowedMaxFileNumber(1)
                    ->setDescription('Square/portrait or taller images look best on Pinterest. This image should be at least 750px wide.'));
        }

        return $fields;
    }

    /**
     * Ensure public URLs are re-scraped by Facebook after publishing.
     */
    public function onAfterPublish()
    {
        $this->owner->clearFacebookCache();
    }

    /**
     * Ensure public URLs are re-scraped by Facebook after writing.
     */
    public function onAfterWrite()
    {
        if (!$this->owner->hasMethod('doPublish')) {
            $this->owner->clearFacebookCache();
        }
    }

    /**
     * Tell Facebook to re-scrape this URL, if it is accessible to the public.
     *
     * @return RestfulService_Response
     */
    public function clearFacebookCache()
    {
        if (!$this->owner->hasMethod('AbsoluteLink')) {
            return false;
        }
        $anonymousUser = new Member();
        if ($this->owner->can('View', $anonymousUser)) {
            $fetch = new RestfulService('https://graph.facebook.com/');
            $fetch->setQueryString(
                array(
                    'id' => $this->owner->AbsoluteLink(),
                    'scrape' => true,
                )
            );
            return $fetch->request();
        }

    }

    /**
     * Extension hook to change all tags
     */
    public function MetaTags(&$tags)
    {
        $MetaMarkup = array();
        $owner = $this->owner;
        // if($includeTitle === true || $includeTitle == 'true') {
            // $MetaMarkup[] = "<title>" . Convert::raw2xml($this->owner->Title) . "</title>";
        // }

        $generator = trim(Config::inst()->get('SiteTree', 'meta_generator'));
        if (!empty($generator)) {
            $MetaMarkup[] = "<meta name='generator' content='" . Convert::raw2att($generator) . "' />";
        }

        $charset = Config::inst()->get('ContentNegotiator', 'encoding');
        $MetaMarkup[] = "<meta http-equiv='Content-type' content='text/html; charset=$charset' />";
        if($owner->MetaDescription) {
            $MetaMarkup[] = "<meta name='description' content='" . Convert::raw2att($owner->MetaDescription) . "' />";
        }

        if($owner->ExtraMeta) {
            $MetaMarkup[] = $owner->ExtraMeta;
        }

        if (!Director::isLive()) {
            $owner->NoIndex = true;
            $owner->NoFollow = true;
        }

        if ($owner->NoIndex || $owner->NoFollow) {
            $robots = array();
            if ($owner->NoIndex) {
                $robots[] = 'noindex';
            }
            if ($owner->NoFollow) {
                $robots[] = 'nofollow';
            }
            $robots = implode(', ', $robots);
            $MetaMarkup[] = "<meta name='robots' content='$robots'>";
        }

        if(Permission::check('CMS_ACCESS_CMSMain')
        && in_array('CMSPreviewable', class_implements($this))
        && !$this instanceof ErrorPage
        && $this->ID > 0
        ) {
            $MetaMarkup[] = "<meta name='x-page-id' content='{$this->ID}' />";
            $MetaMarkup[] = "<meta name='x-cms-edit-link' content='" . $owner->CMSEditLink() . "' />";
        }

        if (Config::inst()->get('SiteTree', 'twitter_card')) {
            $title = htmlspecialchars($owner->getOGTitle());
            $description = htmlspecialchars($owner->getOGDescription());
            $MetaMarkup[] = "<meta name='twitter:title' content='$title'>";
            $MetaMarkup[] = "<meta name='twitter:description' content='$description'>";

            // If we have a big enough image, include an image tag.
            $image = $owner->getOGImage();
            // $image may be a string - don't generate a specific twitter tag
            // in that case as it is probably the default resource.
            if ($image instanceof Image && $image->getWidth() >= 280) {
                $imageURL = htmlspecialchars(Director::absoluteURL($image->Link()));
                $MetaMarkup[] = "<meta name='twitter:card' content='summary_large_image'>";
                $MetaMarkup[] = "<meta name='twitter:image' content='$imageURL'>";
            }

            $username = Config::inst()->get('ShareCare', 'twitter_username');
            if ($username) {
                $MetaMarkup[] = "<meta name='twitter:site' content='@$username'>";
                $MetaMarkup[] = "<meta name='twitter:creator' content='@$username'>";
            }
        }

        $siteconfig = SiteConfig::current_site_config();
        $sitename = array(
            "@context" => "http://schema.org",
            "@type" => "WebSite",
            "name" => $siteconfig->Title,
            "url" => Director::AbsoluteBaseURL()
        );
        $MetaMarkup[] = '<script type="application/ld+json">'. Convert::array2json($sitename) .'</script>';

        $pages = $owner->getBreadcrumbItems();
        if ($pages->Count() > 1) {
            $breadcrumbs = array(
                "@context" => "http://schema.org",
                "@type" => "BreadcrumbList"
            );
            $position = 1;
            foreach ($pages as $page) {
                $breadcrumbs['itemListElement'][] = array(
                    "@type" => "ListItem",
                    "position" => $position,
                    "item" => array(
                        "@id" => $page->AbsoluteLink(),
                        "name" => $page->Title,
                        "image" => $page->BreadcrumbIcon()->Link()
                    )
                );
                $position++;
            }
            $MetaMarkup[] = '<script type="application/ld+json">'. Convert::array2json($breadcrumbs) .'</script>';
        }

        $tags = implode('', $MetaMarkup);
    }

    /**
     * The default/fallback value to be used in the 'og:title' open graph tag.
     *
     * @return string
     */
    public function getDefaultOGTitle()
    {
        if ($this->owner->MetaTitle) {
            $title = trim($this->owner->MetaTitle);
            if (!empty($title)) {
                return $title;
            }
        }
        return $this->owner->getTitle();
    }

    /**
     * The default/fallback value to be used in the 'og:description' open graph tag.
     *
     * @return string
     */
    public function getDefaultOGDescription()
    {
        // Use MetaDescription if set
        if ($this->owner->MetaDescription) {
            $description = trim($this->owner->MetaDescription);
            if (!empty($description)) {
                return $description;
            }
        }

        // Fall back to Content
        if ($this->owner->Content) {
            $description = trim($this->owner->obj('Content')->Summary(20, 5));
            if (!empty($description)) {
                return $description;
            }
        }

        return false;
    }

    /**
     * The default/fallback Image object or absolute URL to be used in the 'og:image' open graph tag.
     *
     * @return Image|string|false
     */
    public function getDefaultOGImage()
    {
        // We don't want to use the SilverStripe logo, so let's use a favicon if available.
        return (file_exists(BASE_PATH.'/apple-touch-icon.png'))
            ? Director::absoluteURL('apple-touch-icon.png', true)
            : false;
    }
}
