<?php
/*
 * iResource Interface
 * Paul Anderson 8/15/15
 * 
 * Provides an interface for implementing individual components of a page.
 * 
 * Dorktron implements the Strategy pattern (or something like it) to build up individual 
 * components of a page.
 * 
 * This way, any individual HTML snippet (header? Nav bar? Whatevs) can be encapsulated
 * in an Element object and retreieved by the view (iHTMLView compliant) object. 
 * 
 * The idea is a 1-n relationship between the page being rendered and its individual components.
 * That way, the view object can iterate through any number of sub-view objects, each implementing
 * a method to return HTML.
 * 
 */

interface iResource
{
	public function getResource();
}