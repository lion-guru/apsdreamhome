<?php
// Simple rules-based APS suggestion model for demonstration
// Use this as a fallback or baseline before LLM integration

function aps_suggest($role, $context = []) {
    $suggestions = [];
    $now = date('Y-m-d');

    // Example rules for different roles
    if ($role === 'customer') {
        if (!empty($context['pending_docs'])) {
            $suggestions[] = "Please upload your pending documents to avoid booking delays.";
        }
        if (!empty($context['upcoming_visit'])) {
            $suggestions[] = "You have a property visit scheduled on {$context['upcoming_visit']}. Prepare your questions!";
        }
        $suggestions[] = "Check out our latest property listings and offers!";
    }
    if ($role === 'agent') {
        if (!empty($context['cold_leads'])) {
            $suggestions[] = "You have ".$context['cold_leads']." leads with no follow-up in 3+ days. Re-engage them for better conversions.";
        }
        $suggestions[] = "Review your assigned tickets and close resolved ones.";
    }
    if ($role === 'builder') {
        $suggestions[] = "Update your project progress to keep customers informed.";
    }
    if ($role === 'associate') {
        $suggestions[] = "Check your commission status and follow up on pending payments.";
    }
    if ($role === 'admin') {
        if (!empty($context['unresolved_tickets'])) {
            $suggestions[] = "There are ".$context['unresolved_tickets']." unresolved support tickets. Assign or escalate as needed.";
        }
        $suggestions[] = "Review recent automation logs for any errors or warnings.";
    }
    // Always add a generic suggestion
    $suggestions[] = "Have feedback? Use the thumbs up/down to help improve APS AI!";

    return $suggestions;
}
