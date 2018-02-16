<?php

class Main extends BlestaReviewsController
{
    public function preAction()
    {
        // Execute parent constructor
        parent::preAction();

        // Load models
        $this->uses(['PluginManager', 'BlestaReviews.Reviews', 'Settings']);

        // Load components
        Loader::loadComponents($this, ['Session']);

        // Redirect if the plugin is not installed
        if (!$this->PluginManager->isInstalled('blesta_reviews', $this->company_id)) {
            $this->redirect($this->client_uri);
        }

        // Set base uri
        $this->base_uri            = WEBDIR;
        $this->view->base_uri      = $this->base_uri;
        $this->structure->base_uri = $this->base_uri;

        // Structure
        $this->structure->setDefaultView(APPDIR);
        $this->structure->setView(null, 'client' . DS . $this->layout);

        if ($this->Session->read('blesta_client_id')) {
            // Get and set client information to the view and structure
            $this->client = $this->Clients->get($this->Session->read('blesta_client_id'));
            $this->view->set('client', $this->client);
            $this->structure->set('client', $this->client);

            // Set menu to the view and structure
            //$this->structure->set('menus_items', $this->CmsPages->getMenuItemsWithChilds());
            //$this->view->set('menus_items', $this->CmsPages->getMenuItemsWithChilds());
            //$this->set('menus_items', $this->CmsPages->getMenuItemsWithChilds());
        }
    }

    /**
     * Call the correct function type.
     */
    public function index()
    {
        // If is language, Set and redirect, otherwise dispatch page
        if (isset($this->get[0]) && $this->Reviews->langExists($this->get[0])) {
            // Set current language
            $this->Reviews->setCurrentLang($this->get[0]);

            // Rebuild array
            unset($this->get[0]);
            $this->get = array_values($this->get);
            $lang      = $this->Reviews->getCurrentLang();
        } elseif (defined('CURRENTLANGUAGE') && $this->Reviews->langExists(CURRENTLANGUAGE)) {
            // Set current language
            $this->Reviews->setCurrentLang(CURRENTLANGUAGE);
            $lang = $this->Reviews->getCurrentLang();
        } else {
            // Set default language
            $lang = $this->Reviews->getAllLang()[0]->uri;
            $this->Reviews->setCurrentLang($lang);

            // Redirect to the default page if the language not exists
            if (isset($this->get[0]) && strlen($this->get[0]) == 2) {
                $this->redirect($this->base_uri . $this->get[1]);
            }
        }

        // Start Caching
        if ($this->Settings->getSetting('BlestaReviews.Caching')->value) {
            // Trick uri string to avoid collisions over companies and languages
            $this->uri_str = $this->uri_str . DS . $this->company_id . DS . $lang;

            // Cache the request
            $this->startCaching(Configure::get('Blesta.cache_length'));

            // Print the cached version if exists
            $cached = Cache::fetchCache($this->uri_str);
            if ($cached) {
                echo $cached;
                exit;
            }
        }

        // Load Content
        $content = isset($this->get[0]) ? $this->get[0] : null;
        switch ($content) {
            case 'blog':
                // Load blog
                $this->blog();

                return $this->view->setView('main_blog', 'default');
                break;
            case 'category':
                // Load category
                $this->category();

                return $this->view->setView('main_category', 'default');
                break;
            default:
                // Load Review
                $this->review();

                return $this->view->setView('main', 'default');
                break;
        }

        // Stop Caching
        if ($this->Settings->getSetting('BlestaReviews.Caching')) {
            $this->stopCaching();
        }
    }

    /**
     * Show a review.
     */
    public function review()
    {
        // Get uri
        $uri = isset($this->get[0]) ? $this->get[0] : '/';

        // Load models
        $this->uses(['BlestaReviews.Reviews', 'PluginManager']);

        // Get current language
        $lang = $this->Reviews->getCurrentLang();

        // Initialize h2o parser
        Loader::load(VENDORDIR . 'h2o' . DS . 'h2o.php');
        $parser_options_html               = Configure::get('Blesta.parser_options');
        $parser_options_html['autoescape'] = false;

        // Get page
        $page = $this->Reviews->getReviewUri($uri, true);

        // Redirect to 404 error if page not exists or if is only for guests
        if (!$page || ($page->permissions == 'guests' && !empty($this->Session->read('blesta_client_id')) && $uri !== '/')) {
            $this->redirect($this->base_uri . '404');
        }

        // Show warning for private content for logged users
        if ($page->permissions == 'logged' && empty($this->Session->read('blesta_client_id'))) {
            $this->setMessage('error', [['result' => Language::_('blesta_reviews.!error.logged_in', true)]], false, null, false);

            return null;
        }

        // Tags
        $plugins           = $this->PluginManager->getAll($this->company_id);
        $installed_plugins = [];

        foreach ($plugins as $plugin) {
            $installed_plugins[$plugin->dir] = $plugin;
        }

        $url  = rtrim($this->base_url, '/');
        $tags = [
            'base_url'   => $this->Html->safe($url),
            'blesta_url' => $this->Html->safe($url . WEBDIR),
            'client_url' => $this->Html->safe($url . $this->client_uri),
            'admin_url'  => $this->Html->safe($url . $this->admin_uri),
            'plugins'    => $installed_plugins
        ];

        // Parse content tags
        $page->content = H2o::parseString($page->content[$lang], $parser_options_html)->render($tags);

        // Redirect to the default language if the translation is empty
        if (empty($page->content) && $lang != $this->Reviews->getAllLang()[0]->uri) {
            $this->redirect($this->base_uri . $uri);
        }

        // Set variables
        $this->set('lang', $lang);
        $this->set('page', $page);
        $this->set('title', $page->title[$lang]);
        $this->set('content', $page->content);
        $this->set('meta_tags', $page->meta_tags[$lang]);
        $this->set('description', $page->description[$lang]);

        // Set structure variables
        $this->structure->set('lang', $lang);
        $this->structure->set('page', $page);
        $this->structure->set('meta_tags', $page->meta_tags[$lang]);
        $this->structure->set('page_title', $page->title[$lang]);
        $this->structure->set('description', $page->description[$lang]);
    }
}
