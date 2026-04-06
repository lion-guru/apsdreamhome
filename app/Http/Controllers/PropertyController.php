<?php
namespace App\Http\Controllers;

class PropertyController 
{
    public function index() 
    {
        // Properties Listing Page
        include __DIR__ . "/../../views/properties/index.php";
    }
    
    public function search() 
    {
        // Property Search Results
        include __DIR__ . "/../../views/properties/search.php";
    }
    
    public function detail($id) 
    {
        // Property Detail Page
        include __DIR__ . "/../../views/properties/detail.php";
    }
    
    public function colonies() 
    {
        // Colonies Listing Page
        include __DIR__ . "/../../views/properties/colonies.php";
    }
    
    public function colony($id) 
    {
        // Colony Detail Page
        include __DIR__ . "/../../views/properties/colony.php";
    }
    
    public function projects() 
    {
        // Projects Listing Page
        include __DIR__ . "/../../views/properties/projects.php";
    }
    
    public function project($id) 
    {
        // Project Detail Page
        include __DIR__ . "/../../views/properties/project.php";
    }
    
    public function resell() 
    {
        // Resell Properties Listing
        include __DIR__ . "/../../views/properties/resell.php";
    }
    
    public function resellDetail($id) 
    {
        // Resell Property Detail
        include __DIR__ . "/../../views/properties/resell_detail.php";
    }
    
    public function submitProperty() 
    {
        // Submit Property Form
        include __DIR__ . "/../../views/properties/submit.php";
    }
    
    public function compare() 
    {
        // Property Comparison
        include __DIR__ . "/../../views/properties/compare.php";
    }
}