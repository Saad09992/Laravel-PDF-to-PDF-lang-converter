# Laravel PDF Language Converter

A Laravel-based application for processing PDF documents and converting
their content into another language.

This project was developed as part of my professional software
development experience.

## Overview

The application combines PDF parsing, document generation, and language
translation into a web-based workflow.

The backend is built with Laravel and integrates PDF processing libraries
and a translation service to process document content and generate
translated PDF output.

## Features

- PDF document processing
- PDF text extraction
- Language translation
- Translated PDF generation
- Server-side document processing
- Web-based Laravel application
- API authentication support

## Processing Flow

```text
           PDF Document
                │
                ▼
        ┌─────────────────┐
        │   PDF Parsing   │
        │  & Text Extract │
        └────────┬────────┘
                 │
                 ▼
        ┌─────────────────┐
        │    Translation  │
        │      Service    │
        └────────┬────────┘
                 │
                 ▼
        ┌─────────────────┐
        │  PDF Generation │
        └────────┬────────┘
                 │
                 ▼
        Translated PDF
```

## Tech Stack

### Backend

- PHP 8.2+
- Laravel 11

### PDF Processing

- smalot/pdfparser
- barryvdh/laravel-dompdf
- mpdf/mpdf

### Translation

- Google Translate integration

### HTTP / API

- Guzzle
- Laravel Sanctum

### Frontend / Build Tools

- Blade
- Vite
- TailwindCSS

## Project Structure

```text
Laravel-PDF-to-PDF-lang-converter/
│
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── artisan
├── composer.json
├── package.json
└── README.md
```

## Requirements

- PHP 8.2+
- Composer
- Node.js
- npm
- A supported database

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/Saad09992/Laravel-PDF-to-PDF-lang-converter.git
cd Laravel-PDF-to-PDF-lang-converter
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install frontend dependencies

```bash
npm install
```

### 4. Create the environment file

```bash
cp .env.example .env
```

### 5. Generate the application key

```bash
php artisan key:generate
```

Configure the required environment variables in `.env`.

### 6. Run migrations

```bash
php artisan migrate
```

### 7. Start the Laravel development server

```bash
php artisan serve
```

### 8. Start the frontend development server

```bash
npm run dev
```

## Development

Laravel provides the application backend and document-processing workflow,
while the PDF libraries handle document parsing and PDF generation.

The application also integrates a translation service to convert extracted
document content before generating the translated output.

## My Contribution

I worked on this project as part of my professional software development
experience.

My work involved the Laravel application and the PDF language-conversion
workflow, including PDF processing, translation integration, and output
generation.

## What I Learned

This project gave me practical experience with:

- Laravel application development
- PDF parsing and generation
- External service integration
- File processing
- Backend application design
- Authentication with Laravel Sanctum
- Working with third-party PHP packages

## Notes

This repository is a public representation of a project worked on
during my professional experience.

Certain production-specific configuration, infrastructure, and
company-specific implementation details are not included.
