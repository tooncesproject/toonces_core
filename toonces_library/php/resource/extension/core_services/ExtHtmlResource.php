<?php
/**
 * @author paulanderson
 * ExtHtmlResource.php
 * Initial commit: 5/5/2018
 * 
 * DataResource class (and subclass of PageDataResource) for managing HTML-content pages.
 * 
 */

require_once LIBPATH.'php/toonces.php';

class ExtHtmlResource extends PageDataResource implements iResource {
    
    function buildFields() {
        /**
         * @override PageDataResource->buildFields to create fields specific to 
         * ExtHtmlResource 
         */
        // Call PageDataResource-buildFields
        parent::buildFields();
        // Make some fields optional
        $this->fields['pageBuilderClass']->allowNull = true;
        $this->fields['pageViewClass']->allowNull = true;
        
        // Add a field for the HTML body
        $htmlBodyField = new HtmlFieldValidator();
        $this->fields['htmlBody'] = $htmlBodyField;
        
        // Add a field for the Client class
        $clientClassField = new StringFieldValidator();
        $clientClassField->allowNull = true;
        $this->fields['clientClass'] = $clientClassField;
        
    }
    
    
    function postAction() {
        /**
         * @override PageDataResource->postAction
         */
        
        $client = null;
        $conn = $this->pageViewReference->getSQLConn();
                
        // Acquire the POST body (if not already set)
        if (count($this->resourceData) == 0)
            $this->resourceData = json_decode(file_get_contents("php://input"), true);
        // Set up default values.
        if (!isset($this->resourceData['pageBuilderClass']))
            $this->resourceData['pageBuilderClass'] = 'ExtHTMLPageBuilder';

        if (!isset($this->resourceData['pageViewClass']))
            $this->resourceData['pageViewClass'] = 'HTMLPageView';
        
        if (!isset($this->resourceData['pageTypeId']))
            $this->resourceData['pageTypeId'] = 5;
        
        if (!isset($this->resourceData['clientClass']))
            $this->resourceData['clientClass'] = 'FileResourceClient';

        // Go through validation and POST actions.
        do {
            // Attempt to instantiate the client
            $clientClass = $this->resourceData['clientClass'];
            try {
                $client = new $clientClass($this->pageViewReference);
            } catch (Exception $e) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'Failed to instantiate the ResourceClient object: '. $e->getMessage();
                break;
            }
            
            // Acquire HTML body prior to page creation
            $htmlBody = $this->resourceData['htmlBody'];

            // Attempt to create the page
            $postResult = parent::postAction();
        
            // If successful, load the HTML to the store and create a record in
            // ext_html_pages
            $clientResponse = null;
            $clientStatus = null;
            $fileUrl = '';
            $pageId = null;
            if ($this->httpStatus == Enumeration::getOrdinal('HTTP_201_CREATED', 'EnumHTTPResponse')) {
            
                $pageId = key($postResult);
                $date = $postResult[$pageId]['createdDate'];
                $fileNameDate = preg_replace('[ ]', '_', $date);
                $fileNameDate = preg_replace('[:]','',$fileNameDate);
                
                // Get the resource URL from toonces_config.xml
                $xml = new DOMDocument();
                $xml->load(ROOTPATH.'toonces-config.xml');
                $pathNode = $xml->getElementsByTagName('html_resource_url')->item(0);
                $urlPath = $pathNode->nodeValue;
                
                // Generate a file URL
                $fileUrl = $urlPath . strval($pageId) . '_' . $fileNameDate . '.htm';
                
                // Create the file
                $clientResponse = $client->put($fileUrl, $htmlBody);
                $clientStatus = $client->getResourceStatus();                
            }
            
            // If file creation was unsuccessful, roll back, break and error.
            if ($clientStatus != Enumeration::getOrdinal('HTTP_201_CREATED', 'EnumHTTPResponse')) {
                $this->parameters['id'] = strval($pageId);
                parent::deleteAction();
                $this->httpStatus = $clientStatus;
                $this->resourceData = $clientResponse;
                break;
            }


            // Insert a record into ext_html_page
            $sql = <<<SQL
                INSERT INTO ext_html_page
                    (page_id, html_path, client_class)
                VALUES
                    (:pageId, :htmlPath, :clientClass)
SQL;
            $stmt = $conn->prepare($sql);
            $sqlParams = array(
                 'pageId' => $pageId
                ,'htmlPath' => $fileUrl
                ,'clientClass' => $this->resourceData['clientClass']
            );
            try {
                $stmt->execute($sqlParams);
            } catch (PDOException $e) {
                // If unsuccessful, delete the page record.
                $this->parameters['id'] = strval($pageId);
                parent::deleteAction();
                $this->httpStatus = Enumeration::getOrdinal('HTTP_500_INTERNAL_SERVER_ERROR', 'EnumHTTPResponse');
                $this->statusMessage = 'PDO error occured when inserting into ext_html_page: ' . $e->getMessage();
                break;
            }
          
            // Success? 
            
        } while (false);
        
        
        
    }
    
    public function deleteAction() {
        // User must use the 'pages' endpoint to delete a page.
        $this->httpStatus = Enumeration::getOrdinal('HTTP_405_METHOD_NOT_ALLOWED', 'EnumHTTPResponse');
    }
}