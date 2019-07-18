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
        'FBPublisherlink' => 'Varchar(255)',
        'FBAuthorlink' => 'Varchar(255)',
        'GplusAuthorlink' => 'Varchar(255)',
        'GplusPublisherlink' => 'Varchar(255)',
        'NoFollow' => 'Boolean',
        'NoVisit' => 'Boolean',
        'NoSnippet' => 'Boolean',
        'NoCache' => 'Boolean',
        'NoIndex' => 'Boolean'
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

    private static $meta_tab_name = 'Meta';

    private static $title_pattern = "";

    /**
     * Update Fields
     * @return FieldList
     */
    public function updateCMSFields(FieldList $fields)
    {
        $tabName = $this->owner->config()->get('meta_tab_name');
        // copy meta fields from main
        if ($Metadata = $fields->fieldByName('Root.Main.Metadata')) {
            $fields->removeFieldFromTab('Root.Main', 'Metadata');
            $fields->addFieldToTab(
                "Root.$tabName",
                TextField::create(
                    "MetaTitle",
                    _t('SiteTree.METATITLE', 'Meta Title')
                )
            );
            $fields->addFieldsToTab(
                "Root.$tabName",
                $Metadata->getChildren()
            );
            // Remove extra meta because we don't really need it
            $fields->removeByName('ExtraMeta');
            $fields->fieldByName("Root.$tabName.MetaDescription")
                ->setRows(2);
        }
        $fields->addFieldsToTab(
            "Root.$tabName",
            array(
                UploadField::create(
                    'MetaImageCustom',
                    _t('SiteTree.METAIMAGECUSTOM', 'Meta Image')
                )
                    ->setAllowedFileCategories('image')
                    ->setAllowedMaxFileNumber(1),
                HeaderField::create(
                    'RobotsHeader',
                    _t('SiteTree.ROBOTSHEADER', 'Search engine')
                ),
                CheckboxField::create(
                    'NoIndex',
                    _t('SiteTree.NOINDEX', 'Prevent indexing of this page')
                ),
                CheckboxField::create(
                    'NoFollow',
                    _t('SiteTree.NOFOLLOW', 'Prevent following links on this page')
                ),
                CheckboxField::create(
                    'NoSnippet',
                    _t('SiteTree.NOSNIPPET', 'Prevent showing a snippet of this page in the search results')
                ),
                CheckboxField::create(
                    'NoCache',
                    _t('SiteTree.NOCACHE', 'Prevent caching a version of this page')
                ),
                HeaderField::create(
                    'SocialHeader',
                    _t('SiteTree.SOCIALHEADER', 'Social')
                ),
                TextField::create(
                    'OGTitleCustom',
                    _t('SiteTree.SHARETITLE', 'Share title')
                )
                    ->setMaxLength(90),
                TextAreaField::create(
                    'OGDescriptionCustom',
                    _t('SiteTree.SHAREDESCRIPTION', 'Share description')
                )
                    ->setRows(2),
                UploadField::create(
                    'OGImageCustom',
                    _t('SiteTree.SHAREIMAGE', 'Share image')
                )
                    ->setAllowedFileCategories('image')
                    ->setAllowedMaxFileNumber(1)
                    ->setDescription('<a href="https://developers.facebook.com/docs/sharing/best-practices#images" target="_blank">Optimum image ratio</a> is 1.91:1. (1200px wide by 630px tall or better)'),
                // Facebook
                TextField::create(
                    "FBAuthorlink",
                    _t('SiteTree.FBAUTHORLINK', 'Facebook author')
                )
                    ->setRightTitle(_t('SiteTree.FBAUTHORLINKHELP', 'Author Facebook PROFILE URL')),
                TextField::create(
                    "FBPublisherlink",
                    _t('SiteTree.FBPUBLISHERLINK', 'Facebook publisher')
                )
                    ->setRightTitle(_t('SiteTree.FBPUBLISHERLINKHELP', 'Publisher Facebook PAGE URL')),
                // Google plus
                TextField::create(
                    "GplusAuthorlink",
                    _t('SiteTree.GPLUSAUTHORLINK', 'Google+ author')
                )
                    ->setRightTitle(_t('SiteTree.GPLUSAUTHORLINKHELP', 'Author Google+ PROFILE URL')),
                TextField::create(
                    "GplusPublisherlink",
                    _t('SiteTree.GPLUSPUBLISHERLINK', 'Google+ publisher')
                )
                    ->setRightTitle(_t('SiteTree.GPLUSPUBLISHERLINKHELP', 'Publisher Google+ PAGE URL')),
                HeaderField::create(
                    'RichSnippetsHeader',
                    _t('SiteTree.RICHSNIPPETSHEADER', 'Rich snippets')
                ),
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
                "Root.$tabName",
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
        $tagTypes = array(
            'TagTitle',
            'MetaTagCharset',
            'MetaTagGenerator',
            'MetaTagDescription',
            'MetaTagRobots',
            'MetaTagResponsive',
            'MetaTagTwitter',
            'SchemaTagWebsite',
            'SchemaTagBreadcrumbs',
            'SchemaTagContactPoints',
            'SchemaTagLocalBusiness'
        );

        foreach ($tagTypes as $tagType) {
            if ($owner->{$tagType}) {
                $MetaMarkup[] = $owner->{$tagType};
            }
        }

        if(Permission::check('CMS_ACCESS_CMSMain')
        && in_array('CMSPreviewable', class_implements($owner))
        && !$owner instanceof ErrorPage
        && $owner->ID > 0
        ) {
            $MetaMarkup[] = MetaHelper::MetaTag('x-page-id', $owner->ID);
            $MetaMarkup[] = MetaHelper::MetaTag('x-cms-edit-link', $owner->CMSEditLink());
            Requirements::customScript(file_get_contents(BASE_PATH.'/'.BETTERMETA_DIR.'/js/cmseditshortcut.js'));
        }

        $tags = implode("\n", $MetaMarkup);
    }

    public function getMetaTagCharset()
    {
        $charset = Config::inst()->get('ContentNegotiator', 'encoding');
        $tags[] = "<meta charset='$charset'>";
        $tags[] = "<meta http-equiv='Content-type' content='text/html; charset=$charset' />";
        return implode("\n", $tags);
    }

    public function getMetaTagGenerator()
    {
        $generator = trim(Config::inst()->get('SiteTree', 'meta_generator'));
        if (!empty($generator)) {
            return MetaHelper::MetaTag('generator', $generator);
        }
    }

    public function getTagTitle()
    {
        $title = $this->owner->config()->get('title_pattern');
        if ($this->owner->title_pattern) {
            $title = $this->owner->title_pattern;
        }

        if ($title) {
            return "<title>".$title."</title>";
        }
    }

    public function getMetaTagDescription()
    {
        $owner = $this->owner;
        if($owner->MetaDescription) {
            return MetaHelper::MetaTag('description', $owner->MetaDescription);
        } else if ($owner->Content) {
            $contentSnippet = Convert::raw2att(strip_tags($owner->Content));
            $maxDescription = substr($contentSnippet, 0, 160);
            $matches = array();
            $regex = preg_match('(.*[\.\?!])', $maxDescription, $matches);
            if(isset($matches[0])) {
                $metaDescription = $matches[0];
            } else {
                $metaDescription = $maxDescription;
            }
            return MetaHelper::MetaTag('description', $metaDescription);
        }
    }

    public function getMetaTagRobots()
    {
        $owner = $this->owner;
        // Force settings on test and dev enviroment
        if (!Director::isLive()) {
            $owner->NoIndex = true;
            $owner->NoFollow = true;
            $owner->NoSnippet = true;
            $owner->NoCache = true;
        }

        // Build robots meta tag
        $robots = array();
        $robots[] = ($owner->NoIndex) ? 'noindex' : 'index';
        $robots[] = ($owner->NoFollow) ? 'nofollow' : 'follow';
        if ($owner->NoSnippet) {
            $robots[] = 'nosnippet';
        }
        if ($owner->NoCache) {
            $robots[] = 'noarchive, nocache';
        }
        $robots = implode(', ', $robots);
        return MetaHelper::MetaTag('robots', $robots);
    }

    public function getMetaTagResponsive()
    {
        return MetaHelper::MetaTag('viewport', 'width=device-width, initial-scale=1.0');
    }

    public function getMetaTagTwitter($value='')
    {
        if (Config::inst()->get('SiteTree', 'twitter_card')) {
            $owner = $this->owner;
            $title = htmlspecialchars($owner->getOGTitle());
            $description = htmlspecialchars($owner->getOGDescription());
            $tags = array();
            $tags[] = MetaHelper::MetaTag('twitter:title', $title);
            $tags[] = MetaHelper::MetaTag('twitter:description', $description);

            // If we have a big enough image, include an image tag.
            $image = $owner->getOGImage();
            // $image may be a string - don't generate a specific twitter tag
            // in that case as it is probably the default resource.
            if ($image instanceof Image && $image->getWidth() >= 280) {
                $imageURL = htmlspecialchars(Director::absoluteURL($image->Link()));
                $tags[] = MetaHelper::MetaTag('twitter:card', 'summary_large_image');
                $tags[] = MetaHelper::MetaTag('twitter:image', $imageURL);
            }

            $username = Config::inst()->get('ShareCare', 'twitter_username');
            if ($username) {
                $tags[] = MetaHelper::MetaTag('twitter:site', "@$username");
                $tags[] = MetaHelper::MetaTag('twitter:creator', "@$username");
            }
            return implode("\n", $tags);
        }
    }

    public function getSchemaTagWebsite()
    {
        $siteconfig = SiteConfig::current_site_config();
        $sitename = array(
            "@context" => "http://schema.org",
            "@type" => "WebSite",
            "name" => $siteconfig->Title,
            "url" => Director::AbsoluteBaseURL()
        );
        return MetaHelper::SchemaTag($sitename);
    }

    public function getSchemaTagBreadcrumbs()
    {
        $pages = $this->owner->getBreadcrumbItems();
        if ($pages->Count() > 1) {
            $breadcrumbsSchema = array(
                "@context" => "http://schema.org",
                "@type" => "BreadcrumbList"
            );
            $position = 1;
            foreach ($pages as $page) {
                $breadcrumbsSchema['itemListElement'][] = array(
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
            return MetaHelper::SchemaTag($breadcrumbsSchema);
        }
        return false;
    }

    public function getSchemaTagContactPoints($value='')
    {
        $siteconfig = SiteConfig::current_site_config();
        if ($contactPoints = $siteconfig->ContactPoints()) {
            $contactPointsSchema = array(
                "@context" => "http://schema.org",
                "@type" => "Organization",
                "url" => Director::absoluteBaseURL()
            );
            foreach ($contactPoints as $contactPoint) {
                $contactPointsSchema['contactPoint'][] = $contactPoint->buildSchemaArray();
            }
            return MetaHelper::SchemaTag($contactPointsSchema);
        }
    }

    public function getSchemaTagLocalBusiness()
    {
        $siteconfig = SiteConfig::current_site_config();
        if ($localBusiness = $siteconfig->LocalBusiness()) {
            $localBusinessSchema = array(
                "@context" => "http://schema.org",
                "@type" => "Organization"
            );
            foreach ($localBusiness as $localBusinessItem) {
                $localBusinessSchema['department'][] = $localBusinessItem->buildSchemaArray();
            }
            return MetaHelper::SchemaTag($localBusinessSchema);
        }
    }

    public function getDefaultMetaTitle()
    {
        if ($this->owner->getTitle()) {
            return $this->owner->getTitle();
        }
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
        $owner = $this->owner;
        if ($owner->OGImageCustomID) {
            return $owner->OGImageCustom();
        }

        if ($owner->MetaImageCustomID) {
            return $owner->MetaImageCustom();
        }
        
        // Check siteconfig for one
        $config = SiteConfig::current_site_config();
        if ($config->MetaImageCustomID) {
            return $config->MetaImageCustom();
        }

        return false;
    }

    public function setTagTitle($title)
    {
        $this->owner->title_pattern = $title;
    }
}
