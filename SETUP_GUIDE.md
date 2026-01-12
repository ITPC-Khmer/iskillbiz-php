# iSkillBiz - Professional Skill Marketplace

A beautiful, modern web application built with Laravel 12, Bootstrap 5, and Facebook integration.

## Features

✨ **Beautiful Bootstrap 5 UI**
- Modern, responsive design
- Stunning gradient backgrounds
- Smooth animations and transitions
- Dark mode support ready

🔐 **Authentication System**
- User login and registration
- Password recovery
- Facebook OAuth integration
- Session management

📊 **Dashboard**
- Professional sidebar navigation
- Statistics cards
- Recent orders table
- Activity feed
- Quick actions

🔗 **Facebook Integration**
- Social login/registration with Facebook
- Get user profile information via Facebook Graph API
- Connect/disconnect Facebook accounts
- Store Facebook ID in database

## Installation

### 1. Clone or setup the project
```bash
cd /Users/globesosuperapp/PhpstormProjects/iskillbiz-php
```

### 2. Install dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

The following Facebook credentials are already added to your `.env`:
```
FACEBOOK_APP_ID=4388603488126290
FACEBOOK_APP_SECRET=e967b7c4129dfbe0f4d11de34d2da0bc
```

### 4. Database Setup
```bash
php artisan migrate
```

### 5. Generate Application Key
```bash
php artisan key:generate
```

## Project Structure

```
iskillbiz-php/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php      # Dashboard logic
│   │   │   ├── FacebookController.php       # Facebook OAuth handling
│   │   │   └── ...
│   │   └── ...
│   └── Models/
│       └── User.php                         # User model (with facebook_id)
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php               # Main layout with sidebar
│       ├── auth/
│       │   ├── login.blade.php             # Beautiful login page
│       │   ├── register.blade.php          # Registration page
│       │   └── forgot-password.blade.php   # Password recovery
│       └── dashboard.blade.php             # Dashboard page
├── routes/
│   └── web.php                             # All routes
├── database/
│   ├── migrations/
│   │   └── 2025_01_10_000003_add_facebook_id_to_users_table.php
│   └── ...
├── config/
│   └── services.php                        # Facebook config
├── .env                                    # Environment variables
└── ...
```

## Pages & Routes

### Public Routes
- `GET /` - Welcome page
- `GET /login` - Login page
- `POST /login` - Login submission
- `GET /register` - Registration page
- `POST /register` - Registration submission
- `GET /forgot-password` - Forgot password page

### Authentication Routes
- `GET /auth/facebook` - Redirect to Facebook login
- `GET /auth/facebook/callback` - Facebook callback handler

### Protected Routes (Require Authentication)
- `GET /dashboard` - User dashboard with stats and recent orders
- `GET /facebook/me` - Get authenticated user's Facebook info
- `POST /facebook/disconnect` - Disconnect Facebook account

## Styling & Design

### Color Scheme
- **Primary**: `#667eea` (Purple Blue)
- **Secondary**: `#764ba2` (Deep Purple)
- **Danger**: `#f85032` (Red)
- **Success**: `#10b981` (Green)
- **Light Background**: `#f8fafc`
- **Dark Background**: `#1a202c`

### Components
- **Sidebar**: Fixed navigation with collapsible items
- **Top Navigation**: Search bar and user actions
- **Cards**: Elevation shadows and hover effects
- **Buttons**: Gradient backgrounds with smooth transitions
- **Tables**: Clean design with hover states
- **Badges**: Color-coded status indicators

## Facebook Integration

### How It Works

1. **Login with Facebook**
   - User clicks "Login with Facebook"
   - Redirected to `GET /auth/facebook`
   - Authenticated via Facebook OAuth
   - Redirected to callback handler

2. **Callback Handler**
   - Validates Facebook response
   - Retrieves user's public profile and email
   - Creates or updates user in database
   - Stores `facebook_id` for future reference
   - Logs in the user

3. **Get Facebook Info**
   - API endpoint: `GET /facebook/me`
   - Returns user's Facebook profile data
   - Accessible only to authenticated users

4. **Disconnect Facebook**
   - Users can disconnect their Facebook account
   - Removes `facebook_id` from database
   - User can still log in with email/password

### Required Permissions
- `email` - Access user's email address
- `public_profile` - Access basic profile info

## User Model Changes

The `User` model has been updated to support Facebook:

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'facebook_id',  // NEW
];
```

## Database Migration

A new migration was created to add `facebook_id` column:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('facebook_id')->nullable()->unique();
});
```

## Running the Application

### Development Server
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

### Build Assets (if needed)
```bash
npm run build
```

### Watch for Changes
```bash
npm run dev
```

## Features Showcase

### Dashboard Statistics
- Total Skills count
- Earnings display
- Average ratings
- Active clients count

### Recent Orders Table
- Client information
- Service details
- Transaction amounts
- Order status badges

### User Profile Card
- Avatar display
- Email information
- Earnings and rating stats
- Facebook connection status
- Quick action buttons

### Sidebar Navigation
- Home/Dashboard
- My Skills
- Reviews
- Analytics
- Messages
- Settings
- Logout

## Responsive Design

The application is fully responsive:
- Mobile: Sidebar collapses, optimized layout
- Tablet: Adjusted spacing and component sizes
- Desktop: Full featured UI with all elements visible

## API Endpoints

### Facebook Integration
```
GET /auth/facebook              - Start Facebook login
GET /auth/facebook/callback     - Handle Facebook callback
GET /facebook/me                - Get logged-in user's Facebook info (Auth required)
POST /facebook/disconnect       - Disconnect Facebook account (Auth required)
```

### Authentication
```
GET /login                      - Show login form
POST /login                     - Process login
GET /register                   - Show registration form
POST /register                  - Process registration
GET /forgot-password            - Show forgot password form
POST /logout                    - Logout user
```

### Dashboard
```
GET /dashboard                  - Show dashboard (Auth required)
GET /                           - Show welcome page
```

## Security Features

✓ CSRF Protection (via `@csrf` in forms)
✓ Password Hashing (using bcrypt)
✓ Session Management
✓ Protected Routes (auth middleware)
✓ SQL Injection Prevention (via Eloquent ORM)
✓ Secure Facebook OAuth flow

## Next Steps (Enhancements)

To further enhance this application:

1. **Email Verification**
   - Send verification emails to new users
   - Verify email before account activation

2. **Password Reset**
   - Complete forgot password functionality
   - Email password reset links

3. **Additional OAuth Providers**
   - Google OAuth integration
   - GitHub OAuth integration
   - LinkedIn OAuth integration

4. **Profile Management**
   - Edit user profile
   - Upload avatar
   - Change password

5. **Admin Panel**
   - User management
   - Analytics
   - Moderation tools

6. **Notifications**
   - Real-time notifications
   - Email notifications
   - Push notifications

7. **Dark Mode**
   - Full dark mode implementation
   - User preference storage

## Troubleshooting

### Migration Issues
If migrations fail, try:
```bash
php artisan migrate:refresh
```

### Facebook Login Not Working
1. Verify `FACEBOOK_APP_ID` and `FACEBOOK_APP_SECRET` in `.env`
2. Check Facebook App Settings for callback URL
3. Ensure localhost is in Valid OAuth Redirect URIs

### Routes Not Found
Regenerate route cache:
```bash
php artisan route:clear
php artisan route:cache
```

## Support

For issues or questions, refer to:
- Laravel Documentation: https://laravel.com/docs
- Bootstrap Documentation: https://getbootstrap.com/docs
- Facebook SDK: https://developers.facebook.com/

## License

This project is open source and available under the MIT license.
