<?php
require_once 'vendor/autoload.php';
require_once 'app/Core/Database/Database.php';
require_once 'app/Models/LegalDocument.php';
require_once 'app/Models/LegalDocumentVersion.php';
require_once 'app/Models/LegalDocumentAcceptance.php';
require_once 'app/Models/User.php';

echo "Testing LegalDocument model...\n";

// Create a test document
$uniqueSuffix = time();
$doc = new \App\Models\LegalDocument();
$doc->title = 'Test Terms of Service ' . $uniqueSuffix;
$doc->slug = 'test-terms-of-service-' . $uniqueSuffix;
$doc->category = 'company';
$doc->document_type = 'terms';
$doc->content = '<h1>Test Terms</h1><p>This is test content.</p>';
$doc->summary = 'Test summary';
$doc->status = 'published';
$doc->is_mandatory = true;
$doc->applies_to_roles = ['customer', 'associate'];
$doc->metadata = ['jurisdiction' => 'India', 'effective_date' => '2024-01-01'];
$doc->created_by = 1;
$doc->save();

echo 'Document created with ID: ' . $doc->id . "\n";
echo 'Slug: ' . $doc->slug . "\n";
echo 'URL: ' . $doc->url . "\n";
echo 'Version: ' . $doc->version . "\n";

// Test scopes (they return arrays now)
$published = \App\Models\LegalDocument::published();
echo "Published count: " . count($published) . "\n";

$mandatory = \App\Models\LegalDocument::mandatory();
echo "Mandatory count: " . count($mandatory) . "\n";

$byCategory = \App\Models\LegalDocument::category('company');
echo "Company category count: " . count($byCategory) . "\n";

// Test relationships
$versions = $doc->getVersions();
echo "Versions count: " . count($versions) . "\n";

// Test acceptance
$acceptances = $doc->getAcceptances();
echo "Acceptances count: " . count($acceptances) . "\n";

// Test URL
echo "URL: " . $doc->url . "\n";

// Test isAcceptedBy
$user = new \App\Models\User();
$user->id = 1;
$accepted = $doc->isAcceptedBy($user);
echo "Accepted by user: " . ($accepted ? 'YES' : 'NO') . "\n";

// Create version
$version = $doc->createVersion('Test version');
echo "Version created: " . $version->version . "\n";

// Test URL attribute
echo "URL attr: " . $doc->url . "\n";

// Clean up
$doc->delete();
echo "Test completed successfully!\n";