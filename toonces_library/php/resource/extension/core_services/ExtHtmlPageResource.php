<?php
/**
 * @author paulanderson
 * ExtHtmlPageResource.php
 * Initial commit: 5/5/2018
 * 
 * DataResource class (and subclass of PageDataResource) for managing HTML-content pages.
 * 
 */

require_once LIBPATH.'php/toonces.php';

class ExtHtmlPageResource extends PageDataResource implements iResource {
    
    var $client;
    var $urlPath;
    
    function setupClient($pageId = null) {        
        $conn = $this->pageViewReference->getSQLConn();

        if (isset($this->resourceData['clientClass']))
            $clientClass = $this->resourceData['clientClass'];
        
        // Only instantiate the client if it hasn't been set externally
        // (Unit tests will set a "dummy" client)
        if (!isset($this->client)) {
            // Class set in parameters?
            if (!$clientClass && $pageId) {
                // If not set in parameters, query the database for the client class
                $sql = "SELECT client_class FROM ext_html_page WHERE page_id = :pageId";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['pageId' => $pageId]);
                $result = $stmt->fetchAll();
                $clientClass = $result[0]['client_class'];
            }
            
            // Attempt to instantiate the client
            try {
                $this->client = new $clientClass($this->pageViewReference);
            } catch (Exception $e) {
                $this->httpStatus = Enumeration::getOrdinal('HTTP_400_BAD_REQUEST', 'EnumHTTPResponse');
                $this->statusMessage = 'Failed to instantiate the ResourceClient object: '. $e->getMessage();
                return 1;                
            }
        }
        return 0;
    }
    
    
    function buildFields() {
        /**
         * @override PageDataResource->buildFields to create fields specific to 
         * ExtHtmlPageResource 
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
        
        $conn = $this->pageViewReference->getSQLConn();
        
        // Build fields.
        $this->buildFields();
                
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
            $clientStatus = $this->setupClient(null);
            // Break if error.
            if ($clientStatus == 1)
                break;
            
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
                
                // Get the resource URL from toonces_config.xml,
                // if not already set.
                if (!isset($this->urlPath)) {
                    $xml = new DOMDocument();
                    $xml->load(ROOTPATH.'toonces-config.xml');
                    $pathNode = $xml->getElementsByTagName('html_resource_url')->item(0);
                    $this->urlPath = $pathNode->nodeValue;
                }
                
                // Generate a file URL
                $fileUrl = $this->urlPath . strval($pageId) . '_' . $fileNameDate . '.htm';
                
                // Create the file
                $email = $_SERVER['PHP_AUTH_USER'];
                $pw = $_SERVER['PHP_AUTH_PW'];
                $clientResponse = $this->client->put($fileUrl, $htmlBody, $email, $pw);
                $clientStatus = $this->client->getHttpStatus();                
            }
            
            // If file creation was unsuccessful, roll back, break and error.
            if (200 > $clientStatus > 299) {
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
            $this->parameters['id'] = strval($pageId);
            $this->getAction();
            $this->httpStatus = $client->getHttpStatus();
            
            // Append the file URL to the output
            $this->resourceData['fileUrl'] = $fileUrl;
             
        } while (false);
        
        return $this->resourceData;
    }
    
    
    public function putAction() {
        $conn = $this->pageViewReference->getSQLConn();
        // Acquire the PUT body (if not already set)
        if (count($this->resourceData) == 0)
            $this->resourceData = json_decode(file_get_contents("php://input"), true);
        
        // Build fields.
        $this->buildFields();
        
        // Make the body field optional.
        $this->fields['htmlBody']->allowNull = true;
        $clientClass = null;
        $htmlBody = null;

        // Get the body if applicable.
        if (isset($this->resourceData['htmlBody']))
            $htmlBody = $this->resourceData['htmlBody'];
        
        if (isset($this->resourceData['clientClass']))
            $clientClass = $this->resourceData['clientClass'];
            
        do {
            // Call parent
            $putResult = parent::putAction();
            
            // Page record updated successfully?
            if ($this->httpStatus != Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse')) {
                break;
            }
            
            // If htmlBody was set, upload the document and update ext_html_page
            $pageId = $this->parameters['id'];
            if ($htmlBody) {
                // Attempt to instantiate the client
                $clientStatus = $this->setupClient($pageId);
                // Break if error.
                if ($clientStatus == 1)
                    break;

                $date = $putResult[$pageId]['updatedDate'];
                $fileNameDate = preg_replace('[ ]', '_', $date);
                $fileNameDate = preg_replace('[:]','',$fileNameDate);
                
                // Get the resource URL from toonces_config.xml
                if (!isset($this->urlPath)) {
                    $xml = new DOMDocument();
                    $xml->load(ROOTPATH.'toonces-config.xml');
                    $pathNode = $xml->getElementsByTagName('html_resource_url')->item(0);
                    $this->urlPath = $pathNode->nodeValue;
                }
                
                // Generate a file URL
                $fileUrl = $this->urlPath . strval($pageId) . '_' . $fileNameDate . '.htm';
                    
                // Create the file
                $email = $_SERVER['PHP_AUTH_USER'];
                $pw = $_SERVER['PHP_AUTH_PW'];
                $clientResponse = $this->client->put($fileUrl, $htmlBody, $email, $pw);
                $clientStatus = $this->client->getHttpStatus();
                    
                    
                if (200 > $clientStatus > 299) {
                    $this->httpStatus = $clientStatus;
                    $this->resourceData['status'] = 'Partial success; failed to upload body file.';
                    break;
                }
                
                // Update the record if success
                $sql = <<<SQL
                INSERT INTO ext_html_page
                    (page_id, html_path, client_class)
                VALUES
                    (:pageId, :htmlPath, :clientClass)
                ON DUPLICATE KEY UPDATE
                     page_id = VALUES(page_id)
                    ,html_path = VALUES(html_path)
                    ,client_class = VALUES(client_class)
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
            }
            
            // Success
            $this->getAction();
            $this->httpStatus = Enumeration::getOrdinal('HTTP_200_OK', 'EnumHTTPResponse');
            
            // Append the file URL to the output
            $this->resourceData['fileUrl'] = $fileUrl;
            
        } while (false);
        
        return $this->resourceData;
  
    }
    
    
    public function getAction() {
        $conn = $this->pageViewReference->getSQLConn();
        
        // Require authentication.
        $userId = $this->authenticateUser();
        if ($userId) {
            parent::getAction();
            
            // For each page returned, append the file URL.
            foreach ($this->resourceData as $pageId => $record) {
                // Query the database for the file URL
                $sql = "SELECT html_path FROM ext_html_page WHERE page_id = :pageId";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['pageId' => $pageId]);
                $result = $stmt->fetchAll();
                $pathUrl = $result[0]['html_path'];
                $record['fileUrl'] = $pathUrl;
            }
        } else {
            // Authentication failed.
            $this->httpStatus = Enumeration::getOrdinal('HTTP_401_UNAUTHORIZED', 'EnumHTTPResponse');
            $this->statusMessage = 'Access denied. Go away.';
            $this->resourceData = array('status' => $this->statusMessage);
            break;
        }
        
        return $this->resourceData;
    }

    public function deleteAction() {
        
        $conn = $this->pageViewReference->getSQLConn();
        
        
        do {
            // Call parent - This will requre authentication.
            parent::deleteAction();
            
            // If delete of page was successful, delete the file.
            if ($this->httpStatus == Enumeration::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse')) {
                // Query the database for the file vector.
                $id = $this->validateIntParameter['id'];
                $sql = <<<SQL
                SELECT html_path
                FROM ext_html_page
                WHERE page_id = :pageId
SQL;
                $stmt = $conn->prepare($sql);
                $stmt->execute(['pageId' => $id]);
                $result = $stmt->fetchAll();
                $htmlPath = $result[0]['html_path'];
                
                // Delete using client
                $clientState = $this->setupClient($this->parameters['id']);
                // Client setup successfully?
                if ($clientState == 1)
                    break;
                
                $email = $_SERVER['PHP_AUTH_USER'];
                $pw = $_SERVER['PHP_AUTH_PW'];
                $this->client->delete($htmlPath, $email, $pw);
                $clientStatus = $client->getHttpStatus();
                if ($clientStatus != Enumeration::getOrdinal('HTTP_204_NO_CONTENT', 'EnumHTTPResponse')) {
                    $this->resourceData['status'] = 'Failed to delete file ' . $htmlPath;
                }
                $this->httpStatus = $clientStatus;
            }

        } while (false);
        
        return $this->resourceData;
    }
}