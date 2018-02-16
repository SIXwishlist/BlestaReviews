<?php

class AdminReviews extends BlestaReviewsController
{
    /**
     * Prepare the controller.
     */
    public function preAction()
    {
        // Parent pre-action
        parent::preAction();

        // Require login
        $this->requireLogin();

        // Load models
        $this->uses(['PluginManager', 'BlestaReviews.Reviews']);

        // Restore structure view location of the admin portal
        $this->structure->setDefaultView(APPDIR);
        $this->structure->setView(null, $this->original_view);
        $this->structure->set('page_title', Language::_('blesta_reviews.reviews', true));
    }

    /**
     * List current pages.
     */
    public function index()
    {
        // Fetch pages
        $reviews = $this->Reviews->getAllReviews();

        // Set variables to the view
        $this->set('reviews', $reviews);
    }

    /**
     * Add a new review.
     */
    public function add()
    {
        // Add review
        if (!empty($this->post)) {
            $result = $this->Reviews->addReview($this->post);

            // Parse result
            if ($result) {
                $this->flashMessage('message', Language::_('blesta_reviews.success', true), null, false);
                $this->redirect($this->base_uri . 'plugin/blesta_reviews/admin_reviews/');
            } else {
                $this->setMessage('error', Language::_('blesta_reviews.!error.empty', true), false, null, false);
            }
        }

        $tags = ['{base_url}', '{blesta_url}', '{admin_url}', '{client_url}', '{plugins}'];

        // Get all installed languages
        $langs = $this->Reviews->getAllLang();

        // Set variables to the view
        $this->set('vars', (object) $this->post);
        $this->set('tags', $tags);
        $this->set('langs', $langs);
    }

    /**
     * Edit a existing review.
     */
    public function edit()
    {
        // Redirect if an ID has not been given
        if (empty($this->get[0])) {
            $this->redirect($this->base_uri . 'plugin/blesta_reviews/admin_reviews/');
        }

        // Edit page
        if (!empty($this->post)) {
            $vars   = $this->post;
            $result = $this->Reviews->editReview($this->get[0], $this->post);

            // Parse result
            if ($result) {
                $this->flashMessage('message', Language::_('blesta_reviews.success', true), null, false);
                $this->redirect($this->base_uri . 'plugin/blesta_reviews/admin_reviews/');
            } else {
                $this->setMessage('error', Language::_('blesta_reviews.!error.empty', true), false, null, false);
            }
        } else {
            $vars = $this->Reviews->getReview($this->get[0]);
        }

        $tags = ['{base_url}', '{blesta_url}', '{admin_url}', '{client_url}', '{plugins}'];

        // Get all installed languages
        $langs = $this->Reviews->getAllLang();

        // Set variables to the view
        $this->set('vars', (object) $vars);
        $this->set('tags', $tags);
        $this->set('langs', $langs);
    }

    /**
     * Delete a reviews.
     */
    public function delete()
    {
        // Delete Reviews
        if (!empty($this->get[0])) {
            $result = $this->Reviews->deleteReview($this->get[0]);

            // Parse result
            if ($result) {
                $this->flashMessage('message', Language::_('blesta_reviews.success', true), null, false);
            } else {
                $this->flashMessage('error', Language::_('blesta_reviews.!error.empty', true), null, false);
            }

            // Redirect
            $this->redirect($this->base_uri . 'plugin/blesta_reviews/admin_reviews/');
        }
    }
}
