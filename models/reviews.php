<?php
class Reviews extends BlestaReviewsModel
{
  
    public function __construct()
    {
        parent::__construct();

        Language::loadLang('blesta_cms', null, PLUGINDIR . 'blesta_cms' . DS . 'language' . DS);

        Loader::loadComponents($this, ['Session', 'Record']);

        Loader::loadModels($this, ['Staff', 'Companies']);
    }

       /**
     * Add a Review.
     *
     * @param  array $vars An array containing the post data to add
     * @return bool  True if the post hass been added
     */
    public function addReview(array $vars)
    {
        // Generate add date
        $vars['date_added'] = date('Y-m-d H:i:s');

        // Get company id
        $company_id = Configure::get('Blesta.company_id');

        // Add the post in the database
        if (!empty($vars['uri']) && !empty($vars['name']) && !empty($vars['content'])) {
            $this->Record->insert('blesta_reviews', [
                'uri'         => $vars['uri'],
                'company_id'  => $company_id,
                'author'      => $this->Session->read('blesta_staff_id'),
                'name'       => serialize($vars['name']),
				'title'       => serialize($vars['title']),
				'company'     => serialize($vars['company']),
                'content'     => serialize($vars['content']),
                'date_added'  => $vars['date_added']
            ]);

            return true;
        }

        return false;
    }

    /**
     * Edit a Review.
     *
     * @param  int   $id   The post id
     * @param  array $vars An array containing the post data to update
     * @return bool  True if the post has been edited
     */
    public function editReview($id, array $vars)
    {
        // Generate add date
        $vars['date_updated'] = date('Y-m-d H:i:s');

        // Edit the post in the database
        if (!empty($vars['title']) && !empty($vars['content'])) {
            $this->Record->where('id', '=', $id)->update('blesta_reviews', [
				'uri'         => $vars['uri'],
                'name'       => serialize($vars['name']),
				'title'       => serialize($vars['title']),
				'company'     => serialize($vars['company']),
                'content'     => serialize($vars['content']),
                'date_updated' => $vars['date_updated']
            ]);

            return true;
        }

        return false;
    }

    /**
     * Get a Review by ID.
     *
     * @param  int      $id The post id
     * @return atdClass An object containing all the post fields
     */
    public function getReview($id)
    {
        // Get company id
        $company_id = Configure::get('Blesta.company_id');

        // Get post
        $records = $this->Record->select()->from('blesta_reviews')->where('id', '=', $id);

        // Unserialize the data
        $result            = $records->fetch();
        $result->author    = $this->Staff->get(empty($result->author) ? 1 : $result->author, $company_id);
        $result->title     = unserialize($result->title);
        $result->content   = unserialize($result->content);
        $result->meta_tags = unserialize($result->meta_tags);

        return $result;
    }

    /**
     * Get a Review by URI.
     *
     * @param  string $uri         The post URI
     * @param  bool   $show_public True to show only if is public post
     * @return array  An array containing all the post fields
     */
    public function getReviewUri($uri, $show_public = false)
    {
        // Get company id
        $company_id = Configure::get('Blesta.company_id');

        // Get the post
        $records = $this->Record->select()->from('blesta_reviews')->
            where('uri', '=', $uri)->
            where('company_id', '=', $company_id);

        // Show only public
        //if ($show_public) {
        //    $records->where('access', '=', 'public');
        //}

        // Unserialize the data
        $result            = $records->fetch();
        $result->author    = $this->Staff->get(empty($result->author) ? 1 : $result->author, $company_id);
        $result->title     = unserialize($result->title);
        $result->content   = unserialize($result->content);
        $result->meta_tags = unserialize($result->meta_tags);

        return empty($result->content) ? false : $result;
    }

    /**
     * Get all the Review of the company.
     *
     * @param  bool  $show_public True to show only the public posts
     * @return array An array containing all the posts
     */
    public function getAllReviews($show_public = false)
    {
        // Get company id
        $company_id = Configure::get('Blesta.company_id');

        // Get all posts from the database
        $records = $this->Record->select()->from('blesta_reviews')->
            where('company_id', '=', $company_id);

        // Show only public
        if ($show_public) {
            $records->where('access', '=', 'public');
        }

        // Unserialize results
        $posts = $records->fetchAll();
        foreach ($posts as $key => $post) {
            $post->author    = $this->Staff->get(empty($post->author) ? 1 : $post->author, $company_id);
            $post->title     = unserialize($post->title);
            $post->content   = unserialize($post->content);
            $post->meta_tags = unserialize($post->meta_tags);

            $posts[$key] = $post;
        }

        return $posts;
    }

    /**
     * Get the latest Review of the company.
     *
     * @param  int   $posts       The quantity of posts to get
     * @param  bool  $show_public True to show only the public posts
     * @return array An array containing all the posts
     */
    public function getLatestReviews($posts = 5, $show_public = true)
    {
        // Get company id
        $company_id = Configure::get('Blesta.company_id');

        // Get all posts from the database
        $records = $this->Record->select()->from('blesta_reviews')
            ->where('company_id', '=', $company_id)
            ->limit($posts)
            ->order(['id' => 'desc']);

        // Show only public
        //if ($show_public) {
        //    $records->where('access', '=', 'public');
        //}

        // Unserialize results
        $posts = $records->fetchAll();
        foreach ($posts as $key => $post) {
            $post->author    = $this->Staff->get(empty($post->author) ? 1 : $post->author, $company_id);
            $post->title     = unserialize($post->title);
            $post->content   = unserialize($post->content);
            $post->meta_tags = unserialize($post->meta_tags);

            $posts[$key] = $post;
        }

        return $posts;
    }

    /**
     * Delete a Review.
     *
     * @param  int  $id The post id
     * @return bool True if the post has been deleted
     */
    public function deleteReview($id)
    {
        // Delete all the comments
        //$comments = $this->getPostComments($id);

        //foreach ($comments as $comment) {
         //   $this->deleteComment($comment->id);
        //}

        // Delete the post
        $this->Record->from('blesta_reviews')->where('id', '=', $id)->delete();

        return true;
    }

}