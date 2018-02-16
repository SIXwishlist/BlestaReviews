<?php
class BlestaReviewsPlugin extends Plugin {
 
    public function __construct() {

		$this->loadConfig(dirname(__FILE__) . DS . "config.json");

		// Load components required by this plugin
        Loader::loadComponents($this, ['Input', 'Record']);
		
    }
	
	public function install($plugin_id) {

	if (!isset($this->Record)) {
            Loader::loadComponents($this, ['Record']);
        }

        try {
			$this->Record->
			setField("id", array('type' => "int",'size' => 10,'unsigned' => true,'auto_increment' => true))->
			setField("company_id", array('type' => "int",'size' => 10))->
			setField("uri", array('type' => "varchar", 'size' => 255))->
     setField("name", array('type' => "varchar", 'size' => 255))->
	 setField("title", array('type' => "varchar", 'size' => 255))->
	 setField("company", array('type' => "varchar", 'size' => 255))->
	 setField("content", array('type' => "varchar", 'size' => 1000))->
     setField("date_added", array('type' => "datetime", 'is_null' => true, 'default' => null))->
	 setField("date_updated", array('type' => "datetime", 'is_null' => true, 'default' => null))->
     setKey(array("id"), "primary")->
	 setKey(array("uri"), "unique")->
     create("blesta_reviews");	
} catch (Exception $e) {
            // Error adding... no permission?
            $this->Input->setErrors(['db'=> ['create'=>$e->getMessage()]]);
            return;
        }
		
    }

    public function upgrade($current_version, $plugin_id) {
        
		  // Upgrade if possible
        if (version_compare($this->getVersion(), $current_version, '>')) {
            // Handle the upgrade, set errors using $this->Input->setErrors() if any errors encountered
        }
		
    }

    public function uninstall($plugin_id, $last_instance) {       
	 // Remove the tables created by this plugin
        if ($last_instance) {
            try {
$this->Record->drop("blesta_reviews");
 } catch (Exception $e) {
                // Error dropping... no permission?
                $this->Input->setErrors(['db'=> ['create'=>$e->getMessage()]]);
                return;
            }
			
    }

    public function getActions() {
        return [
            [
                'action' => 'nav_primary_client',
                'uri' => 'plugin/blesta_reviews/main/',
                'name' => Language::_('blesta_reviews.main', true)
            ]
        ];
    }
}
?>