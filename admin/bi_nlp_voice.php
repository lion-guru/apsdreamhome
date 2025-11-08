<?php
session_start();
include 'config.php';
require_role('Admin');
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>AI Business Intelligence (NLP & Voice)</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>AI Business Intelligence (NLP & Voice)</h2><form method='post'><div class='mb-3'><label>Ask a Question (NLP/Voice)</label><input type='text' name='nlp_query' class='form-control' placeholder='e.g., Show me last month sales'></div><button class='btn btn-success'>Analyze</button></form><div class='alert alert-warning mt-3'>NLP/Voice analytics integration ready. Connect to OpenAI, Google, or custom NLP/voice AI for instant business insights.</div></div></body></html>
