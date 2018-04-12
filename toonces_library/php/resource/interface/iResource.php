<?php
/*
 * iResource Interface
 * Paul Anderson 8/15/15
 * 
 * Provides an interface for implementing individual components of a page.
 * 
 * Toonces implements the Strategy pattern (or something like it) to build up individual 
 * components of a page/API resource.
 * 
 * The idea is a 1-n relationship between the page being rendered and its individual components.
 * That way, the view object can iterate through any number of sub-view objects, each implementing
 * a method to return HTML or JSON.
 * 
 */

interface iResource
{   
    protected function getResource();
}