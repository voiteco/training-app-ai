# Google Sheets API Integration

This document provides instructions on how to set up and use the Google Sheets API integration with this Symfony project.

## Setup Instructions

### 1. Create a Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the **Google Sheets API** for your project:
   - Navigate to "APIs & Services" > "Library"
   - Search for "Google Sheets API"
   - Click on it and press "Enable"

### 2. Create Service Account Credentials

1. In the Google Cloud Console, navigate to "APIs & Services" > "Credentials"
2. Click "Create Credentials" and select "Service Account"
3. Fill in the service account details and click "Create"
4. Grant the service account the necessary roles (at least "Viewer" role)
5. Click "Done"

### 3. Generate and Download JSON Key

1. In the Service Accounts list, click on the email address of the service account you just created
2. Go to the "Keys" tab
3. Click "Add Key" > "Create new key"
4. Select "JSON" as the key type and click "Create"
5. The JSON key file will be downloaded to your computer

### 4. Add Credentials to the Project

1. Rename the downloaded JSON key file to `credentials.json`
2. Copy the file to the `config/google/` directory in this project
3. Make sure the file is not committed to version control (it should be ignored by `.gitignore`)

### 5. Share Your Google Sheet

1. Open the Google Sheet you want to access
2. Click the "Share" button in the top-right corner
3. Add the service account email address (it ends with `@project.iam.gserviceaccount.com`)
4. Grant at least "Viewer" access
5. Click "Done"

## Usage

### Testing the Integration

Use the provided command to test if the integration is working:

```bash
php bin/console app:google-sheets:test SPREADSHEET_ID [RANGE]
```

Where:
- `SPREADSHEET_ID` is the ID of your Google Sheet (found in the URL)
- `RANGE` is optional and defaults to 'Sheet1!A1:D10'

### Importing Training Data

To import training data from a Google Sheet:

```bash
php bin/console app:import:trainings SPREADSHEET_ID [RANGE]
```

The spreadsheet should have the following columns:
- `Title` (required): The title of the training
- `Date` (required): The date of the training (YYYY-MM-DD format)
- `Time` (required): The time of the training (HH:MM format)
- `Slots` (required): The number of available slots
- `Price` (required): The price of the training
- `Description` (optional): A description of the training
- `Duration` (optional): The duration of the training in minutes (defaults to 60)

## Troubleshooting

### Common Issues

1. **"Google API Client not found"**: Make sure you've installed the Google API Client library using Composer:
   ```bash
   composer require google/apiclient:^2.0
   ```

2. **"Invalid credentials"**: Ensure your `credentials.json` file is correctly placed in the `config/google/` directory and contains valid credentials.

3. **"Access denied"**: Make sure you've shared your Google Sheet with the service account email address and granted appropriate permissions.

4. **"API not enabled"**: Verify that you've enabled the Google Sheets API in your Google Cloud project.

### Getting Help

If you encounter any issues not covered here, please refer to:
- [Google Sheets API Documentation](https://developers.google.com/sheets/api)
- [Google API Client Library for PHP Documentation](https://github.com/googleapis/google-api-php-client)