# Webhook Form Transfer
This project is a Laravel-based application that handles webhooks from forms.app and creates new forms in 123 Form Builder. It acts as an intermediary to transfer form data between these two platforms.
## Features

- Receives webhook data from forms.app
- Extracts form name and active status from the received data
- Creates a new form in 123 Form Builder using their API
- Logs the process for debugging and monitoring

## Prerequisites

- PHP 7.3 or higher
- Composer
- Laravel 8.x or higher
- A forms.app account with webhook capability
- A 123 Form Builder account with API access

## Setup

1. Clone the repository:
```bash
git clone https://github.com/Md-Abed-Hossen/webhook-form-transfer.git
cd webhook-form-transfer
```
2. Install dependencies:
```bash
composer install
```
3. Copy the `.env.example` file to `.env` and configure your environment variables:
```bash
copy .env.example .env
```
4. Add your 123 Form Builder API key to the `.env` file:
```bash
FORM_BUILDER_API_KEY=your_api_key_here
```
5. Configure your web server to point to the public directory.
Set up your forms.app webhook to point to your application's webhook URL:
```bash
https://yourdomain.com/webhook
```

## Usage

- Configure your forms.app to send webhook data to your application's `/webhook` endpoint.
- The application will receive the webhook data, extract the form name and active status.
- If the form is marked as active, it will create a new form in 123 Form Builder with the extracted name.
- Check the Laravel logs for detailed information about each webhook received and processed.

## Testing
You can use the provided test route to ensure your application is running correctly:
```bash
GET https://yourdomain.com/test
```
This should return "Test successful" and log a message.
## Troubleshooting

- If you're not receiving webhook data, ensure your forms.app webhook is correctly configured.
- Check the Laravel logs (`storage/logs/laravel.log`) for detailed error messages and process information.
- Verify that your 123 Form Builder API key is correct and has the necessary permissions.

