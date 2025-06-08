# YouTube API Setup Guide

This guide will help you set up the necessary credentials to connect your application to the YouTube API v3.

## Prerequisites

- A Google account
- Your application running locally (or deployed with a publicly accessible URL)

## Step 1: Create a Google Cloud Project

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Click on the project dropdown at the top of the page
3. Click on "New Project"
4. Enter a name for your project and click "Create"
5. Wait for the project to be created and then select it from the project dropdown

## Step 2: Enable the YouTube Data API v3

1. In the Google Cloud Console, navigate to "APIs & Services" > "Library"
2. Search for "YouTube Data API v3"
3. Click on the API in the search results
4. Click "Enable"

## Step 3: Create OAuth 2.0 Credentials

1. In the Google Cloud Console, navigate to "APIs & Services" > "Credentials"
2. Click "Create Credentials" and select "OAuth client ID"
3. If prompted, configure the OAuth consent screen:
   - User Type: External (or Internal if you're using Google Workspace)
   - App name: Your application name
   - User support email: Your email
   - Developer contact information: Your email
   - Authorized domains: Add your domain (if applicable)
   - Click "Save and Continue" through the remaining steps
4. Back on the "Create OAuth client ID" page:
   - Application type: Web application
   - Name: Your application name
   - Authorized JavaScript origins: Add your application's URL (e.g., `http://localhost:8000`)
   - Authorized redirect URIs: Add your application's callback URL (e.g., `http://localhost:8000/auth/youtube/callback`)
   - Click "Create"
5. You'll see a popup with your client ID and client secret. Copy these values.

## Step 4: Create API Key

1. In the Google Cloud Console, navigate to "APIs & Services" > "Credentials"
2. Click "Create Credentials" and select "API Key"
3. Copy the generated API key
4. (Optional but recommended) Click "Restrict Key" and set up restrictions:
   - API restrictions: Restrict to YouTube Data API v3
   - Application restrictions: Consider restricting to your domains/IPs

## Step 5: Update Your .env File

Update your `.env` file with the credentials you just created:

```
YOUTUBE_CLIENT_ID=your-client-id
YOUTUBE_CLIENT_SECRET=your-client-secret
YOUTUBE_API_KEY=your-api-key
YOUTUBE_REDIRECT_URI=http://localhost:8000/auth/youtube/callback
```

Replace the placeholder values with your actual credentials.

## Step 6: Connect Your YouTube Account

1. Start your application
2. Log in to your account
3. Go to the dashboard
4. Click the "Connect YouTube Account" button
5. Follow the Google OAuth flow to authorize your application

## Troubleshooting

### Error: "redirect_uri_mismatch"

Make sure the redirect URI in your `.env` file exactly matches the one you configured in the Google Cloud Console.

### Error: "invalid_client"

Double-check your client ID and client secret in the `.env` file.

### Error: "Access Not Configured"

Make sure you've enabled the YouTube Data API v3 for your project.

### Error: "Daily Limit Exceeded"

The YouTube API has usage limits. Consider implementing caching to reduce API calls.

## Additional Resources

- [YouTube Data API Documentation](https://developers.google.com/youtube/v3/docs)
- [Google OAuth 2.0 Documentation](https://developers.google.com/identity/protocols/oauth2)
- [Google API Explorer](https://developers.google.com/apis-explorer/#p/youtube/v3/)
