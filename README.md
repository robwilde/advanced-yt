<p align="center">
  <img src="public/images/advanced-yt-icon.png" alt="Advanced YT Icon" width="120"/>
</p>

# Advanced YT- Playlist Manager 

## YouTube API Integration

This project includes a service for connecting to the YouTube API to access account playlists. The integration uses the Google API Client library and follows the Actions pattern.

### Environment Variables

The following environment variables are required for the YouTube API integration:

```
YOUTUBE_CLIENT_ID=your-client-id
YOUTUBE_CLIENT_SECRET=your-client-secret
YOUTUBE_REDIRECT_URI=your-redirect-uri
YOUTUBE_API_KEY=your-api-key
```

These are currently stored in the `.env` file but will eventually be moved to a database table associated with users.

### Services

- `YouTubeService`: Handles the connection to the YouTube API and provides methods for authentication and fetching playlists and playlist items.

### Actions

- `FetchYouTubePlaylists`: Action to fetch a user's YouTube playlists.
- `FetchYouTubePlaylistItems`: Action to fetch items from a specific YouTube playlist.

### Testing

The YouTube API integration is covered by PEST tests:

- `tests/Unit/Services/YouTubeServiceTest.php`: Tests for the YouTube service.
- `tests/Unit/Actions/FetchYouTubePlaylistsTest.php`: Tests for the FetchYouTubePlaylists action.
- `tests/Unit/Actions/FetchYouTubePlaylistItemsTest.php`: Tests for the FetchYouTubePlaylistItems action.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development/)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
