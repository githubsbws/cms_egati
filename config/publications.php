<?php

// config/publications.php
// Published documents (admin menu id 13, website /documents).

return [

    // Largest PDF accepted, in KB (annual reports are big)
    'max_pdf_kb' => 51200,
    'max_cover_kb' => 5120,

    // Rating agencies offered in the credit rating form: name => international (true) or domestic (false)
    'agencies' => [
        'S&P Global' => true,
        'TRIS Rating' => false,
    ],

    // Rows per page on the website tables
    'per_page' => 10,

    // Thai pages show the Buddhist era
    'buddhist_offset' => 543,

    // The original website, linked as the source of the documents
    'source_base' => 'https://egati.co.th/th/%E0%B9%80%E0%B8%AD%E0%B8%81%E0%B8%AA%E0%B8%B2%E0%B8%A3%E0%B9%80%E0%B8%9C%E0%B8%A2%E0%B9%81%E0%B8%9E%E0%B8%A3%E0%B9%88/',

    // Website categories: tag text, accent colour and the page of the original website
    'types' => [
        'credit' => ['tag' => 'CREDIT RATINGS', 'color' => '#0754a0', 'title' => 'documents.credit_ratings', 'description' => 'documents.credit_description', 'source' => 'company-credit'],
        'finance' => ['tag' => 'FINANCIAL STATEMENTS', 'color' => '#007e81', 'title' => 'documents.financial_statements', 'description' => 'documents.finance_description', 'source' => 'report-finance'],
        'annual' => ['tag' => 'ANNUAL REPORTS', 'color' => '#90512f', 'title' => 'documents.annual_reports', 'description' => 'documents.annual_description', 'source' => 'report-year'],
    ],

];
