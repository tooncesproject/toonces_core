<?php
/*
 * BlogDataResource.php
 * Initial Commit: Paul Anderson, 4/10/2018
 * 
 * A DataResource object definining the inputs and outputs for a Blogs endpoint.
 * 
*/

require_once LIBPATH.'php/toonces.php';

class BlogDataResource extends DataResource implements iResource {
    

    function buildFields() {
        // Define the sub-resources of this resource.
        // url
        $pathName = new StringDataFieldResource(); // "pathName": null,
        $this->fields['pathName'] = $pathName;
        
        $pageURI = new StringDataFieldResource(); // pageURI": "",
        $this->fields['pageURI'] = $pageURI;
        
        $pageTitle = new StringDataFieldResource(); // "pageTitle": "Sorry, This is Toonces.",
        $this->fields['pageTitle'] = $pageTitle;
        
        $pageLinkText = new StringDataFieldResource(); // "pageLinkText": "Home Page",
        $this->fields['pageLinkText'] = $pageLinkText;
        
        $ancestorPageID = new IntegerDataFieldResource(); // "ancestorPageID": null
        $this->fields['ancestorPageID'] = $ancestorPageID;
        
        // "Blog Posts" // <<< NESTED RESOURCE!!!
    }
    
    function getResource() {
        // Query the database for the resource, depending upon parameters
    }
    
    function postResource($postData) {
        // Validate fields in post data for data type
    }
    
    function putResource($putData) {
        // Validate fields in PUT data for data type
        // Update existing resource
    }
    
    function deleteResource() {
        // Delete resource
    }
    
    
}

