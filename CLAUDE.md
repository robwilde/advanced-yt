# Advanced YouTube - Content Curation & Automation Platform

## Project Vision
This is a **content organization and automation platform** that helps users manage their YouTube subscriptions by categorizing channels and automatically organizing new videos into custom playlists. Users can categorize their 1000+ subscriptions, map categories to playlists, and have new videos automatically added to the appropriate playlists based on their channel's category.

## Project Type
This is a **Laravel 12** web application with **Livewire v3** for reactive UI components. It integrates with the **YouTube Data API v3** to provide subscription management, playlist automation, and content curation capabilities.

## Core Technology Stack
- **Backend**: Laravel 12.17 (PHP 8.3+)
- **Frontend**: Livewire 3.6, Volt 1.7, Tailwind CSS 3.1
- **Database**: SQLite (development), supports other databases
- **Testing**: PEST 3.8 (modern PHP testing framework)
- **Build Tools**: Vite 6.2, Node.js
- **API Integration**: Google API Client for YouTube
- **Authentication**: Laravel Breeze (included)

## Project Structure

### Key Directories
```
/app
├── Actions/           # Single-purpose action classes (Command pattern)
├── Http/Controllers/  # Standard Laravel controllers
├── Services/         # Business logic services with contracts
├── Livewire/         # Livewire components (forms, actions)
├── Models/           # Eloquent models
└── View/Components/  # Blade view components

/resources
├── css/              # Tailwind CSS styles
├── js/               # Frontend JavaScript
└── views/            # Blade templates (includes Livewire views)

/tests
├── Feature/          # Integration tests
└── Unit/             # Unit tests with PEST framework

/config
├── services.php      # Third-party service configs (YouTube API)
└── [standard Laravel configs]
```

### Architecture Patterns

#### 1. **Action Pattern**
Single-purpose classes for complex operations:
- `FetchYouTubePlaylistItems`
- `FetchYouTubePlaylists` 
- `FetchYouTubeSubscriptions`

#### 2. **Service Pattern with Contracts**
- `YouTubeService` implements `YouTubeServiceInterface`
- Handles all YouTube API interactions
- Injectable and testable

#### 3. **Repository/Service Layer**
- Services handle business logic
- Controllers are thin, delegate to services/actions
- Database operations wrapped in transactions

## Key Configuration Files

### PHP Dependencies (composer.json)
```json
{
  "require": {
    "php": "^8.3",
    "laravel/framework": "^12.17",
    "livewire/livewire": "^3.6.3", 
    "livewire/volt": "^1.7.1",
    "google/apiclient": "^2.15.0"
  }
}
```

### Frontend Dependencies (package.json)
```json
{
  "devDependencies": {
    "@tailwindcss/forms": "^0.5.2",
    "tailwindcss": "^3.1.0",
    "vite": "^6.2.4",
    "laravel-vite-plugin": "^1.2.0"
  }
}
```

## Development Commands

### Backend Development
```bash
# Start development server (includes all services)
composer dev
# Equivalent to: php artisan serve + queue:listen + pail + npm run dev

# Individual commands
php artisan serve          # Start Laravel server
php artisan queue:listen   # Process background jobs
php artisan pail          # Real-time log viewer
```

### Frontend Development
```bash
npm run dev     # Start Vite development server
npm run build   # Build for production
```

### Testing
```bash
composer test              # Run full test suite
./vendor/bin/pest         # Run PEST tests directly
./vendor/bin/pest --filter=YouTube  # Run specific tests
```

### Code Quality
```bash
./vendor/bin/pint         # Laravel Pint (PHP-CS-Fixer)
```

## YouTube API Integration

### Environment Variables Required
```env
YOUTUBE_CLIENT_ID=your-google-oauth-client-id
YOUTUBE_CLIENT_SECRET=your-google-oauth-client-secret  
YOUTUBE_REDIRECT_URI=http://localhost:8000/auth/youtube/callback
YOUTUBE_API_KEY=your-youtube-api-key
```

### OAuth Flow
1. User clicks "Connect YouTube Account" 
2. Redirects to Google OAuth (`/auth/youtube`)
3. Google redirects back to callback (`/auth/youtube/callback`)
4. Access token stored in session
5. Token used for API calls

### API Features Implemented
- **Playlists**: Fetch user's YouTube playlists
- **Playlist Items**: Get videos from specific playlists  
- **Subscriptions**: Fetch user's channel subscriptions (with caching for 1000+ subscriptions)
- **OAuth Authentication**: Complete Google OAuth 2.0 flow with persistent token storage

## Content Curation & Automation Features

### Core Functionality
This application provides a comprehensive system for organizing and automating YouTube content consumption:

#### 1. **Subscription Management** ✅ IMPLEMENTED
- **Mass Subscription Handling**: Support for 1000+ subscriptions with database caching
- **Pagination System**: Efficient browsing with 50 items per page (5×10 grid layout)
- **Background Sync**: Automatic subscription fetching via queued jobs
- **Real-time Updates**: Manual sync capability with progress indicators

#### 2. **Category System** 🔄 TO BE IMPLEMENTED
- **User-Defined Categories**: Create custom categories like "Tech", "Science", "Gaming"
- **Multiple Category Support**: Channels can belong to multiple categories
- **Category Management**: Full CRUD operations for category management
- **Category Assignment**: Assign categories to subscribed channels

#### 3. **Playlist Automation** 🔄 TO BE IMPLEMENTED
- **Category-to-Playlist Mapping**: Link categories to multiple playlists
- **Playlist CRUD**: Create, read, update, delete playlists through the app
- **Automatic Video Addition**: New videos auto-added to playlists based on channel categories
- **Manual Override**: Ability to manually move/categorize specific videos

#### 4. **Video Monitoring & Detection** 🔄 TO BE IMPLEMENTED
- **Scheduled Monitoring**: Regular checks for new videos from categorized channels
- **Upload Date Tracking**: Detect new content based on upload timestamps
- **Selective Monitoring**: Only monitor channels with assigned categories
- **Batch Processing**: Efficient handling of multiple new videos

#### 5. **Notification System** 🔄 TO BE IMPLEMENTED
- **Email Notifications**: Alert users about new videos added to playlists
- **User Preferences**: Separate notification email from YouTube account email
- **Notification Settings**: Configure frequency and types of notifications
- **Activity Summary**: Periodic summaries of automation activity

### Current Implementation Status

#### ✅ **Completed Features**
- OAuth 2.0 authentication with persistent token storage
- Subscription caching system supporting 1000+ subscriptions
- Pagination with responsive grid layout (5 items per row)
- Background job processing for large-scale API operations
- Database models: `UserYouTubeAccount`, `YouTubeSubscription`

#### 🔄 **In Progress**
- Subscription list functionality in dashboard

#### 🎯 **Next Implementation Phase**
1. Category management system
2. Channel categorization interface
3. Playlist management and mapping
4. Video monitoring automation
5. Notification system

### Database Schema

#### Current Tables
```sql
-- User YouTube account management
user_youtube_accounts:
- id, user_id, youtube_channel_id
- access_token, refresh_token, token_expires_at
- last_synced_at, created_at, updated_at

-- Cached subscription data
youtube_subscriptions:
- id, user_id, subscription_id, channel_id
- channel_title, channel_description, thumbnail_url
- subscribed_at, cached_at, created_at, updated_at
```

#### Planned Tables
```sql
-- Category management
categories:
- id, user_id, name, description, color
- created_at, updated_at

-- Channel categorization (many-to-many)
channel_categories:
- id, user_id, channel_id, category_id
- created_at, updated_at

-- Playlist management
user_playlists:
- id, user_id, youtube_playlist_id, title
- description, created_at, updated_at

-- Category to playlist mapping (many-to-many)
category_playlists:
- id, category_id, playlist_id
- created_at, updated_at

-- Video tracking for automation
tracked_videos:
- id, user_id, channel_id, video_id
- title, description, published_at
- added_to_playlist_at, created_at, updated_at

-- User preferences
user_preferences:
- id, user_id, notification_email
- email_frequency, notification_types
- created_at, updated_at
```

### Automation Architecture

#### 1. **Monitoring System**
```php
// Scheduled job runs every hour
MonitorChannelsForNewVideos::class
├── Fetch latest videos for categorized channels
├── Compare with last known video dates
├── Identify new videos
└── Trigger playlist addition for new content
```

#### 2. **Playlist Automation Flow**
```
New Video Detected
    ↓
Get Channel Categories
    ↓
Find Mapped Playlists
    ↓
Add Video to Playlists (YouTube API)
    ↓
Log Activity & Send Notifications
```

#### 3. **Background Job Queue**
- `SyncYouTubeSubscriptions` - Mass subscription sync
- `MonitorChannelForNewVideos` - Per-channel monitoring
- `AddVideoToPlaylist` - Automated playlist addition
- `SendVideoNotification` - User notifications

## Routes Structure
```php  
// Main app routes
Route::get('dashboard', DashboardController::class)->name('dashboard');
Route::get('subscriptions', SubscriptionsController::class)->name('subscriptions');

// YouTube OAuth routes
Route::get('auth/youtube', [YouTubeAuthController::class, 'redirect']);
Route::get('auth/youtube/callback', [YouTubeAuthController::class, 'callback']);

// Auth routes (Laravel Breeze)
require __DIR__.'/auth.php';
```

## Database
- Uses SQLite for development (`database/database.sqlite`)
- Standard Laravel migrations in `/database/migrations/`
- Factories and seeders available
- User model with authentication

## Code Standards
- **Strict Types**: All PHP files use `declare(strict_types=1)`
- **Final Classes**: Most classes are marked `final`
- **PSR-12**: Code style enforced by Laravel Pint
- **Dependency Injection**: Constructor injection throughout
- **Interface Segregation**: Services implement contracts

## Current Git Status
- **Current Branch**: `feature/build-subscription-list-in-dashboard`
- **Main Branch**: `main`
- **Recent Work**: Adding subscription functionality, strict types enforcement

## Documentation Files
- `README.md` - Basic Laravel info + YouTube API overview
- `YOUTUBE_API_SETUP.md` - Complete Google Cloud/YouTube API setup guide
- `CHANGES_SUMMARY.md` - Recent implementation details
- This file (`CLAUDE.md`) - Comprehensive project overview

## UI/UX
- **Design System**: Tailwind CSS with dark mode support
- **Components**: Blade components + Livewire reactive components
- **Responsive**: Mobile-first design with grid layouts
- **Theme**: Professional dashboard interface with YouTube branding

## Testing Strategy
- **PEST Framework**: Modern PHP testing
- **Unit Tests**: Services, Actions, Models
- **Feature Tests**: HTTP endpoints, Authentication flows
- **Mocking**: Google API client mocking for reliable tests
- **Database**: In-memory SQLite for test isolation

## Common Development Tasks

### Adding New YouTube API Features
1. Add method to `YouTubeServiceInterface` 
2. Implement method in `YouTubeService`
3. Create Action class for complex operations
4. Add controller method if needed
5. Write PEST tests
6. Update routes if necessary

### Adding New Pages
1. Create controller (or use existing)
2. Create Blade view in `resources/views/`
3. Add route in `routes/web.php`
4. Add navigation links if needed
5. Write feature tests

### Working with Livewire
- Components in `app/Livewire/`
- Views in `resources/views/livewire/`
- Use Livewire Volt for simple reactive components

## Troubleshooting
- Check logs: `storage/logs/laravel.log` or use `php artisan pail`
- YouTube API issues: Verify credentials in `.env`
- OAuth errors: Check redirect URI matches Google Cloud Console
- Build issues: Clear caches with `php artisan config:clear`

## Development Roadmap

### Phase 1: Category Management System 🎯 NEXT
**Goal**: Allow users to create and manage categories for their subscriptions

#### Tasks:
1. **Database Setup**
   - Create `categories` migration and model
   - Create `channel_categories` pivot table migration
   - Add model relationships and validation

2. **Category CRUD Interface**
   - Create category management page/component
   - Add category creation form (name, description, color)
   - Implement category editing and deletion
   - Add category listing with search/filter

3. **Channel Categorization**
   - Add category assignment interface to subscription views
   - Implement multi-select category assignment
   - Add visual indicators for categorized channels
   - Create uncategorized filter view

### Phase 2: Playlist Management 🔄 UPCOMING
**Goal**: Enable playlist creation and category-to-playlist mapping

#### Tasks:
1. **Playlist CRUD Operations**
   - Implement YouTube playlist creation via API
   - Add playlist management interface
   - Enable playlist editing (title, description)
   - Add playlist deletion with confirmation

2. **Category-Playlist Mapping**
   - Create mapping interface for categories to playlists
   - Support multiple playlists per category
   - Add mapping visualization/dashboard
   - Implement mapping validation rules

### Phase 3: Video Monitoring & Automation 🚀 FUTURE
**Goal**: Automate video detection and playlist addition

#### Tasks:
1. **Video Detection System**
   - Create video monitoring scheduled job
   - Implement channel video fetching
   - Add new video detection logic
   - Create video tracking database table

2. **Automation Engine**
   - Build automatic playlist addition system
   - Implement category-based routing logic
   - Add error handling for API failures
   - Create automation activity logging

### Phase 4: Notification & User Preferences 📧 FUTURE
**Goal**: Notify users about automation activity

#### Tasks:
1. **User Preferences**
   - Create user preferences system
   - Add notification email configuration
   - Implement notification frequency settings
   - Add notification type preferences

2. **Notification System**
   - Build email notification system
   - Create notification templates
   - Implement notification queuing
   - Add notification history/tracking

### Technical Debt & Improvements
- **Error Handling**: Enhance API error handling and retry logic
- **Rate Limiting**: Implement proper YouTube API rate limiting
- **Caching**: Optimize database queries and add Redis caching
- **Testing**: Expand test coverage for new features
- **Performance**: Optimize for users with 5000+ subscriptions
- **UI/UX**: Enhance responsive design and accessibility

### API Research Required
- **Playlist Creation**: Investigate YouTube API playlist creation capabilities
- **Video Uploads**: Research detecting new video uploads efficiently
- **Quota Management**: Understand YouTube API quotas for automation
- **Webhook Support**: Investigate YouTube webhook/notification options